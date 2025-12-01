<?php

declare(strict_types=1);

namespace Core;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use Core\Exceptions\DatabaseException;

/**
 * MongoDB Query Builder
 * 
 * Provides a fluent interface for building MongoDB queries.
 */
class MongoQueryBuilder
{
    private MongoDB $mongo;
    private string $collection = '';
    
    /** @var array<string, int> */
    private array $projection = [];
    
    /** @var array<string, mixed> */
    private array $filter = [];
    
    /** @var array<string, int> */
    private array $sort = [];
    
    private ?int $limitValue = null;
    private ?int $skipValue = null;

    public function __construct(MongoDB $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Set the collection for the query
     */
    public function collection(string $collection): self
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Alias for collection
     */
    public function table(string $collection): self
    {
        return $this->collection($collection);
    }

    /**
     * Set columns/fields to select (projection)
     * 
     * @param string|array<string> $fields
     */
    public function select(string|array $fields = []): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();
        
        foreach ($fields as $field) {
            if ($field !== '*') {
                $this->projection[$field] = 1;
            }
        }
        
        return $this;
    }

    /**
     * Add a where clause
     */
    public function where(string $field, mixed $operator = null, mixed $value = null): self
    {
        // Handle where('field', 'value') syntax
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->addCondition($field, $operator, $value);
        
        return $this;
    }

    /**
     * Add condition to filter
     */
    private function addCondition(string $field, string $operator, mixed $value): void
    {
        // Handle _id field with ObjectId
        if ($field === 'id' || $field === '_id') {
            $field = '_id';
            if (is_string($value) && MongoDB::isValidObjectId($value)) {
                $value = MongoDB::objectId($value);
            }
        }
        
        $mongoOperator = match($operator) {
            '=' => null, // Direct equality
            '!=' => '$ne',
            '<>' => '$ne',
            '>' => '$gt',
            '>=' => '$gte',
            '<' => '$lt',
            '<=' => '$lte',
            'LIKE' => '$regex',
            default => null,
        };
        
        if ($operator === 'LIKE') {
            // Convert SQL LIKE to MongoDB regex
            $pattern = str_replace(['%', '_'], ['.*', '.'], preg_quote($value, '/'));
            $value = new Regex($pattern, 'i');
            $this->filter[$field] = ['$regex' => $pattern, '$options' => 'i'];
        } elseif ($mongoOperator === null) {
            $this->filter[$field] = $value;
        } else {
            if (!isset($this->filter[$field])) {
                $this->filter[$field] = [];
            }
            if (is_array($this->filter[$field])) {
                $this->filter[$field][$mongoOperator] = $value;
            } else {
                $this->filter[$field] = [$mongoOperator => $value];
            }
        }
    }

    /**
     * Add where IN clause
     * 
     * @param array<mixed> $values
     */
    public function whereIn(string $field, array $values): self
    {
        if ($field === 'id' || $field === '_id') {
            $field = '_id';
            $values = array_map(function($v) {
                return is_string($v) && MongoDB::isValidObjectId($v) ? MongoDB::objectId($v) : $v;
            }, $values);
        }
        
        $this->filter[$field] = ['$in' => $values];
        return $this;
    }

    /**
     * Add where NOT IN clause
     * 
     * @param array<mixed> $values
     */
    public function whereNotIn(string $field, array $values): self
    {
        if ($field === 'id' || $field === '_id') {
            $field = '_id';
            $values = array_map(function($v) {
                return is_string($v) && MongoDB::isValidObjectId($v) ? MongoDB::objectId($v) : $v;
            }, $values);
        }
        
        $this->filter[$field] = ['$nin' => $values];
        return $this;
    }

    /**
     * Add where NULL clause
     */
    public function whereNull(string $field): self
    {
        $this->filter[$field] = null;
        return $this;
    }

    /**
     * Add where NOT NULL clause
     */
    public function whereNotNull(string $field): self
    {
        $this->filter[$field] = ['$ne' => null];
        return $this;
    }

    /**
     * Add where BETWEEN clause
     */
    public function whereBetween(string $field, mixed $min, mixed $max): self
    {
        $this->filter[$field] = ['$gte' => $min, '$lte' => $max];
        return $this;
    }

    /**
     * Add LIKE search
     */
    public function whereLike(string $field, string $value): self
    {
        return $this->where($field, 'LIKE', $value);
    }

    /**
     * Add raw filter conditions
     * 
     * @param array<string, mixed> $conditions
     */
    public function whereRaw(array $conditions): self
    {
        $this->filter = array_merge($this->filter, $conditions);
        return $this;
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->sort[$field] = strtoupper($direction) === 'DESC' ? -1 : 1;
        return $this;
    }

    /**
     * Order by descending
     */
    public function orderByDesc(string $field): self
    {
        return $this->orderBy($field, 'DESC');
    }

    /**
     * Set LIMIT
     */
    public function limit(int $limit): self
    {
        $this->limitValue = $limit;
        return $this;
    }

    /**
     * Set OFFSET/SKIP
     */
    public function offset(int $offset): self
    {
        $this->skipValue = $offset;
        return $this;
    }

    /**
     * Alias for offset
     */
    public function skip(int $skip): self
    {
        return $this->offset($skip);
    }

    /**
     * Alias for limit
     */
    public function take(int $limit): self
    {
        return $this->limit($limit);
    }

    /**
     * Set pagination
     */
    public function forPage(int $page, int $perPage = 15): self
    {
        return $this->limit($perPage)->offset(($page - 1) * $perPage);
    }

