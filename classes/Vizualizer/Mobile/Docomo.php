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
 * DoCoMoの携帯端末情報を取得するためのクラスです。
 *
 * @package Mobile
 * @author Naohisa Minagawa <info@clay-system.jp>
 */
class Vizualizer_Mobile_Docomo extends Vizualizer_Mobile
{

    /**
     * モバイルの端末情報取得クラスを作成する。
     */
    public static function create($info = null)
    {
        if ($info == null) {
            if (stripos($_SERVER["HTTP_USER_AGENT"], "DoCoMo") === 0) {
                return new Vizualizer_Mobile_Docomo();
            }
        }
        return $info;
    }

    public function __construct()
    {
        $this->isMobile = true;
        $this->isFuturePhone = true;
        $this->isSmartPhone = false;
        $this->deviceType = "DoCoMo";
        if (isset($_SERVER["HTTP_X_DCMGUID"])) {
            $this->mobileId = $_SERVER["HTTP_X_DCMGUID"];
        } else {
            if ($_SERVER["REQUEST_METHOD"] == "GET" && strpos($_SERVER["QUERY_STRING"], "guid=on") !== false) {
                if (strpos($_SERVER["REQUEST_URI"], "?") !== FALSE) {
                    header("Location: " . $_SERVER["REQUEST_URI"] . "&guid=on");
                } else {
                    header("Location: " . $_SERVER["REQUEST_URI"] . "?guid=on");
                }
                exit();
            }
        }
        $this->screenWidth = 0;
        $this->screenHeight = 0;
    }
}
