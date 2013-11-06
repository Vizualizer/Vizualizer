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
 * Smarty {start_session} function plugin
 *
 * Type: function<br>
 * Name: start_session<br>
 * Purpose: start session module.<br>
 *
 * @author Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string null
 */
function smarty_function_start_session($params, $template)
{
    // セッションをスタートし、とりあえず成功のヘッダを送信する
    session_cache_limiter('must-revalidate');
    @session_start();
    header("HTTP/1.1 200 OK");
    Vizualizer_Session::startup();
    
    // POSTにINPUT=NEWが渡った場合は、入力をクリアする。
    $post = Vizualizer::request();
    $inputData = Vizualizer_Session::get("INPUT_DATA");
    if (is_array($inputData)) {
        if (array_key_exists(TEMPLATE_DIRECTORY, $inputData)) {
            if (isset($post["INPUT"]) && $post["INPUT"] == "NEW") {
                unset($inputData[TEMPLATE_DIRECTORY]);
            }
            
            // INPUT_DATAのセッションの内容をPOSTに戻す。（POST優先）
            if (is_array($inputData[TEMPLATE_DIRECTORY])) {
                foreach ($inputData[TEMPLATE_DIRECTORY] as $key => $value) {
                    if (! isset($post[$key])) {
                        $post[$key] = $value;
                    }
                }
            }
        }
        Vizualizer_Session::set("INPUT_DATA", $inputData);
    }
    Vizualizer_Logger::writeDebug("Page Session Started.");
}
?>