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
 * URLからテンプレートパスを取得するための起動処理です。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Bootstrap_TemplateName
{

    public static function start()
    {
        // REQUEST URIから実際に出力するテンプレートファイルを特定
        $attributes = Vizualizer::attr();
        $attributes["templateName"] = str_replace("?" . $_SERVER["QUERY_STRING"], "", $_SERVER["REQUEST_URI"]);
        if (VIZUALIZER_SUBDIR != "") {
            if (strpos($attributes["templateName"], VIZUALIZER_SUBDIR) === 0) {
                $attributes["templateName"] = substr($attributes["templateName"], strlen(VIZUALIZER_SUBDIR));
            }
        }

        // テンプレートにシンボリックリンクを作成する。
        if (Vizualizer_Configure::get("site_home") !== null && Vizualizer_Configure::get("site_home") !== "") {
            if (!is_dir(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_contents")) {
                mkdir(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_contents");
            }
            if (!file_exists(VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_contents" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_domain"))) {
                Vizualizer_Logger::writeDebug("CREATE SYMBOLIC LINK : " . VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_contents" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_domain") . " => " . Vizualizer_Configure::get("site_home"));
                symlink(Vizualizer_Configure::get("site_home"), VIZUALIZER_SITE_ROOT . DIRECTORY_SEPARATOR . "_contents" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_domain"));
            }
            if (is_writable(Vizualizer_Configure::get("site_home"))) {
                if (!file_exists(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "mobile")) {
                    symlink(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "default", Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "mobile");
                }
                if (!file_exists(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "sphone")) {
                    symlink(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "default", Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "sphone");
                }
                if (!file_exists(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "iphone")) {
                    symlink(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "sphone", Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "iphone");
                }
                if (!file_exists(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "android")) {
                    symlink(Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "sphone", Vizualizer_Configure::get("site_home") . DIRECTORY_SEPARATOR . "android");
                }
            }
        }

        // ユーザーのテンプレートを取得する。
        if (Vizualizer_Configure::get("device") !== null) {
            if (Vizualizer_Configure::get("device")->isMobile()) {
                if (Vizualizer_Configure::get("device")->isSmartPhone()) {
                    if (Vizualizer_Configure::get("device")->getDeviceType() == "iPhone") {
                        $attributes["userTemplate"] = DIRECTORY_SEPARATOR . "iphone";
                    } elseif (Vizualizer_Configure::get("device")->getDeviceType() == "Android") {
                        $attributes["userTemplate"] = DIRECTORY_SEPARATOR . "android";
                    }
                    $attributes["userTemplate"] = DIRECTORY_SEPARATOR . "sphone";
                } else {
                    $attributes["userTemplate"] = DIRECTORY_SEPARATOR . "mobile";
                }
            } else {
                $attributes["userTemplate"] = DIRECTORY_SEPARATOR . "default";
            }
        } else {
            $attributes["userTemplate"] = DIRECTORY_SEPARATOR . "default";
        }

        // テンプレートがディレクトリかどうか調べ、ディレクトリの場合はファイル名に落とす。
        // 呼び出し先がディレクトリで最後がスラッシュでない場合は最後にスラッシュを補完
        if (is_dir(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"])) {
            if (is_dir(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"]) && substr($attributes["templateName"], -1) != DIRECTORY_SEPARATOR) {
                $attributes["templateName"] .= DIRECTORY_SEPARATOR;
            }
            if (substr($attributes["templateName"], -1) == DIRECTORY_SEPARATOR) {
                if (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"] . "index.html")) {
                    $attributes["templateName"] .= "index.html";
                } elseif (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"] . "index.htm")) {
                    $attributes["templateName"] .= "index.htm";
                } elseif (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"] . "index.xml")) {
                    $attributes["templateName"] .= "index.xml";
                } else {
                    // いずれも存在しない場合はダミーとしてindex.htmlを設定しておく
                    $attributes["templateName"] .= "index.html";
                }
            }
        }
        if (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"]) || is_dir(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"])) {
            if (is_dir(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"]) && substr($attributes["templateName"], -1) != DIRECTORY_SEPARATOR) {
                $attributes["templateName"] .= DIRECTORY_SEPARATOR;
            }
            // 呼び出し先がスラッシュで終わっている場合にはファイル名を補完
            if (substr($attributes["templateName"], -1) == DIRECTORY_SEPARATOR) {
                if (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"] . "index.html")) {
                    $attributes["templateName"] .= "index.html";
                } elseif (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"] . "index.htm")) {
                    $attributes["templateName"] .= "index.htm";
                } elseif (file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . $attributes["templateName"] . "index.xml")) {
                    $attributes["templateName"] .= "index.xml";
                } else {
                    // いずれも存在しない場合はダミーとしてindex.htmlを設定しておく
                    $attributes["templateName"] .= "index.html";
                }
            }
        }
        if (substr($attributes["templateName"], -1) == DIRECTORY_SEPARATOR) {
            // いずれも存在しない場合はダミーとしてindex.htmlを設定しておく
            $attributes["templateName"] .= "index.html";
        }

        // テンプレートの存在するパスを取得する。
        define("TEMPLATE_DIRECTORY", dirname($attributes["templateName"]));
    }

    /**
     * 終了処理です。
     * ここでは何も行いません。
     */
    public static function stop()
    {
    }
}
