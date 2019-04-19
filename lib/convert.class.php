<?php
/**
 * Converter::関数名　で呼び出し
 * ＜実装コンバータ＞
 // 日付フォーマット
* Converter::my_date_months_years($date_string = null);
 // 日付フォーマット
* Converter::my_date_format($date_string, $time = false);
 // 日付フォーマット
* Converter::my_date_format_by_int($date_int, $time = false);
 // 郵便番号フォーマット
 * Converter::zip_code_format($zip_code);
 // 定数定義 JSON項目対応
* Converter::get_defined_array($json_str);
* Converter::get_defined_name($json_str, $key);
 // ハイフン削除
* Converter::remove_hyphen($str);
 // 全角ハイフン変換
* Converter::replace_zenkaku_hyphen($str);
 // 半角ハイフン変換
* Converter::replace_hankaku_hyphen($str);
 // 半角変換
* Converter::replace_hankaku($str);
 // 全角カタカナ変換(ひらがな -> カタカナ)
* Converter::replace_zenkaku_katakana($str);
 // 全角ひらがな変換(カタカナ -> ひらがな)
* Converter::replace_zenkaku_hiragana($str);
 // コード作成
* Converter::create_code($id, $suffix = "", $prefix = "", $digit = 8);
 // 省略表示
* Converter::omit_str($str, $length = 0);
 // 全角空白対応trim
* Converter::trim_all($str);
* Converter::trim_into_all($str);
 //年齢算出
 * I:生年月日 例)2017/11/28
 * O:年齢
* Converter::getAge($birthday);
 //年齢算出
 * I:生年月日 例)2017/11/28(2017-11-28) $end_date - $start_date
 * O:年齢
* Converter::calAge($start_date, $end_date);
// 暗号化
* Converter::encrypt($data);
// 復号化
* Converter::decrypt($encrypted);
// URL base64 encode ＊用途がURLではない場合はbase64_encode()を使うこと。
* Converter::base64url_encode($data);
// URL base64 decode ＊用途がURLではない場合はbase64_decode()を使うこと。
* Converter::base64url_decode($data);
// マスク(シール) 任意の文字列　→ 文字数分の*に変換
* Converter::get_sealed_str($str, $length = 0);
 */
class Converter
{
  //
  // 暗号関連
  //
  const OPENSSL_METHOD = 'AES-256-CBC';
  const OPENSSL_ENCRYPT_KEY = '8tx7turh9pwszj5gx4lh23SVhvyejit7'; // 32バイト
  const OPENSSL_ENCRYPT_IV = 'ytsxs3Qw6ad7qWmu'; //16バイト

  function __construct()
  {
    // code...
  }

  // 日付フォーマット
  public static function my_date_months_years($date_string = null){
      if($date_string == null) return "";

      return date('Y年m月', strtotime($date_string));
  }

  // 日付フォーマット
  public static function my_date_format($date_string, $time = false){
      if(isset($date_string) && trim($date_string) != "" && !(strpos($date_string, "0000") !== false)){
          return date($time ? 'Y/m/d H:i:s' : 'Y/m/d', strtotime($date_string));
      }
      return "";
  }

  // 日付フォーマット
  public static function my_date_format_by_int($date_int, $time = false){
      if(!empty($date_int) && preg_match('/^[0-9]+$/', $date_int)){
          $yyyymmdd = substr($date_int, 0, 4)."/".substr($date_int, 4, 2)."/".substr($date_int, 6, 2);
          $hhiiss = "";
          if($time){
              $hhiiss = " ".substr($date_int, 8, 2).":".substr($date_int, 10, 2).":".substr($date_int, 12, 2);
          }
          return $yyyymmdd.$hhiiss;
      }
      return "";
  }

  // 郵便番号フォーマット
  public static function zip_code_format($zip_code){
      return (!empty($zip_code) && preg_match('/^[0-9]+$/', $zip_code) && strlen($zip_code) == 7 ? substr($zip_code, 0, 3)."-".substr($zip_code, 3) : "");
  }

  // 定数定義 JSON項目対応
  public static function get_defined_array($json_str){
      return json_decode($json_str, true);
  }
  public static function get_defined_name($json_str, $key){
      $defined_array = get_defined_array($json_str);
      if(isset($key) && array_key_exists($key, $defined_array)){
          return $defined_array[$key];
      }
      return "";
  }

  // ハイフン削除
  public static function remove_hyphen($str){
      $defined_hyphen = get_defined_array(HYPHENS);
      foreach($defined_hyphen as $hyphen){
          $str = mb_ereg_replace($hyphen, '', $str);
      }
      return $str;
  }

