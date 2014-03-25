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
 * 各種モジュールを読み込むためのクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin
{
    /**
     * プレフィックス利用フラグ
     */
    private $prefix;

    /**
     * 読み込む先のネームスペース
     */
    private $namespace;

    /**
     * テーブルキャッシュ
     */
    private $tables;

    /**
     * コンストラクタです。
     */
    public function __construct($namespace, $prefix = true)
    {
        $this->prefix = $prefix;
        $this->namespace = strtoupper(substr($namespace, 0, 1)) . strtolower(substr($namespace, 1));
        $this->tables = array();
    }

    /**
     * 拡張ライブラリファイルを読み込む
     *
     * @param string $type 拡張ファイルの種別
     * @param string $name 拡張ファイルのオブジェクト名
     */
    private function load($type, $name, $params = array())
    {
        try {
            $names = explode(".", $name);
            $className = ($this->prefix?"Vizualizer":"") . $this->namespace . "_" . $type . "_" . implode("_", $names);
            if (class_exists($className)) {
                Vizualizer_Logger::writeDebug("Loading: " . $className . "(" . memory_get_usage() . ")");
                return new $className($params);
            }else{
                $className = "Vizualizer_" . $type . "_" . $this->namespace . "_" . implode("_", $names);
                if (class_exists($className)) {
                    Vizualizer_Logger::writeDebug("Loading: " . $className . "(" . memory_get_usage() . ")");
                    return new $className($params);
                }
            }
            Vizualizer_Logger::writeDebug("No Plugin : " . $className);
            return null;
        } catch (Exception $e) {
            Vizualizer_Logger::writeError("Failed to load plugin", $e);
        }
    }

    /**
     * モジュールクラスのファイルを読み込む
     *
     * @param s string $name モジュール呼び出し名
     */
    function loadModule($name, $params = array())
    {
        return $this->load("Module", $name, $params);
    }

    /**
     * モデルクラスのファイルを読み込む
     *
     * @param s string $name モデル呼び出し名
     */
    function loadModel($name, $params = array())
    {
        return $this->load("Model", $name, $params);
    }

    /**
     * テーブルクラスのファイルを読み込む
     *
     * @param s string $name テーブル呼び出し名
     */
    function loadTable($name)
    {
        return $this->load("Table", $name);
    }

    /**
     * バッチクラスのファイルを読み込む
     *
     * @param s string $name バッチ呼び出し名
     */
    function loadBatch($name)
    {
        return $this->load("Batch", $name);
    }

    /**
     * JSONクラスのファイルを読み込む
     *
     * @param s string $name JSON呼び出し名
     */
    function loadJson($name)
    {
        return $this->load("Json", $name);
    }
}
