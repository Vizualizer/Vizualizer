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
 * 出力時の属性の処理を行うクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Attributes implements Iterator, ArrayAccess
{

    /**
     * 属性のインデックス
     *
     * @var int
     */
    private static $index = 0;

    /**
     * 属性のキー配列
     *
     * @var array
     */
    private static $keys = array();

    /**
     * 属性の配列
     *
     * @var array
     */
    private static $attributes = array();

    /**
     * 現在の位置のパラメータを取得する。
     *
     * @return mixed 現在の値
     */
    public function current()
    {
        return self::$attributes[$this->key()];
    }

    /**
     * 現在の位置のキーを取得する。
     *
     * @return mixed 現在のキー
     */
    public function key()
    {
        return self::$keys[self::$index];
    }

    /**
     * 次のインデックスに移動する
     */
    public function next()
    {
        self::$index ++;
        return $this->current();
    }

    /**
     * 最初のインデックスに戻る
     */
    public function rewind()
    {
        self::$index = 0;
        self::$keys = array_keys(self::$attributes);
    }

    /**
     * インデックスが有効か判断する。
     *
     * @return boolean 有効ならtrue、無効ならfalse
     */
    public function valid()
    {
        return isset(self::$keys[self::$index]);
    }

    /**
     * パラメータのキーが存在するか調べる
     *
     * @param mixed $offset パラメータのキー
     * @return boolean 存在するならtrue、存在しないならfalse
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, self::$attributes);
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
            return self::$attributes[$offset];
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
        self::$attributes[$offset] = $value;
    }

    /**
     * 指定されたキーのパラメータを削除する。
     *
     * @param mixed $offset 削除するパラメータのキー
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset(self::$attributes[$offset]);
        }
    }

    /**
     * 属性のリストを取得
     */
    public function values() {
        return self::$attributes;
    }
}