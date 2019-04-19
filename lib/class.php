<?php


/**
 * Common
 */
class Common{

    public static function getPrefList(){
        return $pref_list = array(
    "北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県","新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県","徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県",
        );

    }

}

/**
 * DB
 */
class DbManager{

    private $pdo;

    public function __construct(){

        $this->pdo = new PDO("sqlite:Jinja.db");
        //tableがない場合
        $sql = $this->getCreateQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }

    public function getDbh(){
        return $this->pdo;
    }

    public function getCreateQuery(){

        return $sql = "CREATE TABLE IF NOT EXISTS JinjaInfo(
            id          INTEGER NOT NULL PRIMARY KEY,
            shrine_name        TEXT    NOT NULL UNIQUE,
            address    TEXT,
            phone TEXT,
            station TEXT,
            homepage TEXT,
            comment1 TEXT,
            comment2 TEXT,
            comment3 TEXT,
            img1 TEXT,
            img2 TEXT,
            img3 TEXT,
            img4 TEXT,
            img5 TEXT,
            zip01 TEXT,
            zip02 TEXT,
            status integer default 0,
            createdate TEXT,
            updatedate TEXT
            );";

    }

}


class DbManager2 extends AutoUpdater {

    private $pdo;
    private $dbh;

    public function __construct(){


        define('DB_HOST', 'localhost');
        define('DB_NAME', 'instime');
        define('DB_USER', 'instime');
        define('DB_PASSWORD', 'ap7SejLSHXsWE5uU');

        // 文字化け対策
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8'");

        // PHPのエラーを表示するように設定
        error_reporting(E_ALL & ~E_NOTICE);

        // データベースの接続
        try {
             $this->pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, $options);
             $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
             echo $e->getMessage();
             exit;
        }

        Parent::__construct($this->pdo);
        $this->dbh = $this->pdo;

    }

    public function getDbh(){
        return $this->pdo;
    }

}



/**
 *  Mailer
 */
class Mailer{

    private $from;
    private $mailto;
    private $subject;
    private $body;

    public function setSubject($subject){
        $this->subject = $subject;
    }

    public function setFrom($from){
        $this->from = $from;
    }

    public function setBody($body){
        $this->body = $body;
    }

    public function setMailTo($mailto){
        $this->mailto = $mailto;
    }

    public function send(&$error){

        $error = "";
        //送信必須情報をチェック
        if(empty($this->mailto) || empty($this->body) || empty($this->subject) || empty($this->from)){
            $error = "必須項目が足りていません。";
            $error .= "mailto:".$this->mailto."<br>";
            $error .= "from:".$this->from."<br>";
            $error .= "subject:".$this->masubjectilto."<br>";
            $error .= "body:".$this->body."<br>";
            return false;
        }

        // 言語と文字エンコーディングを正しくセット
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        // 【予備】宛先情報がある場合はエンコード
        // $to_name = "";
        // $to_addr = $mailto;
        // $to_name_enc = mb_encode_mimeheader($to_name,"ISO-2022-JP");
        // $to = "$to_name_enc<$to_addr>";

        // 送信元情報をエンコード
        $from_name = "弥栄NIPPON";
        $from_addr = $this->from;
        $from_name_enc = mb_encode_mimeheader($from_name, "ISO-2022-JP");
        $from = "$from_name_enc<$from_addr>";
        // メールヘッダを作成
        $header  = "From: $from\n";
        $header .= "Reply-To: $from";

        //【予備】BBCをセットする場合
        // $header .= "Bcc: ".BCC_MAILADDRESS." ";

        $pfrom   = "-f$from";


        // error_reporting(E_ALL|E_STRICT);
        // ini_set('display_errors', 1);

        // 日本語メールの送信
        //$result = mb_send_mail($this->mailto, $this->subject, $this->body, $header, $pfrom);
        $result = mb_send_mail($this->mailto, $this->subject, $this->body, $header);

        if ($result) {
            return true;
        }

        return false;

    }

    public function sendMail($title, $body, $from, $mailto){
        $this->setSubject($title);
        $this->setFrom($from);
        $this->setBody($body);
        $this->setMailTo($mailto);
        $this->send();
    }

}



class ImageManager{

    private $upload_file_max_size = 104857600;//100MB
    private $upload_file_min_width = 1184;//px
    private $allowed_format = array("image/jpeg","image/png","image/jpg","image/gif");


    public function __construct(){


    }

    /**
     * アップロードminWidth
     */
    public function getUploadFileMinWidth(){
        return $this->upload_file_min_width;
    }


    /**
     * 画像回転処理
     * ExifのOrientationの値が「1」以外になっている場合は回転
     */
    public function imageRotate(){
        //https://qiita.com/hiro_y/items/0476bcf39a77ca184009
    }

