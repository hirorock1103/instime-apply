$(function() {

  $("#getaddress").click(function(){
      // 住所自動入力'ボタン押下時
      // 住所要素取得
      var parent = $(this).parents("table");
      var multi = parent.find(".select_murti_address");
      var multi_err = parent.find(".zip_code_error_multi");
      var pref = parent.find("input[name*='pref']");
      var city = parent.find("input[name*='city']");
      var zip1 = parent.find("input[name='post_number[1]']");
      var zip2 = parent.find("input[name='post_number[2]']");

      // 要素初期化
      multi.find("option").remove();
      multi.css('display', 'none');
      pref.val('');
      city.val('');
      multi_err.html('');

      // 郵便番号の半角変換
      zip1.val(to_hankaku(zip1.val()));
      zip2.val(to_hankaku(zip2.val()));
      var zip_code = zip1.val() + zip2.val();

      // 郵便番号空白の場合、処理なし
      if( zip_code == "" ){return;}

      var loading = parent.find(".loading");
      loading.html("<img src='img/loading.gif'/>");

      res = {};

      $.ajax({
          url: 'ajax/search_addresses.php',
          type: 'post',
          dataType: 'json',
          data: {
            "zipcode": zip_code,
          }
      })
      .done(function (res) {
        var address_data = JSON.parse(res);
        if(
          address_data.results != null
          && address_data.status == 200
        ){
          input_addresses(address_data.results);
        }
      }).always(function(){
        loading.empty();
      });

      function input_addresses(addresses){
        input_address(addresses[0]);
        if(addresses.length > 1){
          input_multi_option(addresses);
          $(multi).change(function(){
            input_address(addresses[$(this).val()]);
          });
        }
      }

      function input_address(address){
        pref.val(address.address1);
        city.val(address.address2 + address.address3);
      }

      function input_multi_option(addresses) {
        multi_err.html("住所データが複数存在します。選択してください。");
        multi.css('display','inline');
        $.each(addresses, function(i, val) {
          output_address_text = val.address1 + val.address2 + val.address3;
          option = $('<option>').html(output_address_text).val(i);
          multi.append(option); // セレクトボックスに追加
        });
      }

  });


  function to_hankaku(strVal){
    var halfVal = strVal.replace(/[！-～]/g,
      function( tmpStr ) {
        return String.fromCharCode( tmpStr.charCodeAt(0) - 0xFEE0 );
      }
    );
    return halfVal.replace(/”/g, "\"")
    .replace(/’/g, "'")
    .replace(/‘/g, "`")
    .replace(/￥/g, "\\")
    .replace(/　/g, " ")
    .replace(/〜/g, "~");
  }



});



