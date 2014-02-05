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
 * 一覧アップロード用のモジュールクラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Upload extends Vizualizer_Plugin_Module
{

    /**
     */
    protected $errors = array();

    /**
     * エラーチェックを行い、OKであればtrue、NGであればfalseを返す。
     *
     * @param $title CSVのタイトル行データ
     */
    protected abstract function checkTitle($title);

    /**
     * エラーチェックを行い、登録するモデルを返す。
     *
     * @param $line CSVの行番号
     * @param $model 登録に使用するモデルクラス
     * @param $data CSVの行データ
     */
    protected abstract function check($line, $model, $data);

    private function getCsvData($handle)
    {
        $data = fgetcsv($handle);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = mb_convert_encoding($value, "UTF-8", "Shift_JIS");
            }
        }
        return $data;
    }

    protected function executeImpl($params, $type, $name, $key)
    {
        if (!$params->check("upload") || isset($_POST[$params->get("upload")])) {
            $loader = new Vizualizer_Plugin($type);

            // アップされたファイルのデータを取得する。
            if ($_FILES[$key]["error"] == UPLOAD_ERR_OK) {
                if (($fp = fopen($_FILES[$key]["tmp_name"], "r")) !== FALSE) {
                    // １行目はタイトル行とする。
                    $data = $this->getCsvData($fp);
                    if ($this->checkTitle($data)) {
                        $line = 2;
                        // トランザクションの開始
                        $connection = Vizualizer_Database_Factory::begin(strtolower($type));

                        try {
                            while (($data = $this->getCsvData($fp)) !== FALSE) {
                                $model = $loader->loadModel($name);
                                $model = $this->check($line, $model, $data);
                                if ($model != null) {
                                    $model->save();
                                }
                                $line ++;
                            }
                            if (count($this->errors) > 0) {
                                throw new Vizualizer_Exception_Invalid($key, $this->errors);
                            }
                            // エラーが無かった場合、処理をコミットする。
                            Vizualizer_Database_Factory::commit($connection);
                        } catch (Exception $e) {
                            Vizualizer_Database_Factory::rollback($connection);
                            throw new Vizualizer_Exception_Database($e);
                        }
                    } else {
                        throw new Vizualizer_Exception_Invalid($key, array("アップされたファイルのタイトル行が正しくありません"));
                    }
                    fclose($fp);
                }
            }
        }
    }
}
