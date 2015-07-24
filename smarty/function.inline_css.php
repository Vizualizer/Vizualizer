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
 * Smarty {inline_css} function plugin
 *
 * Type: function<br>
 * Name: inline_css<br>
 * Purpose: inline css activate plugin.<br>
 *
 * @author Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string null
 */
function smarty_function_inline_css($params, $template)
{
    $attr = Vizualizer::attr();
    $script = "";
    if (!empty($params["src"])) {
        $script .= "<style";
        if (!empty($params["type"])) {
            $script .= " type=\"".$params["type"]."\"";
        } else {
            $script .= " type=\"text/css\"";
        }
        if (!empty($params["media"])) {
            $script .= " media=\"".$params["media"]."\"";
        } else {
            $script .= " media=\"all\"";
        }
        $script .= ">\r\n";
        if (substr($params["src"], 0, 7) !== "http://" && substr($params["src"], 0, 8) !== "https://") {
            if (substr($params["src"], 0, 1) !== "/") {
                $info = pathinfo($attr["templateName"]);
                $params["src"] = $info["dirname"] . "/" . $params["src"];
            }
            $params["src"] = VIZUALIZER_URL . $params["src"];
        }
        if (class_exists("Memcache") && Vizualizer_Configure::get("memcache_contents") && Vizualizer_Configure::get("memcache") !== "") {
            // memcacheの場合は静的コンテンツをmemcacheにキャッシュする。
            $contents = Vizualizer_Cache_Factory::create("inlineCss_" . urlencode($params["src"]));
            $data = $contents->export();
            if (empty($data)) {
                if (($buffer = file_get_contents($params["src"])) !== FALSE) {
                    $contents->set("content", $buffer);
                }
                $data = $contents->export();
            }
            $script .= $data["content"];
        } else {
            if (($buffer = file_get_contents($params["src"])) !== FALSE) {
                $script .= $buffer;
            }
        }
        $script .= "\r\n</style>";
    }
    return $script;
}
