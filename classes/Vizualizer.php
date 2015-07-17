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

// Vizualizerの初期化
Vizualizer::initialize();

/**
 * フレームワークの起点となるクラス
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer
{

    /**
     * 入力パラメータ保存用の定数
     *
     * @var string
     */
    const INPUT_KEY = "INPUT_DATA";

    /**
     * エラーリスト保存用の定数
     *
     * @var string
     */
    const ERROR_KEY = "ERROR_LIST_KEY";

    /**
     * システムエラーのタイプ
     *
     * @var string
     */
    const ERROR_TYPE_SYSTEM = "SYSTEM";

    /**
     * データベースエラーのタイプ
     *
     * @var string
     */
    const ERROR_TYPE_DATABASE = "DATABASE";

    /**
     * 不明なエラーのタイプ
     *
     * @var string
     */
    const ERROR_TYPE_UNKNOWN = "UNKNOWN";

    /**
     * リクエストパラメータのインスタンス用
     */
    private static $parameters;

    /**
     * 属性のインスタンス用
     */
    private static $attributes;

    /**
     * フレームワークの初期化処理を行うメソッドです。
     */
    final public static function initialize()
    {
        // 出力バッファをリセットする。
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();

        // IISの場合、HTTPSでなく、SSLがonに設定されるため、パラメータを転記する。
        if (array_key_exists("SSL", $_SERVER) && $_SERVER["SSL"] === "on") {
            $_SERVER["HTTPS"] = 'on';
        }

        // LBやRPを介している場合、HTTPSのパラメータが正常に取れないため、パラメータを転記する。
        if (array_key_exists("X_FORWARDED_PROTO", $_SERVER) && $_SERVER["X_FORWARDED_PROTO"] === "https") {
            $_SERVER["HTTPS"] = "on";
        }
        if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https") {
            $_SERVER["HTTPS"] = "on";
        }

        // システムのルートディレクトリを設定
        if (!defined('VIZUALIZER_ROOT')) {
            define('VIZUALIZER_ROOT', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".."));
        }

        // システムのクラスディレクトリを設定
        if (!defined('VIZUALIZER_CLASSES_DIR')) {
            define('VIZUALIZER_CLASSES_DIR', VIZUALIZER_ROOT . "/classes");
        }

        // キャッシュのベースディレクトリを設定
        if (!defined('VIZUALIZER_CACHE_ROOT')) {
            $cacheBase = realpath(".");
            if (!is_writable($cacheBase)) {
                $cacheBase = VIZUALIZER_ROOT;
                while (!is_writable($cacheBase)) {
                    // ルートディレクトリまで書き込みできない場合はエラー終了。
                    if ($cacheBase == "/") {
                        die("ログ／キャッシュ用に書き込み可能なディレクトリが必要です");
                        break;
                    }
                    $cacheBase = realpath($cacheBase . "/../");
                }
            }
            define('VIZUALIZER_CACHE_ROOT', $cacheBase);
        }

        // パラメータをnullで初期化
        self::$parameters = null;

        // 属性を初期化
        self::$attributes = null;
    }

    /**
     * フレームワークの起動処理を行うメソッドです。
     */
    final public static function startup($siteRoot = ".")
    {
        // システムのルートディレクトリを設定
        if (!defined('VIZUALIZER_SITE_ROOT')) {
            define('VIZUALIZER_SITE_ROOT', realpath($siteRoot));
        }

        // インストールの処理を実行
        if (array_key_exists("argc", $_SERVER) && $_SERVER["argc"] > 2) {
            // Bootstrapを実行する。
            Vizualizer_Bootstrap::register(10, "PhpVersion");
            Vizualizer_Bootstrap::register(20, "Configure");
            Vizualizer_Bootstrap::register(30, "ErrorMessage");
            Vizualizer_Bootstrap::register(40, "Timezone");
            Vizualizer_Bootstrap::register(50, "Locale");
            Vizualizer_Bootstrap::register(60, "UserAgent");
            Vizualizer_Bootstrap::startup();

            // システム実行時の時間を記録するため、カレンダーを取得する。
            Vizualizer_Data_Calendar::get();

            $class =& $_SERVER["argv"][2];
            if ($_SERVER["argv"][1] === "install") {
                try {
                    $class::install();
                    echo "Package " . $class . " installed successfully\r\n";
                } catch (Exception $e) {
                    echo "Package " . $class . " install failed\r\n";
                }
            } elseif ($_SERVER["argv"][1] === "batch") {
                try {
                    $batch = new $class();
                    $batch->execute($_SERVER["argv"]);
                    echo "Batch " . $class . " executed successfully\r\n";
                } catch (Exception $e) {
                    echo "Batch " . $class . " execution failed\r\n";
                }
            }
            exit();
        } else {
            // ドキュメントルートを調整
            $_SERVER["DOCUMENT_ROOT"] = realpath($_SERVER["DOCUMENT_ROOT"]);
            if (substr_compare($_SERVER["DOCUMENT_ROOT"], "/", -1)) {
                $_SERVER["DOCUMENT_ROOT"] = substr($_SERVER["DOCUMENT_ROOT"], 0, -1);
            }
            if (strpos(VIZUALIZER_SITE_ROOT, $_SERVER["DOCUMENT_ROOT"]) === 0) {
                $_SERVER['DOCUMENT_ROOT'] = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['SCRIPT_FILENAME']);
            }

            // システムのルートURLへのサブディレクトリを設定
            if (!defined('VIZUALIZER_SUBDIR')) {
                if (substr_compare($_SERVER["DOCUMENT_ROOT"], "/", -1) === 0) {
                    define('VIZUALIZER_SUBDIR', substr(VIZUALIZER_SITE_ROOT, strlen($_SERVER["DOCUMENT_ROOT"]) - 1));
                } else {
                    define('VIZUALIZER_SUBDIR', substr(VIZUALIZER_SITE_ROOT, strlen($_SERVER["DOCUMENT_ROOT"])));
                }
            }

            // システムのルートURLを設定
            if (!defined('VIZUALIZER_URL')) {
                define('VIZUALIZER_URL', "http" . ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] . VIZUALIZER_SUBDIR);
            }
            // Bootstrapを実行する。
            Vizualizer_Bootstrap::register(10, "PhpVersion");
            Vizualizer_Bootstrap::register(20, "Configure");
            Vizualizer_Bootstrap::register(30, "ErrorMessage");
            Vizualizer_Bootstrap::register(40, "Timezone");
            Vizualizer_Bootstrap::register(50, "Locale");
            Vizualizer_Bootstrap::register(60, "UserAgent");
            Vizualizer_Bootstrap::register(70, "SessionId");
            Vizualizer_Bootstrap::register(80, "Session");
            Vizualizer_Bootstrap::register(90, "TemplateName");
            Vizualizer_Bootstrap::startup();
        }

        $prefilters = Vizualizer_Configure::get("prefilters");
        if (is_array($prefilters) && !empty($prefilters)) {
            foreach ($prefilters as $prefilter) {
                $prefilter::prefilter();
            }
        }

        $attr = Vizualizer::attr();
        $info = pathinfo($attr["templateName"]);

        // フレームとコンテンツタイプのオプションを指定
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");

        try{
            switch ($info["extension"]) {
                case "html":
                case "htm":
                case "xml":
                    // テンプレートを生成
                    $templateClass = "Vizualizer_Template_" . Vizualizer_Configure::get("template");
                    $template = new $templateClass();
                    $template->assign("ERRORS", array());

                    // テンプレートを表示
                    $attr["template"] = $template;
                    $template->display(substr($attr["templateName"], 1));
                    break;
                case "php":
                    // PHPを実行する場合、インクルードパスの最優先をテンプレートのディレクトリに設定
                    ini_set("include_path", Vizualizer_Configure::get("site_home") . $attr["userTemplate"] . PATH_SEPARATOR . ini_get("include_path") );

                    $source = file_get_contents(Vizualizer_Configure::get("site_home") . $attr["userTemplate"] . $attr["templateName"]);
                    // 先頭のPHPタグを除去
                    $source = "?>".$source;
                    // バッファを除去
                    ob_end_clean();
                    ob_start();
                    eval($source);
                    // 実行後のデータを取得し、バッファを再度除去
                    $source = ob_get_contents();
                    ob_end_clean();
                    ob_start();
                    // テンプレートを生成
                    $templateClass = "Vizualizer_Template_" . Vizualizer_Configure::get("template");
                    $template = new $templateClass();
                    $template->assign("ERRORS", array());
                    // ソースデータをリソース化
                    $source = "eval:" . $source;
                    // テンプレートを表示
                    $attr["template"] = $template;
                    $template->display($source);
                    break;
                case "json":
                    if (file_exists(Vizualizer_Configure::get("site_home") . $attr["userTemplate"] . $attr["templateName"])) {
                        // バッファをクリアしてJSONの結果を出力する。
                        // テンプレートを生成
                        $templateClass = "Vizualizer_Template_" . Vizualizer_Configure::get("template");
                        $template = new $templateClass();
                        $template->assign("ERRORS", array());

                        // テンプレートを表示
                        $attr["template"] = $template;
                        header("Content-Type: application/json; charset=utf-8");
                        Vizualizer_Parameter::$enableRefresh = false;
                        $template->display(substr($attr["templateName"], 1));
                    } elseif (Vizualizer_Configure::get("json_api_key") == "" || isset($_POST["k"]) && Vizualizer_Configure::get("json_api_key") == $_POST["k"]) {
                        Vizualizer_Parameter::$enableRefresh = false;
                        $post = Vizualizer::request();
                        // コールバックを取得
                        $callback = "";
                        if (!empty($post["callback"])) {
                            $callback = $post["callback"];
                            $post->remove("callback");
                        }
                        $post->remove("_");

                        // JSONモジュールを実行する。
                        $loader = new Vizualizer_Plugin(substr($info["dirname"], 1));
                        $json = $loader->loadJson($info["filename"]);
                        $result = json_encode($json->execute());

                        // バッファをクリアしてJSONの結果を出力する。
                        ob_end_clean();
                        header("Content-Type: application/json; charset=utf-8");
                        if (!empty($callback)) {
                            echo $callback . "(" . $result . ");";
                        } else {
                            echo $result;
                        }
                    } else {
                        header("HTTP/1.0 404 Not Found");
                        exit();
                    }
                    break;
                case "css":
                    header("Content-Type: text/css");
                    echo self::getStaticContents();
                    break;
                case "javascript":
                    header("Content-Type: text/javascript");
                    echo self::getStaticContents();
                    break;
                default:
                    echo self::getStaticContents();
                    break;
            }
        } catch(Exception $e) {
            Vizualizer_Logger::writeError("Uncaught Exeception was throwed.", $e);
            throw $e;
        }
        $postfilters = Vizualizer_Configure::get("postfilters");
        if (is_array($postfilters) && !empty($postfilters)) {
            foreach ($postfilters as $postfilter) {
                $postfilter::postfilter();
            }
        }
    }

    /**
     * 静的コンテンツを取得する。
     */
    protected static function getStaticContents($filter = null)
    {
        $attr = Vizualizer::attr();

        // 静的コンテンツをキャッシュする。
        $contents = Vizualizer_Cache_Factory::create("content" . strtr($attr["userTemplate"] . $attr["templateName"], array("/" => "_")));
        $data = $contents->export();
        if (empty($data)) {
            if (($buffer = file_get_contents(Vizualizer_Configure::get("site_home") . $attr["userTemplate"] . $attr["templateName"])) !== FALSE) {
                if ($filter !== null) {
                    $contents->set("data", $filter($buffer));
                } else {
                    $contents->set("data", $buffer);
                }
            }
            $data = $contents->export();
        }
        return $data["data"];
    }

    /**
     * フレームワークの終了処理を行うメソッドです。
     */
    final public static function shutdown()
    {
        Vizualizer_Bootstrap::shutdown();
    }

    /**
     * フレームワークでエラーが発生してキャッチされなかった場合の処理を記述するメソッドです。
     */
    final public static function error($code, $message, $ex = null)
    {
        // ダウンロードの際は、よけいなバッファリングをクリア
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // エラーログに書き込み
        Vizualizer_Logger::writeError($message . "(" . $code . ")", $ex);

        // カスタムエラーページのパス
        $path = $_SERVER["CONFIGURE"]->site_home . $_SERVER["USER_TEMPLATE"] . DIRECTORY_SEPARATOR . "ERROR_" . $code . ".html";

        // ファイルがある場合はエラーページを指定ファイルで出力
        if (file_exists($path)) {
            try {
                header("HTTP/1.0 " . $code . " " . $message, true, $code);
                header("Status: " . $code . " " . $message);
                header("Content-Type: text/html; charset=utf-8");
                $_SERVER["TEMPLATE"]->display("ERROR_" . $code . ".html");
            } catch (Exception $e) {
                // エラーページでのエラーは何もしない
            }
        } else {
            // エラーページが無い場合はデフォルト
            header("HTTP/1.0 " . $code . " " . $message, true, $code);
            header("Status: " . $code . " " . $message);
            header("Content-Type: text/html; charset=utf-8");
            echo $message;
        }
        exit();
    }

    /**
     * パラメータオブジェクトを取得します。
     *
     * @return Vizualizer_Parameter
     */
    public static function request()
    {
        if (self::$parameters === null) {
            // パラメータを生成
            self::$parameters = new Vizualizer_Parameter();
        }
        return self::$parameters;
    }

    /**
     * 属性を取得します。
     *
     * @return Vizualizer_Attributes
     */
    public static function attr()
    {
        if (self::$attributes === null) {
            // パラメータを生成
            self::$attributes = new Vizualizer_Attributes();
        }
        return self::$attributes;
    }

    /**
     * システムの起動時間（基準時）を取得します。
     */
    public static function now()
    {
        return Vizualizer_Data_Calendar::get();
    }

}
