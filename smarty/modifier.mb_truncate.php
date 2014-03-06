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
 * Smarty mb_truncate modifier plugin
 *
 * Type: modifier<br>
 * Name: mb_truncate<br>
 * Date: Feb 24, 2003
 * Purpose: truncate multibyte text module
 * Input: string to catenate
 * Example: {$var|cat:"foo"}
 *
 * @link http://smarty.php.net/manual/en/language.modifier.mb_truncate.php cat
 *       (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_mb_truncate($string, $length = 80, $etc = '...')
{
    if ($length == 0) {
        return '';
    }

    if (mb_strlen($string) > $length) {
        return mb_substr($string, 0, $length) . $etc;
    } else {
        return $string;
    }
}
?>
