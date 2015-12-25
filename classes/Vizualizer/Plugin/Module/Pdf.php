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
 * PDF出力用のモジュールです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_Pdf extends Vizualizer_Plugin_Module
{
    /**
     * PDFドキュメント
     */
    private $document;

    /**
     * 現在描画中のページ
     */
    private $page;

    /**
     * フォントオブジェクト
     */
    private $font;

    /**
     * PDF出力の処理を開始する。
     * 出力処理にはHaruを使用します。
     */
    protected function startDocument(){
        if(!class_exists("HaruDoc")){
            throw new Vizualizer_Exception_Invalid("Haru", "PDF出力するためにはHaruをインストールしてください。");
        }

        // PDFドキュメントを生成
        $this->document = new HaruDoc();

        // 日本語用のフォントとエンコーディングを読み込み
        $this->document->useJPFonts();
        $this->document->useJPEncodings();
        $this->font = $this->document->getFont('MS-PGothic','90msp-RKSJ-H');
    }

    protected function startPage(){
        $this->page = $this->document->addPage();
        $this->page->setSize(HaruPage::SIZE_A4,HaruPage::PORTRAIT);
    }

    /**
     * PDF出力の処理を実行する。
     * 出力処理にはHaruを使用します。
     * @param $type モデルクラスのタイプ
     * @param $name モデルクラスの名前
     * @param $value 取得する主キー
     */
    protected function getData($type, $name, $value){
        // データを取得する。
        $loader = new Vizualizer_Plugin($type);
        $model = $loader->loadModel($name);
        $model->findByPrimaryKey($value);
        return $model;
    }

    /**
     * PDF出力の処理を実行する。
     * 出力処理にはHaruを使用します。
     * @param $name モデルクラスの名前
     * @param $result 出力したファイルのパスを格納するキー
     */
    protected function output($name, $result)
    {
        // 結果のPDFを出力する。
        if(!empty($result)){
            // 出力するPDFファイル名を生成
            $saveDir = Vizualizer_Configure::get("upload_root") . "/pdf/" . sha1("site" . Vizualizer_Configure::get("site_id")) . "/".$name."/";
            if (!file_exists($saveDir)) {
                mkdir($saveDir, 0777, true);
            }
            // 保存するファイル名を構築
            $saveFile = sha1(uniqid()) . ".pdf";
            // 保存するファイルを移動
            $this->document->save($saveDir.$saveFile);
            // 登録した内容をPOSTに設定
            $attr = Vizualizer::attr();
            $attr[$result] = str_replace(VIZUALIZER_SITE_ROOT, VIZUALIZER_SUBDIR, $saveDir . $saveFile);
        }else{
            header("Content-Type: application/pdf");
            $this->document->output();
            exit;
        }
    }

    /**
     * PDFのY座標を左上座標基点から変換する。
     * @param $y 左上基点の座標
     * @return PDFの座標
     */
    private function pdfy($y){
        return 840 - $y;
    }

    protected function text($x, $y, $size, $text, $border = false){
        if($this->page){
            $this->page->beginText();
            $this->page->moveTextPos($x, $this->pdfy($y));
            $this->page->setFontAndSize($this->font, $size);
            $this->page->setTextLeading(ceil($size * 0.55));
            $lines = explode("\r\n", $text);
            foreach($lines as $index => $line){
                if($index > 0){
                    $this->page->showTextNewLine(mb_convert_encoding($line,"SJIS-win", "UTF-8"));
                }else{
                    $this->page->showText(mb_convert_encoding($line,"SJIS-win", "UTF-8"));
                }
            }
            $position = $this->page->getCurrentTextPos();
            $this->page->endText();
            if($border){
                $this->line($x - 2, $y + 2, $position["x"] + 2, $y + 2, floor($size / 20));
            }
        }
    }

    protected function boxtext($x, $y, $width, $height, $size, $text, $border = false, $align = "left"){
        if($this->page){
            $this->page->beginText();
            $text = str_replace("\r", "\n", str_replace("\r\n", "\n", $text));
            $this->page->setFontAndSize($this->font, $size);
            $this->page->setTextLeading(ceil($size * 0.55));
            switch($align){
                case "center":
                    $pAlign = HaruPage::TALIGN_CENTER;
                    break;
                case "right":
                    $pAlign = HaruPage::TALIGN_RIGHT;
                    break;
                default:
                    $pAlign = HaruPage::TALIGN_LEFT;
                    break;
            }
            $this->page->textRect($x, $this->pdfy($y), $x + $width, $this->pdfy($y + $height), mb_convert_encoding($text,"SJIS-win", "UTF-8"), $pAlign);
            $this->page->endText();
            if($border){
                $this->rect($x - 2, $y - 2, $width + 4, $height + 4, floor($size / 20));
            }
        }
    }

    protected function line($sx, $sy, $ex, $ey, $size){
        if($this->page){
            $this->page->setLineWidth($size);
            $this->page->setRGBStroke(0,0,0);
            $this->page->moveTo($sx, $this->pdfy($sy));
            $this->page->lineTo($ex, $this->pdfy($ey));
            $this->page->stroke();
        }
    }

    protected function rect($x, $y, $width, $height, $size){
        if($this->page){
            $this->page->setLineWidth($size);
            $this->page->setRGBStroke(0,0,0);
            $this->page->rectangle($x, $this->pdfy($y + $height), $width, $height);
            $this->page->stroke();
        }
    }

    protected function image($x, $y, $imageFilename, $width = 0, $height = 0){
        $filename = str_replace(VIZUALIZER_SUBDIR, VIZUALIZER_SITE_ROOT, $imageFilename);
        $size = getimagesize($filename);
        if($width > 0 && $size[0] > $width){
            $size[1] = floor($size[1] * $width / $size[0]);
            $size[0] = $width;
        }
        if($height > 0 && $size[1] > $height){
            $size[0] = floor($size[0] * $height / $size[1]);
            $size[1] = $height;
        }
        switch($size[2]){
            case IMAGETYPE_JPEG:
                $image = $this->document->loadJPEG($filename);
                break;
            case IMAGETYPE_PNG:
                $image = $this->document->loadPNG($filename);
                break;
            default:
                return;
        }
        $this->page->drawImage($image, $x, $this->pdfy($y + $size[1]), $size[0], $size[1]);
    }
}
