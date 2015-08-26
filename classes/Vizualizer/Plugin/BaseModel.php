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
     * パラメータの値により、WHERE句を構築する。
     *
     * @param $select SELECTオブジェクト
     * @param $key 追加するキー
     * @param $value 追加する値
     * @return SELECTオブジェクト
     */
    protected function appendWhere($select, $key, $value)
    {
        if (strpos($key, ":") > 0) {
            if (count(explode(":", $key, 3)) > 2) {
                list ($op, $key, $default) = explode(":", $key, 3);
            } else {
                list ($op, $key) = explode(":", $key, 3);
                $default = null;
            }
        } else {
            $op = "eq";
        }
        if (strpos($key, "+") > 0) {
            $keys = explode("+", $key);
            $isString = false;
            foreach ($keys as $index => $key) {
                $keys[$index] = $this->access->$key;
                switch ($this->access->$key->type) {
                    case "char":
                    case "varchar":
                    case "binary":
                    case "varbinary":
                    case "text":
                    case "tinytext":
                    case "mediumtext":
                    case "longtext":
                    case "blob":
                    case "tinyblob":
                    case "mediumblob":
                    case "longblob":
                        $isString = true;
                        break;
                }
            }
            if ($isString) {
                $fullkey = "CONCAT(" . implode(", ", $keys) . ")";
            } else {
                $fullkey = implode(" + ", $keys);
            }
        } elseif (strpos($key, "*") > 0) {
            $keys = explode("*", $key);
            foreach ($keys as $index => $key) {
                $keys[$index] = $this->access->$key;
            }
            $fullkey = "COALESCE(" . implode(", ", $keys) . ")";
        } else {
            $fullkey = $this->access->$key;
            if (isset($default) && $default != null) {
                if (is_numeric($default) && (substr($default, 0, 1) != "0" || strlen($default) === 1)) {
                    // 全て数字で先頭が0でない、もしくは1桁のみの場合は数値データとして扱う
                    $fullkey = "COALESCE(" . $fullkey . ", " . $default . ")";
                } else {
                    $fullkey = "COALESCE(" . $fullkey . ", '" . $default . "')";
                }
            }
        }
        if ($op !== "in" && $op !== "nin" && is_array($value)) {
            foreach ($value as $item) {
                if (empty($item)) {
                    return $select;
                }
            }
            $value = implode("-", $value);
        }
        if (in_array($key, $this->columns)) {
            switch ($op) {
                case "eq":
                    if ($value === null) {
                        $select->addWhere($fullkey . " IS NULL");
                    } else {
                        $select->addWhere($fullkey . " = ?", array($value));
                    }
                    break;
                case "ne":
                    if ($value === null) {
                        $select->addWhere($fullkey . " IS NOT NULL");
                    } else {
                        $select->addWhere($fullkey . " != ?", array($value));
                    }
                    break;
                case "gt":
                    $select->addWhere($fullkey . " > ?", array($value));
                    break;
                case "ge":
                    $select->addWhere($fullkey . " >= ?", array($value));
                    break;
                case "lt":
                    $select->addWhere($fullkey . " < ?", array($value));
                    break;
                case "le":
                    $select->addWhere($fullkey . " <= ?", array($value));
                    break;
                case "ngt":
                    $select->addWhere("(".$fullkey . " > ?) IS NOT TRUE", array($value));
                    break;
                case "nge":
                    $select->addWhere("(".$fullkey . " >= ?) IS NOT TRUE", array($value));
                    break;
                case "nlt":
                    $select->addWhere("(".$fullkey . " < ?) IS NOT TRUE", array($value));
                    break;
                case "nle":
                    $select->addWhere("(".$fullkey . " <= ?) IS NOT TRUE", array($value));
                    break;
                case "like":
                    $select->addWhere($fullkey . " LIKE ?", array($value));
                    break;
                case "part":
                    $select->addWhere($fullkey . " LIKE ?", array("%" . $value . "%"));
                    break;
                case "for":
                    $select->addWhere($fullkey . " LIKE ?", array("%" . $value));
                    break;
                case "back":
                    $select->addWhere($fullkey . " LIKE ?", array($value . "%"));
                    break;
                case "nlike":
                    $select->addWhere($fullkey . " NOT LIKE ?", array($value));
                    break;
                case "in":
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    $placeholders = "";
                    if(!empty($value)){
                        foreach ($value as $v) {
                            if (!empty($placeholders)) {
                                $placeholders .= ",";
                            }
                            $placeholders .= "?";
                        }
                        $select->addWhere($fullkey . " in (" . $placeholders . ")", $value);
                    }
                    break;
                case "nin":
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    $placeholders = "";
                    if(!empty($value)){
                        foreach ($value as $v) {
                            if (!empty($placeholders)) {
                                $placeholders .= ",";
                            }
                            $placeholders .= "?";
                        }
                        $select->addWhere($fullkey . " NOT IN (" . $placeholders . ")", $value);
                    }
                    break;
                default:
                    break;
            }
        }
        return $select;
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
