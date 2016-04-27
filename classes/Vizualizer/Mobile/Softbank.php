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

/**
 * Softbankの携帯端末情報を取得するためのクラスです。
 *
 * @package Mobile
 * @author Naohisa Minagawa <info@clay-system.jp>
 */
class Vizualizer_Mobile_Softbank extends Vizualizer_Mobile
{

    /**
     * モバイルの端末情報取得クラスを作成する。
     */
    public static function create($info = null)
    {
        if ($info == null) {
            if (stripos($_SERVER["HTTP_USER_AGENT"], "J-PHONE") === 0 || stripos($_SERVER["HTTP_USER_AGENT"], "Vodafone") === 0 || stripos($_SERVER["HTTP_USER_AGENT"], "SoftBank") === 0 || stripos($_SERVER["HTTP_USER_AGENT"], "MOT-") === 0) {
                return new Vizualizer_Mobile_Softbank();
            }
        }
        return $info;
    }

    public function __construct()
    {
        $this->isMobile = true;
        $this->isFuturePhone = true;
        $this->isSmartPhone = false;
        $this->deviceType = "Softbank";
        $this->mobileId = $_SERVER["HTTP_X_JPHONE_UID"];
        list ($this->screenWidth, $this->screenHeight) = explode("*", $_SERVER["HTTP_X-JPHONE-DISPLAY"]);
    }
}
