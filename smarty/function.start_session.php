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
    // POSTにINPUT=NEWが渡った場合は、入力をクリアする。
    $post = Vizualizer::request();
    /*
     * $inputData = Vizualizer_Session::get(Vizualizer::INPUT_KEY); if
     * (is_array($inputData)) { if (array_key_exists(TEMPLATE_DIRECTORY,
     * $inputData)) { if (isset($post["INPUT"]) && $post["INPUT"] == "NEW") {
     * unset($inputData[TEMPLATE_DIRECTORY]); } //
     * INPUT_DATAのセッションの内容をPOSTに戻す。（POST優先） if
     * (is_array($inputData[TEMPLATE_DIRECTORY])) { foreach
     * ($inputData[TEMPLATE_DIRECTORY] as $key => $value) { if
     * (!isset($post[$key])) { $post[$key] = $value; } } } }
     * Vizualizer_Session::set(Vizualizer::INPUT_KEY, $inputData); }
     */
    Vizualizer_Logger::writeDebug("Page Session Started.");
}
