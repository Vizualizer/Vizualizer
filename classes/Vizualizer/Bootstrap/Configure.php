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
 * 設定読み込み用の起動処理です。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Bootstrap_Configure
{

    /**
     * 起動処理です。
     * 設定ファイルを読み込み、システムの設定を行います。
     */
    public static function start()
    {
        // SERVER_NAMEが未設定の場合はlocalhostを割当
        if (!isset($_SERVER["SERVER_NAME"])) {
            $_SERVER["SERVER_NAME"] = "localhost";
        }

        // デフォルトの設定
        Vizualizer_Configure::set("timezone", "Asia/Tokyo");
        Vizualizer_Configure::set("locale", "ja_JP.UTF-8");
        $selectMonth = array();
        for ($i = 1; $i <= 12; $i ++) {
            $selectMonth[sprintf("%02d", $i)] = $i . "月";
        }
        Vizualizer_Configure::set("select_month", $selectMonth);
        $selectDay = array();
        for ($i = 1; $i <= 31; $i ++) {
            $selectDay[sprintf("%02d", $i)] = $i . "月";
        }
        Vizualizer_Configure::set("select_day", $selectDay);
        Vizualizer_Configure::set("debug", true);
        Vizualizer_Configure::set("display_error", "On");
        Vizualizer_Configure::set("session_manager", "");

        // プラグインとテンプレートのパス
        Vizualizer_Configure::set("site_home", VIZUALIZER_ROOT . DIRECTORY_SEPARATOR . "templates");
        Vizualizer_Configure::set("log_root", VIZUALIZER_ROOT . DIRECTORY_SEPARATOR . "logs");
        Vizualizer_Configure::set("max_logs", 100);

        // データベースの接続設定
        Vizualizer_Configure::set("database", array());

        // memcacheのホスト設定
        Vizualizer_Configure::set("memcache", "");

        // セッションマネージャー設定
        Vizualizer_Configure::set("sessionManager", "");

        // JSONインターフェイス用キー設定
        Vizualizer_Configure::set("json_key", "");

        // Facebookのプロトコル
        Vizualizer_Configure::set("facebook_protocol", "http");

        // FacebookのAPP ID
        Vizualizer_Configure::set("facebook_app_id", "");

        // FacebookのAPP Secret
        Vizualizer_Configure::set("facebook_app_secret", "");

        // サイトコード
        Vizualizer_Configure::set("site_code", "test");

        // FacebookのAPP Secret
        Vizualizer_Configure::set("site_name", "テストサイト");

        // FacebookのAPP Secret
        Vizualizer_Configure::set("site_domain", $_SERVER["SERVER_NAME"]);

        // デフォルトのテンプレート
        Vizualizer_Configure::set("template", "Smarty");

        // 設定ファイルを読み込み
        if (file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure_" . Vizualizer_Configure::get("site_domain") . ".php")) {
            require (VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure_" . Vizualizer_Configure::get("site_domain") . ".php");
        }

        // データベースを初期化する。
        Vizualizer_Database_Factory::initialize(Vizualizer_Configure::get("database"));
    }

    /**
     * 終了処理です。
     * ここでは何も行いません。
     */
    public static function stop()
    {
    }
}
