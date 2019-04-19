<?php
/**　実装例
     $valid = new Validator($datas);

     $valid->rule('first_name', '担当者名', ['required']);
     $valid->rule('last_name', '担当者名', ['required']);
     $valid->rule('mailaddress', 'メールアドレス', ['required','email']);
     $valid->rule('biko', 'お問い合わせ内容', ['required']);

     try {
       $valid->validate();
       $error = $valid->getErrmsg();
     } catch (Exception $e) {
       $error[] = "エラーチェックに失敗(".$e->getMessage().")";
     }
 * 実装チェック項目
 * 必須チェック required
 * 日付妥当チェック date
 * メールアドレス妥当チェック email
 * ひらがなのみ許可チェック hiragana
 * 全角カタカナのみ許可チェック katakana
 * 全角カタカナと()のみ許可チェック katakana_kakko
 * 半角カタカナのみ許可チェック han_katakana
 * アップロードファイルチェック upload_file
 *　郵便番号の形式チェック zip Validator::zip
 * 半角数字チェック number
 *
 */
class Validator {

	private $req;
	private $order = [];
	private $my_methods_tmp = [];
	private $my_methods = [];
	private $err_msg = [];
	private $columns = [];
	private $valid_ext_list = [
		"exe"
		,"php"
	];//アップロード禁止ファイル拡張子をここで定義

	function __construct($req)
	{
		$this->req = $req;
		$this->my_methods_tmp = get_class_methods('Validator');
		for ($i=0; $i < count($this->my_methods_tmp); $i++) {
			$this->my_methods[$this->my_methods_tmp[$i]] = "";
		}
	}

	// バリデートエラー取得
	public function getErrmsg()
	{
		return $this->err_msg;
	}

	// バリデートチェック項目のセット
	public function rule($column, $column_wamei, $valids)
	{
		$this->columns[$column] = !empty($column_wamei) ? $column_wamei : $column;
		if(is_array($valids)){
			foreach ($valids as $valid) {
				$this->order[$column][] = $valid;
			}
		}else {
			$this->order[$column][] = $valids;
		}
	}

	// バリデートチェック実行
	public function validate()
	{
		// バリデートチェック項目のセットされているか
		if( !empty($this->order) ){

			// カラムごとにチェック
			foreach ($this->order as $column => $valids) {

				foreach ($valids as $valid) {

					// セットされたチェック項目が実装されているか
					if( isset($this->my_methods[$valid]) ){

						// 実装されているチェックの実施
						// チェック対象項目の値が配列の場合
						if( is_array($this->req[$column]) ){
							foreach ($this->req[$column] as $key => $value) {
								if( !$this->$valid($value, $column) ){
									// チェック結果がエラーの場合、処理ぬける
									break 2;
								}
							}
						// チェック対象項目の値が配列以外の場合
						}else if( !$this->$valid($this->req[$column], $column) ){
							// チェック結果がエラーの場合、処理ぬける
							break;
						}

					}else{
						// セットされたチェック項目が実装されていない場合、例外処理
						throw new Exception("{$valid} は実装されていないチェック項目です。");
					}

				}

			}

		}
	}

	// 必須チェック
	public function required($value, $column)
	{
		$ret = false;

		if(empty($value)){
        $this->err_msg[] = "{$this->columns[$column]}は必須です。";
    }else{
			$ret = true;
		}

		return $ret;
	}

	// 日付妥当チェック
	function date($date, $column){
		try{
			if(
				date("Y-m-d", strtotime($date)) == $date || date("Y/m/d", strtotime($date)) == $date || date("Ymd", strtotime($date)) == $date ||
				date("Y-n-d", strtotime($date)) == $date || date("Y/n/d", strtotime($date)) == $date || date("Ynd", strtotime($date)) == $date ||
				date("Y-m-j", strtotime($date)) == $date || date("Y/m/j", strtotime($date)) == $date || date("Ymj", strtotime($date)) == $date ||
				date("Y-n-j", strtotime($date)) == $date || date("Y/n/j", strtotime($date)) == $date || date("Ynj", strtotime($date)) == $date
			){
				return true;
			}
		} catch (Exception $ex) {
			$this->err_msg[] = "{$this->columns[$column]}が正しい日付ではありません。";
			return false;
		}
		$this->err_msg[] = "{$this->columns[$column]}が正しい日付ではありません。";
		return false;
	}

	public static function zip($value, $column = "")
	{
		$ret = false;
		if(
			preg_match('/^[0-9]{3}-[0-9]{4}$/', $value)
			|| preg_match('/^[0-9]{7}$/', $value)
		){
			$ret = true;
		}

		if( !$ret && !empty($column)){
			$this->err_msg[] = "{$this->columns[$column]}は正しい郵便番号の形式ではありません。";
		}

		return $ret;
	}

