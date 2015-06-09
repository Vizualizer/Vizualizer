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
 * 日本の都道府県用のプルダウンを作成するモジュール。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Module_Locale_Japanese_Prefecture extends Vizualizer_Plugin_Module
{

    function execute($params)
    {
        if($params->check("key")){
            $attr = Vizualizer::attr();
            $prefBase = array(
                "北海道", "青森県", "岩手県", "宮城県", "秋田県", "山形県", "福島県", "茨城県", "栃木県", "群馬県", "埼玉県",
                "千葉県", "東京都", "神奈川県", "新潟県", "富山県", "石川県", "福井県", "山梨県", "長野県", "岐阜県", "静岡県",
                "愛知県", "三重県", "滋賀県", "京都府", "大阪府", "兵庫県", "奈良県", "和歌山県", "鳥取県", "島根県", "岡山県",
                "広島県", "山口県", "徳島県", "香川県", "愛媛県", "高知県", "福岡県", "佐賀県", "長崎県", "熊本県", "大分県",
                "宮崎県", "鹿児島県", "沖縄県"
            );
            $prefs = array();
            // 空の項目を追加する場合に設定
            if($params->check("empty")){
                $prefs[""] = $params->get("empty");
            }
            // 基本の47都道府県を追加
            foreach($prefBase as $base){
                $prefs[$base] = $base;
            }
            // 追加で項目を入れる場合
            if($params->check("append")){
                $appends = explode(",", $params->get("append"));
                foreach($appends as $append){
                    $prefs[trim($append)] = trim($append);
                }
            }
            $attr[$params->get("key")] = $prefs;
        }
    }
}
