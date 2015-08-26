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
 * データモデル用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_BaseModel
{
    // カラムリスト
    protected $columns;

    // 元設定値リスト
    protected $values_org;

    // 設定値リスト
    protected $values;

    // キャッシュの有効時間
    private static $cachedTime;

    // モデルで利用するキャッシュ用の変数
    private static $cached;

    /**
     * データベースモデルを初期化する。
     * 初期の値を配列で渡すことで、その値でモデルを構築する。
     */
    public function __construct($columns, $values = array())
    {
        $this->columns = array();
        $this->values_org = array();
        $this->values = array();
        foreach ($columns as $column) {
            $this->columns[] = $column;
            $this->values_org[$column] = "";
            $this->values[$column] = "";
        }
        $this->setValues($values);
    }

    /**
     * プロパティを復元する。
     */
    public static function __set_state($props){
        $class = get_called_class();
        $object = new $class(array());
        foreach($props as $key=>$val){
            //__setでなく可変変数でセットするのが楽
            $object->$key = $val;
        }
        return $object;
    }

    /**
     * データベースのカラムのデータを取得する。
     */
    public function __get($name)
    {
        if (isset($this->values[$name])) {
            return $this->values[$name];
        }
        return null;
    }

    /**
     * データベースのカラムを主キー以外についてのみ登録する。
     * また、レコード作成日は未設定の場合のみ設定可能。
     */
    public function __set($name, $value)
    {
        // 主キー以外のカラムとして存在した場合は変更を行う。
        if (!in_array($name, $this->primary_keys)) {
            if ($name == "create_role_id" || $name == "create_operator_id" || $name == "create_time") {
                if (empty($this->values[$name])) {
                    // データ登録日は未設定の場合のみ設定する。
                    $this->values[$name] = $value;
                }
            } else {
                $this->values[$name] = $value;
            }
        }
    }

    /**
     * そのカラムが設定されているかどうかをチェックする。
     */
    public function __isset($name)
    {
        return isset($this->values[$name]);
    }

    /**
     * オブジェクトを文字列として出力する。
     */
    public function __toString()
    {
        return var_export($this->values, true);
    }

    /**
     * 配列になっているデータを一括でモデルに設定する。
     * 元データも設定しなおすため、実質的にデータの初期化処理と同じ扱いとなる。
     */
    public function setValues($values)
    {
        $this->values_org = array();
        $this->values = array();
        if ($values instanceof Vizualizer_Plugin_Model) {
            $values = $values->values;
        } elseif (is_array($values)) {
            foreach ($values as $key => $value) {
                $this->values[$key] = $this->values_org[$key] = $value;
            }
        }
    }

    /**
     * 登録オペレータを設定する内部処理です。
     */
    protected function updateOperatorInfo() {
        try{
            if (class_exists("VizualizerAdmin")) {
                $operator = Vizualizer_Session::get(VizualizerAdmin::SESSION_KEY);
                if (is_array($operator) && array_key_exists("operator_id", $operator) && $operator["operator_id"] > 0) {
                    if ($this->operator_id > 0) {
                        $this->create_operator_id = $this->update_operator_id = $operator["operator_id"];
                    }else{
                        $this->operator_id = $this->create_operator_id = $this->update_operator_id = $operator["operator_id"];
                    }
                }
            }
        }catch(Exception $e){
            // Adminパッケージを使っていない場合は、登録者／更新者IDの設定をスキップする。
        }
    }

    /**
     * 登録日時や登録オペレータなどを設定する内部処理です。
     */
    protected function updateRegisterInfo(){
        // オペレータIDを設定する。
        $this->updateOperatorInfo();

        // データ作成日／更新日は自動的に設定する。
        $this->create_time = $this->update_time = Vizualizer_Data_Calendar::now()->date("Y-m-d H:i:s");
    }

    /**
     * モデルの配列表現を返す。
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * インスタンスのコピーを新規作成する。
     */
    public function copy()
    {
        $thisClass = get_class($this);
        $copy = new $thisClass(array());
        foreach ($this->values as $key => $value) {
            $copy->$key = $value;
        }
        return $copy;
    }

    /**
     * キャッシュを利用するためのメソッド
     */
    protected static function cacheData($key, $value = null){
        if(!self::$cached || self::$cachedTime != Vizualizer::now()->date("YmdHis")){
            // キャッシュデータが無いか、キャッシュ時間が更新されている場合は初期化
            self::$cachedTime = Vizualizer::now()->date("YmdHis");
            self::$cached = array();
        }
        if($value !== null){
            // 値が設定されている場合にはキーに対応する値に設定
            self::$cached[$key] = $value;
        }
        // キャッシュが存在する場合には値を返し、存在しない場合にはnullを返す。
        if(array_key_exists($key, self::$cached)){
            return self::$cached[$key];
        }
        return null;
    }
}
