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



$GLOBALS['Core']->loadExtension('bill_hosting', 'serverHostingObject');

class servconnectDa extends serverHostingObject{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if($this->getIps()) return true;

		$http = $this->sendReq('/CMD_API_SHOW_ADMINS');
		if(regExp::Match('list[]', $http->getResponse())){
			return true;
		}

		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}



	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	/*
		Создание мацриц
	*/

	public function __ava__setAddPkgMatrix($service, $params = array()){
		/*
			Матрица для пакета
		*/

		$matrix = array(
			'pkgdsc_'.$this->getParamsVar($service, 'ip') => array(
				'additional' => $this->getIps(),
				'warn' => '{Call:Lang:modules:bill_hosting:vyneukazalii}'
			)
		);

		return $this->insPkgMatrix($service, '/CMD_SHOW_USER_PACKAGE', $params, $matrix);
	}

	protected function __ava__insPkgMatrix($service, $path, $params = array(), $matrix = array()){		/*
			Устанавливает матрицу
		*/
		$pData = $this->obj->packageDataById($service, $this->obj->values['id']);
		foreach($this->getSkinsAndLangs($service, $path, array('package' => $pData['server_name'])) as $i => $e){
			$matrix['pkgdsc_'.$this->getParamsVar($service, $i)] = array('additional' => $e, 'type' => 'select');
		}

		$this->obj->addFormBlock($params['fObj'], $matrix, array(), array(), 'block2');
		return true;
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params, '/CMD_MANAGE_USER_PACKAGES', '/CMD_API_PACKAGES_USER');
	}

	protected function __ava__sendAddPkg($service, $params, $path1, $path2){		/*
			Собственно отправка запроса на создание
		*/
		$params = $this->insertSpecialParams($this->convertParams($service, $this->obj->values, 'pkgdsc_'));
		$params['add'] = 'Save';
		$params['packagename'] = $this->obj->values['server_name'];

		$http = $this->sendReq($path1, $params, 'POST');
		$result = $this->sendReq($path2)->getResponseBody();
		parse_str($result, $result);

		if(!empty($result['list']) && in_array($this->obj->values['server_name'], $result['list'])){
			return true;
		}

		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params, '/CMD_MANAGE_USER_PACKAGES', '/CMD_API_PACKAGES_USER');
	}

	protected function __ava__sendDelPkg($service, $params, $path1, $path2){		/*
			Собственно удаление. Введено для совместимости с реселлерами
		*/
		$reqParams = array('delete' => 'Delete');
		$return = Library::arrayFill($params['pkgs'], false);
		$j = 0;

		foreach($params['pkgs'] as $i => $e){
			$reqParams['delete'.$j] = $e['server_name'];
			$j ++;
		}

		$this->sendReq($path1, $reqParams, 'POST');
		$http = $this->sendReq($path2);
		$body = trim($http->getResponseBody());

		if(!$body && $http->getResponseCode() == '200') $result = array('list' => array());
		elseif($http->getResponseCode() == '200') parse_str($body, $result);
		else $result = false;

		if(!is_array($result) || !isset($result['list'])){
			if($this->setErrorByHttp($http)) $this->setIntError($http);
			return $return;
		}

		foreach($params['pkgs'] as $i1 => $e1){
			if(!in_array($e1['server_name'], $result['list'])){
				$return[$i1] = true;
			}
		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																	Формы для работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__setAccOrderMatrix($service, $params = array()){
		/*
			Форма заказа услуги
		*/

		$pkgData = $this->obj->getPkgByOrderEntry($params['eId']);
		$matrix = array(
			$params['prefix'].'ident'.$params['id'] => array(
				'comment' => 'Допускаются буквы английского алфавита в нижнем регистре и цифры, от 3 до 10 символов',
				'warn_pattern' => '/^[a-z0-9]{3,10}$/'
			)
		);

		foreach($this->getSkinsAndLangs($service, '/CMD_SHOW_USER_PACKAGE', array('package' => $pkgData['server_name'])) as $i => $e){
			$matrix[$params['prefix'].$this->getParamsVar($service, $i).$params['id']] = array('additional' => $e);
		}
		$this->obj->addFormBlock($params['fObj'], $matrix, array(), array(), $params['bName']);
	}

	public function __ava__checkAccOrderMatrix($service, $params = array()){
		/*
			Проверка заказа
		*/

		$http = $this->sendReq('/CMD_API_DOMAIN_OWNERS');
		$result = Library::parseStr(trim($http->getResponseBody()));

		if(!empty($result[regExp::replace(".", '_', $params['values'][$params['prefix'].'domain'.$params['id']])])){
			$this->obj->setError($params['prefix'].'domain'.$params['id'], '{Call:Lang:modules:bill_hosting:takojdomenuz}');
		}

		if(!empty($params['values'][$params['prefix'].'ident'.$params['id']]) && !$this->loginIsEmpty($params['values'][$params['prefix'].'ident'.$params['id']])){
			$this->obj->setError($params['prefix'].'ident'.$params['id'], '{Call:Lang:modules:bill_hosting:takojloginuz1}');
		}
	}

	protected function __ava__loginIsEmpty($login){
		$http = $this->sendReq('/CMD_API_SHOW_USER_USAGE', array('user' => $login));
		$result = Library::parseStr(trim($http->getResponseBody()));
		if($result && empty($result['error'])) return false;
		return true;
	}

	public function __ava__setAddAccMatrix($service, $params = array()){
		/*
			Установка матрицы добавления услуги админом
		*/

		$this->obj->addFormBlock($params['fObj'], array($params['prefix'].$this->getParamsVar($service, 'ip').$params['id'] => array('additional' => $this->getIps())), array(), array(), $params['bName']);
	}


	/********************************************************************************************************************************************************************

																			Управление аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		return $this->sendAddAcc($service, $params, '/CMD_ACCOUNT_USER');
	}

	protected function __ava__sendAddAcc($service, $params, $path){
		/*
			Собственно создание акка. Введено для совместимости с реселлерами
		*/

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);
		$reqParams = $this->insertSpecialParams($this->convertParams($service, $params['installData']), true);

		$reqParams['add'] = 'Submit';
		$reqParams['action'] = 'create';
		$reqParams['username'] = $eData['ident'];

		$reqParams['passwd'] = $eData['extra']['params1']['pwd'];
		$reqParams['passwd2'] = $eData['extra']['params1']['pwd'];
		$reqParams['email'] = $this->obj->getClientEml($eData['client_id']);

		$reqParams['domain'] = $this->idna($eData['extra']['params1']['domain']);
		if(empty($params['modify'])) $reqParams['package'] = $pkgData['server_name'];
		$http = $this->sendReq($path, $reqParams);

		if(regExp::Match('Unix User created successfully', $http->getResponseBody()) || regExp::Match('|User \w+ created|is', $http->getResponseBody(), true, true) || regExp::Match('Пользователь создан', $http->getResponseBody())) return true;
		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}

	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		return $this->sendModifyAcc($service, $params, '/CMD_MODIFY_USER');
	}

	protected function __ava__sendModifyAcc($service, $params, $path){
		/*
			Отправка запроса на смену акка
		*/

		$reqParams = $this->insertSpecialParams($this->convertParams($service, $params['installData']));
		if(!$params['modify']){
			$reqParams['package'] = $params['pkgData']['server_name'];
			$reqParams['action'] = 'package';
		}
		else $reqParams['action'] = 'customize';

		foreach($params['accs'] as $i => $e){
			$reqParams['user'] = $e['ident'];
			$http = $this->sendReq($path, $reqParams);

			if(regExp::Match('User Modified', $http->getResponseBody()) || regExp::Match('{Call:Lang:modules:bill_hosting:polzovateliz}', $http->getResponseBody()) || ($path == '/CMD_MODIFY_RESELLER' && $http->getResponseCode() == '302')) $return[$i] = true;
			else{
				if($this->setErrorByHttp($http)) $this->setIntError($http);
				$return[$i] = false;
			}
		}

		return $return;
	}















































	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	//Мацрицы для пользовательского акка





	public function __ava__prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/

		return true;
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		return $this->sendMultiAccsReq($service, $params, '/CMD_SELECT_USERS', array('confirmed' => 'Confirm', 'delete' => 'yes'), 'delete');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, '/CMD_SELECT_USERS', array('suspend' => 'Suspend'), 'suspend');
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, '/CMD_SELECT_USERS', array('suspend' => 'Unsuspend'), 'unsuspend');
	}

	protected function __ava__sendMultiAccsReq($service, $params, $path, $req, $type){		/*
			Операции над множеством акков
		*/
		$return = Library::arrayFill($params['accs'], false);
		$j = 0;

		foreach($params['accs'] as $i => $e){			$req['select'.$j] = $e['ident'];
			$j++;
		}

		$http = $this->sendReq($path, $req);
		if($type == 'delete') $isset = $this->listAccs($service);

		if($type == 'suspendreseller' || $type == 'unsuspendreseller') $return = Library::arrayFill($params['accs'], true);
		else{
			foreach($params['accs'] as $i => $e){
				if(
					(($type == 'delete') && empty($isset[$e['ident']])) ||
					(($type == 'suspend') && ($this->isSuspendAcc($service, $e) == 1)) ||
					(($type == 'unsuspend') && ($this->isSuspendAcc($service, $e) == -1))
				) $return[$i] = true;
				else{					$this->setErrorParams(2);
					$return[$i] = false;				}
			}
		}

		return $return;
	}

	public function __ava__isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
			Возвращает 1 - если да, -1 если нет, false - если ХЗ и -2 если такого вообще нет
		*/

		return $this->sendIsSuspendAcc($service, $params, '/CMD_API_SHOW_USER_CONFIG');
	}

	protected function __ava__sendIsSuspendAcc($service, $params, $path){
		$reqParams = array( 'user' => $params['ident'] );

		if(!$http = $this->sendReq($path, $reqParams)) return false;
		elseif($http->getResponseCode() != '200'){
			$this->setErrorByHttp($http);
			return false;
		}

		parse_str($http->getResponseBody(), $result);

		if(empty($result['suspended'])) return false;
		elseif($result['suspended'] == 'yes') return 1;
		elseif($result['suspended'] == 'no') return -1;
		else return false;
	}

	public function __ava__listAccs($service, $params = array()){
		/*
			Тупо список логинов без параметров
		*/

		return $this->sendListAccs($service, $params, '/CMD_API_SHOW_USERS');
	}

	protected function __ava__sendListAccs($service, $params, $path){
		/*
			Усе акки
		*/

		if(!$http = $this->sendReq($path)) return false;
		$body = trim($http->getResponseBody());

		if(!$body && $http->getResponseCode() == '200') $result = array('list' => array());
		elseif($http->getResponseCode() == '200') parse_str($body, $result);
		else $result = array();

		if(!isset($result['list'])){
			if($this->setErrorByHttp($http)) $this->setIntError($http);
			return array();
		}

		$return = array();
		foreach($result['list'] as $i => $e){
			$return[$e] = $e;
		}

		return $return;
	}



	/********************************************************************************************************************************************************************

																		Служебные функции

	*********************************************************************************************************************************************************************/

	protected function getIps(){
		if(!$http = $this->sendReq('/CMD_API_SHOW_RESELLER_IPS')) return false;
		parse_str($http->getResponseBody(), $ips);
		if(empty($ips['list'])) return array();

		$return = array();
		foreach($ips['list'] as $i => $e){
			$return[$e] = $e;
		}

		return $return;
	}

	protected function __ava__getSkinsAndLangs($service, $path, $req = array()){		/*
			Список скинов и языков
		*/

		$fields = array('skin' => array(), 'language' => array());
		$result = $this->sendReq($path, $req)->getResponseBody();

		foreach($fields as $i => $e){
			regExp::match('|<select[^>]*'.$i.'[^>]*>(.+)</select\s*>|iUs', $result, true, true, $m);

			if(!empty($m['0'])){
				$vars = regExp::matchAll('|<option[^>]+value="([^"]+)"[^>]*>([^>]+)</option\s*>|iUs', $m['0']);
				foreach($vars['0'] as $i1 => $e1) $fields[$i][$vars['1'][$i1]] = $vars['2'][$i1];
			}
		}

		if(!$fields['skin'] && !$fields['language'] && $req) return $this->getSkinsAndLangs($service, $path);
		return $fields;
	}

	protected function __ava__sendReq($path, $vars = array(), $method = 'GET'){
		/*
			Отправляет запрос
		*/

		return $this->httpConnect($this->getOnlyHost($this->connectData['host']).$path, $this->getAuthHeaders(), $vars, $method);
	}

	protected function __ava__setIntError(httpClient $httpObj){
		/*
			Устанавливает параметр внутренней ошибки запроса
		*/

		if($httpObj->getResponseHeadParam('X-DirectAdmin') == 'unauthorized') $this->setErrorParams('10');
		elseif($m = regExp::matchAll('|<p align="center">(.+)</p>|iUs', $httpObj->getResponseBody())){
			$this->setErrorParams('100', implode(': ', $m['1']));		}
		else $this->setErrorParams(2);
	}



	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'ip' => array('type' => 'select', 'text' => 'IP', 'aacc' => 1, 'mpkg' => 0, 'pkg_list' => 0),
				'language' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:iazyk}', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1),
				'skin' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:skin}', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1),
				'quota' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'uquota', 'text' => '{Call:Lang:modules:bill_hosting:diskovaiakvo1}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalio}')),
				'bandwidth' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'ubandwidth', 'text' => '{Call:Lang:modules:bill_hosting:trafikmb}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalio1}')),
				'vdomains' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'uvdomains', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvod}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalic}')),
				'nsubdomains' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'unsubdomains', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvos}'),
				'domainptr' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'udomainptr', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvod1}'),
				'nemails' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'unemails', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoe}'),
				'nemailf' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'unemailf', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop1}'),
				'nemailml' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'unemailml', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvol}'),
				'nemailr' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'unemailr', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoa}'),
				'mysql' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'umysql', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvob}'),
				'ftp' => array('type' => 'text', 'unlimit' => 'ON', 'unlimitAlias' => 'uftp', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvof}'),
				'aftp' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:anonimnyjftp}'),
				'cgi' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkacg}'),
				'php' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkaph}'),
				'spam' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:nastrojkaspa}'),
				'ssl' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass1}'),
				'ssh' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass}'),
				'cron' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkacr}'),
				'sysinfo' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:dostupksiste}'),
				'dnscontrol' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:upravleniedn}'),
				'catchall' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:sborshchikvs}'),
				'suspend_at_limit' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:zablokirovat}', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'extra' => array('value' => 1)),
				'notify' => array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:otpravitpism}', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'extra' => array('value' => 1))
			),
			'DirectAdmin',
			'bill_hosting'
		);
	}
}

?>