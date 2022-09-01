<?php

/**
 * Name: PdoHelper Class File by Prk PHP Framework
 * Author: 消失的彩虹海 & Prk
 * Website: https://blog.cccyun.cn/ & https://imprk.me/
 * Date: (UNKNOW)
 * Location: (UNKNOW)
 */

namespace lib;

class PdoHelper {

    private $sqlPrefix = "pre_";
    private $db;
    private $fetchStyle = \PDO::FETCH_ASSOC;
    private $prefix;
    private $errorInfo;

    function __construct($config) {
        $this->prefix = $config['prefix'] . '_';
        try {
            $this->db = new \PDO(
                "mysql:host={$config['host']};dbname={$config['name']};port={$config['port']}",
                $config['username'],
                $config['password']
            );
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $this->db->exec("set sql_mode = ''");
        $this->db->exec("set names utf8mb4");
    }

    public function setFetchStyle($_style) {
        $this->fetchStyle = $_style;
    }

    private function dealPrefix($_sql) {
        return str_replace(
            $this->sqlPrefix,
            $this->prefix,
            $_sql
        );
    }

    private function _where($conditions) {
        $result = [
            "_where"        =>  " ",
            "_bindParams"   =>  []
        ];
        if (is_array($conditions) && !empty($conditions)) {
            $fieldss = [];
            $sql = null;
            $join = [];
            if (isset($conditions[0]) && $sql = $conditions[0]) unset($conditions[0]);
            foreach ($conditions as $key => $condition) {
                if (substr($key, 0, 1) != ":") {
                    unset($conditions[$key]);
                    $conditions[":" . $key] = $condition;
                }
                $join[] = "`{$key}` = :{$key}";
            }
            if (!$sql) $sql = join(" AND ", $join);
            $result["_where"] = " WHERE " . $sql;
            $result["_bindParams"] = $conditions;
        } elseif (!empty($conditions)) $result["_where"] = " WHERE " . $conditions;
        return $result;
    }

    private function _select($table, $fields = '*', $where = [], $sort = null, $limit = null) {
        $sort = !empty($sort) ? ' ORDER BY ' . $sort : '';
        $fields = !empty($fields) ? $fields : '*';
        if (is_array($fields)) $fields = implode(
            ',',
            $fields
        );
        $conditions = $this->_where($where);
        $sql = ' FROM pre_' . $table . $conditions["_where"];
            if (is_array($limit))   $limit = ' LIMIT ' . $limit[0] . ',' . $limit[1];
        elseif (!empty($limit))     $limit = ' LIMIT ' . $limit;
        else                        $limit = '';
        return [
            'sql'   =>  'SELECT ' . $fields . $sql . $sort . $limit,
            'bind'  =>  $conditions["_bindParams"]
        ];
    }

    public function find($table, $fields = '*', $where = [], $sort = null, $limit = null) {
        $sql_arr = $this->_select(
            $table,
            $fields,
            $where,
            $sort,
            $limit
        );
        return $this->getRow(
            $sql_arr['sql'],
            $sql_arr['bind']
        );
    }

    public function findAll($table, $fields = '*', $where = [], $sort = null, $limit = null) {
        $sql_arr = $this->_select(
            $table,
            $fields,
            $where,
            $sort,
            $limit
        );
        return $this->getAll(
            $sql_arr['sql'],
            $sql_arr['bind']
        );
    }

    public function findColumn($table, $fields, $where = [], $sort = null) {
        $sql_arr = $this->_select(
            $table,
            $fields,
            $where,
            $sort,
            1
        );
        return $this->getColumn(
            $sql_arr['sql'],
            $sql_arr['bind']
        );
    }

    public function insert($table, $data) {
        $values = [];
        foreach ($data as $k => $v) {
            $keys[] = "`{$k}`";
                if ($v == 'NOW()' || $v == 'CURDATE()' || $v == 'CURTIME()') $marks[] = $v;
            elseif ($v == '') $marks[] = 'NULL';
            else {
                $values[":" . $k] = $v;
                $marks[] = ":" . $k;
            }
        }
        $rowCount = $this->exec(
            "INSERT INTO pre_" . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $marks) . ")",
            $values
        );
        if ($rowCount) return $this->lastInsertId();
        else return false;
    }

