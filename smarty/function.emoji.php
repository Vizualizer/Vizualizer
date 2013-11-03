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
function smarty_function_emoji($params, $smarty, $template)
{
    // codeパラメータは必須です。
    if (empty($params['code'])) {
        trigger_error("emoji: missing code parameter", E_USER_WARNING);
        return;
    }
	
	// パラメータを変数にコピー
    $code = $params['code'];
	
	if(preg_match("/[0-9]{1,3}/", $code) && is_numeric($code) && 0 < $code && $code < 253) {
		//変換表を配列に格納
		$emoji_array = array(); 
		$emoji_array[] = ""; 
		$contents = @file(dirname(__FILE__)."/emojix.csv"); 
		foreach($contents as $line){ 
			$line = rtrim( $line ); 
			$emoji_array[] = explode(",", $line); 
		}
		
		if(Net_UserAgent_Mobile::isMobile()){
			// モバイルユーザーエージェントのインスタンスを取得
			$agent = Net_UserAgent_Mobile::singleton();
			
			if($agent->isDoCoMo()){
				// DoCoMo
				echo mb_convert_encoding($emoji_array[$code][1], "UTF-8", "SJIS");
			}elseif($agent->isEZweb()){
				// au
				if (preg_match("/[^0-9]/", $emoji_array[$code][2])) {
					echo $emoji_array[$code][2];
				} else {
					echo "<img localsrc=\"".$emoji_array[$code][2]."\" />";
				}
			}elseif($agent->isSoftbank()){
				if($agent->isType3GC()){
					// Softbank-UTF8
					$e = new Emoji();
					if (preg_match("/^[A-Z]{1}?/", $emoji_array[$code][3])) {
						echo "\x1B\$".$emoji_array[$code][3]."\x0F";
					} else {
						echo $emoji_array[$code][3];
					}
				}else{
					// Softbank-SJIS
					if (preg_match("/^[A-Z]{1}?/", $emoji_array[$code][3])) {
						echo "\x1B\$".mb_convert_encoding($emoji_array[$code][3], "SJIS", "UTF-8")."\x0F";
					} else {
						echo mb_convert_encoding($emoji_array[$code][3], "SJIS", "UTF-8");
					}
				}
			}
		}else{
			echo "<img src=\"/emoji_images/".$emoji_array[$code][0].".gif\" width=\"12\" height=\"12\" border=\"0\" alt=\"\" />";
		}
	}else{
		// 絵文字のコードが規定値以外
		return "[Error!]\n";
	}
}

