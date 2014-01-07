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

        $attributes = Vizualizer::attr();

        // テンプレートのディレクトリとコンパイルのディレクトリをフレームワークのパス上に展開
        $this->template_dir = $this->core->template_dir = array(Vizualizer_Configure::get("site_home") . $attributes["userTemplate"] . "/");
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
        return $this->core->fetch($template, $cache_id, $compile_id, $parent, $display);
    }
}
