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
 * Smarty mb_mail_text modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mb_mail_text<br>
 * Date:     Jul 5, 2011
 * Purpose:  insert cr+lf when long text
 * Input:    string to split
 * Example:  {$var|mb_mail_text:76}
 * @author   Naohisa Minagawa
 * @version 1.0
 * @param string
 * @param integer
 * @return string
 */
function smarty_modifier_mb_mail_text($string, $length = 76) {
    if ($length == 0) {return '';}
	
	$result = array();
	$lines = explode("\r\n", $string);
	
	foreach($lines as $line){
		while(mb_strlen($line) > $length){
			$result[] = mb_substr($line, 0, $length);
			$line = mb_substr($line, $length);
		}
		$result[] = $line;
	}
	
	return implode("\r\n", $result);
}
?>
