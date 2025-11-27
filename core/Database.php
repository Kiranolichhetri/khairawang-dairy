<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use PDOStatement;
use Core\Exceptions\DatabaseException;

/**
 * Database Connection Manager
 * 
 * PDO wrapper with prepared statements only, transaction support, and query logging.
 */
class Database
{
    private ?PDO $pdo = null;
    
    /** @var array<string, mixed> */
    private array $config;
    
    private bool $inTransaction = false;
    
    /** @var array<array{query: string, bindings: array, time: float}> */
    private array $queryLog = [];
    
    private bool $loggingEnabled = false;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => '',
            'username' => '',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [],
        ], $config);
    }

    /**
     * Get the PDO connection
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        
        return $this->pdo;
    }

    /**
     * Connect to the database
     */
    private function connect(): void
    {
        $driver = $this->config['driver'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];
        
        $dsn = match($driver) {
            'mysql' => "mysql:host={$host};port={$port};dbname={$database};charset={$charset}",
            'pgsql' => "pgsql:host={$host};port={$port};dbname={$database}",
            'sqlite' => "sqlite:{$database}",
            default => throw new DatabaseException("Unsupported database driver: {$driver}"),
        };
        
        $options = array_merge([
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ], $this->config['options']);
        
        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
            
            // Set MySQL specific options
            if ($driver === 'mysql') {
                $collation = $this->config['collation'];
                $this->pdo->exec("SET NAMES '{$charset}' COLLATE '{$collation}'");
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Execute a raw SQL query with prepared statements
     * 
     * @param array<int|string, mixed> $bindings
     */
    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $startTime = microtime(true);
        
        try {
            $statement = $this->getConnection()->prepare($sql);
            $statement->execute($bindings);
            
            if ($this->loggingEnabled) {
                $this->logQuery($sql, $bindings, microtime(true) - $startTime);
            }
            
            return $statement;
        } catch (PDOException $e) {
            throw new DatabaseException(
                "Query failed: " . $e->getMessage() . " [SQL: {$sql}]",
                0,
                $e
            );
        }
    }

    /**
     * Execute a SELECT query and return all results
     * 
     * @param array<int|string, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    /**
     * Execute a SELECT query and return first row
     * 
     * @param array<int|string, mixed> $bindings
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $result = $this->query($sql, $bindings)->fetch();
        return $result === false ? null : $result;
    }

    /**
     * Execute a SELECT query and return single value
     * 
     * @param array<int|string, mixed> $bindings
     */
    public function selectValue(string $sql, array $bindings = []): mixed
    {
        return $this->query($sql, $bindings)->fetchColumn();
    }

    /**
     * Execute an INSERT query
     * 
     * @param array<string, mixed> $data
     */
    public function insert(string $table, array $data): int
    {
        $table = $this->prefixTable($table);
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', array_map(fn($col) => "`{$col}`", $columns)),
            implode(', ', $placeholders)
        );
        
        $this->query($sql, array_values($data));
        
        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Execute a batch INSERT query
     * 
     * @param array<int, array<string, mixed>> $rows
     */
    public function insertBatch(string $table, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }
        
        $table = $this->prefixTable($table);
        $columns = array_keys($rows[0]);
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $table,
            implode(', ', array_map(fn($col) => "`{$col}`", $columns)),
            implode(', ', array_fill(0, count($rows), $placeholders))
        );
        
        $bindings = [];
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $bindings[] = $row[$column] ?? null;
            }
        }
        
        $statement = $this->query($sql, $bindings);
        return $statement->rowCount();
    }

    /**
     * Execute an UPDATE query
     * 
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    public function update(string $table, array $data, array $where): int
    {
        $table = $this->prefixTable($table);
        
        $setClauses = [];
        $bindings = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "`{$column}` = ?";
            $bindings[] = $value;
        }
        
        $whereClauses = [];
        foreach ($where as $column => $value) {
            $whereClauses[] = "`{$column}` = ?";
            $bindings[] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setClauses),
            implode(' AND ', $whereClauses)
        );
        
        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * Execute a DELETE query
     * 
     * @param array<string, mixed> $where
     */
    public function delete(string $table, array $where): int
    {
        $table = $this->prefixTable($table);
        
        $whereClauses = [];
        $bindings = [];
        
        foreach ($where as $column => $value) {
            $whereClauses[] = "`{$column}` = ?";
            $bindings[] = $value;
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereClauses)
        );
        
        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction) {
            return false;
        }
        
        $this->inTransaction = $this->getConnection()->beginTransaction();
        return $this->inTransaction;
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->commit();
        $this->inTransaction = false;
        return $result;
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        
        $result = $this->getConnection()->rollBack();
        $this->inTransaction = false;
        return $result;
    }

    /**
     * Execute a callback within a transaction
     * 
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * Get table prefix
     */
    public function getPrefix(): string
    {
        return $this->config['prefix'];
    }

    /**
     * Prefix a table name
     */
    public function prefixTable(string $table): string
    {
        $prefix = $this->config['prefix'];
        
        if ($prefix && !str_starts_with($table, $prefix)) {
            return $prefix . $table;
        }
        
        return $table;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): int
    {
        return (int) $this->getConnection()->lastInsertId();
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
     * 
     * @param array<mixed> $bindings
     */
    private function logQuery(string $sql, array $bindings, float $time): void
    {
        $this->queryLog[] = [
            'query' => $sql,
            'bindings' => $bindings,
            'time' => round($time * 1000, 2), // ms
        ];
    }

    /**
     * Get query log
     * 
     * @return array<array{query: string, bindings: array, time: float}>
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
     * Create a new QueryBuilder instance
     */
    public function table(string $table): QueryBuilder
    {
        return (new QueryBuilder($this))->table($table);
    }

    /**
     * Get raw expression
     */
    public static function raw(string $expression): object
    {
        return new class($expression) {
            public function __construct(public readonly string $value) {}
            public function __toString(): string { return $this->value; }
        };
    }

    /**
     * Disconnect from database
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Reconnect to database
     */
    public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }
}
