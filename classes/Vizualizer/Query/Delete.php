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
 * データベース削除処理用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Query_Delete
{

    /**
     *
     * @var string 接続に使用するモジュール名
     */
    private $module;

    /**
     * 削除対象のテーブル
     */
    private $tables;

    /**
     * 削除対象のレコードの条件
     */
    private $wheres;

    /**
     * 削除対象のレコードの条件に設定するパラメータリスト
     */
    private $values;

    /**
     * レコード削除処理を初期化します。
     *
     * @param s string $table レコード削除対象のテーブル
     */
    public function __construct($table)
    {
        $this->module = $table->getModuleName();
        $this->tables = $table->_T;
        $this->wheres = array();
        $this->values = array();
    }

    /**
     * レコード削除条件を追加します。
     *
     * @param s string $condition レコード削除条件式
     */
    public function addWhere($condition, $values = array())
    {
        $this->wheres[] = "(" . $condition . ")";
        foreach ($values as $v) {
            $this->values[] = (is_string($v) ? trim($v) : $v);
        }
        return $this;
    }

    /**
     * 現在の状態で発行する削除クエリを取得する。
     *
     * @return string レコード削除クエリ
     */
    public function buildQuery()
    {
        // クエリのビルド
        $sql = "DELETE FROM " . $this->tables;
        $sql .= (!empty($this->wheres) ? " WHERE " . implode(" AND ", $this->wheres) : "");

        return $sql;
    }

    public function showQuery()
    {
        $sql = $this->buildQuery();

        if (is_array($this->values) && count($this->values) > 0) {
            $partSqls = explode("?", $sql);
            $sql = $partSqls[0];

            $connection = Vizualizer_Database_Factory::begin($this->module);
            foreach ($this->values as $index => $value) {
                $sql .= "'" . $connection->escape($value) . "'" . $partSqls[$index + 1];
            }
        }

        return $sql;
    }

    /**
     * レコードの削除を実行する。
     */
    public function execute()
    {
        // クエリを実行する。
        try {
            $sql = $this->showQuery();
            $connection = Vizualizer_Database_Factory::begin($this->module);
            Vizualizer_Logger::writeDebug($sql);
            $result = $connection->query($sql);
        } catch (Exception $e) {
            Vizualizer_Logger::writeError($sql, $e);
            throw new Vizualizer_Exception_Database($e);
        }
        return $result;
    }
}
