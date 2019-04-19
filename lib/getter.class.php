<?php
include_once("../../../lib/validate.class.php");
/**
 *
 */
class Getter
{
  private $zipcode;

  public function setzipcode($zipcode)
  {
    $this->zipcode = $zipcode;
  }

  public static function address($zipcode = "")
  {
    $ret = [];

    $zipcode = empty($zipcode) ? $this->zipcode : $zipcode;

    if( Validator::zip($zipcode) ){
      // ZipCloudのAPI用のアドレス文字列を生成
      $url = "http://zipcloud.ibsnet.co.jp/api/search?zipcode={$zipcode}";

      // テキストデータを読み込む (HTTP通信)
      $ret = file_get_contents($url);

      // 文字化けしないようにUTF-8に変換
      $ret = mb_convert_encoding($ret, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

    }

    return $ret;

  }
}
