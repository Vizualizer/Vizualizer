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
 * テキスト形式のメール送信に使用するクラスです。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Sendmail
{

    /**
     * メールの送信元（名称）
     */
    protected $from;

    /**
     * メールの送信元アドレス
     */
    protected $fromAddress;

    /**
     * メールの送信先（名称）
     */
    protected $to;

    /**
     * メールの送信先アドレス
     */
    protected $toAddress;

    /**
     * メールのタイトル
     */
    protected $subject;

    /**
     * メールの本文
     */
    protected $body;

    /**
     * メールの追加本文
     */
    protected $extBody;

    /**
     * 添付ファイル
     */
    protected $files;

    /**
     * インライン画像ファイル
     */
    protected $images;

    /**
     * コンストラクタです。テキストメールのための初期設定を行います。
     *
     * @access public
     */
    public function __construct()
    {
        $this->db = null;
        $this->body = "";
        $this->extBody = array();
        $this->files = array();
    }

    /**
     * メールの送信元を設定します。
     *
     * @param s string $address 送信元のメールアドレス
     * @param s string $name 送信元のメールアドレスに対応する名前
     * @access public
     */
    public function setFrom($address, $name = "")
    {
        $this->fromAddress = $address;
        if (!empty($name)) {
            $this->from = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($name, "JIS", "UTF-8")) . "?= <" . $address . ">";
        } else {
            $this->from = $address;
        }
    }

    /**
     * メールの送信先を設定します。
     *
     * @param s string $address 送信先のメールアドレス
     * @param s string $name 送信先のメールアドレスに対応する名前
     * @access public
     */
    public function setTo($address, $name = "")
    {
        $this->toAddress = $address;
        if (!empty($name)) {
            $this->to = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($name, "JIS", "UTF-8")) . "?= <" . $address . ">";
        } else {
            $this->to = $address;
        }
    }

    /**
     * メールのタイトルを設定します。
     *
     * @param s string $subject メールのタイトル
     * @access public
     */
    public function setSubject($subject)
    {
        $this->subject = "=?iso-2022-jp?B?" . base64_encode(mb_convert_encoding($subject, "JIS", "UTF-8")) . "?=";
    }

    /**
     * メールの本文を設定します。
     *
     * @param s string $parts メールの本文
     * @access public
     */
    public function addBody($parts)
    {
        $this->body .= $parts;
    }

    /**
     * メールの追加本文を設定します。
     *
     * @param s string $data メールのファイルデータ
     * @param s string $mimeType メールのファイルデータのMIMEタイプ
     * @param s string $encoding メールのファイルデータのエンコード
     * @access public
     */
    public function setExtBody($data, $mimeType = "text/plain", $encoding = "7bit")
    {
        $this->extBody = array("data" => $data, "mimeType" => $mimeType, "encoding" => $encoding);
    }

    /**
     * メールの添付ファイルを追加します。
     *
     * @param s string $data メールのファイルデータ
     * @param s string $mimeType メールのファイルデータのMIMEタイプ
     * @param s string $encoding メールのファイルデータのエンコード
     * @access public
     */
    public function addFile($data, $mimeType = "text/plain", $encoding = "7bit")
    {
        $this->files[] = array("data" => $data, "mimeType" => $mimeType, "encoding" => $encoding);
    }

    /**
     * メールのインライン画像ファイルを追加します。
     *
     * @param s string $data メールのファイルデータ
     * @param s string $mimeType メールのファイルデータのMIMEタイプ
     * @param s string $encoding メールのファイルデータのエンコード
     * @access public
     */
    public function addInlineImage($data, $mimeType = "image/jpeg")
    {
        $this->images[] = array("data" => $data, "mimeType" => $mimeType);
    }

    /**
     * テキストメールを送信します。
     *
     * @parmas string $contentType メール全体のコンテンツタイプ。
     *
     * @param s string $suffix メールの最後に付加する文字列メール毎に違う文章を設定する際に利用。
     * @access public
     */
    public function send($contentType = "text/plain", $suffix = "")
    {
        // 添付ファイルが存在している場合にはマルチパートメールにする。
        if (!empty($this->extBody) || count($this->images) > 0 || count($this->files) > 0) {
            $boundary_base = "claymail-" . uniqid("b");
            $boundary = $boundary_base . "_mixed";
            $boundary2 = $boundary_base . "_related";
            $boundary3 = $boundary_base . "_alternative";
            $body = "\n--" . $boundary . "\n";
            $body .= "Content-Type: multipart/related;boundary=\"" . $boundary2 . "\"\n\n";
            $body .= "\n--" . $boundary2 . "\n";
            $body .= "Content-Type: multipart/alternative;boundary=\"" . $boundary3 . "\"\n\n";
            $body .= "\n--" . $boundary3 . "\n";
            $body .= "Content-Type: " . $contentType . "; charset=iso-2022-jp\n";
            $body .= "Content-Transfer-Encoding: quoted-printable\n\n";
            $body .= $this->qp_encode(mb_convert_encoding($this->body . $suffix, "JIS", "UTF-8"));
            if (!empty($this->extBody)) {
                $body .= "\n--" . $boundary3 . "\n";
                $body .= "Content-Type: " . $this->extBody["mimeType"] . "\n";
                $body .= "Content-Transfer-Encoding: " . $this->extBody["encoding"] . "\n\n";
                if ($this->extBody["encoding"] == "quoted-printable") {
                    $body .= $this->qp_encode($this->extBody["data"]);
                } elseif ($this->extBody["encoding"] == "base64") {
                    $body .= chunk_split(base64_encode($this->extBody["data"]));
                } else {
                    $body .= $this->extBody["data"];
                }
            }
            $body .= "\n--" . $boundary3 . "--\n";
            if (count($this->images) > 0) {
                foreach ($this->files as $file) {
                    $body .= "\n--" . $boundary2 . "\n";
                    $body .= "Content-Type: " . $file["mimeType"] . "\n";
                    $body .= "Content-Transfer-Encoding: base64\n\n";
                    $body .= chunk_split(base64_encode($file["data"]));
                }
            }
            $body .= "\n--" . $boundary2 . "--\n";
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    $body .= "\n--" . $boundary . "\n";
                    $body .= "Content-Type: " . $file["mimeType"] . "\n";
                    $body .= "Content-Transfer-Encoding: " . $file["encoding"] . "\n\n";
                    if ($file["encoding"] == "quoted-printable") {
                        $body .= $this->qp_encode($file["data"]);
                    } elseif ($file["encoding"] == "base64") {
                        $body .= chunk_split(base64_encode($file["data"]));
                    } else {
                        $body .= $file["data"];
                    }
                }
            }
            $body .= "\n--" . $boundary . "--\n";
            $contentType = "multipart/mixed;boundary=\"" . $boundary . "\"";
            $transferEncoding = "";
        } else {
            // デフォルトのコンテンツタイプと本文を設定
            $contentType = $contentType . "; charset=iso-2022-jp";
            $transferEncoding = "7bit";
            $body = mb_convert_encoding($this->body . $suffix, "JIS", "UTF-8");
        }
        // メールヘッダを作成
        $this->sendRaw($this->from, $this->fromAddress, $this->to, $this->subject, $body, $contentType, $transferEncoding);
    }

    /**
     * テキストメールを送信します。
     *
     * @parmas string $contentType メール全体のコンテンツタイプ。
     *
     * @param s string $suffix メールの最後に付加する文字列メール毎に違う文章を設定する際に利用。
     * @access public
     */
    public function sendlog($contentType = "text/plain", $suffix = "")
    {
        $this->send($contentType, $suffix);

        // メールログに書き込み
        $this->logMail();
    }

    /**
     * メールログに保存する。
     */
    protected function logMail()
    {
        /*
        // ローダーを初期化
        $loader = new Vizualizer_Plugin();
        // メールログのテーブルモデルを読み込み
        $maillogs = $loader->LoadTable("MaillogsTable");

        // データベースINSERTモデルの読み込み
        $insert = new Clay_Query_Insert($maillogs);

        // 設定するデータ配列を定義
        $values = array();
        $values["mail_from"] = $this->fromAddress;
        $values["mail_to"] = $this->toAddress;
        $values["subject"] = $this->subject;
        $values["body"] = $this->body;
        $values["mail_time"] = date("Y-m-d H:i:s");

        // INSERTの実行
        try {
            $insert->execute($values);
        } catch (Exception $e) {
            // メールログの書き込み失敗はエラーと見なさない。
        }
        */
    }

    public function sendRaw($from, $fromAddress, $to, $subject, $body, $contentType = "text/plain", $transferEncoding = "7bit")
    {
        // メールヘッダを作成
        $header = "";
        $header .= "From: " . $from . "\n";
        $header .= "Reply-To: " . $from . "\n";
        $header .= "MIME-Version: 1.0\n";
        $header .= "Content-Type: " . $contentType . "\n";
        if (!empty($transferEncoding)) {
            $header .= "Content-Transfer-Encoding: " . $transferEncoding . "\n";
        }
        $header .= "X-Mailer: PHP/" . phpversion();

        if (!mail($to, $subject, $body, $header, "-f " . $fromAddress)) {
            Vizualizer_Logger::writeAlert("メール送信に失敗しました。");
        }
    }

    // エンコード関数
    function qp_encode($text)
    {
        if (function_exists('quoted_printable_encode')) {
            return quoted_printable_encode($text);
        } elseif (function_exists('imap_8bit')) {
            return imap_8bit($text);
        } else {
            $arrEncodeSupport = mb_list_encodings();
            if (array_search('Quoted-Printable', $arrEncodeSupport) != FALSE) {
                return mb_convert_encoding($text, 'Quoted-Printable', "JIS");
            } else {
                $crlf = "\r\n";
                $text = trim($text);

                $lines = preg_split("/(\r\n|\n|\r)/s", $text);

                $out = '';
                $temp = '';

                foreach ($lines as $line) {
                    for ($j = 0; $j < strlen($line); $j ++) {
                        $char = substr($line, $j, 1);
                        $ascii = ord($char);

                        if ($ascii < 32 || $ascii == 61 || $ascii > 126) {
                            $char = '=' . strtoupper(dechex($ascii));
                        }

                        if ((strlen($temp) + strlen($char)) >= 76) {
                            $out .= $temp . '=' . $crlf;
                            $temp = '';
                        }
                        $temp .= $char;
                    }
                }
                $out .= $temp;

                return trim($out);
            }
        }
    }
}