    /**
     * file アップロード
     */
    public function uploadImageFile($files, $dest_dir, &$error){

        $format         = $files['images']['type'];
        $max_file_size  = $this->upload_file_max_size;

        //画像サイズ
        $img_info       = getimagesize($files['images']['tmp_name']);
        $img_width      = $img_info[0];
        $img_height     = $img_info[1];

        //フィル名
        $file_name = $files['images']['name'];//現在未使用
        $upload_path    = $dest_dir.$file_name;//現在未使用

        $path_parts = pathinfo($files['images']['name']);

        if(isset($path_parts['filename'])){
            $resize_file_name = $path_parts['filename'];//unavailable php version 5.1
        }else{
            $resize_file_name = basename($file_name);
        }

        //$resize_file_name = openssl_encrypt($files['images']['name'],'aes-256-ecb',rand(1,100));
        //$resize_file_name  = chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) .chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90));

        $result = false;

        $error = array();
        $error = $this->fileValidate($file_name);

        if ($files['images']['size'] === 0) {
            $error[] = 'ファイルを選択してください！';
        }else if($files['images']['size'] > $max_file_size){
            $error[] = 'ファイルサイズが大きすぎます。';
        }

        //サイズは暫定規定（横サイズ1184px）以上のみ許容
        if($img_width < $this->upload_file_min_width){
            //$error[] = "横サイズが規定（".$this->upload_file_min_width."px）以上ではありません。";
        }

        if(!in_array($format, $this->allowed_format)){
            $error[] = '許可されているアップロード型式ではありません。';
        }


        if(empty($error)){

            try{

                //$result = move_uploaded_file($files['images']['tmp_name'], $upload_path);
                //リサイズして指定ディレクトリに保存
                $result = $this->resizeImage($files['images'],$this->getUploadFileMinWidth(),$resize_file_name, $dest_dir, $changed_name);
                if($result == true){
                    $result = $changed_name;
                }else{
                    $error[] = "error when upload";
                }

            }catch(Exception $e){
                $error[] = $e->getMessage();
            }


        }

        return $result;

    }


    /**
     * validate
     */
    public function fileValidate($file_name){

        $error = array();

        if(!empty($file_name)){

            $info = pathinfo($file_name);
            $file_name = $info['filename'];

            //ファイル命名ルールに則り「_」で分割
            $str_arr = explode("_",$file_name);

            //ルールに則っているか
            if( count($str_arr) == 3 && isset($str_arr[0]) && isset($str_arr[1]) && isset($str_arr[2]) ){

                //var_dump("$str_arr[2]" . $str_arr[2]);
                //並び順を表すため、2桁の数値
                if(!preg_match('/^([0-9]{1,2})$/', $str_arr[2])){
                    //$error[] = "ファイル名がルールに沿っているかご確認ください。[並び順]は数値2桁でセットして下さい。";
                }

            }else{

                //$error[] = "ファイル名がルールに沿っているかご確認ください。：".$file_name;

            }


        }else{

            $error[] = "ファイル名が空です。";

        }

        return $error;

    }

    /**
     * 画像をリサイズする
     */
    public function resizeImage($image,$new_width,$new_name,$dir = ".",&$chenged_name){

        list($width,$height,$type) = getimagesize($image["tmp_name"]);
        $new_height = round($height*$new_width/$width);
        $emp_img = imagecreatetruecolor($new_width,$new_height);
        switch($type){
            case IMAGETYPE_JPEG:
                $new_image = imagecreatefromjpeg($image["tmp_name"]);
                break;
            case IMAGETYPE_GIF:
                $new_image = imagecreatefromgif($image["tmp_name"]);
                break;
            case IMAGETYPE_PNG:
                imagealphablending($emp_img, false);
                imagesavealpha($emp_img, true);
                $new_image = imagecreatefrompng($image["tmp_name"]);
                break;
        }
        imagecopyresampled($emp_img,$new_image,0,0,0,0,$new_width,$new_height,$width,$height);
        switch($type){
            case IMAGETYPE_JPEG:
                imagejpeg($emp_img,$dir."/".$new_name.".jpg");
                $chenged_name = $new_name.=".jpg";
                break;
            case IMAGETYPE_GIF:
                $bgcolor = imagecolorallocatealpha($new_image,0,0,0,127);
                imagefill($emp_img, 0, 0, $bgcolor);
                imagecolortransparent($emp_img,$bgcolor);
                imagegif($emp_img,$dir."/".$new_name.".gif");
                $chenged_name = $new_name.=".gif";
                break;
            case IMAGETYPE_PNG:
                imagepng($emp_img,$dir."/".$new_name.".png");
                $chenged_name = $new_name.=".png";
                break;
        }


        imagedestroy($emp_img);
        imagedestroy($new_image);


        return true;
    }



}





?>