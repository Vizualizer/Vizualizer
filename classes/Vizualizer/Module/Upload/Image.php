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
 * 画像をアップロードを処理するためのクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Upload_Image extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        // 実行時間制限を解除
        ini_set("max_execution_time", 0);

        // リクエストを取得
        $post = Vizualizer::request();

        $images = array();
        if (is_array($_FILES)) {
            foreach ($_FILES as $key1 => $upload) {
                if ($_FILES[$key1]["error"] == 0) {
                    Vizualizer_Logger::writeDebug(var_export($_FILES, true));
                    // 保存先のディレクトリを構築
                    $saveDir = Vizualizer_Configure::get("upload_root") . "/" . sha1("site" . Vizualizer_Configure::get("site_id")) . "/" . $key1 . "/";
                    if (!file_exists($saveDir)) {
                        mkdir($saveDir, 0777, true);
                    }
                    // 保存するファイル名を構築
                    $info = pathinfo($_FILES[$key1]["name"]);
                    $saveFile = sha1(uniqid($_FILES[$key1]["name"])) . (!empty($info["extension"]) ? "." . $info["extension"] : "");
                    // 保存するファイルを移動
                    move_uploaded_file($_FILES[$key1]["tmp_name"], $saveDir . $saveFile);
                    // 登録した内容をPOSTに設定
                    $post[$key1 . "_name"] = $_FILES[$key1]["name"];
                    $post[$key1] = str_replace(VIZUALIZER_SITE_ROOT, VIZUALIZER_SUBDIR, $saveDir . $saveFile);
                }
            }
            if ($params->check("reload")) {
                $this->reload($params->get("reload"));
            }
        }
    }
}
