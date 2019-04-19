<?php
/**
 *
 */
class ApplyForm extends TableFormPartsMaker
{
  protected $form_item_conditions_json = '[
    { "name" : "customer_type", "label" : "法人・個人区分" , "type" : "radio", "required" : "1"}
    ,{ "name" : "corp_name", "label" : "法人名(店舗名)" , "type" : "text"}
    ,{ "name" : "last_name", "label" : "申込者氏名（姓）" , "type" : "name", "required" : "1"}
    ,{ "name" : "first_name", "label" : "申込者氏名（名）" , "type" : "name", "required" : "1"}
    ,{ "name" : "mailaddress", "label" : "メールアドレス" , "type" : "email", "required" : "1"}
    ,{ "name" : "mobile_phone", "label" : "携帯番号" , "type" : "tel", "required" : "1"}
    ,{ "name" : "post_number", "label" : "郵便番号" , "type" : "zip", "required" : "1"}
    ,{ "name" : "pref", "label" : "都道府県" , "type" : "pref", "required" : "1"}
    ,{ "name" : "city", "label" : "市区町村" , "type" : "text", "required" : "1"}
    ,{ "name" : "banchi", "label" : "番地等" , "type" : "text", "required" : "1"}
    ,{ "name" : "building", "label" : "建物名・部屋番号" , "type" : "text"}
    ,{ "name" : "instagram_id", "label" : "インスタID" , "type" : "text", "required" : "1"}
    ,{ "name" : "instagram_pw", "label" : "インスタPW" , "type" : "password", "required" : "1"}
    ,{ "name" : "gender", "label" : "性別" , "type" : "radio"}
    ,{ "name" : "generation", "label" : "世代" , "type" : "checkbox"}
    ,{ "name" : "area", "label" : "エリア" , "type" : "text"}
    ,{ "name" : "hashtag", "label" : "ハッシュタグ" , "type" : "text"}
    ,{ "name" : "categories", "label" : "カテゴリー" , "type" : "select"}
    ,{ "name" : "biko", "label" : "備考" , "type" : "textarea"}
    ,{ "name" : "agreement", "label" : "利用規約同意" , "type" : "agreement", "required" : "1"}
  ]';
  protected $form_item_conditions;
  protected $Arr;
  protected $values;
  protected $mode;

  function __construct($values, $mode = "input")
  {
    Parent::__construct($values, $mode);
  }
}

/**
 *
 */
abstract class TableFormPartsMaker
{
  protected $form_item_conditions_json;
  protected $form_item_conditions;
  protected $Arr;
  protected $values;
  protected $mode;

  function __construct($values, $mode = "input")
  {
    $this->Arr = new ArrDefine();
    $this->form_item_conditions = $this->Arr->get_defined_array($this->form_item_conditions_json);
    $this->values = $values;
    $this->mode = $mode;
  }

  public function setmode($mode)
  {
    $this->mode = $mode;
  }

  public function getFormContext()
  {
    $ret = "";
    $func = $this->mode == "hidden" ? "get_hidden_tag" : "getParts";
    foreach ($this->form_item_conditions as $key => $item) {
      $ret .= $this->$func($item);
    }

    return $ret;
  }

  public function getItemConditions()
  {
    return $this->form_item_conditions;
  }

  public function getParts($item = "")
  {
    $ret = "";

    if (!empty($item)) {
      switch ($this->mode) {
        case 'input':
          $input_tag = $this->get_input_tag($item);
          break;
        case 'confirm':
          $input_tag = $this->get_confirm_tag($item);
          break;
        default:
          break;
      }
      $label_auth = [
        "text"
        ,"name"
        ,"email"
        ,"pref"
        ,"password"
        ,"select"
        ,"textarea"
      ];
      $for = in_array($item["type"], $label_auth) ? "for=\"{$item['name']}\"" : "";
      $required = isset($item["required"]) ? "<span>必須</span>" : "";
      $ret = <<< HTML
      <tr>
        <th><label {$for}>{$item['label']}{$required}</label></th>
        <td>{$input_tag}</td>
      </tr>
HTML;
    }

    return $ret;
  }

  private function get_confirm_tag($item)
  {
    $ret = "";
    if(is_array($this->values[$item["name"]])){
      foreach ($this->values[$item["name"]] as $key => $value) {
        $ret .= isset($this->Arr->get($item["name"])[$this->h($value)]) ? $this->Arr->get($item["name"])[$this->h($value)]." " : $this->h($value);
      }
    }else {
      $value = $this->values[$item["name"]];
      $ret = isset($this->Arr->get($item["name"])[$this->h($value)]) ? $this->Arr->get($item["name"])[$this->h($value)] : $this->h($value);
      $ret = $item["type"] == "password" ? str_pad("", strlen($this->h($value)), "*") : $ret;
    }
    return $ret;
  }

  private function get_hidden_tag($item)
  {
    $ret = "";
    if(is_array($this->values[$item["name"]])){
      foreach ($this->values[$item["name"]] as $key => $value) {
        $value = $this->h($value);
        $ret .= "<input type=\"hidden\" name=\"{$item['name']}[]\" value=\"{$value}\">";
      }
    }else {
      $value = $this->h($this->values[$item["name"]]);
      $ret = "<input type=\"hidden\" name=\"{$item['name']}\" value=\"{$value}\">";
    }
    return $ret;
  }

