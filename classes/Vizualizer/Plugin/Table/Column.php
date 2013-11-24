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
 * データベースカラムラッパー用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_Table_Column
{

    /**
     *
     * @var string テーブルのモジュール名
     */
    private $module;

    /**
     *
     * @var string テーブルの名称
     */
    private $table;

    /**
     *
     * @var string フィールド名
     */
    private $field;

    /**
     *
     * @var boolean NULL可能かどうかのフラグ
     */
    private $canNull;

    /**
     *
     * @var boolean 主キーかどうかのフラグ
     */
    private $isKey;

    /**
     *
     * @var boolean 自動採番かどうかのフラグ
     */
    private $isAutoIncrement;

    /**
     * データベースのフィールドインスタンスを生成する。
     * 
     * @param Vizualizer_Plugin_Table $table フィールドを保有しているテーブルのインスタンス
     * @param string $column フィールドのカラム名
     */
    public function __construct($table, $column)
    {
        $this->module = $table->getModuleName();
        $this->table = $table->_C;
        $this->field = $column["Field"];
        $this->canNull = (($column["Null"] == "YES") ? true : false);
        $this->isKey = (($column["Key"] == "PRI") ? true : false);
        $this->isAutoIncrement = (($column["Extra"] == "auto_increment") ? true : false);
    }

    /**
     * テーブルのカラムの詳細情報を取得
     * 
     * @param string $name カラム種別
     * @return string カラム詳細
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * フィールドを文字列として扱った場合にフィールド名となるようにする。
     * 
     * @return string クラスの文字列表現
     */
    public function __toString()
    {
        // DBの接続を取得する。
        $connection = Vizualizer_Database_Factory::conn($this->module);
        
        // カラム名をエスケープする。
        return $this->table . "." . $connection->escapeIdentifier($this->field);
    }
}
