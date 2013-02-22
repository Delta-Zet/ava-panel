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


class installServiceObject extends InstallModuleObject{
	/*
		Общий класс для инсталляторов услуг
	*/

	public $billObj;			//Объект биллинга
	public $ext;				//Имя расширения
	public $setType = 'Ins';
	private $serviceId = array();

	public function __construct($DB, $obj, $prefix = false, $params = array()){
		$this->DB = $DB;
		$this->obj = $obj;
		$this->params = $params;

		if($prefix){
			$this->prefix = $prefix;
			$this->iObj = $this->obj->Core->callModule($prefix);
		}

		$this->billObj = $GLOBALS['Core']->callModule(empty($this->obj->values['united_billing']) ? $GLOBALS['Core']->getUnitedModule($this->prefix, 'billing') : $this->obj->values['united_billing'], false, array(), true);
		$this->ext = isset($this->params['name']) ? $this->params['name'] : '';
	}

	public function setServiceId(){
		foreach($GLOBALS['Core']->getModulesByType($this->ext, $this->billObj->getMod()) as $i => $e){
			foreach($this->billObj->getServicesByIdByExtension($i) as $i1 => $e1){
				$this->serviceId[] = $i1;
			}
		}
	}

	public function __ava__addServiceId($id){
		$this->serviceId = is_array($id) ? $id : array($id);
	}

	public function __ava__installExtension(){
		/*
			Инсталлирует расширение услуги
		*/

		$this->billObj->DB->{$this->setType}(
			array(
				'service_extensions',
				array(
					'mod' => $this->prefix,
					'name' => $this->obj->values['text_'.$this->ext]
				),
				"`mod`='{$this->prefix}'"
			)
		);
	}

	public function __ava__installService($serviceName, $service, $base_term, $test_term = 'day', $invoice_type = 'oneinmonth', $modify_type = 'paidto', $pkg_table_mode = 'h', $hide_if_none = 1, $compact_if_alike = 1, $type = 'prolonged', $id = 0){
		/*
			Установка услуги
		*/

		$this->billObj->setVar(
			'values',
			$this->paramReplaces(
				array(
					'name' => $service,
					'text' => $serviceName,
					'extension' => $this->prefix,
					'type' => $type,
					'invoice_type' => $invoice_type,
					'modify_type' => $modify_type,
					'base_term' => $base_term,
					'test_term' => $test_term,
					'pkg_table_mode' => $pkg_table_mode,
					'hide_if_none' => $hide_if_none,
					'compact_if_alike' => $compact_if_alike,
					'show' => '1',
					'max_test_accs' => '1',
					'ava_form_transaction_id' => $this->billObj->callFuncAndGetFormId('services', 'servicesNew')
				)
			)
		);

		if($this->setType == 'Upd') $this->billObj->values['modify'] = $id;
		if(!$sid = $this->billObj->callFunc('servicesNew')) $sid = $this->billObj->DB->cellFetch(array('services', 'id', "`name`='{$this->prefix}'"));

		if(!$sid) throw new AVA_Exception('{Call:Lang:modules:billing:neudalossozd:'.Library::serialize(array($serviceName)).'}');
		$this->setServiceId($sid);
		return $sid;
	}

	public function __ava__updateAllServices(){
		/*
			Обновление всех услуг построенных по данному расширению
		*/

		foreach($this->billObj->getServicesByExtension($this->prefix) as $i => $e){
			$this->billObj->callServiceObj('getServiceParams', $i, array('inUpdate' => true));
		}
	}

	public function __ava__getConnectExtensions($params){
		/*
			Параметры устанавливаемых под услугу соединений
		*/

		$return = array();
		if(!isset($params[$this->ext]['serverExtensions'])) $params[$this->ext]['serverExtensions'] = array();
		elseif(!is_array($params[$this->ext]['serverExtensions'])) $params[$this->ext]['serverExtensions'] = array($params[$this->ext]['serverExtensions']);

		foreach($params[$this->ext]['serverExtensions'] as $i => $e){
			$GLOBALS['Core']->loadExtension($this->ext, 'servconnect'.$e);
			$return[$e] = call_user_func(array('servconnect'.$e, 'getInstallParams'));
		}

		return $return;
	}

	public function __ava__setConnectExtensions($exParams){
		/*
			Устанавливает соединения
		*/

		$j = $this->billObj->DB->cellFetch(array('service_extensions_connect', 'sort', '', "`sort` DESC")) + 1;
		$serviceParams = array();
		$exParams = $this->paramReplaces($exParams);

		foreach($exParams as $i => $e){
			$instParams = array();

			foreach($e[0] as $i1 => $e1){
				$serviceParams[$i][$i1] = $e1;
				$instParams[$i1] = array(
					'text' => $e1['text'],
					'type' => $e1['type']
				);

				if(isset($e1['unlimit'])) $instParams[$i1]['unlimit'] = $e1['unlimit'];
				if(isset($e1['unlimitAlias'])) $instParams[$i1]['unlimitAlias'] = $e1['unlimitAlias'];
				if(isset($e1['ch'])) $instParams[$i1]['ch'] = $e1['ch'];
				if(isset($e1['noch'])) $instParams[$i1]['noch'] = $e1['noch'];
				if(isset($e1['k'])) $instParams[$i1]['k'] = $e1['k'];
				if(!empty($e1['noFunc'])) $instParams[$i1]['noFunc'] = 1;
			}

			if($this->setType != 'Ins') $insParams = array('extra' => array('cpParams' => $instParams));
			else $insParams = array('mod' => $i, 'service' => $e[2], 'name' => $e[1], 'extra' => array('cpParams' => $instParams), 'sort' => $j);
			$this->billObj->DB->{$this->setType}(array('service_extensions_connect', $insParams, "`mod`='$i' AND `service`='{$e[2]}'"));
			$j ++;
		}

		$this->setAllServiceParams($serviceParams);
	}

