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


class servconnectObject extends objectInterface{

	protected $connectData = array();
	protected $serverId;
	protected $extension;

	protected $obj;
	protected $sObj;
	protected $serverParams = array();
	protected $serverConf = array();
	protected $extensionParams = array();

	//Результат отправки http-запроса
	public $result;
	public $resultId = 0;
	public $transactionId;
	public $code;
	public $description;

	/*
		$code
		0 - Удачное выполнение
		1 - timeout или ошибка на уровне TCP/IP
		2 - Неизвестная ошибка
		3 - Ошибка http из категории 300
		4 - Ошибка http из категории 400
		5 - Ошибка http из категории 500
		6 - Ошибка в запросе
		7 - Ошибка в ответе

		Выше 10 - ошибки обрабатываемые внутри дрйвера соединения:
		10 - неправильный логин/пасс
	*/

	public function __construct($connectData, $serverId, $extension, moduleInterface $obj, serviceExtensionsObject $sObj){
		$this->connectData = $connectData;
		$this->extension = $extension;
		$this->connectData['parsedHost'] = parse_url($this->connectData['host']);

		$this->obj = $obj;
		$this->sObj = $sObj;
		if(!empty($this->connectData['vars']) && is_array($this->connectData['vars'])) $this->connectData = Library::array_merge($this->connectData, $this->connectData['vars']);
		if(method_exists($this, '__init') || method_exists($this, '__ava____init')) $this->__init();
	}


	/********************************************************************************************************************************************************************

																			Обработка запросов по http

	*********************************************************************************************************************************************************************/

	protected function __ava__httpConnect($url, $headers = array(), $vars = '', $method = 'GET', $timeout = 30){
		/*
			Создает подключение по http, возвращает ответ сервера. Вписывает данные ответа в лог
		*/

		$http = new httpClient($url, $method);
		if(is_array($vars)) $http->setVars($vars);
		else $http->setBody($vars);

		$http->prepareHeaders($headers);
		$http->Send($timeout);

		if(defined('SHOW_HTTP_REQS') && SHOW_HTTP_REQS > 0) $this->result .= $http->getRequest()."\n\n\n\n\n\n\n\n\n\n\n\n";
		$this->result .= $http->getResponse()."\n\n\n\n\n\n\n\n\n\n\n\n";
		return $http;
	}

	protected function __ava__getOnlyHost($host = false){
		$parts = $host ? parse_url($host) : $this->connectData['parsedHost'];
		if(!empty($parts['port'])) $parts['host'] .= ':'.$parts['port'];
		return $parts['scheme'].'://'.$parts['host'];
	}

	protected function __ava__getHostAppendQuery($q){
		$parts = $this->connectData['parsedHost'];
		if(empty($parts['path'])) $parts['path'] = '/';
		$parts['path'] = (empty($parts['query'])) ? $parts['path'] : $parts['path'].'?'.$parts['query'];
		$parts['path'] = (empty($parts['path']) || $parts['path'] == '/') ? $q : $parts['path'];

		if(!empty($parts['port'])) $parts['host'] .= ':'.$parts['port'];
		return $parts['scheme'].'://'.$parts['host'].$parts['path'];
	}


	protected function getAuthHeaders(){
		return array('Authorization' => 'Basic '.base64_encode($this->connectData['login'].":".$this->connectData['pwd']));
	}

	protected function __ava__setErrorByHttp($http){
		$httpCode = $http->getResponseCode();

		if(!$httpCode){ $this->setErrorParams(1); return false; }
		elseif($httpCode >= 500){ $this->setErrorParams(5); return false; }
		elseif($httpCode == 404){ $this->setErrorParams(4); return false; }
		elseif($httpCode == 401 || $httpCode == 403){ $this->setErrorParams(10); return false; }
		elseif($httpCode >= 400){ $this->setErrorParams(6); return false; }
		elseif($httpCode >= 300){ $this->setErrorParams(3); return false; }
		elseif($httpCode == 200 && !$this->code){ $this->setErrorParams(0); }

		return true;
	}

