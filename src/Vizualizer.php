<?php
/**
 * Copyright (C) 2012 Clay System All Rights Reserved.
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
 * フレームワークの起点となるクラス
 * 
 * @package Base
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer{
	/**
	 * フレームワークの起動処理を行うメソッドです。
	 */
	public static function startup(){
		// システムのルートディレクトリを設定
		if (!defined('VIZUALIZER_ROOT')) {
			define('VIZUALIZER_ROOT', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.".."));
		}
		
		// システムのルートURLへのサブディレクトリを設定
		if (!defined('VIZUALIZER_SUBDIR')) {
			if(substr($_SERVER["DOCUMENT_ROOT"], -1) == "/"){
				define('VIZUALIZER_SUBDIR', str_replace(substr($_SERVER["DOCUMENT_ROOT"], 0, -1), "", VIZUALIZER_ROOT));
			}else{
				define('VIZUALIZER_SUBDIR', str_replace($_SERVER["DOCUMENT_ROOT"], "", VIZUALIZER_ROOT));
			}
		}
		
		// システムのルートURLを設定
		if (!defined('VIZUALIZER_URL')) {
			define('VIZUALIZER_URL', "http".((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")?"s":"")."://".$_SERVER["SERVER_NAME"].VIZUALIZER_SUBDIR);
		}

		// ライブラリのクラス自動ローダーを初期化する。
		require(VIZUALIZER_ROOT.DIRECTORY_SEPARATOR."Vizualizer".DIRECTORY_SEPARATOR."Autoloader.php");
		Vizualizer_Autoloader::register();
		
		// 後起動処理を追加
		Clay_Bootstrap_PhpVersion::start();
		Clay_Bootstrap_CheckPermission::start();
		Clay_Bootstrap_Configure::start();
		Clay_Bootstrap_ErrorMessage::start();
		Clay_Bootstrap_Timezone::start();
		Clay_Bootstrap_Locale::start();
		Clay_Bootstrap_UserAgent::start();
		Clay_Bootstrap_SessionId::start();
		Clay_Bootstrap_Parameter::start();
		Clay_Bootstrap_Session::start();
		Clay_Bootstrap_TemplateName::start();
		Clay_Bootstrap_Filter::start();
		
		register_shutdown_function(array("Clay", "shutdown"));
	}
	
	/**
	 * フレームワークの終了処理を行うメソッドです。
	 */
	public static function shutdown(){
		
	}
	
	/**
	 * フレームワークでエラーが発生してキャッチされなかった場合の処理を記述するメソッドです。
	 */
    public static function error($code, $message, $ex = null){
    	// ダウンロードの際は、よけいなバッファリングをクリア
    	while(ob_get_level() > 0){
    		ob_end_clean();
    	}
    		
    	// エラーログに書き込み
    	Clay_Logger::writeError($message."(".$code.")", $ex);
    	
    	// カスタムエラーページのパス
    	$path = $_SERVER["CONFIGURE"]->site_home.$_SERVER["USER_TEMPLATE"].DIRECTORY_SEPARATOR."ERROR_".$code.".html";
    
    	// ファイルがある場合はエラーページを指定ファイルで出力
    	if(file_exists($path)){
    		try{
    			header("HTTP/1.0 ".$code." ".$message, true, $code);
    			header("Status: ".$code." ".$message);
    			header("Content-Type: text/html; charset=utf-8");
    			$_SERVER["TEMPLATE"]->display("ERROR_".$code.".html");
    		}catch(Exception $e){
    			// エラーページでのエラーは何もしない
    		}
    	}else{
    		// エラーページが無い場合はデフォルト
    		header("HTTP/1.0 ".$code." ".$message, true, $code);
    		header("Status: ".$code." ".$message);
    		header("Content-Type: text/html; charset=utf-8");
    		echo $message;
    	}
    	exit;
    }
}
