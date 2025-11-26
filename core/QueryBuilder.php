<?php

declare(strict_types=1);

namespace Core;

use Core\Exceptions\DatabaseException;

/**
 * Fluent Query Builder
 * 
 * Provides a fluent interface for building SQL queries with support for
 * selects, inserts, updates, deletes, joins, where clauses, and pagination.
 */
class QueryBuilder
{
    private Database $db;
    private string $table = '';
    
    /** @var array<string> */
    private array $columns = ['*'];
    
    /** @var array<array{type: string, table: string, first: string, operator: string, second: string}> */
    private array $joins = [];
    
    /** @var array<array{type: string, column: string, operator: string, value: mixed, boolean: string}> */
    private array $wheres = [];
    
    /** @var array<int|string, mixed> */
    private array $bindings = [];
    
    /** @var array<string> */
    private array $orderBy = [];
    
    /** @var array<string> */
    private array $groupBy = [];
    
    /** @var array<array{column: string, operator: string, value: mixed, boolean: string}> */
    private array $havings = [];
    
    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    private bool $distinct = false;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Set the table for the query
     */
    public function table(string $table): self
    {
        $this->table = $this->db->prefixTable($table);
        return $this;
    }

    /**
     * Set columns to select
     * 
     * @param string|array<string> $columns
     */
    public function select(string|array $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add columns to select
     * 
     * @param string|array<string> $columns
     */
    public function addSelect(string|array $columns): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        if ($this->columns === ['*']) {
            $this->columns = $columns;
        } else {
            $this->columns = array_merge($this->columns, $columns);
        }
        
        return $this;
    }

