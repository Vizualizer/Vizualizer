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
 * データベースのインスタンスを生成するためのファクトリクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Database_Factory
{

    const MODE_WRITE = "write";
    const MODE_READ = "read";

    /**
     *
     * @var array[string] データベースの接続情報を保持するインスタンス属性
     */
    private static $configures;

    /**
     *
     * @var array[PDOConnection] データベースの接続を保持するインスタンス属性
     */
    private static $connections;

    /**
     * データベースファクトリクラスを初期化します。
     *
     * @param array[string] $configures データベースの接続情報
     */
    public static function initialize($configures)
    {
        self::$configures = $configures;
        self::refresh();
    }

    /**
     * データベースの接続をリセットします。
     */
    public static function refresh()
    {
        self::$connections = array();
    }

    /**
     * データベースの設定情報を取得します。
     *
     * @param string $code データベース設定の元となるキー
     * @return array[string] データベースの接続情報
     */
    private static function getConfigure($code = "default")
    {
        if(!array_key_exists($code, self::$configures)){
            $code = "default";
        }
        if(array_key_exists($code, self::$configures)){
            return self::$configures[$code];
        }
        return array();
    }

    /**
     * データベースの書き込み用接続を取得します。
     *
     * @param string $code データベース設定の元となるキー
     * @return Vizualizer_Database_Connection データベースの接続
     */
    private static function getConnection($code = "default", $mode = self::MODE_WRITE)
    {

        // DBのコネクションが設定されていない場合は接続する。
        if (!array_key_exists($code . "_" . $mode, self::$connections)) {
            $confs = Vizualizer_Database_Factory::getConfigure($code);
            $conf = $confs[$mode];

            try {
                // 設定に応じてDBに接続
                switch ($conf["dbtype"]) {
                    case "mysql":
                        self::$connections[$code . "_" . $mode] = new Vizualizer_Database_Mysql_Connection($conf);
                        break;
                }
            } catch (PDOException $e) {
                // 接続に失敗した場合にはデータベース例外を発行
                throw new Vizualizer_Exception_Database($e);
            }
        }
        return Vizualizer_Database_Factory::$connections[$code . "_" . $mode];
    }

    /**
     * トランザクションを開始して、書き込み用の接続を取得する。
     * @param string $code
     * @return Vizualizer_Database_Connection|boolean
     */
    public static function begin($code = "default")
    {
        $connection = self::getConnection($code);
        if ($connection instanceof Vizualizer_Database_Connection) {
            $connection->begin();
            return $connection;
        }
        return false;
    }

    /**
     * 読み込み用の接続を取得する。
     * @param unknown $code
     * @return Vizualizer_Database_Connection|boolean
     */
    public static function conn($code = "default"){
        $connection = self::getConnection($code, self::MODE_READ);
        if ($connection instanceof Vizualizer_Database_Connection) {
            return $connection;
        }
        return false;
    }

    /**
     * トランザクションをコミットする。
     * @param Vizualizer_Database_Connection $connection
     */
    public static function commit($connection)
    {
        if ($connection instanceof Vizualizer_Database_Connection) {
            $connection->commit();
        }
    }

    /**
     * トランザクションをロールバックする。
     * @param Vizualizer_Database_Connection $connection
     */
    public static function rollback($connection)
    {
        if ($connection instanceof Vizualizer_Database_Connection) {
            $connection->rollback();
        }
    }

    /**
     * 接続を全て閉じる。
     */
    public static function close()
    {
        foreach (self::$connections as $code => $connection) {
            $connection->close();
            unset(self::$connections[$code]);
        }
    }

}
