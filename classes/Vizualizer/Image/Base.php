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
 * 画像フィルタリングの基底クラス。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Image_Base
{

    private $width;

    private $height;

    /**
     * 処理後の画像の幅を取得する。
     */
    function getWidth()
    {
        return $this->width;
    }

    /**
     * 処理後の画像の高さを取得する。
     */
    function getHeight()
    {
        return $this->height;
    }

    /**
     * 透過処理済みの新しいイメージオブジェクトを生成する。
     */
    function transparent($image, $info, $newImage = null)
    {
        if ($newImage == null) {
            $newImage = imagecreatetruecolor($this->width, $this->height);
        }
        if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
            // 元画像の透過色を取得する。
            $trnprt_indx = imagecolortransparent($image);

            // パレットサイズを取得する。
            $palletsize = imagecolorstotal($image);

            // 透過色が設定されている場合は透過処理を行う。
            if ($trnprt_indx >= 0 && $trnprt_indx < $palletsize) {
                // カラーインデックスから透過色を取得する。
                $trnprt_color = imagecolorsforindex($image, $trnprt_indx);

                // 取得した透過色から変換後の画像用のカラーインデックスを生成
                $trnprt_indx = imagecolorallocate($newImage, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                // 生成した透過色で変換後画像を塗りつぶし
                imagefill($newImage, 0, 0, $trnprt_indx);

                // 生成した透過色を変換後画像の透過色として設定
                imagecolortransparent($newImage, $trnprt_indx);
            } elseif ($info[2] == IMAGETYPE_PNG) {
                // アルファブレンディングをOFFにする。
                imagealphablending($newImage, false);

                // アルファブレンドのカラーを作成する。
                $trnprt_indx = imagecolorallocatealpha($newImage, 0, 0, 0, 127);

                // 生成した透過色で変換後画像を塗りつぶし
                imagefill($newImage, 0, 0, $trnprt_indx);

                // 透過色をGIF用に設定
                imagecolortransparent($newImage, $trnprt_indx);

                // 生成した透過色を変換後画像の透過色として設定
                imagesavealpha($newImage, true);
            }
        }
        return $newImage;
    }

    abstract function filter($image, $info);
}
