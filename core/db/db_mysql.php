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


class db_mysql extends db_main implements db_Interface{

	protected $host;
	protected $user;
	protected $pwd;
	protected $params;

	protected $db;
	protected $prefix;
	protected $tblPrefix;

	private $connection;

	private $lockedTables = array();
	private $inTransaction = false;
	private $tables = array();
	private $tablesExtra = array();

	public $error;
	public $errorCode;


	public function __construct($host, $user, $pwd, $params, $nl = true, $pConnect = false){
		$this->host = $host;
		$this->user = $user;
		$this->pwd = $pwd;
		$this->params = $params;
		$this->connect($nl, $pConnect);
	}

	public function connect($nl = true, $pConnect = false){
		/*
			Устанавливает соединение
		*/

		if(function_exists('mysql_pconnect') && $pConnect) $this->connection = mysql_pconnect($this->host, $this->user, $this->pwd);
		else $this->connection = mysql_connect($this->host, $this->user, $this->pwd, $nl);
		if(!$this->connection) $this->error('{Call:Lang:core:core:neudalosusta}');
		$this->Req("SET sql_mode=''");

		if(!empty($this->params['inCharset']) && ($this->params['inCharset'] == $this->params['outCharset'])) $this->Req("SET NAMES ".$this->params['inCharset']);
		elseif(!empty($this->params['inCharset'])){
			$this->Req("SET character_set_client='".$this->params['inCharset']."'");
			$this->Req("SET character_set_results='".$this->params['outCharset']."'");
			$this->Req("SET collation_connection='".$this->params['cc']."'");
		}
	}

	public function setDB($db, $prefix){
		/*
			Устанавливает БД
		*/

		$this->db = $db;
		$this->prefix = '`'.$db.'`.'.$prefix;
		$this->tblPrefix = $prefix;

		if(!mysql_select_db($this->db, $this->connection)){
			$this->error('{Call:Lang:core:core:neudalosvybr:'.Library::serialize(array($db)).'}');
		}
	}


	//Возвращает параметры
	public function GetHost(){
		return $this->host;
	}

	public function GetUser(){
		return $this->user;
	}

	public function GetParams(){
		return $this->params;
	}

	public function GetDBName(){
		return $this->db;
	}

	public function getPrefix(){
		return $this->prefix;
	}

	public function getTblPrefix(){
		return $this->tblPrefix;
	}

	public function GetConnection(){
		return $this->connection;
	}

	public function GetStatus(){
		if(!is_resource($this->connection)) return false;
		return mysql_stat($this->connection);
	}

	public function Ping(){
		return mysql_ping($this->connection);
	}


	/***********************************************************************************************************************************************************************

																			Работа с запросами

	************************************************************************************************************************************************************************/

	public function Req($req){
		if(is_array($req)){
			if(empty($req['req_type'])) $req['req_type'] = 'Select';
			$reqTypeName = 'get'.$req['req_type'];
			$req = $this->$reqTypeName($req);
		}

		$q = new db_requests_mysql($this);
		if(SHOW_DB_REQS >= 1) $dId = $GLOBALS['Core']->setDebugAction('Отправлен запрос MySQL '.regExp::subStr($req, 0, 30));
		$q->Send($req);
		if(SHOW_DB_REQS >= 1) $GLOBALS['Core']->setDebugInterval('Завершен запрос '.$req, $dId);
		return $q;
	}


	/***********************************************************************************************************************************************************************

																		Запросы типа Select

	************************************************************************************************************************************************************************/

	public function RowFetch($req){
		if(is_array($req)){
			$req['limit'] = 1;
			$req = $this->getSelect($req);
		}
		elseif(!regExp::Match('limit', $req, false, true)) $req .= ' LIMIT 1';

		return $this->Req($req)->Fetch();
	}

	public function CellFetch($req){
		if(empty($req['value']) && !empty($req['1'])) $req['value'] = $req['1'];
		$req['limit'] = 1;
		$req['fields'] = "`{$req['value']}`";
		return $this->Req($this->getSelect($req))->cellFetch(0, $req['value']);
	}

