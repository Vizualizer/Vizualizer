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
abstract class Vizualizer_Plugin_Module extends Vizualizer_Plugin_Object
{

    public $key_prefix;

    public $continue;

    /**
     * 実際にモジュールの呼び出されるメソッドです。
     * このモジュールが内部的にexecuteなどの処理を呼び出します。
     */
    public function start($params)
    {
        $this->prefilter($params);
        // データの登録に複数のモジュールを使用する場合のパラメータを処理する。
        if ($params->check("continue") && $params->get("continue")) {
            $this->continue = true;
        }
        $this->execute($params);
        $this->postfilter($params);
    }

    /**
     * 前処理用のメソッドです。
     * 共通処理がある場合、その前に実行される処理を記述します。
     */
    public function prefilter($params)
    {

    }

    /**
     * 後処理用のメソッドです。
     * 共通処理がある場合、その後に実行される処理を記述します。
     */
    public function postfilter($params)
    {

    }

    /**
     * デフォルト実行のメソッドになります。
     * このメソッド以外がモジュールとして呼ばれることはありません。
     *
     * @param array $params モジュールの受け取るパラメータ
     * @access public
     */
    abstract function execute($params);

    /**
     * 別途用意したサブモジュールのexecuteを呼び出すためのメソッドです。
     *
     * @param string $name サブモジュール名
     * @param Vizualizer_Plugin_Module_Parameters $params サブモジュールのexecuteに渡すパラメータ
     */
    protected function executeSubModule($name, $params){
        list ($namespace, $class) = explode(".", $name, 2);
        $loader = new Vizualizer_Plugin($namespace);
        $object = $loader->loadModule($class);
        if (method_exists($object, "execute")) {
            $this->debug("=========== " . $name . " start ===========");
            $object->execute($params);
            $this->debug("=========== " . $name . " end ===========");
        } else {
            $this->debug($name . " is not plugin module.");
        }
    }

    /**
     * 入力データをキーを指定して破棄するためのメソッドです。
     * 直接Vizualizer_Parameterのremoveを実行しても構いません。
     *
     * @param string $key 破棄する入力のキー
     */
    protected function removeInput($key)
    {
        Vizualizer::request()->remove($key);
    }

    /**
     * meta refreshでリダイレクトするためのメソッドです。
     *
     * @param string $url リダイレクト先URL
     */
    protected function redirectMeta($url)
    {
        echo "<html><head><meta http-equiv=\"refresh\" content=\"0; URL=".$url."\"></head><body></body></html>";
        exit;
    }

    /**
     * header locationでリダイレクトするためのメソッドです。
     *
     * @param string $url リダイレクト先URL
     */
    protected function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * システム内部のページにリダイレクトするためのメソッドです。
     *
     * @param string $url リダイレクト先の相対パス
     */
    protected function redirectInside($url)
    {
        $this->redirect(VIZUALIZER_SUBDIR . $url);
    }

    /**
     * 現在表示しようとしているページにリダイレクトします。
     * 結果としてリロードする形になります。
     */
    protected function reload()
    {
        $attr = Vizualizer::attr();
        $this->redirectInside($attr["templateName"]);
    }

}
