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
 * アクセスした携帯端末情報を取得するためのクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Mobile
{

    protected $isMobile;

    protected $isFuturePhone;

    protected $isSmartPhone;

    protected $deviceType;

    protected $mobileId;

    protected $screenWidth;

    protected $screenHeight;

    /**
     * モバイルの端末情報取得クラスを作成する。
     */
    public static function create($info = null)
    {
        // 各端末のインスタンス作成処理を行う。
        $info = Vizualizer_Mobile_Docomo::create($info);
        $info = Vizualizer_Mobile_Ezweb::create($info);
        $info = Vizualizer_Mobile_Softbank::create($info);
        $info = Vizualizer_Mobile_Emobile::create($info);
        $info = Vizualizer_Mobile_Willcom::create($info);
        $info = Vizualizer_Mobile_Apple::create($info);
        $info = Vizualizer_Mobile_Android::create($info);
        $info = Vizualizer_Mobile_WindowsMobile::create($info);
        $info = Vizualizer_Mobile_BlackBerry::create($info);

        // いずれにも該当しない場合にはこのクラスのインスタンスをPC用として作成
        if ($info == null) {
            $info = new Vizualizer_Mobile();
        }
        return $info;
    }

    public function __construct()
    {
        $this->isMobile = false;
        $this->isFuturePhone = false;
        $this->isSmartPhone = false;
        $this->deviceType = "PC";
        $this->mobileId = "";
        // PCの場合は画面サイズを考慮しない
        $this->screenWidth = 0;
        $this->screenHeight = 0;
    }

    public function isMobile()
    {
        return $this->isMobile;
    }

    public function isFuturePhone()
    {
        return $this->isFuturePhone;
    }

    public function isSmartPhone()
    {
        return $this->isSmartPhone;
    }

    public function getDeviceType()
    {
        return $this->deviceType;
    }

    public function getMobileId()
    {
        return $this->mobileId;
    }

    public function getScreenWidth()
    {
        return $this->screenWidth;
    }

    public function getScreenHeight()
    {
        return $this->screenHeight;
    }
}
