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
 * ページャーをテンプレート文字列で作成するためのクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Pager
{
    // ページIDキー
    const PAGE_ID_KEY = "pageID";
    
    // デフォルトのページサイズ
    const DEFAULT_PAGE_SIZE = 10;
    
    // ページモード(全表示形式)
    const PAGE_ALL = 0;
    
    // ページモード(ジャンプ形式)
    const PAGE_JUMP = 1;
    
    // ページモード（スライド形式）
    const PAGE_SLIDE = 2;
    
    // 表示モード（前へなどのボタン無効化時に属性付加）
    const DISPLAY_ATTR = 0;
    
    // 表示モード（前へなどのボタン無効化時に非表示
    const DISPLAY_HIDE = 1;
    
    // ページモード
    protected $pageMode;
    
    // 表示モード
    protected $displayMode;
    
    // ページの表示数
    protected $displayPages;
    
    // ページャー全体のテンプレート文字列
    // {1}：最初のページ、{2}：前のページ、{3}：ページリスト、{4}：次のページ、{5}：最後のページ
    protected $pagerText = "<div class=\"pagination\"><ul>{1}{2}{3}{4}{5}</ul></div>";
    
    // ページ番号リンクのセパレータ
    protected $separator = "";
    
    // 最初のページ用のテンプレート
    // {1}：ページリンク
    // {2}：表示モードが属性の場合にdisabledが入る
    protected $firstPageText = "<li class=\"{2}\"><a href=\"{1}\">&lt;&lt;</a></li>";
    
    // 最後のページ用のテンプレート
    // {1}：ページリンク
    // {2}：表示モードが属性の場合にdisabledが入る
    protected $lastPageText = "<li class=\"{2}\"><a href=\"{1}\">&gt;&gt;</a></li>";
    
    // 前のページ用のテンプレート
    // {1}：ページリンク
    // {2}：表示モードが属性の場合にdisabledが入る
    protected $prevPageText = "<li class=\"{2}\"><a href=\"{1}\">Prev</a></li>";
    
    // 次のページ用のテンプレート
    // {1}：ページリンク
    // {2}：表示モードが属性の場合にdisabledが入る
    protected $nextPageText = "<li class=\"{2}\"><a href=\"{1}\">Next</a></li>";
    
    // 現在ページ番号用のテンプレート
    // {1}：ページリンク、{2}：ページ番号
    protected $currentPageText = "<li class=\"active\"><a href=\"{1}\">{2}</a></li>";
    
    // ページ番号用のテンプレート
    // {1}：ページリンク、{2}：ページ番号
    protected $pageText = "<li><a href=\"{1}\">{2}</a></li>";
    
    // ページ表示用のクエリ文字列
    protected $queryString;
    
    // 現在のページID
    protected $currentPageId;
    
    // ページのサイズ
    protected $pageSize;
    
    // データのサイズ
    protected $dataSize;

    /**
     * コンストラクタ
     */
    public function __construct($pageMode = 0, $displayMode = 0, $size = 0, $display = 0)
    {
        $post = Vizualizer::request();
        $this->pageMode = $pageMode;
        $this->displayMode = $displayMode;
        $this->displayPages = $display;
        if ($this->pageMode == self::PAGE_JUMP && $this->displayPages == 0) {
            $this->displayPages = 7;
        }
        if ($this->pageMode == self::PAGE_SLIDE && $this->displayPages == 0) {
            $this->displayPages = 3;
        }
        $this->pageSize = $size;
        if (!($this->pageSize > 0)) {
            $this->pageSize = self::DEFAULT_PAGE_SIZE;
        }
        if ($post[self::PAGE_ID_KEY] > 0) {
            $this->currentPageId = $post[self::PAGE_ID_KEY];
        } else {
            $this->currentPageId = "1";
        }
        $this->queryString = "";
        if (count($post) > 0) {
            foreach ($post as $key => $value) {
                if ($key == self::PAGE_ID_KEY) {
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $k2 => $v2) {
                                if (!empty($this->queryString)) {
                                    $this->queryString .= "&";
                                }
                                $this->queryString .= urlencode($key . "[" . $k . "][" . $k2 . "]") . "=" . urlencode($v2);
                            }
                        } else {
                            if (!empty($this->queryString)) {
                                $this->queryString .= "&";
                            }
                            $this->queryString .= urlencode($key . "[" . $k . "]") . "=" . urlencode($v);
                        }
                    }
                } else {
                    if (!empty($this->queryString)) {
                        $this->queryString .= "&";
                    }
                    $this->queryString .= urlencode($key) . "=" . urlencode($value);
                }
            }
            if (!empty($this->queryString)) {
                $this->queryString = "?" . $this->queryString;
            }
        }
    }

    /**
     * モジュールのパラメータからテンプレートを抽出する。
     *
     * @param _pager_all ページャー全体のテンプレート
     * @param _pager_first 最初のページのテンプレート
     * @param _pager_prev 前のページのテンプレート
     * @param _pager_separator ページリンクのセパレータ
     * @param _pager_page ページリンクのテンプレート
     * @param _pager_current 現在ページリンクのテンプレート
     * @param _pager_next 次のページのテンプレート
     * @param _pager_last 最後のページのテンプレート
     */
    public function importTemplates($params)
    {
        if ($params->check("_pager_all")) {
            $this->pagerText = $params->get("_pager_all");
        }
        if ($params->check("_pager_first")) {
            $this->firstPageText = $params->get("_pager_first");
        }
        if ($params->check("_pager_prev")) {
            $this->prevPageText = $params->get("_pager_prev");
        }
        if ($params->check("_pager_separator")) {
            $this->separator = $params->get("_pager_separator");
        }
        if ($params->check("_pager_current")) {
            $this->currentPageText = $params->get("_pager_current");
        }
        if ($params->check("_pager_next")) {
            $this->nextPageText = $params->get("_pager_next");
        }
        if ($params->check("_pager_last")) {
            $this->lastPageText = $params->get("_pager_last");
        }
    }

    /**
     * データサイズを設定
     */
    public function setDataSize($size)
    {
        $this->dataSize = $size;
    }

    /**
     * データサイズを取得
     */
    public function getDataSize()
    {
        return $this->dataSize;
    }

    /**
     * 現在のページサイズを取得
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * ページ数を取得
     */
    public function getPages()
    {
        return ceil($this->dataSize / $this->pageSize);
    }

    /**
     * 現在のページ番号を取得
     */
    public function getCurrentPageId()
    {
        return $this->currentPageId;
    }

    /**
     * 次のページ番号を取得
     */
    public function getNextPageId()
    {
        return $this->currentPageId + 1;
    }

    /**
     * 前のページ番号を取得
     */
    public function getPrevPageId()
    {
        return $this->currentPageId - 1;
    }

    /**
     * 現在ページのデータの先頭オフセットを取得
     */
    public function getCurrentFirstOffset()
    {
        return ($this->getCurrentPageId() - 1) * $this->pageSize;
    }

    /**
     * 現在ページのデータの最終オフセットを取得
     */
    public function getCurrentLastOffset()
    {
        return $this->getCurrentPageId() * $this->pageSize - 1;
    }

    /**
     * ページャ全体のテンプレートを設定
     */
    public function setPagerText($text)
    {
        $this->pagerText = $text;
    }

    /**
     * ページ番号リンクのセパレータを設定
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * 最初のページのテンプレートを設定
     */
    public function setFirstPageText($text)
    {
        $this->firstPageText = $text;
    }

    /**
     * 最後のページのテンプレートを設定
     */
    public function setLastPageText($text)
    {
        $this->lastPageText = $text;
    }

    /**
     * 前のページのテンプレートを設定
     */
    public function setPrevPageText($text)
    {
        $this->prevPageText = $text;
    }

    /**
     * 次のページのテンプレートを設定
     */
    public function setNextPageText($text)
    {
        $this->nextPageText = $text;
    }

    /**
     * 現在ページ番号のテンプレートを設定
     */
    public function setCurrentPageText($text)
    {
        $this->currentPageText = $text;
    }

    /**
     * ページ番号のテンプレートを設定
     */
    public function setPageText($text)
    {
        $this->pageText = $text;
    }

    /**
     * ページ番号のリンクを作成する。
     */
    public function getPageLink()
    {
        if ($this->getCurrentPageId() > 1) {
            $first = $this->getFormatText($this->firstPageText, $this->getLink("1"));
            $prev = $this->getFormatText($this->prevPageText, $this->getLink($this->getPrevPageId()));
        } else {
            if ($this->displayMode == self::DISPLAY_HIDE) {
                $first = "";
                $prev = "";
            } else {
                $first = $this->getFormatText($this->firstPageText, $this->getLink("1"), "disabled");
                $prev = $this->getFormatText($this->prevPageText, $this->getLink($this->getPrevPageId()), "disabled");
            }
        }
        if ($this->getCurrentPageId() < $this->getPages()) {
            $last = $this->getFormatText($this->lastPageText, $this->getLink($this->getPages()));
            $next = $this->getFormatText($this->nextPageText, $this->getLink($this->getNextPageId()));
        } else {
            if ($this->displayMode == self::DISPLAY_HIDE) {
                $last = "";
                $next = "";
            } else {
                $last = $this->getFormatText($this->lastPageText, $this->getLink($this->getPages()), "disabled");
                $next = $this->getFormatText($this->nextPageText, $this->getLink($this->getNextPageId()), "disabled");
            }
        }
        $page = "";
        for ($p = $this->getMinDisplayPageId(); $p <= $this->getMaxDisplayPageId(); $p ++) {
            if (!empty($page)) {
                $page .= $this->separator;
            }
            if ($p == $this->getCurrentPageId()) {
                $page .= $this->getFormatText($this->currentPageText, $this->getLink($p), $p);
            } else {
                $page .= $this->getFormatText($this->pageText, $this->getLink($p), $p);
            }
        }
        return $this->getFormatText($this->pagerText, $first, $prev, $page, $next, $last);
    }

    /**
     * ページ番号から遷移先のリンクを取得する
     */
    protected function getLink($page)
    {
        if (!empty($this->queryString)) {
            return $this->queryString . "&" . self::PAGE_ID_KEY . "=" . $page;
        } else {
            return "?" . self::PAGE_ID_KEY . "=" . $page;
        }
    }

    /**
     * テンプレートからページのリンクを生成する。
     */
    protected function getFormatText($text)
    {
        // すべての引数を取得
        $args = func_get_args();
        
        // $textを$argsから取り除く
        array_shift($args);
        
        $replacePairs = array();
        foreach ($args as $i => $arg) {
            $replacePairs['{' . ($i + 1) . '}'] = $arg;
        }
        return strtr($text, $replacePairs);
    }

    /**
     * 表示するページ番号の最小を取得する。
     */
    private function getMinDisplayPageId()
    {
        switch ($this->pageMode) {
            case self::PAGE_ALL:
                return 1;
            case self::PAGE_JUMP:
                return floor(($this->getCurrentPageId() - 1) / $this->displayPages) * $this->displayPages + 1;
            case self::PAGE_SLIDE:
                if ($this->getCurrentPageId() <= $this->displayPages) {
                    return 1;
                } elseif ($this->getCurrentPageId() >= $this->getPages() - $this->displayPages) {
                    if ($this->getPages() > $this->displayPages * 2) {
                        return $this->getPages() - ($this->displayPages * 2);
                    } else {
                        return 1;
                    }
                } else {
                    return $this->getCurrentPageId() - $this->displayPages;
                }
        }
    }

    /**
     * 表示するページ番号の最大を取得する。
     */
    private function getMaxDisplayPageId()
    {
        switch ($this->pageMode) {
            case self::PAGE_ALL:
                return $this->getPages();
            case self::PAGE_JUMP:
                if ($this->getMinDisplayPageID() + $this->displayPages - 1 < $this->getPages()) {
                    return $this->getPages();
                } else {
                    return $this->getMinDisplayPageID() + $this->displayPages - 1;
                }
            case self::PAGE_SLIDE:
                if ($this->getCurrentPageId() + $this->displayPages >= $this->getPages()) {
                    return $this->getPages();
                } elseif ($this->getCurrentPageId() <= $this->displayPages) {
                    if ($this->getPages() > $this->displayPages * 2) {
                        return $this->displayPages * 2 + 1;
                    } else {
                        return $this->getPages();
                    }
                } else {
                    return $this->getCurrentPageId() + $this->displayPages;
                }
        }
    }
}
