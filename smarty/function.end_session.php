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
 * Smarty {end_session} function plugin
 *
 * Type: function<br>
 * Name: end_session<br>
 * Purpose: end session module.<br>
 * 
 * @author Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string null
 */
function smarty_function_end_session($params, $template)
{
    // POSTの内容をセッションに戻す
    $post = Vizualizer::request();
    $inputData = Vizualizer_Session::get("INPUT_DATA");
    if (is_array($post)) {
        $inputData = array(TEMPLATE_DIRECTORY => array());
        foreach ($post as $key => $value) {
            $inputData[TEMPLATE_DIRECTORY][$key] = $value;
        }
    }
    
    // テンプレートに各種変数を割り当て
    $attr = Vizualizer::attr();
    $template = $attr["template"];
    $template->assign("configure", Vizualizer_Configure::values());
    $template->assign("post", Vizualizer::request());
    $template->assign("attr", $attr);
    $template->assign("sessionName", session_name());
    $template->assign("sessionId", session_id());
    
    Vizualizer_Logger::writeDebug("Page Session Ended.");
}
?>