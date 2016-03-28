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
            $serverName = getenv("PHP_SERVER_NAME");
            if (!empty($serverName)) {
                $_SERVER["SERVER_NAME"] = $serverName;
            }else{
                $_SERVER["SERVER_NAME"] = "localhost";
            }
        }

        // デフォルトの設定
        Vizualizer_Configure::set("timezone", "Asia/Tokyo");
        Vizualizer_Configure::set("locale", "ja_JP.UTF-8");
        Vizualizer_Configure::set("select_month", array(
            "01" => "1月", "02" => "2月", "03" => "3月", "04" => "4月", "05" => "5月", "06" => "6月",
            "07" => "7月", "08" => "8月", "09" => "9月", "10" => "10月", "11" => "11月", "12" => "12月"
        ));
        $selectDay = array();
        for ($i = 1; $i <= 31; $i ++) {
            $selectDay[sprintf("%02d", $i)] = $i . "日";
        }
        Vizualizer_Configure::set("select_day", array(
            "01" => "1日", "02" => "2日", "03" => "3日", "04" => "4日", "05" => "5日", "06" => "6日",
            "07" => "7日", "08" => "8日", "09" => "9日", "10" => "10日", "11" => "11日", "12" => "12日",
            "13" => "13日", "14" => "14日", "15" => "15日", "16" => "16日", "17" => "17日", "18" => "18日",
            "19" => "19日", "20" => "20日", "21" => "21日", "22" => "22日", "23" => "23日", "24" => "24日",
            "25" => "25日", "26" => "26日", "27" => "27日", "28" => "28日", "29" => "29日", "30" => "30日",
            "31" => "31日"
        ));
        $selectHour = array();
        $selectHalfHour = array();
        for ($i = 0; $i <= 23; $i ++) {
            $hh = sprintf("%02d", $i);
            $selectHour[$hh . ":00"] = $i . ":00";
            $selectHalfHour[$hh . ":00"] = $i . ":00";
            $selectHalfHour[$hh . ":30"] = $i . ":30";
        }
        Vizualizer_Configure::set("select_hour", array(
            "00:00" => "0:00", "01:00" => "1:00", "02:00" => "2:00", "03:00" => "3:00", "04:00" => "4:00", "05:00" => "5:00",
            "06:00" => "6:00", "07:00" => "7:00", "08:00" => "8:00", "09:00" => "9:00", "10:00" => "10:00", "11:00" => "11:00",
            "12:00" => "12:00", "13:00" => "13:00", "14:00" => "14:00", "15:00" => "15:00", "16:00" => "16:00", "17:00" => "17:00",
            "18:00" => "18:00", "19:00" => "19:00", "20:00" => "20:00", "21:00" => "21:00", "22:00" => "22:00", "23:00" => "23:00"
        ));
        Vizualizer_Configure::set("select_half_hour", array(
            "00:00" => "0:00", "00:30" => "0:30", "01:00" => "1:00", "01:30" => "1:30", "02:00" => "2:00", "02:30" => "2:30",
            "03:00" => "3:00", "03:30" => "3:30", "04:00" => "4:00", "04:30" => "4:30", "05:00" => "5:00", "05:30" => "5:30",
            "06:00" => "6:00", "06:30" => "6:30", "07:00" => "7:00", "07:30" => "7:30", "08:00" => "8:00", "08:30" => "8:30",
            "09:00" => "9:00", "09:30" => "9:30", "10:00" => "10:00", "10:30" => "10:30", "11:00" => "11:00", "11:30" => "11:30",
            "12:00" => "12:00", "12:30" => "12:30", "13:00" => "13:00", "13:30" => "13:30", "14:00" => "14:00", "14:30" => "14:30",
            "15:00" => "15:00", "15:30" => "15:30", "16:00" => "16:00", "16:30" => "16:30", "17:00" => "17:00", "17:30" => "17:30",
            "18:00" => "18:00", "18:30" => "18:30", "19:00" => "19:00", "19:30" => "19:30", "20:00" => "20:00", "20:30" => "20:30",
            "21:00" => "21:00", "21:30" => "21:30", "22:00" => "22:00", "22:30" => "22:30", "23:00" => "23:00", "23:30" => "23:30"
        ));

        Vizualizer_Configure::set("debug", true);
        Vizualizer_Configure::set("debug_level", 99);
        Vizualizer_Configure::set("display_error", "On");
        Vizualizer_Configure::set("session_manager", "");

        // プラグインとテンプレートのパス
        if(!file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "templates")){
            mkdir(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "templates");
            chmod(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "templates", 0777);
        }
        Vizualizer_Configure::set("site_home", VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "templates");
        if(!file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_logs")){
            mkdir(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_logs");
            chmod(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_logs", 0777);
        }
        Vizualizer_Configure::set("log_root", VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_logs");
        Vizualizer_Configure::set("upload_root", VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "upload");
        Vizualizer_Configure::set("max_logs", 100);

        // データベースの接続設定
        Vizualizer_Configure::set("database", array());

        // memcacheのホスト設定
        Vizualizer_Configure::set("memcache", "");

        // コンテンツのmemcache利用設定
        Vizualizer_Configure::set("memcache_contents", false);

        // JSONインターフェイス用キー設定
        Vizualizer_Configure::set("json_key", "");

        // Facebookのプロトコル
        Vizualizer_Configure::set("facebook_protocol", "http");

        // FacebookのAPP ID
        Vizualizer_Configure::set("facebook_app_id", "");

        // FacebookのAPP Secret
        Vizualizer_Configure::set("facebook_app_secret", "");

        // サイトコード
        Vizualizer_Configure::set("site_code", "default");

        // サイト名
        Vizualizer_Configure::set("site_name", "デフォルトサイト");

        // サイトドメイン
        Vizualizer_Configure::set("site_domain", $_SERVER["SERVER_NAME"]);

        // デフォルトのテンプレートエンジン
        Vizualizer_Configure::set("template", "Smarty");

        // 設定ファイルを読み込み
        $siteDomain = Vizualizer_Configure::get("site_domain");
        if (file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure_" . $siteDomain . ".php")) {
            require (VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure_" . $siteDomain . ".php");
        } else {
            // 一つ上の階層の設定がある場合はそちらを見に行く。
            list($dummy, $siteDomain) = explode(".", $siteDomain, 2);
            if(!empty($siteDomain) && file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure_" . $siteDomain . ".php")){
                require (VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure_" . $siteDomain . ".php");
            } elseif (file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure.php")) {
                // ホスト別の設定が無い場合はデフォルトの設定ファイルを使用する。
                require (VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_configure" . DIRECTORY_SEPARATOR . "configure.php");
            }
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
