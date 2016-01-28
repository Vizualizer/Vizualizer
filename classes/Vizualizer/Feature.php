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
 * 機能モジュールの実装クラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Feature
{
    protected $info;

    protected $type;

    public function __construct($info, $type) {
        $this->info = $info;
        $this->type = $type;
    }

    public function execute($mode, $params)
    {
        if (method_exists($this, $mode)) {
            $this->$mode($params);
            return true;
        }
        return false;
    }

    protected function check($params)
    {
        $post = Vizualizer::request();
        $errors = array();
        if (array_key_exists("require", $params) && is_array($params["require"])) {
            foreach($params["require"] as $key => $value) {
                if (empty($post[$key])) {
                    if (!array_key_exists("require", $errors) || !is_array($errors["require"])) {
                        $errors["require"] = array();
                    }
                    $errors["require"][$key] = $value;
                }
            }
        }
        if (array_key_exists("digit", $params) && is_array($params["digit"])) {
            foreach($params["digit"] as $key => $value) {
                if (!empty($post[$key]) && is_numeric($post[$key])) {
                    if (!array_key_exists("digit", $errors) || !is_array($errors["digit"])) {
                        $errors["digit"] = array();
                    }
                    $errors["digit"][$key] = $value;
                }
            }
        }
        if (array_key_exists("alpha", $params) && is_array($params["alpha"])) {
            foreach($params["alpha"] as $key => $value) {
                if (!empty($post[$key]) && ctype_alnum($post[$key])) {
                    if (!array_key_exists("alpha", $errors) || !is_array($errors["alpha"])) {
                        $errors["alpha"] = array();
                    }
                    $errors["alpha"][$key] = $value;
                }
            }
        }
        if (array_key_exists("hiragana", $params) && is_array($params["hiragana"])) {
            foreach($params["hiragana"] as $key => $value) {
                if (!empty($post[$key]) && preg_match("/^[ぁ-ん 　]+$/u", $post[$key]) == 0) {
                    if (!array_key_exists("hiragana", $errors) || !is_array($errors["hiragana"])) {
                        $errors["hiragana"] = array();
                    }
                    $errors["hiragana"][$key] = $value;
                }
            }
        }
        if (array_key_exists("katakana", $params) && is_array($params["katakana"])) {
            foreach($params["katakana"] as $key => $value) {
                if (!empty($post[$key]) && preg_match("/^[ァ-ヶー 　]+$/u", $post[$key]) == 0) {
                    if (!array_key_exists("katakana", $errors) || !is_array($errors["katakana"])) {
                        $errors["katakana"] = array();
                    }
                    $errors["katakana"][$key] = $value;
                }
            }
        }
        if (array_key_exists("email", $params) && is_array($params["email"])) {
            foreach($params["email"] as $key => $value) {
                if (!empty($post[$key]) && preg_match("/^[a-zA-Z0-9!$&*.=^`|~#%'+\\/?_{}-]+@([a-zA-Z0-9_-]+\\.)+[a-zA-Z]{2,}$/", $post[$key]) == 0) {
                    if (!array_key_exists("email", $errors) || !is_array($errors["email"])) {
                        $errors["email"] = array();
                    }
                    $errors["email"][$key] = $value;
                }
            }
        }
        if (array_key_exists("date", $params) && is_array($params["date"])) {
            foreach($params["date"] as $key => $value) {
                if (!empty($post[$key])){
                    if (($time = $this->getDateString($post[$key])) === FALSE) {
                        if (!array_key_exists("date", $errors) || !is_array($errors["date"])) {
                            $errors["date"] = array();
                        }
                        $errors["date"][$key] = $value;
                    }
                }
            }
        }
        if (array_key_exists("past", $params) && is_array($params["past"])) {
            foreach($params["past"] as $key => $value) {
                if (!empty($post[$key])){
                    if (($time = $this->getDateString($post[$key])) === FALSE || time() <= $time) {
                        if (!array_key_exists("past", $errors) || !is_array($errors["past"])) {
                            $errors["past"] = array();
                        }
                        $errors["past"][$key] = $value;
                    }
                }
            }
        }
        if (array_key_exists("future", $params) && is_array($params["future"])) {
            foreach($params["future"] as $key => $value) {
                if (!empty($post[$key])){
                    if (($time = $this->getDateString($post[$key])) === FALSE || $time <= time()) {
                        if (!array_key_exists("future", $errors) || !is_array($errors["future"])) {
                            $errors["future"] = array();
                        }
                        $errors["future"][$key] = $value;
                    }
                }
            }
        }
        return $errors;
    }

    protected function getDateString($date)
    {
        if (is_array($date)) {
            return strtotime($date[0]."-".$date[1]."-".$date[2]);
        } else {
            return strtotime($date);
        }
    }


}
