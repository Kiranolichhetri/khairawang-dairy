<?php

declare(strict_types=1);

namespace Core;

use Core\Exceptions\ModelException;
use MongoDB\BSON\ObjectId;

/**
 * Base Model Class (Active Record Pattern)
 * 
 * Provides CRUD operations, relationships, soft deletes, and timestamps.
 * Supports both MySQL (via PDO) and MongoDB backends.
 */
abstract class Model
{
    /** @var string Table/Collection name */
    protected static string $table = '';
    
    /** @var string Primary key column */
    protected static string $primaryKey = 'id';
    
    /** @var bool Enable timestamps */
    protected static bool $timestamps = true;
    
    /** @var bool Enable soft deletes */
    protected static bool $softDeletes = false;
    
    /** @var string Soft delete column */
    protected static string $deletedAtColumn = 'deleted_at';
    
    /** @var string Created at column */
    protected static string $createdAtColumn = 'created_at';
    
    /** @var string Updated at column */
    protected static string $updatedAtColumn = 'updated_at';
    
    /** @var array<string> Fillable attributes */
    protected static array $fillable = [];
    
    /** @var array<string> Guarded attributes */
    protected static array $guarded = ['id'];
    
    /** @var array<string> Hidden attributes (excluded from toArray) */
    protected static array $hidden = [];
    
    /** @var array<string, string> Attribute casting */
    protected static array $casts = [];
    
    /** @var array<string, mixed> Model attributes */
    protected array $attributes = [];
    
    /** @var array<string, mixed> Original attributes (before changes) */
    protected array $original = [];
    
    /** @var bool Whether model exists in database */
    protected bool $exists = false;
    
    /** @var Database|null Database instance */
    protected static ?Database $db = null;
    
    /** @var MongoDB|null MongoDB instance */
    protected static ?MongoDB $mongo = null;

    /**
     * Create a new model instance
     * 
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Set the database connection
     */
    public static function setDatabase(Database $db): void
    {
        self::$db = $db;
    }

    /**
     * Set the MongoDB connection
     */
    public static function setMongoDB(MongoDB $mongo): void
    {
        self::$mongo = $mongo;
    }

    /**
     * Check if MongoDB is the default connection
     */
    protected static function isMongoDb(): bool
    {
        $app = Application::getInstance();
        return $app?->isMongoDbDefault() ?? false;
    }

    /**
     * Get the database connection
     */
    protected static function db(): Database
    {
        if (self::$db === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                self::$db = $app->db();
            } else {
                throw new ModelException('Database connection not set');
            }
        }
        
