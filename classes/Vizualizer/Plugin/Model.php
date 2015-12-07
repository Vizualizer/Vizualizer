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
 * データベースモデルラッパー用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_Model
{
    // オペレータIDで制限をかけるかどうかのフラグ
    public static $limitedOperator = true;

    // ディスティンクトオプションのフラグ
    protected $distinct;

    // ベースのデータベースアクセスオブジェクト
    protected $access;

    // カラムリスト
    protected $columns;

    // 主キーのリスト
    protected $primary_keys;

    // 元設定値リスト
    protected $values_org;

    // 設定値リスト
    protected $values;

    // 結果のグループ化
    protected $groupBy;

    // 出力レコード数
    protected $limit;

    // 出力レコードオフセット
    protected $offset;

    // 自動的にオペレータ検索条件を無視するフラグを設定
    private $ignoreOperator;

    // キャッシュの有効時間
    private static $cachedTime;

    // モデルで利用するキャッシュ用の変数
    private static $cached;

    /**
     * データベースモデルを初期化する。
     * 初期の値を配列で渡すことで、その値でモデルを構築する。
     */
    public function __construct($accessTable, $values = array())
    {
        $this->distinct = false;
        $this->access = $accessTable;
        $this->columns = array();
        $this->primary_keys = $this->access->getPrimaryKeys();
        $this->values_org = array();
        $this->values = array();
        $this->ignoreOperator = false;
        foreach ($this->access->getColumns() as $column) {
            $this->columns[] = $column;
            $this->values_org[$column] = "";
            $this->values[$column] = "";
        }
        $this->setValues($values);
        $this->setGroupBy();
        $this->limit();
    }

    /**
     * データベースモデルを複製する。
     * この処理はコンストラクタがDBにアクセスしているため、それによる負荷上昇の軽減用に使用する。
     */
    public function __clone(){
        $this->values_org = array();
        $this->values = array();
        $this->groupBy = "";
        $this->limit = "";
        $this->offset = "";
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

    public function setGroupBy($groupBy = null)
    {
        $this->groupBy = $groupBy;
    }

    public function setDistinct($distinct = true)
    {
        $this->distinct = $distinct;
    }

    /**
     * 出力されるデータを制限する。
     */
    public function limit($limit = null, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * 出力されるデータのオフセットを設定する。
     */
    public function offset($offset = null)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * 自動オペレータ検索条件を無視するフラグを設定
     */
    public function setIgnoreOperator($ignoreOperator) {
        $this->ignoreOperator = $ignoreOperator;
        return $this;
    }

    /**
     * レコードが作成可能な場合に、レコードを作成します。
     */
    public function create()
    {
        $this->updateRegisterInfo();

        $insert = new Vizualizer_Query_InsertIgnore($this->access);
        $sqlvals = array();
        $insertSet = false;
        foreach ($this->columns as $column) {
            if (array_key_exists($column, $this->values) && (!empty($this->values[$column]) || is_numeric($this->values[$column]))) {
                $sqlvals[$column] = $this->values[$column];
                if($column != "create_time" && $column != "update_time"){
                    $insertSet = true;
                }
            }
        }
        // 何かしらの情報が登録されている場合のみ登録処理を実行する。
        if (!empty($sqlvals) && $insertSet) {
            // データ作成日／更新日は自動的に設定する。
            $sqlvals["create_time"] = $sqlvals["update_time"] = Vizualizer_Data_Calendar::now()->date("Y-m-d H:i:s");
            $insert->execute($sqlvals);
            foreach ($this->primary_keys as $key) {
                if (empty($this->values[$key])) {
                    $this->values[$key] = $this->values_org[$key] = $insert->lastInsertId();
                }
            }
        }
        return $this;
    }

    /**
     * レコードが更新可能な場合に、レコードを更新します。
     */
    public function update()
    {
        $this->updateRegisterInfo();

        $update = new Vizualizer_Query_Update($this->access);
        $updateSet = false;
        $updateWhere = false;
        foreach ($this->columns as $column) {
            if (in_array($column, $this->primary_keys)) {
                // 主キーは更新条件
                $update->addWhere($this->access->$column . " = ?", array($this->values[$column]));
                $updateWhere = true;
            } elseif ($column === "create_operator_id" && $column === "create_time") {
                // 更新時は登録オペレータIDと登録日時は対象外
                continue;
            } elseif ($column === "operator_id" && (array_key_exists($column, $this->values_org) && $this->values_org[$column] > 0 || !array_key_exists($column, $this->values) || !($this->values[$column] > 0))) {
                // 更新時は元の値が設定されているか、更新値が設定されていないオペレータIDは対象外
                continue;
            } elseif (array_key_exists($column, $this->values) && (!array_key_exists($column, $this->values_org) || $this->values[$column] != $this->values_org[$column])) {
                if(array_key_exists($column, $this->values) && (!empty($this->values[$column]) || is_numeric($this->values[$column])) || array_key_exists($column, $this->values_org) && (!empty($this->values_org[$column]) || is_numeric($this->values_org[$column]))){
                    if (array_key_exists($column, $this->values) && $this->values[$column] !== null) {
                        $update->addSets($this->access->$column . " = ?", array($this->values[$column]));
                    } else {
                        $update->addSets($this->access->$column . " = NULL", array());
                    }
                }
                if($column !== "update_time"){
                    $updateSet = true;
                }
            }
        }
        // WHERE句とSET句の両方が設定されている場合のみ更新処理を実行
        if ($updateSet && $updateWhere) {
            $update->execute();
        }
        return $this;
    }

    /**
     * レコードを特定のキーで検索する。
     * 複数件ヒットした場合は、最初の１件をデータとして取得する。
     */
    public function findBy($values = array())
    {
        $result = $this->findAllBy($values);

        if (($data = $result->current()) !== NULL) {
            $this->setValues($data->toArray());
            return true;
        }
        return false;
    }

    /**
     * レコードを特定のキーで検索する。
     */
    public function findAllBy($values = array(), $order = "", $reverse = false)
    {
        $select = new Vizualizer_Query_Select($this->access);
        $select->distinct($this->distinct);
        $select->addColumn($this->access->_W);
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $select = $this->appendWhere($select, $key, $value);
            }
        }
        // Adminパッケージを使っている場合で利用ユーザーが管理権限で無い場合は自分の作成したデータしか閲覧できない
        try{
            if (!$this->ignoreOperator) {
                // DBのカラムにoperator_idが存在し、Adminパッケージをインストールしている場合のみ有効
                if (class_exists("VizualizerAdmin") && !empty($this->access->operator_id)) {
                    $operator = Vizualizer_Session::get(VizualizerAdmin::SESSION_KEY);
                    // セッションからオペレータIDが取得できた場合のみ処理を実施
                    if (is_array($operator) && array_key_exists("operator_id", $operator) && $operator["operator_id"] > 0) {
                        // 管理者以外もしくは強制的にオペレータ適用のフラグを設定した場合のみオペレータIDの制限を付ける。
                        if(self::$limitedOperator) {
                            $select = $this->appendWhere($select, "operator_id", $operator["operator_id"]);
                        }
                    }
                }
            }
        }catch(Exception $e){
            // Adminパッケージを使っていない場合は、条件の設定をスキップする。
        }

        if ($this->groupBy != null) {
            $select->addGroupBy($this->groupBy);
        }

        if (!empty($order)) {
            if (is_array($order)) {
                foreach ($order as $index => $ord) {
                    if (is_array($reverse)) {
                        if (isset($reverse[$index])) {
                            $select->addOrder($ord, $reverse[$index]);
                        } else {
                            $select->addOrder($ord, false);
                        }
                    } else {
                        $select->addOrder($ord, $reverse);
                    }
                }
            } else {
                if (is_array($reverse)) {
                    if (isset($reverse[0])) {
                        $select->addOrder($order, $reverse[0]);
                    } else {
                        $select->addOrder($order, false);
                    }
                } else {
                    $select->addOrder($order, $reverse);
                }
            }
        }
        $select->setLimit($this->limit, $this->offset);
        $sqlResult = $select->fetch($this->limit, $this->offset);
        $thisClass = get_class($this);
        $result = new Vizualizer_Plugin_ModelIterator($thisClass, $sqlResult);
        return $result;
    }

    /**
     * レコードを特定のキーで検索する。
     */
    public function queryAllBy($select)
    {
        $sqlResult = $select->fetch($this->limit, $this->offset);
        $thisClass = get_class($this);
        $result = new Vizualizer_Plugin_ModelIterator($thisClass, $sqlResult);

        return $result;
    }

    /**
     * レコードの件数を取得する。
     */
    public function countBy($values = array(), $columns = "*")
    {
        $select = new Vizualizer_Query_Select($this->access);
        $select->addColumn("COUNT(" . $columns . ") AS count");
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $select = $this->appendWhere($select, $key, $value);
            }
        }
        $result = $select->execute();

        if (count($result) > 0) {
            return $result[0]["count"];
        } else {
            return "0";
        }
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
     * 登録日時や登録オペレータなどを設定する内部処理です。
     */
    protected function updateRegisterInfo(){
        // データ作成日／更新日は自動的に設定する。
        try{
            if (!$this->ignoreOperator && class_exists("VizualizerAdmin")) {
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
        $this->create_time = $this->update_time = Vizualizer_Data_Calendar::now()->date("Y-m-d H:i:s");
    }

    /**
     * 指定したトランザクション内にて主キーベースでデータの保存を行う。
     * 主キーが存在しない場合は何もしない。
     * また、モデル内のカラムがDBに無い場合はスキップする。
     * データ作成日／更新日は自動的に設定される。
     */
    public function save()
    {
        if (!empty($this->primary_keys)) {
            // 現在該当のデータが登録されているか調べる。
            $pkset = false;
            $select = new Vizualizer_Query_Select($this->access);
            $select->addColumn($this->access->_W);
            foreach ($this->primary_keys as $key) {
                if (isset($this->values[$key])) {
                    $select->addWhere($this->access->$key . " = ?", array($this->values[$key]));
                } else {
                    $pkset = false;
                    break;
                }
                $pkset = true;
            }
            if ($pkset) {
                $result = $select->execute();
            } else {
                $result = array();
            }

            if (!is_array($result) || empty($result)) {
                // 主キーのデータが無かった場合はデータを作成する。
                $this->create();
            } else {
                // 主キーのデータがあった場合はデータを更新する。
                $this->update();
            }
        }
    }

    /**
     * 指定したトランザクション内にて主キーベースでデータの保存を行う。
     * 主キーが存在しない場合は何もしない。
     * また、モデル内のカラムがDBに無い場合はスキップする。
     * データ作成日／更新日は自動的に設定される。
     */
    public function saveAll($list)
    {
        // 主キーのデータが無かった場合はInsert
        $insert = new Vizualizer_Query_InsertIgnore($this->access);
        foreach ($list as $index => $data) {
            // データ作成日／更新日は自動的に設定する。
            $data["create_time"] = $data["update_time"] = Vizualizer_Data_Calendar::now()->date("Y-m-d H:i:s");
            $insert->execute($data);
            foreach ($this->primary_keys as $key) {
                if (empty($data[$key])) {
                    $list[$index][$key] = $insert->lastInsertId();
                }
            }
        }
        return $list;
    }

    /**
     * 指定したトランザクション内にて主キーベースでデータの削除を行う。
     * 主キーが存在しない場合は何もしない。
     */
    public function delete()
    {
        if (!empty($this->primary_keys)) {
            $delete = new Vizualizer_Query_Delete($this->access);
            $deleteWhere = false;
            foreach ($this->columns as $column) {
                if (in_array($column, $this->primary_keys)) {
                    // 主キーは削除条件
                    $delete->addWhere($this->access->$column . " = ?", array($this->values[$column]));
                    $deleteWhere = true;
                }
            }
            // WHERE句が設定されている場合のみ削除処理を実行
            if ($deleteWhere) {
                $delete->execute();
            }
        }
    }

    /**
     * 指定したトランザクション内にて主データのクリアを行う。
     */
    public function truncate()
    {
        $truncate = new Vizualizer_Query_Truncate($this->access);
        $truncate->execute();
    }

    /**
     * モデルのカラムリストを返す
     */
    public function columns()
    {
        return $this->columns;
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
