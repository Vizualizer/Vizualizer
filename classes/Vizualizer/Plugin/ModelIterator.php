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
 * モデルのイテレータークラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_ModelIterator implements Iterator
{

    /**
     * 検索結果のインデックス
     *
     * @var int
     */
    private $index;

    /**
     * 検索結果の現在の値
     *
     * @var array
     */
    private $currentData;

    /**
     * モデルクラス名
     *
     * @var string
     */
    private $modelClass;

    /**
     * モデルオブジェクト
     */
    private $modelObject;

    /**
     * DBの結果オブジェクト
     *
     * @var Vizualizer_Query_Select_Result
     */
    private $result;

    /**
     * コンストラクタ
     *
     * @param string $modelClass
     * @param Vizualizer_Query_Select_Result $result
     */
    public function __construct($modelClass, Vizualizer_Query_Select_Result $result)
    {
        // プロパティを初期化
        $this->modelClass = $modelClass;
        $this->modelObject = new $modelClass();
        $this->result = $result;

        // 最初の位置に移動
        $this->rewind();
    }

    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->result->close();
    }

    /**
     * 現在の位置のパラメータを取得する。
     *
     * @return mixed 現在の値
     */
    public function current()
    {
        $modelObject = clone $this->modelObject;
        $modelObject->setValues($this->currentData);
        return $modelObject;
    }

    /**
     * 現在の位置のキーを取得する。
     *
     * @return mixed 現在のキー
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * 次のインデックスに移動する
     */
    public function next()
    {
        $this->currentData = $this->result->next();
        $this->index ++;
        return $this;
    }

    /**
     * 最初のインデックスに戻る
     */
    public function rewind()
    {
        $this->result->rewind();
        $this->index = -1;
        return $this->next();
    }

    /**
     * インデックスが有効か判断する。
     *
     * @return boolean 有効ならtrue、無効ならfalse
     */
    public function valid()
    {
        return ($this->currentData !== NULL);
    }

    /**
     * 結果のモデルの件数を取得する。
     *
     * @return int 結果件数
     */
    public function count()
    {
        return $this->result->count();
    }
}