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



$GLOBALS['Core']->loadExtension('bill_hosting', 'servConnectCp');

class servConnectCpReseller extends servConnectCp{

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		$this->obj->values['vars']['pwd_type'] = $this->obj->values['pwd_type'];
		$http = $this->sendReq('resellerlist');

		if($http->getResponseCode() == '200' && regExp::Match('/scripts/addres', $http->getResponseBody())) return true;
		if($this->setErrorByHttp($http)) $this->setIntError($http);

		return false;
	}

	//Управление аккаунтами
	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		if($this->sendAddAcc($service, $params, array('reseller' => 1))) return $this->setResellerParams($service, $this->obj->values['acc_ident'.$params['id']], $params);
		return false;
	}

	protected function __ava__setResellerParams($service, $login, $params = array()){		if(empty($params['modify'])){			$eData = $this->obj->getOrderEntry($params['id']);
			$pkgData = $this->obj->serviceData($eData['service'], $eData['package']);
			$params['installData'] = Library::array_merge($params['installData'], $this->obj->sumParams($pkgData['vars'], $eData['extra']['params3'], $service));
		}
		$send = $this->insertSpecialParams($this->convertParams($service, $params['installData']), true);
		$send['res'] = $login;
		if(!empty($send['resnumlimitamt'])) $send['limits_number_of_accounts'] = 1;
		if(!empty($send['rslimit-disk']) || !empty($send['rsolimit-disk']) || !empty($send['rslimit-bw']) || !empty($send['rsolimit-bw'])) $send['limits_resources'] = 1;

		$http = $this->sendReq('editressv', $send, 'scripts2', 'POST');
		if(!regExp::Match("Modified reseller", $http->getResponse())){
			if($this->setErrorByHttp($http)) $this->setIntError($http);
			return false;
		}

		return true;
	}

	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$return = $this->sendModifyAcc($service, $params, array('reseller' => 1));
		foreach($return as $i => $e){			if($e){
				$this->sendReq('addres', array('res' => $i), 'scripts');				$return[$i] = $this->setResellerParams($service, $params['accs'][$i]['ident'], $params);			}		}

		return $return;
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/
	}



	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){		$data = parent::getInstallParams();
		//Лимиты
		$data[0]['limit_capt'] = array('type' => 'caption', 'text' => '{Call:Lang:modules:bill_hosting:limityakkaun}');
		$data[0]['resnumlimitamt'] = array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limitsozdava}', 'name' => 'userlimit', 'extra' => array('sort' => 300));
		$data[0]['rslimit-disk'] = array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limitdiskovo}', 'extra' => array('sort' => 300));

		$data[0]['rslimit-bw'] = array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limittrafika}', 'extra' => array('sort' => 300));
		$data[0]['rsolimit-disk'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:oversellingd}');
		$data[0]['rsolimit-bw'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:oversellingt}');

		//Настройки доступа к аккам
		$data[0]['priv_capt'] = array('type' => 'caption', 'text' => '{Call:Lang:modules:bill_hosting:upravlenieak}');
		$data[0]['acl-create-acct'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh}');
		$data[0]['acl-list-accts'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh1}');

		$data[0]['acl-suspend-acct'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh2}');
		$data[0]['acl-kill-acct'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh3}');
		$data[0]['acl-upgrade-account'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh4}');

		$data[0]['acl-passwd'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh5}');
		$data[0]['acl-clustering'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:gruppirovkaa}');
		$data[0]['acl-rearrange-accts'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh6}');

		$data[0]['acl-edit-account'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh7}');
		$data[0]['acl-limit-bandwidth'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh8}');
		$data[0]['acl-quota'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh9}');
		$data[0]['acl-res-cart'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh10}');

		//Управление пакетами
		$data[0]['pkgs_capt'] = array('type' => 'caption', 'text' => '{Call:Lang:modules:bill_hosting:upravleniepa}');
		$data[0]['acl-add-pkg'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh11}');
		$data[0]['acl-edit-pkg'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh12}');

		$data[0]['acl-viewglobalpackages'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh13}');
		$data[0]['acl-allow-addoncreate'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh14}');
		$data[0]['acl-allow-parkedcreate'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh15}');

		$data[0]['acl-add-pkg-shell'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh16}');
		$data[0]['acl-allow-unlimited-bw-pkgs'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh17}');
		$data[0]['acl-allow-unlimited-disk-pkgs'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh18}');

		$data[0]['acl-allow-unlimited-pkgs'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh19}');
		$data[0]['acl-add-pkg-ip'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh20}');

		//SSL-сертификаты
		$data[0]['ssl_capt'] = array('type' => 'caption', 'text' => '{Call:Lang:modules:bill_hosting:upravleniess}');
		$data[0]['acl-ssl-gencrt'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh21}');
		$data[0]['acl-ssl-buy'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh22}');
		$data[0]['acl-ssl'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh23}');

		//DNS
		$data[0]['dns_capt'] = array('type' => 'caption', 'text' => '{Call:Lang:modules:bill_hosting:upravleniedn}');
		$data[0]['acl-create-dns'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh24}');
		$data[0]['acl-edit-dns'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh25}');
		$data[0]['acl-kill-dns'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh26}');
		$data[0]['acl-park-dns'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh27}');

		//Прочее
		$data[0]['other_capt'] = array('type' => 'caption', 'text' => '{Call:Lang:modules:bill_hosting:prochienastr}');
		$data[0]['ownerself'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh28}');
		$data[0]['acl-restart'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh29}');
		$data[0]['acl-resftp'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh30}');

		$data[0]['acl-edit-mx'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh31}');
		$data[0]['acl-frontpage'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh32}');
		$data[0]['acl-disallow-shell'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:predotvrashc}');

		$data[0]['acl-stats'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh33}');
		$data[0]['acl-status'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh34}');
		$data[0]['acl-show-bandwidth'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh35}');

		$data[0]['acl-locale-edit'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh36}');
		$data[0]['acl-thirdparty'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:upravlenietr}');
		$data[0]['acl-mailcheck'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:otpravkapise}');

		$data[0]['acl-news'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:modifikatsii}');
		$data[0]['acl-demo-setup'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:resellermozh37}');
		$data[0]['acl-all'] = array('type' => 'checkbox', 'ch' => 1, 'text' => '{Call:Lang:modules:bill_hosting:rootdostupsu}');

		return array($data[0], '{Call:Lang:modules:bill_hosting:cpanelpakety}', 'bill_hosting');
	}
}

?>