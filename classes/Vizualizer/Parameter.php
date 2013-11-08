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
 * パラメータの処理を行うクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Parameter implements Iterator, ArrayAccess
{

    /**
     * パラメータのインデックス
     *
     * @var int
     */
    private $index;

    /**
     * パラメータのキー配列
     *
     * @var array
     */
    private $keys;

    /**
     * パラメータの配列
     *
     * @var array
     */
    private $parameters;

    /**
     * パラメータオブジェクトを初期化する。
     */
    public function __construct()
    {
        $this->index = 0;

        // HTTPのパラメータを統合する。（POST優先）
        $this->parameters = $_GET;
        if (!is_array($this->parameters)) {
            $this->parameters = array();
        }
        if (is_array($_POST)) {
            foreach ($_POST as $name => $value) {
                $this->parameters[$name] = $value;
            }
        }

        // input-imageによって渡されたパラメータを展開
        $inputImageKeys = array();
        foreach ($this->parameters as $name => $value) {
            if (preg_match("/^(.+)_([xy])$/", $name, $params) > 0) {
                $inputImageKeys[$params[1]][$params[2]] = $value;
            }
        }
        foreach ($inputImageKeys as $key => $inputImage) {
            if (isset($inputImage["x"]) && isset($inputImage["y"])) {
                $this->parameters[$key] = $inputImage["x"] . "," . $inputImage["y"];
                unset($this->parameters[$key . "_x"]);
                unset($this->parameters[$key . "_y"]);
            }
        }
        $this->parameters = $this->normalize($this->parameters);
        $this->keys = array_keys($this->parameters);
    }

    /**
     * 現在の位置のパラメータを取得する。
     *
     * @return mixed 現在の値
     */
    public function current()
    {
        return $this->parameters[$this->key()];
    }

    /**
     * 現在の位置のキーを取得する。
     *
     * @return mixed 現在のキー
     */
    public function key()
    {
        return $this->keys[$this->index];
    }

    /**
     * 次のインデックスに移動する
     */
    public function next()
    {
        $this->index ++;
        return $this->current();
    }

    /**
     * 最初のインデックスに戻る
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * インデックスが有効か判断する。
     *
     * @return boolean 有効ならtrue、無効ならfalse
     */
    public function valid()
    {
        return isset($this->keys[$this->index]);
    }

    /**
     * パラメータのキーが存在するか調べる
     *
     * @param mixed $offset パラメータのキー
     * @return boolean 存在するならtrue、存在しないならfalse
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->parameters);
    }

    /**
     * 指定されたキーのパラメータを取得する。
     *
     * @param mixed $offset 指定されたキー
     * @return mixed キーに対応するパラメータの値
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->parameters[$offset];
        }
        return false;
    }

    /**
     * 指定されたキーにパラメータを設定する。
     *
     * @param mixed $offset 設定するパラメータのキー
     * @param mixed $value 設定するパラメータの値
     */
    public function offsetSet($offset, $value)
    {
        $this->parameters[$offset] = $value;
    }

    /**
     * 指定されたキーのパラメータを削除する。
     *
     * @param mixed $offset 削除するパラメータのキー
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->parameters[$offset]);
        }
    }

    /**
     * パラメータを正常化する関数
     *
     * @param mixed $value 正常化する値
     * @return mixed 正常化された値
     */
    protected function normalize($value)
    {
        if (is_array($value)) {
            foreach ($value as $i => $val) {
                if (Vizualizer_Configure::get("device") !== false && Vizualizer_Configure::get("device")->isFuturePhone()) {
                    $i = mb_convert_encoding($i, "UTF-8", "Shift_JIS");
                }
                $value[$i] = $this->normalize($val);
            }
        } else {
            if (get_magic_quotes_gpc() == "1") {
                $value = str_replace("\\\"", "\"", $value);
                $value = str_replace("\\\'", "\'", $value);
                $value = str_replace("\\\\", "\\", $value);
            }
            if (Vizualizer_Configure::get("device") !== false && Vizualizer_Configure::get("device")->isFuturePhone()) {
                $value = mb_convert_encoding($value, "UTF-8", "Shift_JIS");
            }
        }
        return $value;
    }
}