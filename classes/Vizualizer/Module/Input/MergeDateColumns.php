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
 * 日付形式としてテキストを結合するクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Input_MergeDate extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        $post = Vizualizer::request();
        $errors = array();
        $result = "";
        if ($params->check("year") && isset($post[$params->get("year")]) && is_numeric($post[$params->get("year")]) && $params->check("month") && isset($post[$params->get("month")]) && is_numeric($post[$params->get("month")]) && $params->check("day") && isset($post[$params->get("day")]) && is_numeric($post[$params->get("day")])) {
            $result .= sprintf("%04d", $post[$params->get("year")]);
            $result .= "-";
            $result .= sprintf("%02d", $post[$params->get("month")]);
            $result .= "-";
            $result .= sprintf("%02d", $post[$params->get("day")]);
            if (Vizualizer::now()->strToTime($result)->date("Y-m-d") != $result) {
                throw new Vizualizer_Exception_Invalid($params->get("result"), $params->get("result_name") . $params->get("suffix", "は日付の指定が正しくありません。"));
            }
            if ($params->check("hour") && isset($post[$params->get("hour")]) && is_numeric($post[$params->get("hour")]) && $params->check("minute") && isset($post[$params->get("minute")]) && is_numeric($post[$params->get("minute")])) {
                if (!empty($post[$params->get("result")])) {
                    $post[$params->get("result")] .= " ";
                }
                $result .= sprintf("%02d", $post[$params->get("hour")]);
                $result .= ":";
                $result .= sprintf("%02d", $post[$params->get("minute")]);
                if (Vizualizer::now()->strToTime($result)->date("Y-m-d H:i") != $result) {
                    throw new Vizualizer_Exception_Invalid($params->get("result"), $params->get("result_name") . $params->get("suffix", "は日付の指定が正しくありません。"));
                }
                if ($params->check("second") && isset($post[$params->get("second")]) && is_numeric($post[$params->get("second")])) {
                    $result .= ":";
                    $result .= sprintf("%02d", $post[$params->get("second")]);
                    if (Vizualizer::now()->strToTime($result)->date("Y-m-d H:i:s") != $result) {
                        throw new Vizualizer_Exception_Invalid($params->get("result"), $params->get("result_name") . $params->get("suffix", "は日付の指定が正しくありません。"));
                    }
                }
            } elseif ($params->check("hourminute") && isset($post[$params->get("hourminute")])) {
                if (!empty($result)) {
                    $result .= " ";
                }
                $result .= $post[$params->get("hourminute")];
                if (Vizualizer::now()->strToTime($result)->date("Y-m-d H:i") != $result) {
                    throw new Vizualizer_Exception_Invalid($params->get("result"), $params->get("result_name") . $params->get("suffix", "は日付の指定が正しくありません。"));
                }
            }
            $post->set($params->get("result"), $result);
        }
    }
}
?>
