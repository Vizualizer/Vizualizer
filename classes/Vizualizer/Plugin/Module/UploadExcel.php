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
 * Excelの一覧アップロード用のモジュールクラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_UploadExcel extends Vizualizer_Plugin_Module
{

    /**
     */
    protected $errors = array();

    /**
     * アップロードされたExcelファイルを処理する。
     */
    protected abstract function process($params, $book);

    protected function getValue($sheet, $cell){
        $value = $sheet->getCell($cell)->getCalculatedValue();
        $values = explode("※", $value);
        return $this->trim($values[0]);
    }

    /**
     *
     * @param unknown $book
     */
    protected function save($book, $path)
    {
        $writer = PHPExcel_IOFactory::createWriter($book, "Excel2007");
        $writer->save($path);
    }

    protected function executeImpl($params, $type, $name, $key, $continue = false)
    {
        if (!$params->check("upload") || isset($_POST[$params->get("upload")])) {
            $loader = new Vizualizer_Plugin($type);

            // アップされたファイルのデータを取得する。
            if ($_FILES[$key]["error"] == UPLOAD_ERR_OK) {
                // Excelファイルを読み込む
                $book = PHPExcel_IOFactory::load($_FILES[$key]["tmp_name"]);

                // 処理を実行する
                $data = $this->process($params, $book);

                // トランザクションの開始
                $connection = Vizualizer_Database_Factory::begin(strtolower($type));

                try {
                    foreach ($data as $item) {
                        $model = $loader->loadModel($name);
                        foreach ($item as $col => $value) {
                            $model->$col = $value;
                        }
                        $model->save();
                    }
                    // エラーが無かった場合、処理をコミットする。
                    Vizualizer_Database_Factory::commit($connection);

                    // 画面をリロードする。
                    if (!$continue) {
                        // 登録に使用したキーを無効化
                        $this->removeInput("upload");

                        $this->reload();
                    }
                } catch (Exception $e) {
                    Vizualizer_Database_Factory::rollback($connection);
                    throw new Vizualizer_Exception_Database($e);
                }
            }
        }
    }
}
