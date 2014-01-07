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
 * ファイルによるデータキャッシュクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Cache_File extends Vizualizer_Cache_Base
{

    /**
     * キャッシュファイルを配置するルートディレクトリ
     *
     * @var string
     */
    private $cacheRoot;

    /**
     * コンストラクタ
     *
     * @param string $server
     * @param string $file
     * @param int $expires
     */
    public function __construct($server, $file, $expires)
    {
        $this->cacheRoot = VIZUALIZER_CACHE_ROOT . DIRECTORY_SEPARATOR . "_cache";
        $this->init($server, $file, $expires);
    }

    /**
     * キャッシュを初期化する。
     *
     * @param string $server
     * @param string $file
     * @param int $expires
     */
    public function init($server, $file, $expires)
    {
        parent::init($server, $file, $expires);
        $filename = $this->cacheRoot . DIRECTORY_SEPARATOR . $this->server . DIRECTORY_SEPARATOR . $this->file . ".php";
        if (file_exists($filename) && time() < fileatime($filename) + $this->expires) {
            require_once ($filename);
        }
    }

    /**
     * キャッシュの内容を保存する。
     */
    protected function save()
    {
        if (!is_dir($this->cacheRoot)) {
            mkdir($this->cacheRoot);
            chmod($this->cacheRoot, 0777);
        }
        if (!is_dir($this->cacheRoot . DIRECTORY_SEPARATOR . $this->server)) {
            mkdir($this->cacheRoot . DIRECTORY_SEPARATOR . $this->server);
            chmod($this->cacheRoot . DIRECTORY_SEPARATOR . $this->server, 0777);
        }
        if (($fp = fopen($this->cacheRoot . DIRECTORY_SEPARATOR . $this->server . DIRECTORY_SEPARATOR . $this->file . ".php", "w+")) !== FALSE) {
            fwrite($fp, "<" . "?php\r\n");
            foreach ($this->values as $key => $value) {
                fwrite($fp, '$this->values["' . $key . '"] = ' . var_export($value, TRUE) . ";\r\n");
            }
            fclose($fp);
            chmod($this->cacheRoot . DIRECTORY_SEPARATOR . $this->server . DIRECTORY_SEPARATOR . $this->file . ".php", 0666);
        }
    }
}