    public function update($table, $data, $where) {
        if (is_array($data) && !empty($data)) {
            $values = [];
            foreach ($data as $k => $v) {
                if ('NOW()' == $v || 'CURDATE()' == $v || 'CURTIME()' == $v) $setstr[] = "`{$k}` = " . $v;
                elseif ('' == $v) $setstr[] = "`{$k}` = NULL";
                else {
                    $values[":M_UPDATE_" . $k] = $v;
                    $setstr[] = "`{$k}` = :M_UPDATE_" . $k;
                }
            }
            $update = implode(
                ', ',
                $setstr
            );
        } elseif (!empty($data)) $update = $data;
        else return false;
        $conditions = $this->_where($where);
        $rowCount = $this->exec(
            "UPDATE pre_" . $table . " SET " . $update . $conditions["_where"],
            $conditions["_bindParams"] + $values
        );
        return $rowCount;
    }

    public function delete($table, $where) {
        $conditions = $this->_where($where);
        $rowCount = $this->exec(
            "DELETE FROM pre_" . $table . $conditions["_where"],
            $conditions["_bindParams"]
        );
        return $rowCount;
    }

    public function count($table, $where) {
        $conditions = $this->_where($where);
        $count = $this->getColumn(
            "SELECT COUNT(*) FROM pre_" . $table . $conditions["_where"],
            $conditions["_bindParams"]
        );
        return $count;
    }

    public function exec($_sql, $_array = null) {
        $_sql = $this->dealPrefix($_sql);
        if (is_array($_array)) {
            $stmt = $this->db->prepare($_sql);
            if ($stmt) {
                $result = $stmt->execute($_array);
                if (false !== $result) return $result;
                else {
                    $this->errorInfo = $stmt->errorInfo();
                    return false;
                }
            } else {
                $this->errorInfo = $this->db->errorInfo();
                return false;
            }
        } else {
            $result = $this->db->exec($_sql);
            if (false !== $result) return $result;
            else {
                $this->errorInfo = $this->db->errorInfo();
                return false;
            }
        }
    }

    public function query($_sql, $_array = null) {
        $_sql = $this->dealPrefix($_sql);
        if (is_array($_array)) {
            $stmt = $this->db->prepare($_sql);
            if ($stmt) {
                if ($stmt->execute($_array)) return $stmt;
                else {
                    $this->errorInfo = $stmt->errorInfo();
                    return false;
                }
            } else {
                $this->errorInfo = $this->db->errorInfo();
                return false;
            }
        } else {
            if ($stmt = $this->db->query($_sql)) return $stmt;
            else {
                $this->errorInfo = $this->db->errorInfo();
                return false;
            }
        }
    }

    public function getRow($_sql, $_array = null) {
        $stmt = $this->query($_sql, $_array);
        if ($stmt) return $stmt->fetch($this->fetchStyle);
        else return false;
    }

    public function getAll($_sql, $_array = null) {
        $stmt = $this->query($_sql, $_array);
        if ($stmt) return $stmt->fetchAll($this->fetchStyle);
        else return false;
    }

    public function getCount($_sql, $_array = null) {
        $stmt = $this->query($_sql, $_array);
        if ($stmt) return $stmt->rowCount();
        else return false;
    }

    public function getColumn($_sql, $_array = null) {
        $stmt = $this->query($_sql, $_array);
        if ($stmt) return $stmt->fetchColumn();
        else return false;
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function error() {
        $error = $this->errorInfo;
        if ($error) return '[' . $error[1] . ']' . $error[2];
        else return null;
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollBack() {
        return $this->db->rollBack();
    }

    function __get($name) {
        return $this->$name;
    }

    function __destruct() {
        $this->db = null;
    }

}
