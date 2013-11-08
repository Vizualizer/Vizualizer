<?php

/**
 * Copyright (C) 2012 Vizualizer All Rights Reserved.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Naohisa Minagawa <info@vizualizer.jp>
 * @copyright Copyright (c) 2010, Vizualizer
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 * @since PHP 5.3
 * @version   1.0.0
 */

/**
 * データベース更新処理用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Query_Update
{

    /**
     *
     * @var string 接続に使用するモジュール名
     */
    private $module;

    private $tables;

    private $sets;

    private $wheres;

    private $setValues;

    private $tableValues;

    private $whereValues;

    public function __construct($table)
    {
        $this->module = $table->getModuleName();
        $this->tables = $table->_T;
        $this->sets = array();
        $this->wheres = array();
        $this->setValues = array();
        $this->tableValues = array();
        $this->whereValues = array();
    }

    public function joinInner($table, $conditions = array(), $values = array())
    {
        $this->tables .= " INNER JOIN " . $table->_T . (!empty($conditions) ? " ON " . implode(" AND ", $conditions) : "");
        foreach ($values as $v) {
            $this->tableValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function joinLeft($table, $conditions = array(), $values = array())
    {
        $this->tables .= " LEFT JOIN " . $table->_T . (!empty($conditions) ? " ON " . implode(" AND ", $conditions) : "");
        foreach ($values as $v) {
            $this->tableValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function addSets($expression, $values = array())
    {
        $this->sets[] = $expression;
        foreach ($values as $v) {
            $this->setValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function addWhere($condition, $values = array())
    {
        $this->wheres[] = "(" . $condition . ")";
        foreach ($values as $v) {
            $this->whereValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function buildQuery()
    {
        // クエリのビルド
        $sql = "UPDATE " . $this->tables;
        $sql .= (!empty($this->sets) ? " SET " . implode(", ", $this->sets) : "");
        $sql .= (!empty($this->wheres) ? " WHERE " . implode(" AND ", $this->wheres) : "");
        
        return $sql;
    }

    public function showQuery()
    {
        $sql = $this->buildQuery();
        
        $values = array_merge($this->tableValues, $this->setValues, $this->whereValues);
        
        if (is_array($values) && count($values) > 0) {
            $partSqls = explode("?", $sql);
            $sql = $partSqls[0];
            
            $connection = Vizualizer_Database_Factory::getConnection($this->module, true);
            foreach ($values as $index => $value) {
                $sql .= "'" . $connection->escape($value) . "'" . $partSqls[$index + 1];
            }
        }
        
        return $sql;
    }

    public function execute()
    {
        if (!empty($this->sets)) {
            
            // クエリを実行する。
            try {
                $sql = $this->showQuery();
                $connection = Vizualizer_Database_Factory::getConnection($this->module);
                Vizualizer_Logger::writeDebug($sql);
                $result = $connection->query($sql);
            } catch (Exception $e) {
                Vizualizer_Logger::writeError($sql, $e);
                throw new Vizualizer_Exception_Database($e);
            }
            
            return $result;
        } else {
            return 0;
        }
    }
}
 