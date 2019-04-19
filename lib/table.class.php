<?php


/**
 * Table db
 */
class Table{

	private $dbh;

	public function __construct($dbh){
		$this->dbh = $dbh;
	}

	public function getColumns($table){
		$data = array();
		$sql = "SHOW COLUMNS FROM ".$table;
		$db = $this->dbh->prepare($sql);
		$db->execute();
		while($row = $db->fetch(PDO::FETCH_ASSOC)){
			$data[] = $row['Field'];
		}
		return $data;
	}

	public function getFieldType($table){
		$data = array();
		$sql = "SHOW COLUMNS FROM ".$table;
		$db = $this->dbh->prepare($sql);
		$db->execute();
		while($row = $db->fetch(PDO::FETCH_ASSOC)){
			$data[$row['Field']] = $row['Type'];
		}
		return $data;
	}

	public function getFieldAll($table){
		$data = array();
		$sql = "SHOW COLUMNS FROM ".$table;
		$db = $this->dbh->prepare($sql);
		$db->execute();
		while($row = $db->fetch(PDO::FETCH_ASSOC)){
			$data[$row['Field']] = $row;
		}
		return $data;
	}

}



/**
 * AutoUpdater
 * 継承先クラスのコンストラクタでParent::__construct($dbh);を実行してください
 */
class AutoUpdater extends Table{

	private $dbh;

	function __construct($dbh){

		Parent::__construct($dbh);
		$this->dbh = $dbh;

	}

	//使用注意
	public function delete($table, $id){

		$problems = array();
		$problem_code = "AU_".time();

		if(isset($_SESSION['sales_staff_code']) && !empty($_SESSION['sales_staff_code'])){
			$updater = $_SESSION['sales_staff_code'];
		}else{
			$updater = NULL;
		}

		$sql = " DELETE FROM ".$table." WHERE id = :id";
		$db = $this->dbh->prepare($sql);
		$db->bindValue(':id', $id, PDO::PARAM_INT);

		try {

			$db->execute();

		}catch(Exception $e){

			throw new Exception($e->getMessage());

		}catch(PDOException $e){

			throw new Exception($e->getMessage());

		}


	}


	/**
	 * 独自sql
	 */
	public function selectMysql($sql, $condition, $condition_pdo_type, $code = "no code"){

		$creater = $_SESSION['sales_staff_code'];

		$data = array();

		try{

			$stmt = $this->dbh->prepare($sql);
			foreach ($condition as $key => $value) {
				$stmt->bindValue(":".$key, $value, $condition_pdo_type[$key]);
			}
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[] = $row;
			}

		}catch(Exception $e){

			throw new Exception($e->getMessage());

		}catch(PDOException $e){

			throw new Exception($e->getMessage());

		}

