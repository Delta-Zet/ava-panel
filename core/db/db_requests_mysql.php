<?


	/******************************************************************************************************
	*** Package: AVA-Panel Version 3.0
	*** Copyright (c) 2006, Anton A. Rassypaeff. All rights reserved
	*** License: GNU General Public License v3
	*** Author: Anton A. Rassypaeff | Рассыпаев Антон Александрович
	*** Contacts:
	***   Site: http://ava-panel.ru
	***   E-mail: manage@ava-panel.ru
	******************************************************************************************************/


class db_requests_mysql extends objectInterface implements db_RequestsInterface{

	private $connection;
	private $dbResource;
	private $query;

	private $result;
	private $rows;
	private $id;
	private $info;

	public function __construct($connection){
		$this->connection = $connection;
		$this->dbResource = $connection->GetConnection();
	}


	//Отправка запросов

	public function Send($query){
		$this->query = $query;
		$this->result = mysql_query($query, $this->dbResource);

		if($this->result === false){
			$this->connection->error('{Call:Lang:core:core:oshibkaotpra}', $this);
			if($this->connection->inTransaction){
				mysql_query("ROLLBACK", $this->dbResource);
				$this->connection->inTransaction = false;
			}

			return false;
		}

		return true;
	}

	public function ReSend(){
		return $this->Send($this->query);
	}


	//Сведения о запросахъ

	public function GetQuery(){
		return $this->query;
	}

	public function GetResult(){
		return $this->result;
	}

	public function GetRows(){
		if(!$this->rows){
			if(is_resource($this->query)) $this->rows = mysql_num_rows($this->query);
			else $this->rows = mysql_affected_rows($this->dbResource);
		}
		return $this->rows;
	}

	public function getId(){
		if(!$this->id) $this->id = mysql_insert_id($this->dbResource);
		return $this->id;
	}

	public function getInfo(){
		if(!$this->info) $this->info = mysql_info($this->dbResource);
		return $this->info;
	}


	//Операции над запросами

	public function Seek($row){
		mysql_data_seek($this->result, $row);
	}

	public function Fetch($row = false){
		if(!is_resource($this->result)){
			throw new AVA_DB_Exception('{Call:Lang:core:core:nekorrektnai}');
		}

		if($row !== false){
			$this->Seek($row);
		}

		return mysql_fetch_assoc($this->result);
	}

	public function FetchArray($row = false){
		if(!is_resource($this->result)){
			throw new AVA_DB_Exception('{Call:Lang:core:core:nekorrektnai}');
		}

		if($row !== false){
			$this->Seek($row);
		}

		return mysql_fetch_array($this->result);
	}

	public function CellFetch($row, $cell){
		if($this->getRows() <= 0) return false;
		return mysql_result($this->result, $row, $cell);
	}


	//Ошибки

	public function GetError(){
		return mysql_error($this->dbResource);
	}

	public function GetErrorCode(){
		return mysql_errno($this->dbResource);
	}


	//Чистка

	public function Clear(){
		if(is_resource($this->result)) mysql_free_result($this->result);
	}

	public function __destruct(){
		$this->Clear();
	}

}

?>