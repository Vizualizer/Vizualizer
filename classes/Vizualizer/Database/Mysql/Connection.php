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
 * MySQLのコネクションを管理するためのクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Database_Mysql_Connection implements Vizualizer_Database_Connection
{
    private $configure;

    private $connection;

    private $inTransaction;

    /**
     * コンストラクタ
     *
     * @param array $configure
     */
    public function __construct($configure)
    {
        $this->configure = $configure;
        if (!isset($this->configure["port"])) {
            $this->configure["port"] = "3306";
        }
        $this->connect();
    }

    /**
     * MySQLサーバーに接続する
     */
    protected function connect()
    {
        $this->connection = mysqli_connect($this->configure["host"], $this->configure["user"], $this->configure["password"], $this->configure["database"], $configure["port"]);
        mysqli_set_charset($this->connection, "UTF-8");
        mysqli_query($this->connection, $this->configure["query"]);
        $this->inTransaction = false;
    }

    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * テーブルのカラムを取得する。
     *
     * @param string $table テーブル名
     * @return array カラムのリスト
     * @throws Vizualizer_Exception_System
     */
    public function columns($table)
    {
        // テーブルの定義を取得
        if (($result = $this->query("SHOW COLUMNS FROM " . $table)) === FALSE) {
            throw new Vizualizer_Exception_System("カラムの取得に失敗しました。");
        }
        $columns = array();
        while ($column = $result->fetch()) {
            $columns[] = $column;
        }
        $result->close();
        return $columns;
    }

    /**
     * テーブルのキーを取得する。
     *
     * @param string $table テーブル名
     * @return array キーのリスト
     */
    public function keys($table)
    {
        $result = $this->query("SHOW INDEXES FROM " . $table . " WHERE Key_name = 'PRIMARY'");
        $keys = array();
        while ($key = $result->fetch()) {
            $keys[] = $key["Column_name"];
        }
        $result->close();
        return $keys;
    }

    /**
     * テーブルのインデックスを取得する。
     *
     * @param string $table テーブル名
     * @return array インデックスのリスト
     */
    public function indexes($table)
    {
        $result = $this->query("SHOW INDEXES FROM " . $table);
        $indexes = array();
        while ($index = $result->fetch()) {
            if (!isset($indexes[$index["Key_name"]]) || !is_array($indexes[$index["Key_name"]])) {
                $indexes[$index["Key_name"]] = array();
            }
            $indexes[$index["Key_name"]][] = $index["Column_name"];
        }
        $result->close();
        return $indexes;
    }

    /**
     * トランザクションの開始
     */
    public function begin()
    {
        if(!$this->inTransaction){
            $this->query("BEGIN");
            $this->inTransaction = true;
        }
    }

    /**
     * トランザクションのコミット
     */
    public function commit()
    {
        if($this->inTransaction){
            $this->query("COMMIT");
            $this->inTransaction = false;
        }
    }

    /**
     * トランザクションのロールバック
     */
    public function rollback()
    {
        if($this->inTransaction){
            $this->query("ROLLBACK");
            $this->inTransaction = false;
        }
    }

    /**
     * 値のエスケープ処理
     *
     * @param string $value エスケープする値
     * @return string エスケープした値
     */
    public function escape($value)
    {
        if ($this->connection != null) {
            return mysqli_real_escape_string($this->connection, $value);
        }
        return null;
    }

    /**
     * 識別子のエスケープ処理
     *
     * @param string $value エスケープする識別子
     * @return string エスケープした識別子
     */
    public function escapeIdentifier($identifier)
    {
        return "`" . $identifier . "`";
    }

    /**
     * クエリの実行
     *
     * @param string $query 実行するクエリ
     * @return Vizualizer_Database_Result 実行結果
     */
    public function query($query)
    {
        if ($this->connection != null) {
            if(!mysqli_ping($this->connection)){
                $this->connect();
            }
            $result = mysqli_query($this->connection, $query);
            if ($result === FALSE) {
                return FALSE;
            } elseif ($result !== TRUE) {
                return new Vizualizer_Database_Mysql_Result($result);
            } else {
                return mysqli_affected_rows($this->connection);
            }
        }
        return FALSE;
    }

    /**
     * 最後に挿入した自動採番を取得
     *
     * @return int 最後の自動採番値
     */
    public function lastInsertId()
    {
        return mysqli_insert_id($this->connection);
    }

    /**
     * 接続を閉じる
     */
    public function close()
    {
        if ($this->connection != null) {
            $this->rollback();
            mysqli_close($this->connection);
            $this->connection = null;
        }
    }
}
