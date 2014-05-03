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
 * フレームワークの初期化処理を制御するクラス
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class Vizualizer_Bootstrap
{

    /**
     * 実行するブートストラップの処理リスト
     */
    private static $bootstraps = array();

    /**
     * ブートストラップを登録するメソッド
     *
     * @param int $order 実行順
     * @param string $bootstrap 実行するブートストラップの名前
     * @return boolean 登録成功ならtrue、実行順に登録済みで失敗したらfalse
     */
    public static function register($order, $bootstrap)
    {
        if (!array_key_exists($order, self::$bootstraps)) {
            self::$bootstraps[$order] = $bootstrap;
            return true;
        }
        return false;
    }

    /**
     * 初期化処理を起動するためのメソッドです。
     * この中で各Bootstrapモジュールを呼び出します。
     */
    public static function startup()
    {
        ksort(self::$bootstraps);
        reset(self::$bootstraps);
        foreach (self::$bootstraps as $bootstrap) {
            // クラス名を生成
            $class = "Vizualizer_Bootstrap_" . $bootstrap;
            // 開始メソッドを実行
            $class::start();
        }

        // 終了時に自動的に終了処理が呼ばれるように設定
        register_shutdown_function(array("Vizualizer", "shutdown"));
    }

    /**
     * 終了処理を起動するためのメソッドです。
     * この中で各Bootstrapモジュールの終了を行います。
     */
    public static function shutdown()
    {
        rsort(self::$bootstraps);
        reset(self::$bootstraps);
        foreach (self::$bootstraps as $bootstrap) {
            // クラス名を生成
            $class = "Vizualizer_Bootstrap_" . $bootstrap;
            // 開始メソッドを実行
            $class::stop();
        }
    }
}