	public function columnFetch($req, $key = false){
		if(is_array($req)){
			if(empty($req['value'])) $req['value'] = isset($req['1']) ? $req['1'] : '';
			if(empty($req['key'])) $req['key'] = isset($req['2']) ? $req['2'] : '';

			if($req['value'] == '*') $req['fields'] = $req['value'];
			elseif(is_array($req['value'])){
				$req['fields'] = $req['value'];
				if($req['key'] && !in_array($req['key'], $req['fields'])) $req['fields'][] = $req['key'];
			}
			else{
				$req['fields'] = array($req['value']);
				if($req['key']) $req['fields'][] = $req['key'];
			}

			if(empty($req['table'])) $req['table'] = isset($req['0']) ? $req['0'] : '';
			if(empty($req['where'])) $req['where'] = isset($req['3']) ? $req['3'] : '';
			if(empty($req['order'])) $req['order'] = isset($req['4']) ? $req['4'] : '';
			if(empty($req['limit'])) $req['limit'] = isset($req['5']) ? $req['5'] : '';

			unset($req['0'], $req['1'], $req['2'], $req['3'], $req['4'], $req['5']);
			$obj = $this->Req($this->getSelect($req));
		}
		else{
			$obj = $this->Req($req);
		}

		$return = array();
		while($r = $obj->Fetch()){
			if(is_array($req) && $req['key']) $return[$r[$req['key']]] = (!is_array($req['value']) && $req['value'] != '*') ? $r[$req['value']] : $r;
			elseif($key && !is_array($req)) $return[$r[$key]] = $r;
			else $return[] = (is_array($req) && !is_array($req['value']) && $req['value'] != '*') ? $r[$req['value']] : $r;
		}

		return $return;
	}

	public function Count($req){
		if(is_array($req)){
			if(empty($req['where'])) $req['where'] = empty($req['1']) ? '' : $req['1'];
			if(empty($req['id'])) $req['id'] = empty($req['2']) ? 'id' : $req['2'];
			unset($req['1'], $req['2']);

			$req['fields'] = "COUNT(`{$req['id']}`)";
			$req = $this->getSelect($req);
		}

		return $this->Req($req)->cellFetch(0, 0);
	}

	public function Min($req){
		/*
			Минимальное значение столбца
		*/

		if(is_array($req)){
			if(empty($req['field'])) $req['field'] = empty($req['1']) ? 'id' : $req['1'];
			if(empty($req['where'])) $req['where'] = empty($req['2']) ? '' : $req['2'];
			unset($req['1'], $req['2']);

			$req['fields'] = "MIN(`{$req['field']}`)";
			$req = $this->getSelect($req);
		}

		return $this->Req($req)->cellFetch(0, 0);
	}

	public function Max($req){
		/*
			Максимальное значение столбца
		*/

		if(is_array($req)){
			if(empty($req['field'])) $req['field'] = empty($req['1']) ? 'id' : $req['1'];
			if(empty($req['where'])) $req['where'] = empty($req['2']) ? '' : $req['2'];
			unset($req['1'], $req['2']);

			$req['fields'] = "MAX(`{$req['field']}`)";
			$req = $this->getSelect($req);
		}

		return $this->Req($req)->cellFetch(0, 0);
	}

	public function Sum($req){
		/*
			Сумма всех значений столбца
		*/

		if(is_array($req)){
			if(empty($req['field'])) $req['field'] = empty($req['1']) ? '' : $req['1'];
			if(empty($req['where'])) $req['where'] = empty($req['2']) ? '' : $req['2'];
			unset($req['1'], $req['2']);

			$req['fields'] = "SUM(`{$req['field']}`)";
			$req = $this->getSelect($req);
		}

		return $this->Req($req)->cellFetch(0, 0);
	}

	public function Rnd($req){
		/*
			Случайная запись
		*/

	}


	/************************************************************************************************************************************************************************************

																			Прочие запросы

	************************************************************************************************************************************************************************************/

	public function Ins($req){
		$req = $this->getInsert($req);
		if(is_array($req)){
			foreach($req as $i => $e) $return = $this->Req($e)->getId();
		}
		else $return = $this->Req($req)->getId();

		return $return;
	}

	public function Upd($req){
		return $this->Req($this->getUpdate($req))->getRows();
	}

	public function Del($req){
		return $this->Req($this->getDelete($req))->getRows();
	}

	public function CT($req){
		return $this->Req($this->getCreate($req));
	}

