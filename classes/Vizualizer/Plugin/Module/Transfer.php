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
 * 一覧作成し、外部サーバーへの転送用のモジュールクラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Transfer extends Vizualizer_Plugin_Module
{

    private $groupBy = "";

    protected function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    protected function executeImpl($params, $type, $name, $defaultSortKey = "create_time")
    {
        $post = Vizualizer::request();
        if (!$params->check("search") || isset($post[$params->get("search")])) {
            $loader = new Vizualizer_Plugin($type);

            // カテゴリが選択された場合、カテゴリの商品IDのリストを使う
            $conditions = array();
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
            $result = $model->getAllBy($conditions, $sortOrder, $sortReverse);

            $titles = explode(",", $params->get("titles"));
            $columns = explode(",", $params->get("columns"));

            $basename = uniqid($type . "_" . $name . "_") . ".csv";
            $filename = CLAY_ROOT . DIRECTORY_SEPARATOR . "_uploads" . DIRECTORY_SEPARATOR . $basename;

            if (($fp = fopen($filename, "w+")) !== FALSE) {
                // CSVヘッダを出力
                fwrite($fp, mb_convert_encoding("\"" . implode("\",\"", $titles) . "\"\r\n", "Shift_JIS", "UTF-8"));
                while ($data = $result->next()) {

                    // データが０件以上の場合は繰り返し
                    foreach ($columns as $index => $column) {
                        if ($index > 0)
                            fwrite($fp, ",");
                        fwrite($fp, "\"" . mb_convert_encoding($data[$column], "Shift_JIS", "UTF-8") . "\"");
                    }
                    fwrite($fp, "\r\n");
                }
                fclose($fp);

                // 作成したファイルを転送
                $info = parse_url($params->get("url", ""));
                $info["chost"] = $info["host"];
                if ($info["scheme"] == "https") {
                    $info["chost"] = "ssl://" . $info["host"];
                    if (empty($info["port"])) {
                        $info["port"] = "443";
                    }
                } elseif ($info["scheme"] == "http") {
                    if (empty($info["port"])) {
                        $info["port"] = "80";
                    }
                }
                $protocol = $info["scheme"];
                $chost = $info["chost"];
                $host = $info["host"];
                $port = $info["port"];
                if (($fp = fsockopen($chost, $port)) !== FALSE) {
                    $postdata = "";
                    $postdata .= "POST " . $info["path"] . " HTTP/1.0\r\n";
                    $postdata .= "Host: " . $host . "\r\n";
                    $postdata .= "User-Agent: VIZUALIZER-TRANSFER-CALLER\r\n";
                    $data = $params->get("data", "");
                    $data = str_replace("[[filename]]", urlencode($basename), $data);
                    $data = str_replace("[[filepath]]", urlencode($filename), $data);
                    $filesize = filesize($filename);
                    $filecontents = chunk_split(base64_encode(file_get_contents($filename)));
                    $boundary = "TRANSFER-" . sha1(uniqid());
                    $postdata .= "Content-Type: multipart/form-data; boundary=" . $boundary . "\r\n";
                    $postdata2 = "--" . $boundary . "\r\n";
                    $postdata2 .= "Content-Disposition: form-data; name=\"" . $params->get("file_key", "FILE") . "_input\"\r\n";
                    $postdata2 .= "Content-Length: " . strlen($data) . "\r\n";
                    $postdata2 .= "\r\n";
                    $postdata2 .= $data . "\r\n";
                    $postdata2 .= "\r\n--" . $boundary . "\r\n";
                    $postdata2 .= "Content-Type: text/csv\r\n";
                    $postdata2 .= "Content-Disposition: form-data; name=\"" . $params->get("file_key", "FILE") . "\"; filename=\"" . $basename . "\"\r\n";
                    $postdata2 .= "Content-Length: " . strlen($filecontents) . "\r\n";
                    $postdata2 .= "Content-Transfer-Encoding: base64\r\n";
                    $postdata2 .= "\r\n";
                    $postdata2 .= $filecontents;
                    $postdata2 .= "\r\n--" . $boundary . "--";
                    $postdata .= "Content-Length: " . strlen($postdata2) . "\r\n";
                    $postdata .= "\r\n" . $postdata2;

                    echo $postdata;
                    fputs($fp, $postdata);
                    $response = "";
                    while (!feof($fp)) {
                        $response .= fgets($fp, 4096);
                    }
                    fclose($fp);
                    $result = explode("\r\n\r\n", $response, 2);
                    $attr = Vizualizer::attr();
                    $attr["TransferResult"] = $result[1];
                }
            }
        }
    }
}