class Emoji {
	function softbank_sjis2utf8($text) {
		$str_ar = unpack("C*",$text);
		$len = count($str_ar);
		$buf = "";
		$res = "";
		$idx = 1;
		while($idx <= $len) {
			$c1 = $str_ar[$idx];
			$c2 = $str_ar[$idx+1];
			$c3 = $c4 = 0;
			if ($c1 == 0xF9 && ($c2 >= 0x41 && $c2 <= 0x7E)) {			//1 
				$c3 = 0x47;
				$c4 = $c2 - 0x20;
			} elseif ($c1 == 0xF9 && ($c2 >= 0x80 && $c2 <= 0x9B)) {
				$c3 = 0x47;
				$c4 = $c2 - 0x21;
			} elseif ($c1 == 0xF7 && ($c2 >= 0x41 && $c2 <= 0x7E)) {	//2 
				$c3 = 0x45;
				$c4 = $c2 - 0x20;
			} elseif ($c1 == 0xF7 && ($c2 >= 0x80 && $c2 <= 0x9B)) {
				$c3 = 0x45;
				$c4 = $c2 - 0x21;
			} elseif ($c1 == 0xF7 && ($c2 >= 0xA1 && $c2 <= 0xF3)) {	// 3
				$c3 = 0x46;
				$c4 = $c2 - 0x80;
			} elseif ($c1 == 0xF9 && ($c2 >= 0xA1 && $c2 <= 0xED)) {	// 4
				$c3 = 0x4F;
				$c4 = $c2 - 0x80;
			} elseif ($c1 == 0xFB && ($c2 >= 0x41 && $c2 <= 0x7E)) {	// 5
				$c3 = 0x50;
				$c4 = $c2 - 0x20;
			} elseif ($c1 == 0xFB && ($c2 >= 0x80 && $c2 <= 0x8D)) {
				$c3 = 0x50;
				$c4 = $c2 - 0x21;
			} elseif ($c1 == 0xFB && ($c2 >= 0xA1 && $c2 <= 0xD7)) {	// 6
				$c3 = 0x51;
				$c4 = $c2 - 0x80;
			} 
			if ($c3 && $c4) {
				if ($buf != "") {
					$res .= mb_convert_encoding($buf,"UTF8","SJIS-WIN");
					$buf = "";
				}
				$res .= "\x1B\x24".chr($c3).chr($c4)."\x0F";
				$idx += 2;
				continue;
			}
			// 絵文字ではない
			if($c1 >= 0x80) {		// 2byte
				$buf .= chr($c1).chr($c2);
				$idx += 2;
			} else {						// ascii
				$buf .= chr($c1);
				$idx++;
			}
		}
		if ($buf != "") {
			$res .= mb_convert_encoding($buf,"UTF8","SJIS-WIN");
			$buf = "";
		}
		return $res;
	}
	function softbank_utf82sjis($text){
		$str_ar = unpack("C*",$text);
		$len = count($str_ar);
		$buf = "";
		$res = "";
		$idx = 1;
		while($idx <= $len) {
			$c1 = $str_ar[$idx];
			$c2 = $str_ar[$idx+1];
			$c3 = $str_ar[$idx+2];
			// 余計な 0x0F がついてることがある
			if ($c1 == 0x0F) {
				$idx++;
				continue;
			}
			// 絵文字 (0x1Bから始まる)
			if ($c1 == 0x1B) {
				if ($buf != "") {
					$res .= mb_convert_encoding($buf,"sjis-win","UTF8");
					$buf = "";
				}
				if ($c2 == 0x24) {
					// softbank 5byte のうち、3,4byte目が可変
					$c1 = $str_ar[$idx+2];
					$c2 = $str_ar[$idx+3];
					// ここで softbank絵文字を Shift_JIS に変換して文字列に追加
					$res .= $this->_softbank_char_5b2sjis($c1,$c2);
				}
				if ($str_ar[$idx+4] == 0x0F) { // 0x0F
				}
				$idx += 5;
			} 
			// 絵文字 (utf8バイナリ)
			else if (($c1 == 0xEE) && 
				((($c2 == 0x80 && (0x81 <= $c3 && $c3 <= 0xEF)) || 
				  ($c2 == 0x81 && (0x80 <= $c3 && $c3 <= 0x9A)))   || 
				 (($c2 == 0x84 && (0x81 <= $c3 && $c3 <= 0xEF)) || 
				  ($c2 == 0x85 && (0x80 <= $c3 && $c3 <= 0x9A)))   || 
				 (($c2 == 0x88 && (0x81 <= $c3 && $c3 <= 0xEF)) || 
				  ($c2 == 0x89 && (0x80 <= $c3 && $c3 <= 0x9A)))   || 
				 (($c2 == 0x8C && (0x81 <= $c3 && $c3 <= 0xEF)) || 
				  ($c2 == 0x8D && (0x80 <= $c3 && $c3 <= 0x9A)))   || 
				 (($c2 == 0x90 && (0x81 <= $c3 && $c3 <= 0xEF)) || 
				  ($c2 == 0x91 && (0x80 <= $c3 && $c3 <= 0x9A)))   || 
				 (($c2 == 0x94 && (0x81 <= $c3 && $c3 <= 0xBE)))    )
				) 
			{
				if ($c = $this->_softbank_char_utf82sjis($c2,$c3)) {
					if ($buf != "") {
						$res .= mb_convert_encoding($buf,"sjis-win","UTF8");
						$buf = "";
					}
					$res .= $c;
				}
				$idx += 3;
			}
			// 絵文字ではない
			else if ($c1 >= 0xE0) {			// 3byte
				$buf .= chr($c1).chr($c2).chr($c3);
				$idx += 3;
			} else if($c1 >= 0x80) {		// 2byte
				$buf .= chr($c1).chr($c2);
				$idx += 2;
			} else {						// ascii
				$buf .= chr($c1);
				$idx++;
			}
		}
		if ($buf != "") {
			$res .= mb_convert_encoding($buf,"sjis-win","UTF8");
			$buf = "";
		}
		return $res;
	}
	
