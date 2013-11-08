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
 * セッションの管理を行うクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Session
{

    private static $started = false;

    private static $session = array();

    /**
     * セッションのクラスを初期化する。
     */
    public static function startup()
    {
        if (!self::$started) {
            self::$session = $_SESSION;
            self::$started = true;
        }
    }

    /**
     * セッションのクラスを解放する
     */
    public static function shutdown()
    {
        $_SESSION = self::$session;
    }

    /**
     * 指定されたキーのパラメータを取得する。
     *
     * @param string $key 指定されたキー
     * @return mixed キーに対応するパラメータの値
     */
    public static function get($key)
    {
        if (array_key_exists($key, self::$session)) {
            return self::$session[$key];
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
        self::$session[$key] = $value;
    }

    /**
     * 指定されたキーのパラメータを削除する。
     *
     * @param string $key
     */
    public static function remove($key)
    {
        unset(self::$session[$key]);
    }

    /**
     * 設定のリストを取得
     */
    public static function values()
    {
        return self::$session;
    }
}