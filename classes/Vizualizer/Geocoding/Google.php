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
 * GoogleMapの機能を利用したGeocodingの処理を制御するクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Geocoding_Google
{

    /**
     * 言語
     */
    private $language;

    /**
     * データキャッシュ
     *
     * @var Vizualizer_Cache_Base
     */
    private $cache;

    /**
     * コンストラクタ
     *
     * @param string $language
     */
    public function __construct($language = "en")
    {
        // 言語設定を保存
        $this->language = $language;
        // キャッシュを読み込み
        $this->cache = Vizualizer_Cache_Factory::create("google.geocoding");
    }

    public function getDistance($origin, $destination)
    {
        $key = $this->language . ":" . $origin . "-" . $destination;
        $locations = $this->cache->get($key);

        if (empty($location)) {
            // ベースURLを取得
            $baseUrl = "https://maps.googleapis.com/maps/api/distancematrix/json?alternatives=true&sensor=false&region=jp&mode=transit&departure_time=".time()."&";

            // 追加ヘッダ情報を設定
            $options = array("http" => array("header" => "Accept-Language: " . $this->language));
            $context = stream_context_create($options);

            // コンテンツを取得
            if (preg_match("/^([^0-9]+)([0-9]+)?([^0-9]*[0-9]+)?([^0-9]*[0-9]+)?/", mb_convert_kana($origin, "n"), $p) > 0) {
                $origin = $p[0];
            }
            if (preg_match("/^([^0-9]+)([0-9]+)?([^0-9]*[0-9]+)?([^0-9]*[0-9]+)?/", mb_convert_kana($destination, "n"), $p) > 0) {
                $destination = $p[0];
            }
            echo "URL : " . $baseUrl . "origins=" . urlencode($origin) . "&destinations=" . urlencode($destination) . "<br>\r\n";
            $location = file_get_contents($baseUrl . "origins=" . urlencode($origin) . "&destinations=" . urlencode($destination), false, $context);
            $this->cache->set($key, $location);
        }
        echo $location;
        $data = json_decode($location);
        if ($data->status == "OK") {
            return $data->results;
        } else {
            return array();
        }
    }

    public function getAddressData($address)
    {
        $key = $this->language . ":" . $address;
        $location = $this->cache->get($key);

        if (empty($location)) {
            // ベースURLを取得
            $baseUrl = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=";

            // 追加ヘッダ情報を設定
            $options = array("http" => array("header" => "Accept-Language: " . $this->language));
            $context = stream_context_create($options);

            // コンテンツを取得
            if (preg_match("/^([^0-9]+)([0-9]+)?([^0-9]*[0-9]+)?([^0-9]*[0-9]+)?/", mb_convert_kana($address, "n"), $p) > 0) {
                $address = $p[0];
            }
            $location = file_get_contents($baseUrl . urlencode($address), false, $context);
            $this->cache->set($key, $location);
        }
        $data = json_decode($location);
        if ($data->status == "OK") {
            return $data->results;
        } else {
            return array();
        }
    }

    public function getAddress($address, $index)
    {
        $data = $this->getAddressData($address);
        if ($index < count($data)) {
            return $data[$index];
        }
        return "";
    }

    public function getFormattedAddresses()
    {
        $data = $this->getAddressData($address);
        $results = array();
        foreach ($data as $item) {
            $results[] = $item->formatted_address;
        }
        return $results;
    }

    public function getLatLng($address){
        $data = $this->getAddressData($address);
        if(count($data) > 0){
            $location = $data[0]->geometry->location;
        }
        return $location->lat.",".$location->lng;
    }
}
