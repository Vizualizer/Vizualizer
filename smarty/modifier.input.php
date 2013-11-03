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
 * Smarty {input} modifier plugin
 *
 * Type:     modifier<br>
 * Name:     input<br>
 * Purpose:  modify value prefer input<br>
 * @author   Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string|null
 */
function smarty_modifier_input($value, $key, $subkey = ""){
	if($value === null || $value === "" || isset($_POST[$key])){
		if($subkey != "" && is_array($_POST[$key])){
			return $_POST[$key][$subkey];
		}else{
			return $_POST[$key];
		}
	}
	return $value;
}
?>