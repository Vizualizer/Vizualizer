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
 * エラーメッセージの表示制御を行うための起動処理です。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Bootstrap_ErrorMessage
{

    public static function start()
    {
        // エラーメッセージを限定させる。
        if (Vizualizer_Configure::get("display_error") != "On") {
            Vizualizer_Configure::set("display_error", "Off");
        }
        if (Vizualizer_Configure::get("debug")) {
            if (defined("E_DEPRECATED")) {
                error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
            } else {
                error_reporting(E_ALL & ~E_STRICT);
            }
            ini_set('display_errors', Vizualizer_Configure::get("display_error"));
            ini_set('log_errors', 'On');
        } else {
            error_reporting(E_ERROR);
            ini_set('display_errors', Vizualizer_Configure::get("display_error"));
            ini_set('log_errors', 'On');
        }
    }

    /**
     * 終了処理です。
     * ここでは何も行いません。
     */
    public static function stop()
    {
    }
}
