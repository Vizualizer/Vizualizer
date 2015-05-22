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
 * 設定の管理を行うクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Configure
{

    private static $configure = array();

    /**
     * 指定されたキーのパラメータが存在するか取得する
     *
     * @param string $key 指定されたキー
     * @return boolean キーが存在する場合はtrue、しない場合はfalse
     */
    public static function exists($key)
    {
        return array_key_exists($key, self::$configure);
    }

    /**
     * 指定されたキーのパラメータを取得する。
     *
     * @param string $key 指定されたキー
     * @return mixed キーに対応するパラメータの値
     */
    public static function get($key)
    {
        if (self::exists($key)) {
            return self::$configure[$key];
        }
        return null;
    }

    /**
     * 指定されたキーにパラメータを設定する。
     *
     * @param string $key 設定するパラメータのキー
     * @param mixed $value 設定するパラメータの値
     */
    public static function set($key, $value)
    {
        self::$configure[$key] = $value;
    }

    /**
     * 設定のリストを取得
     */
    public static function values()
    {
        return self::$configure;
    }
}