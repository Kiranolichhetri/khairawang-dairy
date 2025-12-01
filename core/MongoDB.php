<?php

declare(strict_types=1);

namespace Core;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database as MongoDatabase;
use MongoDB\BSON\ObjectId;
use Core\Exceptions\DatabaseException;

/**
 * MongoDB Connection Manager
 * 
 * MongoDB wrapper with CRUD operations, collection support, and query building.
 */
class MongoDB
{
    private ?Client $client = null;
    private ?MongoDatabase $database = null;
    
    /** @var array<string, mixed> */
    private array $config;
    
    /** @var array<array{operation: string, collection: string, time: float}> */
    private array $queryLog = [];
    
    private bool $loggingEnabled = false;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'uri' => 'mongodb://localhost:27017',
            'database' => 'khairawang_dairy',
            'options' => [],
        ], $config);
    }

    /**
     * Get the MongoDB client
     */
    public function getClient(): Client
    {
        if ($this->client === null) {
            $this->connect();
        }
        
        return $this->client;
    }

    /**
     * Get the database instance
     */
    public function getDatabase(): MongoDatabase
    {
        if ($this->database === null) {
            $this->connect();
        }
        
        return $this->database;
    }

    /**
     * Connect to MongoDB
     */
    private function connect(): void
    {
        try {
            $this->client = new Client(
                $this->config['uri'],
                $this->config['options'] ?? []
            );
            $this->database = $this->client->selectDatabase($this->config['database']);
        } catch (\Exception $e) {
            throw new DatabaseException("MongoDB connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get a collection
     */
    public function collection(string $name): Collection
    {
        return $this->getDatabase()->selectCollection($name);
    }

    /**
     * Find documents in a collection
     * 
     * @param array<string, mixed> $filter
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    public function find(string $collection, array $filter = [], array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $cursor = $this->collection($collection)->find($filter, $options);
            $results = [];
            
            foreach ($cursor as $document) {
                $results[] = $this->documentToArray($document);
            }
            
            if ($this->loggingEnabled) {
                $this->logQuery('find', $collection, microtime(true) - $startTime);
            }
            
            return $results;
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB find failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Find one document in a collection
     * 
     * @param array<string, mixed> $filter
     * @param array<string, mixed> $options
     * @return array<string, mixed>|null
     */
    public function findOne(string $collection, array $filter = [], array $options = []): ?array
    {
        $startTime = microtime(true);
        
        try {
            $document = $this->collection($collection)->findOne($filter, $options);
            
            if ($this->loggingEnabled) {
                $this->logQuery('findOne', $collection, microtime(true) - $startTime);
            }
            
            return $document !== null ? $this->documentToArray($document) : null;
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB findOne failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Find a document by ID
     * 
     * @return array<string, mixed>|null
     */
    public function findById(string $collection, string|ObjectId $id): ?array
    {
        $objectId = $id instanceof ObjectId ? $id : new ObjectId($id);
        return $this->findOne($collection, ['_id' => $objectId]);
    }

    /**
     * Insert a document
     * 
     * @param array<string, mixed> $document
     */
    public function insertOne(string $collection, array $document): string
    {
        $startTime = microtime(true);
        
        try {
            // Add timestamps if not present
            if (!isset($document['created_at'])) {
                $document['created_at'] = new \MongoDB\BSON\UTCDateTime();
            }
            if (!isset($document['updated_at'])) {
                $document['updated_at'] = new \MongoDB\BSON\UTCDateTime();
            }
            
            $result = $this->collection($collection)->insertOne($document);
            
            if ($this->loggingEnabled) {
                $this->logQuery('insertOne', $collection, microtime(true) - $startTime);
            }
            
            return (string) $result->getInsertedId();
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB insert failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Insert multiple documents
     * 
     * @param array<int, array<string, mixed>> $documents
     * @return array<string>
     */
    public function insertMany(string $collection, array $documents): array
    {
        $startTime = microtime(true);
        
        try {
            // Add timestamps to all documents
            $now = new \MongoDB\BSON\UTCDateTime();
            foreach ($documents as &$document) {
                if (!isset($document['created_at'])) {
                    $document['created_at'] = $now;
                }
                if (!isset($document['updated_at'])) {
                    $document['updated_at'] = $now;
                }
            }
            
            $result = $this->collection($collection)->insertMany($documents);
            
            if ($this->loggingEnabled) {
                $this->logQuery('insertMany', $collection, microtime(true) - $startTime);
            }
            
            return array_map(fn($id) => (string) $id, $result->getInsertedIds());
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB insertMany failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Update one document
     * 
     * @param array<string, mixed> $filter
     * @param array<string, mixed> $update
     * @param array<string, mixed> $options
     */
    public function updateOne(string $collection, array $filter, array $update, array $options = []): int
    {
        $startTime = microtime(true);
        
        try {
            // Wrap update in $set if not already using operators
            if (!$this->hasUpdateOperators($update)) {
                $update = ['$set' => $update];
            }
            
            // Always update the updated_at timestamp
            if (!isset($update['$set'])) {
                $update['$set'] = [];
            }
            $update['$set']['updated_at'] = new \MongoDB\BSON\UTCDateTime();
            
            $result = $this->collection($collection)->updateOne($filter, $update, $options);
            
            if ($this->loggingEnabled) {
                $this->logQuery('updateOne', $collection, microtime(true) - $startTime);
            }
            
            return $result->getModifiedCount();
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB updateOne failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Update multiple documents
     * 
     * @param array<string, mixed> $filter
     * @param array<string, mixed> $update
     * @param array<string, mixed> $options
     */
    public function updateMany(string $collection, array $filter, array $update, array $options = []): int
    {
        $startTime = microtime(true);
        
        try {
            if (!$this->hasUpdateOperators($update)) {
                $update = ['$set' => $update];
            }
            
            if (!isset($update['$set'])) {
                $update['$set'] = [];
            }
            $update['$set']['updated_at'] = new \MongoDB\BSON\UTCDateTime();
            
            $result = $this->collection($collection)->updateMany($filter, $update, $options);
            
            if ($this->loggingEnabled) {
                $this->logQuery('updateMany', $collection, microtime(true) - $startTime);
            }
            
            return $result->getModifiedCount();
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB updateMany failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Delete one document
     * 
     * @param array<string, mixed> $filter
     */
    public function deleteOne(string $collection, array $filter): int
    {
        $startTime = microtime(true);
        
        try {
            $result = $this->collection($collection)->deleteOne($filter);
            
            if ($this->loggingEnabled) {
                $this->logQuery('deleteOne', $collection, microtime(true) - $startTime);
            }
            
            return $result->getDeletedCount();
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB deleteOne failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Delete multiple documents
     * 
     * @param array<string, mixed> $filter
     */
    public function deleteMany(string $collection, array $filter): int
    {
        $startTime = microtime(true);
        
        try {
            $result = $this->collection($collection)->deleteMany($filter);
            
            if ($this->loggingEnabled) {
                $this->logQuery('deleteMany', $collection, microtime(true) - $startTime);
            }
            
            return $result->getDeletedCount();
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB deleteMany failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Count documents in a collection
     * 
     * @param array<string, mixed> $filter
     */
    public function count(string $collection, array $filter = []): int
    {
        $startTime = microtime(true);
        
        try {
            $count = $this->collection($collection)->countDocuments($filter);
            
            if ($this->loggingEnabled) {
                $this->logQuery('count', $collection, microtime(true) - $startTime);
            }
            
            return $count;
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB count failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Aggregate documents
     * 
     * @param array<int, array<string, mixed>> $pipeline
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    public function aggregate(string $collection, array $pipeline, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $cursor = $this->collection($collection)->aggregate($pipeline, $options);
            $results = [];
            
            foreach ($cursor as $document) {
                $results[] = $this->documentToArray($document);
            }
            
            if ($this->loggingEnabled) {
                $this->logQuery('aggregate', $collection, microtime(true) - $startTime);
            }
            
            return $results;
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB aggregate failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Create an index
     * 
     * @param array<string, int> $keys
     * @param array<string, mixed> $options
     */
    public function createIndex(string $collection, array $keys, array $options = []): string
    {
        try {
            return $this->collection($collection)->createIndex($keys, $options);
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB createIndex failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Drop a collection
     */
    public function dropCollection(string $collection): void
    {
        try {
            $this->collection($collection)->drop();
        } catch (\Exception $e) {
            throw new DatabaseException(
                "MongoDB dropCollection failed: " . $e->getMessage() . " [Collection: {$collection}]",
                0,
                $e
            );
        }
    }

    /**
     * Check if update array has MongoDB operators
     * 
     * @param array<string, mixed> $update
     */
    private function hasUpdateOperators(array $update): bool
    {
        foreach (array_keys($update) as $key) {
            if (str_starts_with($key, '$')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convert MongoDB document to array
     * 
     * @param object|array<string, mixed> $document
     * @return array<string, mixed>
     */
    private function documentToArray(object|array $document): array
    {
        $array = [];
        
        foreach ($document as $key => $value) {
            if ($value instanceof ObjectId) {
                $array[$key] = (string) $value;
                // Also set 'id' for convenience when _id is ObjectId
                if ($key === '_id') {
                    $array['id'] = (string) $value;
                }
            } elseif ($value instanceof \MongoDB\BSON\UTCDateTime) {
                $array[$key] = $value->toDateTime()->format('Y-m-d H:i:s');
            } elseif ($value instanceof \MongoDB\Model\BSONArray || $value instanceof \MongoDB\Model\BSONDocument) {
                $array[$key] = $this->documentToArray($value);
            } elseif (is_object($value)) {
                $array[$key] = (array) $value;
            } else {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }

    /**
     * Create ObjectId from string
     */
    public static function objectId(string $id): ObjectId
    {
        return new ObjectId($id);
    }

    /**
     * Check if string is valid ObjectId
     */
    public static function isValidObjectId(string $id): bool
    {
        try {
            new ObjectId($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Enable query logging
     */
    public function enableLogging(): void
    {
        $this->loggingEnabled = true;
    }

    /**
     * Disable query logging
     */
    public function disableLogging(): void
    {
        $this->loggingEnabled = false;
    }

    /**
     * Log a query
     */
    private function logQuery(string $operation, string $collection, float $time): void
    {
        $this->queryLog[] = [
            'operation' => $operation,
            'collection' => $collection,
            'time' => round($time * 1000, 2), // ms
        ];
    }

    /**
     * Get query log
     * 
     * @return array<array{operation: string, collection: string, time: float}>
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Clear query log
     */
    public function clearQueryLog(): void
    {
        $this->queryLog = [];
    }

    /**
     * Disconnect from MongoDB
     */
    public function disconnect(): void
    {
        $this->client = null;
        $this->database = null;
    }
}
