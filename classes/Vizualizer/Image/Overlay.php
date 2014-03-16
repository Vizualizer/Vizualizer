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
 * 画像に対して重ねあわせの処理を行う。
 * 位置を指定しない場合には中央になるように配置する。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Image_Overlay extends Vizualizer_Image_Base
{

    var $resize;

    var $info;

    var $image;

    function __construct($file, $resize = false)
    {
        $this->resize = $resize;
        $this->info = getimagesize($file);
        switch ($this->info[2]) {
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $this->image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($file);
                break;
            default:
                break;
        }
    }

    function filter($image, $info)
    {
        // 拡大縮小はしないため、幅と高さは元画像のものを使用する。
        $this->width = $info[0];
        $this->height = $info[1];

        $newHeight = $this->info[1];
        $newWidth = $this->info[0];

        if ($this->resize) {
            // 幅が規定値より大きい場合は調整する。
            if ($info[0] < $this->info[0]) {
                $newHeight = floor($info[0] * $this->info[1] / $this->info[0]);
                $newWidth = $info[0];
            }

            // 高さが規定値より大きい場合は調整する。
            if ($info[1] < $newHeight) {
                $newWidth = floor($info[1] * $newWidth / $newHeight);
                $newHeight = $info[1];
            }
        }

        // 画像重ね合わせ処理
        imagecopyresampled($image, $this->image, floor(($info[0] - $newWidth) / 2), floor(($info[1] - $newHeight) / 2), 0, 0, $newWidth, $newHeight, $this->info[0], $this->info[1]);

        return $image;
    }
}
