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