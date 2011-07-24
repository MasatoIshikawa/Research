<?php
/**
 * Description of SortingBaseModel
 *
 * @author Yoshihide
 */
class SortingBaseModel extends AbstractBaseModel{

	public $katakana_list;
	public $cho_on_list;
	public $nigori_list;
	public $clean_list;

	public function _initHiraganaList(){
		//優先度付きカタカナ一覧
		$this ->katakana_list = 'ァアィイゥウェエォオ'.
								'カガキギクグケゲコゴ'.
								'サザシジスズセゼソゾ'.
								'タダチヂツヅテデトド'.
								'ナニヌネノ'.
								'ハバパヒビピフブプヘベペホボポ'.
								'マミムメモ'.
								'ャヤュユョヨ'.
								'ラリルレロ'.
								'ヮワヲン';

		//katakanaに対応する母音
		$this ->cho_on_list =	'アアイイウウエエオオ'.
								'アアイイウウエエオオ'.
								'アアイイウウエエオオ'.
								'アアイイウウエエオオ'.
								'アイウエオ'.
								'アアアイイイウウウエエエオオオ'.
								'アイウエオ'.
								'アアウウオオ'.
								'アイウエオ'.
								'アアオン';

		//濁りリスト
		$this ->nigori_list =	'ガギグゲゴ'.
								'ザジズゼゾ'.
								'ダヂヅデド'.
								'バパビピブプベペボポ';
								
		//非濁りリスト
		$this ->clean_list =	'カキクケコ'.
								'サシスセソ'.
								'タチツテト'.
								'ハハヒヒフフヘヘホホ';

		//self check
		if(mb_strlen($this ->katakana_list) != mb_strlen($this ->cho_on_list)){
			trigger_error('SortingBaseModelの初期化に失敗しました。', E_USER_ERROR);
		}
	}

	public function TranslateHyphenToJA($str){
		$output = '';
		
		while(($now = mb_strpos($str, 'ー')) !== false){
			$output .= mb_substr($str, 0, $now);
			$output .= mb_substr($this ->cho_on_list, mb_strpos($this ->katakana_list, mb_substr($str, $now - 1, 1)), 1);
			if(mb_strlen($str) <= $now + 1){
				$str = '';
				break;
			}
			$str = mb_substr($str, $now + 1);
		}

		return $output.$str;
	}

	public function nikorilizer($str){
		$output = '';

		for($i = 0; $i < mb_strlen($str); $i++){
			if(($pos = mb_strpos($this ->nigori_list, mb_substr($str, $i, 1))) !== false){
				$output .= mb_substr($this ->clean_list, $pos, 1);
			}
			else{
				$output .= mb_substr($str, $i, 1);
			}
		}

		return $output;
	}

	public function CancelUnknownChar($str){
		$output = '';
		
		for($i = 0; $i < mb_strlen($str); $i ++){
			$now_char = mb_substr($str, $i, 1);

			if($now_char == 'ー' || mb_strpos($this ->katakana_list, $now_char) !== false){
				$output .= $now_char;
			}
		}

		return $output;
	}

	public function ConvertToCompareingNumerics($str, $padding_len){
		$output = '';
		for($i = 0; $i < mb_strlen($str); $i++){
			$temp = mb_strpos($this ->katakana_list, mb_substr($str, $i, 1)) + 1;
			if($temp < 10){
				$output .= '0';
			}
			$output .= (string)$temp;
		}

		return  str_pad($output, $padding_len, '0', STR_PAD_RIGHT);
	}

