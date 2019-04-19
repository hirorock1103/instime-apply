<?php

/**
 * $Arr->get(取得したい配列)
 * private $column = '{ "1" : "val1", "2" : "val2", "3" : "val3", "4" : "val4", "5" : "val5", "99" : "その他"}';
 */
class ArrDefine
{
  private $exist = [];
  // ここにjson配列を追加していく
  private $generation = '{ "1" : "10代", "2" : "20代", "3" : "30代", "4" : "40代", "5" : "50代", "6" : "60代以上"}';
  private $gender = '{ "1" : "男", "2" : "女"}';
  private $agreement = '{ "1" : "同意する"}';
  private $customer_type = '{ "1" : "個人", "2" : "法人", "3" : "店舗"}';
  private $categories = '{ "1" : "イベントソース", "2" : "ウェブサイト、ブログ", "3" : "スポーツ", "4" : "テレビ", "5" : "ブランド・製品", "6" : "ローカルビジネス", "7" : "映画", "8" : "音楽", "9" : "企業・団体", "10" : "人物", "11" : "本＆雑誌", "99" : "その他"}';

  function __construct()
  {
    foreach ( $this->getVars() as $var_name => $value ) {
      $this->exist[$var_name] = "";
    }
  }

  public function getVars()
  {
    return get_class_vars(__CLASS__);
  }

  public function getJson($name)
  {
    return isset($this->exist[$name]) ? $this->$name : "";
  }

  public function get($name)
  {
    return isset($this->exist[$name]) ? $this->get_defined_array($this->$name) : "";
  }

  // 定数定義 JSON項目対応
  function get_defined_array($json_str){
      return json_decode($json_str, true);
  }

  function get_defined_name($json_str, $key){
      $defined_array = $this->get_defined_array($json_str);
      if(isset($key) && array_key_exists($key, $defined_array)){
          return $defined_array[$key];
      }
      return "";
  }

}

$Arr = new ArrDefine();