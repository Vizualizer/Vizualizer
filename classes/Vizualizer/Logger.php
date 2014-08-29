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
 * ログ出力用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Logger
{
    public static $logFilePrefix = "";
    public static $logOutputStandard = false;

    // ログの種別
    const LOG_ERROR = "error";
    const LOG_ALERT = "alert";
    const LOG_INFO = "info";
    const LOG_DEBUG = "debug";

    /**
     * メッセージログを出力する。
     *
     * @param string $prefix ログの種別
     * @param string $message エラーメッセージ
     * @param Exception $exception エラーの原因となった例外オブジェクト
     */
    private static function writeMessage($prefix, $message, $exception = null)
    {
        try {
            if (Vizualizer_Configure::get("site_code") !== null && Vizualizer_Configure::get("site_code") !== "") {
                $siteCode = self::$logFilePrefix . Vizualizer_Configure::get("site_code");
            } else {
                $siteCode = self::$logFilePrefix . "default";
            }
            // ログディレクトリが無い場合は自動的に作成
            $logHome = Vizualizer_Configure::get("log_root") . DIRECTORY_SEPARATOR;
            if (!is_dir($logHome)) {
                mkdir($logHome);
                @chmod($logHome, 0777);
            }
            if (is_dir($logHome) && is_writable($logHome)) {
                // 現在のログファイルが10MB以上の場合ローテーションする。
                if (file_exists($logHome . $siteCode . ".log") && filesize($logHome . $siteCode . ".log") > 1024 * 1024 * 10) {
                    if(($fp = fopen($logHome . $siteCode . ".log", "r")) !== FALSE){
                        if(flock($fp, LOCK_EX | LOCK_NB)){
                            $logHistorys = Vizualizer_Configure::get("max_logs");
                            for ($index = $logHistorys - 1; $index > 0; $index --) {
                                if (file_exists($logHome . $siteCode . "_" . $index . ".log")) {
                                    @rename($logHome . $siteCode . "_" . $index . ".log", $logHome . $siteCode . "_" . ($index + 1) . ".log");
                                }
                            }
                            @rename($logHome . $siteCode . ".log", $logHome . $siteCode . "_1.log");
                            flock($fp, LOCK_UN);
                        }
                        fclose($fp);
                    }
                }

                // ログファイルに記載
                $logFile = $logHome . $siteCode . ".log";
                if (($fp = fopen($logFile, "a+")) !== FALSE) {
                    if (class_exists("VizualizerAdmin")) {
                        $operator = Vizualizer_Session::get(VizualizerAdmin::SESSION_KEY);
                        if (is_array($operator) && array_key_exists("operator_id", $operator) && $operator["operator_id"] > 0) {
                            $prefix .= "][".$operator["login_id"];
                        }
                    }
                    fwrite($fp, "[" . $_SERVER["SERVER_NAME"] . "][" . Vizualizer_Data_Calendar::now() . "][" . $prefix . "]" . $message . "\r\n");
                    if(self::$logOutputStandard){
                        echo "[" . $_SERVER["SERVER_NAME"] . "][" . Vizualizer_Data_Calendar::now() . "][" . $prefix . "]" . $message . "\r\n";
                    }
                    if ($exception != null) {
                        fwrite($fp, "[" . $_SERVER["SERVER_NAME"] . "][" . Vizualizer_Data_Calendar::now() . "][" . $prefix . "]" . $exception->getMessage() . "\r\n");
                        if(self::$logOutputStandard){
                            echo "[" . $_SERVER["SERVER_NAME"] . "][" . Vizualizer_Data_Calendar::now() . "][" . $prefix . "]" . $exception->getMessage() . "\r\n";
                        }
                        fwrite($fp, "[" . $_SERVER["SERVER_NAME"] . "][" . Vizualizer_Data_Calendar::now() . "][" . $prefix . "]" . $exception->getTraceAsString());
                        if(self::$logOutputStandard){
                            echo "[" . $_SERVER["SERVER_NAME"] . "][" . Vizualizer_Data_Calendar::now() . "][" . $prefix . "]" . $exception->getTraceAsString();
                        }
                    }
                    fclose($fp);
                    @chmod($logFile, 0666);
                }
            }
        } catch (Exception $e) {
            // エラーログ出力に失敗した場合は無限ネストの可能性があるため、例外を無効にする。
        }
    }

    /**
     * エラーログを出力する。
     *
     * @param s string $message エラーメッセージ
     * @param s Exception $exception エラーの原因となった例外オブジェクト
     */
    public static function writeError($message, $exception = null)
    {
        self::writeMessage(self::LOG_ERROR, $message, $exception);
    }

    /**
     * 警告ログを出力する。
     *
     * @param s string $message エラーメッセージ
     * @param s Exception $exception エラーの原因となった例外オブジェクト
     */
    public static function writeAlert($message)
    {
        self::writeMessage(self::LOG_ALERT, $message);
    }

    /**
     * 情報ログを出力する。
     *
     * @param s string $message エラーメッセージ
     * @param s Exception $exception エラーの原因となった例外オブジェクト
     */
    public static function writeInfo($message)
    {
        self::writeMessage(self::LOG_INFO, $message);
    }

    /**
     * デバッグログを出力する。
     *
     * @param s string $message エラーメッセージ
     * @param s Exception $exception エラーの原因となった例外オブジェクト
     */
    public static function writeDebug($message)
    {
        if (Vizualizer_Configure::get("debug")) {
            self::writeMessage(self::LOG_DEBUG, $message);
        }
    }
}
