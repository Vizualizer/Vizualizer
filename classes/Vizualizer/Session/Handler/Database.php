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
 * データベースにセッション情報を持たせるためのハンドラです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Session_Handler_Database extends Clay_Session_Handler
{

    private $table;

    private $id_key;

    private $data_key;

    /**
     * コンストラクタ
     */
    public function __construct($table = "session_stores", $id_key = "session_id", $data_key = "session_data")
    {
        list ($module, $name) = explode("_", $table, 2);
        $names = explode("_", $name);
        $name = "";
        $module = strtoupper(substr($module, 0, 1)) . strtolower(substr($module, 1));
        foreach ($names as $part) {
            $name .= strtoupper(substr($part, 0, 1)) . strtolower(substr($part, 1));
        }
        $name .= "Table";
        $loader = new Vizualizer_Plugin($module);
        $this->table = $loader->loadTable($name);
        $this->id_key = $id_key;
        $this->data_key = $data_key;

        // 初期化時にクラスのローディングを行う。
        $select = new Vizualizer_Query_Select($this->table);
        $insert = new Vizualizer_Query_Replace($this->table);
    }

    /**
     * セッションを開始する.
     *
     * @param string $save_path セッションを保存するパス(使用しない)
     * @param string $session_name セッション名(使用しない)
     * @return bool セッションが正常に開始された場合 true
     */
    public function open($savePath, $sesionName)
    {
        return true;
    }

    /**
     * セッションを閉じる.
     *
     * @return bool セッションが正常に終了した場合 true
     */
    function close()
    {
        return true;
    }

    /**
     * セッションのデータををDBから読み込む.
     *
     * @param string $id セッションID
     * @return string セッションデータの値
     */
    function read($id)
    {
        $id_key = $this->id_key;
        $data_key = $this->data_key;

        // セッションデータを取得する。
        $select = new Vizualizer_Query_Select($this->table);
        $select->addColumn($this->table->_W);
        $select->addWhere($this->table->$id_key . " = ?", array($id));
        $result = $select->execute();

        return $result[0][$data_key];
    }

    /**
     * セッションのデータをDBに書き込む.
     *
     * @param string $id セッションID
     * @param string $sess_data セッションデータの値
     * @return bool セッションの書き込みに成功した場合 true
     */
    function write($id, $sess_data)
    {
        $id_key = $this->id_key;
        $data_key = $this->data_key;

        // セッションに値を設定
        try {
            $insert = new Vizualizer_Query_Replace($this->table);
            $sqlval = array($id_key => $id, $data_key => $sess_data);
            $sqlval["create_time"] = $sqlval["update_time"] = Vizualizer_Data_Calendar::now()->date("Y-m-d H:i:s");
            Vizualizer_Logger::writeDebug($insert->showQuery($sqlval));
            $insert->execute($sqlval);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * セッションを破棄する.
     *
     * @param string $id セッションID
     * @return bool セッションを正常に破棄した場合 true
     */
    function destroy($id)
    {
        $id_key = $this->id_key;

        // セッションに値を設定
        try {
            $delete = new Vizualizer_Query_Delete($this->table);
            $delete->addWhere($this->table->$id_key . " = ?", array($id));
            Vizualizer_Logger::writeDebug($delete->showQuery());
            $delete->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * ガーベジコレクションを実行する.
     *
     * 引数 $maxlifetime の代りに 定数 MAX_LIFETIME を使用する.
     *
     * @param integer $maxlifetime セッションの有効期限
     */
    function clean($maxlifetime)
    {
        $limit = Vizualiezr_Data_Calendar::now()->strToTime("-" . $maxlifetime . " secs")->date("Y-m-d H:i:s");

        // セッションに値を設定
        try {
            $delete = new Vizualizer_Query_Delete($this->table);
            $delete->addWhere($this->table->update_time . " < ?", array($limit));
            Vizualizer_Logger::writeDebug($delete->showQuery());
            $delete->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

