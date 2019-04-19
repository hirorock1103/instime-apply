$(function(){

    var areaLinks = {
        "北海道地方" : "#recruit",
        "東北地方" : "#recruit",
        "関東地方" : "#recruit",
        "北陸・甲信越地方" : "#recruit",
        "東海地方" : "#recruit",
        "近畿地方" : "#recruit",
        "中国地方" : "#recruit",
        "四国地方" : "#recruit",
        "九州地方" : "#recruit",
        "沖縄地方" : "#recruit",
    };

    /*
      {"code": 任意の文字列 , "name":"表示したい文字", "color":"エリアの色", "hoverColor":"エリアをhoverした時の色", "prefectures":[対象の都道府県のコード]}
    */
    var areas = [
        {"code": 1 , "name":"北海道地方", "color":"#F38181", "hoverColor":"#fff", "prefectures":[1]},
        {"code": 2 , "name":"東北地方",   "color":"#fbad8b", "hoverColor":"#fff", "prefectures":[2,3,4,5,6,7]},
        {"code": 3 , "name":"関東地方",   "color":"#FCE38A", "hoverColor":"#fff", "prefectures":[8,9,10,11,12,13,14]},
        {"code": 4 , "name":"北陸・甲信越地方",   "color":"#8eeb99", "hoverColor":"#fff", "prefectures":[15,16,17,18,19,20]},
        {"code": 5 , "name":"東海地方",   "color":"#51d76e", "hoverColor":"#fff", "prefectures":[21,22,23,24]},
        {"code": 6 , "name":"近畿地方",   "color":"#6fdcc8", "hoverColor":"#fff", "prefectures":[25,26,27,28,29,30]},
        {"code": 7 , "name":"中国地方",   "color":"#A5DEF1", "hoverColor":"#fff", "prefectures":[31,32,33,34,35]},
        {"code": 8 , "name":"四国地方",   "color":"#70A1D7", "hoverColor":"#fff", "prefectures":[36,37,38,39]},
        {"code": 9 , "name":"九州地方",   "color":"#FF99FE", "hoverColor":"#fff", "prefectures":[40,41,42,43,44,45,46]},
        {"code":10 , "name":"沖縄地方",   "color":"#BA52ED", "hoverColor":"#fff", "prefectures":[47]}
    ];

    $("#map").japanMap(
        {
            areas  : areas,//オブジェクト
            selection : "prefecture",
            borderLineWidth: 0.25,
            drawsBoxLine : false,
            movesIslands : true,
            showsAreaName : true,
            width: 800,//地図の幅（heightも指定できるが、アスペクト比を維持して大きい方が優先される）
            font : "YuGothic",//地図上に表示されるフォントの種類
            fontSize : 12,//フォントサイズ
            fontColor : "#333",//フォントカラー
            fontShadowColor : "white",//テキストシャドウ
            onSelect:function(data){//クリックした時
                //console.log(data);
                var result = confirm(data.name + "の検索一覧画面へ移動します。よろしいですか？");
                //alert(result);
                if(result == true){
                    window.location.href = 'shrine_list.html?pref_code=' + data.code;
                }
                
            },
            
            onHover:function(data){//マウスオーバー時
                $("#text").html(data.area.name + "　" + data.name);
                $("#text").css("background", data.area.color);
            }
        }
    );
});