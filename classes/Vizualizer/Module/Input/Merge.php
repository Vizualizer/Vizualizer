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
 * カラムを結合するクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Input_Merge extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        if ($params->check("target") && $params->check("result")) {
            $columns = explode(",", $params->get("target"));
            $value = "";
            $post = Vizualizer::request();
            foreach ($columns as $i => $column) {
                if ($i > 0) {
                    $value .= $params->get("delimiter");
                }
                if (is_array($post[$column])) {
                    foreach ($post[$column] as $j => $data) {
                        if ($i > 0 || $j > 0) {
                            $data .= $params->get("delimiter");
                        }
                        $value .= $data;
                    }
                } else {
                    $value .= $post[$column];
                }
            }
            $post->set($params->get("result"), $value);
        }
    }
}
