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
 * データベース参照処理用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Query_Select
{

    /**
     *
     * @var string 接続に使用するモジュール名
     */
    private $module;

    /**
     *
     * @var boolean distinctするかどうかのフラグ
     */
    private $distinct;

    /**
     *
     * @var array[Vizualizer_Plugin_Table_Column] 表示対象のカラム
     */
    private $columns;

    /**
     *
     * @var Vizualizer_Plugin_Table 検索対象のテーブル
     */
    private $tables;

    /**
     *
     * @var array[string] 検索抽出条件
     */
    private $wheres;

    /**
     *
     * @var array[string] 検索結果グルーピング
     */
    private $groups;

    /**
     *
     * @var array[string] グルーピング抽出条件
     */
    private $having;

    /**
     *
     * @var array[string] 抽出結果並べ替え条件
     */
    private $orders;

    /**
     * テーブル結合用の変数
     */
    private $tableValues;

    /**
     * 検索条件用の変数
     */
    private $whereValues;

    /**
     * グループ検索条件用の変数
     */
    private $havingValues;

    /**
     * 検索抽出上限件数の変数
     */
    private $limit;

    /**
     * 検索抽出開始位置の変数
     */
    private $offset;

    /**
     * コンストラクタ
     *
     * @param Vizualizer_Plugin_Table $table メインテーブル用オブジェクト
     * @param string $from メインテーブルとは別にテーブル名を指定する場合のテーブル名
     */
    public function __construct($table, $from = null)
    {
        $this->module = $table->getModuleName();
        $this->distinct = false;
        $this->columns = array();
        if ($from == null) {
            $this->tables = $table->_T;
        } else {
            $this->tables = $from;
        }
        $this->wheres = array();
        $this->groups = array();
        $this->having = array();
        $this->orders = array();
        $this->tableValues = array();
        $this->whereValues = array();
        $this->havingValues = array();
        if (isset($table->delete_flg)) {
            $this->wheres[] = $table->delete_flg . " = 0";
        }
    }

    /**
     * DISTINCT抽出を行うフラグを設定
     *
     * @param boolean $distinct trueならDISTINCT検索する
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;
    }

    public function addColumn($column, $alias = "")
    {
        $this->columns[] = $column . (!empty($alias) ? " AS `" . $alias . "`" : "");
        return $this;
    }

    public function join($table, $conditions = array(), $values = array())
    {
        return $this->joinInner($table, $conditions, $values);
    }

    public function joinInner($table, $conditions = array(), $values = array())
    {
        $tableName = $table->_T;
        if (is_string($table)) {
            $tableName = $table;
        }
        $this->tables .= " INNER JOIN " . $tableName . (!empty($conditions) ? " ON " . implode(" AND ", $conditions) : "");
        foreach ($values as $v) {
            $this->tableValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function joinLeft($table, $conditions = array(), $values = array())
    {
        $tableName = $table->_T;
        if (is_string($table)) {
            $tableName = $table;
        }
        $this->tables .= " LEFT JOIN " . $tableName . (!empty($conditions) ? " ON " . implode(" AND ", $conditions) : "");
        foreach ($values as $v) {
            $this->tableValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function where($condition, $values = array())
    {
        return $this->addWhere($condition, $values);
    }

    public function addWhere($condition, $values = array())
    {
        $this->wheres[] = "(" . $condition . ")";
        foreach ($values as $v) {
            $this->whereValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function group($column)
    {
        return $this->addGroupBy($column);
    }

    public function addGroupBy($column)
    {
        $this->groups[] = $column;
        return $this;
    }

    public function having($condition, $values = array())
    {
        return $this->addHaving($condition, $values);
    }

    public function addHaving($condition, $values = array())
    {
        $this->having[] = "(" . $condition . ")";
        foreach ($values as $v) {
            $this->havingValues[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    public function order($column, $reverse = false)
    {
        return $this->addOrder($column, $reverse);
    }

    public function addOrder($column, $reverse = false)
    {
        $this->orders[] = $column . ($reverse ? " DESC" : " ASC");
        return $this;
    }

    public function setLimit($limit = null, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function buildQuery()
    {
        // クエリのビルド
        $sql = "SELECT ";
        if ($this->distinct) {
            $sql .= " DISTINCT ";
        }
        $sql .= implode(", ", $this->columns) . " FROM " . $this->tables;
        $sql .= (!empty($this->wheres) ? " WHERE " . implode(" AND ", $this->wheres) : "");
        $sql .= (!empty($this->groups) ? " GROUP BY " . implode(", ", $this->groups) : "");
        $sql .= (!empty($this->having) ? " HAVING " . implode(", ", $this->having) : "");
        $sql .= (!empty($this->orders) ? " ORDER BY " . implode(", ", $this->orders) : "");
        $sql .= ((is_numeric($this->limit) && $this->limit >= 0) ? " LIMIT " . $this->limit : "");
        $sql .= ((is_numeric($this->offset) && $this->offset >= 0) ? " OFFSET " . $this->offset : "");

        return $sql;
    }

    public function showQuery()
    {
        $sql = $this->buildQuery();

        $values = array_merge($this->tableValues, $this->whereValues, $this->havingValues);

        $connection = Vizualizer_Database_Factory::conn($this->module);

        if (is_array($values) && count($values) > 0) {
            $partSqls = explode("?", $sql);
            $sql = $partSqls[0];

            foreach ($values as $index => $value) {
                $sql .= "'" . $connection->escape($value) . "'" . $partSqls[$index + 1];
            }
        }
        return $sql;
    }

    public function fetch($limit = null, $offset = null)
    {
        // クエリのビルド
        $sql = $this->buildQuery();

        // クエリの検索件数を制限する。
        if ($limit != null) {
            $sql .= " LIMIT " . $limit;
            if ($offset != null) {
                $sql .= " OFFSET " . $offset;
            }
        }

        // クエリを実行する。
        try {
            $sql = $this->showQuery();
            Vizualizer_Logger::writeDebug($sql);
            $connection = Vizualizer_Database_Factory::conn($this->module);
            $result = $connection->query($sql);
        } catch (Exception $e) {
            Vizualizer_Logger::writeError($sql, $e);
            throw new Vizualizer_Exception_Database($e);
        }

        return new Vizualizer_Query_Select_Result($result);
    }

    public function count($limit = null, $offset = null)
    {
        // 結果オブジェクトを取得
        $result = $this->fetch($limit, $offset);

        // 結果を全て取得
        $data = $result->count();

        // 結果オブジェクトを解放
        $result->close();
        $result = null;

        // クエリの実行結果を返す。
        return $data;
    }

    public function execute($limit = null, $offset = null)
    {
        // 結果オブジェクトを取得
        $result = $this->fetch($limit, $offset);

        // 結果を全て取得
        $data = $result->all();

        // 結果オブジェクトを解放
        $result->close();
        $result = null;

        // クエリの実行結果を返す。
        return $data;
    }
}
