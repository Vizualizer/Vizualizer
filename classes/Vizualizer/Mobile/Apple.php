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
 * iPhone/iPadの携帯端末情報を取得するためのクラスです。
 *
 * @package Mobile
 * @author Naohisa Minagawa <info@clay-system.jp>
 */
class Vizualizer_Mobile_Apple extends Vizualizer_Mobile
{

    /**
     * モバイルの端末情報取得クラスを作成する。
     */
    public static function create($info = null)
    {
        if ($info == null) {
            if (strpos($_SERVER["HTTP_USER_AGENT"], "iPhone") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "iPod") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "iPad") !== false) {
                return new Vizualizer_Mobile_Apple();
            }
        }
        return $info;
    }

    public function __construct()
    {
        $this->isMobile = true;
        $this->isFuturePhone = false;
        $this->isSmartPhone = true;
        $this->deviceType = "iPhone";
        // iPhone/iPad自体はIDを持たないが、アプリなどで取得する場合はこのIDに設定する
        if (isset($_SERVER["HTTP_X_DCMGUID"])) {
            $this->mobileId = $_SERVER["HTTP_X_DCMGUID"];
        } else {
            $this->mobileId = "";
        }
        $this->screenWidth = 0;
        $this->screenHeight = 0;
    }
}
