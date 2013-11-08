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
 * データベースSELECTの結果処理用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Query_Select_Result
{

    /**
     * クエリ実行に使ったプPrepared Statementオブジェクト
     */
    private $result;

    /**
     * データベースの参照結果を初期化します。
     *
     * @param s object $prepare クエリ実行に使ったPrepared Statementオブジェクト
     * @param s object $result クエリの実行結果オブジェクト
     */
    public function __construct($result)
    {
        $this->result = & $result;
    }

    /**
     * 次の実行結果レコードの連想配列を取得するメソッド
     *
     * @return array 次の実行結果レコードの連想配列、次のレコードが無い場合はFALSE
     */
    public function next()
    {
        return $this->result->fetch();
    }

    /**
     * 実行結果の取得位置を巻き戻すメソッド
     */
    public function rewind()
    {
        $this->result->rewind();
    }

    /**
     * 実行結果の件数を取得するメソッド
     */
    public function count()
    {
        return $this->result->count();
    }

    /**
     * 次の実行結果レコードの連想配列を取得するメソッド
     *
     * @return array 次の実行結果レコードの連想配列、次のレコードが無い場合はFALSE
     */
    public function all()
    {
        return $this->result->fetchAll();
    }

    /**
     * クエリの実行結果をクローズし、リソースを解放する
     */
    public function close()
    {
        $this->result->close();
    }
}
 