    /**
     * Set distinct flag
     */
    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add a where clause
     */
    public function where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): self
    {
        // Handle where('column', 'value') syntax
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean,
        ];
        
        $this->bindings[] = $value;
        
        return $this;
    }

    /**
     * Add an OR where clause
     */
    public function orWhere(string $column, mixed $operator = null, mixed $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a where IN clause
     * 
     * @param array<mixed> $values
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        $type = $not ? 'not_in' : 'in';
        
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'operator' => '',
            'value' => $values,
            'boolean' => $boolean,
        ];
        
        foreach ($values as $val) {
            $this->bindings[] = $val;
        }
        
        return $this;
    }

    /**
     * Add a where NOT IN clause
     * 
     * @param array<mixed> $values
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add a where NULL clause
     */
    public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self
    {
        $this->wheres[] = [
            'type' => $not ? 'not_null' : 'null',
            'column' => $column,
            'operator' => '',
            'value' => null,
            'boolean' => $boolean,
        ];
        
        return $this;
    }

    /**
     * Add a where NOT NULL clause
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add a where BETWEEN clause
     */
    public function whereBetween(string $column, mixed $min, mixed $max, string $boolean = 'AND', bool $not = false): self
    {
        $this->wheres[] = [
            'type' => $not ? 'not_between' : 'between',
            'column' => $column,
            'operator' => '',
            'value' => [$min, $max],
            'boolean' => $boolean,
        ];
        
        $this->bindings[] = $min;
        $this->bindings[] = $max;
        
        return $this;
    }

    /**
     * Add a where LIKE clause
     */
    public function whereLike(string $column, string $value, string $boolean = 'AND'): self
    {
        return $this->where($column, 'LIKE', $value, $boolean);
    }

    /**
     * Add a raw where clause
     */
    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'raw',
            'column' => $sql,
            'operator' => '',
            'value' => null,
            'boolean' => $boolean,
        ];
        
        foreach ($bindings as $binding) {
            $this->bindings[] = $binding;
        }
        
        return $this;
    }

    /**
     * Add an INNER JOIN
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('INNER', $table, $first, $operator, $second);
    }

    /**
     * Add a LEFT JOIN
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('LEFT', $table, $first, $operator, $second);
    }

    /**
     * Add a RIGHT JOIN
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('RIGHT', $table, $first, $operator, $second);
    }

    /**
     * Add a join clause
     */
    private function addJoin(string $type, string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $this->db->prefixTable($table),
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];
        
        return $this;
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }
        
        $this->orderBy[] = "`{$column}` {$direction}";
        
        return $this;
    }

    /**
     * Order by descending
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * Add GROUP BY clause
     * 
     * @param string|array<string> $columns
     */
    public function groupBy(string|array $columns): self
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $this->groupBy = array_merge($this->groupBy, $columns);
        return $this;
    }

    /**
     * Add HAVING clause
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $this->havings[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean,
        ];
        
        $this->bindings[] = $value;
        
        return $this;
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
     * Set OFFSET
     */
    public function offset(int $offset): self
    {
        $this->offsetValue = $offset;
        return $this;
    }

    /**
     * Alias for limit and offset
     */
    public function take(int $limit): self
    {
        return $this->limit($limit);
    }

    /**
     * Alias for offset
     */
    public function skip(int $offset): self
    {
        return $this->offset($offset);
    }

    /**
     * Set page for pagination
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
        $sql = $this->toSql();
        return $this->db->select($sql, $this->bindings);
    }

    /**
     * Execute query and get first result
     * 
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Find by primary key
     * 
     * @return array<string, mixed>|null
     */
    public function find(int|string $id, string $primaryKey = 'id'): ?array
    {
        return $this->where($primaryKey, $id)->first();
    }

    /**
     * Get count of results
     */
    public function count(string $column = '*'): int
    {
        $this->columns = ["COUNT({$column}) as aggregate"];
        $result = $this->first();
        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Get sum of column
     */
    public function sum(string $column): float
    {
        $this->columns = ["SUM(`{$column}`) as aggregate"];
        $result = $this->first();
        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Get average of column
     */
    public function avg(string $column): float
    {
        $this->columns = ["AVG(`{$column}`) as aggregate"];
        $result = $this->first();
        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Get max of column
     */
    public function max(string $column): mixed
    {
        $this->columns = ["MAX(`{$column}`) as aggregate"];
        $result = $this->first();
        return $result['aggregate'] ?? null;
    }

    /**
     * Get min of column
     */
    public function min(string $column): mixed
    {
        $this->columns = ["MIN(`{$column}`) as aggregate"];
        $result = $this->first();
        return $result['aggregate'] ?? null;
    }

    /**
     * Check if any records exist
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Get paginated results
     * 
     * @return array{data: array, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        // Clone for count query
        $countQuery = clone $this;
        $total = $countQuery->count();
        
        // Get paginated data
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
     * Insert a new record
     * 
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Insert multiple records
     * 
     * @param array<int, array<string, mixed>> $rows
     */
    public function insertBatch(array $rows): int
    {
        return $this->db->insertBatch($this->table, $rows);
    }

    /**
     * Update records
     * 
     * @param array<string, mixed> $data
     */
    public function update(array $data): int
    {
        $sql = $this->compileUpdate($data);
        return $this->db->query($sql, $this->bindings)->rowCount();
    }

    /**
     * Delete records
     */
    public function delete(): int
    {
        $sql = $this->compileDelete();
        return $this->db->query($sql, $this->bindings)->rowCount();
    }

    /**
     * Increment a column value
     */
    public function increment(string $column, int $amount = 1): int
    {
        $sql = "UPDATE {$this->table} SET `{$column}` = `{$column}` + ?";
        $this->bindings = array_merge([$amount], $this->bindings);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        return $this->db->query($sql, $this->bindings)->rowCount();
    }

    /**
     * Decrement a column value
     */
    public function decrement(string $column, int $amount = 1): int
    {
        $sql = "UPDATE {$this->table} SET `{$column}` = `{$column}` - ?";
        $this->bindings = array_merge([$amount], $this->bindings);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        return $this->db->query($sql, $this->bindings)->rowCount();
    }

    /**
     * Full-text search (MySQL MATCH AGAINST)
     * 
     * @param array<string> $columns
     */
    public function search(array $columns, string $term, string $mode = 'boolean'): self
    {
        $columnList = implode(', ', array_map(fn($col) => "`{$col}`", $columns));
        
        $modeString = match($mode) {
            'boolean' => 'IN BOOLEAN MODE',
            'natural' => 'IN NATURAL LANGUAGE MODE',
            'expansion' => 'WITH QUERY EXPANSION',
            default => 'IN BOOLEAN MODE',
        };
        
        $this->whereRaw(
            "MATCH({$columnList}) AGAINST(? {$modeString})",
            [$term]
        );
        
        // Add relevance score to select
        $this->addSelect("MATCH({$columnList}) AGAINST(? {$modeString}) as relevance_score");
        $this->bindings[] = $term;
        
        return $this;
    }

    /**
     * Build SELECT SQL query
     */
    public function toSql(): string
    {
        $sql = 'SELECT ';
        
        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }
        
        $sql .= implode(', ', $this->columns);
        $sql .= ' FROM ' . $this->table;
        
        // Joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Where
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        // Group By
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        
        // Having
        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->compileHavings();
        }
        
        // Order By
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        // Limit
        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }
        
        // Offset
        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }
        
        return $sql;
    }

    /**
     * Compile WHERE clauses
     */
    private function compileWheres(): string
    {
        $parts = [];
        
        foreach ($this->wheres as $index => $where) {
            $part = '';
            
            if ($index > 0) {
                $part = $where['boolean'] . ' ';
            }
            
            $part .= match($where['type']) {
                'basic' => "`{$where['column']}` {$where['operator']} ?",
                'in' => "`{$where['column']}` IN (" . implode(', ', array_fill(0, count($where['value']), '?')) . ")",
                'not_in' => "`{$where['column']}` NOT IN (" . implode(', ', array_fill(0, count($where['value']), '?')) . ")",
                'null' => "`{$where['column']}` IS NULL",
                'not_null' => "`{$where['column']}` IS NOT NULL",
                'between' => "`{$where['column']}` BETWEEN ? AND ?",
                'not_between' => "`{$where['column']}` NOT BETWEEN ? AND ?",
                'raw' => $where['column'],
                default => '',
            };
            
            $parts[] = $part;
        }
        
        return implode(' ', $parts);
    }

    /**
     * Compile HAVING clauses
     */
    private function compileHavings(): string
    {
        $parts = [];
        
        foreach ($this->havings as $index => $having) {
            $part = '';
            
            if ($index > 0) {
                $part = $having['boolean'] . ' ';
            }
            
            $part .= "`{$having['column']}` {$having['operator']} ?";
            $parts[] = $part;
        }
        
        return implode(' ', $parts);
    }

    /**
     * Compile UPDATE SQL
     * 
     * @param array<string, mixed> $data
     */
    private function compileUpdate(array $data): string
    {
        $setClauses = [];
        $newBindings = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "`{$column}` = ?";
            $newBindings[] = $value;
        }
        
        // Prepend data bindings before where bindings
        $this->bindings = array_merge($newBindings, $this->bindings);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        return $sql;
    }

    /**
     * Compile DELETE SQL
     */
    private function compileDelete(): string
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        return $sql;
    }

    /**
     * Reset the query builder
     */
    public function reset(): self
    {
        $this->columns = ['*'];
        $this->joins = [];
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->havings = [];
        $this->limitValue = null;
        $this->offsetValue = null;
        $this->distinct = false;
        
        return $this;
    }

    /**
     * Clone the query builder
     */
    public function __clone(): void
    {
        // Deep copy arrays to prevent shared references
        $this->joins = array_map(fn($item) => $item, $this->joins);
        $this->wheres = array_map(fn($item) => $item, $this->wheres);
        $this->bindings = array_values($this->bindings);
        $this->orderBy = array_values($this->orderBy);
        $this->groupBy = array_values($this->groupBy);
        $this->havings = array_map(fn($item) => $item, $this->havings);
    }
}
