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
 * Smarty loadmodule prefilter plugin
 *
 * File: prefilter.loadmodule.php<br>
 * Type: prefilter<br>
 * Name: loadmodule<br>
 * Date: Jan 25, 2003<br>
 * Purpose: Convert meta loadmodule tag to smarty loadmodule tag<br>
 * Install: Drop into the plugin directory, call
 * <code>$smarty->load_filter('pre','loadmodule');</code>
 * from application.
 *
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author Contributions from Lars Noschinski <lars@usenet.noschinski.de>
 * @version 1.0
 * @param string $source input string
 * @param object &$smarty Smarty object
 * @return string filtered output
 */
function smarty_prefilter_loadmodule($source, $smarty)
{
    // メタタグを全て抽出する。
    // if(preg_match_all("/<meta((\\s+([a-z0-9]+)\\s*=\\s*\"([^\"]*)\")+)\\s+\\/>/i",
    // $source, $params, PREG_SET_ORDER) > 0){
    $definition = "";
    $modules = "";
    $redirect = "";
    if (preg_match_all("/<meta ((?:\\s*[a-z0-9_:.-]+(?:=(?:[^> '\"\\t\\n]+|(?:'.*?')|(?:\".*?\")))?)*)\\s*\\/?\\s*>/is", $source, $params, PREG_SET_ORDER) > 0) {
        // メタタグに使用されているパラメータを抽出
        $list = array();
        foreach ($params as $param) {
            // パラメータを細分化
            $item = array();
            $param[1] = str_replace("\\\"", "%BSDQ%", $param[1]);
            if (preg_match_all("/([a-z0-9_:.-]+)(?:=([^> '\"\\t\\n]+|(?:'.*?')|(?:\".*?\")))?/is", $param[1], $keyPairs, PREG_SET_ORDER) > 0) {
                foreach ($keyPairs as $keyPair) {
                    if (!isset($keyPair[2])) {
                        $item[$keyPair[1]] = "";
                    } elseif (substr($keyPair[2], 0, 1) == "'" && substr($keyPair[2], -1) == "'" || substr($keyPair[2], 0, 1) == "\"" && substr($keyPair[2], -1) == "\"") {
                        $keyPair[2] = str_replace("%BSDQ%", "\\\"", $keyPair[2]);
                        $item[$keyPair[1]] = substr($keyPair[2], 1, -1);
                    } else {
                        $keyPair[2] = str_replace("%BSDQ%", "\\\"", $keyPair[2]);
                        $item[$keyPair[1]] = $keyPair[2];
                    }
                }
            }
            $list[$param[0]] = $item;
        }
        // paramsの値を整形したものに置き換える。
        $params = $list;
        foreach ($params as $tag => $param) {
            if (!array_key_exists("name", $param)) {
                continue;
            }
            switch ($param["name"]) {
                // モジュール呼び出し用メタタグの解析
                case "loadmodule":
                    $definition .= "<!--{define_module name=\"" . $param["content"] . "\"}-->\r\n";
                    $modules .= "<!--{" . $param["name"] . " name=\"" . $param["content"] . "\"";
                    foreach ($param as $name => $value) {
                        if ($name != "name" && $name != "content") {
                            $modules .= " " . $name . "=\"" . $value . "\"";
                        }
                    }
                    $modules .= "}-->\r\n";
                    $source = str_replace($tag, "", $source);
                    break;
                // リダイレクト用メタタグの解析
                case "redirect":
                    $redirect = "<!--{" . $param["name"] . " url=\"" . $param["content"] . "\"}-->\r\n";
                    $source = str_replace($tag, "", $source);
                    break;
                // ページシフト用のメタタグの解析
                case "shift":
                    $redirect = "<!--{" . $param["name"] . " path=\"" . $param["content"] . "\"}-->\r\n";
                    $source = str_replace($tag, "", $source);
                    break;
                default:
                    break;
            }
        }
    }
    // 解析したテンプレートの処理を再構成する。
    $source = $definition . "<!--{start_session}-->\r\n" . $modules . "<!--{end_session}-->\r\n" . $redirect . $source . "\r\n";
    return $source;
}
?>
