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
 * データキャッシュ用のインターフェイス
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Cache_Base
{

    /**
     * キャッシュする値
     *
     * @var unknown
     */
    protected $values;

    /**
     * キャッシュするサーバー
     *
     * @var string
     */
    protected $server;

    /**
     * キャッシュするファイル名
     *
     * @var string
     */
    protected $file;

    /**
     * キャッシュの有効期間
     *
     * @var int
     */
    protected $expires;

    /**
     * キャッシュを初期化する。
     *
     * @param string $server
     * @param string $file
     * @param int $expires
     */
    public function init($server, $file, $expires)
    {
        $this->server = $server;
        $this->file = $file;
        $this->expires = $expires;
    }

    /**
     * キャッシュの内容を保存する。
     */
    protected abstract function save();

    /**
     * キャッシュの値をインポートする。
     *
     * @param array $values
     */
    public function import($values)
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = $value;
        }
        $this->save();
    }

    /**
     * キャッシュの値をエクスポートする。
     *
     * @return array
     */
    public function export()
    {
        return $this->values;
    }

    /**
     * キャッシュの値を設定する。
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
        $this->save();
    }

    /**
     * キャッシュの値を取得する。
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return "";
    }

    /**
     * キャッシュの値を取得する。
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * キャッシュの値を設定する。
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * キャッシュの値が設定されているか調べる。
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->values[$name]);
    }
}