	protected function __ava__setErrorParams($code, $descript = ''){
		/*
			Параметры ошибки. Системой зарезервированы коды 1-99. Все ошибки расшифровываемые панелями начинаются с префикса 100
		*/

		if(!$descript){
			switch($code){
				case 0: $descript = '{Call:Lang:modules:billing:zaprosuspesh}'; break;
				case 1: $descript = '{Call:Lang:modules:billing:oshibkasoedi}'; break;
				case 2: $descript = '{Call:Lang:modules:billing:neopredelenn}'; break;
				case 3: $descript = '{Call:Lang:modules:billing:dostupkhttpa}'; break;
				case 4: $descript = '{Call:Lang:modules:billing:poukazannomu}'; break;
				case 5: $descript = '{Call:Lang:modules:billing:oshibkaudale}'; break;
				case 6: $descript = '{Call:Lang:modules:billing:nekorrektnyj}'; break;
				case 7: $descript = '{Call:Lang:modules:billing:nekorrektnyj1}'; break;
				case 8: $descript = '{Call:Lang:modules:billing:poterianatra}'; break;
				case 10: $descript = '{Call:Lang:modules:billing:nepravilnyel}'; break;
				case 11: $descript = '{Call:Lang:modules:billing:nedostatochn}'; break;
			}
		}

		$this->code = $code;
		$this->description = $descript;
	}

	public function __ava__saveResults($DB, $objType = '', $objId = 0){
		/*
			Сохраняет результат выполнения запроса
		*/

		$objId = is_array($objId) ? ','.implode($objId).',' : ','.$objId.',';
		$this->resultId = $DB->Ins(
			array(
				'server_reply',
				array(
					'date' => time(),
					'body' => $this->result,
					'connection_id' => $this->serverId,
					'object_type' => $objType,
					'object_id' => $objId,
					'code' => $this->code,
					'description' => $GLOBALS['Core']->paramReplaces($this->description, $this->obj)
				)
			)
		);

		return $this->resultId;
	}

	public function __ava__getConnectionResultId(){
		return $this->resultId;
	}


	/********************************************************************************************************************************************************************

																			Обработка параметровъ

	*********************************************************************************************************************************************************************/

	protected function __ava__convertParams($service, $sourceParams, $prefix = '', $postfix = ''){
		/*
			Создает массив параметров и их значений для панели
		*/

		$this->loadServerParams($service);
		$return = array();
		foreach($this->serverParams as $i => $e) if(isset($sourceParams[$prefix.$i.$postfix])) $return[$e['conformity']] = $sourceParams[$prefix.$i.$postfix];
		return $return;
	}

	protected function __ava__getParamsVar($service, $param){
		/*
			Возвращает имя переменной в форме
		*/

		$this->loadServerParams($service);
		return $this->serverConf[$param];
	}

	protected function __ava__getParamsValue($service, $param, $prf = '', $pstf = ''){
		/*
			Возвращает значение в форме для параметра
		*/

		if(!$var = $this->getParamsVar($service, $param)) return '';
		return empty($this->obj->values[$prf.$var.$pstf]) ? '' : $this->obj->values[$prf.$var.$pstf];
	}

	protected function loadServerExtensionParams(){
		if(!$this->extensionParams){
			$this->extensionParams = $this->obj->DB->rowFetch(array('service_extensions_connect', '*', "`mod`='{$this->extension}'"));
			$this->extensionParams['extra'] = Library::unserialize($this->extensionParams['extra']);
		}
	}

	protected function __ava__loadServerParams($service){
		$this->loadServerExtensionParams();
		if(!$this->serverParams){
			foreach($this->obj->DB->columnFetch(array('package_descripts', array('type', 'vars'), 'name', "`service`='".db_main::Quot($service)."' AND `cp` REGEXP (',{$this->extension},')")) as $i => $e){
				$e['vars'] = Library::unserialize($e['vars']);

				if(isset($e['vars']['extra']['cp_conformity_'.$this->extension])){
					$this->serverParams[$i]['type'] = $e['type'];
					$this->serverParams[$i]['conformity'] = $e['vars']['extra']['cp_conformity_'.$this->extension];
					$this->serverConf[$e['vars']['extra']['cp_conformity_'.$this->extension]] = $i;
				}
			}
		}

		return $this->serverParams;
	}
}

?>