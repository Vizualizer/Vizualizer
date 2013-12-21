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
 * デフォルトの入力値として当日の日付を設定します。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Input_Today extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        if ($params->check("result")) {
            $post = Vizualizer::request();
            if($params->check("parent")){
                $parent = $post[$params->get("parent")];
                if(empty($parent[$params->check("result")])){
                    $parent[$params->get("result")] = date($params->get("format", "Y-m-d"));
                    $post->set($params->get("parent"), $parent);
                }
            }else{
                if(empty($post[$params->check("result")])){
                    $post->set($params->get("result"), date($params->get("format", "Y-m-d")));
                }
            }
        }
    }
}
