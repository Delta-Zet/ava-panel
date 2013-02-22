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


class mod_bill_domains extends gen_bill_domains{

	protected function __init(){
		/*
			Дополнительная инициализация модуля
		 */

		$this->setContent('<link rel="stylesheet" type="text/css" href="'.$this->Core->getModuleTemplateUrl($this->mod).'style.css" />', 'head');
	}


	/********************************************************************************************************************************************************************

																		Формы заказа доменов

	*********************************************************************************************************************************************************************/

	protected function func_getDomainsForm(){
		/*
			Создает форму поиска доменов
		*/

		if(!empty($this->values['tlds'])) $tldsList = $this->values['tlds'];
		else{
			$bm = $this->getBillingMod();
			if(empty($this->values['service'])) $this->values['service'] = $bm->getServicesByExtension($this->mod);
			elseif(!is_array($this->values['service'])) $this->values['service'] = array($this->values['service']);

			$tldsList = array();
			foreach($this->values['service'] as $i => $e){
				foreach($bm->DB->columnFetch(array('order_packages', array('text', 'server_name'), "", "`service`='{$i}'")) as $e1){
					if($e1['server_name'] && empty($tldsList[$i][$e1['server_name']])) $tldsList[$i][$e1['server_name']] = $e1['text'];
				}
			}
		}

		$mObj = $this->Core->getMainModObj();
		$this->setContent(
			$this->Core->readBlockAndReplace(
				$this->Core->getModuleTemplatePath($this->mod).'domains_form.tmpl',
				empty($mObj->values['domainTemplate']) ? 'domain_form' : $mObj->values['domainTemplate'],
				$this,
				array(
					'tlds' => $tldsList,
					'mod' => $this->mod,
					'tld' => isset($mObj->values['tld']) ? $mObj->values['tld'] : array(),
					'domain' => isset($mObj->values['domain']) ? $mObj->values['domain'] : ''
				),
				'cover'
			)
		);
	}

	protected function func_searchDomain(){
		/*
			Поиск доменов
		*/

		if(empty($this->values['tld'])) $this->setError('tld', '{Call:Lang:modules:bill_domains:nezadanoniod}');
		if(empty($this->values['domain'])) $this->setError('', '{Call:Lang:modules:bill_domains:nezadanoimia}');
		if($this->errorMessages){
			$this->back('getDomainsForm');
			return false;
		}

		$this->setContent(
			$this->Core->readBlockAndReplace(
				$this->Core->getModuleTemplatePath($this->mod).'domains_search.tmpl',
				empty($this->values['domTmpl']) ? 'domains_search' : $this->values['domTmpl'],
				$this,
				array('domain' => $this->values['domain'], 'tld' => $this->values['tld'], 'mod' => $this->mod),
				'cover'
			)
		);

		$this->setMeta('{Call:Lang:modules:bill_domains:poiskdomena}');
	}

	protected function func_search(){
		/*
			Whois-проверка домена
		*/

		$this->setMeta('{Call:Lang:modules:bill_domains:proverkadome:'.Library::serialize(array($this->values['domain'])).'}');
		$this->Core->loadExtension('bill_domains', 'whois');
		$whois = new whois($this->values['domain'], $this->DB);
		$whois->send();

		$this->setContent($this->Core->readBlockAndReplace(
			$this->Core->getModuleTemplatePath($this->mod).'domains_search.tmpl',
			empty($this->values['dom_tmpl']) ? 'domains_search_result' : $this->values['dom_tmpl'],
			$this,
			array(
				'domain' => $this->values['domain'],
				'service' => $this->values['service'],
				'occupate' => $whois->getResultStatus(),
				'registrators' => $this->getRegistrators($this->values['service'], $this->getDomainZone($this->values['domain'])),
				'id' => $this->values['id'],
				'mod' => $this->mod
			)
		));

		$this->Core->setFlag('rawOutput');
	}

	protected function func_whois(){
		/*
			Выводит Whois-сведения по домену
		*/

		$this->Core->loadExtension('bill_domains', 'whois');
		$whois = new whois($this->values['domain'], $this->DB);
		$this->setContent(
			$this->Core->readAndReplace(
				$this->Core->getModuleTemplatePath($this->mod).'whois.tmpl',
				$this,
				array('whois' => regExp::win($whois->send()), 'domain' => $this->values['domain'])
			)
		);
	}

	protected function func_regDomains(){
		/*
			Функция сохраняет все выбранные домены, создает строку переадресации на функцию заказа
		*/

		if(empty($this->values['domain'])){
			$this->back('', '', '', 'Не выбран ни один домен');
			return false;
		}

		$redirect = $this->path.'?mod='.$this->getBillingMod()->getMod().'&func=order';
		$j = 0;

		foreach($this->values['domain'] as $i => $e){
			$redirect .= '&pkg_'.$this->values['service'][$i].'['.$j.']='.$this->values['pkg'][$i].'&ident_'.$this->values['service'][$i].'['.$j.']='.$e;
			$services[$this->values['service'][$i]] = $this->values['service'][$i];
			$j ++;
		}

		foreach($services as $i => $e) $redirect .= '&service[]='.$i;
		$this->redirect2($redirect);
	}


	/********************************************************************************************************************************************************************

																Управление доменами в аккаунте пользвателя

	*********************************************************************************************************************************************************************/

