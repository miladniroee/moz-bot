<?php

namespace App;

use Exception;
use PDO;
use PDOException;


/**
 * here we have a simple php class for connecting to MYSQL database.
 *
 */
class Database
{

    /**
     * @var string $table
     * This variable is for table name, it should be set on Model class
     */
    protected string $table;

    /**
     * @var string|array $primaryKey
     * This is the table primary key, we use if for find() method.
     */
    protected string|array $primaryKey = 'id';


    /**
     * @var string $select
     * @var array $where
     * @var array $orWhere
     * @var string $groupBy
     *
     * These variables are set with dynamic methods on model calls.
     */
    private string $select = '*';
    private array $where = [];
    private array $orWhere = [];
    private string $groupBy = '';


    /**
     * @var PDO $conn
     *  This variable use for connection method
     */
    private PDO $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO('mysql:host=localhost;dbname=' . config('DB_NAME'), config('DB_USER'), config('DB_PASS'));
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            return $this;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            exit();
        }
    }

    private function fetchAll(string $query, array $params = []): array
    {
        $prepare = $this->conn->prepare($query);
        $prepare->execute($params);
        return $prepare->fetchAll();
    }

    private function fetch(string $query, array $params = [])
    {
        $prepare = $this->conn->prepare($query);
        $prepare->execute($params);
        return $prepare->fetch();
    }


    public function execute(string $query, array $params = []): bool
    {
        $prepare = $this->conn->prepare($query);
        return $prepare->execute($params);
    }


    public static function query(): static
    {
        return new static();
    }

    public function select(...$attr): static
    {
        $this->select = implode(',', $attr);

        return $this;
    }

    public function groupBy(string $column): static
    {
        $this->groupBy = 'GROUP BY ' . $column;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function find($key)
    {
        if (is_array($this->primaryKey)):
            if (count(array_diff($this->primaryKey, array_keys($key))) !== 0) throw new Exception('Keys provided are not compatible with Model primary keys');

            foreach ($this->primaryKey as $val):
                $this->where["`{$val}` = :{$val}"] = [":{$val}" => $key[$val]];
            endforeach;
        else:
            $this->where["`{$this->primaryKey}` = :{$this->primaryKey}"] = [":{$this->primaryKey}" => $key];
        endif;

        [$whereClause, $whereValues] = $this->calculateWhere();

        return $this->fetch("SELECT {$this->select} FROM {$this->table} WHERE $whereClause", $whereValues);
    }

    public function get(): array
    {
        [$whereClause, $whereValues] = $this->calculateWhere();

        return $this->fetchAll("SELECT {$this->select} FROM {$this->table} WHERE $whereClause {$this->groupBy}", $whereValues);
    }

    public function first(): false|object
    {
        [$whereClause, $whereValues] = $this->calculateWhere();

        return $this->fetch("SELECT {$this->select} FROM {$this->table} WHERE $whereClause", $whereValues);
    }

    public function where(string $column, string $operand, mixed $value = null): static
    {
        if (func_num_args() === 3) {
            $this->where["`$column` $operand :$column"] = [":$column" => $value];
        } else {
            $this->where["`$column` = :$column"] = [":$column" => $operand];
        }

        return $this;
    }

    public function orWhere(string $column, string $operand, mixed $value = null): static
    {
        if (func_num_args() === 3) {
            $this->orWhere["`$column` $operand :$column"] = [":$column" => $value];
        } else {
            $this->orWhere["`$column` = :$column"] = [":$column" => $operand];
        }

        return $this;
    }

    public function update(array $values): bool
    {
        [$whereClause, $whereValues] = $this->calculateWhere();

        $setClause = [];
        $setValues = [];

        foreach ($values as $key => $value) {
            $setClause[] = "`$key` = :$key";
            $setValues[":$key"] = $value;
        }

        $setClause = implode(',', $setClause);

        return $this->execute("UPDATE `{$this->table}` SET $setClause WHERE $whereClause", [...$setValues, ...$whereValues]);
    }

    public function insert(array $values): bool
    {
        $insertClause = [];
        $insertValues = [];
        $bind = [];

        foreach ($values as $key => $value) {
            $insertClause[] = "`$key`";
            $insertValues[] = ":$key";
            $bind[$key] = $value;
        }

        $insertClause = implode(',', $insertClause);
        $insertValues = implode(',', $insertValues);
        return $this->execute("INSERT IGNORE INTO `{$this->table}` ($insertClause) VALUES ($insertValues)", $bind);
    }

    private function calculateWhere(): array
    {

        $whereClause = implode(" AND ", array_keys($this->where));
        $whereValues = [];

        $orWhereClause = '';
        if (count($this->orWhere) > 0) {
            $whereClause .= ' AND (' . implode(" OR ", array_keys($this->orWhere)) . ')';
        }

        foreach (array_merge($this->where, $this->orWhere) as $key => $value) {
            $whereValues[array_key_first($value)] = $value[array_key_first($value)];
        }

        return [$whereClause, $whereValues];
    }
}

