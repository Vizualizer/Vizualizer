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
 * このシステムにおける全てのモジュールの基底クラスになります。
 * 必ず拡張する必要があり、executeメソッドを実装する必要があります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module
{

    public $key_prefix;

    public $continue;

    /**
     * デフォルト実行のメソッドになります。
     * このメソッド以外がモジュールとして呼ばれることはありません。
     *
     * @param array $params モジュールの受け取るパラメータ
     * @access public
     */
    abstract function execute($params);

    protected function isEmpty($value)
    {
        return (!isset($value) || $value === null || $value === "");
    }

    protected function encryptPassword($login_id, $plain_password)
    {
        return sha1($login_id . ":" . $plain_password);
    }

    protected function removeInput($key)
    {
        Vizualizer::request()->remove($key);
    }

    protected function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    protected function reload()
    {
        $attr = Vizualizer::attr();
        $this->redirect(VIZUALIZER_SUBDIR . $attr["templateName"]);
    }
}
