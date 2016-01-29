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
 * モジュールマップの管理を行うクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_FeatureMap
{

    private static $featureMap = array();

    /**
     * 指定されたパスのモジュールが存在するか取得する
     *
     * @param string $path 指定されたパス
     * @return boolean パスが存在する場合はtrue、しない場合はfalse
     */
    public static function exists($path)
    {
        return array_key_exists($path, self::$featureMap);
    }

    /**
     * 指定されたパスのモジュール情報を取得する。
     *
     * @param string $path 指定されたパス
     * @return mixed パスに対応するモジュールの情報
     */
    public static function get($path)
    {
        if (self::exists($path)) {
            return self::$featureMap[$path];
        }
        return null;
    }

    /**
     * 指定されたパスにモジュール基本設定を設定する。
     *
     * @param string $path パス
     * @param string $feature モジュール名
     * @param string $type モジュールの種別
     * @param mixed $args モジュールに設定する追加パラメータ
     */
    public static function set($path, $feature, $type, $args = array())
    {
        self::$featureMap[$path] = array(
            "feature" => $feature,
            "type" => $type,
            "args" => $args
        );
    }

    /**
     * 指定されたパスにモジュール追加パラメータを設定する。
     *
     * @param string $path パス
     * @param string $key 追加パラメータキー
     * @param mixed $value 追加パラメータ値
     */
    public static function addFeatureArgs($path, $key, $value)
    {
        if (self::exists($path)) {
            self::$featureMap[$path]["args"][$key] = $value;
        }
    }

    /**
     * 指定されたパスのモジュールを実行する。
     *
     * @param string $path パス
     */
    public static function execute($path)
    {
        if (!self::exists($path)) {
            $info = pathinfo($path);
            $index = strrpos($info["filename"], "_");
            if ($index > 1) {
                $mode = substr($info["filename"], $index + 1);
                $info["filename"] = substr($info["filename"], 0, $index);
                $path = $info["dirname"]."/".$info["filename"].".".$info["extension"];
            }
        } else {
            $mode = "index";
        }
        if (self::exists($path)) {
            $featureClass = null;
            if (class_exists(self::$featureMap[$path]["feature"])) {
                $featureName = self::$featureMap[$path]["feature"];
                $featureClass = new $featureName($info, self::$featureMap[$path]["type"]);
            } elseif (class_exists("Vizualizer_Feature_".self::$featureMap[$path]["feature"])) {
                $featureName = "Vizualizer_Feature_".self::$featureMap[$path]["feature"];
                $featureClass = new $featureName($info, self::$featureMap[$path]["type"]);
            }
            if ($featureClass !== null) {
                return $featureClass->execute($mode, self::$featureMap[$path]["args"]);
            }
        }
        return false;
    }

    /**
     * 設定のリストを取得
     */
    public static function values()
    {
        return self::$featureMap;
    }
}