    /**
     * Execute query and get all results
     * 
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        $options = [];
        
        if (!empty($this->projection)) {
            $options['projection'] = $this->projection;
        }
        
        if (!empty($this->sort)) {
            $options['sort'] = $this->sort;
        }
        
        if ($this->limitValue !== null) {
            $options['limit'] = $this->limitValue;
        }
        
        if ($this->skipValue !== null) {
            $options['skip'] = $this->skipValue;
        }
        
        return $this->mongo->find($this->collection, $this->filter, $options);
    }

    /**
     * Execute query and get first result
     * 
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $options = [];
        
        if (!empty($this->projection)) {
            $options['projection'] = $this->projection;
        }
        
        if (!empty($this->sort)) {
            $options['sort'] = $this->sort;
        }
        
        return $this->mongo->findOne($this->collection, $this->filter, $options);
    }

    /**
     * Find by ID
     * 
     * @return array<string, mixed>|null
     */
    public function find(int|string $id, string $primaryKey = '_id'): ?array
    {
        if ($primaryKey === 'id') {
            $primaryKey = '_id';
        }
        
        if ($primaryKey === '_id' && is_string($id) && MongoDB::isValidObjectId($id)) {
            $id = MongoDB::objectId($id);
        }
        
        return $this->where($primaryKey, $id)->first();
    }

    /**
     * Count documents
     */
    public function count(): int
    {
        return $this->mongo->count($this->collection, $this->filter);
    }

    /**
     * Check if any documents exist
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Get sum of a field using aggregation
     */
    public function sum(string $field): float
    {
        $pipeline = [];
        
        if (!empty($this->filter)) {
            $pipeline[] = ['$match' => $this->filter];
        }
        
        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'total' => ['$sum' => '$' . $field]
            ]
        ];
        
        $result = $this->mongo->aggregate($this->collection, $pipeline);
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Get average of a field
     */
    public function avg(string $field): float
    {
        $pipeline = [];
        
        if (!empty($this->filter)) {
            $pipeline[] = ['$match' => $this->filter];
        }
        
        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'average' => ['$avg' => '$' . $field]
            ]
        ];
        
        $result = $this->mongo->aggregate($this->collection, $pipeline);
        return (float) ($result[0]['average'] ?? 0);
    }

    /**
     * Get max of a field
     */
    public function max(string $field): mixed
    {
        $pipeline = [];
        
        if (!empty($this->filter)) {
            $pipeline[] = ['$match' => $this->filter];
        }
        
        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'max' => ['$max' => '$' . $field]
            ]
        ];
        
        $result = $this->mongo->aggregate($this->collection, $pipeline);
        return $result[0]['max'] ?? null;
    }

    /**
     * Get min of a field
     */
    public function min(string $field): mixed
    {
        $pipeline = [];
        
        if (!empty($this->filter)) {
            $pipeline[] = ['$match' => $this->filter];
        }
        
        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'min' => ['$min' => '$' . $field]
            ]
        ];
        
        $result = $this->mongo->aggregate($this->collection, $pipeline);
        return $result[0]['min'] ?? null;
    }

    /**
     * Get paginated results
     * 
     * @return array{data: array, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $total = $this->count();
        $data = $this->forPage($page, $perPage)->get();
        $lastPage = (int) ceil($total / $perPage);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Insert a document
     * 
     * @param array<string, mixed> $data
     */
    public function insert(array $data): string
    {
        return $this->mongo->insertOne($this->collection, $data);
    }

    /**
     * Insert multiple documents
     * 
     * @param array<int, array<string, mixed>> $documents
     * @return array<string>
     */
    public function insertBatch(array $documents): array
    {
        return $this->mongo->insertMany($this->collection, $documents);
    }

    /**
     * Update documents matching filter
     * 
     * @param array<string, mixed> $data
     */
    public function update(array $data): int
    {
        return $this->mongo->updateMany($this->collection, $this->filter, $data);
    }

    /**
     * Delete documents matching filter
     */
    public function delete(): int
    {
        return $this->mongo->deleteMany($this->collection, $this->filter);
    }

    /**
     * Increment a field
     */
    public function increment(string $field, int $amount = 1): int
    {
        return $this->mongo->updateMany(
            $this->collection,
            $this->filter,
            ['$inc' => [$field => $amount]]
        );
    }

    /**
     * Decrement a field
     */
    public function decrement(string $field, int $amount = 1): int
    {
        return $this->increment($field, -$amount);
    }

    /**
     * Text search (requires text index)
     * 
     * @param array<string> $fields Not used for MongoDB - requires text index setup
     */
    public function search(array $fields, string $term): self
    {
        // Build $or condition for regex search across multiple fields
        $orConditions = [];
        foreach ($fields as $field) {
            $orConditions[] = [$field => ['$regex' => preg_quote($term, '/'), '$options' => 'i']];
        }
        
        if (!empty($orConditions)) {
            $this->filter['$or'] = $orConditions;
        }
        
        return $this;
    }

    /**
     * Reset the query builder
     */
    public function reset(): self
    {
        $this->projection = [];
        $this->filter = [];
        $this->sort = [];
        $this->limitValue = null;
        $this->skipValue = null;
        
        return $this;
    }

    /**
     * Clone the query builder
     */
    public function __clone(): void
    {
        $this->projection = $this->projection;
        $this->filter = $this->filter;
        $this->sort = $this->sort;
    }

    /**
     * Get the current filter
     * 
     * @return array<string, mixed>
     */
    public function getFilter(): array
    {
        return $this->filter;
    }
}
