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

class servConnectIsp extends serverHostingObject{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if($this->sendReq('usagestat')) return true;
		return false;
	}


	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function __ava__setAddPkgMatrix($service, $params = array()){
		/*
			Матрица для пакета
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'pkgdsc_'.$this->getParamsVar($service, 'ip') => array(
					'additional' => Library::array_merge($this->getIps(false), array('new' => '{Call:Lang:modules:bill_hosting:novyjip}', 'none' => '{Call:Lang:modules:bill_hosting:nesviazyvatn}'))
				)
			),
			array(),
			array(),
			'block2'
		);
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params);
	}

	protected function __ava__sendAddPkg($service, $params = array(), $type = ''){
		/*
			Добавляет пакет
		*/

		$sendParams = $this->insertSpecialParams($this->convertParams($service, $this->obj->values, 'pkgdsc_'));
		$sendParams['sok'] = 'yes';
		$sendParams['name'] = $this->obj->values['server_name'];
		$func = 'preset.edit';

		if($type == 'vds') $func = 'vdspreset.edit';		elseif($type == 'vdsreseller') $func = 'rslrtempl.edit';
		elseif($type == 'reseller') $sendParams['ptype'] = 'reseller';
		else $sendParams['ptype'] = 'user';

		if(is_array($result = $this->sendReq($func, $sendParams))){
			return true;
		}
		elseif($result == -1){
			$sendParams['elid'] = $this->obj->values['server_name'];
			if(is_array($this->sendReq($func, $sendParams))){				$this->setErrorParams(0);				return true;			}
		}

		return false;
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params);
	}

	protected function __ava__sendDelPkg($service, $params = array(), $func = 'preset.delete'){		/*
			Удаляет пакет
		*/

		$pkgs = array();
		foreach($params['pkgs'] as $i => $e){			$pkgs[] = $e['server_name'];		}

		if($this->sendReq($func, array('elid' => implode(', ', $pkgs)))) return Library::arrayFill($params['pkgs'], true);
		else return Library::arrayFill($params['pkgs'], false);
	}


	/********************************************************************************************************************************************************************

																		Формы для работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__setAccOrderMatrix($service, $params = array()){		if(!empty($params['fObj']->matrix[$params['prefix'].'ident'.$params['id']])){			$this->obj->addFormBlock(
				$params['fObj'],
				array(
					$params['prefix'].'ident'.$params['id'] => array(
						'comment' => 'Логин может содержать только латинские буквы в нижнем регистре и цифры, начинается с буквы',
						'warn_pattern' => '|^[a-z][a-z0-9]{3,10}$|',
					)
				),
				array(),
				array(),
				$params['bName']
			);
		}
	}

	public function __ava__setAddAccMatrix($service, $params = array()){		return $this->setAddAccMatrix2($service, $params);
	}

	protected function __ava__setAddAccMatrix2($service, $params = array()){
		/*
			Форма заказу
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				$params['prefix'].$this->getParamsVar($service, 'ip').$params['id'] => array(
					'additional' => Library::array_merge(
						$this->getIps(false),
						array('new' => '{Call:Lang:modules:bill_hosting:novyjip}', 'none' => '{Call:Lang:modules:bill_hosting:nesviazyvatn}')
					)
				)
			),
			array(),
			array(),
			$params['bName']
		);
	}


	/********************************************************************************************************************************************************************

																			Управление аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$reqParams['domain'] = $this->idna($eData['extra']['params1']['domain']);
		$reqParams['owner'] = $this->connectData['login'];
		return $this->sendAddAcc($service, $params, $reqParams);
	}

	public function sendAddAcc($service, $params = array(), $reqParams = array(), $restart = true, $func = 'user.edit', &$result = false){
		/*
			Создает аккаунт пользовотеля
		*/

		$eData = $this->obj->getOrderEntry($params['id']);
		$pkgData = $this->obj->serviceData($service, $eData['package']);
		$reqParams2 = $this->insertSpecialParams($this->convertParams($service, $params['installData']), true);

		if(method_exists($this, 'accAliaces') || method_exists($this, '__ava__accAliaces')) $reqParams2 = $this->accAliaces($reqParams2);
		$reqParams['preset'] = $pkgData['server_name'];
		$reqParams['email'] = $this->obj->getClientEml($eData['client_id']);

		$reqParams2['name'] = $eData['ident'];
		$reqParams2['passwd'] = $eData['extra']['params1']['pwd'];
		$reqParams2['sok'] = 'yes';

		if(is_array($result = $this->sendReq($func, Library::array_merge($reqParams2, $reqParams)))){
			if($restart) $this->restart();
			return true;
		}

		return false;
	}






































	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/



	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$reqParams['preset'] = $params['pkgData']['server_name'];
		return $this->sendModifyAcc($service, $params, $reqParams);
	}

	public function __ava__sendModifyAcc($service, $params = array(), $reqParams = array(), $func = 'user.edit', $pkgFunc = 'preset.edit'){		/*
			Непосредственно отправка запроса
		*/

		$reqParams = Library::array_merge($this->insertSpecialParams($this->convertParams($service, $params['installData']), true), $reqParams);
		$reqParams['sok'] = 'yes';
		if(!$params['modify']) $reqParams = Library::array_merge($this->getPkgParams($params['pkgData']['server_name'], $pkgFunc), $reqParams);		if(method_exists($this, 'accAliaces') || method_exists($this, '__ava__accAliaces')) $reqParams = $this->accAliaces($reqParams);

		foreach($params['accs'] as $i => $e){
			$reqParams['elid'] = $reqParams['name'] = $e['ident'];
			$return[$i] = is_array($this->sendReq($func, $reqParams)) ? true : false;
		}

		return $return;
	}

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

		return $this->sendMultiAccsReq($service, $params, 'user.delete');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'user.disable');
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'user.enable');
	}

	protected function __ava__sendMultiAccsReq($service, $params, $func){		/*
			Выполняет операцию со множеством акков
		*/
		$accs = array();
		foreach($params['accs'] as $i => $e){			$accs[] = $e['ident'];
		}

		if($this->sendReq($func, array('elid' => implode(', ', $accs)))) return Library::arrayFill($params['accs'], true);
		else return Library::arrayFill($params['accs'], false);
	}

	public function isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/
	}



	/********************************************************************************************************************************************************************

																		Служебные функции

	*********************************************************************************************************************************************************************/

	public function restart(){		/*
			Перезапукает ISP
		*/

		if(!$GLOBALS['Core']->getFlag('ispRestart')){
			$GLOBALS['Core']->setFlag('ispRestart');
			$this->sendReq('restart');
		}
	}

	private function getPkgParams($pkgName, $func){
		/*
			Возвращает параметры для отправки на создание акка
		*/

		$return = array();
		$params = $this->sendReq($func, array('elid' => $pkgName));
		foreach($params[0]['doc'] as $i => $e){			if($e !== '' && $e != 'elid' && $e != 'name') $return[$i] = $e;
			elseif($e === '') $return[$i] = 'on';		}
		return $return;
	}

	protected function __ava__sendReq($action, $vars = array()){
		/*
			Отправляет запрос
		*/

		$vars['out'] = 'xml';
		$vars['authinfo'] = $this->connectData['login'].':'.$this->connectData['pwd'];
		$vars['func'] = $action;
		return $this->parseResult($this->httpConnect($this->getHostAppendQuery('/manager/ispmgr'), array(), $vars, 'GET', 120));
	}

	protected function __ava__parseResult($httpObj){		/*
			Парсит результат
			возвращает:
				1 - если запрос удачный
				-1 - если объект существует
				false во всех иных случаях
		*/

		$response = XML::parseXML($httpObj->getResponseBody(), $attr);

		if(!$response){			$this->setErrorParams(7);
			return false;		}
		elseif(!empty($attr['doc']['error']['@attr']['code'])){			$this->setIntError($attr['doc']['error']['@attr']['code'], array($response, $attr));
			return ($attr['doc']['error']['@attr']['code'] == 2 || ($attr['doc']['error']['@attr']['code'] == 1 && regExp::Match('Dublicate key', $response['doc']['error']))) ? -1 : false;		}
		else return array($response, $attr);
	}

	protected function __ava__setIntError($code, $response){
		/*
			Устанавливает параметр внутренней ошибки запроса
		*/

		switch($code){			case '1': $this->setErrorParams('101', '{Call:Lang:modules:bill_hosting:vnutrenniaia}'); break;
			case '2': $this->setErrorParams('102', '{Call:Lang:modules:bill_hosting:obektuzhesus:'.Library::serialize(array($response['1']['doc']['error']['@attr']['obj'])).'}'); break;
			case '3': $this->setErrorParams('103', '{Call:Lang:modules:bill_hosting:obektnesushc:'.Library::serialize(array($response['1']['doc']['error']['@attr']['obj'])).'}'); break;
			case '4': $this->setErrorParams('104', '{Call:Lang:modules:bill_hosting:ukazanonedop:'.Library::serialize(array($response['1']['doc']['error']['@attr']['val'])).'}'); break;
			case '5': $this->setErrorParams('105', '{Call:Lang:modules:bill_hosting:prevyshenlim:'.Library::serialize(array((isset($response['1']['doc']['error']['@attr']['val']) ? $response['1']['doc']['error']['@attr']['val'] : $response['1']['doc']['error']['@attr']['obj']))).'}'); break;
			case '6': $this->setErrorParams('106', '{Call:Lang:modules:bill_hosting:uvasnetdostu}: '.$response['1']['doc']['error']['@attr']['obj']); break;
			case '7': $this->setErrorParams('107', '{Call:Lang:modules:bill_hosting:problemaslit}'); break;
			default:
				switch($response['0']['doc']['error']){					case '':
						$this->setErrorParams('11');
						break;
					case 'access deny':
						$this->setErrorParams('10');
						break;

					default:
						$this->setErrorParams(100, trim($response['0']['doc']['error']));
						break;
				}
		}	}

	protected function __ava__getIps($assigned = false, $shared = true, $free = true){		/*
			Возвращает список IP-адресов тех типов которые true
		*/

		$return = array();
		$ips = $this->sendReq('iplist');
		$ips = Library::isHash($ips['0']['doc']['elem']) ? array($ips['0']['doc']['elem']) : $ips['0']['doc']['elem'];

		foreach($ips as $i => $e){			if((!isset($e['stat']) && empty($e['usedby'])) || (isset($e['stat']) && (($assigned && $e['stat'] == 'assigned') || ($shared && $e['stat'] == 'shared') || ($free && $e['stat'] == 'free')))) $return[$e['name']] = $e['name'];		}
		return $return;
	}

	protected function formSelect($params, $key, $value){		/*
			Формирует массив для выпадающего списка по параметрам
		*/
		if(!is_array($params)) return array();
		elseif(library::isHash($params)) $params = array($params);
		$return = array();

		foreach($params as $i => $e){			$return[$e[$key]] = $e[$value];		}

		return $return;	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'ip' => array('type' => 'select', 'text' => 'IP'),
				'ip6' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:ipvadres}', 'noFunc' => true, 'extra' => array('sort' => 100), 'aacc' => 1, 'mpkg' => 0, 'pkg_list' => 0),
				'disklimit' => array('type' => 'text', 'unlimit' => '1000000', 'text' => '{Call:Lang:modules:bill_hosting:diskovaiakvo}', 'name' => 'quota'),
				'cpulimit' => array('type' => 'text', 'unlimit' => '100000', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalio2}')),
				'memlimit' => array('type' => 'text', 'unlimit' => '100000', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie1}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalio3}')),
				'proclimit' => array('type' => 'text', 'unlimit' => '100000', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie2}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalio4}')),
				'bandwidthlimit' => array('type' => 'text', 'unlimit' => '100000000', 'text' => '{Call:Lang:modules:bill_hosting:trafik}', 'name' => 'bandwidth'),
				'domainlimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvod}', 'name' => 'vdomains'),
				'webdomainlimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvow}', 'name' => 'nsubdomains'),
				'maillimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoe}', 'name' => 'nemails'),
				'maildomainlimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvom}'),
				'ftplimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvof}', 'name' => 'ftp'),
				'baselimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvob}', 'name' => 'mysql'),
				'baseuserlimit' => array('type' => 'text', 'unlimit' => '10000', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop2}'),
				'mysqlquerieslimit' => array('type' => 'text', 'unlimit' => '100000000', 'text' => '{Call:Lang:modules:bill_hosting:zaprosovkmys}'),
				'mysqlupdateslimit' => array('type' => 'text', 'unlimit' => '100000000', 'text' => '{Call:Lang:modules:bill_hosting:obnovlenijmy}'),
				'mysqlconnectlimit' => array('type' => 'text', 'unlimit' => '100000000', 'text' => '{Call:Lang:modules:bill_hosting:soedinenijsm}'),
				'mysqluserconnectlimit' => array('type' => 'text', 'unlimit' => '100000000', 'text' => 'Одновременных соединений к MySQL'),
				'shell' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass}', 'name' => 'ssh'),
				'ssl' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass1}'),
				'phpmod' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:phpkakmodula}'),
				'phpcgi' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:phpkakcgi}'),
				'phpfcgi' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:phpkakfastcg}'),
				'safemode' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:phpvrezhimes}', 'name' => 'php_safe_mode'),
				'cgi' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkacg}'),
				'ssi' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass2}'),
				'welcome' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:otpravitpism}', 'name' => 'notify')
			),
			'ISP Manager',
			'bill_hosting'
		);
	}
}

?>