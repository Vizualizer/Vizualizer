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
class Vizualizer_Image_Resize extends Vizualizer_Image_Base
{

    function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    function calculateSize($info)
    {
        // 一方の辺指定が0の場合、比率を維持した時のサイズを設定する。
        if ($this->width == 0) {
            $this->width = floor($info[0] * $this->height / $info[1]);
        }
        if ($this->height == 0) {
            $this->height = floor($info[1] * $this->width / $info[0]);
        }

        // 幅が規定値より大きい場合は調整する。
        if ($this->width < $info[0] && floor($this->width * $info[1] / $info[0]) < $this->height) {
            $this->height = floor($this->width * $info[1] / $info[0]);
        }

        // 高さが規定値より大きい場合は調整する。
        if ($this->height < $info[1] && floor($this->height * $info[0] / $info[1]) < $this->width) {
            $this->width = floor($this->height * $info[0] / $info[1]);
        }
        if ($info[0] < $this->width && $info[1] < $this->height) {
            $this->width = $info[0];
            $this->height = $info[1];
        }
    }

    function filter($image, $info)
    {
        if ($this->width > 0 || $this->height > 0) {
            // 変形後の幅と高さを計算する。
            $this->calculateSize($info);

            // 透過処理済みの新しい画像オブジェクトを生成
            $newImage = $this->transparent($image, $info);

            // 画像縮小処理
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $this->width, $this->height, $info[0], $info[1]);
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