  private function get_input_tag($item)
  {
    $ret = "";
    $id = "id=\"{$item['name']}\"";
    $name = "name=\"{$item['name']}\"";
    $class = "class=\"\"";
    $label_class = "class=\"\"";
    switch ($item["type"]) {

      case 'text':
        $value = $this->h($this->values[$item["name"]]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control\"";
        $ret = "<input type='text' {$id} {$name} {$class} {$placeholder} value='{$value}'>";
      break;

      case 'tel':
        $value1 = $this->h($this->values[$item["name"]][1]);
        $value2 = $this->h($this->values[$item["name"]][2]);
        $value3 = $this->h($this->values[$item["name"]][3]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control width60\"";
        $ret = "<input type='text' {$id} name='{$item["name"]}[1]' {$class} {$placeholder} value='{$value1}'>";
        $ret .= "<span style='padding:0 5px;display:inline'>-</span>";
        $ret .= "<input type='text' style=\"float:unset\" name='{$item["name"]}[2]' {$class} {$placeholder} value='{$value2}'>";
        $ret .= "<span style='padding:0 5px;display:inline'>-</span>";
        $ret .= "<input type='text' style=\"float:unset\" name='{$item["name"]}[3]' {$class} {$placeholder} value='{$value3}'>";
      break;

      case 'pref':
        $value = $this->h($this->values[$item["name"]]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control width200\"";
        $ret = "<input type='text' {$id} {$name} {$class} {$placeholder} value='{$value}'>";
      break;

      case 'zip':
        $value1 = $this->h($this->values[$item["name"]][1]);
        $value2 = $this->h($this->values[$item["name"]][2]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control width50\"";
        $ret = "<input type='text' {$id} name='{$item["name"]}[1]' {$class} {$placeholder} value='{$value1}'>";
        $class = "class=\"form-control width60\"";
        $ret .= "<span style='padding:0 5px;display:inline'>-</span>";
        $ret .= "<input type='text' style=\"float:unset\" name='{$item["name"]}[2]' {$class} {$placeholder} value='{$value2}'>";
        $ret .= "<a id='getaddress'>住所自動入力</a><span class=\"loading\"></span>";
        $ret .= "<span class=\"zip_code_error_multi\" style=\"color: red;\"></span>";
        $ret .= "<select class=\"select_murti_address\" style=\"display:none;\"></select>";
      break;

      case 'name':
        $value = $this->h($this->values[$item["name"]]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control width200 mr20 mb10s\"";
        $ret = "<input type='text' {$id} {$name} {$class} {$placeholder} value='{$value}'>";
      break;

      case 'email':
        $value = $this->h($this->values[$item["name"]]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control\"";
        $ret = "<input type='email' {$id} {$name} {$class} {$placeholder} value='{$value}'>";
      break;

      case 'checkbox':
        foreach ($this->Arr->get($item["name"]) as $key => $value) {
          $id = "id=\"{$item['name']}_{$key}\"";
          $name = "name=\"{$item['name']}[$key]\"";
          $checked = isset($this->values[$item["name"]][$key]) ? "checked='checked'" : "";
          $placeholder = "placeholder=''";
          $ret .= "<label {$label_class}><input type='checkbox' {$id} {$name} {$class} {$placeholder} value='{$key}' {$checked}>{$value}</label>";
        }
      break;

      case 'agreement':
        foreach ($this->Arr->get($item["name"]) as $key => $value) {
          $id = "id=\"{$item['name']}_{$key}\"";
          $name = "name=\"{$item['name']}[$key]\"";
          $checked = isset($this->values[$item["name"]][$key]) ? "checked='checked'" : "";
          $placeholder = "placeholder=''";
          $ret .= "<label {$label_class}><input type='checkbox' {$id} {$name} {$class} {$placeholder} value='{$key}' {$checked}>{$value}</label>";
        }
        $policy_url = isset($item["policy_url"]) ? $item["policy_url"] : "#";
        $ret .= "<a href=\"{$policy_url}\" class=\"policy_url\" target=\"_blank\">利用規約はこちら</a>";
      break;

      case 'radio':
        $ret = "";
        foreach ($this->Arr->get($item["name"]) as $key => $value) {
          $checked = ($key == $this->values[$item["name"]]) ? "checked" : "";
          $id = "id=\"{$item['name']}_{$key}\"";
          $ret .= "<label {$label_class}><input type=\"radio\" {$id} {$name} {$class} value=\"{$key}\" {$checked}>{$value}</label>";
        }
      break;

      case 'select':
        $class = "class=\"form-control\"";
        $ret = "<select {$name} {$id} {$class}><option value=\"0\">--</option>";
        foreach ($this->Arr->get($item["name"]) as $key => $value) {
          $selected = ($key == $this->values[$item["name"]]) ? "selected" : "";
          $ret .= "<option value =\"{$key}\" {$selected}>{$value}</option>";
        }
        $ret .= "</select>";
      break;

      case 'textarea':
        $value = $this->h($this->values[$item["name"]]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control\"";
        $ret = "<textarea {$id} {$name} {$class} {$placeholder} rows=\"3\">{$value}</textarea>";
      break;

      case 'password':
        $value = $this->h($this->values[$item["name"]]);
        $retype_value = $this->h($this->values["retype_".$item["name"]]);
        $placeholder = "placeholder=''";
        $class = "class=\"form-control\"";
        $ret = "<input type='password' {$id} {$name} {$class} {$placeholder} value='{$value}'>";
        $placeholder = "placeholder='パスワード再入力'";
        $ret .= "<input type='password' name=\"retype_{$item['name']}\" {$class} {$placeholder} value='{$retype_value}'>";
      break;

      default:break;
    }

    return $ret;
  }

  public function h($string){
    return htmlspecialchars($string);
  }

}
