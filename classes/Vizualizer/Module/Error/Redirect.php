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
 * チェックしたエラーの内容をコミットして、リダイレクトさせる。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Error_Redirect extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        $post = Vizualizer::request();
        $attr = Vizualizer::attr();
        $errors = $attr[Vizualizer::ERROR_KEY];
        if (!empty($errors) && ($params->check("url") || $params->check("urlkey"))) {
            VizualizerSession::set("LAST_ERRORS", array(Vizualizer::ERROR_KEY => $errors, Vizualizer::INPUT_KEY => $post));
            if ($params->check("url")) {
                header("Location: " . $params->get("url"));
                exit();
            } else {
                header("Location: " . $post[$params->get("urlkey")]);
                exit();
            }
        }
    }
}
