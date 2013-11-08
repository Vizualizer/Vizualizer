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
 * ビューのようにクエリをモデル化するクラスです
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_Model_View extends Vizualizer_Plugin_Model
{

    /**
     * ビューとして扱うためのSelect文
     *
     * @var string
     */
    private $viewTable;

    /**
     * 取得カラムを設定するためのマップ
     *
     * @var array
     */
    private $columnMap;

    /**
     * 条件を設定するためのマップ
     *
     * @var array
     */
    private $conditionMap;

    /**
     * データベースモデルを初期化する。
     * 初期の値を配列で渡すことで、その値でモデルを構築する。
     *
     * @param Vizualizer_Plugin_Table $accessTable
     * @param array $values
     */
    public function __construct($accessTable, $values = array())
    {
        parent::__construct($accessTable, $values);
        $this->setViewTable($accessTable);
        $this->columnMap = array();
        $this->conditionMap = array();
    }

    /**
     * 関連させるテーブルを設定する。
     *
     * @param Vizualizer_Plugin_Table $viewTable
     */
    protected function setViewTable($viewTable)
    {
        $this->viewTable = $viewTable;
    }

    /**
     * カラムとして利用する項目を追加する。
     *
     * @param string $key
     * @param string $value
     */
    protected function addColumnItem($key, $value)
    {
        $this->columnMap[$key] = $value;
    }

    /**
     * 結合条件として利用する項目を追加する。
     *
     * @param string $key
     * @param string $value
     */
    protected function addConditionItem($key, $value)
    {
        $this->conditionMap[$key] = $value;
    }

    /**
     * 実際のキー名を取得する。
     *
     * @param string $key
     * @return string
     */
    protected function getRealKey($key)
    {
        if (array_key_exists($key, $this->conditionMap)) {
            return $this->conditionMap[$key];
        }
        return $this->access->$key;
    }

    /**
     * グループ化に利用するカラムを追加する。
     *
     * @param string $groupBy
     */
    public function setGroupBy($groupBy = null)
    {
        // Viewの場合、モデル内でGroupByを発行するため、設定できないようにする。
    }

    /**
     * 内部的なグループ化に利用するカラムを追加する。
     *
     * @param string $groupBy
     */
    protected function setInnerGroupBy($groupBy = null)
    {
        parent::setGroupBy($groupBy);
    }

    /**
     * レコードが作成可能な場合に、レコードを作成します。
     */
    public function create()
    {
        // Viewの場合、モデルからデータの作成を行えないようにします。
    }

    /**
     * 指定された条件のレコードを全て取得する。
     *
     * @param array $values
     * @param string $order
     * @param string $reverse
     * @return Vizualizer_Plugin_ModelIterator
     */
    public function getAllBy($values = array(), $order = "", $reverse = false)
    {
        $select = new Vizualizer_Query_Select($this->access, $this->viewTable);
        $select->addColumn($this->access->_W);
        foreach ($this->columnMap as $key => $value) {
            $select->addColumn($value, $key);
        }
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $select = $this->appendWhere($select, $key, $value);
            }
        }
        
        if ($this->groupBy != null) {
            $select->addGroupBy($this->groupBy);
        }
        
        if (!empty($order)) {
            if (is_array($order)) {
                foreach ($order as $index => $ord) {
                    if (is_array($reverse)) {
                        if (isset($reverse[$index])) {
                            $select->addOrder($this->getRealKey($ord), $reverse[$index]);
                        } else {
                            $select->addOrder($this->getRealKey($ord), false);
                        }
                    } else {
                        $select->addOrder($this->getRealKey($ord), $reverse);
                    }
                }
            } else {
                if (is_array($reverse)) {
                    if (isset($reverse[0])) {
                        $select->addOrder($this->getRealKey($order), $reverse[0]);
                    } else {
                        $select->addOrder($this->getRealKey($order), false);
                    }
                } else {
                    $select->addOrder($this->getRealKey($order), $reverse);
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
    public function findAllBy($values = array(), $order = "", $reverse = false)
    {
        $res = $this->getAllBy($values, $order, $reverse);
        
        $thisClass = get_class($this);
        $result = array();
        foreach ($res->all() as $i => $data) {
            $result[$i] = new $thisClass($data);
        }
        $res->close();
        
        return $result;
    }

    /**
     * レコードを特定のキーで検索する。
     */
    public function queryAllBy($select)
    {
        // Viewの場合、クエリを発行して検索はできないようにする。
    }

    /**
     * レコードの件数を取得する。
     */
    public function countBy($values = array(), $columns = "*")
    {
        $select = new Vizualizer_Query_Select($this->access, $this->viewTable);
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
            foreach ($keys as $index => $key) {
                $keys[$index] = $this->getRealKey($key);
            }
            $fullkey = "CONCAT(" . implode(", ", $keys) . ")";
        } else {
            $fullkey = $this->getRealKey($key);
            if (isset($default) && $default != null) {
                if (is_numeric($default) && (substr($default, 0, 1) != "0" || strlen($default) == 1)) {
                    // 全て数字で先頭が0でない、もしくは1桁のみの場合は数値データとして扱う
                    $fullkey = "COALESCE(" . $fullkey . ", " . $default . ")";
                } else {
                    $fullkey = "COALESCE(" . $fullkey . ", '" . $default . "')";
                }
            }
        }
        if ($op != "in" && $op != "nin" && is_array($value)) {
            foreach ($value as $item) {
                if (empty($item)) {
                    return $select;
                }
            }
            $value = implode("-", $value);
        }
        switch ($op) {
            case "eq":
                if ($value == null) {
                    $select->addWhere($fullkey . " IS NULL");
                } else {
                    $select->addWhere($fullkey . " = ?", array($value));
                }
                break;
            case "ne":
                if ($value == null) {
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
                foreach ($value as $v) {
                    if (!empty($placeholders)) {
                        $placeholders .= ",";
                    }
                    $placeholders .= "?";
                }
                $select->addWhere($fullkey . " in (" . $placeholders . ")", $value);
                break;
            case "nin":
                if (!is_array($value)) {
                    $value = array($value);
                }
                $placeholders = "";
                foreach ($value as $v) {
                    if (!empty($placeholders)) {
                        $placeholders .= ",";
                    }
                    $placeholders .= "?";
                }
                $select->addWhere($fullkey . " NOT IN (" . $placeholders . ")", $value);
                break;
            default:
                break;
        }
        return $select;
    }

    /**
     * 指定したトランザクション内にて主キーベースでデータの保存を行う。
     * 主キーが存在しない場合は何もしない。
     * また、モデル内のカラムがDBに無い場合はスキップする。
     * データ作成日／更新日は自動的に設定される。
     */
    public function save()
    {
        // Viewは更新を行えない
    }

    /**
     * 指定したトランザクション内にて主キーベースでデータの保存を行う。
     * 主キーが存在しない場合は何もしない。
     * また、モデル内のカラムがDBに無い場合はスキップする。
     * データ作成日／更新日は自動的に設定される。
     */
    public function saveAll($list)
    {
        // Viewは更新を行えない
        return array();
    }

    /**
     * 指定したトランザクション内にて主キーベースでデータの削除を行う。
     * 主キーが存在しない場合は何もしない。
     */
    public function delete()
    {
        // Viewは削除を行えない
    }
}