	protected function func_modifyNs(){
		/*
			Вывод формы для смены NS
		*/

		$sData = $this->getBillingMod()->getOrderedService($this->values['id']);
		if(!($userId = $this->Core->User->getUserId()) || (($clientId = $this->getBillingMod()->getClientId()) != $sData['client_id'])) throw new AVA_Access_Exception('{Call:Lang:modules:bill_domains:vyneavtorizo}');

		$this->setMeta('{Call:Lang:modules:bill_domains:smenitnsdome:'.Library::serialize(array($sData['ident'])).'}');
		$values = $this->callServerExtension($sData['server'], 'ns', $this->getBillingMod(), $sData['service'], array('ident' => $sData['ident']));

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'modifyNs2',
						'modifyNs2'
					),
					'ns',
					array(
						'id' => '',
						'prefix' => ''
					)
				),
				$values,
				array('id' => $this->values['id'])
			)
		);
	}

	protected function func_modifyNs2(){
		/*
			Собственно смена NS
		*/

		$sData = $this->getBillingMod()->getOrderedService($this->values['id']);
		if(!($userId = $this->Core->User->getUserId()) || (($clientId = $this->getBillingMod()->getClientId()) != $sData['client_id'])) throw new AVA_Access_Exception('{Call:Lang:modules:bill_domains:vyneavtorizo}');
		if(!$this->check()) return false;

		if($this->callServerExtension($sData['server'], 'newNs', $this->getBillingMod(), $sData['service'], array('ident' => $sData['ident'], 'values' => $this->values))){
			for($i = 1; $i <= 4; $i ++){
				if(isset($this->values['ns'.$i.'_'])) $sData['extra']['ns'.$i.'_'] = $this->values['ns'.$i.'_'];
			}

			$this->getBillingMod()->DB->Upd(array('order_services', array('extra' => $sData['extra']), "`id`='{$this->values['id']}'"));
			$this->refresh('myServices', false, $this->getBillingModName());
			return true;
		}
		else{
			$this->back('myServices', false, $this->getBillingModName());
			return false;
		}
	}

	protected function func_modifyWhois(){
		/*
			Новые данные для Whois
		*/

		$sData = $this->getBillingMod()->getOrderedService($this->values['id']);
		if(!($userId = $this->Core->User->getUserId()) || (($clientId = $this->getBillingMod()->getClientId()) != $sData['client_id'])) throw new AVA_Access_Exception('{Call:Lang:modules:bill_domains:vyneavtorizo}');

		$pkgData = $this->getBillingMod()->serviceData($sData['service'], $sData['package']);
		$this->setMeta('{Call:Lang:modules:bill_domains:izmeneniedom:'.Library::serialize(array($sData['ident'])).'}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'modifyWhois2',
						'modifyWhois2'
					),
					'blanks',
					array(
						'owners' => $this->getOwners($sData['client_id'], $this->getNeedOwnerType($pkgData['server_name'])),
						'prefix' => '',
						'id' => '',
						'pkg' => $pkgData['server_name'],
					)
				),
				$sData['extra'],
				array('id' => $this->values['id'])
			)
		);
	}

	protected function func_modifyWhois2(){
		/*
			Устанавливает параметры контакта
		*/

		$sData = $this->getBillingMod()->getOrderedService($this->values['id']);
		if(!($userId = $this->Core->User->getUserId()) || (($clientId = $this->getBillingMod()->getClientId()) != $sData['client_id'])) throw new AVA_Access_Exception('{Call:Lang:modules:bill_domains:vyneavtorizo}');

		$params['id'] = $this->values['id'];
		$params['ident'] = $sData['ident'];
		$params['domain_owner'] = $this->values['domain_owner'];

		$params['domain_owner_a'] = $this->values['domain_owner_a'];
		$params['domain_owner_b'] = $this->values['domain_owner_b'];
		$params['domain_owner_t'] = $this->values['domain_owner_t'];

		if($this->callServerExtension($sData['server'], 'setWhois', $this->getBillingMod(), $sData['service'], $params)){
			foreach(array('', '_a', '_b', '_t') as $e){
				if(isset($this->values['domain_owner'.$e])) $sData['extra']['domain_owner'.$e] = $this->values['domain_owner'.$e];
			}

			$this->getBillingMod()->DB->Upd(array('order_services', array('extra' => $sData['extra']), "`id`='{$this->values['id']}'"));
			$this->refresh('myServices', false, $this->getBillingModName());
			return true;
		}
		else{
			$this->back('myServices', false, $this->getBillingModName());
			return false;
		}
	}


	/********************************************************************************************************************************************************************

																			Анкеты по доменам

	*********************************************************************************************************************************************************************/

	protected function func_domainClientBlanks(){
		/*
			Список анкет
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($clientId = $this->getBillingMod()->getClientId())) throw new AVA_Access_Exception('{Call:Lang:modules:bill_domains:vyneavtorizo1:'.Library::serialize(array($userId)).'}');
		$this->setMeta('{Call:Lang:modules:bill_domains:anketyadmini}');

		$modifyData = array();
		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'new'){
				if($this->setNewBlank($this, $clientId)) $this->refresh('domainClientBlanks');
				return;
			}
			elseif($this->values['type_action'] == 'modify'){
				$values = $this->DB->rowFetch(array('domain_owners', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
				$modifyData['values'] = Library::unserialize($values['vars']);
				$modifyData['values']['correspond'] = Library::str2arrKeys($values['correspond']);
				$modifyData['values']['blankName'] = $values['name'];
			}
		}

		return $this->typicalMain(
			array(
				'name' => 'domain_owners',
				'caption' => '{Call:Lang:modules:bill_domains:dobavitanket}',
				'listTemplName' => 'users',
				'listParams' => array(
					'req' => array('domain_owners', '*', "`client_id`='$clientId'"),
					'actions' => array(
						'modify' => 'domainClientBlanks&type_action=modify'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:bill_domains:spisokanket}'
				),
				'modifyReq' => false,
				'modifyData' => $modifyData
			)
		);
	}
}

?>