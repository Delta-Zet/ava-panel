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

class servConnectCp extends serverHostingObject{

	public function setConnectMatrix($service = '', $params = array()){		/*
			Матрица для коннекта. Позволяет
				- Указать используется для коннекта пароль или хеш
		*/

		$params['fObj']->setMatrix(
			array(
				'pwd' => array('type' => 'textarea'),
				'pwd_type' => array(
					'text' => '{Call:Lang:modules:bill_hosting:tipparolia}',
					'type' => 'select',
					'additional' => array(
						'hash' => '{Call:Lang:modules:bill_hosting:kliuchudalen}',
						'pwd' => '{Call:Lang:modules:bill_hosting:paroladmina}'
					)
				)
			),
			'form'
		);

		$params['fObj']->matrixBlocks['form'] = Library::syncArraySeq(
			$params['fObj']->matrixBlocks['form'],
			array('name', 'extension', 'pwd_type', 'login', 'pwd', 'comment')
		);
	}

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		$this->obj->values['vars']['pwd_type'] = $this->obj->values['pwd_type'];
		$http = $this->sendReq('showversion');

		if($http->getResponseCode() == '200') return true;
		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}

	/*
		Создание мацриц
	*/

	public function __ava__setAddPkgMatrix($service, $params = array()){
		/*
			Мацрица формы добавления тарифа
			Позволяет выбрать:
				- cpMod (cPanel Theme)
				- Feature List
				- Language
		*/

		$this->obj->addFormBlock($params['fObj'], $this->getExtraMatrixParams($service, 'pkgdsc_'), array(), array(), 'block2');
		return true;
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		$sendParams = $this->insertSpecialParams($this->convertParams($service, $this->obj->values, 'pkgdsc_'));
		$sendParams['name'] = $this->obj->values['server_name'];

		$fullPkgName = $this->getPkgFullName($this->obj->values['server_name']);
		$http = $this->sendReq('addpkg', $sendParams);
		$result = $http->getResponse();

		if(regExp::Match("Created the package ".$fullPkgName, $result) || regExp::Match("Created the package ".$sendParams['name'], $result)){			if(regExp::Match("Created the package ".$fullPkgName, $result)) $this->obj->values['server_name'] = $fullPkgName;			return true;		}
		elseif(regExp::Match("The package ".$fullPkgName." already exists.", $result) || regExp::Match("The package ".$sendParams['name']." already exists.", $result)){
			$sendParams['edit'] = 'yes';
			$http = $this->sendReq('addpkg', $sendParams);
			$result = $http->getResponse();

			if(regExp::Match("Modified the package ".$fullPkgName, $result) || regExp::Match("Modified the package ".$sendParams['name'], $result)){				if(regExp::Match("Modified the package ".$fullPkgName, $result)) $this->obj->values['server_name'] = $fullPkgName;				return true;			}
		}

		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		$return = array();
		foreach($params['pkgs'] as $i => $e){
			$http = $this->sendReq('killpkg', array('pkg' => $e['server_name']), 'scripts');

			if(regExp::Match("The package was successfully deleted.", $http->getResponse())){
				$return[$i] = true;
			}
			else{				$return[$i] = false;				if($this->setErrorByHttp($http)) $this->setIntError($http);
			}
		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																		Формы для работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__setAccOrderMatrix($service, $params = array()){
		$matrix = array();
		foreach($this->getExtraMatrixParams($service, $params['prefix'], $params['id']) as $i => $e){
			$matrix[$i] = array('additional' => $e['additional']);
		}

		$this->obj->addFormBlock($params['fObj'], $matrix, array(), array(), $params['bName']);
	}

	public function __ava__setAddAccMatrix($service, $params = array()){
		$this->obj->addFormBlock($params['fObj'], $this->getIpList($service, $params['prefix'], $params['id']), array(), array(), $params['bName']);
	}


	/********************************************************************************************************************************************************************

																			Управление аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		return $this->sendAddAcc($service, $params);
	}

	protected function __ava__sendAddAcc($service, $params = array(), $extraParams = array()){
		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);
		$extraParams = Library::array_merge($this->insertSpecialParams($this->convertParams($service, $params['installData']), true), $extraParams);

		$extraParams['plan'] = $pkgData['server_name'];
		$extraParams['username'] = $eData['ident'];
		$extraParams['domain'] = $this->idna($eData['extra']['params1']['domain']);

		$extraParams['password'] = $eData['extra']['params1']['pwd'];
		$extraParams['contactemail'] = $this->obj->getClientEml($eData['client_id']);
		$extraParams['hasuseregns'] = 1;

		$http = $this->sendReq('wwwacct', $extraParams, 'scripts');
		if(regExp::Match("New Account Info", $http->getResponse())){
			return true;
		}

		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		$send = array('verify' => 'I understand this will irrevocably remove all the accounts that have been checked');
		foreach($params['accs'] as $i => $e) $send['acct-'.$e['ident']] = 1;
		$http = $this->sendReq('domultikill', $send, 'scripts2', 'POST');

		if(regExp::Match('All Account Terminations Complete', $http->getResponse())) return Library::arrayFill($params['accs'], true);
		else{
			if($this->setErrorByHttp($http)) $this->setIntError($http);
			return Library::arrayFill($params['accs'], false);
		}
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($params['accs'], 'account has been suspended', array('suspend-user' => 'Suspend'));
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($params['accs'], 'account is now active', array('unsuspend-user' => 'UnSuspend'), 'this account is partially suspended');
	}

	protected function __ava__sendMultiAccsReq($accs, $phrase, $params = array(), $phrase2 = ''){		/*
			Отправка многоаккаунтных запросов
		*/

//		$params['reason'] = $this->obj->values['reason'];
		$return = array();

		foreach($accs as $i => $e){
			$params['user'] = $e['ident'];
			$http = $this->sendReq('suspendacct', $params);

			if(regExp::Match($phrase, $http->getResponse()) || ($phrase2 && regExp::Match($phrase2, $http->getResponse()))) $return[$i] = true;
			else{
				$return[$i] = false;
				if($this->setErrorByHttp($http)) $this->setIntError($http);
			}
		}

		return $return;
	}



































	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/



	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		return $this->sendModifyAcc($service, $params);
	}

	protected function __ava__sendModifyAcc($service, $params = array(), $extraParams = array()){		$extraParams['pkg'] = $params['pkgData']['server_name'];
		foreach($params['accs'] as $i => $e) $extraParams['acct-'.$e['ident']] = 1;
		$http = $this->sendReq('domassmodify', $extraParams);

		if(!regExp::Match("All Modifications Complete", $http->getResponse())){			if($this->setErrorByHttp($http)) $this->setIntError($http);			return Library::arrayFill($params['accs'], false);		}		$return = Library::arrayFill($params['accs'], true);

		foreach($return as $i => $e) $this->sendReq('delres', array('res' => $i), 'scripts');

		if(!empty($params['modify'])){			foreach($this->insertSpecialParams($this->convertParams($service, $params['installData'])) as $i => $e){				$extraParams2[strtoupper($i)] = $e;			}

			foreach($params['accs'] as $i => $e){
				$extraParams2['user'] = $e['ident'];
				$http = $this->sendReq('saveedituser', $extraParams2, 'scripts', 'POST');

				if(regExp::Match("Account Modified", $http->getResponse())) $return[$i] = true;
				else{
					$return[$i] = false;
					if($this->setErrorByHttp($http)) $this->setIntError($http);
				}
			}
		}

		return $return;
	}

	public function __ava__prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/

		return true;
	}

	public function __ava__isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/

		$http = $this->sendReq('suspendlist', array(), 'scripts');
		return regExp::Match(">".$params['ident']."<", $http->getResponse());
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/

		return array();
	}



	/*
		Служебные
	*/

	protected function __ava__sendReq($action, $vars = array(), $path = 'scripts2', $method = 'GET'){
		/*
			Отправляет запрос
		*/

		return $this->httpConnect(
			$this->getOnlyHost($this->connectData['host']).'/'.$path.'/'.$action,
			$this->connectData['pwd_type'] == 'hash' ? $this->getAuthHeadersByKey() : $this->getAuthHeaders(),
			$vars,
			$method
		);
	}

	protected function getAuthHeadersByKey(){		return array(
			'Authorization' => 'WHM '.$this->connectData['login'].':'.regExp::Replace("/\s/", "", $this->connectData['pwd'], true),
			'Connection' => 'close'
		);
	}

	protected function __ava__setIntError(httpClient $httpObj){
		/*
			Устанавливает параметр внутренней ошибки запроса
		*/

		if(
			regExp::match("|<pre>(.+)</pre>|is", $httpObj->getResponseBody(), true, true, $m) ||
			regExp::match('|<td class="greenstatus">(.+)</td>|is', $httpObj->getResponseBody(), true, true, $m) ||
			regExp::match('|<td class="redstatus">(.+)</td>|is', $httpObj->getResponseBody(), true, true, $m)
		){			$this->setErrorParams(100, '{Call:Lang:modules:bill_hosting:oshibkaobrab:'.Library::serialize(array($m['1'])).'}');		}
		elseif(regExp::Match('Unauthorized copying is prohibited', $httpObj->getResponseBody())) $this->setErrorParams(110, '{Call:Lang:modules:bill_hosting:ehtotfunktsi}');
		elseif(regExp::Match("Sorry, that's an invalid domain", $httpObj->getResponseBody())) $this->setErrorParams(101, '{Call:Lang:modules:bill_hosting:nekorrektnou}');
		elseif(regExp::Match("Sorry, a group for that username already exists", $httpObj->getResponseBody())) $this->setErrorParams(102, '{Call:Lang:modules:bill_hosting:takojloginuz}');
		else $this->setErrorParams(2);
	}

	protected function __ava__getPkgFullName($name){		/*
			Возвращает имя ТП на сервере вместе с префиксм
		*/

		if(regExp::Match("/^".$this->connectData['login']."_/", $name, true)) return $name;
		else return $this->connectData['login'].'_'.$name;	}

	protected function __ava__getExtraMatrixParams($service, $prefix, $id = ''){		$http = $this->sendReq('addpkgform', array(), 'scripts');
		$text = $http->getResponse();
		$matrix = array();

		foreach(array('cpmod' => 'cPanel Theme', 'featurelist' => 'Feature List', 'language' => 'Language') as $i => $e){
			regExp::match('|<select name="'.$i.'">(.+)</select>|iUs', $text, true, true, $m);

			if(!empty($m['0'])){
				$i = $this->getParamsVar($service, $i);
				$vars = regExp::matchAll('|<option[^>]+value="([^"]+)"[^>]*>([^>]+)</option>|iUs', $m['0']);
				if(empty($vars['2'])) $vars = regExp::matchAll('|<option[^>]*>(([^>]+))</option>|iUs', $m['0']);
				foreach($vars['0'] as $i1 => $e1) $matrix[$prefix.$i.$id]['additional'][$vars['1'][$i1]] = $vars['2'][$i1];
			}
		}

		return $matrix;
	}

	protected function __ava__getIPList($service, $prefix, $id = ''){		$var = $this->getParamsVar($service, 'customip');
		$matrix[$prefix.$var.$id]['additional'][''] = '{Call:Lang:modules:bill_hosting:nenaznachat}';

		$http = $this->sendReq('wwwacctform', array(), 'scripts');
		$text = $http->getResponse();
		regExp::match('|<select name="customip">(.+)</select>|iUs', $text, true, true, $m);

		if(!empty($m['0'])){
			$vars = regExp::matchAll('|<option[^>]+value="([^"]+)"[^>]*>([^>]+)</option>|iUs', $m['0']);
			if(empty($vars['2'])) $vars = regExp::matchAll('|<option[^>]*>(([^>]+))</option>|iUs', $m['0']);
			foreach($vars['0'] as $i => $e) $matrix[$prefix.$var.$id]['additional'][$vars['1'][$i]] = $vars['2'][$i];
		}

		return $matrix;
	}



	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'customip' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:naznachitvyd}', 'name' => 'customip', 'aacc' => 1, 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0),
				'mxcheck' => array('type' => 'select', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:nastrojkamar}', 'aacc' => 1, 'mpkg' => 0, 'extra' => array('additional' => array('auto' => '{Call:Lang:modules:bill_hosting:avtomatiches}', 'local' => '{Call:Lang:modules:bill_hosting:lokalnyjkomp}', 'secondary' => '{Call:Lang:modules:bill_hosting:rezervnyjkom}', 'remote' => '{Call:Lang:modules:bill_hosting:udalennyjkom}'))),
				'cpmod' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:tema}', 'name' => 'skin', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1),
				'featurelist' => array('type' => 'select', 'text' => 'Feature List', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1),
				'language' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:iazyk}', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1),
				'quota' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:diskovaiakvo}'),
				'bwlimit' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:trafik}', 'name' => 'bandwidth'),
				'maxaddon' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvod}', 'name' => 'vdomains'),
				'maxsub' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvos}', 'name' => 'nsubdomains'),
				'maxpark' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop}', 'name' => 'domainptr'),
				'maxftp' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvof}', 'name' => 'ftp'),
				'maxsql' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvob}', 'name' => 'mysql'),
				'maxpop' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoe}', 'name' => 'nemails'),
				'maxlst' => array('type' => 'text', 'unlimit' => 'unlimited', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvol}', 'name' => 'nemailml'),
				'ip' => array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:vydelennyjip}', 'name' => 'dedicate_ip'),
				'cgi' => array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:podderzhkacg}'),
				'hasshell' => array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass}', 'name' => 'ssh'),
				'frontpage' => array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:podderzhkafr}', 'name' => 'fp'),
				'useregns' => array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:ispolzovatdn}', 'aacc' => 1, 'opkg' => 1, 'pkg_list' => 0),
				'forcedns' => array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:perepisatsus}', 'aacc' => 1, 'opkg' => 1, 'pkg_list' => 0),
			),
			'cPanel',
			'bill_hosting'
		);
	}
}

?>