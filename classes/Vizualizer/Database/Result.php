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
 * データベース接続のインターフェイスです。。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
interface Vizualizer_Database_Result
{

    /**
     * クエリの結果から１件を取得する。
     * @reutrn array クエリ結果
     */
    public function fetch();

    /**
     * クエリ結果の全件を取得する。
     * 
     * @return array クエリ結果
     */
    public function fetchAll();

    /**
     * 結果のポインタを先頭に移動する。
     */
    public function rewind();

    /**
     * クエリの結果の件数を取得する。
     */
    public function count();

    /**
     * クエリ結果のリソースを解放する。
     */
    public function close();
}
 