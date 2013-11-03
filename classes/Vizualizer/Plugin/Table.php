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
 * データベーステーブルラッパー用のクラスです。
 * 
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_Table{
	/** 
	 * @var string DB接続に使用するモジュール名
	 */
	protected $module;
	
	/**
	 * @var string DB接続に使用するテーブル名
	 */
	protected $tableName;

	/**
	 * @var array[Vizualizer_Plugin_Table_Column] カラムリスト 
	 */
	private $_COLUMNS;

	/**
	 * @var array[string] 主キーのリスト 
	 */
	private $_PKEYS;

	/**
	 * @var array[string] カラムフル名リスト 
	 */
	private $_FIELDS;

	/** 
	 * @var string 元テーブル名 
	 */
	public $_B;

	/** 
	 * @var string テーブル名
	 */
	public $_T;

	/**
	 * @var string カラム用テーブル名
	 */
	public $_C;

	/** 
	 * @var string テーブルワイルドカード
	 */
	public $_W;

	/**
	 * データベーステーブルモデルを初期化
	 * @param string $tableName テーブル名
	 * @param string $module モジュール名
	 */
	public function __construct($tableName, $module = DEFAULT_PACKAGE_NAME){
		// モジュール名を保存
		$this->module = strtolower($module);
		
		// テーブル名を保存
		$this->tableName = $tableName;
		
		// 初期化処理
		$this->initialize();
	}

	protected function initialize(){
		// DBの接続を取得する。
		$connection = Vizualizer_Database_Factory::getConnection($this->module, true);
		
		// 構成されたカラム情報を元に設定値を生成
		$this->_B = $this->_T = $this->_C = $connection->escape_identifier($this->tableName);
		$this->_W = $this->_C.".*";

		// テーブル構成のキャッシュがある場合にはキャッシュからテーブル情報を取得
		$tableConfigure = Vizualizer_Cache_Factory::create("table_".$this->tableName);
		if($tableConfigure->options == ""){
			try{
				// テーブルの定義を取得
				$options = $connection->columns($this->_T);
			}catch(Exception $e){
				$this->install($connection);
				// テーブルの定義を再取得
				$options = $connection->columns($this->_T);
			}
				
			// テーブルの主キーを取得
			$keys = $connection->keys($this->_T);

			// テーブルのインデックスを取得
			$indexes = $connection->indexes($this->_T);
				
			// テーブルのリレーションを取得
			$relations = $connection->relations($this->_T);
			
			// テーブルの設定をデータキャッシュに登録する。
			$tableConfigure->import(array("options" => $options, "keys" => $keys));
		}

		// カラム情報を設定
		$this->_COLUMNS = array();
		$this->_FIELDS = array();
		foreach($tableConfigure->options as $option){
			$column = new Vizualizer_Plugin_Table_Column($this, $option);
			$field = $column->field;
			$this->_COLUMNS[] = $field;
			$this->_FIELDS[$field] = $column;
		}
		// 主キー情報を設定
		$this->_PKEYS = array();
		if(is_array($tableConfigure->keys)){
			foreach($tableConfigure->keys as $key){
				$this->_PKEYS[] = $key;
			}
		}
	}

	/**
	 * テーブルの接続に使用しているモジュールの名前を取得する。
	 * @return string モジュール名
	 */
	public function getModuleName(){
		return $this->module;
	}

	/**
	 * テーブルのカラム情報を取得
	 * @param string $name カラム情報
	 * @return string カラム名
	 */
	public function __get($name){
		return $this->_FIELDS[$name];
	}

	/**
	 * テーブルのカラム情報が設定されているかチェックする。
	 * @param string $name カラム情報
	 * @return boolean カラムが存在している場合にはtrue
	 */
	public function __isset($name){
		return isset($this->_FIELDS[$name]);
	}

	/**
	 * テーブルエイリアス名を設定する。
	 * @param string $tableName エイリアス名
	 * @return Vizualizer_Plugin_Table テーブルオブジェクト
	 */
	public function setAlias($tableName){
		// DBの接続を取得する。
		$connection = Vizualizer_Database_Factory::getConnection($this->module, true);
		
		// エイリアスの設定に応じて、テーブル内の各変数を調整
		$this->_C = $connection->escape_identifier($tableName);
		$this->_T = $this->_B." AS ".$connection->escape_identifier($tableName);
		$this->_W = $this->_C.".*";
		return $this;
	}

	/**
	 * 主キーのリストを取得する。
	 * @return array[string] 主キーのリスト
	 */
	public function getPrimaryKeys(){
		return $this->_PKEYS;
	}
	
	public function getColumns(){
		return $this->_COLUMNS;
	}

	/**
	 * テーブルクラスを文字列として扱った場合にテーブル名として扱われるようにする。
	 * @return string テーブルクラスの文字列表現
	 */
	public function __toString(){
		return $this->_T;
	}
	
	public function __sleep(){
		return array("tableName", "module");
	}
	
	public function __wakeup(){
		$this->initialize();
	}
	
	protected function createTable($connection, $columns, $keys, $uniques, $indexes, $datas = array()){
		$sql = "CREATE TABLE IF NOT EXISTS ".$this->_T."(";
		foreach($columns as $index => $column){
			if($index > 0){
				$sql .= ", ";
			}
			if(!isset($column["auto"])) $column["auto"] = false;
			if(!isset($column["null"])) $column["null"] = false;
			$sql .= "`".$column["name"]."` ".$column["type"].(!empty($column["size"])?"(".$column["size"].")":"");
			$sql .= " COLLATE utf8_unicode_ci".($column["auto"]?" NOT NULL AUTO_INCREMENT":(($column["null"])?" DEFAULT NULL":" NOT NULL".((isset($column["default"])?" DEFAULT ".$column["default"]:""))));
		}
		if(is_array($keys) && !empty($keys)){
			$sql .= ", PRIMARY KEY(`".implode("`, `", $keys)."`)";
		}
		foreach($uniques as $name => $ukeys){
			if(!empty($name) && is_array($ukeys) && !empty($ukeys)){
				$sql .= ", UNIQUE KEY `".$name."` (`".implode("`, `", $ukeys)."`)";
			}
		}
		foreach($indexes as $name => $ukeys){
			if(!empty($name) && is_array($ukeys) && !empty($ukeys)){
				$sql .= ", KEY `".$name."` (`".implode("`, `", $ukeys)."`)";
			}
		}
		$sql .= ") ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$connection->query($sql);
		
		$cols = array();
		foreach($columns as $index => $column){
			$cols[] = $column["name"];
		}
		foreach($datas as $data){
			$keys = array();
			$values = array();
			foreach($cols as $col){
				if($col == "create_time" || $col == "update_time"){
					$keys[] = $connection->escape_identifier($col);
					$values[] = "NOW()";
				}else{
					if(isset($data[$col])){
						$keys[] = $connection->escape_identifier($col);
						$values[] .= "'".$connection->escape($data[$col])."'";
					}
				}
			}
			$sql = "INSERT INTO ".$this->_T."(".implode(", ", $keys).") VALUES (".implode(", ", $values).")";
			$connection->query($sql);
		}
	}
	
	protected function createConstraints($connection, $relations){
		$sql = "ALTER TABLE ".$this->_T;
		$index = 0;
		foreach($relations as $name => $relation){
			$rels = implode(".", $relation);
			if(!empty($relation) && is_array($rels) && count($rels) == 2){
				if($index > 0){
					$sql .= ", ";
				}
				$sql .= " ADD CONSTRAINT `fk_".$this->tableName."_".$name."` FOREIGN KEY (`".$name."`) ";
				$sql .= "REFERENCES `".$rels[0]."` (`".$rels[1]."`) ON DELETE CASCADE ON UPDATE CASCADE";
				$index ++;
			}
		}
		$connection->query($sql);
	}
}
 