	public function UT($req){
		$req = $this->getCreateReqArr($req);
		return $this->Alter(array($req['table'], array('add' => $req['fields']), $req['extras']));
	}

	public function Alter($req){
		if(empty($req['table'])) $req['table'] = !empty($req['0']) ? $req['0'] : '';
		if(empty($req['fields'])) $req['fields'] = !empty($req['1']) ? $req['1'] : '';
		if(empty($req['extras'])) $req['extras'] = !empty($req['2']) ? $req['2'] : array();
		$alter = array();

		if(!empty($req['fields']['add'])){
			foreach($this->getColumnParams($req['fields']['add'], $req['extras']) as $i => $e){
				if(regExp::Match("|^#index\-(.+)$|", $i, true, true, $m)){
					if(!$this->issetIndex($req['table'], $m[1])) $alter[] = "ADD $e";
				}
				elseif(!$this->issetField($req['table'], $i)) $alter[] = "ADD COLUMN $e";
			}
		}

		if(!empty($req['fields']['modify'])){
			foreach($this->getColumnParams($req['fields']['modify'], $req['extras']) as $i => $e){
				if(!regExp::digit($i) && $this->issetField($req['table'], $i)) $alter[] = "MODIFY COLUMN $e";
			}
		}

		if(!empty($req['fields']['drop'])){
			foreach($req['fields']['drop'] as $i => $e){
				if($this->issetField($req['table'], $i)) $alter[] = "DROP COLUMN `$i`";
			}

			if(!empty($req['extras']['uni'])){
				foreach($req['extras']['uni'] as $i => $e){
					if($this->issetField($req['table'], $i)) $alter[] = "DROP UNIQUE `$i`";
				}
			}

			if(!empty($req['extras']['index'])){
				foreach($req['extras']['index'] as $i => $e){
					if($this->issetField($req['table'], $i)) $alter[] = "DROP INDEX `$i`";
				}
			}
		}

		return $alter ? $this->Req("ALTER TABLE ".$this->prefix.$req['table']." ".implode(', ', $alter)) : true;
	}

	public function Drop($table){
		if(is_array($table)){
			foreach($table as $i => $e){
				$table[$i] = $this->prefix.regExp::replace("/^".$this->tblPrefix."/iUs", "", $table[$i], true);
			}
			$table = implode(", ", $table);
		}
		else $table = $this->prefix.regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true);

