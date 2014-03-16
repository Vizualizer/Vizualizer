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
 * Smarty mb_mail_text modifier plugin
 *
 * Type: modifier<br>
 * Name: mb_mail_text<br>
 * Date: Jul 5, 2011
 * Purpose: insert cr+lf when long text
 * Input: string to split
 * Example: {$var|mb_mail_text:76}
 *
 * @author Naohisa Minagawa
 * @version 1.0
 * @param string
 * @param integer
 * @return string
 */
function smarty_modifier_mb_mail_text($string, $length = 76)
{
    if ($length == 0) {
        return '';
    }

    $result = array();
    $lines = explode("\r\n", $string);

    foreach ($lines as $line) {
        while (mb_strlen($line) > $length) {
            $result[] = mb_substr($line, 0, $length);
            $line = mb_substr($line, $length);
        }
        $result[] = $line;
    }

    return implode("\r\n", $result);
}
?>