        return self::$db;
    }

    /**
     * Get the MongoDB connection
     */
    protected static function mongo(): MongoDB
    {
        if (self::$mongo === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                self::$mongo = $app->mongo();
            } else {
                throw new ModelException('MongoDB connection not set');
            }
        }
        
        return self::$mongo;
    }

    /**
     * Get table name
     */
    public static function getTable(): string
    {
        if (static::$table === '') {
            // Convert class name to snake_case plural
            $class = (new \ReflectionClass(static::class))->getShortName();
            static::$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
        }
        
        return static::$table;
    }

    /**
     * Get primary key name
     */
    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    /**
     * Create a new query builder for the model
     * 
     * @return QueryBuilder|MongoQueryBuilder
     */
    public static function query(): QueryBuilder|MongoQueryBuilder
    {
        if (static::isMongoDb()) {
            $query = (new MongoQueryBuilder(static::mongo()))->collection(static::getTable());
            
            // Apply soft delete scope by default
            if (static::$softDeletes) {
                $query->whereNull(static::$deletedAtColumn);
            }
            
            return $query;
        }
        
        $query = self::db()->table(static::getTable());
        
        // Apply soft delete scope by default
        if (static::$softDeletes) {
            $query->whereNull(static::$deletedAtColumn);
        }
        
        return $query;
    }

    /**
     * Find a model by primary key
     */
    public static function find(int|string $id): ?static
    {
        if (static::isMongoDb()) {
            $filter = [];
            
            // Handle MongoDB ObjectId or numeric id
            if (MongoDB::isValidObjectId((string) $id)) {
                $filter['_id'] = MongoDB::objectId((string) $id);
            } else {
                // For backwards compatibility with numeric IDs
                $filter['id'] = is_numeric($id) ? (int) $id : $id;
            }
            
            if (static::$softDeletes) {
                // MongoDB's {deleted_at: null} only matches documents where the field
                // EXISTS and equals null. Use $or to also match documents where the
                // field is missing entirely (which is the case for non-deleted records).
                $filter['$or'] = [
                    [static::$deletedAtColumn => null],
                    [static::$deletedAtColumn => ['$exists' => false]],
                ];
            }
            
            $data = static::mongo()->findOne(static::getTable(), $filter);
        } else {
            $data = static::query()->find($id, static::$primaryKey);
        }
        
        if ($data === null) {
            return null;
        }
        
        return static::hydrate($data);
    }

    /**
     * Find a model by primary key or throw exception
     */
    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);
        
        if ($model === null) {
            throw new ModelException('Model not found: ' . static::class . ' with ID ' . $id);
        }
        
        return $model;
    }

    /**
     * Get all models
     * 
     * @return array<static>
     */
    public static function all(): array
    {
        $rows = static::query()->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get first model matching query
     */
    public static function first(): ?static
    {
        $data = static::query()->first();
        
        if ($data === null) {
            return null;
        }
        
        return static::hydrate($data);
    }

    /**
     * Create a new model and save it
     * 
     * @param array<string, mixed> $attributes
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Update or create a model
     * 
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     */
    public static function updateOrCreate(array $attributes, array $values = []): static
    {
        $query = static::query();
        
        foreach ($attributes as $column => $value) {
            $query->where($column, $value);
        }
        
        $data = $query->first();
        
        if ($data !== null) {
            $model = static::hydrate($data);
            $model->fill($values);
            $model->save();
            return $model;
        }
        
        return static::create(array_merge($attributes, $values));
    }

    /**
     * Find by a specific column
     */
    public static function findBy(string $column, mixed $value): ?static
    {
        $data = static::query()->where($column, $value)->first();
        
        if ($data === null) {
            return null;
        }
        
        return static::hydrate($data);
    }

    /**
     * Find all by a specific column
     * 
     * @return array<static>
     */
    public static function findAllBy(string $column, mixed $value): array
    {
        $rows = static::query()->where($column, $value)->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get count of models
     */
    public static function count(): int
    {
        return static::query()->count();
    }

    /**
     * Where clause shortcut
     * 
     * @return QueryBuilder|MongoQueryBuilder
     */
    public static function where(string $column, mixed $operator = null, mixed $value = null): QueryBuilder|MongoQueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Order by clause shortcut
     * 
     * @return QueryBuilder|MongoQueryBuilder
     */
    public static function orderBy(string $column, string $direction = 'ASC'): QueryBuilder|MongoQueryBuilder
    {
        return static::query()->orderBy($column, $direction);
    }

    /**
     * Include soft deleted models
     * 
     * @return QueryBuilder|MongoQueryBuilder
     */
    public static function withTrashed(): QueryBuilder|MongoQueryBuilder
    {
        if (static::isMongoDb()) {
            return (new MongoQueryBuilder(static::mongo()))->collection(static::getTable());
        }
        return self::db()->table(static::getTable());
    }

    /**
     * Only soft deleted models
     * 
     * @return QueryBuilder|MongoQueryBuilder
     */
    public static function onlyTrashed(): QueryBuilder|MongoQueryBuilder
    {
        if (static::isMongoDb()) {
            return (new MongoQueryBuilder(static::mongo()))
                ->collection(static::getTable())
                ->whereNotNull(static::$deletedAtColumn);
        }
        return self::db()->table(static::getTable())
            ->whereNotNull(static::$deletedAtColumn);
    }

    /**
     * Hydrate a model from database row
     * 
     * @param array<string, mixed> $data
     */
    protected static function hydrate(array $data): static
    {
        $model = new static();
        $model->attributes = $data;
        $model->original = $data;
        $model->exists = true;
        $model->castAttributes();
        
        return $model;
    }

    /**
     * Fill model attributes
     * 
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }

    /**
     * Check if attribute is fillable
     */
    protected function isFillable(string $key): bool
    {
        // If fillable is defined, key must be in it
        if (!empty(static::$fillable)) {
            return in_array($key, static::$fillable, true);
        }
        
        // Otherwise, check if not guarded
        return !in_array($key, static::$guarded, true);
    }

    /**
     * Set an attribute
     */
    public function setAttribute(string $key, mixed $value): self
    {
        // Check for mutator method
        $method = 'set' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        
        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        }
        
        $this->attributes[$key] = $value;
        
        return $this;
    }

    /**
     * Get an attribute
     */
    public function getAttribute(string $key): mixed
    {
        // Check for accessor method
        $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        
        if (method_exists($this, $method)) {
            return $this->$method($this->attributes[$key] ?? null);
        }
        
        return $this->attributes[$key] ?? null;
    }

    /**
     * Cast attributes to their proper types
     */
    protected function castAttributes(): void
    {
        foreach (static::$casts as $key => $type) {
            if (isset($this->attributes[$key])) {
                $this->attributes[$key] = $this->castAttribute($key, $this->attributes[$key], $type);
            }
        }
    }

    /**
     * Cast a single attribute
     */
    protected function castAttribute(string $key, mixed $value, string $type): mixed
    {
        return match($type) {
            'int', 'integer' => (int) $value,
            'float', 'double', 'decimal' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : []),
            'datetime' => $value instanceof \DateTimeInterface ? $value : new \DateTime($value),
            'date' => $value instanceof \DateTimeInterface ? $value : new \DateTime($value),
            default => $value,
        };
    }

    /**
     * Save the model to database
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }
        
        return $this->performInsert();
    }

    /**
     * Update model with array of data
     * 
     * @param array<string, mixed> $data
     */
    public function update(array $data): bool
    {
        $this->fill($data);
        return $this->save();
    }

    /**
     * Perform insert operation
     */
    protected function performInsert(): bool
    {
        $now = date('Y-m-d H:i:s');
        
        // Add timestamps
        if (static::$timestamps) {
            $this->attributes[static::$createdAtColumn] = $now;
            $this->attributes[static::$updatedAtColumn] = $now;
        }
        
        // Prepare data
        $data = $this->prepareForDatabase();
        
        if (static::isMongoDb()) {
            $id = static::mongo()->insertOne(static::getTable(), $data);
            $this->attributes['_id'] = $id;
            $this->attributes['id'] = $id;
        } else {
            $id = self::db()->insert(static::getTable(), $data);
            $this->attributes[static::$primaryKey] = $id;
        }
        
        $this->original = $this->attributes;
        $this->exists = true;
        
        return true;
    }

    /**
     * Perform update operation
     */
    protected function performUpdate(): bool
    {
        // Check if anything changed
        if (empty($this->getDirty())) {
            return true;
        }
        
        // Add updated timestamp
        if (static::$timestamps) {
            $this->attributes[static::$updatedAtColumn] = date('Y-m-d H:i:s');
        }
        
        // Prepare data
        $data = $this->prepareForDatabase();
        
        // Get only changed values
        $dirty = [];
        foreach ($this->getDirty() as $key) {
            if (isset($data[$key])) {
                $dirty[$key] = $data[$key];
            }
        }
        
        // Add updated_at if timestamps enabled
        if (static::$timestamps && isset($data[static::$updatedAtColumn])) {
            $dirty[static::$updatedAtColumn] = $data[static::$updatedAtColumn];
        }
        
        if (empty($dirty)) {
            return true;
        }
        
        if (static::isMongoDb()) {
            $filter = $this->getMongoIdFilter();
            static::mongo()->updateOne(static::getTable(), $filter, $dirty);
        } else {
            self::db()->update(
                static::getTable(),
                $dirty,
                [static::$primaryKey => $this->getKey()]
            );
        }
        
        $this->original = $this->attributes;
        
        return true;
    }

    /**
     * Get MongoDB filter for current document
     * 
     * @return array<string, mixed>
     */
    protected function getMongoIdFilter(): array
    {
        if (isset($this->attributes['_id'])) {
            $id = $this->attributes['_id'];
            if (is_string($id) && MongoDB::isValidObjectId($id)) {
                return ['_id' => MongoDB::objectId($id)];
            }
            return ['_id' => $id];
        }
        
        // Fallback to numeric id
        if (isset($this->attributes['id'])) {
            return ['id' => $this->attributes['id']];
        }
        
        throw new ModelException('Cannot determine document ID for update');
    }

    /**
     * Prepare attributes for database storage
     * 
     * @return array<string, mixed>
     */
    protected function prepareForDatabase(): array
    {
        $data = [];
        
        foreach ($this->attributes as $key => $value) {
            // Skip primary key for inserts (let DB auto-increment)
            if (!$this->exists && $key === static::$primaryKey && $value === null) {
                continue;
            }
            
            // Skip _id for MongoDB inserts
            if (!$this->exists && $key === '_id') {
                continue;
            }
            
            // Convert arrays to JSON for MySQL
            if (!static::isMongoDb() && is_array($value)) {
                $value = json_encode($value);
            }
            
            // Convert DateTimeInterface to string
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
            
            // Convert booleans for MySQL
            if (!static::isMongoDb() && is_bool($value)) {
                $value = $value ? 1 : 0;
            }
            
            $data[$key] = $value;
        }
        
        return $data;
    }

    /**
     * Get changed attributes
     * 
     * @return array<string>
     */
    public function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[] = $key;
            }
        }
        
        return $dirty;
    }

    /**
     * Check if model has been modified
     */
    public function isDirty(string $key = null): bool
    {
        if ($key !== null) {
            return in_array($key, $this->getDirty(), true);
        }
        
        return !empty($this->getDirty());
    }

    /**
     * Delete the model
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        // Soft delete
        if (static::$softDeletes) {
            $this->attributes[static::$deletedAtColumn] = date('Y-m-d H:i:s');
            return $this->save();
        }
        
        // Hard delete
        if (static::isMongoDb()) {
            $filter = $this->getMongoIdFilter();
            static::mongo()->deleteOne(static::getTable(), $filter);
        } else {
            self::db()->delete(static::getTable(), [static::$primaryKey => $this->getKey()]);
        }
        
        $this->exists = false;
        
        return true;
    }

    /**
     * Permanently delete (force delete for soft deletes)
     */
    public function forceDelete(): bool
    {
        if (static::isMongoDb()) {
            $filter = $this->getMongoIdFilter();
            static::mongo()->deleteOne(static::getTable(), $filter);
        } else {
            self::db()->delete(static::getTable(), [static::$primaryKey => $this->getKey()]);
        }
        
        $this->exists = false;
        
        return true;
    }

    /**
     * Restore a soft deleted model
     */
    public function restore(): bool
    {
        if (!static::$softDeletes) {
            return false;
        }
        
        $this->attributes[static::$deletedAtColumn] = null;
        
        if (static::isMongoDb()) {
            $filter = $this->getMongoIdFilter();
            static::mongo()->updateOne(
                static::getTable(),
                $filter,
                [static::$deletedAtColumn => null]
            );
        } else {
            self::db()->update(
                static::getTable(),
                [static::$deletedAtColumn => null],
                [static::$primaryKey => $this->getKey()]
            );
        }
        
        return true;
    }

    /**
     * Check if model is soft deleted
     */
    public function trashed(): bool
    {
        return static::$softDeletes && $this->attributes[static::$deletedAtColumn] !== null;
    }

    /**
     * Get the primary key value
     */
    public function getKey(): int|string|null
    {
        // For MongoDB, prefer _id
        if (static::isMongoDb()) {
            return $this->attributes['_id'] ?? $this->attributes['id'] ?? null;
        }
        return $this->attributes[static::$primaryKey] ?? null;
    }

    /**
     * Refresh the model from database
     */
    public function refresh(): self
    {
        if (!$this->exists) {
            return $this;
        }
        
        $key = $this->getKey();
        if ($key === null) {
            return $this;
        }
        
        $fresh = static::find($key);
        
        if ($fresh !== null) {
            $this->attributes = $fresh->attributes;
            $this->original = $fresh->original;
        }
        
        return $this;
    }

    /**
     * Define a hasOne relationship
     * 
     * @return array<string, mixed>|null
     */
    protected function hasOne(string $related, string $foreignKey = null, string $localKey = null): ?array
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? static::$primaryKey;
        
        $relatedTable = (new $related())->getTable();
        
        if (static::isMongoDb()) {
            return static::mongo()->findOne($relatedTable, [
                $foreignKey => $this->getAttribute($localKey)
            ]);
        }
        
        return self::db()
            ->table($relatedTable)
            ->where($foreignKey, $this->getAttribute($localKey))
            ->first();
    }

    /**
     * Define a hasMany relationship
     * 
     * @return array<int, array<string, mixed>>
     */
    protected function hasMany(string $related, string $foreignKey = null, string $localKey = null): array
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? static::$primaryKey;
        
        $relatedTable = (new $related())->getTable();
        
        if (static::isMongoDb()) {
            return static::mongo()->find($relatedTable, [
                $foreignKey => $this->getAttribute($localKey)
            ]);
        }
        
        return self::db()
            ->table($relatedTable)
            ->where($foreignKey, $this->getAttribute($localKey))
            ->get();
    }

    /**
     * Define a belongsTo relationship
     * 
     * @return array<string, mixed>|null
     */
    protected function belongsTo(string $related, string $foreignKey = null, string $ownerKey = null): ?array
    {
        // Create instance to get table information
        try {
            $reflection = new \ReflectionClass($related);
            if (!$reflection->isSubclassOf(Model::class)) {
                throw new ModelException("Related class must extend Model: {$related}");
            }
            
            // Get static properties without instantiation
            $relatedTable = $reflection->getStaticPropertyValue('table');
            if (empty($relatedTable)) {
                // Generate table name from class name
                $shortName = $reflection->getShortName();
                $relatedTable = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName)) . 's';
            }
            
            $relatedPrimaryKey = $reflection->getStaticPropertyValue('primaryKey');
        } catch (\ReflectionException $e) {
            throw new ModelException("Unable to resolve relationship: {$related}");
        }
        
        $foreignKey = $foreignKey ?? strtolower($reflection->getShortName()) . '_id';
        $ownerKey = $ownerKey ?? $relatedPrimaryKey;
        
        if (static::isMongoDb()) {
            $foreignValue = $this->getAttribute($foreignKey);
            
            // Handle ObjectId for _id lookups
            if ($ownerKey === 'id' || $ownerKey === '_id') {
                $ownerKey = '_id';
                if (is_string($foreignValue) && MongoDB::isValidObjectId($foreignValue)) {
                    $foreignValue = MongoDB::objectId($foreignValue);
                }
            }
            
            return static::mongo()->findOne($relatedTable, [$ownerKey => $foreignValue]);
        }
        
        return self::db()
            ->table($relatedTable)
            ->where($ownerKey, $this->getAttribute($foreignKey))
            ->first();
    }

    /**
     * Define a belongsToMany relationship
     * 
     * @return array<int, array<string, mixed>>
     */
    protected function belongsToMany(
        string $related,
        string $pivotTable,
        string $foreignPivotKey = null,
        string $relatedPivotKey = null
    ): array {
        $instance = new $related();
        $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? strtolower((new \ReflectionClass($related))->getShortName()) . '_id';
        
        $relatedTable = $instance->getTable();
        
        if (static::isMongoDb()) {
            // For MongoDB, we need to do a lookup manually
            $pivotDocs = static::mongo()->find($pivotTable, [
                $foreignPivotKey => $this->getKey()
            ]);
            
            $relatedIds = array_column($pivotDocs, $relatedPivotKey);
            
            if (empty($relatedIds)) {
                return [];
            }
            
            return static::mongo()->find($relatedTable, [
                '_id' => ['$in' => array_map(function($id) {
                    return is_string($id) && MongoDB::isValidObjectId($id) ? MongoDB::objectId($id) : $id;
                }, $relatedIds)]
            ]);
        }
        
        return self::db()
            ->table($relatedTable)
            ->join($pivotTable, "{$relatedTable}.id", '=', "{$pivotTable}.{$relatedPivotKey}")
            ->where("{$pivotTable}.{$foreignPivotKey}", $this->getKey())
            ->get();
    }

    /**
     * Get foreign key name for this model
     */
    protected function getForeignKey(): string
    {
        $class = (new \ReflectionClass(static::class))->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . '_id';
    }

    /**
     * Convert model to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, static::$hidden, true)) {
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $array[$key] = $value;
            }
        }
        
        return $array;
    }

    /**
     * Convert model to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Magic getter for attributes
     */
    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    /**
     * Magic setter for attributes
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Magic isset for attributes
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Magic unset for attributes
     */
    public function __unset(string $name): void
    {
        unset($this->attributes[$name]);
    }

    /**
     * JSON serialize
     * 
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
