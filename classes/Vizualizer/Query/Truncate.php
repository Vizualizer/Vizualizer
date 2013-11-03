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
 * データベースクリーンアップ処理用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Query_Truncate{
	/** 
	 * @var string 接続に使用するモジュール名 
	 */
	private $module;

	private $tables;

	public function __construct($table){
		$this->module = $table->getModuleName();
		$this->tables = $table->_T;
	}

	public function buildQuery(){
		// クエリのビルド
		$sql = "TRUNCATE ".$this->tables;

		return $sql;
	}

	public function execute(){
		// クエリのビルド
		$sql = $this->buildQuery();

		// クエリを実行する。
		try{
			$connection = Vizualizer_Database_Factory::getConnection($this->module);
			Vizualizer_Logger::writeDebug($sql);
			$result = $connection->query($sql);
		}catch(Exception $e){
			Vizualizer_Logger::writeError($sql, $e);
			throw new Vizualizer_Exception_Database($e);
		}

		return $result;
	}
}
 