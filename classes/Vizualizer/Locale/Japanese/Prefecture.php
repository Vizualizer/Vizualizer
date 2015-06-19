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
 * 日本語エリアの都道府県を制御するためのクラス。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Locale_Japanese_Prefecture
{
    private static $prefectures = array(
        "北海道" => array("alphabet" => "Hokkaido", "kana" => "ホッカイドウ"),
        "青森県" => array("alphabet" => "Aomori", "kana" => "アオモリケン"),
        "岩手県" => array("alphabet" => "Iwate", "kana" => "イワテケン"),
        "宮城県" => array("alphabet" => "Miyagi", "kana" => "ミヤギケン"),
        "秋田県" => array("alphabet" => "Akita", "kana" => "アキタケン"),
        "山形県" => array("alphabet" => "Yamagata", "kana" => "ヤマガタケン"),
        "福島県" => array("alphabet" => "Fukushima", "kana" => "フクシマケン"),
        "茨城県" => array("alphabet" => "Ibaraki", "kana" => "イバラキケン"),
        "栃木県" => array("alphabet" => "Tochigi", "kana" => "トチギケン"),
        "群馬県" => array("alphabet" => "Gunma", "kana" => "グンマケン"),
        "埼玉県" => array("alphabet" => "Saitama", "kana" => "サイタマケン"),
        "千葉県" => array("alphabet" => "Chiba", "kana" => "チバケン"),
        "東京都" => array("alphabet" => "Tokyo", "kana" => "トウキョウト"),
        "神奈川県" => array("alphabet" => "Kanagawa", "kana" => "カナガワケン"),
        "新潟県" => array("alphabet" => "Nigata", "kana" => "ニイガタケン"),
        "富山県" => array("alphabet" => "Toyama", "kana" => "トヤマケン"),
        "石川県" => array("alphabet" => "Ishikawa", "kana" => "イシカワケン"),
        "福井県" => array("alphabet" => "Fukui", "kana" => "フクイケン"),
        "山梨県" => array("alphabet" => "Yamanashi", "kana" => "ヤマナシケン"),
        "長野県" => array("alphabet" => "Nagano", "kana" => "ナガノケン"),
        "岐阜県" => array("alphabet" => "Gifu", "kana" => "ギフケン"),
        "静岡県" => array("alphabet" => "Shizuoka", "kana" => "シズオカケン"),
        "愛知県" => array("alphabet" => "Aichi", "kana" => "アイチケン"),
        "三重県" => array("alphabet" => "Mie", "kana" => "ミエケン"),
        "滋賀県" => array("alphabet" => "Shiga", "kana" => "シガケン"),
        "京都府" => array("alphabet" => "Kyoto", "kana" => "キョウトフ"),
        "大阪府" => array("alphabet" => "Osaka", "kana" => "オオサカフ"),
        "兵庫県" => array("alphabet" => "Hyogo", "kana" => "ヒョウゴケン"),
        "奈良県" => array("alphabet" => "Nara", "kana" => "ナラケン"),
        "和歌山県" => array("alphabet" => "Wakayama", "kana" => "ワカヤマケン"),
        "鳥取県" => array("alphabet" => "Tottori", "kana" => "トットリケン"),
        "島根県" => array("alphabet" => "Shimane", "kana" => "シマネケン"),
        "岡山県" => array("alphabet" => "Okayama", "kana" => "オカヤマケン"),
        "広島県" => array("alphabet" => "Hiroshima", "kana" => "ヒロシマケン"),
        "山口県" => array("alphabet" => "Yamaguchi", "kana" => "ヤマグチケン"),
        "徳島県" => array("alphabet" => "Tokushima", "kana" => "トクシマケン"),
        "香川県" => array("alphabet" => "Kagawa", "kana" => "カガワケン"),
        "愛媛県" => array("alphabet" => "Ehime", "kana" => "エヒメケン"),
        "高知県" => array("alphabet" => "Kouchi", "kana" => "コウチケン"),
        "福岡県" => array("alphabet" => "Fukuoka", "kana" => "フクオカケン"),
        "佐賀県" => array("alphabet" => "Saga", "kana" => "サガケン"),
        "長崎県" => array("alphabet" => "Nagasaki", "kana" => "ナガサキケン"),
        "熊本県" => array("alphabet" => "Kumamoto", "kana" => "クマモトケン"),
        "大分県" => array("alphabet" => "Oita", "kana" => "オオイタケン"),
        "宮崎県" => array("alphabet" => "Miyazaki", "kana" => "ミヤザキケン"),
        "鹿児島県" => array("alphabet" => "Kagoshima", "kana" => "カゴシマケン"),
        "沖縄県" => array("alphabet" => "Okinawa", "kana" => "オキナワケン")
    );

    /**
     * 選択用に使用する都道府県のリストを取得する。
     */
    public static function getSelection(){
        return array_keys(self::$prefectures);
    }

    /**
     * 都道府県の名前からアルファベット名を取得する
     */
    public static function toAlphabet($name){
        return self::$prefectures[$name]["alphabet"];
    }

    /**
     * 都道府県の名前からカナ名を取得する
     */
    public static function toKana($name){
        return self::$prefectures[$name]["kana"];
    }
}
