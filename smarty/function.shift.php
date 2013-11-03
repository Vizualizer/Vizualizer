<?php
/**
 * This file is part of CLAY Framework for view-module based system.
 *
 * @author    Naohisa Minagawa <info@clay-system.jp>
 * @copyright Copyright (c) 2010, Naohisa Minagawa
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 * @since PHP 5.3
 * @version   3.0.0
 */

/**
 * Smarty {shift} function plugin
 *
 * Type:     function<br>
 * Name:     shift<br>
 * Purpose:  shift page module.<br>
 * @author   Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string|null
 */
function smarty_function_shift($params, $smarty, $template){
	if(!empty($params["path"]) && !empty($_POST)){
		// 遷移時に既に出力したバッファをクリアする。
		ob_end_clean();
		ob_start();
		// 別のテンプレートに対してdisplayを呼び出す。
		$_SERVER["TEMPLATE"]->display($params["path"]);
		exit;
	}
}
?>