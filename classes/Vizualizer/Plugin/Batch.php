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
 * このシステムにおけるバッチ処理の基底クラスになります。
 *
 * @package Vizualizer
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
abstract class Vizualizer_Plugin_Batch extends Vizualizer_Plugin_Module
{

    /**
     * バッチをデーモン化する場合には、ここで名前を返す
     */
    public function getDaemonName()
    {
        return "";
    }

    /**
     * デーモン化したバッチの1回あたりの待機時間
     */
    public function getDaemonInterval()
    {
        return 10;
    }

    /**
     * バッチの名前を取得する
     */
    public abstract function getName();

    /**
     * バッチの処理フロー配列を取得する
     */
    public abstract function getFlows();

    /**
     * unlockがあるか確認する
     */
    protected function isUnlocked()
    {
        return file_exists($this->getDaemonName() . ".unlock");
    }

    /**
     * デフォルト実行のメソッドになります。
     * このメソッド以外がモジュールとして呼ばれることはありません。
     *
     * @param array $params モジュールの受け取るパラメータ
     */
    public function execute($params)
    {
        // 出力バッファをリセットする。
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        Vizualizer_Logger::$logFilePrefix = "batch_";
        Vizualizer_Logger::$logOutputStandard = true;
        Vizualizer_Logger::writeInfo("Batch " . $this->getName() . " Start.");
        if ($this->getDaemonName() != "") {
            if (count($params) > 3 && $params[3] == "stop") {
                if (($fp = fopen($this->getDaemonName() . ".unlock", "w+")) !== FALSE) {
                    fclose($fp);
                }
            }elseif (($fp = fopen($this->getDaemonName() . ".lock", "a+")) !== FALSE) {
                if (!flock($fp, LOCK_EX | LOCK_NB)) {
                    list($time, $pid) = explode(",", file_get_contents($this->getDaemonName() . ".lock"));
                    // 12時間以上起動し続けている場合は再起動を実施
                    if($time + 12 * 3600 < time()){
                        system("kill -HUP ".$pid);
                    }
                    Vizualizer_Logger::writeInfo("Batch " . $this->getName() . " was already running.");
                    die("プログラムは既に実行中です。");
                }

                // デーモンの起動時刻とプロセスIDをロックファイルに記述
                ftruncate($fp, 0);
                fwrite($fp, time().",".getmypid());

                if ($this->isUnlocked()) {
                    // 実行前にunlockファイルがある場合は予め削除する。
                    unlink($this->getDaemonName() . ".unlock");
                }

                while (true) {
                    Vizualizer::now()->reset();

                    Vizualizer_Logger::writeInfo("==== START ".$this->getName()." ROUTINE ======");

                    $this->executeImpl($params);

                    Vizualizer_Logger::writeInfo("==== END ".$this->getName()." ROUTINE ======");

                    if (file_exists($this->getDaemonName() . ".unlock")) {
                        // unlockファイルがある場合はループを終了
                        unlink($this->getDaemonName() . ".unlock");
                        break;
                    }

                    // 一周回ったら所定秒数ウェイト
                    if($this->getDaemonInterval() > 10){
                        sleep($this->getDaemonInterval());
                    }else{
                        sleep(60);
                    }
                }
                fclose($fp);
            }
        } else {
            if (($fp = fopen($this->getDaemonName() . ".lock", "w+")) !== FALSE) {
                if (!flock($fp, LOCK_EX | LOCK_NB)) {
                    Vizualizer_Logger::writeInfo("Batch " . $this->getName() . " was already running.");
                    die("プログラムは既に実行中です。");
                }

                $this->executeImpl($params);

                fclose($fp);
            }
        }
        Vizualizer_Logger::writeInfo("Batch " . $this->getName() . " End.");
    }

    /**
     * デフォルト実行のメソッドの本体になります。
     * このメソッド以外がモジュールとして呼ばれることはありません。
     *
     * @param array $params モジュールの受け取るパラメータ
     */
    protected function executeImpl($params)
    {
        $data = array();
        foreach ($this->getFlows() as $flow) {
            if (method_exists($this, $flow)) {
                Vizualizer_Logger::writeInfo("Execute Module : " . $flow);
                try {
                    $data = $this->$flow($params, $data);
                } catch (Exception $e) {
                    // 例外発生時にはエラーログを出力し、バッチ自体を終了させる。
                    Vizualizer_Logger::writeError("Batch failed in " . $flow . ".", $e);
                    break;
                }
            } else {
                // 必要なモジュールが無かった場合はエラーログを出力し、終了させる。
                Vizualizer_Logger::writeAlert("Module " . $flow . " was not found.");
                break;
            }
        }
    }
}
