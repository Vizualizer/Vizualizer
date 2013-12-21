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
 * データ登録用のモジュールです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Save extends Vizualizer_Plugin_Module
{

    protected function executeImpl($type, $name, $primary_key)
    {
        $post = Vizualizer::request();
        if ($post["add"] || $post["save"]) {
            // サイトデータを取得する。
            $loader = new Vizualizer_Plugin($type);
            $model = $loader->loadModel($name);
            if (!empty($this->key_prefix)) {
                $key = $this->key_prefix . $key;
            }
            if (!empty($post[$this->key_prefix . $primary_key])) {
                $model->findByPrimaryKey($post[$this->key_prefix . $primary_key]);
            }
            foreach ($post as $key => $value) {
                if (!empty($this->key_prefix)) {
                    if (substr($key, 0, strlen($this->key_prefix)) == $this->key_prefix) {
                        $key = preg_replace("/^" . $this->key_prefix . "/", "", $key);
                        $model->$key = $value;
                    }
                } else {
                    $model->$key = $value;
                }
            }

            // トランザクションの開始
            $connection = Vizualizer_Database_Factory::begin(strtolower($type));

            try {
                $model->save();
                if (!empty($this->key_prefix)) {
                    $post->set($this->key_prefix . $primary_key, $model->$primary_key);
                } else {
                    $post->set($primary_key, $model->$primary_key);
                }

                // エラーが無かった場合、処理をコミットする。
                Vizualizer_Database_Factory::commit($connection);

            } catch (Exception $e) {
                Vizualizer_Database_Factory::rollback($connection);
                throw new Vizualizer_Exception_Database($e);
            }
        }
    }
}
