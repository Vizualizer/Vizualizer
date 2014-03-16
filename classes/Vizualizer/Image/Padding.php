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
 * 規定の矩形に収まるように縮小処理を行う。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Image_Padding extends Vizualizer_Image_Base
{

    var $background;

    function __construct($width, $height, $background = array(255, 255, 255))
    {
        $this->width = $width;
        $this->height = $height;
        $this->background = $background;
    }

    function filter($image, $info)
    {
        if ($this->width > 0 || $this->height > 0) {
            // 調整前の幅と高さを取得する
            $baseWidth = $this->width;
            $baseHeight = $this->height;

            // 変形後の幅と高さを計算する。
            $this->calculateSize($info);

            // 調整後の幅と高さを取得する
            $targetWidth = $this->width;
            $targetHeight = $this->height;

            // 調整前の幅と高さが0の時は調整後の値を使う
            if ($baseWidth == 0) {
                $baseWidth = $targetWidth;
            }
            if ($baseHeight == 0) {
                $baseHeight = $targetHeight;
            }

            // 画像は調整前の高さで作成するため、置き換える
            $this->width = $baseWidth;
            $this->height = $baseHeight;

            // 透過処理済みの新しい画像オブジェクトを生成
            $newImage = $this->transparent($image, $info);

            // 背景部分を指定色で塗りつぶし
            imagefill($newImage, 0, 0, imagecolorallocate($newImage, $this->background[0], $this->background[1], $this->background[2]));

            // 画像縮小処理
            imagecopyresampled($newImage, $image, floor(($baseWidth - $targetWidth) / 2), floor(($baseHeight - $targetHeight) / 2), 0, 0, $targetWidth, $targetHeight, $info[0], $info[1]);
            imagedestroy($image);
            $image = $newImage;
        } else {
            // 処理をしなかった場合は元の幅と高さを返す
            $this->width = $info[0];
            $this->height = $info[1];
        }
        return $image;
    }
}
