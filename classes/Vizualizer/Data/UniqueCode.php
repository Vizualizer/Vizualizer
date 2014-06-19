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
 * ユニークコード生成用のクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Data_UniqueCode
{
    /**
     * MD5の値を数値化する際に使用するテーブル
     */
    private static $srcTable = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");

    /**
     * 数値を結果のテキストに変換する際に使用するテーブル
     */
    private static $destTable = array(
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m",
        "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
        "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
        "_", "-"
    );

    /**
     * ユニークコードを取得する。
     *
     * @param string $prefix ユニークコード用の識別子
     * @return string ユニークコード
     */
    public static function get($prefix = "")
    {
        // ユニークなコードを算出
        $uid = md5(uniqid(rand()));
        // ベースが３文字の倍数になるように先頭に０を追加
        while(strlen($uid) % 3 > 0){
            $uid = "0" . $uid;
        }
        // テキストを変換
        $value = 0;
        $code = $prefix;
        for($i = 0; $i < strlen($uid); $i ++){
            if($i % 3 == 0 && $i > 0){
                $code .= self::$destTable[floor($value / count(self::$destTable))].self::$destTable[$value % count(self::$destTable)];
                $value = 0;
            }
            $value = $value * 16 + array_search(substr($uid, $i, 1), self::$srcTable);
        }
        return $code;
    }
}