	function _softbank_char_5b2sjis($c3,$c4) {		// 0x1B 0x24 の次の2byteのみ
		$_c1 = $_c2 = 0;
		if ($c3 == 0x47) { 
			if ($c4 < 0x60) {		// 1
				$_c1 = 0x80;
				$_c2 = ($c4 + 0x60);
			} else {
				$_c1 = 0x81;
				$_c2 = ($c4 + 0x20);
			}
		} elseif($c3 == 0x45) {		// 2
			if ($c4 < 0x60) {
				$_c1 = 0x84;
				$_c2 = ($c4 + 0x60);
			} else {
				$_c1 = 0x85;
				$_c2 = ($c4 + 0x20);
			}
		} elseif($c3 == 0x46) {		// 3
			if ($c4 < 0x60) {
				$_c1 = 0x88;
				$_c2 = ($c4 + 0x60);
			} else {
				$_c1 = 0x89;
				$_c2 = ($c4 + 0x20);
			}
		} elseif($c3 == 0x4F) {		// 4
			if ($c4 < 0x60) {
				$_c1 = 0x8C;
				$_c2 = ($c4 + 0x60);
			} else {
				$_c1 = 0x8D;
				$_c2 = ($c4 + 0x20);
			}
		} elseif($c3 == 0x50) {		// 5
			if ($c4 < 0x60) {
				$_c1 = 0x90;
				$_c2 = ($c4 + 0x60);
			} else {
				$_c1 = 0x91;
				$_c2 = ($c4 + 0x20);
			}
		} elseif($c3 == 0x51) {		// 6
			if ($c4 < 0x60) {
				$_c1 = 0x94;
				$_c2 = ($c4 + 0x60);
			} else {
				$_c1 = 0x94;
				$_c2 = ($c4 + 0x20);
			}
		} 
		if ($_c1 && $_c2) {
			return $this->_softbank_char_utf82sjis($_c1,$_c2);
		}
	}
	function _softbank_char_utf82sjis($c2, $c3) {	// 1byte 目は 0xEEしかないので省略
		$r1 = $r2 = 0;
		// 1 
		if ($c2 == 0x80 && (0x81 <= $c3 && $c3 <= 0xBF)) {
			$r1 = 0xF9;
			$r2 = ($c3 == 0xBF) ? 0x80 : ($c3 - 0x40);
		} elseif ($c2 == 0x81 && (0x80 <= $c3 && $c3 <= 0x9A)) {
			$r1 = 0xF9;
			$r2 = ($c3 + 0x01);
		}
		// 2 
		else if ($c2 == 0x84 && (0x81 <= $c3 && $c3 <= 0xBF)) {
			$r1 = 0xF7;
			$r2 = ($c3 == 0xBF) ? 0x80 : ($c3 - 0x40);
		} elseif ($c2 == 0x85 && (0x80 <= $c3 && $c3 <= 0x9A)) {
			$r1 = 0xF7;
			$r2 = ($c3 + 0x01);
		}
		// 3 
		else if ($c2 == 0x88 && (0x81 <= $c3 && $c3 <= 0xBF)) {
			$r1 = 0xF7;
			$r2 = ($c3 + 0x20);
		} elseif ($c2 == 0x89 && (0x80 <= $c3 && $c3 <= 0x93)) {
			$r1 = 0xF7;
			$r2 = ($c3 + 0x60);
		}
		// 4 
		else if ($c2 == 0x8C && (0x81 <= $c3 && $c3 <= 0xBF)) {
			$r1 = 0xF9;
			$r2 = ($c3 + 0x20);
		} elseif ($c2 == 0x8D && (0x80 <= $c3 && $c3 <= 0x8D)) {
			$r1 = 0xF9;
			$r2 = ($c3 + 0x60);
		}
		// 5 
		else if ($c2 == 0x90 && (0x81 <= $c3 && $c3 <= 0xBF)) {
			$r1 = 0xFB;
			$r2 = ($c3 - 0x40);
		} elseif ($c2 == 0x91 && (0x80 <= $c3 && $c3 <= 0x8C)) {
			$r1 = 0xFB;
			$r2 = ($c3 + 0x01);
		}
		// 6 
		else if ($c2 == 0x94 && (0x81 <= $c3 && $c3 <= 0xB1)) {
			$r1 = 0xFB;
			$r2 = ($c3 + 0x20);
		} 
		return ($r1 && $r2) ? chr($r1).chr($r2) : "";
	}
}
?>