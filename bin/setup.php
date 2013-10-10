<?php
// 現在のディレクトリを取得
$currentDir = realpath(dirname("."));

// 実行ファイルのディレクトリを取得
$binDirReal = realpath(dirname(__FILE__));
$binDir = str_replace($currentDir, "", $binDirReal);

// パッケージのベースディレクトリを取得
$baseDirReal = realpath($binDirReal."/../");
$baseDir = str_replace($currentDir, "", $baseDirReal);

echo "セットアップを開始します。\r\n\r\n";

do {
    // 設定するドメインをパラメータから取得
    echo "設定を行うドメインを入力してください。\r\n";
    echo "ドメイン : ";
    $domainName = trim(fgets(STDIN));
} while(empty($domainName));

do {
    // サイトコードをパラメータから取得
    echo "サイトコードを入力してください。\r\n";
    echo "サイトコード : ";
    $siteCode = trim(fgets(STDIN));
} while(empty($siteCode) || preg_match("/^[a-z0-9_-]+$/", $siteCode) == 0);

do {
    // サイト名をパラメータから取得
    echo "サイト名を入力してください。\r\n";
    echo "サイト名 : ";
    $siteName = trim(fgets(STDIN));
} while(empty($siteName));

do {
    // DBのホストをパラメータから取得
    echo "MySQLの接続ホスト名を入力してください。\r\n";
    echo "MySQLホスト : ";
    $dbServer = trim(fgets(STDIN));
} while(empty($dbServer));

do {
    // DBのポートをパラメータから取得
    echo "MySQLの接続ポート番号を入力してください。\r\n";
    echo "MySQLポート : ";
    $dbPort = trim(fgets(STDIN));
} while(empty($dbPort) || !($dbPort > 0));

do {
    // DBのユーザー名をパラメータから取得
    echo "MySQLの接続ユーザー名を入力してください。\r\n";
    echo "MySQLユーザー : ";
    $dbUser = trim(fgets(STDIN));
} while(empty($dbUser));

do {
    // DBのパスワードをパラメータから取得
    echo "MySQLの接続パスワードを入力してください。\r\n";
    echo "MySQLパスワード : ";
    $dbPass = trim(fgets(STDIN));
} while(empty($dbPass));


