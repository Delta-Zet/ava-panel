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



class API extends objectInterface{

	/*
		API использует XML-интерфейс
		Общий формат запросов:

			<request>
				<version>0.0.0.0</version>
				<auth>
					<login></login>
					<pwd></pwd>
				</auth>
				<params>
					<mod></mod>
					<func></func>
					<id></id>
					...
				</params>
			</request>

		version, params, mod, func, auth - обязательные параметры всегда


		Общий формат правильного ответа:

			<response>
				<status>true</status>
				<result>
					<entry></entry>
					<entry></entry>
					<entry></entry>
					<error>
						<code></code>
						<message></message>
					</error>
					<error>
						<code></code>
						<message></message>
					</error>
				</result>
				<output>
					Вывод HTML если есть
				</output>
			</response>

		status - обязательное поле
		В result могут быть некритичные ошибки


		Общий формат ответа с сообщением об ошибке

			<response>
				<status>false</status>
				<result>
					<error>
						<code></code>
						<message></message>
					</error>
					<error>
						<code></code>
						<message></message>
					</error>
				</result>
				<output>
					Вывод HTML если есть
				</output>
			</response>

		status - обязательное поле. Сообщения об ошибках будут только если они есть



		Коды ошибок:

		 0: Некритичные ошибки. При удачном выполнении запроса могут быть только они.
		 1: Ошибки не завершающиеся исключением
		 2: Неопределенная ошибка
		 3: Доступ к API по этой версии протокола не поддерживается
		 4: Доступ к API запрещен
		 5: Доступ к API временно запрещен
		 6: Некорректный запрос. Формат правильный, но отсутствуют обязательные позиции
		 10: Неправильные логин или пароль
		 11: Запрос к несуществующему функционалу
		 12: Запрос к функционалу к которому у клиента нет доступа
		 20: Объект не найден
		 30: Ошибка разбора XML из запроса
		 40: Внутренняя ошибка. Ошибка работы с файлом
		 50: Внутренняя ошибка. Ошибка работы с шаблоном
		 60: Внутренняя ошибка. Ошибка работы с БД
		 100: Общая ошибка системы, завершенная исключением
		 Свыше 100 - Устанавливаются модулем

	*/
	public $Core;
	public $DB;

	private $rawInput;					//Чистый ввод
	private $version;
	private $login;
	private $pwd;
	private $mod;
	private $func;
	private $params;

	private $auth;
	private $errors = array();


	public function __construct(){		/*
			Установка всех параметров API
		*/

		$this->Core = $GLOBALS['Core'];
		$this->DB = $this->Core->DB;	}

	public function __ava__loadInput($input){		/*
			Считывает весь ввод переданный http-запросом
		*/

		$this->rawInput = $input;
		$requestData = XML::parseXML($text);

		if(empty($requestData['request']['version'])) $this->setError(6, '{Call:Lang:core:core:neukazanaver}');		elseif($requestData['request']['version'] != '0.0.0.0') $this->setError(6, '{Call:Lang:core:core:ehtaversiiap}');

		if(empty($requestData['request']['auth']['login'])) $this->setError(6, '{Call:Lang:core:core:neukazanlogi}');
		if(empty($requestData['request']['auth']['pwd'])) $this->setError(6, '{Call:Lang:core:core:neukazanparo}');
		if(empty($requestData['request']['auth']['type'])) $this->setError(6, '{Call:Lang:core:core:neukazantipp}');
		elseif($requestData['request']['auth']['type'] != 'user' && $requestData['request']['auth']['type'] != 'admin') $this->setError(6, '{Call:Lang:core:core:tippolzovate}');

		if(empty($requestData['request']['params'])) $this->setError(6, '{Call:Lang:core:core:neukazanypar}');
		else{
			if(empty($requestData['request']['params']['mod'])) $this->setError(6, '{Call:Lang:core:core:neukazanmodu}');
			if(empty($requestData['request']['params']['func'])) $this->setError(6, '{Call:Lang:core:core:neukazanafun}');
		}

		if(!$this->errors){
			$this->version = $requestData['request']['version'];
			$this->login = $requestData['request']['auth']['login'];
			$this->pwd = $requestData['request']['auth']['pwd'];
			$this->type = $requestData['request']['auth']['type'];

			$this->mod = $requestData['request']['params']['mod'];
			$this->func = $requestData['request']['params']['func'];
			$this->params = $requestData['request']['params'];
			return true;
		}

		return false;
	}

	public function auth(){		/*
			Метод проверяет что пользователь существует, и что у него правильные логин и пароль
		*/

		if($this->login && $this->pwd && $this->type){			$authObj = new mod_main('main', 'main', $this->DB, array('login' => $this->login, 'pwd' => $this->pwd));
			if($this->type == 'admin'){
				$this->Core->User->authAdmin($authObj);
				$this->Core->User->adminAccess();
			}
			elseif($this->type == 'user'){
				$this->Core->User->auth($authObj);
				$this->Core->User->userAccess();
			}

			if(!$authObj->check()){				$this->setError(10, '{Call:Lang:core:core:nepravilnyel}');
				return false;			}

			$accessType = $this->Core->getParam('apiAccessType');
			$id = $this->Core->User->getUserId();
			$ip = $this->Core->getGPCVar('s', 'REMOTE_ADDR');

			if(
				($accessType == 'disallow') &&
				$this->DB->cellFetch(
					array(
						'api_access_ip',
						'id',
						db_main::q("#0 REGEXP (`ip`) AND `type`!='disallow' AND (!`user_id` OR `user_id`=#1)", array($ip, $id))
					)
				)
			){
				//Если доступ запрещен со всех IP кроме разрешенных и разрешенный IP есть в списке, то доступ разрешаем

				$this->auth = true;
				return true;
			}
			elseif($accessType == 'disallow'){
				$this->setError(4, '{Call:Lang:core:core:dostupkapiza}');
				return false;
			}
			elseif(
				($accessType != 'disallow') &&
				$this->DB->cellFetch(
					array(
						'api_access_ip',
						'id',
						db_main::q("#0 REGEXP (`ip`) AND `type`='disallow' AND (!`user_id` OR `user_id`=#1)", array($ip, $id))
					)
				)
			){
				//Если доступ разрешен со всех IP кроме запрещенных и запрещенный IP есть в списке, то доступ запрещаем

				$this->setError(4, '{Call:Lang:core:core:dostupkapiza}');
				return false;
			}

			$this->auth = true;
			return true;
		}

		return false;	}

	public function callFunc(){
		/*
			Обращается к определенной функции через API
		*/

		if($this->auth && $this->mod && $this->func){

			$modName = 'api_'.$this->mod;
			$obj = new $modName($this, $this->type, $this->mod, $this->func, $this->params);
			return $obj->callFunc();
		}

		return false;
	}

	public function __ava__getXMLOutput($result, $buffer){		/*
			Вывод в формате XML
		*/

		$return['response'] = array('status' => 'false', 'result' => array(), 'output' => $buffer);

		if(($result === false) || $this->issetFatalErrors()){			//Если результат неудачный

			$return['response']['result']['error'] = $this->errors;		}
		else{			//Если удачный

			$return['response']['result']['return'] = $result;			$return['response']['result']['error'] = $this->errors;		}

		return XML::getXML($return);	}

	public function issetFatalErrors(){		/*
			Проверяет существуют ли fatal errors
		*/

		foreach($this->errors as $i => $e){			if($e['code']) return true;		}

		return false;	}

	public function __ava__setError($code, $msg){		$this->errors[] = array('code' => $code, 'message' => $msg);	}

	public function getErrors(){
		return $this->errors;
	}
}

?>