		return $data;

	}

	/**
	 * データ型を自動判別 連番指定での更新 $update_options
	 *
	 * ※genralMemoを利用する際はAutoUpdaterMemoをimplemantsしたクラスのオブジェクトを渡すこと
	 *
	 */
	public function insert($table,$datas,$update_options = array(), $code = ""){


		$result = false;

		//option
		$opt_key = isset($update_options['key']) && !empty($update_options['key']) ? $update_options['key'] : "";

		$problems = array();
		$problem_code = $opt_key."_".time();

		if(!empty($code)){
			$problem_code = $code;
		}

		//updaterはsessionから
		if(isset($_SESSION['sales_staff_code']) && !empty($_SESSION['sales_staff_code'])){
			$updater = $_SESSION['sales_staff_code'];
		}else{
			$updater = NULL;
		}
		if(isset($_SESSION['sales_staff_code']) && !empty($_SESSION['sales_staff_code'])){
			$creater = $_SESSION['sales_staff_code'];
		}else{
			$creater = NULL;
		}

		//テーブルのカラムと定義を取得する。
		$column_info = Parent::getFieldType($table);

		//定義詳細 -- nullありなしとか
		$column_detail = Parent::getFieldAll($table);


		//update,createdate,updatedateなどたまに存在しないテーブルがあるため、column_info_tmpに配列を残し、別軸でチェックする。
		$column_info_tmp = $column_info;

		//以下は基本的に引数の値で変更しないのでunsetしてチェック対象項目から外す
		if(isset($column_info['id'])){
			unset($column_info['id']);
		}
		//createdateの指定があっても無視
		if(isset($column_info['createdate'])){
			unset($column_info['createdate']);
		}
		//updatedateはnow()固定　指定があっても無視
		if(isset($column_info['updatedate'])){
			unset($column_info['updatedate']);
		}
		//updater固定　指定があっても無視
		if(isset($column_info['updater'])){
			unset($column_info['updater']);
		}
		//creater固定　指定があっても無視
		if(isset($column_info['creater'])){
			unset($column_info['creater']);
		}
		try{

			$sql = " INSERT INTO ".$table;

			$set = "";
			$set_replace = "";
			foreach ($datas as $key => $value) {
				if(!array_key_exists($key, $column_info)){
					$problems['not_in_array'][] = array(
						'key' => $key
					);
					continue;
				}

				$set_replace .= empty($set_replace) ? "" : ",";
				$set_replace .= ":".$key;

				$set .= empty($set) ? "" : ",";
				$set .= $key;

			}

			if(array_key_exists("updatedate", $column_info_tmp)){
				$set .= " ,updatedate";
				$set_replace .= " ,NOW()";
			}
			if(array_key_exists("createdate", $column_info_tmp)){
				$set .= " ,createdate";
				$set_replace .= " ,NOW()";
			}

			if(array_key_exists("updater", $column_info_tmp)){
				$set .= ",updater";
				$set_replace .= ",:updater";
			}
			if(array_key_exists("creater", $column_info_tmp)){
				$set .= ",creater";
				$set_replace .= ",:creater";
			}

			$sql .= "(". $set .")values(". $set_replace .")";

				//検証sql ここから---------------------------------------------


				//検証sql ここまで---------------------------------------------

			//no columns to update
			if(empty($set_replace)){
				throw new Exception("更新する項目が存在しません。");
			}


			$db = $this->dbh->prepare($sql);

			foreach ($datas as $key => $value) {

				if(!array_key_exists($key, $column_info)){
					continue;
				}

				//カラムのデータ型参照する
				if(substr($column_info[$key],0,3) == 'int'){

					if(!empty($value)){
						$db->bindValue(':'.$key, $value, PDO::PARAM_INT);
					}else{
						$db->bindValue(':'.$key, 0, PDO::PARAM_INT);
					}

				}else if(substr($column_info[$key],0,3) == 'dec'){//decimal


					//key  null指定なし
					if( isset($column_detail[$key]['Null']) && $column_detail[$key]['Null'] == 'NO' ){


						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, "0", PDO::PARAM_STR);
						}


					}else{

						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, null, PDO::PARAM_STR);
						}

					}



				} else {

					//key  null指定なし
					if( isset($column_detail[$key]['Null']) && $column_detail[$key]['Null'] == 'NO' ){


						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, "", PDO::PARAM_STR);
						}


					}else{

						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, null, PDO::PARAM_STR);
						}

					}

				}

			}

			if(array_key_exists("updater", $column_info_tmp)){
				$db->bindValue(':updater', $updater, PDO::PARAM_INT);
			}

			if(array_key_exists("creater", $column_info_tmp)){
				$db->bindValue(':creater', $creater, PDO::PARAM_INT);
			}
			$result = $db->execute();

		}catch(Exception $e){

			throw new Exception($e->getMessage());

		}catch(PDOException $e){

			throw new Exception($e->getMessage());

		}

		//存在しないカラムがセットされていた場合
		if(!empty($problems)){

		}else{

		}


		return $result;

	}

	/**
	 * select
	 */
	public function select($table,$condition, $code = ""){


		$data = array();

		$opt_key = "";
		$str = "";

		$problems = array();
		// $problem_code = $opt_key."_".time();

		//updaterはsessionから
		if(isset($_SESSION['sales_staff_code']) && !empty($_SESSION['sales_staff_code'])){
			$selecter = $_SESSION['sales_staff_code'];
		}else{
			$selecter = NULL;
		}

		//テーブルのカラムと定義を取得する。
		$column_info = Parent::getFieldType($table);

		//定義詳細 -- nullありなしとか
		$column_detail = Parent::getFieldAll($table);


		//update,createdate,updatedateなどたまに存在しないテーブルがあるため、column_info_tmpに配列を残し、別軸でチェックする。
		$column_info_tmp = $column_info;

		//以下は基本的に引数の値で変更しないのでunsetしてチェック対象項目から外す

		try{

			$sql = "SELECT * FROM ".$table;

			$where = "";
			if (!empty($condition)) {
				foreach ($condition as $key => $value) {
					$where .= (empty($where) ? " WHERE " : " AND ").$key." = :".$key;
				}
			}
			$sql .= $where;

				//検証sql ここから---------------------------------------------


				//検証sql ここまで---------------------------------------------

			$db = $this->dbh->prepare($sql);

			foreach ($condition as $key => $value) {

				if(!array_key_exists($key, $column_info)){
					continue;
				}

				//カラムのデータ型参照する
				if(substr($column_info[$key],0,3) == 'int'){

					if(!empty($value)){
						$db->bindValue(':'.$key, $value, PDO::PARAM_INT);
					}else{
						$db->bindValue(':'.$key, 0, PDO::PARAM_INT);
					}

				}else if(substr($column_info[$key],0,3) == 'dec'){//decimal


					//key  null指定なし
					if( isset($column_detail[$key]['Null']) && $column_detail[$key]['Null'] == 'NO' ){


						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, "0", PDO::PARAM_STR);
						}


					}else{

						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, null, PDO::PARAM_STR);
						}

					}



				} else {

					//key  null指定なし
					if( isset($column_detail[$key]['Null']) && $column_detail[$key]['Null'] == 'NO' ){


						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, "", PDO::PARAM_STR);
						}


					}else{

						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, null, PDO::PARAM_STR);
						}

					}

				}

			}

			$result = $db->execute();

			while ($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$data[] = $row;
			}

		}catch(Exception $e){

			throw new Exception($e->getMessage());

		}catch(PDOException $e){

			throw new Exception($e->getMessage());

		}


		//存在しないカラムがセットされていた場合
		if(!empty($problems)){

		}else{

		}


		return $data;


	}


	/**
	 * データ型を自動判別 連番指定での更新 $update_options
	 *
	 * ※genralMemoを利用する際はAutoUpdaterMemoをimplemantsしたクラスのオブジェクトを渡すこと
	 *
	 */
	public function update($table, $datas,$id,$update_options = array(), $code = ""){


		$result = false;

		//option
		$opt_key = isset($update_options['key']) && !empty($update_options['key']) ? $update_options['key'] : "";

		$problems = array();
		$problem_code = $opt_key."_".time();
		if(!empty($code)){
			$problem_code = $code;
		}

		//updaterはsessionから
		if(isset($_SESSION['sales_staff_code']) && !empty($_SESSION['sales_staff_code'])){
			$updater = $_SESSION['sales_staff_code'];
		}else{
			$updater = NULL;
		}


		//テーブルのカラムと定義を取得する。
		$column_info = Parent::getFieldType($table);

		//定義詳細 -- nullありなしとか
		$column_detail = Parent::getFieldAll($table);


		//update,createdate,updatedateなどたまに存在しないテーブルがあるため、column_info_tmpに配列を残し、別軸でチェックする。
		$column_info_tmp = $column_info;

		//以下は基本的に引数の値で変更しないのでunsetしてチェック対象項目から外す
		if(isset($column_info['id'])){
			unset($column_info['id']);
		}
		//createdateの指定があっても無視
		if(isset($column_info['createdate'])){
			unset($column_info['createdate']);
		}
		//updatedateはnow()固定　指定があっても無視
		if(isset($column_info['updatedate'])){
			unset($column_info['updatedate']);
		}
		//updater固定　指定があっても無視
		if(isset($column_info['updater'])){
			unset($column_info['updater']);
		}

		try{

			$sql = " UPDATE ".$table;

			$set = "";
			foreach ($datas as $key => $value) {
				if(!array_key_exists($key, $column_info)){
					$problems['not_in_array'][] = array(
						'key' => $key
					);
					continue;
				}
				$set .= empty($set) ? " SET " : " , ";
				$set .= $key." = :".$key;
			}


			if(array_key_exists("updatedate", $column_info_tmp)){
				$set .= empty($set) ? " SET " : " , ";
				$set .= " updatedate = NOW()";
			}

			if(array_key_exists("updater", $column_info_tmp)){
				$set .= empty($set) ? " SET " : " , ";
				$set .= " updater = :updater";
			}

			$sql .= $set;

			//$sql .= " FROM ".$table;
			$sql .= " WHERE id=:id";

				//検証sql
				$sql_test = " UPDATE ".$table;

				$set_test = "";
				foreach ($datas as $key => $value) {
					if(!array_key_exists($key, $column_info)){
						continue;
					}
					$set_test .= empty($set_test) ? " SET " : " , ";
					if(empty($value)){
						$set_test .= $key." = ''";
					}else{
						$set_test .= $key." = ".$value;
					}
				}

				if(array_key_exists("updatedate", $column_info_tmp)){
					$set_test .= empty($set_test) ? " SET " : " , ";
					$set_test .= " updatedate = NOW()";
				}

				if(array_key_exists("updater", $column_info_tmp)){
					$set_test .= empty($set_test) ? " SET " : " , ";
					$set_test .= " updater = :updater";
				}

				$sql_test .= $set_test;
				$sql_test .= " WHERE id=".$id;

			//no columns to update
			if(empty($set)){
				throw new Exception("更新する項目が存在しません。");
			}

			$db = $this->dbh->prepare($sql);

			foreach ($datas as $key => $value) {

				if(!array_key_exists($key, $column_info)){
					continue;
				}

				//カラムのデータ型参照する
				if(substr($column_info[$key],0,3) == 'int'){

					if(!empty($value)){
						$db->bindValue(':'.$key, $value, PDO::PARAM_INT);
					}else{
						$db->bindValue(':'.$key, 0, PDO::PARAM_INT);
					}

				}else if(substr($column_info[$key],0,3) == 'dec'){//decimal


					//key  null指定なし
					if( isset($column_detail[$key]['Null']) && $column_detail[$key]['Null'] == 'NO' ){


						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, "0", PDO::PARAM_STR);
						}


					}else{

						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, null, PDO::PARAM_STR);
						}

					}



				} else {

					//key  null指定なし
					if( isset($column_detail[$key]['Null']) && $column_detail[$key]['Null'] == 'NO' ){


						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, "", PDO::PARAM_STR);
						}


					}else{

						if(!empty($value)){
							$db->bindValue(':'.$key, $value, PDO::PARAM_STR);
						}else{
							$db->bindValue(':'.$key, null, PDO::PARAM_STR);
						}

					}

				}

			}

			if(array_key_exists("updater", $column_info_tmp)){
				$db->bindValue(':updater', $updater, PDO::PARAM_INT);
			}

			$db->bindValue(':id', $id, PDO::PARAM_INT);

			$result = $db->execute();

		}catch(Exception $e){

			throw new Exception($e->getMessage());

		}catch(PDOException $e){

			throw new Exception($e->getMessage());

		}

		//存在しないカラムがセットされていた場合
		if(!empty($problems)){

		}else{

		}


		return $result;

	}

}


/**
 * Select
 */
class AutoSelect{

	private $dbh;
	private $table;
	public function __construct($dbh){
		$this->dbh = $dbh;
	}

	public function setTable($table){
		$this->table = $table;
	}

	public function select(){

		if(empty($this->table)){
			return false;
		}

		$sql = "SELECT * FROM ".$this->table;
		$db = $this->dbh->prepare($sql);
		$db->execute();
		return $db;

	}
	public function fetchAll(){

		if(empty($this->table)){
			return false;
		}

		$sql = "SELECT * FROM ".$this->table;
		$db = $this->dbh->prepare($sql);
		$db->execute();
		return $db->fetchAll();
	}

}




?>