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
 * データキャッシュファクトリクラス
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Cache_Factory
{

    /**
     * ファクトリクラスを生成する。
     *
     * @param string $file
     * @param int $expires
     * @return Vizualizer_Cache_Memory Vizualizer_Cache_File
     */
    public static function create($file, $expires = 3600)
    {
        if (class_exists("Memcache") && Vizualizer_Configure::get("memcache") !== "") {
            return new Vizualizer_Cache_Memory(Vizualizer_Configure::get("site_domain"), $file, $expires);
        } else {
            return new Vizualizer_Cache_File(Vizualizer_Configure::get("site_domain"), $file, $expires);
        }
    }
}
