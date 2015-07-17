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
 * 一覧ダウンロード用のモジュールクラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Download extends Vizualizer_Plugin_Module
{

    private $groupBy = "";

    protected function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    protected function filterData($data){
        return $data;
    }

    protected function executeImpl($params, $type, $name, $result, $defaultSortKey = "create_time")
    {
        if (!$params->check("search") || isset($_POST[$params->get("search")])) {
            $loader = new Vizualizer_Plugin($type);

            // カテゴリが選択された場合、カテゴリの商品IDのリストを使う
            $conditions = array();
            $post = Vizualizer::request();
            if (is_array($post["search"])) {
                foreach ($post["search"] as $key => $value) {
                    if (!$this->isEmpty($value)) {
                        $conditions[$key] = $value;
                    }
                }
            }

            // 並べ替え順序が指定されている場合に適用
            $sortOrder = "";
            $sortReverse = false;
            if ($params->check("sort_key")) {
                $sortOrder = $post[$params->get("sort_key")];
                if ($this->isEmpty($sortOrder)) {
                    $sortOrder = $defaultSortKey;
                    $sortReverse = true;
                } elseif (strpos($sortOrder, "rev@") === 0) {
                    list ($dummy, $sortOrder) = explode("@", $sortOrder);
                    $sortReverse = true;
                }
            }

            $model = $loader->LoadModel($name);

            // 顧客データを検索する。
            if ($this->groupBy) {
                $model->setGroupBy($this->groupBy);
            }
            $result = $model->findAllBy($conditions, $sortOrder, $sortReverse);

            if($params->check("titles")){
                $titles = explode(",", $params->get("titles"));
            }
            $columns = explode(",", $params->get("columns"));

            // ヘッダを送信
            header("Content-Type: application/csv");
            header("Content-Disposition: attachment; filename=\"" . $params->get("prefix", "csvfile") . Vizualizer::now()->date("YmdHis") . ".csv\"");

            ob_end_clean();

            ob_start();

            // CSVヘッダを出力
            $out = fopen("php://output", "w");
            if($params->check("titles")){
                fputcsv($out, $titles);
            }

            foreach ($result as $data) {

                // データが０件以上の場合は繰り返し
                $output = array();
                foreach ($columns as $index => $column) {
                    $output[] = $this->filterData($data->$column);
                }
                fputcsv($out, $output);
            }
            fclose($out);
            echo mb_convert_encoding(ob_get_clean(), "Shift_JIS", "UTF-8");
            exit();
        }
    }
}
