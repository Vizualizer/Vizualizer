<?php

/**
 * This file is part of CLAY Framework for view-module based system.
 *
 * @author    Naohisa Minagawa <info@clay-system.jp>
 * @copyright Copyright (c) 2010, Naohisa Minagawa
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 * @since PHP 5.3
 * @version   3.0.0
 */
class LoadModuleParams
{

    var $params;

    function __construct($params)
    {
        $this->params = $params;
    }

    function check($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }

    function get($name, $default = "")
    {
        if (isset($this->params[$name]) && $this->params[$name] != null && $this->params[$name] != "") {
            return $this->params[$name];
        }
        return $default;
    }
}

/**
 * Smarty {loadmodule} function plugin
 *
 * Type: function<br>
 * Name: loadmodule<br>
 * Purpose: load framework module.<br>
 *
 * @author Naohisa Minagawa <minagawa at web-life dot co dot jp>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string null
 */
function smarty_function_loadmodule($params, $template)
{
    // nameパラメータは必須です。
    if (empty($params['name'])) {
        trigger_error("loadmodule: missing name parameter", E_USER_WARNING);
        return;
    }

    if (!empty($params["if"])) {
        $result = true;
        $expression = '$result = ' . str_replace('_POST', '$_POST', $params["if"]) . ';';
        eval($expression);
        if (!$result) {
            return;
        }
    }

    // パラメータを変数にコピー
    $name = $params['name'];

    // errorパラメータはエラー例外時に指定されたテンプレートに変更する。
    if (isset($params["error"])) {
        $error = $params['error'];
    } else {
        $error = "";
    }

    // モジュールのクラスが利用可能か調べる。
    $errors = null;

    try {
        // モジュール用のクラスをリフレクション
        list ($namespace, $class) = explode(".", $name, 2);
        $loader = new Vizualizer_Plugin($namespace);
        $object = $loader->loadModule($class);
        if (method_exists($object, "execute")) {
            Vizualizer_Logger::writeDebug("=========== " . $name . " start ===========");
            // 検索条件と並べ替えキー以外を無効化する。
            if (isset($params["clear"]) && $params["clear"] == "1") {
                if (isset($params["sort_key"]) && !empty($params["sort_key"])) {
                    $_POST = array("search" => $_POST["search"], $params["sort_key"] => $_POST[$params["sort_key"]]);
                } else {
                    $_POST = array("search" => $_POST["search"]);
                }
            }
            if (!empty($params["key_prefix"])) {
                $object->key_prefix = $params["key_prefix"];
            } else {
                $object->key_prefix = "";
            }
            if (!empty($params["continue"])) {
                $object->continue = $params["continue"];
            } else {
                $object->continue = "";
            }
            $object->execute(new LoadModuleParams($params));
            Vizualizer_Logger::writeDebug("=========== " . $name . " end ===========");
        } else {
            Vizualizer_Logger::writeAlert($name . " is not plugin module.");
        }
    } catch (Vizualizer_Exception_Invalid $e) {
        // 入力エラーなどの例外（ただし、メッセージリストを空にすると例外処理を行わない）
        Vizualizer_Logger::writeError($e->getMessage(), $e);
        $errors = $e->getErrors();
    } catch (Vizualizer_Exception_Database $e) {
        // システムエラーの例外処理
        Vizualizer_Logger::writeError($e->getMessage(), $e);
        $errors = array(Vizualizer::ERROR_TYPE_DATABASE => $e->getMessage());
    } catch (Vizualizer_Exception_System $e) {
        // システムエラーの例外処理
        Vizualizer_Logger::writeError($e->getMessage(), $e);
        $errors = array(Vizualizer::ERROR_TYPE_SYSTEM => $e->getMessage());
    } catch (Exception $e) {
        // 不明なエラーの例外処理
        Vizualizer_Logger::writeError($e->getMessage(), $e);
        $errors = array(Vizualizer::ERROR_TYPE_UNKNOWN => $e->getMessage());
    }

    // エラー配列をスタックさせる
    if (is_array($errors) && !empty($errors)) {
        $attr = Vizualizer::attr();
        // エラー用配列が配列になっていない場合は初期化
        $errorData = $attr[Vizualizer::ERROR_KEY];
        if (!is_array($errorData)) {
            $errorData = array();
        }
        // エラー内容をマージさせる。
        foreach($errors as $key => $message){
            if($key != "" && !array_key_exists($key, $errorData)){
                $errorData[$key] = $message;
            }
        }
        $templateEngine = $attr["template"];
        if (!empty($error)) {
            // errorパラメータが渡っている場合はスタックさせたエラーを全て出力してエラー画面へ
            $templateEngine->assign("ERRORS", $errorData);
            unset($attr[Vizualizer::ERROR_KEY]);
            $templateEngine->display($error);
            exit;
        } else {
            $attr[Vizualizer::ERROR_KEY] = $errorData;
        }
    }
}
