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
 * ページ表示用のテンプレートクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Template
{
    // コアとなるテンプレートエンジンのインスタンス
    protected $core;

    // テンプレートディレクトリのパス
    protected $template_dir;

    /**
     * 共通のアサイン処理
     */
    protected function initialAssign()
    {
        // 月のセレクタのアサイン処理
        $monthSelect = array();
        for ($i = 1; $i <= 12; $i ++) {
            $monthSelect[sprintf("%02d", $i)] = $i . "月";
        }
        $this->assign("SelectionMonth", $monthSelect);

        // 日のセレクタのアサイン処理
        $daySelect = array();
        for ($i = 1; $i <= 31; $i ++) {
            $daySelect[sprintf("%02d", $i)] = $i . "日";
        }
        $this->assign("SelectionDay", $daySelect);

        // 時間のセレクタのアサイン処理
        $hourSelect = array();
        for ($i = 0; $i <= 23; $i ++) {
            $hourSelect[sprintf("%02d", $i) . ":00"] = $i . ":00";
        }
        $this->assign("SelectionHour", $hourSelect);

        // 30分単位時間のセレクタのアサイン処理
        $halfHourSelect = array();
        for ($i = 0; $i <= 23; $i ++) {
            $halfHourSelect[sprintf("%02d", $i) . ":00"] = $i . ":00";
            $halfHourSelect[sprintf("%02d", $i) . ":30"] = $i . ":30";
        }
        $this->assign("HalfSelectionHour", $halfHourSelect);
    }

    /**
     * ページに変数を割り当てるメソッドです。
     */
    public abstract function assign($tpl_var, $value = null, $nocache = false, $scope = SMARTY_LOCAL_SCOPE);

    /**
     * ページテンプレートを適用し、結果をテキストデータとして取得するメソッドです。
     */
    public abstract function fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false);

    /**
     * ページ出力用のメソッドをオーバーライドしています。
     * 携帯のページについて、SJISに変換し、カナを半角にしています。
     *
     * @access public
     */
    public function display($template, $cache_id = null, $compile_id = null, $parent = null)
    {
        // キャッシュ無効にするヘッダを送信
        header("P3P: CP='UNI CUR OUR'");
        header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        if (array_key_exists("HTTPS", $_SERVER) && $_SERVER['HTTPS'] == 'on') {
            header("Cache-Control: must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
        } else {
            header("Cache-Control: no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }

        $attr = Vizualizer::attr();
        $templateEngine = $attr["template"];
        $templateEngine->assign("configure", Vizualizer_Configure::values());
        $templateEngine->assign("post", Vizualizer::request());
        $templateEngine->assign("attr", $attr);
        $templateEngine->assign("sessionName", session_name());
        $templateEngine->assign("sessionId", session_id());

        // display template
        Vizualizer_Logger::writeDebug("Template Dir : " . var_export($this->template_dir, true));
        Vizualizer_Logger::writeDebug("Template Name : " . $template);
        if (Vizualizer_Configure::get("device")->isFuturePhone()) {
            // モバイルの時は出力するHTMLをデータとして取得
            $content = trim($this->core->fetch($template, $cache_id, $compile_id, $parent));
            // カタカナを半角にする。
            $content = mb_convert_kana($content, "k");

            // ソフトバンク以外の場合は、SJISエンコーディングに変換
            if (Vizualizer_Configure::get("device")->getDeviceType() != "Softbank") {
                header("Content-Type: text/html; charset=Shift_JIS");
                if (preg_match("/<meta\\s+http-equiv\\s*=\\s*\"Content-Type\"\\s+content\\s*=\\s*\"([^;]+);\\s*charset=utf-8\"\\s*\\/?>/i", $content, $params) > 0) {
                    header("Content-Type: " . $params[1] . "; charset=Shift_JIS");
                    $content = str_replace($params[0], "<meta http-equiv=\"Content-Type\" content=\"" . $params[1] . "; charset=Shift_JIS\" />", $content);
                } else {
                    header("Content-Type: text/html; charset=Shift_JIS");
                }
                echo mb_convert_encoding($content, "Shift_JIS", "UTF-8");
            } else {
                header("Content-Type: text/html; charset=UTF-8");
                echo $content;
            }
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            echo trim($this->fetch($template, $cache_id, $compile_id, $parent));
        }
    }
}
