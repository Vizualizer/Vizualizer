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
 * セッションを初期化するための起動処理です。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Bootstrap_Session
{

    public static function start()
    {
        // セッション管理クラスをインクルード
        switch (Vizualizer_Configure::get("session_manager")) {
            case "":
            case false:
                ini_set("session.save_handler", "files");
                break;
            case "memcached":
                if (strpos(Vizualizer_Configure::get("memcache"), ":") > 0) {
                    list ($host, $port) = explode(":", Vizualizer_Configure::get("memcache"));
                } else {
                    $host = Vizualizer_Configure::get("memcache");
                    $port = 0;
                }
                if (! ($port > 0)) {
                    $port = 11211;
                }
                ini_set("session.save_handler", "memcache");
                ini_set("session.save_path", $host . ":" . $port);
                break;
            default:
                ini_set("session.save_handler", "user");
                $manager = "Vizualizer_Session_Handler_" . str_replace("SessionHandler", "", Vizualizer_Configure::get("session_manager"));
                Vizualizer_Session_Manager::create(new $manager());
                break;
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
 