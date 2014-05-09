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
 * Excelの一覧ダウンロード用のモジュールクラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Module_DownloadExcel extends Vizualizer_Plugin_Module
{

    /**
     */
    protected $errors = array();

    /**
     * アップロードされたExcelファイルを処理する。
     */
    protected abstract function process($params, $book);

    protected function setFont($name, $size)
    {
        $sheet->getDefaultStyle()
            ->getFont()
            ->setName($name);
        $sheet->getDefaultStyle()
            ->getFont()
            ->setSize($size);
    }

    protected function setValue($sheet, $cell, $value, $border = false)
    {
        if ($border) {
            $sheet->getStyle($cell)
                ->getBorders()
                ->getTop()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle($cell)
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle($cell)
                ->getBorders()
                ->getRight()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle($cell)
                ->getBorders()
                ->getBottom()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        $sheet->setCellValue($cell, $value);
    }

    protected function executeImpl($params, $type)
    {
        if (!$params->check("download") || isset($_POST[$params->get("download")])) {
            $loader = new Vizualizer_Plugin($type);

            if ($params->check("template")) {
                // テンプレートファイルを読み込み
                $book = PHPExcel_IOFactory::load($params->get("template"));
            } else {
                // Excelファイルを新規作成
                $book = new PHPExcel();
            }

            // データを処理する。
            $book = $this->process($params, $book);

            // ダウンロードを実行する
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename="' . $params->get("file", "download") . '.xlsx"');
            ob_end_clean();
            $writer = PHPExcel_IOFactory::createWriter($book, 'Excel2007');
            $writer->save('php://output');
            exit();
        }
    }
}