	/**
	 *
	 * @param array $names
	 * @param bool $family_name_first
	 * @return array
	 */
	public function SortNamesJA($names, $family_name_first = true){
		if(count($names) < 2){
			return $names;
		}

		$sorter = array();
		$sort_temp = array();
		$longest_first = 0;
		$longest_last = 0;

		//paddingの長さを調査
		foreach($names as $name){
			//不明な文字を除去
			$name['first'] = $this ->CancelUnknownChar($name['first']);
			$name['last'] = $this ->CancelUnknownChar($name['last']);

			//一番長い名字の文字数
			if(mb_strlen($name['first']) > $longest_first){
				$longest_first = mb_strlen($name['first']);
			}
			//一番長い名前の文字数
			if(mb_strlen($name['last']) > $longest_last){
				$longest_last = mb_strlen($name['last']);
			}
		}

		$padding_first_len = $longest_first * 2;
		$padding_last_len = $longest_last * 2;

		foreach($names as $key => $name){
			$temp = array();

			//ハイフンキャンセレータ
			$name['first'] = $this ->TranslateHyphenToJA($name['first']);
			$name['last'] = $this ->TranslateHyphenToJA($name['last']);

			//名前用固有比較数値生成
			$temp['first']['nigori'] = $this ->ConvertToCompareingNumerics($name['first'], $padding_first_len);
			$temp['first']['clear'] = $this ->ConvertToCompareingNumerics($this ->nikorilizer($name['first']), $padding_first_len);

			//名字用固有比較数値生成
			$temp['last']['nigori'] = $this ->ConvertToCompareingNumerics($name['last'], $padding_first_len);
			$temp['last']['clear'] = $this ->ConvertToCompareingNumerics($this ->nikorilizer($name['last']), $padding_first_len);

			//誕生日を指定
			$temp['birthdate'] = date('Ymd', strtotime($name['birthdate']));

			//ソート対象に追加
			$sorter[5]['KEY_'.(string)$key] = $temp['last']['clear'];
			$sorter[4]['KEY_'.(string)$key] = $temp['last']['nigori'];
			$sorter[3]['KEY_'.(string)$key] = $temp['first']['clear'];
			$sorter[2]['KEY_'.(string)$key] = $temp['first']['nigori'];
			$sorter[1]['KEY_'.(string)$key] = $temp['birthdate'];
			$sorter[0]['KEY_'.(string)$key] = $name['id'];
		}

		//ソート（要因がなくなるまで繰り返す）
		$start_from = 5;
		$sort_result = $this ->ArraySorter($sorter[$start_from], $start_from - 1, $sorter);
		
		//出力配列を生成
		$ret = array();
		foreach($sort_result as $id){
			$temp = substr($id, 4);

			//通常配列か連想配列かで代入方法を変更
			if(is_numeric($temp)){
				$ret[] = $names[$temp];
			}
			else{
				$ret[$temp] = $names[$temp];
			}
		}

		return $ret;
	}

	public function ArraySorter($sorter, $now, $sortee){
		//ソート要因がなくなったらやめる
		if($now < 0){
			return array_keys($sorter);
		}

		//長い場合を考慮し文字列
		asort($sorter, SORT_STRING);

		//出力を初期化
		$order = array();

		//一時変数を初期化
		$val_before = null;
		$same = array();

		foreach($sorter as $key => $val){
			//初回は蓄える
			if($val_before == null){
				$val_before = $val;
				$same[] = $key;
				continue;
			}
			//前の値と一致する場合は記録
			if($val_before == $val){
				$same[] = $key;
			}
			//前の値と一致しない場合
			else{
				//前に一個以上一致した場合はその中でソート
				if(count($same) > 1){
					//再ソート配列生成
					$temp_sorter = array();
					foreach($same as $same_key){
						$temp_sorter[$same_key] = $sortee[$now][$same_key];
					}
					//ソートする（次の要因に進める）
					$temp = $this ->ArraySorter($temp_sorter, $now - 1, $sortee);
					//結果を出力の最後に加える
					$order = array_merge($order, $temp);
				}
				//一個しかない場合
				else{
					//出力の最後に追加
					array_push($order, $same[0]);
				}
				//一致した配列を初期化
				$same = array($key);
			}
			//前の値を更新
			$val_before = $val;
		}

		//前に一個以上一致した場合はその中でソート
		if(count($same) > 1){
			//再ソート配列生成
			$temp_sorter = array();
			foreach($same as $same_key){
				$temp_sorter[$same_key] = $sortee[$now][$same_key];
			}
			//ソートする（次の要因に進める）
			$temp = $this ->ArraySorter($temp_sorter, $now - 1, $sortee);
			//結果を出力の最後に加える
			$order = array_merge($order, $temp);
		}
		//一個しかない場合
		else{
			//出力の最後に追加
			array_push($order, $same[0]);
		}

		return $order;
	}

}