		$return = $this->Req("DROP TABLE IF EXISTS ".$table);
		if($this->tables) $this->GetTables(true);
		return $return;
	}

	public function Truncate($table){
		$table = $this->prefix.regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true);
		return $this->Req("TRUNCATE TABLE ".$table);
	}

	public function Grant($req){}

	public function Rev($req){}


	/************************************************************************************************************************************************************************************

																	Построение определенных типов запросов

	************************************************************************************************************************************************************************************/

	private function getSelect($req){
		$req = $this->getReqArr($req);
		return "SELECT ".(is_array($req['fields']) ? '`'.regExp::implode('`, `', $req['fields']).'`' : $req['fields'])." FROM ".$this->prefix.$req['table']." ".$this->getExtras($req);
	}

	private function getInsert($req){
		$req = $this->getReqArr($req);
		if(empty($req['fields'])) throw new AVA_DB_Exception('Отсутствуют поля для вставки в таблицу');
		if(Library::isHash($req['fields'])) $req['fields'] = array($req['fields']);

		foreach($req['fields'] as $i => $e){
			if(!is_array($e)) throw new AVA_DB_Exception('{Call:Lang:core:core:dliazaprosov}');
			foreach($e as $i1 => $e1){
				$f[$i1] = '`'.$i1.'`';
			}
		}

		foreach($req['fields'] as $i => $e){
			foreach($f as $i1 => $e1){
				if(!isset($e[$i1])) $e[$i1] = '';
				elseif(is_array($e[$i1]) || is_object($e[$i1])) $e[$i1] = Library::serialize($e[$i1]);
				$v[$i][$i1] = "'".$this->Quot($e[$i1])."'";
			}

			$v[$i] = '('.regExp::implode(', ', $v[$i]).')';
		}

		$extras = '';
		if(!empty($req['extra']['low'])) $extras .= 'LOW_PRIORITY ';
		if(empty($req['extra']['notIgnore'])) $extras .= 'IGNORE ';

		$req2 = "INSERT ".$extras."INTO ".$this->prefix."{$req['table']} (".regExp::implode(', ', $f).") VALUES ";
		$l = $this->req("SHOW GLOBAL VARIABLES LIKE 'max_allowed_packet'")->cellFetch(0, 1);;
		$return = array();

		$j = 0;
		foreach($v as $i => $e){
			if(!isset($return[$j])) $return[$j] = $req2.$e;
			else $return[$j] .= ', '.$e;
			if(isset($v[$i + 1]) && regExp::bLen($return[$j].', '.$v[$i + 1]) > $l) $j ++;
		}

		if(!empty($req['extra']['onDuplicate'])){
			if(is_array($req['extra']['onDuplicate'])) $req['extra']['onDuplicate'] = $this->getUpdateFields($req['extra']['onDuplicate']);
			foreach($return as $i => $e){
				$return[$i] .= ' ON DUPLICATE KEY UPDATE '.$req['extra']['onDuplicate'];
			}
		}

		return $return;
	}

	private function getUpdate($req){
		$req = $this->getReqArr($req);
		if(!is_array($req['fields'])) throw new AVA_DB_Exception('{Call:Lang:core:core:dliazaprosov}');
		$extras = '';

		if(!empty($req['extra']['low'])) $extras .= 'LOW_PRIORITY ';
		if(empty($req['extra']['notIgnore'])) $extras .= 'IGNORE ';
		return "UPDATE ".$extras.$this->prefix."{$req['table']} SET ".$this->getUpdateFields($req['fields'])." ".$this->getExtras($req);
	}

	private function getUpdateFields($fields){
		$f = array();
		foreach($fields as $i => $e){
			if($i == '#isExp') continue;
			elseif(empty($fields['#isExp'][$i])){
				if(is_array($e) || is_object($e)) $e = Library::serialize($e);
				$f[] = "`$i`='".$this->Quot($e)."'";
			}
			else $f[] = "`$i`=".$e;
		}

		return regExp::implode(', ', $f);
	}

	private function getDelete($req){
		if(empty($req['table'])) $req['table'] = !empty($req['0']) ? $req['0'] : '';
		if(empty($req['where'])) $req['where'] = !empty($req['1']) ? $req['1'] : '';
		if(empty($req['order'])) $req['order'] = !empty($req['2']) ? $req['2'] : '';
		if(empty($req['limit'])) $req['limit'] = !empty($req['3']) ? $req['3'] : '';
		return "DELETE FROM ".$this->prefix."{$req['table']} ".$this->getExtras($req);
	}

	private function getCreate($req){
		$req = $this->getCreateReqArr($req);
		return "CREATE TABLE IF NOT EXISTS ".$this->prefix."{$req['table']} (".implode(', ', $this->getColumnParams($req['fields'], $req['extras'])).") ENGINE=InnoDB".(empty($this->params['inCharset']) ? '' : " DEFAULT CHARSET={$this->params['inCharset']}");
	}

	private function getReqArr($req){
		if(empty($req['table'])) $req['table'] = !empty($req['0']) ? $req['0'] : '';
		if(empty($req['fields'])) $req['fields'] = !empty($req['1']) ? $req['1'] : '';
		if(empty($req['where'])) $req['where'] = !empty($req['2']) ? $req['2'] : '';

		if(empty($req['order'])) $req['order'] = !empty($req['3']) ? $req['3'] : '';
		if(empty($req['limit'])) $req['limit'] = !empty($req['4']) ? $req['4'] : '';
		if(empty($req['extras'])) $req['extras'] = !empty($req['5']) ? $req['5'] : '';

		return $req;
	}

	private function getCreateReqArr($req){
		if(empty($req['table'])) $req['table'] = !empty($req['0']) ? $req['0'] : '';
		if(empty($req['fields'])) $req['fields'] = !empty($req['1']) ? $req['1'] : '';
		if(empty($req['extras'])) $req['extras'] = !empty($req['2']) ? $req['2'] : array();
		return $req;
	}

	private function getExtras($req){
		$req['where'] = empty($req['where']) ? '' : "WHERE {$req['where']}";
		$req['order'] = empty($req['order']) ? '' : "ORDER BY {$req['order']}";
		$req['limit'] = empty($req['limit']) ? '' : "LIMIT {$req['limit']}";
		return "{$req['where']} {$req['order']} {$req['limit']}";
	}

	private function getColumnParams($fields, $extras = array()){
		/*
			Параметры поля по умолчанию
		*/

		$return = array();

		foreach($fields as $i => $e){
			if(!$e){
				switch($i){
					case 'id':
						$extras['primary'] = $i;
						$extras['auto'][$i] = $i;

					case 'parent_id':
					case 'sort':
					case 'date':
						$e = 'INT';
						break;

					case 'stick':
					case 'on':
					case 'default':
						$e = 'CHAR(1)';
						break;

					case 'show':
					case 'status':
						$e = 'TINYINT';
						break;

					case 'login':
						$extras['uni']["login"][] = "login";

					case 'name':
					case 'pwd':
					case 'eml':
					case 'symptom':
						$e = 'VARCHAR(255)';
						break;

					case 'vars':
					case 'data':
					case 'body':
					case 'descript':
					case 'extra':
						$e = 'TEXT';
						break;

					case 'sum':
						$e = 'DECIMAL(11,2)';
						break;

					default:
						$e = 'VARCHAR(255)';
				}
			}

			if(empty($extras['auto'][$i]) && !empty($extras['null'][$i])) $e .= ' DEFAULT NULL';
			else{
				$e .= ' NOT NULL';

				if(empty($extras['auto'][$i]) && !regExp::Match('text', $e, false, true) && !regExp::Match('blob', $e, false, true) && !regExp::Match('enum', $e, false, true)){
					if(isset($extras['default'])) $dfl = '"'.$extras['default'].'"';
					elseif(regExp::Match("int", $e, false, true) || regExp::Match("float", $e, false, true) || regExp::Match("decimal", $e, false, true)) $dfl = '0';
					else $dfl = '""';
					$e .= ' DEFAULT '.$dfl;
				}
			}

			$return[$i] = '`'.$i.'` '.$e;
		}

		if(!empty($extras['primary'])) $return[$extras['primary']] .= " PRIMARY KEY";
		if(!empty($extras['auto'])) foreach($extras['auto'] as $e) $return[$e] .= " AUTO_INCREMENT";

		if(!empty($extras['index'])){
			foreach($extras['index'] as $i => $e){
				if(regExp::digit($i)) $i = $e[Library::firstKey($e)];
				$return['#index-'.$i] = "qINDEX `$i`(`".implode(", `", $e)."`)";
			}
		}

		if(!empty($extras['uni'])){
			foreach($extras['uni'] as $i => $e){
				if(regExp::digit($i)) $i = $e[Library::firstKey($e)];
				$return['#index-'.$i] = "UNIQUE `$i`(`".implode("`, `", $e)."`)";
			}
		}

		return $return;
	}

	private function getCreateFields($fields, $extras){
		/*
			Создает поля запросе create
			extras содержит доп. параметры, например uni содержит данные о уникальности поля, key объявляет поле ключем, а auto - auto_increment:
			$extras = array(
				'uni' => array(
					0 => (key => true),
					1 => (
						key1 => true,
						key2 => '100'
					)
				),
				'key' => 'field',
				'auto' => 'field'
			)
		*/

		$f = $u = array();

		if(!empty($extras['uni'])){
			foreach($extras['uni'] as $i => $e){
				foreach($e as $i1 => $e1){
					if($e1 === true) $u[$i][] = " `{$i1}` ";
					else $u[$i1][] = " `{$i1}` ( {$e1} ) ";
				}
			}
		}

		foreach($fields as $i => $e){
			$extr = '';
			if(!empty($extras[$i])){
				if(!is_array($extras['other'][$i])){
					$extr = $extras['other'][$i];
				}
				if(!empty($extras['key'][$i])){
					$extr .= "PRIMARY KEY ";
				}
				if(!empty($extras['auto'][$i])){
					$extr .= "AUTO_INCREMENT";
				}
			}

			}

			$f[] = "`$i` $e NOT NULL $extr";

		return array($f, $u);
	}

	private function columnTypesIsAlike($col1, $col2){
		/*
			Проверяет что типы столбцов одинаковые
		*/

		$col1 = regExp::replace("\s+", "", regExp::lower($col1), true);
		$col2 = regExp::replace("\s+", "", regExp::lower($col2), true);

		if($col1 == 'int') $col1 = 'int(11)';
		elseif($col1 == 'bigint') $col1 = 'bigint(20)';
		elseif($col1 == 'mediumint') $col1 = 'mediumint(8)';
		elseif($col1 == 'smallint') $col1 = 'smallint(6)';
		elseif($col1 == 'tinyint') $col1 = 'tinyint(4)';

		if($col2 == 'int') $col2 = 'int(11)';
		elseif($col2 == 'bigint') $col2 = 'bigint(20)';
		elseif($col2 == 'mediumint') $col2 = 'mediumint(8)';
		elseif($col2 == 'smallint') $col2 = 'smallint(6)';
		elseif($col2 == 'tinyint') $col2 = 'tinyint(4)';

		return ($col1 == $col2);
	}



	/************************************************************************************************************************************************************************************

																			Блокировка таблиц

	************************************************************************************************************************************************************************************/

	public function lock($tbl){
		/*
			LOCK TABLE
		*/

		if(!is_array($tbl)) $tbl = array($tbl);
		if($this->lockedTables[$i]) throw new AVA_DB_Exception('{Call:Lang:core:core:nevozmozhnos}');

		$locks = array();
		if(is_array($tbl)){
			foreach($tbl as $i => $e){
				$this->lockedTables[$i] = $e;
				$locks[] = "`{$this->prefix}{$i}` {$e}";
			}
		}

		return $this->Req("LOCK TABLES ".implode(', ', $locks));
	}

	public function unlock(){
		/*
			LOCK TABLE
		*/

		if(!empty($this->lockedTables)){
			$this->Req("UNLOCK TABLES");
			$this->lockedTables = array();
		}
	}

	public function isLock($tbl){
		return !empty($this->lockedTables[$tbl]);
	}


	/*******************************************************************************************************************************************************************

																		Поиск таблиц и полей

	********************************************************************************************************************************************************************/

	public function GetTables($force = false){
		if(!$this->tables || $force) $this->fetchTables();
		return $this->tables;
	}

	public function GetFields($table, &$extra = array(), $force = false){
		$this->GetTables($force);
		$table = regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true);
		if($force || empty($this->tables[$table]) || !is_array($this->tables[$table])) $this->fetchFields($table);
		$extra = isset($this->tablesExtra[$table]) ? $this->tablesExtra[$table] : array();
		return $this->tables[$table];
	}

	public function issetTable($table){
		/*
			Проверяет существование таблицы $table в выбранной базе
			$table может содержать префикс а может и нет
		*/

		$table = $this->tblPrefix ? regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true) : $table;
		$this->GetTables();
		return isset($this->tables[$table]);
	}

	public function issetField($table, $field){
		/*
			Проверяет существование поля $field таблицы $table в выбранной базе
		*/

		$table = $this->tblPrefix ? regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true) : $table;
		$this->GetFields($table);
		return isset($this->tables[$table][$field]);
	}

	public function issetIndex($table, $index){
		/*
			Проверяет существование поля $field таблицы $table в выбранной базе
		*/

		$table = $this->tblPrefix ? regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true) : $table;
		$this->GetFields($table, $extra);
		return isset($extra['uni'][$index]);
	}

	public function getTableStructure($table){
		return array(
			$this->getFields($table)
		);
	}

	public function copyTable($oldTable, $newTable, $newDB = false){
		if($newDB === false) $newDB = $this;
		if(!is_array($oldTable)) $oldTable = array($oldTable);
		if(!is_array($newTable)) $newTable = array($newTable);
		if(count($oldTable) != count($newTable)) throw new AVA_DB_Exception('Не совпадает количество копируемых таблиц');

		foreach($oldTable as $i => $e){
			if(!$newDB->issetTable($newTable[$i])){
				$TS = $this->getTableStructure($e);
				$newDB->CT(array($newTable[$i], $TS[0], $TS[1]));
			}

			foreach($this->columnFetch(array($e, '*')) as $r){
				$newDB->Ins($newTable[$i], $i);
			}
		}
	}

	private function fetchTables(){
		$this->tables = array();
		$rObj = $this->Req("SHOW TABLES FROM `".$this->db."`");

		while($r = $rObj->fetchArray()){
			if(!regExp::Match("/^".$this->tblPrefix."/iUs", $r['0'], true)) continue;
			else $r['0'] = regExp::replace("/^".$this->tblPrefix."/iUs", "", $r['0'], true);
			$this->tables[$r['0']] = $r['0'];
		}
	}

	private function fetchFields($table){
		$table = regExp::replace("/^".$this->tblPrefix."/iUs", "", $table, true);
		$this->tables[$table] = array();
		$this->extraTables[$table] = array();

		$obj = $this->Req("DESCRIBE {$this->prefix}{$table}");
		while($r = $obj->Fetch()){
			$this->tables[$table][$r['Field']] = $r['Type'];
			if($r['Extra'] == 'auto_increment') $this->tablesExtra[$table]['auto'][] = $r['Field'];
		}

		$obj = $this->Req("SHOW INDEX FROM {$this->prefix}{$table}");
		while($r = $obj->Fetch()){
			if($r['Key_name'] != 'PRIMARY') $this->tablesExtra[$table]['uni'][$r['Key_name']][$r['Seq_in_index']] = $r['Column_name'];
			else $this->tablesExtra[$table]['primary'] = $r['Column_name'];
		}
	}



	/*******************************************************************************************************************************************************************

																		Поиск таблиц и полей

	********************************************************************************************************************************************************************/

	public function trStart(){
		/*
			Начинает транзакцию
		 */

		$this->Req('START TRANSACTION');
		$this->inTransaction = true;
	}

	public function trEnd($result){
		/*
			Откатывает транзакцию
		 */

		if($result) $this->Req('COMMIT');
		else $this->Req('ROLLBACK');
		$this->inTransaction = false;
	}

	public function trIsStarted(){
		return $this->inTransaction;
	}


	/*******************************************************************************************************************************************************************

																				Дамп БД

	********************************************************************************************************************************************************************/

	public function GetDump($tables = 'all', $params = array()){}

	public function AddDump($dump, $params = array()){}


	/*******************************************************************************************************************************************************************

																		Работа над ошибками

	********************************************************************************************************************************************************************/

	public function error($msg, $rObj = false){
		$this->errorCode = mysql_errno($this->connection);
		$this->error = mysql_error($this->connection);
		$req = $rObj ? '{Call:Lang:core:core:tekstzaprosa:'.Library::serialize(array('[nocall]'.regExp::substr($rObj->GetQuery(), 0, 4096).'[/nocall]')).'}' : '';
		throw new AVA_DB_Exception('{Call:Lang:core:core:soobshchenie:'.Library::serialize(array($msg, $this->errorCode, '[nocall]'.$this->error.'[/nocall]', $req)).'}');
	}


	/*******************************************************************************************************************************************************************

																		Разрушение соединения

	********************************************************************************************************************************************************************/

	public function Disconnect(){
		return mysql_close($this->connection);
	}

	public function __destruct(){
		$this->unlock();
		if($this->inTransaction) mysql_query("ROLLBACK", $this->connection);
		if(is_resource($this->connection)) mysql_close($this->connection);
	}


	/*******************************************************************************************************************************************************************

																			Прочие функции

	********************************************************************************************************************************************************************/

	public static function checkConnect($host, $user, $pwd, $db){
		/*
			Проверка в принципе возможности соединения по указанным реквизитам
		*/

		if(!$sql = @mysql_connect($host, $user, $pwd, true)) $return = false;
		elseif(!@mysql_select_db($db, $sql)) $return = false;
		else $return = true;

		if(is_resource($sql)) mysql_close($sql);
		return $return;
	}

	public static function getConnectMatrix($params = array()){
		$matrix['inCharset']['type'] = 'text';
		$matrix['inCharset']['text'] = '{Call:Lang:core:core:vkhodiashcha}';
		$values['inCharset'] = 'utf8';

		$matrix['outCharset']['type'] = 'text';
		$matrix['outCharset']['text'] = '{Call:Lang:core:core:ickhodiashch}';
		$values['outCharset'] = 'utf8';

		$matrix['cc']['type'] = 'text';
		$matrix['cc']['text'] = '{Call:Lang:core:core:sootnoshenie}';
		$values['cc'] = 'utf8_unicode_ci';

		return array($matrix, $values);
	}

	public static function getConnectParams(Moduleinterface $obj, $params){
		return $obj->fieldValues(array('inCharset', 'outCharset', 'cc'));
	}
}

?>