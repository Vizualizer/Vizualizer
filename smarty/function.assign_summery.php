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
function smarty_function_assign_summery($params, $smarty, $template)
{
    // varパラメータは必須です。
    if (empty($params['var'])) {
        trigger_error("assign_summery: missing var parameter", E_USER_WARNING);
        return;
    }
    // valueパラメータは必須です。
    if (empty($params['value'])) {
        trigger_error("assign_summery: missing value parameter", E_USER_WARNING);
        return;
    }
    // valueパラメータは必須です。
    if (empty($params['title'])) {
        trigger_error("assign_summery: missing title parameter", E_USER_WARNING);
        return;
    }
    // valueパラメータは必須です。
    if (empty($params['key'])) {
        trigger_error("assign_summery: missing key parameter", E_USER_WARNING);
        return;
    }

    $title = $params['title'];
    $key = $params['key'];
    $summery = array();
    foreach ($params['value'] as $data) {
        if (is_array($data)) {
            if (!isset($summery[$data[$title]][$key])) {
                $summery[$data[$title]][$title] = $data[$title];
                $summery[$data[$title]][$key] = 0;
            }
            $summery[$data[$title]][$key] += $data[$key];
        } else {
            if (!isset($summery[$data->$title][$key])) {
                $summery[$data->$title][$title] = $data->$title;
                $summery[$data->$title][$key] = 0;
            }
            $summery[$data->$title][$key] += $data->$key;
        }
    }
    $result = array();
    foreach ($summery as $data) {
        $result[] = $data;
    }
    $template->assign($params['var'], $result);
}
?>