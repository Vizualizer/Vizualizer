<?php

/**
 * Copyright (C) 2012 Vizualizer System All Rights Reserved.
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
 * @author    Naohisa Minagawa <info@clay-system.jp>
 * @copyright Copyright (c) 2010, Vizualizer System
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 * @since PHP 5.3
 * @version   4.0.0
 */

/**
 * MySQLのクエリ実行結果を管理するためのクラスです。
 *
 * @package Database
 * @author Naohisa Minagawa <info@clay-system.jp>
 */
class Vizualizer_Database_Mysql_Result implements Vizualizer_Database_Result
{

    private $resource;

    /**
     * コンストラクタ
     *
     * @param unknown $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * クエリの結果から１件を取得する。
     * @reutrn array クエリ結果
     */
    public function fetch()
    {
        if ($this->resource != null) {
            $result = mysqli_fetch_assoc($this->resource);
            return $result;
        }
        return null;
    }

    /**
     * クエリ結果の全件を取得する。
     *
     * @return array クエリ結果
     */
    public function fetchAll()
    {
        $result = array();
        while ($data = $this->fetch()) {
            $result[] = $data;
        }
        return $result;
    }

    /**
     * 結果のポインタを先頭に移動する。
     */
    public function rewind()
    {
        if ($this->count() > 0) {
            mysqli_data_seek($this->resource, 0);
        }
    }

    /**
     * クエリの結果の件数を取得する。
     */
    public function count()
    {
        return mysqli_num_rows($this->resource);
    }

    /**
     * クエリ結果のリソースを解放する。
     */
    public function close()
    {
        if ($this->resource != null) {
            mysqli_free_result($this->resource);
            $this->resource = null;
        }
    }
}
