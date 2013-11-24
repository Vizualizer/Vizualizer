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
                $siteCode = Vizualizer_Configure::get("site_code");
            } else {
                $siteCode = "default";
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
                    $logHistorys = Vizualizer_Configure::get("max_logs");
                    for ($index = $logHistorys - 1; $index > 0; $index --) {
                        if (file_exists($logHome . $siteCode . "_" . $index . ".log")) {
                            rename($logHome . $siteCode . "_" . $index . ".log", $logHome . $siteCode . "_" . ($index + 1) . ".log");
                        }
                    }
                    rename($logHome . $siteCode . ".log", $logHome . $siteCode . "_1.log");
                }

                // ログファイルに記載
                $logFile = $logHome . $siteCode . ".log";
                if (($fp = fopen($logFile, "a+")) !== FALSE) {
                    fwrite($fp, "[" . $_SERVER["SERVER_NAME"] . "][" . date("Y-m-d H:i:s") . "][" . $prefix . "]" . $message . "\r\n");
                    if ($exception != null) {
                        fwrite($fp, "[" . $_SERVER["SERVER_NAME"] . "][" . date("Y-m-d H:i:s") . "][" . $prefix . "]" . $exception->getMessage() . "\r\n");
                        fwrite($fp, "[" . $_SERVER["SERVER_NAME"] . "][" . date("Y-m-d H:i:s") . "][" . $prefix . "]" . $exception->getTraceAsString());
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
