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
 * Smarty {check} modifier plugin
 *
 * Type:     modifier<br>
 * Name:     check<br>
 * Purpose:  modify value prefer input<br>
 * @author   Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string|null
 */
function smarty_modifier_zeroday($value){
	if($value == "0000-00-00"){
		return "";
	}
	if($value == "0000-00-00 00:00:00"){
		return "";
	}
	return $value;
}
?>