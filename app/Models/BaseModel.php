<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected static string $table;
    protected static array $jsonFields = [];

    protected static function map(array $row): array
    {
        return $row;
    }

    protected static function decodeRow(array $row): array
    {
        foreach (static::$jsonFields as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = json_decode($row[$field], true) ?: [];
            }
        }
        return static::map($row);
    }

    protected static function buildWhere(array $filters, array &$params): string
    {
        $clauses = ['1=1'];
        foreach ($filters as $key => $value) {
            if ($key === '$or') {
                continue;
            }
            if ($key === 'category' && is_array($value) && isset($value['$in'])) {
                $in = $value['$in'];
                $placeholders = implode(',', array_fill(0, count($in), '?'));
                $clauses[] = 'category IN (' . $placeholders . ')';
                foreach ($in as $v) {
                    $params[] = $v;
                }
                continue;
            }
            if (is_array($value)) {
                if (isset($value['$gte'])) {
                    $col = static::snake($key);
                    $clauses[] = "$col >= ?";
                    $params[] = $value['$gte'];
                }
                if (isset($value['$lte'])) {
                    $col = static::snake($key);
                    $clauses[] = "$col <= ?";
                    $params[] = $value['$lte'];
                }
                if (isset($value['$in'])) {
                    $col = static::snake($key);
                    $placeholders = implode(',', array_fill(0, count($value['$in']), '?'));
                    $clauses[] = "$col IN ($placeholders)";
                    foreach ($value['$in'] as $v) {
                        $params[] = $v;
                    }
                }
                continue;
            }
            $col = static::snake($key);
            if ($key === '_id' || $key === 'id') {
                $col = 'id';
            }
            if ($key === 'slug' && is_object($value)) {
                continue;
            }
            if (is_bool($value)) {
                $clauses[] = "$col = ?";
                $params[] = $value ? 1 : 0;
            } else {
                $clauses[] = "$col = ?";
                $params[] = $value;
            }
        }
        return implode(' AND ', $clauses);
    }

    protected static function snake(string $key): string
    {
        if ($key === '_id') {
            return 'id';
        }
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
    }

    public static function findOne(array $filters): ?array
    {
        $params = [];
        $where = static::buildWhere($filters, $params);
        if (isset($filters['slug']) && is_object($filters['slug'])) {
            $where .= ' AND slug LIKE ?';
            $params[] = '%' . (string) $filters['slug']->pattern ?? '';
        }
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $where . ' LIMIT 1';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? static::decodeRow($row) : null;
    }

    public static function find(array $filters, array $options = []): array
    {
        $params = [];
        $where = static::buildWhere($filters, $params);
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $where;
        if (!empty($options['sort'])) {
            $orders = [];
            foreach ($options['sort'] as $col => $dir) {
                $orders[] = static::snake($col) . ' ' . (strtolower($dir) === 'desc' ? 'DESC' : 'ASC');
            }
            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }
        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . (int) $options['limit'];
        }
        if (!empty($options['skip'])) {
            $sql .= ' OFFSET ' . (int) $options['skip'];
        }
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return array_map([static::class, 'decodeRow'], $stmt->fetchAll());
    }

    public static function count(array $filters): int
    {
        $params = [];
        $where = static::buildWhere($filters, $params);
        $sql = 'SELECT COUNT(*) FROM ' . static::$table . ' WHERE ' . $where;
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function insert(array $data): int
    {
        $mapped = static::toDb($data);
        $cols = array_keys($mapped);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(',', $cols) . ') VALUES (' . $placeholders . ')';
        Database::pdo()->prepare($sql)->execute(array_values($mapped));
        return (int) Database::pdo()->lastInsertId();
    }

    public static function updateOne(array $filters, array $data): void
    {
        $mapped = static::toDb($data);
        $setSql = [];
        $setParams = [];
        foreach ($mapped as $col => $val) {
            $setSql[] = "$col = ?";
            $setParams[] = $val;
        }
        $whereParams = [];
        $where = static::buildWhere($filters, $whereParams);
        // SET params must come before WHERE params
        $params = array_merge($setParams, $whereParams);
        $sql = 'UPDATE ' . static::$table . ' SET ' . implode(', ', $setSql) . ' WHERE ' . $where;
        Database::pdo()->prepare($sql)->execute($params);
    }

    public static function updateMany(array $filters, array $data): void
    {
        static::updateOne($filters, $data);
    }

    public static function deleteOne(array $filters): void
    {
        $params = [];
        $where = static::buildWhere($filters, $params);
        $sql = 'DELETE FROM ' . static::$table . ' WHERE ' . $where;
        Database::pdo()->prepare($sql)->execute($params);
    }

    public static function deleteMany(array $filters): void
    {
        static::deleteOne($filters);
    }

    protected static function toDb(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if ($key === '_id' || $key === '_method' || $key === '_files') {
                continue;
            }
            $col = static::snake($key);
            if (in_array($col, static::$jsonFields, true) && is_array($value)) {
                $out[$col] = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $out[$col] = $value;
            }
        }
        return $out;
    }

    public static function findBySlugLike(string $keyword, array $extra = []): array
    {
        $filters = array_merge($extra, ['deleted' => false]);
        $params = [];
        $where = static::buildWhere($filters, $params);
        $where .= ' AND slug LIKE ?';
        $params[] = '%' . $keyword . '%';
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $where . ' ORDER BY position DESC';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return array_map([static::class, 'decodeRow'], $stmt->fetchAll());
    }

    public static function findByDateRange(array $filters, string $field, $gte, $lt): array
    {
        $params = [];
        $where = static::buildWhere($filters, $params);
        $col = static::snake($field);
        $where .= " AND {$col} >= ? AND {$col} < ?";
        $params[] = $gte;
        $params[] = $lt;
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . $where;
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return array_map([static::class, 'decodeRow'], $stmt->fetchAll());
    }

    public static function updateByIds(array $ids, array $data): void
    {
        if (empty($ids)) {
            return;
        }
        $mapped = static::toDb($data);
        $sets = [];
        $params = [];
        foreach ($mapped as $col => $val) {
            $sets[] = "$col = ?";
            $params[] = $val;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'UPDATE ' . static::$table . ' SET ' . implode(', ', $sets) . " WHERE id IN ($placeholders)";
        foreach ($ids as $id) {
            $params[] = $id;
        }
        Database::pdo()->prepare($sql)->execute($params);
    }

    public static function deleteByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'DELETE FROM ' . static::$table . ' WHERE id IN (' . $placeholders . ')';
        Database::pdo()->prepare($sql)->execute($ids);
    }
}
