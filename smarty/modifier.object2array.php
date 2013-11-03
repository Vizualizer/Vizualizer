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
 * Smarty {object2array} modifier plugin
 *
 * Type:     modifier<br>
 * Name:     object2array<br>
 * Purpose:  modify object value to array<br>
 * @author   Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string|null
 */
function smarty_modifier_object2array($value){
	if(is_object($value)){
		$value = (array) $value;
	}
	if(is_array($value)){
		foreach($value as $key => $v){
			$value[$key] = smarty_modifier_object2array($v);
		}
	}
	return $value;
}
?>