	// メールアドレス妥当チェック
	function email($email, $column){
		if (
			$this->checkString($email)
			&& preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $email)
		){
				return true;
		} else {
				$this->err_msg[] = "{$this->columns[$column]}が正しい形式ではありません。";
				return false;
		}

	}

	// ひらがなのみ許可チェック
	function hiragana($str, $column, $space = false){
		$ret = false;

		if($space){
			if(preg_match('/^[ぁ-ゟ　 ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}はひらがなとスペースのみ入力してください。";
			}
		}else{
			if(preg_match('/^[ぁ-ゟ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}はひらがなのみ入力してください。";
			}
		}

		return $ret;
	}

	// 全角カタカナのみ許可チェック
	function katakana($str, $column, $space = false){
		$ret = false;

		if($space){
			if(preg_match('/^[ァ-ヿ　 ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は全角カタカナとスペースのみ入力してください。";
			}
		}else{
			if(preg_match('/^[ァ-ヿ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は全角カタカナのみ入力してください。";
			}
		}

		return $ret;
	}

	// 全角カタカナと()のみ許可チェック 20171004前川追加
	function katakana_kakko($str, $column, $space = false){
		$ret = false;

		if($space){
			if(preg_match('/^[ァ-ヿ()（）.．　 ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は全角カタカナと()とスペースのみ入力してください。";
			}
		}else{
			if(preg_match('/^[ァ-ヿ()（）.．]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は全角カタカナと()を入力してください。";
			}
		}

		return $ret;
	}

	// 半角カタカナのみ許可チェック
	function han_katakana($str, $column, $space = false){
		$ret = false;

		if($space){
			if(preg_match('/^[ｦ-ﾟ　 ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は半角カタカナととスペースのみ入力してください。";
			}
		}else{
			if(preg_match('/^[ｦ-ﾟ]+$/u', $str)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は半角カタカナのみを入力してください。";
			}
		}

		return $ret;
	}

	// アップロードファイルチェック
	function upload_file($file, $column){
		$result = array();

		if (
			!isset($file['error']) ||
			!is_int($file['error'])
		) {
			$this->err_msg[] = "{$this->columns[$column]}で不正なアップロードが実施されました。";
		}

		switch ($file['error']) {
			case UPLOAD_ERR_OK: // OK
			case UPLOAD_ERR_NO_FILE:   // ファイル未選択
			break;
			// if($required){
			// 	$this->err_msg[] = "{$this->columns[$column]}のファイルが選択されていません。");
			// }else{
			// 	return $result;
			// }
			case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
			case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
			$this->err_msg[] = "{$this->columns[$column]}のファイルサイズが大きすぎます。";
			default:
			$this->err_msg[] = "{$this->columns[$column]}のファイルアップロード中にエラーが発生しました。";
		}

		if (!empty($file['name'])) {
			$file_name = $file['name'];
			$pos = strrpos($file_name, '.');
			if($pos !== false){
				$ext = substr($file_name, $pos + 1);
				if(!in_array($ext,  $this->valid_ext_list, true)){
					$this->err_msg[] = "{$this->columns[$column]}で選択されたファイルはアップロードできないファイルです。";
				}else{
					$result["ext"] = $ext;
				}
			} else {
				$this->err_msg[] = "{$this->columns[$column]}で選択されたファイルはアップロードできないファイルです。";
			}
		} else {
			$this->err_msg[] = "{$this->columns[$column]}で選択されたファイルはアップロードできないファイルです。";
		}

		return $result;
	}

	/**
	   * 文字列型チェック<br>
	   * 文字列として扱えるかどうかのチェック<br>
	   * 数字もOKとするが、arrayやクラスはNGとする
	   *
	   * @param string $arg チェックする値
	   * @return bool 文字列の場合true、そうでなければfalse
	   */
	  public static function checkString($arg, $column = "")
	  {
	      if (is_string($arg) || is_numeric($arg)) {
	          return true;
	      } else {
					if( !empty($column) ){
						$this->err_msg[] = "{$this->columns[$column]}は文字列として扱うことはできません。";
					}
	        return false;
	      }
	  }

		//携帯電話を判別
		function is_mobile($tel, $column){

		    if(preg_match('/(050|070|080|090)-\d{4}-\d{4}/', $tel)){
		        return true;
		    }else{
					$this->err_msg[] = "{$this->columns[$column]}は携帯電話番号の形式ではありません。";
				}

		    return false;
		}

		// 半角数字チェック
		function number($value, $column){
			$ret = false;

			if(preg_match('/^[0-9]+$/u', $value)){
				$ret = true;
			}else{
				$this->err_msg[] = "{$this->columns[$column]}は半角数字のみ入力してください。";
			}

			return $ret;
		}

		// 再入力との一致チェック
		public function retype($value, $column)
		{
			$ret = false;

			if( isset($this->req["retype_{$column}"])	){
				if($this->req[$column] == $this->req["retype_{$column}"]){
					$ret = true;
				}else {
					$this->err_msg[] = "{$this->columns[$column]}と再入力値が一致しません。";
				}
			}else {
				// セットされたチェック項目が実装されていない場合、例外処理
				throw new Exception("{$this->columns[$column]} は再入力フォームが存在しません。");
			}

			return $ret;
		}


}

