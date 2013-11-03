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
 * データベース接続のインターフェイスです。。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
interface Vizualizer_Database_Connection
{

    /**
     * テーブルのカラムを取得する。
     * 
     * @param string $table テーブル名
     * @return array カラムのリスト
     * @throws Clay_Exception_System
     */
    public function columns($table);

    /**
     * テーブルのキーを取得する。
     * 
     * @param string $table テーブル名
     * @return array キーのリスト
     */
    public function keys($table);

    /**
     * テーブルのインデックスを取得する。
     * 
     * @param string $table テーブル名
     * @return array インデックスのリスト
     */
    public function indexes($table);

    /**
     * トランザクションの開始
     */
    public function begin();

    /**
     * トランザクションのコミット
     */
    public function commit();

    /**
     * トランザクションのロールバック
     */
    public function rollback();

    /**
     * 値のエスケープ処理
     * 
     * @param string $value エスケープする値
     * @return string エスケープした値
     */
    public function escape($value);

    /**
     * 識別子のエスケープ処理
     * 
     * @param string $value エスケープする識別子
     * @return string エスケープした識別子
     */
    public function escapeIdentifier($identifier);

    /**
     * クエリの実行
     * 
     * @param string $query 実行するクエリ
     * @return Vizualizer_Database_Result 実行結果
     */
    public function query($query);

    /**
     * 最後に挿入した自動採番を取得
     * 
     * @return int 最後の自動採番値
     */
    public function lastInsertId();

    /**
     * 接続を閉じる
     */
    public function close();
}
 