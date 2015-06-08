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
 * 入力の指定のキーの値が配列でない場合、空の配列を設定する。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Input_AsArray extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        if($params->check("key")){
            $post = Vizualizer::request();
            if(!is_array($post[$params->get("key")])){
                if($params->check("split") && !empty($post[$params->get("key")])){
                    $post->set($params->get("key"), explode($params->get("split"), $post[$params->get("key")]));
                }else{
                    $post->set($params->get("key"), array());
                }
            }
        }
    }
}
