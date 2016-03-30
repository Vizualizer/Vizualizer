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
 * ページ表示用のテンプレートクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Template_Smarty extends Vizualizer_Template
{

    private $template;

    /**
     * コンストラクタです。ページテンプレートを初期化します。
     *
     * @access public
     */
    public function __construct()
    {
        // コアの処理を設定する。
        if (!defined("SMARTY_LOCAL_SCOPE")) {
            define('SMARTY_LOCAL_SCOPE', 0);
        }
        $this->core = new Smarty();

        // 文字列用のリソースを追加
        $this->core->registerResource("str", array(array("Vizualizer_Template_Smarty_StringResrouce", "get_template"),
            array("Vizualizer_Template_Smarty_StringResrouce", "get_timestamp"),
            array("Vizualizer_Template_Smarty_StringResrouce", "db_get_secure"),
            array("Vizualizer_Template_Smarty_StringResrouce", "db_get_trusted")));

        $attributes = Vizualizer::attr();

        // テンプレートのディレクトリとコンパイルのディレクトリをフレームワークのパス上に展開
        $this->core->setTemplateDir($this->template_dir = array(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . "/"));
        if (!is_dir(VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty")) {
            mkdir(VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty");
        }
        if (!is_dir(VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_code") . "/")) {
            mkdir(VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_code") . "/");
        }
        if (!is_dir(VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_code") . $attributes["userTemplate"] . "/")) {
            mkdir(VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_code") . $attributes["userTemplate"] . "/");
        }
        $this->core->compile_dir = VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache_smarty" . DIRECTORY_SEPARATOR . Vizualizer_Configure::get("site_code") . $attributes["userTemplate"] . "/";

        // プラグインのディレクトリを追加する。
        $this->core->addPluginsDir(VIZUALIZER_ROOT . DIRECTORY_SEPARATOR . "smarty" . DIRECTORY_SEPARATOR);

        // デリミタを変更する。
        $this->core->left_delimiter = "<!--{";
        $this->core->right_delimiter = "}-->";

        // モジュール呼び出し用のフィルタを設定する。
        if (!isset($this->core->autoload_filters["pre"])) {
            $this->core->autoload_filters["pre"] = array();
        }
        $this->core->autoload_filters["pre"][] = "loadmodule";

        // デフォルトのアサインを設定
        $this->initialAssign();
    }

    public function assign($tpl_var, $value = null, $nocache = false, $scope = SMARTY_LOCAL_SCOPE)
    {
        return $this->core->assign($tpl_var, $value, $nocache, $scope);
    }

    public function fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false)
    {
        $attributes = Vizualizer::attr();

        // 標準で使用できるパラメータを登録
        $templateEngine = $attributes["template"];
        $templateEngine->assign("configure", Vizualizer_Configure::values());
        $templateEngine->assign("post", Vizualizer::request());
        $templateEngine->assign("attr", $attributes);
        $templateEngine->assign("session", Vizualizer_Session::values());
        $templateEngine->assign("sessionName", session_name());
        $templateEngine->assign("sessionId", session_id());

        // リソースの利用を判定
        $prefix = substr($template, 0, strpos($template, ":"));
        if(ctype_alpha($prefix) && $prefix != "file"){
            return $this->core->fetch($template, $cache_id, $compile_id, $parent, $display);
        }else{
            if(file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . "/" . $template)) {
                return $this->core->fetch($template, $cache_id, $compile_id, $parent, $display);
            } elseif(file_exists(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . "/err404.html")) {
                return $this->core->fetch("err404.html", $cache_id, $compile_id, $parent, $display);
            }else{
                header("HTTP/1.0 404 Not Found");
                echo "ファイルが存在しません。";
                exit;
            }
        }
    }

    /**
     * テンプレートに割り当てた変数を取得する。
     * @param string $varname 取得する変数のキー名、省略した場合は、全ての変数を取得する。
     */
    public function getVars($varname = "")
    {
        if (!empty($varname)) {
            return $this->core->tpl_vars[$varname]->value;
        } else {
            return $this->core->tpl_vars;
        }
    }

}