  // 全角ハイフン変換
  public static function replace_zenkaku_hyphen($str){
      $defined_hyphen = get_defined_array(HYPHENS);
      foreach($defined_hyphen as $hyphen){
          $str = mb_ereg_replace($hyphen, '－', $str);
      }
      return $str;
  }

  // 半角ハイフン変換
  public static function replace_hankaku_hyphen($str){
      $defined_hyphen = get_defined_array(HYPHENS);
      foreach($defined_hyphen as $hyphen){
          $str = mb_ereg_replace($hyphen, '-', $str);
      }
      return $str;
  }

  // 全角変換
  public static function replace_zenkaku($str){
      $str = mb_convert_kana($str, 'KVAS', 'UTF-8');
      $str = replace_zenkaku_hyphen($str);
      return str_replace("'","’",$str);
  }

  // 半角変換
  public static function replace_hankaku($str){
      $str = mb_convert_kana($str, 'kvas', 'UTF-8');
      return replace_hankaku_hyphen($str);
  }

  // 全角カタカナ変換(ひらがな -> カタカナ)
  public static function replace_zenkaku_katakana($str){
      $str = mb_convert_kana($str, 'KVCAS', 'UTF-8');
      return replace_zenkaku_hyphen($str);
  }

  // 全角ひらがな変換(カタカナ -> ひらがな)
  public static function replace_zenkaku_hiragana($str){
      $str = mb_convert_kana($str, 'KVHcAS', 'UTF-8');
      return replace_zenkaku_hyphen($str);
  }

  // コード作成
  public static function create_code($id, $suffix = "", $prefix = "", $digit = 8){
      if(intval($digit) > 0){
          return $suffix.sprintf('%0'.intval($digit).'d', $id).$prefix;
      }else{
          return $suffix.$id.$prefix;
      }
  }

  // 省略表示
  public static function omit_str($str, $length = 0) {
      return ($length > 0 && mb_strlen($str) > $length ? mb_substr($str, 0, $length)."..." : $str);
  }

  // 全角空白対応trim
  public static function trim_all($str) {
      $str = trim($str);
      $str = preg_replace('/^[ 　]+/u', '', $str);
      $str = preg_replace('/[ 　]+$/u', '', $str);
      return trim($str);
  }
  public static function trim_into_all($str) {
      return preg_replace('/[ 　]/u', '', $str);
  }

  //年齢算出
  //I:生年月日 例)2017/11/28
  //O:年齢
  public static function getAge($birthday){
      $birthday = str_replace("/", "", $birthday);
      $age = floor( (date("Ymd")-$birthday)/10000 );
      return $age;
  }

  //年齢算出
  //I:生年月日 例)2017/11/28(2017-11-28) $end_date - $start_date
  //O:年齢
  public static function calAge($start_date, $end_date){
      $start_date = date("Ymd", strtotime($start_date));
      $end_date = date("Ymd", strtotime($end_date));
      $age = floor(($end_date - $start_date) / 10000);
      return $age;
  }

  // 暗号化
  public static function encrypt($data){
      return base64_encode(openssl_encrypt($data, self::OPENSSL_METHOD, self::OPENSSL_ENCRYPT_KEY, true, self::OPENSSL_ENCRYPT_IV));
  }

  // 復号化
  public static function decrypt($encrypted){
      return openssl_decrypt(base64_decode($encrypted), self::OPENSSL_METHOD, self::OPENSSL_ENCRYPT_KEY, true, self::OPENSSL_ENCRYPT_IV);
  }

  // パスワード用暗号化
  public static function pw_encrypt($value){
      return password_hash($value , PASSWORD_DEFAULT, array('cost' => 12));
  }

  // URL base64 encode ＊用途がURLではない場合はbase64_encode()を使うこと。
  public static function base64url_encode($data) {
    return rtrim(str_replace(array('+', '/'), array('-', '_'), base64_encode($data)), '=');
  }

  // URL base64 decode ＊用途がURLではない場合はbase64_decode()を使うこと。
  public static function base64url_decode($data) {
    return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
  }

  // マスク(シール)
  public static function get_sealed_str($str, $length = 0){
      $result = "";
      if(!empty($str)){
          $result = str_repeat("*", mb_strlen($str));
          if($length > 0 && mb_strlen($str) > $length){
              $result = mb_substr($result, 0, mb_strlen($str) - $length).mb_substr($str, -$length);
          }
      }
      return $result;
  }

}
