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
 * プラグインとして利用する基本クラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Plugin_Object
{
    /**
     * デバッグログを出力する。
     * @param string $message ログメッセージ
     * @param int $level 出力するデバッグレベル（最大99）
     */
    protected function debug($message, $level = 1)
    {
        Vizualizer_Logger::writeDebug($message, $level);
    }

    /**
     * 情報ログを出力する。
     * @param string $message ログメッセージ
     */
    protected function info($message)
    {
        Vizualizer_Logger::writeInfo($message);
    }

    /**
     * 警告ログを出力する。
     * @param string $message ログメッセージ
     */
    protected function alert($message)
    {
        Vizualizer_Logger::writeAlert($message);
    }

    /**
     * エラーログを出力する。
     * @param string $message ログメッセージ
     * @param Exception $exception エラーの原因となった例外オブジェクト
     */
    protected function error($message, $exception = null)
    {
        Vizualizer_Logger::writeError($message, $exception);
    }

    /**
     * 値が空であることを調べる。
     * empty関数が数字の0に対してtrueを返すため、数字の0を空として扱わないようにするために利用する。
     *
     * @param mixed $value 対象の変数
     * @return boolean データが空の場合はtrue、そうでない場合はfalse
     */
    protected function isEmpty($value)
    {
        return (!isset($value) || $value === FALSE || $value === null || $value === "");
    }

    /**
     * パスワードを暗号化するためのメソッドです。
     * 暗号化処理の整合性を保つため、暗号化する場合は必ずこのメソッドを利用して行うこと。
     *
     * @param string $login_id ログインID
     * @param string $plain_password パスワード
     */
    protected function encryptPassword($login_id, $plain_password)
    {
        return sha1($login_id . ":" . $plain_password);
    }

    /**
     * 全角スペースを含んだ形でtrimするためのメソッドです。
     *
     * @param string $str 対象文字列
     * @return string trimした文字列
     */
    protected function trim($str){
        // 先頭の半角、全角スペースを、空文字に置き換える
        $str = preg_replace('/^[　]+/u', '', $str);
        // 最後の半角、全角スペースを、空文字に置き換える
        $str = preg_replace('/[　]+$/u', '', $str);
        return trim($str);
    }

    /**
     * テンプレートで使うことのできるデータをデバッグログに出力
     */
    protected function logTemplateData() {
        $attr = Vizualizer::attr();
        $template = $attr["template"];
        $this->debug("======= CONFIGURE =======", 99);
        $this->debug(print_r($template->getVars("configure"), true), 99);
        $this->debug("======= PARAMETER =======", 99);
        $this->debug(print_r($template->getVars("post")->values(), true), 99);
        $this->debug("======= SESSIONS =======", 99);
        $attrs = $template->getVars("session");
        foreach ($attrs as $key => $attr) {
            if ($attr instanceof Vizualizer_Template) {

            } elseif ($attr instanceof Vizualizer_Plugin_Model) {
                $this->debug($key . " = " . print_r($attr->toArray(), true), 99);
            } else {
                $this->debug($key . " = " . print_r($attr, true), 99);
            }
        }
        $this->debug("======= ATTRIBUTES =======", 99);
        $attrs = $template->getVars("attr")->values();
        foreach ($attrs as $key => $attr) {
            if ($attr instanceof Vizualizer_Template) {

            } elseif ($attr instanceof Vizualizer_Plugin_Model) {
                $this->debug($key . " = " . print_r($attr->toArray(), true), 99);
            } else {
                $this->debug($key . " = " . print_r($attr, true), 99);
            }
        }
    }
}
