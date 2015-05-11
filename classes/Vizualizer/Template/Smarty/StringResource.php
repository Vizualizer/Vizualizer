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
 * 文字列をテンプレートにするリソースクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Template_Smarty_StringResrouce extends Vizualizer_Template
{

    public static function get_template ($template, &$tpl_source, &$smarty_obj)
    {
        // テンプレートのソースとして指定された文字列を指定
        $tpl_source = $template;
        return true;
    }

    public static function get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj)
    {
        $tpl_timestamp = time();
        return true;
    }

    public static function get_secure($tpl_name, &$smarty_obj)
    {
        // 全てのテンプレートがセキュアであると仮定します
        return true;
    }

    public static function get_trusted($tpl_name, &$smarty_obj)
    {
        // テンプレートから使用しません
    }
}
