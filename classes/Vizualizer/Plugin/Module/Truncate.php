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
 * 詳細表示用のモジュールです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Truncate extends Vizualizer_Plugin_Module
{

    protected function executeImpl($type, $name, $continue = false)
    {
        $post = Vizualizer::request();
        if ($post["truncate"]) {
            // サイトデータを取得する。
            $loader = new Vizualizer_Plugin($type);
            $model = $loader->loadModel($name);

            // トランザクションデータベースの取得
            $connection = Vizualizer_Database_Factory::begin(strtolower($type));

            try {
                $model->truncate();

                // エラーが無かった場合、処理をコミットする。
                Vizualizer_Database_Factory::commit($connection);

                // 画面をリロードする。
                if (!$continue) {
                    // 登録に使用したキーを無効化
                    $this->removeInput("truncate");

                    $this->reload();
                }
            } catch (Exception $e) {
                Vizualizer_Database_Factory::rollback($connection);
                throw new Vizualizer_Exception_Database($e);
            }
        }
    }
}