	public function __ava__setAllServiceParams($cpParams){
		/*
			Инсталлирует все параметры услуги
		*/

		$cpParams = $this->paramReplaces($cpParams);

		foreach($this->serviceId as $sid){
			$sData = $this->billObj->serviceDataById($sid);
			$issetParams = $this->billObj->DB->columnFetch(array('package_descripts', 'id', 'name', "`service`='{$sData['name']}'"));
			$installElements = array();

			foreach($cpParams as $i => $e){
				foreach($e as $i1 => $e1){
					$name = regExp::replace("/\W/", '_', empty($e1['name']) ? $i1 : $e1['name'], true);
					if(!isset($installElements[$name])) $installElements[$name] = $e1;
					$installElements[$name]['cpConf'][$i] = $i1;
				}
			}

			foreach($installElements as $i => $e){
				$id = 0;
				$params = array();

				if(isset($issetParams[$i])){
					$id = $issetParams[$i];
					$params = $this->billObj->DB->rowFetch(array('package_descripts', array('vars', 'cp'), "`id`='$id'"));
					$params['vars'] = Library::unserialize($params['vars']);
					$params['cp'] = Library::str2arrKeys($params['cp']);
				}
				else{
					$params['sort'] = isset($params['extra']['sort']) ? $params['extra']['sort'] : (((($e['type'] == 'checkbox' || $e['type'] == 'caption') ? 3 : (($e['type'] == 'text') ? 2 : (($e['type'] == 'checkbox_array') ? 0 : 1))) * 100));
					unset($params['extra']['sort']);
					$params['show'] = 1;

					$params['name'] = $i;
					$params['text'] = $e['text'];
					$params['type'] = $e['type'];

					$params['service'] = $sData['name'];
					$params['apkg'] = !isset($e['apkg']) ? 1 : $e['apkg'];
					$params['mpkg'] = !isset($e['mpkg']) ? ($e['type'] == 'caption' ? 0 : 1) : $e['mpkg'];

					$params['pkg_list'] = !isset($e['pkg_list']) ? ($e['type'] == 'caption' ? 0 : 1) : $e['pkg_list'];
					$params['opkg'] = !isset($e['opkg']) ? 0 : $e['opkg'];
					$params['aacc'] = !isset($e['aacc']) ? 0 : $e['aacc'];

					if(isset($e['extra']['eval'])){
						$params['vars']['eval'] = $e['extra']['eval'];
						unset($e['extra']['eval']);
					}
					$params['vars']['matrix'] = isset($e['extra']) ? $e['extra'] : array();

					if(isset($e['unlimit']) && $e['unlimit'] != ''){
						if(!isset($params['vars']['extra']['use_unlimit'])) $params['vars']['extra']['use_unlimit'] = 1;
						if(!isset($params['warn_function']) && empty($e['noFunc'])) $params['vars']['matrix']['warn_function'] = 'mod_billing::isValidPkgValue';
					}
					elseif($e['type'] == 'text' && empty($e['noFunc'])){
						if(!isset($params['vars']['matrix']['warn_function'])) $params['vars']['matrix']['warn_function'] = 'regExp::float';
					}

					if(($params['type'] == 'select' || $params['type'] == 'checkbox_array' || $params['type'] == 'radio') && !isset($params['vars']['matrix']['additional'])){
						$params['vars']['matrix']['additional'] = array();
					}
				}

				foreach($e['cpConf'] as $i1 => $e1){
					$params['vars']['extra']['cp_conformity_'.$i1] = $e1;
					$params['cp'][$i1] = 1;
				}
				$params['cp'] = Library::arrKeys2str($params['cp']);

				$this->billObj->DB->{$this->setType}(array('package_descripts', $params, "`id`='$id'"));
			}
		}
	}

	public function __ava__dropExtension(){
		return $this->billObj->DB->Del(array('service_extensions', "`mod`='{$this->prefix}'"));
	}

	public function __ava__dropService(){
		$return = array();
		foreach($this->billObj->DB->columnFetch(array('services', 'id', '', "`extension`='{$this->prefix}'")) as $e){
			$this->billObj->setVar('values', array('id' => $e));
			$return[$e] = $this->billObj->callFunc('serviceDel');
		}

		return $return;
	}
}

?>