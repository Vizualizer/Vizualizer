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
 * 内部カレンダー制御用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Data_Calendar
{

    /**
     * システム基準時間のカレンダーオブジェクト
     */
    private static $calendar;

    /**
     * カレンダーの持つ時間
     */
    private $time;

    /**
     * 現在時刻のカレンダーを取得する。
     */
    public static function now()
    {
        return new Vizualizer_Data_Calendar();
    }

    /**
     * システム実行の基準時間用のカレンダーを取得する。
     */
    public static function get()
    {
        if (self::$calendar == null) {
            self::$calendar = self::now();
        }
        return self::$calendar;
    }

    /**
     * コンストラクタ
     * @param int $time 初期設定する時間（デフォルトは現在時刻）
     */
    private function __construct($time = 0)
    {
        if (empty($time)) {
            $this->time = time();
        } else {
            $this->time = $time;
        }
    }

    /**
     * カレンダーにstrtotimeを実行した結果の新しいカレンダーを取得する。
     * @param string $str strtotimeのパラメータ
     * @return Vizualizer_Data_Calendar 実行後の新しいカレンダー
     */
    public function strToTime($str)
    {
        return new Vizualizer_Data_Calendar(strtotime($str, $this->time));
    }

    /**
     * カレンダーの時刻にdateを実行した文字列を取得する。
     * @param string $format フォーマット
     * @return string 書式化した時刻
     */
    public function date($format)
    {
        return date($format, $this->time);
    }

    /**
     * カレンダーを文字列として扱う場合はY-m-d H:i:s形式として扱う。
     */
    public function __toString(){
        return $this->date("Y-m-d H:i:s");
    }
}
