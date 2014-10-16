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
 * 一覧取得用のモジュールクラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_List extends Vizualizer_Plugin_Module
{

    private $condition = array();

    private $groupBy = "";

    protected function addCondition($key, $value)
    {
        $this->condition[$key] = $value;
    }

    protected function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    protected function executeImpl($params, $type, $name, $result, $defaultSortKey = "create_time")
    {
        $post = Vizualizer::request();
        // セレクトモードの時は通常の検索条件を適用しない
        if ($params->check("mode", "normal") == "select") {
            $savedPost = $post->export();
            $selectSearch = array();
            if($params->check("selectSearchKeys") && array_key_exists("search", $savedPost) && is_array($savedPost["search"])){
                $selectKeys = explode(",", $params->get("selectSearchKeys"));
                foreach($selectKeys as $key){
                    if(array_key_exists($key, $savedPost["search"])){
                        $selectSearch[$key] = $savedPost["search"][$key];
                    }
                }
            }
            Vizualizer::request()->set("search", $selectSearch);
        }
        if (!$params->check("search") || isset($post[$params->get("search")])) {
            // サイトデータを取得する。
            $loader = new Vizualizer_Plugin($type);
            $model = $loader->loadModel($name);

            // カテゴリが選択された場合、カテゴリの商品IDのリストを使う
            $conditions = $this->condition;
            if (is_array($post["search"])) {
                foreach ($post["search"] as $key => $value) {
                    if (!$this->isEmpty($value)) {
                        if ($params->get("mode", "list") != "select" || !$params->check("select") || $key != substr($params->get("select"), 0, strpos($params->get("select"), "|"))) {
                            $conditions[$key] = $value;
                        }
                    }
                }
            }

            // 追加の検索条件があれば設定
            if ($params->check("wkey")) {
                $conditions[$params->check("wkey")] = $params->check("wvalue");
            }

            $attr = Vizualizer::attr();
            if ($this->groupBy) {
                $model->setGroupBy($this->groupBy);
            }

            // 並べ替え順序が指定されている場合に適用
            $sortOrder = "";
            $sortReverse = false;
            if ($params->check("sort_key")) {
                $sortOrder = $post[$params->get("sort_key")];
                if ($this->isEmpty($sortOrder)) {
                    $sortOrder = $defaultSortKey;
                    $sortReverse = true;
                } elseif (preg_match("/^rev@/", $sortOrder) > 0) {
                    list ($dummy, $sortOrder) = explode("@", $sortOrder);
                    $sortReverse = true;
                }
            } elseif ($params->check("sort")) {
                $sortOrder = $params->check("sort");
                if (preg_match("/^rev@/", $sortOrder) > 0) {
                    list ($dummy, $sortOrder) = explode("@", $sortOrder);
                    $sortReverse = true;
                }
            }

            $models = $model->findAllBy($conditions, $sortOrder, $sortReverse);
            if ($params->get("mode", "list") == "list") {
                $attr[$result] = $models;
            } elseif ($params->get("mode", "list") == "select") {
                $attr[$result] = array();
                if ($params->check("select")) {
                    list ($select_key, $select_value) = explode("|", $params->get("select"));
                    $selection = array();
                    foreach ($models as $model) {
                        $selection[$model->$select_key] = $model->$select_value;
                    }
                    $attr[$result] = $selection;
                }
            }
            if ($params->check("mode", "normal") == "select") {
                Vizualizer::request()->import($savedPost);
            }
        }
    }
}
