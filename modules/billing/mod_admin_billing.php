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


class mod_admin_billing extends gen_billing{


	/********************************************************************************************************************************************************************

															Соединения со внешними серверами и т.п. хуета

	*********************************************************************************************************************************************************************/

	protected function func_connections(){
		/*
			Создание нового соединения
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'connections',
						'connectionsNew',
						array(
							'caption' => '{Call:Lang:modules:billing:novoesoedine}'
						)
					),
					'connections',
					array(
						'connectionMods' => $this->getConnectMods()
					)
				)
			)
		);

		$tbl1 = $this->DB->getPrefix().'connections';
		$tbl2 = $this->DB->getPrefix().'service_extensions_connect';

		$where = 't1.extension=t2.mod';
		$order = "t1.sort";

		$searchFields = array(
			'extension' => '{Call:Lang:modules:billing:rasshirenie}',
			'name' => '{Call:Lang:modules:billing:imia}',
			'host' => '{Call:Lang:modules:billing:khostdostupa}',
			'login' => '{Call:Lang:modules:billing:login}',
			'comment' => '{Call:Lang:modules:billing:kommentarij}'
		);

		if(!empty($this->values['in_search'])){
			if(
				$wh = $this->getListSearchWhere(
					$this->values,
					$searchFields,
					array('extension' => 't1', 'name' => 't1', 'host' => 't1', 'login' => 't1', 'comment' => 't1')
				)
			) $where .= ' AND '.$wh;

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('name' => 't1', 'host' => 't1', 'login' => 't1')
				);
			}
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'connections_list',
					array(
						'req' => "SELECT t1.*, t2.name AS extname FROM $tbl1 AS t1, $tbl2 AS t2 WHERE {$where} ORDER BY {$order}",
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'text' => 'connectionsData'
						),
						'action' => 'connectionsActions',
						'table' => 'connections',
						'searchForm' => array(
							'searchFields' => $searchFields,
							'orderFields' => array(
								'name' => '{Call:Lang:modules:billing:imeni}',
								'host' => '{Call:Lang:modules:billing:khostu}',
								'login' => '{Call:Lang:modules:billing:loginu}',
							),
							'searchMatrix' => array(
								'extension' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->DB->columnFetch(array('service_extensions_connect', 'name', 'mod', "", "`name`")))
								)
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny}'
					)
				)
			)
		);
	}

	protected function func_connectionsNew(){
		/*
			Добавляет услугу
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->values['id'] = $id;
		$this->isUniq('connections', array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'), $id);

		if($id){
			if($this->values['extension'] && !$this->callServiceByExt('connect', $this->DB->cellFetch(array('service_extensions_connect', 'service', "`mod`='".db_main::Quot($this->values['extension'])."'")))){
				$this->setError('host', '{Call:Lang:modules:billing:neudalosusta1}');

				if($rid = $this->getConnectionResultId(0)){
					$this->setError('host', '{Call:Lang:modules:billing:otvetservera:'.Library::serialize(array($this->path, $this->mod, $rid)).'}');
				}
			}
		}

		if(!$this->check()) return false;

		$insParams = $this->fieldValues(array('name', 'text', 'extension', 'host', 'login_host', 'login', 'pwd', 'comment', 'vars'));
		if(isset($this->values['pwd'])) $insParams['pwd'] = Library::crypt($this->values['pwd']);

		$newId = $this->typeIns('connections', $insParams);
		if(!$id && $newId !== false){
			$this->redirect('connectionsData&id='.$newId);
		}
		$this->refresh('connections');

		return $newId;
	}

	protected function func_connectionsData(){
		/*
			Установка данных для соединения
		*/

		$p = $this->DB->getPrefix();
		$t1 = $p.'connections';
		$t2 = $p.'service_extensions_connect';

		$values = $this->DB->rowFetch("SELECT t1.*, t2.service AS ext_mod FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.extension=t2.mod WHERE t1.id='".db_main::Quot($this->values['id'])."' LIMIT 1");
		$values['pwd'] = Library::Decrypt($values['pwd']);
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$fObj = $this->addFormBlock(
			$this->newForm(
				'connections',
				'connectionsNew',
				array(
					'caption' => '{Call:Lang:modules:billing:novoesoedine}'
				)
			),
			'connections',
			array('connectionMods' => $this->getConnectMods(), 'extra' => true)
		);

		$this->callServiceByExt('setConnectMatrix', $values['ext_mod'], array('fObj' => $fObj, 'values' => $values));
		$this->setContent($this->getFormText(false, $values, array('modify' => $values['id']), 'big'));

		return true;
	}

	protected function func_connectionsActions(){
		foreach($this->DB->columnFetch(array('connections', 'text', 'name', $this->getEntriesWhere(false, 'id'))) as $i => $e){
			if($this->DB->cellFetch(array('order_packages', 'server', "`server`='$i'"))){
				$this->setError('', '{Call:Lang:modules:billing:nelziaudalit:'.Library::serialize(array($e)).'}');
			}
			elseif($this->DB->cellFetch(array('order_services', 'server', "`server`='$i' AND `step`>-1"))){
				$this->setError('', '{Call:Lang:modules:billing:nelziaudalit1:'.Library::serialize(array($e)).'}');
			}
		}

		if($this->errorMessages){
			$this->back('connections');
			return false;
		}

		return $this->typeActions('connections', 'connections');
	}

	protected function func_connectionResult(){
		/*
			Результат выполнения запроса
		*/

		$this->Core->setTempl('empty.tmpl');
		if(!$values = $this->DB->rowFetch(array('server_reply', '*', "`id`='".db_main::Quot($this->values['id'])."'"))) throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:takojzaprosn}');
		elseif(!$values['description'] && $values['code']) $values['description'] = '{Call:Lang:modules:billing:otsutstvueto}';
		elseif(!$values['description'] && !$values['code']) $values['description'] = '{Call:Lang:modules:billing:zaprosvypoln}';

		$this->setContent($this->Core->readAndReplace($this->Core->getModuleTemplatePath($this->mod).'server_reply.tmpl', $this, $values));
		$this->setMeta($values['description']);
	}


	/********************************************************************************************************************************************************************

																		Расширения соединений

	*********************************************************************************************************************************************************************/

	protected function func_connectExtensions(){
		/*
			Расширения для соединений
		*/

		$exList = array('' => '{Call:Lang:modules:billing:vse}');
		foreach($this->getExtensions() as $i => $e){
			$tn = $this->Core->getModuleTechName($i);
			$exList[$tn] = $this->Core->getModuleTechNameByTechId($tn);
		}

		$billMods = $this->Core->getModulesByType('billing');
		unset($billMods[$this->mod]);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'connectExtensionsAdd',
						'connectExtensionsAdd',
						array(
							'caption' => '{Call:Lang:modules:billing:ustanovitras}'
						)
					),
					'connection_extension',
					array('billMods' => $billMods)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'connect_extensions_list',
					array(
						'req' => array('service_extensions_connect', array('id', 'mod', 'name', 'service', 'sort'), "", "`sort`"),
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'name' => 'connectExtensionData',
							'update' => 'upConnectExtensionData',
							'descript' => 'connectExtensionsDescript'
						),
						'action' => 'connectExtensionActions',
						'searchForm' => array(
							'searchFields' => array(
								'name' => '{Call:Lang:modules:billing:imiarasshire}',
								'module' => '{Call:Lang:modules:billing:identifikato11}',
								'service' => '{Call:Lang:modules:billing:moduluslugi}'
							),
							'orderFields' => array(
								'name' => '{Call:Lang:modules:billing:imeni}',
								'mod' => '{Call:Lang:modules:billing:identifikato12}'
							),
							'searchAlias' => array('module' => 'mod'),
							'isBe' => array('service' => 1),
							'searchMatrix' => array(
								'service' => array(
									'type' => 'select',
									'additional' => $exList
								)
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny1}'
					)
				)
			)
		);
	}

	protected function func_connectExtensionData(){
		/*
			Параметры расширения соединения
		*/

		return $this->typeModify(
			array('service_extensions_connect', '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			'connection_extension',
			'connectExtensionDataSet',
			array(
				'formData' => array('modify' => 1),
				'params' => array('caption' => '{Call:Lang:modules:billing:rasshirenien}')
			)
		);
	}

	protected function func_connectExtensionDataSet(){
		$this->isUniq('service_extensions_connect', array('name' => '{Call:Lang:modules:billing:takoenazvani}'), $this->values['modify']);
		return $this->typeIns('service_extensions_connect', array('name' => $this->values['name']), 'connectExtensions');
	}

	protected function func_connectExtensionActions(){
		return $this->typeActions('service_extensions_connect', 'connectExtensions');
	}

	protected function func_upConnectExtensionData(){
		/*
			Обновление расширения
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'connectExtensionsUpdate',
						'connectExtensionsUpdate',
						array(
							'caption' => '{Call:Lang:modules:billing:obnovitrassh}'
						)
					),
					'connection_extension_file'
				),
				array(),
				array('id' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_connectExtensionsAdd(){
		/*
			Устанавливаем новое расширение
		*/

		if(!$this->check()) return false;

		list($tmpFolder, $exMod, $files, $params) = $this->loadConnectExtensionParams();
		foreach($files as $e) if(file_exists(_W.'modules/'.$params[2].'/extensions/'.$e)) $this->setError('extension', '{Call:Lang:modules:billing:uzhesuzhestv:'.Library::serialize(array($e)).'}');
		$this->isUniq('service_extensions_connect', array('mod' => '{Call:Lang:modules:billing:takojmoduluz}', 'name' => '{Call:Lang:modules:billing:takoeimiauzh}'), false, '', array('mod' => $exMod, 'name' => $this->values['name']));

		if(!$this->check()) return false;
		$this->refresh('connectExtensions');
		return $this->installConnectExtension(isset($this->values['bill_mods']) ? $this->values['bill_mods'] : array(), $tmpFolder, $exMod, $params);
	}

	protected function func_connectExtensionsUpdate(){
		/*
			Устанавливаем новое расширение
		*/

		if(!$this->check()) return false;
		list($tmpFolder, $exMod, $files, $params) = $this->loadConnectExtensionParams();
		if($exMod && $exMod != regExp::lower($this->DB->cellFetch(array('service_extensions_connect', 'mod', "`id`='{$this->values['id']}'")))) $this->setError('extension', '{Call:Lang:modules:billing:nesovpadaiut}');
		if(!$this->check()) return false;

		$billMods = array();
		foreach($this->Core->getModulesByType('billing') as $i => $e){
			if($this->Core->callModule($i)->DB->cellFetch(array('service_extensions_connect', 'id', "`mod`='$exMod'"))) $billMods[$i] = 1;
		}

		$return = $this->installConnectExtension($billMods, $tmpFolder, $exMod, $params, 'Upd');
		$this->refresh('connectExtensions');
		return $return;
	}

	private function loadConnectExtensionParams(){
		/*
			Считывает параметры расширения
		*/

		$files = $params = array();

		if(!$tmpFolder = $this->Core->extract2tmpArc(TMP.$this->values['extension'])) $this->setError('extension', '{Call:Lang:modules:billing:neudalosrasp}');
		else{
			$files = Files::readFolder($tmpFolder);
			foreach($files as $e) if(regExp::Match("/^servconnect(\w+)\.php$/", $e, true, true, $m)) break;
			if(empty($m[1])) $this->setError('extension', '{Call:Lang:modules:billing:nenajdenfajl}');
			else $params = $this->readConnectExtensionParams($tmpFolder, $m['1']);
		}

		return array($tmpFolder, isset($m[1]) ? $m[1] : '', $files, $params);
	}

	private function readConnectExtensionParams($folder, $mod){
		/*
			Считывает параметры расширения
		*/

		require_once($folder.'servconnect'.$mod.'.php');
		$params = call_user_func(array('servconnect'.$mod, 'getInstallParams'));
		return $params;
	}

	private function installConnectExtension($billMods, $tmpFolder, $exMod, $params, $instType = 'Ins'){
		/*
			Ставит расширение для соединения
		*/

		$this->Core->setFlag('tmplLock');
		$this->Core->ftpCopy($tmpFolder, _W.'modules/'.$params[2].'/extensions/');
		$this->Core->loadExtension('billing', 'installServiceObject');
		$billMods[$this->mod] = 1;

		foreach($billMods as $i => $e){
			$this->values['united_billing'] = $i;
			$iObj = new installServiceObject($this->Core->DB, $this, $i, array('name' => $params[2]));

			$iObj->setType = $instType;
			$iObj->setServiceId();
			$iObj->setConnectExtensions(array($exMod => $params));
		}

		$this->Core->rmFlag('tmplLock');
		$this->Core->rmFlag('refreshed');
		$this->Core->rmHeader('Location');
	}

	protected function func_connectExtensionsDescript(){
		/*
			Описание тарифов (уровень расширения)
		*/

		$params = $this->DB->rowFetch(array('service_extensions_connect', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$params['extra'] = Library::unserialize($params['extra']);
		$this->funcName = 'Описание расширения "'.$params['name'].'"';
		$this->pathFunc = 'connectExtensions';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'connectExtensionsDescriptNew',
						'connectExtensionsDescriptNew&exId='.$this->values['id'],
						array(
							'caption' => 'Добавить пункт описания'
						)
					),
					'connection_extension_descript'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'connection_extension_descript_list',
					array(
						'arr' => $params['extra']['cpParams'],
						'actions' => array('text' => 'connectExtensionsDescriptData&exId='.$this->values['id'])
					),
					array(
						'caption' => 'Список'
					)
				)
			)
		);
	}

	protected function func_connectExtensionsDescriptNew(){
		/*
			Изменение описания
		*/

		$params = $this->DB->rowFetch(array('service_extensions_connect', '*', "`id`='".db_main::Quot($this->values['exId'])."'"));
		$params['extra'] = Library::unserialize($params['extra']);
		if(empty($this->values['modify']) && isset($params['extra']['cpParams'][$this->values['name']])){
			$this->setError('name', 'Такой параметр уже есть');
		}

		if(!$this->check()) return false;

		$params['extra']['cpParams'][$this->values['name']] = array(
			'text' => $this->values['text'],
			'type' => $this->values['type']
		);

		if($this->values['type'] == 'text' || $this->values['type'] == 'select'){
			if(!empty($this->values['k'])) $params['extra']['cpParams'][$this->values['name']]['k'] = $this->values['k'];
			if(!empty($this->values['unlimit'])) $params['extra']['cpParams'][$this->values['name']]['unlimit'] = $this->values['unlimit'];
			if(!empty($this->values['unlimitAlias'])) $params['extra']['cpParams'][$this->values['name']]['unlimitAlias'] = $this->values['unlimitAlias'];
		}

		if($this->values['type'] == 'checkbox'){
			if(!empty($this->values['ch'])) $params['extra']['cpParams'][$this->values['name']]['ch'] = $this->values['ch'];
			if(!empty($this->values['noch'])) $params['extra']['cpParams'][$this->values['name']]['noch'] = $this->values['noch'];
		}

		$this->DB->Upd(array('service_extensions_connect', array('extra' => $params['extra']), "`id`='".db_main::Quot($this->values['exId'])."'"));
		$this->refresh('connectExtensionsDescript&id='.$this->values['exId']);
		return true;
	}

	protected function func_connectExtensionsDescriptData(){
		/*
			Изменение описания
		*/

		$params = $this->DB->rowFetch(array('service_extensions_connect', '*', "`id`='".db_main::Quot($this->values['exId'])."'"));
		$params['extra'] = Library::unserialize($params['extra']);
		$params['extra']['cpParams'][$this->values['id']]['name'] = $this->values['id'];

		$this->funcName = 'Параметр "'.$params['extra']['cpParams'][$this->values['id']]['text'].'"';
		$this->pathFunc = 'connectExtensions';
		$this->pathPoint = array('connectExtensionsDescript' => 'Описание расширения "'.$params['name'].'"');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'connectExtensionsDescriptNew',
						'connectExtensionsDescriptNew&exId='.$this->values['exId'],
						array(
							'caption' => 'Добавить пункт описания'
						)
					),
					'connection_extension_descript',
					array(
						'modify' => $this->values['id']
					)
				),
				$params['extra']['cpParams'][$this->values['id']],
				array('modify' => $this->values['id']),
				'big'
			)
		);
	}


	/********************************************************************************************************************************************************************

																			Управление услугами

	*********************************************************************************************************************************************************************/

	protected function func_services(){
		/*
			Общее управление услугами. Создает новую услугу
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'servicesNew',
						'servicesNew',
						array(
							'caption' => '{Call:Lang:modules:billing:novaiausluga}'
						)
					),
					'services',
					array( 'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzuets}'), $this->getExtensions()) )
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'services_list',
					array(
						'req' => array( 'services', '*', '', "`sort`" ),
						'actions' => array(
							'text' => 'servicesData',
							'packages' => 'packages',
							'orders' => 'orders',
							'modifyOrders' => 'modifyOrders',
							'discounts' => 'discounts',
							'descript' => 'pkgDescripts',
							'group' => 'pkgGroups',
							'del' => 'serviceDel'
						),
						'searchForm' => array(
							'searchFields' => array(
								'extension' => '{Call:Lang:modules:billing:rasshirenie}',
								'name' => '{Call:Lang:modules:billing:identifikato3}',
								'text' => '{Call:Lang:modules:billing:imia}'
							),
							'orderFields' => array(
								'text' => '{Call:Lang:modules:billing:imeni}',
								'name' => '{Call:Lang:modules:billing:identifikato12}'
							),
							'searchMatrix' => array(
								'extension' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->DB->columnFetch(array('service_extensions', 'name', 'mod', "", "`name`")))
								)
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:vseuslugi}'
					)
				)
			)
		);
	}

	protected function func_servicesNew(){
		/*
			Добавляет услугу
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		if(!$id) $this->isUniq('services', array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'), $id);
		if(!$this->check()) return false;

		$this->values['vars'] = Library::array_merge(
			$this->fieldValues(array('invoice_type', 'max_test_accs', 'pay_test_install', 'pay_test_modify', 'modify_type', 'modify_type2', 'modify_minus', 'modify_install_type', 'modify_test_type', 'modify_discount_type', 'modify_price_type', 'modify_price_type_discount', 'pkg_table_mode', 'hide_if_none', 'compact_if_alike')),
			$this->callServiceObj('getNewServiceParams', $this->values['name'])
		);

		$return = $this->typeIns(
			'services',
			$this->fieldValues(array('name', 'text', 'type', 'extension', 'base_term', 'test_term', 'vars', 'sort', 'show')),
			'services'
		);

		if($return && !$id && $this->values['name']){
			//Создаем таблицу в базе данных для услуг и для клиентов

			$this->DB->CT(array('packages_'.$this->values['name'], array( 'package_id' => '' ), array('uni' => array(array('package_id')))));
			$this->DB->CT(array('orders_'.$this->values['name'], array('service_order_id' => 'INT'), 'extras' => array('uni' => array(array('service_order_id')))));

			if(!empty($this->values['extension'])){
				$this->callServiceObj('getServiceParams', $this->values['name']);
				$this->Core->loadExtension('billing', 'installServiceObject');
				$exData = $this->Core->getModuleParams($this->values['extension']);

				$iObj = new installServiceObject($this->Core->DB, $this, $this->values['extension'], array('name' => $exData['name']));
				$iObj->addServiceId($return);

				$cpParams = array();
				foreach($iObj->getConnectExtensions(array($exData['name'] => array('serverExtensions' => $this->DB->columnFetch(array('service_extensions_connect', 'mod', '', "`service`='{$exData['name']}'", "`id`"))))) as $i => $e){
					foreach($e[0] as $i1 => $e1) $cpParams[$i][$i1] = $e1;
				}

				$iObj->setAllServiceParams($cpParams);
			}

			$this->redirect('servicesData&id='.$return);
		}

		return $return;
	}

	protected function func_servicesData(){
		/*
			Модификация данных услуги
		*/

		$values = $this->DB->rowFetch(array('services', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$fObj = $this->addFormBlock(
			$this->newForm(
				'servicesNew',
				'servicesNew',
				array(
					'caption' => '{Call:Lang:modules:billing:parametryusl:'.Library::serialize(array($values['text'])).'}'
				)
			),
			'services',
			array(
				'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzuets}'), $this->getExtensions()),
				'extra' => 1,
				'type' => $values['type']
			)
		);

		$this->callServiceObj('setNewService', $values['name'], array('fObj' => $fObj));
		$this->setContent($this->getFormText($fObj, $values, array('modify' => $this->values['id']), 'big'));
		$this->pathFunc = 'services';
		$this->funcName = $values['text'];
	}

	protected function func_servicesActions(){
		return $this->typeActions('services', 'services');
	}

	protected function func_serviceDel(){
		/*
			Удаляет услугу.
			Услуга не удаляется если для нее имеется хотябы 1 действующий заказ
		*/

		$id = db_main::Quot($this->values['id']);
		$data = $this->DB->rowFetch(array('services', array('name', 'extension'), "`id`='$id'"));

		if($this->DB->cellFetch(array('order_services', 'id', "`step`=2 AND `service`='{$data['name']}'"))){
			$this->setError('', '{Call:Lang:modules:billing:nelziaudalit2}');
		}

		if($this->errorMessages){
			$this->back('services');
			return false;
		}

		$this->DB->Drop(array('packages_'.$data['name'], 'orders_'.$data['name']));
		$this->DB->Del(array('services', "`id`='$id'"));
		$this->DB->Del(array('order_packages', "`service`='{$data['name']}'"));

		$this->DB->Del(array('order_services', "`service`='{$data['name']}'"));
		$this->DB->Del(array('package_descripts', "`service`='{$data['name']}'"));
		$this->DB->Del(array('discounts', "`service`='{$data['name']}'"));

		if($data['extension']) Library::CallClass('mod_admin_'.$this->Core->getModuleTechName($data['extension']), 'delService', $data['name']);
		$this->refresh('services');
		return true;
	}



	/********************************************************************************************************************************************************************

																		Заказанные услуги

	*********************************************************************************************************************************************************************/

	protected function func_orders(){
		/*
			Список всех заказанных услуг
		*/

		$sData = $this->serviceDataById($this->values['id']);
		if($sData['base_term']) $sData['baseTerm'] = Dates::termsListVars($sData['base_term'], 0);
		$this->funcName = $sData['caption'] = '{Call:Lang:modules:billing:zakazydliaus:'.Library::serialize(array($sData['text'])).'}';
		$this->pathFunc = 'services';

		$extraFilter = '';
		$searchExpr = array();

		if(!empty($this->values['in_search'])){
			if($this->values['user_login'] || $this->values['user_name']){
				if($this->values['user_login']) $filter[] = "`login` REGEXP ('".db_main::Quot($this->values['user_login'])."')";
				if($this->values['user_name']) $filter[] = "`name` REGEXP ('".db_main::Quot($this->values['user_name'])."')";
				$extraFilter = " AND client_id='".$this->getClientByUserId($this->Core->DB->cellFetch(array('users', 'id', implode(' AND ', $filter))))."'";
			}

			foreach(array('total' => 't1.price + t1.modify_price', 'price' => false, 'modify_price' => false, 'all_payments' => false) as $i => $e){
				$searchExpr[$i] = array();
				if(!empty($this->values[$i])) $searchExpr[$i][] = ($e ? $e : "t1.$i")." * t6.exchange >= '{$this->values[$i]}'";
				if(!empty($this->values[$i.'_to'])) $searchExpr[$i][] = ($e ? $e : "t1.$i")." * t6.exchange <= '".$this->values[$i.'_to']."'";
				$searchExpr[$i] = implode(" AND ", $searchExpr[$i]);
			}
		}

		$p = $this->DB->getPrefix();

		$t1 = $p.'order_services';
		$t2 = $p.'orders_'.$sData['name'];
		$t3 = $p.'clients';

		$t4 = $p.'order_packages';
		$t5 = $p.'connections';
		$t6 = $p.'currency';

		$lObj = $this->newList(
			'ordered_services_list',
			library::array_merge(
				array(
					'req' => "SELECT
							t1.id, t1.client_id, t1.ident, t1.package, t1.date, t1.created, t1.last_paid, t1.paid_to, t1.price, t1.modify_price, t1.ind_price, t1.all_payments, t1.step,
							t2.*,
							t3.id AS client_id, t3.user_id AS clients_user_id,
							t4.text AS pkg_name, t4.server_name AS pkg_server_name, t4.show AS pkg_show, t4.currency AS pkg_currency,
							t5.name AS connect_name, t5.text AS connect_textname, t5.login_host AS connect_login_host,
							t6.exchange AS cur_ex, t6.text AS cur_name, t6.default AS cur_default
						FROM
							$t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.id=t2.service_order_id
							LEFT JOIN $t3 AS t3 ON t1.client_id=t3.id
							LEFT JOIN $t4 AS t4 ON t1.package=t4.name AND t1.service=t4.service
							LEFT JOIN $t5 AS t5 ON t1.server=t5.name
							LEFT JOIN $t6 AS t6 ON (t4.currency AND t4.currency=t6.name) OR (!t4.currency AND t6.default=1)
						WHERE
							t1.service='{$sData['name']}'".
							(!isset($this->values['step']) || $this->values['step'] === '' ? " AND t1.step>=0" : "").
							(empty($this->values['in_search']) ? " ORDER BY t1.paid_to" : $extraFilter),
					'extraReqs' => array(
						array(
							'req' => array('users', array('id', 'login', 'name', 'eml')),
							'DB' => $this->Core->DB,
							'unitedFld1' => 'clients_user_id',
							'unitedFld2' => 'id',
							'prefix' => 'user_'
						)
					),
					'form_actions' => array(
						'prolong' => '{Call:Lang:modules:billing:prodlit}',
						'modify' => '{Call:Lang:modules:billing:smenittarif}',
						'transmit' => '{Call:Lang:modules:billing:peredat}',
						'suspend' => '{Call:Lang:modules:billing:zablokirovat}',
						'unsuspend' => '{Call:Lang:modules:billing:razblokirova2}',
						'delete' => '{Call:Lang:modules:billing:udalit}'
					),
					'actions' => array(
						'params' => 'ordersData',
						'history' => 'serviceHistory'
					),
					'action' => 'ordersActions&serviceId='.$this->values['id'],
					'searchForm' => array(
						'searchFields' => array(
							'ident' => '{Call:Lang:modules:billing:login}',
							'user_login' => '{Call:Lang:modules:billing:loginklienta}',
							'user_name' => '{Call:Lang:modules:billing:imiaklienta}',
							'package' => '{Call:Lang:modules:billing:tarif}',
							'server_name' => '{Call:Lang:modules:billing:tarifnaserve}',
							'server' => '{Call:Lang:modules:billing:server}',
							'step' => '{Call:Lang:modules:billing:status}',
							'suspend_reason' => '{Call:Lang:modules:billing:zablokirovan2}',
							'date' => '{Call:Lang:modules:billing:datavnesenii}',
							'created' => '{Call:Lang:modules:billing:datasozdanii}',
							'last_paid' => '{Call:Lang:modules:billing:posledniaiao}',
							'paid_to' => '{Call:Lang:modules:billing:oplachenapo}',
							'total' => '{Call:Lang:modules:billing:tsenavsego:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							'price' => '{Call:Lang:modules:billing:tsenaosnovno:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							'modify_price' => '{Call:Lang:modules:billing:tsenamodifik1:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							'all_payments' => '{Call:Lang:modules:billing:vsegopotrach:'.Library::serialize(array($this->getMainCurrencyName())).'}'
						),
						'notSearchFields' => array('user_login', 'user_name'),
						'orderFields' => array(
							'package' => '{Call:Lang:modules:billing:tarifu}',
							'date' => '{Call:Lang:modules:billing:datevnesenii}',
							'created' => '{Call:Lang:modules:billing:datesozdanii}',
							'paid_to' => '{Call:Lang:modules:billing:okonchaniius}',
							'last_paid' => '{Call:Lang:modules:billing:dateposledne}',
							'total' => '{Call:Lang:modules:billing:tsene}',
							'price' => '{Call:Lang:modules:billing:tseneosnovno}',
							'modify_price' => '{Call:Lang:modules:billing:tsenemodifik}',
							'all_payments' => '{Call:Lang:modules:billing:kolichestvup}'
						),
						'searchMatrix' => array(
							'created' => array('type' => 'calendar'),
							'last_paid' => array('type' => 'calendar'),
							'paid_to' => array('type' => 'calendar'),
							'total' => array('type' => 'gap'),
							'price' => array('type' => 'gap'),
							'modify_price' => array('type' => 'gap'),
							'all_payments' => array('type' => 'gap'),
							'package' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getPackages($sData['name']))
							),
							'server_name' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getServerPackages($sData['name']))
							),
							'server' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getConnections($sData['name']))
							),
							'step' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:modules:billing:rabotaiushch1}',
									'1' => '{Call:Lang:modules:billing:rabotaiushch2}',
									'0' => '{Call:Lang:modules:billing:zablokirovan3}',
									'-1' => '{Call:Lang:modules:billing:udalennye}'
								)
							),
							'suspend_reason' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:modules:billing:vse}',
									'accord' => '{Call:Lang:modules:billing:dobrovolno}',
									'term' => '{Call:Lang:modules:billing:istecheniesr}',
									'policy' => '{Call:Lang:modules:billing:narushenie}',
									'other' => '{Call:Lang:modules:billing:drugaiaprich}',
								)
							)
						),
						'searchParams' => array(
							'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$this->values['id']
						),
						'searchPrefix' => array('ident' => 't1', 'package' => 't1', 'server_name' => 't4', 'server' => 't1', 'step' => 't1', 'suspend_reason' => 't1', 'date' => 't1', 'created' => 't1', 'last_paid' => 't1', 'paid_to' => 't1', 'price' => 't1', 'modify_price' => 't1', 'all_payments' => 't1'),
						'isBe' => array('package' => 1, 'server_name' => 1, 'server' => 1, 'step' => 1, 'suspend_reason' => 1),
						'searchExpr' => $searchExpr,
						'orderExpr' => array('total' => 't1.price + t1.modify_price')
					)
				),
				$this->callServiceObj('setOrderAdminListParams', $sData['name'])
			),
			$sData
		);

		$this->callServiceObj('setOrderAdminListEntries', $sData['name'], array('lObj' => $lObj));
		$this->setContent($this->getListText($lObj, 'big'));
		$this->addAccDirectForm($sData['name']);
	}

	protected function func_serviceHistory(){
		/*
			История заказа услуги
		*/

		$data = $this->getOrderedService($this->values['id']);
		$sData = $this->serviceData($data['service']);
		krsort($data['history']);

		foreach($data['history'] as $i => $e){
			$data['history'][$i]['date'] = $i;
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'service_history_list',
					array(
						'arr' => $data['history'],
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:billing:udalit}'
						)
					),
					array(
						'caption' => 'История услуги "'.$sData['text'].'" для "'.$data['ident'].'"'
					)
				)
			)
		);
	}

	protected function func_orders1(){
		/*
			Список всех заказанных услуг
		*/

		$sData = $this->serviceDataById($this->values['id']);
		$sData['caption'] = '{Call:Lang:modules:billing:zakazydliaus:'.Library::serialize(array($sData['text'])).'}';
		if($sData['base_term']) $sData['baseTerm'] = Dates::termsListVars($sData['base_term'], 0);

		$searchFields = $sf = array(
			'ident' => '{Call:Lang:modules:billing:login}',
			'user_login' => '{Call:Lang:modules:billing:loginklienta}',
			'user_name' => '{Call:Lang:modules:billing:imiaklienta}',
			'package' => '{Call:Lang:modules:billing:tarif}',
			'server_name' => '{Call:Lang:modules:billing:tarifnaserve}',
			'server' => '{Call:Lang:modules:billing:server}',
			'step' => '{Call:Lang:modules:billing:status}',
			'suspend_reason' => '{Call:Lang:modules:billing:zablokirovan2}',
			'date' => '{Call:Lang:modules:billing:datavnesenii}',
			'created' => '{Call:Lang:modules:billing:datasozdanii}',
			'last_paid' => '{Call:Lang:modules:billing:posledniaiao}',
			'paid_to' => '{Call:Lang:modules:billing:oplachenapo}',
			'total' => '{Call:Lang:modules:billing:tsenavsego1}',
			'price' => '{Call:Lang:modules:billing:tsenaosnovno1}',
			'modify_price' => '{Call:Lang:modules:billing:tsenamodifik2}',
			'all_payments' => '{Call:Lang:modules:billing:vsegopotrach1}',
			'currency' => '{Call:Lang:modules:billing:raschetnaiav}',
		);

		$searchMatrix = array(
			'created' => array('type' => 'calendar'),
			'last_paid' => array('type' => 'calendar'),
			'paid_to' => array('type' => 'calendar'),
			'total' => array('type' => 'gap'),
			'price' => array('type' => 'gap'),
			'modify_price' => array('type' => 'gap'),
			'all_payments' => array('type' => 'gap'),
			'package' => array(
				'type' => 'select',
				'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getPackages($sData['name']))
			),
			'server_name' => array(
				'type' => 'select',
				'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getServerPackages($sData['name']))
			),
			'server' => array(
				'type' => 'select',
				'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getConnections($sData['name']))
			),
			'step' => array(
				'type' => 'select',
				'additional' => array(
					'' => '{Call:Lang:modules:billing:rabotaiushch1}',
					'2' => '{Call:Lang:modules:billing:rabotaiushch2}',
					'1' => '{Call:Lang:modules:billing:vozhidaniiop}',
					'0' => '{Call:Lang:modules:billing:vprotsesseza}',
					'-1' => '{Call:Lang:modules:billing:zablokirovan3}',
					'-2' => '{Call:Lang:modules:billing:udalennye}',
					'-3' => '{Call:Lang:modules:billing:udalennyenas}'
				)
			),
			'suspend_reason' => array(
				'type' => 'select',
				'additional' => array(
					'' => '{Call:Lang:modules:billing:vse}',
					'accord' => '{Call:Lang:modules:billing:dobrovolno}',
					'term' => '{Call:Lang:modules:billing:istecheniesr}',
					'policy' => '{Call:Lang:modules:billing:narushenie}',
					'other' => '{Call:Lang:modules:billing:drugaiaprich}',
				)
			),
			'currency' => array(
				'type' => 'select',
				'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getCurrency())
			)
		);

		unset($sf['user_login'], $sf['user_name'], $sf['total']);
		$where = "t1.service='{$sData['name']}'";
		$order = "t1.paid_to";

		if(!empty($this->values['in_search'])){
			$v = $this->values;
			if($this->values['step'] === ''){
				unset($v['step']);
				$where .= " AND (t1.step=-1 OR t1.step=2)";
			}

			if($this->values['user_login'] || $this->values['user_name']){
				$sf['client_id'] = '';
				if($this->values['user_login']) $filter[] = "`login` REGEXP ('".db_main::Quot($this->values['user_login'])."')";
				if($this->values['user_name']) $filter[] = "`name` REGEXP ('".db_main::Quot($this->values['user_name'])."')";
				$v['client_id'] = $this->getClientByUserId($this->Core->DB->cellFetch(array('users', 'id', implode(' AND ', $filter))));
			}

			if($wh = $this->getListSearchWhere(
				$v,
				$sf,
				array('ident' => 't1', 'package' => 't1', 'server_name' => 't4', 'server' => 't1', 'step' => 't1', 'suspend_reason' => 't1', 'date' => 't1', 'created' => 't1', 'last_paid' => 't1', 'paid_to' => 't1', 'price' => 't1', 'modify_price' => 't1', 'all_payments' => 't1', 'currency' => 't4'),
				array('package' => 1, 'server_name' => 1, 'server' => 1, 'step' => 1, 'suspend_reason' => 1),
				$searchMatrix
			)){
				$where .= ' AND '.$wh;
			}

			if(!empty($this->values['total'])) $where .= " AND t1.price + t1.modify_price >= '{$this->values['total']}'";
			if(!empty($this->values['total_to'])) $where .= " AND t1.price + t1.modify_price <= '{$this->values['total_to']}'";

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('package' => 't1', 'date' => 't1', 'created' => 't1', 'paid_to' => 't1', 'last_paid' => 't1', 'price' => 't1', 'modify_price' => 't1', 'all_payments' => 't1', 'server_name' => 't4'),
					array('total' => 't1.price + t1.modify_price')
				);
			}
		}
		else{
			$where .= " AND (t1.step=-1 OR t1.step=2)";
		}

		$p = $this->DB->getPrefix();
		$t1 = $p.'order_services';
		$t2 = $p.'orders_'.$sData['name'];

		$t3 = $p.'clients';
		$t4 = $p.'order_packages';
		$t5 = $p.'connections';

		$lObj = $this->newList(
			'ordered_services_list',
			library::array_merge(
				array(
					'req' => "SELECT
							t1.id, t1.client_id, t1.ident, t1.package, t1.date, t1.created, t1.last_paid, t1.paid_to, t1.price, t1.modify_price, t1.ind_price, t1.all_payments, t1.step,
							t2.*,
							t3.id AS client_id, t3.user_id AS clients_user_id,
							t4.text AS pkg_name, t4.server_name AS pkg_server_name, t4.show AS pkg_show, t4.currency AS pkg_currency,
							t5.name AS connect_name, t5.text AS connect_textname, t5.login_host AS connect_login_host
						FROM
							$t1 AS t1
							LEFT JOIN $t2 AS t2 ON t1.id=t2.service_order_id
							LEFT JOIN $t3 AS t3 ON t1.client_id=t3.id
							LEFT JOIN $t4 AS t4 ON t1.package=t4.name AND t1.service=t4.service
							LEFT JOIN $t5 AS t5 ON t1.server=t5.name
						WHERE
							{$where} ORDER BY {$order}",
					'countReq' => "SELECT COUNT(t1.id) FROM $t1 AS t1 WHERE {$where}",
					'extraReqs' => array(
						array(
							'req' => array('users', array('id', 'login', 'name', 'eml')),
							'DB' => $this->Core->DB,
							'unitedFld1' => 'clients_user_id',
							'unitedFld2' => 'id',
							'prefix' => 'user_'
						)
					),
					'form_actions' => array(
						'prolong' => '{Call:Lang:modules:billing:prodlit}',
						'modify' => '{Call:Lang:modules:billing:smenittarif}',
						'transmit' => '{Call:Lang:modules:billing:peredat}',
						'suspend' => '{Call:Lang:modules:billing:zablokirovat}',
						'unsuspend' => '{Call:Lang:modules:billing:razblokirova2}',
						'delete' => '{Call:Lang:modules:billing:udalit}'
					),
					'actions' => array(
						'params' => 'ordersData'
					),
					'action' => 'ordersActions&serviceId='.$this->values['id'],
					'searchForm' => array(
						'searchFields' => $searchFields,
						'orderFields' => array(
							'package' => '{Call:Lang:modules:billing:tarifu}',
							'date' => '{Call:Lang:modules:billing:datevnesenii}',
							'created' => '{Call:Lang:modules:billing:datesozdanii}',
							'paid_to' => '{Call:Lang:modules:billing:okonchaniius}',
							'last_paid' => '{Call:Lang:modules:billing:dateposledne}',
							'total' => '{Call:Lang:modules:billing:tsene}',
							'price' => '{Call:Lang:modules:billing:tseneosnovno}',
							'modify_price' => '{Call:Lang:modules:billing:tsenemodifik}',
							'all_payments' => '{Call:Lang:modules:billing:kolichestvup}'
						),
						'searchMatrix' => $searchMatrix,
						'searchParams' => array(
							'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$this->values['id']
						)
					)
				),
				$this->callServiceObj('setOrderAdminListParams', $sData['name'])
			),
			$sData
		);

		$this->callServiceObj('setOrderAdminListEntries', $sData['name'], array('lObj' => $lObj));
		$this->setContent($this->getListText($lObj, 'big'));
		$this->addAccDirectForm($sData['name']);
	}

	public function func_orderService(){
		/*
			Директный заказ услуги
		*/

		$this->addAccDirectForm($this->values['service'], array('client_id' => $this->values['id']));
	}

	private function addAccDirectForm($service, $values = array()){
		/*
			Форма директивного добавления услуги
		*/

		$sData = $this->serviceData($service);
		$t = time();

		$fObj = $this->addFormBlock(
			$this->newForm('addAccDirect', 'addAccDirect', array('caption' => '{Call:Lang:modules:billing:vnestinovuiu}')),
			array('add_service_direct', 'add_service_terms'),
			array('packages' => $this->getPackages($sData['name']), 'type' => $sData['type'], 'id' => '')
		);

		$fObj->setValues($values);
		$this->setContent($this->getFormText($fObj, array(), array('serviceId' => $sData['id']), 'big100'));
	}

	protected function func_addAccDirect(){
		/*
			Вносит запись о заказе и переадресовывает дальше.
		*/

		if(!$clientId = $this->getClientByIdOrLogin($this->values['client_id'])) $this->setError('client_id', '{Call:Lang:modules:billing:nenajdenotak}');
		if(!$this->check()) return false;
		unset($this->values['ava_form_transaction_id']);

		$sData = $this->serviceDataById($this->values['serviceId']);
		$this->values['entryId'] = $this->addNewOrderEntry('new', $clientId, $sData['name'], $this->values['pkg']);
		$this->redirect('addAccDirect2&'.Library::deparseStr($this->values));
	}

	protected function func_addAccDirect2(){
		/*
			Директивное внесение услуги.
		*/

		$values = $this->values;
		$sData = $this->serviceDataById($this->values['serviceId']);
		$p = $this->serviceData($sData['name'], $this->values['pkg']);

		$term = Dates::sec2term($sData['base_term'], $this->values['paid_to'] - $this->values['created']);
		$total = $this->getTotalPrice($p['price'], $p['price2'], $p['prolong_price'], $term, $p['install_price'], 0, 0);

		$values['term'] = round($term);
		$this->setOrderEntryPayParams($this->values['entryId'], $p['price'], $p['price2'], $p['install_price'], 0, 0, $p['prolong_price'], $this->Core->getParam('recalculatePayPrice', $this->mod), 0, array(), $total, $total);
		$fObj = $this->newForm('addAccDirect3', 'addAccDirect3', array('caption' => '{Call:Lang:modules:billing:vnestinovuiu}'));

		$this->setCreateServiceForm($fObj, $this->values['entryId'], '', '', false, 'form');
		$fObj->setValues($values);
		$this->setContent($this->getFormText($fObj, array(), array('entryId' => $this->values['entryId']), 'big100'));
	}

	protected function func_addAccDirect3(){
		/*
			Создает услугу форсированно
		*/

		if(!$this->check()) return false;

		$eData = $this->getOrderEntry($this->values['entryId']);
		$this->setOrderEntryUserParams($this->values['entryId'], $this->values['term'], $this->values['acc_ident'], '', '', '', array(), 0, false);
		$this->checkEntry($this->values['entryId']);

		$this->setEntry($this->values['entryId'], '');
		$sData = $this->serviceData($eData['service']);
		$this->back('orders&id='.$sData['id'], '', '', '');
	}

	protected function func_ordersData(){
		/*
			Параметры заказа
		*/

		$data = $this->getOrderedService($this->values['id']);
		$sData = $this->serviceData($data['service'], $data['package']);
		$values = $this->DB->rowFetch(array('order_services', array('price', 'modify_price', 'ind_price', 'ident', 'server', 'suspend_reason_descript', 'step', 'suspend_reason', 'auto_prolong', 'auto_prolong_fract'), "`id`='{$this->values['id']}'"));

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'ordersData2',
						'ordersData2',
						array(
							'caption' => '{Call:Lang:modules:billing:skorrektirov}'
						)
					),
					'service_params',
					array(
						'baseTerm' => $sData['base_term'],
						'pTerms' => $this->getProlongTermsList($data['service'], $data['package']),
						'currency' => $this->currencyName($sData['currency']),
						'servers' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzovat}'), $this->getConnections($data['service'])),
						'pkgs' => $this->getPackages($data['service'])
					)
				),
				$values,
				array('modify' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_ordersData2(){
		/*
			Корректирует параметры заказа
		*/

		$data = $this->getOrderedService($this->values['modify']);
		$sData = $this->serviceData($data['service']);
		return $this->typeIns('order_services', 'service_params', 'orders&id='.$sData['id']);
	}

	protected function func_ordersActions(){
		/*
			Операции над заказанными услугами
		*/

		if(empty($this->values['entry'])){
			$this->back($back, '{Call:Lang:modules:billing:neotmechenon}');
			return false;
		}

		$sData = $this->serviceDataById($this->values['serviceId']);
		switch($this->values['action']){
			case 'prolong': return $this->prolongServices();
			case 'modify': return $this->modifyServices();
			case 'transmit': return $this->transmitServices();
			case 'suspend': return $this->suspendServices();
			case 'unsuspend': return $this->unsuspendServices();
			case 'delete': return $this->deleteServices();
		}
	}

	private function getAccs($entries = false){
		/*
			Список аккаунтов
		*/

		return $this->DB->columnFetch(array('order_services', 'ident', 'id', $this->getEntriesWhere($entries)));
	}

	private function transmitServices(){
		/*
			Продление списка услуг
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm('transmitServices2', 'transmitServices2', array('caption' => 'Передать услуги в другой аккаунт')),
					'transmit_services_admin'
				),
				array(),
				$this->values,
				'big'
			)
		);
	}

	protected function func_transmitServices2(){
		/*
			Продление списка услуг
		*/

		$clientId = $this->getClientByIdOrLogin($this->values['user']);
		if(!$clientId) $this->setError('Такой пользователь не найден либо он не является клиентом');
		if(!$this->check()) return false;

		foreach($this->values['entry'] as $i => $e){
			if($id = $this->addTransmitOrder($i, $clientId)) $this->endTransmitOrder($id);
		}

		$this->back('orders&id='.$this->values['serviceId'], '', '', '');
	}

	private function prolongServices(){
		/*
			Продление списка услуг
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm('prolongServices2', 'prolongServices2', array('caption' => 'Продление услуг')),
					'prolong_services_admin'
				),
				array(),
				$this->values,
				'big'
			)
		);
	}

	protected function func_prolongServices2(){
		/*
			Продление списка услуг. Расчитывает для каждой услуги срок и ценник.
		*/

		if(!$this->check()) return false;

		$serviceData = $this->serviceDataById($this->values['serviceId']);
		$fObj = $this->newForm('prolongServices3', 'prolongServices3', array('caption' => 'Продление услуг'));

		if($this->values['prolong_type'] == 'days') $len = Dates::term2sec('day', $this->values['days']);
		$values = array();
		$hiddens = array('serviceId' => $this->values['serviceId'], 'eIds' => array());
		$j = 0;

		foreach($this->values['entry'] as $i => $e){
			$sData = $this->getOrderedService($i);
			$hiddens['eIds'][$i] = $this->addNewOrderEntry('prolong', $sData['client_id'], $sData['service'], $sData['package'], 0, $i);
			$this->setOrderEntryCaption($hiddens['eIds'][$i], 'Продление услуги для "'.$sData['ident'].'"');
			$this->setOrderEntryUserParams($hiddens['eIds'][$i], 0, $sData['ident']);

			$pkgData = $this->serviceData($serviceData['name'], $sData['package']);
			$this->addFormBlock($fObj, 'prolong_service', array('baseTerm' => $serviceData['base_term'], 'currency' => $pkgData['currency'], 'id' => $i), array(), 'block'.$j);
			$fObj->setParam('caption'.$j, $sData['ident']);

			$values['prolong_price'.$i] = $sData['price'];
			$values['modify_price'.$i] = $sData['modify_price'];
			$values['ind_price'.$i] = $sData['ind_price'];

			if($this->values['prolong_type'] == 'date'){
				$values['paid_to'.$i] = $this->values['date'];
				$values['sum'.$i] = round(Dates::sec2term($serviceData['base_term'], $this->values['date'] - $sData['paid_to'], false) * ($sData['price'] + $sData['modify_price']), 2);
			}
			elseif($this->values['prolong_type'] == 'days'){
				$values['paid_to'.$i] = $sData['paid_to'] + $len;
				$values['sum'.$i] = round(Dates::sec2term($serviceData['base_term'], $len, false) * ($sData['price'] + $sData['modify_price']), 2);
			}
			else throw new AVA_Exception('Неопределенный тип продления');

			$values['discount'.$i] = 0;
			$values['total'.$i] = $values['sum'.$i];
			$j ++;
		}

		$fObj->setValues($values);
		$this->setContent($this->getFormText($fObj, array(), $hiddens, 'multiblock'));
	}

	protected function func_prolongServices3(){
		/*
			Завершает продление
		*/

		foreach($this->values['eIds'] as $i => $e){
			$eData = $this->getOrderEntry($e);
			$this->checkEntry($e, $i, $this->values['last_paid'.$i]);
		}

		if(!$this->check()) return false;
		foreach($this->values['eIds'] as $i => $e) $this->setEntry($e, $i);
		$this->back('orders&id='.$this->values['serviceId'], '', '', '');
	}

	private function modifyServices(){
		/*
			Модификация услуг
		*/

		$sData = $this->serviceDataById($this->values['serviceId']);
		$fObj = $this->newForm('modifyServices2', 'modifyServices2', array('caption' => 'Смена тарифного плана'));
		$this->setAccModifyMatrix($fObj, $sData['name'], '', 'form', true);
		$this->setContent($this->getFormText($fObj, array(), $this->values, 'big'));
	}

	protected function func_modifyServices2(){
		/*
			Модификация услуг. Шаг2
		*/

		$sData = $this->serviceDataById($this->values['serviceId']);
		$this->checkAccModifyMatrix($sData['name'], '', true);
		if(!$this->check()) return false;

		$fObj = $this->newForm('modifyServices3', 'modifyServices3', array('caption' => 'Смена тарифного плана. Дополнительные параметры.'));
		$this->setAccModifyMatrix2($fObj, $id = $this->newServiceMainModify($sData['name'], $this->values['pkg'], $this->values['entry'], 'a'), 'form', true);
		$this->setContent($this->getFormText($fObj, array(), array('serviceId' => $this->values['serviceId'], 'id' => $id), 'big100'));
	}

	protected function func_modifyServices3(){
		/*
			Расчетные цены модификации
		*/

		$this->checkAccModifyMatrix2($this->values['id'], true);
		if(!$this->check()) return false;

		$this->DB->Upd(array('modify_service_orders', array('status' => 4), "`main_id`='{$this->values['id']}' AND `status`<4"));
		$this->setServiceModifyExtraParams($this->values['id'], false, true);

		foreach($this->getServiceMainModifyOrders($this->values['id']) as $e){
			$this->prepareServiceModifyBasePrice($e);
			$this->prepareServiceModifyPayData($e, false, true);
		}

		$this->setContent(
			$this->getFormText(
				$this->generateModifyOrderMultiForm($this->values['id'], false),
				array(),
				array('id' => $this->values['id'], 'serviceId' => $this->values['serviceId']),
				'multiblock'
			)
		);
	}

	protected function func_modifyServices4(){
		/*
			Завершает модификацию всех услуг
		*/

		if(!$this->check()) return false;
		$this->modifyServicesEnd($this->values['id'], $this->setModify($this->values['id'], $this->getServiceMainModifyOrders($this->values['id'])));
		$this->back('orders&id='.$this->values['serviceId'], '', '', '');
	}

	private function suspendServices(){
		/*
			Блокировка услуг
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'suspendServices2',
						'suspendServices2',
						array(
							'caption' => 'Блокирование аккаунтов'
						)
					),
					'suspend_services_admin',
					array('accs' => $this->getAccs())
				),
				array(),
				$this->values,
				'big'
			)
		);
	}

	protected function func_suspendServices2(){
		/*
			Удаление услуг по списку
		*/

		if(!$this->check()) return false;

		$sData = $this->serviceDataById($this->values['serviceId']);
		$list = array();

		foreach($this->values['entry'] as $i => $e){
			if(!empty($this->values['suspend'.$i])){
				if($sId = $this->addSuspendOrder($i, $this->values['type'.$i], $this->values['reason'.$i])){
					$list[$sId] = $this->getActionServiceValues($i);
				}
			}
		}

		$this->setSuspendServiceList($sData['name'], $list);
		$this->back('orders&id='.$this->values['serviceId'], '', '', '');
	}

	private function unsuspendServices(){
		/*
			Блокировка услуг
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'unsuspendServices2',
						'unsuspendServices2',
						array(
							'caption' => 'Блокирование аккаунтов'
						)
					),
					'unsuspend_services_admin',
					array('accs' => $this->getAccs())
				),
				array(),
				$this->values,
				'big'
			)
		);
	}

	protected function func_unsuspendServices2(){
		/*
			Удаление услуг по списку
		*/

		if(!$this->check()) return false;

		$sData = $this->serviceDataById($this->values['serviceId']);
		$list = array();

		foreach($this->values['entry'] as $i => $e){
			if(!empty($this->values['unsuspend'.$i])){
				if($uId = $this->addUnsuspendOrder($i, $this->values['type'.$i], $this->values['reason'.$i])){
					$list[$uId] = $this->getActionServiceValues($i);
				}
			}
		}

		$this->setUnsuspendServiceList($sData['name'], $list);
		$this->back('orders&id='.$this->values['serviceId'], '', '', '');
	}

	private function deleteServices(){
		/*
			Удаление услуг
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'deleteServices2',
						'deleteServices2',
						array(
							'caption' => 'Удаление аккаунтов'
						)
					),
					'delete_services',
					array('accs' => $this->getAccs())
				),
				array(),
				$this->values,
				'big100'
			)
		);
	}

	protected function func_deleteServices2(){
		/*
			Удаление услуг по списку
		*/

		if(!$this->check()) return false;

		$sData = $this->serviceDataById($this->values['serviceId']);
		$list = array();

		foreach($this->values['entry'] as $i => $e){
			if(!empty($this->values['delete'.$i])){
				if($dId = $this->addDeleteOrder($i)){
					$this->addDeleteOrderPays($dId, $this->values['stay'.$i] > 0 ? $this->values['stay'.$i] : 0, $this->values['stay'.$i] < 0 ? -$this->values['stay'.$i] : 0, $this->values['date'.$i]);
					$this->addDeleteOrderReason($i, $this->values['type'.$i], $this->values['reason'.$i]);
					$list[$dId] = $this->getActionServiceValues($i);
				}
			}
		}

		$this->setDeleteServiceList($sData['name'], $list);
		$this->back('orders&id='.$this->values['serviceId'], '', '', '');
	}





















































	protected function func_ordersActions2(){
		/*
			2 шаг actions над заказами
		*/

		switch($this->values['action']){
			case 'transmit':
				if(!($userData = $this->Core->getUserParamsById($this->values['new_owner']))){
					$this->setError('new_owner', '{Call:Lang:modules:billing:novyjpolzova}');
				}
				break;

			case 'modify':
				if(!empty($this->values['modify']) && empty($this->values['isModify'])) return $this->ordersActionsModify();
				break;
		}

		$sData = $this->serviceDataById($this->values['serviceId']);
		$entries = $this->getSelectedServicesEntries($sData['name']);

		$clientsFilter = array();
		foreach($entries as $e){
			foreach($e as $r){
				$clientsFilter[] = "`id`='{$r['user_id']}'";
				if($this->values['action'] == 'transmit' && isset($userData['id']) && $userData['id'] == $r['user_id']){
					$this->setError('new_owner', '{Call:Lang:modules:billing:dlianachalny:'.Library::serialize(array($r['ident'])).'}');
				}
			}
		}

		foreach($this->Core->DB->columnFetch(array('users', 'eml', 'id', implode(' OR ', $clientsFilter))) as $i => $e){
			foreach($entries as $i1 => $e1){
				if(isset($e1[$i])) $entries[$i1][$i]['eml'] = $e;
			}
		}

		if(!$this->check()){
			return false;
		}

		$return = $this->{$this->values['action'].'OrderedServices'}($entries, $sData['name']);
		$this->back('orders&id='.$this->values['serviceId'], $this->getErrorsList().$this->getPrintMessages(), '', '');
		return $return;
	}

	protected function ordersActionsModify(){
		/*
			Мудрифицировать услсгу по инд. торифу
		*/

		if(!$this->check()){
			return false;
		}

		$sData = $this->serviceDataById($this->values['serviceId']);
		$pkgData = $this->serviceData($sData['name'], $this->values['pkg']);

		$matrix = $this->getPkgDescriptForm(
			$sData['name'],
			$pkgData['name'],
			'mpkg',
			'mpkg_',
			'',
			$values,
			array($this->getConnectionCp($pkgData['server']))
		);

		$this->values['isModify'] = 1;
		$this->setContent($this->getBasePkgDescript($this->getPkgBase($pkgData['vars'], $matrix)));
		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'ordersActions2',
						'ordersActions2',
						array(
							'currency' => $this->getCurrencyNameByPkg($sData['name'], $this->values['pkg']),
							'sData' => $sData,
							'pkgData' => $pkgData
						)
					),
					$matrix
				),
				$values,
				$this->values,
				'big'
			)
		);
	}


	/********************************************************************************************************************************************************************

																			Комплексные заказы

	*********************************************************************************************************************************************************************/

	protected function func_complex(){
		/*
			Создание комплаксных заказов
		*/

		$form = $this->newForm('complex', 'complexNew', array('caption' => 'Добавить комплекс услуг'));
		$this->addFormBlock($form, 'complex', array(), array(), 'block0');
		$this->addFormBlock($form, 'complex_price', array('pays' => $this->fetchPayments(), 'smsPays' => $this->getSms(), 'smsNumbers' => $this->getSmsNumbersByAgr(), 'currency' => Library::array_merge(array('' => $this->currencyName('')), $this->getCurrency())), array(), 'block1');
		$this->addFormBlock($form, 'complex_services', array('services' => $this->getServices(), 'packages' => $this->getAllPackages()), array(), 'block2');

		$form->setParam('caption0', 'Общие параметры');
		$form->setParam('caption1', 'Расценки');
		$form->setParam('caption2', 'Услуги');
		$this->setContent($this->getFormText($form, array(), array(), 'multiblock'));

		$this->setContent(
			$this->getListText(
				$this->newList(
					'complex_list',
					array(
						'req' => array('complex', array('id', 'name', 'text', 'sort', 'show'), '', '`sort`'),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:billing:skryt}',
							'unsuspend' => '{Call:Lang:modules:billing:otkryt}',
							'delete' => '{Call:Lang:modules:billing:udalit}',
						),
						'actions' => array(
							'text' => 'complexData'
						),
						'action' => 'complexActions',
						'table' => 'complex',
						'searchForm' => array(
							'searchFields' => array(
								'name' => 'Имя',
								'text' => 'Идентификатор',
								'show' => '',
							),
							'orderFields' => array(
								'name' => 'имени',
								'text' => 'идентификатору'
							)
						)
					),
					array(
						'caption' => 'Список комплектов услуг'
					)
				)
			)
		);
	}

	protected function func_complexNew(){
		/*
			Создает новый комплекс
		*/

		$this->isUniq('complex', array('name' => 'Такой идентификатор уже используется', 'text' => 'Такое имя уже используется'), isset($this->values['modify']) ? $this->values['modify'] : '');
		$fields = $this->fieldValues(array('text', 'name', 'sort', 'show'));
		$fields['vars'] = array('pays' => array(), 'smsPays' => array(), 'services' => array());

		foreach($this->fetchPayments() as $i => $e) $fields['vars']['pays'][$i] = $this->values['price_'.$i];
		foreach($this->getSms() as $i => $e) $fields['vars']['smsPays'][$i] = $this->values['price_sms_'.$i];

		foreach($this->getAllPackages() as $i => $e){
			foreach($e as $i1 => $e1){
				$fields['vars']['services'][$i][$i1] = array(
					'count' => $this->values['count_'.$i.'_'.$i1],
					'term' => isset($this->values['term_'.$i.'_'.$i1]) ? $this->values['term_'.$i.'_'.$i1] : ''
				);
			}
		}

		return $this->typeIns('complex', $fields, 'complex');
	}

	protected function func_complexData(){
		/*
			Параметры комплекта
		*/

		$values = $this->DB->rowFetch(array('complex', '*', "`id`='{$this->values['id']}'"));
		$values['vars'] = Library::unserialize($values['vars']);

		foreach($values['vars']['pays'] as $i => $e) $values['price_'.$i] = $e;
		foreach($values['vars']['smsPays'] as $i => $e) $values['price_sms_'.$i] = $e;
		foreach($values['vars']['services'] as $i => $e){
			foreach($e as $i1 => $e1){
				$values['count_'.$i.'_'.$i1] = $e1['count'];
				$values['term_'.$i.'_'.$i1] = $e1['term'];
			}
		}

		$form = $this->newForm('complex', 'complexNew', array('caption' => 'Изменить комплекс "'.$values['text'].'"'));
		$this->addFormBlock($form, 'complex', array('modify' => $this->values['id']), array(), 'block0');
		$this->addFormBlock($form, 'complex_price', array('pays' => $this->fetchPayments(), 'smsPays' => $this->getSms(), 'smsNumbers' => $this->getSmsNumbersByAgr(), 'currency' => Library::array_merge(array('' => $this->currencyName('')), $this->getCurrency())), array(), 'block1');
		$this->addFormBlock($form, 'complex_services', array('services' => $this->getServices(), 'packages' => $this->getAllPackages()), array(), 'block2');

		$form->setParam('caption0', 'Общие параметры');
		$form->setParam('caption1', 'Расценки');
		$form->setParam('caption2', 'Услуги');
		$this->setContent($this->getFormText($form, $values, array('modify' => $this->values['id']), 'multiblock'));
	}

	protected function func_complexActions(){
		return $this->typeActions('complex', 'complex');
	}



	/********************************************************************************************************************************************************************

																		Запросы на смену тарифа

	*********************************************************************************************************************************************************************/

	protected function func_modifyOrders(){
		/*
			Список запросов
		*/

		$sData = $this->serviceDataById($this->values['id']);

		$p = $this->DB->getPrefix();
		$t1 = $p.'modify_service_orders';
		$t2 = $p.'modify_service_main_orders';

		$t3 = $p.'order_services';
		$t4 = $p.'order_packages';
		$t5 = $p.'clients';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'modify_orders_list',
					array(
						'req' => "SELECT t1.*, t2.date, t2.init, t2.service, t2.pkg, t2.vars, t3.client_id, t3.ident, t3.package AS `old_pkg`, t4.currency, t5.user_id ".
							"FROM $t1 AS t1 ".
							"LEFT JOIN $t2 AS t2 ON t1.main_id=t2.id ".
							"LEFT JOIN $t3 AS t3 ON t1.service_order_id=t3.id ".
							"LEFT JOIN $t4 AS t4 ON t2.pkg=t4.name AND t2.service=t4.service ".
							"LEFT JOIN $t5 AS t5 ON t3.client_id=t5.id ".
							"WHERE t1.status>=2 AND t2.service='{$sData['name']}' ORDER BY t2.date DESC",
						'extraReqs' => array(
							array(
								'req' => array('users', array('id', 'login', 'name', 'eml')),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'user_id',
								'unitedFld2' => 'id',
								'prefix' => 'user_'
							)
						),
						'actions' => array('modifyRun' => 'modifyRun&serviceId='.$this->values['id'])
					),
					array('caption' => 'Заявки на смену тарифа услуги "'.$sData['text'].'"')
				),
				'big'
			)
		);
	}

	protected function func_modifyRun(){
		/*
			Форма модификации в админке
		*/

		$this->funcName = 'Заявка на модификацию №'.$this->values['id'];
		$this->pathFunc = 'modifyOrders&id='.$this->values['serviceId'];
		$this->setContent($this->getFormText($this->generateModifyOrderSimpleForm($this->values['id']), array(), array('serviceId' => $this->values['serviceId']), 'big100'));
	}

	protected function func_modifyEnd(){
		/*
			Процiдурка вiполнення модификацiи тарiфа
		*/

		$mData = $this->getServiceModifyData($this->values['id']);
		$this->checkAccModifyMatrix($mData['service'], $mData['pkg'], true);
		$this->checkAccModifyMatrix2($mData['main_id'], true);

		if(!$this->check()) return false;
		$this->modifyServicesEnd($mData['main_id'], $this->setModify($mData['main_id'], $this->values['id']));
		$this->back('modifyOrders&id='.$this->values['serviceId'], '', '', '');
	}


	/********************************************************************************************************************************************************************

																		Тарифные планы

	*********************************************************************************************************************************************************************/

	protected function func_packages(){
		/*
			Добавляет тарифный план
		 */

		$id = db_main::Quot($this->values['id']);
		$data = $this->serviceDataById($id);

		$this->pathFunc = 'services';
		$this->funcName = '{Call:Lang:modules:billing:tarifnyeplan:'.Library::serialize(array($data['text'])).'}';
		$form = $this->newForm('packagesNew', 'packagesNew&serviceId='.$id, array('caption' => '{Call:Lang:modules:billing:dobavittarif}'));

		$groups = $this->getPkgGroups($data['name'], "", false);
		$mGroups = $this->getPkgGroups($data['name'], "AND `main`='1'");
		$currency = Library::array_merge(array('' => '{Call:Lang:modules:billing:vsegdavaliut}'), $this->getCurrency());
		$connections = $this->getConnections($data['extension']);

		$this->addFormBlock(
			$form,
			'package',
			array(
				'groups' => $groups,
				'main_groups' => $mGroups,
				'pkgs' => Library::array_merge(array('' => '{Call:Lang:modules:billing:sozdatpustoj}'), $this->getPkgs($data['name'])),
				'currency' => $currency,
				'connections' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzovat}'), $connections)
			)
		);

		$this->callServiceObj('setAddPkgMatrix', $data['name'], array('fObj' => $form));
		$this->setContent($this->getFormText($form));

		$searchFields = array(
			'server' => '{Call:Lang:modules:billing:server}',
			'main_group' => '{Call:Lang:modules:billing:gruppa}',
			'text' => '{Call:Lang:modules:billing:imia}',
			'server_name' => '{Call:Lang:modules:billing:imianaserver}',
			'name' => '{Call:Lang:modules:billing:identifikato3}',
			'price' => '{Call:Lang:modules:billing:tsena}',
			'prolong_price' => '{Call:Lang:modules:billing:tsenaprodlen1}',
			'install_price' => '{Call:Lang:modules:billing:tsenaustanov2}',
			'currency' => '{Call:Lang:modules:billing:valiutauslug}',
			'test' => '{Call:Lang:modules:billing:testovyjsrok1}',
			'terms' => '{Call:Lang:modules:billing:dopustimyesr}',
			'prolong_terms' => '{Call:Lang:modules:billing:dopustimyesr1}',
		);

		$prefixes = array(
			'server' => 't1',
			'main_group' => 't1',
			'text' => 't1',
			'server_name' => 't1',
			'name' => 't1',
			'price' => 't1',
			'install_price' => 't1',
			'prolong_price' => 't1',
			'currency' => 't1',
			'terms' => 't1',
			'prolong_terms' => 't1',
			'test' => 't1'
		);

		$where = '';
		$order = "t1.sort";

		if(!empty($this->values['in_search'])){
			if(
				$where = $this->getListSearchWhere(
					$this->values,
					$searchFields,
					$prefixes,
					array('main_group' => true, 'server' => true, 'price' => true, 'install_price' => true, 'prolong_price' => true, 'currency' => true, 'test' => true)
				)
			) $where = ' AND '.$where;

			if(!empty($this->values['search_sort'])){
				$order = $this->getListSearchOrder(
					$this->values['search_sort'],
					$this->values['search_direction'],
					array('currency' => 't1', 'terms' => 't1', 'prolong_terms' => 't1', 'test' => 't1', 'text' => 't1', 'server_name' => 't1', 'name' => 't1', 'price' => 't1', 'install_price' => 't1', 'prolong_price' => 't1')
				);
			}
		}

		$p = $this->DB->getPrefix();
		$t1 = $p.'order_packages';
		$t2 = $p.'packages_'.$data['name'];
		$t3 = $p.'package_groups';
		$t4 = $p.'connections';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'packages_list',
					array(
						'req' => "SELECT t1.*, t2.*, t3.text AS grp_name, t4.text AS c_name
							FROM $t1 AS t1
								LEFT JOIN $t2 AS t2 ON t1.`id`=t2.`package_id`
								LEFT JOIN $t3 AS t3 ON t1.main_group=t3.name
								LEFT JOIN $t4 AS t4 ON t1.server=t4.name
							WHERE t1.`service`='{$data['name']}' {$where} ORDER BY {$order}",
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:billing:skryt}',
							'unsuspend' => '{Call:Lang:modules:billing:otkryt}',
							'delete' => '{Call:Lang:modules:billing:udalittolkov}',
							'deleteAll' => '{Call:Lang:modules:billing:udalitvbilli}'
						),
						'actions' => array(
							'text' => 'packagesData&serviceId='.$id
						),
						'action' => 'packagesActions&serviceId='.$id,
						'table' => 'order_packages',
						'searchForm' => array(
							'searchFields' => $searchFields,
							'orderFields' => array(
								'grp_name' => '{Call:Lang:modules:billing:gruppe}',
								'currency' => '{Call:Lang:modules:billing:valiute}',
								'terms' => '{Call:Lang:modules:billing:minimalnomus}',
								'prolong_terms' => '{Call:Lang:modules:billing:minimalnomus1}',
								'test' => '{Call:Lang:modules:billing:testovomusro}',
								'text' => '{Call:Lang:modules:billing:imeni}',
								'server_name' => '{Call:Lang:modules:billing:imeninaserve}',
								'name' => '{Call:Lang:modules:billing:identifikato12}',
								'price' => '{Call:Lang:modules:billing:tsene}',
								'install_price' => '{Call:Lang:modules:billing:tseneustanov}',
								'prolong_price' => '{Call:Lang:modules:billing:tseneprodlen}'
							),
							'searchMatrix' => array(
								'price' => array('type' => 'gap'),
								'install_price' => array('type' => 'gap'),
								'prolong_price' => array('type' => 'gap'),
								'terms' => array('type' => 'gap'),
								'prolong_terms' => array('type' => 'gap'),
								'test' => array('type' => 'gap'),
								'server' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}', '0' => '{Call:Lang:modules:billing:net}'), $connections)
								),
								'main_group' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $groups)
								),
								'currency' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $currency)
								)
							),
							'searchParams' => array(
								'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$id
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:vsetarifyusl:'.Library::serialize(array($data['text'])).'}',
						'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl('packages&id='.$id).'&table=order_packages',
						'sType' => $data['type']
					)
				)
			)
		);
	}

	protected function func_packagesNew(){
		/*
			Добавляем пакет
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$serviceId = db_main::Quot($this->values['serviceId']);
		$data = $this->DB->rowFetch(array('services', array('name', 'text', 'extension'), "`id`='$serviceId'"));

		$tbl = 'packages_'.$data['name'];
		$this->values['groups'] = empty($this->values['groups']) ? '' : Library::arrKeys2str($this->values['groups']);
		$this->values['service'] = $data['name'];

		$this->isUniq(
			'order_packages',
			array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'),
			$id,
			" AND `service`='{$data['name']}'"
		);

		if(!$this->check()) return false;

		if($this->values['pkgs']){
			$newId = $this->packageFromIsset();
		}
		else{
			$fields = $this->fieldValues(array('name', 'text', 'server_name', 'server', 'service', 'main_group', 'groups', 'show'));
			$fields['vars'] = array('rights' => array('new' => 1, 'prolong' => 1, 'changeGrp' => 1, 'changeSrv' => 1, 'changeDn' => 1, 'changeUp' => 1, 'pause' => 1, 'del' => 1));
			$newId = $this->typeIns('order_packages', $fields, 'packages&id='.$serviceId);
		}

		if($newId){
			$this->DB->Ins(array($tbl, array('package_id' => $newId)));
			$this->redirect('packagesData&id='.$newId.'&serviceId='.$serviceId);

			$this->values['service'] = db_main::Quot($this->values['service']);
			if(!$this->DB->cellFetch(array('order_packages', 'id', "`service`='{$this->values['service']}' AND `id`!='$newId'"))){
				$this->insertLink(
					array(
						'text' => $data['text'],
						'name' => 'packages_'.$this->values['service'],
						'mod' => $this->mod,
						'url' => 'index.php?mod='.$this->mod.'&func=packages&service='.$this->values['service'],
						'usedCmsLevel' => array('mainmenu', 'menu1')
					),
					'packages'
				);
			}

			$where[] = "`main_group`='{$this->values['main_group']}'";
			$grpWhere[] = "`name`='{$this->values['main_group']}'";

			foreach($this->values['groups'] as $i => $e){
				$where[] = "`groups` REGEXP (',{$i},')";
				$grpWhere[] = "`name`='{$i}'";
			}

			$where = '('.implode(' OR ', $where).')';
			$grpWhere = '('.implode(' OR ', $grpWhere).')';
			$groups = $this->DB->columnFetch(array('package_groups', array('name', 'text'), 'name', "`service`='{$this->values['service']}' AND ($grpWhere)"));

			foreach($groups as $i => $e){
				if(!$this->DB->cellFetch(array('order_packages', 'id', "`service`='{$this->values['service']}' AND `id`!='$newId' AND (`main_group`='$i' OR `groups` REGEXP (',{$i},'))"))){
					$this->insertLink(
						array(
							'text' => $e['text'],
							'name' => 'packages_'.$this->values['name'].'_'.$i,
							'mod' => $this->mod,
							'url' => 'index.php?mod='.$this->mod.'&func=packages&service='.$this->values['service'].'&grp='.$i,
							'usedCmsLevel' => array('mainmenu', 'menu1')
						),
						'packages_'.$this->values['service']
					);
				}
			}
		}
		else{
			$this->back('packages', 'Ошибка создания пакета');
		}

		return $newId;
	}

	private function packageFromIsset(){
		/*
			Создает тариф из существующего
		*/

		$name = db_main::Quot($this->values['pkgs']);
		$params = $this->DB->rowFetch(array('order_packages', '*', "`name`='$name'"));
		unset($params['id']);

		$params['name'] = $this->values['name'];
		$params['text'] = $this->values['text'];
		$params['server_name'] = $this->values['server_name'];
		$params['server'] = $this->values['server'];

		$params['sort'] = $this->values['sort'];
		$params['main_group'] = $this->values['main_group'];
		$params['groups'] = $this->values['groups'];
		$params['show'] = $this->values['show'];

		return $this->DB->Ins(array('order_packages', $params));
	}

	protected function func_packagesData(){
		/*
			Выставление данных по тарифу
		 */

		$id = db_main::Quot($this->values['id']);
		$sid = $this->values['serviceId'];
		$data = $this->serviceDataById($sid);

		$p = $this->DB->getPrefix();
		$tbl = $p.'packages_'.$data['name'];
		$values = $this->DB->rowFetch("SELECT t1.*, t2.*, t3.extension AS connect_name
			FROM {$p}order_packages AS t1 LEFT JOIN {$tbl} AS t2 ON t1.id=t2.package_id
			LEFT JOIN {$p}connections AS t3 ON t1.server=t3.name WHERE t1.id='$id' LIMIT 1");

		$this->pathFunc = 'services';
		$this->funcName = '{Call:Lang:modules:billing:uslugatarif:'.Library::serialize(array($data['text'], $values['text'])).'}';
		$vars = Library::unserialize($values['vars']);

		if(empty($vars['notify_rights'])){
			$vars['notify_rights'] = array(
				'notify_settings_type' => 'useMain',
				'mail_tmpl_new' => 'newService',
				'mail_tmpl_admin_new' => 'newServiceAdmin',
				'mail_tmpl_admin_new_fail' => 'newServiceFailAdmin',
				'notify_new' => '1',
				'notify_admin_new' => $this->Core->getParam('addAccsSuccessMail', $this->mod),
				'notify_fail_admin_new' => $this->Core->getParam('addAccsFailMail', $this->mod),
				'mail_tmpl_modify' => 'modifyService',
				'mail_tmpl_modify_admin' => 'modifyServiceAdmin',
				'mail_tmpl_modify_admin_fail' => 'modifyServiceFailAdmin',
				'notify_modify' => '1',
				'notify_admin_modify' => $this->Core->getParam('modifyServiceAdminMail', $this->mod),
				'notify_fail_admin_modify' => $this->Core->getParam('modifyServiceFailAdminMail', $this->mod),
				'mail_tmpl_prolong' => 'prolongService',
				'mail_tmpl_prolong_admin' => 'prolongServiceAdmin',
				'mail_tmpl_prolong_admin_fail' => 'prolongServiceFailAdmin',
				'notify_prolong' => '1',
				'notify_admin_prolong' => $this->Core->getParam('prolongServiceAdminMail', $this->mod),
				'notify_fail_admin_prolong' => $this->Core->getParam('prolongServiceFailAdminMail', $this->mod),
				'mail_tmpl_transmit' => 'transmitService',
				'mail_tmpl_transmit_new_client' => 'transmitServiceNewClient',
				'mail_tmpl_transmit_admin' => 'transmitServiceAdmin',
				'notify_transmit' => '1',
				'notify_transmit_new_client' => '1',
				'notify_admin_transmit' => $this->Core->getParam('transmitServiceAdminMail', $this->mod),
				'mail_tmpl_term_finish' => 'termFinishService',
				'mail_tmpl_term_finish_admin' => 'termFinishServiceAdmin',
				'term_finish_notify' => $this->Core->getParam('termFinishNotify', $this->mod),
				'notify_term_finish' => '1',
				'notify_admin_term_finish' => '1',
				'mail_tmpl_suspend' => 'suspendService',
				'mail_tmpl_suspend_admin' => 'suspendServiceAdmin',
				'term_finish_suspend' => $this->Core->getParam('termFinishSuspend', $this->mod),
				'notify_suspend' => '1',
				'notify_admin_suspend' => $this->Core->getParam('suspendServiceAdminMail', $this->mod),
				'mail_tmpl_unsuspend' => 'unsuspendService',
				'mail_tmpl_unsuspend_admin' => 'unsuspendServiceAdmin',
				'notify_unsuspend' => '1',
				'notify_admin_unsuspend' => $this->Core->getParam('unsuspendServiceAdminMail', $this->mod),
				'mail_tmpl_delete' => 'deleteService',
				'mail_tmpl_delete_admin' => 'deleteServiceAdmin',
				'term_finish_del' => $this->Core->getParam('termFinishDel', $this->mod),
				'notify_delete' => '1',
				'notify_admin_delete' => $this->Core->getParam('deleteServiceAdminMail', $this->mod),
			);
		}

		$values = Library::array_merge($values, $vars['notify_rights']);
		if(isset($vars['restrictions'])) $values = Library::array_merge($values, $vars['restrictions']);
		$values['groups'] = Library::str2arrKeys($values['groups']);
		$values['rights'] = $vars['rights'];

		$hiddens = array('modify' => $id);
		$mGrp = $values['main_group'];

		$blMatrix = $this->getPkgDescriptForm($data['name'], $values['name'], 'apkg', 'pkgdsc_', '', $values, array($values['connect_name']));
		if(!empty($vars['params'])) foreach($vars['params'] as $i => $e) $values['pkgdsc_'.$i] = $e;
		if(!empty($vars['extraDescript'])) foreach($vars['extraDescript'] as $i => $e) $values['pkgdsc2_'.$i] = $e;

		$form = $this->newForm('packagesData2', 'packagesData2&serviceId='.$sid, array('caption' => '{Call:Lang:modules:billing:dobavittarif}'), $this->Core->getModuleTemplatePath($this->mod).'form.tmpl');
		$formData = array(
			'groups' => $this->getPkgGroups($data['name'], '', false),
			'main_groups' => $this->getPkgGroups($data['name'], "AND `main`='1'"),
			'connections' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzovat}'), $this->getConnections($data['extension'])),
			'extra' => '1',
			'baseTerm' => $data['base_term'],
			'testTerm' => $data['test_term'],
			'currency' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vsegdavaliut}'), $this->getCurrency()),
			'type' => $data['type']
		);

		$y = 0;
		$this->addFormBlock( $form, 'package', $formData, array(), 'block'.$y );
		$form->setParam('caption'.$y, '{Call:Lang:modules:billing:nastrojki}');
		$y ++;

		$this->addFormBlock( $form, 'package_price', $formData, array(), 'block'.$y );
		$form->setParam('caption'.$y, '{Call:Lang:modules:billing:tseny}');
		$y ++;

		if($blMatrix){
			$this->addFormBlock( $form, $blMatrix, $formData, array(), 'block'.$y );
			$form->setParam('caption'.$y, '{Call:Lang:modules:billing:parametrytar}');
			$y ++;
		}
		$this->callServiceObj('setAddPkgMatrix', $data['name'], array('fObj' => $form, 'server' => $values['server'], 'pkgData' => $values));

		$this->addFormBlock($form, 'package_restrictions', $formData, array(), 'block'.$y);
		$form->setParam('caption'.$y, 'Ограничения на заказ тарифа');
		$y ++;

		$this->addFormBlock(
			$form,
			'package_mail',
			array(
				'mailTemplates' => $this->Core->DB->columnFetch(array('mail_templates', 'text', 'name', "`mod`='{$this->mod}'")),
				'admins' => Library::array_merge(array('' => 'Всегда суперпользователь'), $this->Core->getAdminsList())
			),
			array(),
			'block'.$y
		);

		$form->setParam('caption'.$y, '{Call:Lang:modules:billing:parametryotp}');
		$y ++;
		$caps = array('aacc' => '{Call:Lang:modules:billing:parametryfor}', 'opkg' => '{Call:Lang:modules:billing:parametryfor1}', 'mpkg' => '{Call:Lang:modules:billing:parametrydli}', 'pkg_list' => '{Call:Lang:modules:billing:parametrydli1}');

		foreach($this->getPkgElementForm($data['name'], $values['name'], 'pkgdsc2_', '', $values, array($values['connect_name'])) as $i => $e){
			if($e){
				$this->addFormBlock($form, $e, $formData, array(), 'block'.$y);
				$form->setParam('caption'.$y, $caps[$i]);
				$y ++;
			}
		}

		$this->setContent($this->getFormText($form, $values, $hiddens, 'multiblock2'));
	}

	protected function func_packagesData2(){
		/*
			Обновляем данные о ТП. Создаем ТП на удаленном сервере
		 */

		$id = db_main::Quot($this->values['modify']);
		$sid = $this->values['serviceId'];

		$this->sData = $this->serviceDataById($sid);
		$service = $this->sData['name'];
		$pkgData = $this->serviceData($service, $this->values['name']);

		$extraPkgVars = $this->callServiceObj(
			'checkAddPkgMatrix',
			$service,
			array('server' => $this->values['server']),
			array('type' => 'order_packages', 'id' => $id)
		);

		if(!$this->check()){
			return false;
		}

		$return = true;
		if(!empty($this->values['create_on_server']['default']) && !empty($this->values['server'])){
			if($this->callServiceObj('addPkg', $service, array('server' => $this->values['server']), array('type' => 'order_packages', 'id' => $id))){
				$this->setContent('{Call:Lang:modules:billing:tarifnyjplan1}', 'refresh_msg');
			}
			else{
				$this->setContent('{Call:Lang:modules:billing:neudalossozd1}', 'refresh_msg');
				$return = false;
			}

			if($rid = $this->getConnectionResultId($this->values['server'])){
				$this->setContent('{Call:Lang:modules:billing:otvetservera1:'.Library::serialize(array($this->path, $this->mod, $rid)).'}', 'refresh_msg');
			}
		}

		$fields = $this->fieldValues(array('name', 'text', 'server_name', 'server', 'main_group', 'show', 'currency', 'price', 'price2', 'prolong_price', 'install_price', 'change_down_price', 'change_up_price', 'change_srv_price', 'change_grp_price', 'change_modify_price', 'del_price', 'pause_start_price', 'pause_stop_price', 'terms', 'prolong_terms', 'test', 'max_test_accs', 'pay_test_install', 'pay_test_modify', 'inner_test', 'fract_prolong', 'sort'));
		$fields['service'] = $service;
		$fields['groups'] = isset($this->values['groups']) ? Library::arrKeys2str($this->values['groups']) : '';

		$fields['vars'] = array(
			'params' => Library::array_merge(
				$this->getPkgParams($service, 'apkg', 'pkgdsc_', '', array($this->getConnectionCp($this->values['server']))),
				$extraPkgVars
			),
			'rights' => $this->values['rights'],
			'restrictions' => $this->getFields('package_restrictions', array(), false),
			'notify_rights' => $this->getFields('package_mail', array(), false),
			'extraDescript' => $this->getExtraDescript($service, $this->values['name'], 'apkg', 'pkgdsc2_', '', array($this->getConnectionCp($this->values['server'])))
		);
		if(empty($fields['vars']['notify_rights']['notify_settings_type'])) $fields['vars']['notify_rights']['notify_settings_type'] = 'useMain';

		$this->DB->Upd(array('order_packages', $fields, "`id`='$id'"));
		if($return) $this->refresh('packages&id='.$sid);
		else $this->back('packages&id='.$sid, '{Call:Lang:modules:billing:izmeneniiavn}', '', '');

		return $return;
	}

	protected function func_packagesActions(){
		/*
			Операции над пакетами
		*/

		if($this->values['action'] == 'deleteAll'){
			$entries = array();
			$results = array();
			$ids = array();
			$sData = $this->serviceDataById($this->values['serviceId']);

			foreach($this->DB->columnFetch(array('order_packages', array('server_name', 'server', 'text', 'service', 'id'), 'name', '('.$this->getEntriesWhere(false, 'id').')')) as $i => $e){
				$entries[$e['server']][$i] = $e;
				$ids[$e['server']][$i] = $e['id'];
			}

			foreach($entries as $i => $e){
				if(!$i) continue;
				$results[$i] = $this->callServiceObj('delPkg', $sData['name'], array('server' => $i, 'pkgs' => $e), array('type' => 'order_packages', 'id' => $ids[$i]));

				foreach($results[$i] as $i1 => $e1){
					if(!$e1){
						$this->setError('', '{Call:Lang:modules:billing:neudalosudal:'.Library::serialize(array($e[$i1]['text'], $this->path, $this->mod, $this->getConnectionResultId($i))).'}');
						unset($this->values['entry'][$ids[$i][$i1]]);
					}
					else $this->setContent('{Call:Lang:modules:billing:paketudalenn:'.Library::serialize(array($e[$i1]['text'], $this->path, $this->mod, $this->getConnectionResultId($i))).'}', 'refresh_msg');
				}
			}

			if(!empty($this->values['entry'])){
				$this->values['action'] = 'delete';
				$this->typeActions('order_packages', 'packages&id='.$this->values['serviceId']);
			}
			else $this->back('packages&id='.$this->values['serviceId']);
		}
		else{
			$results = $this->typeActions('order_packages', 'packages&id='.$this->values['serviceId']);
		}

		return $results;
	}


	/********************************************************************************************************************************************************************

																		Группы тарифов

	*********************************************************************************************************************************************************************/

	protected function func_pkgGroups(){
		/*
			Группы тарифных планов
		*/

		$id = db_main::Quot($this->values['id']);
		$data = $this->DB->rowFetch(array('services', array('name', 'text'), "`id`='$id'"));
		$service = $data['name'];

		$this->pathFunc = 'services';
		$this->funcName = '{Call:Lang:modules:billing:gruppytarifo:'.Library::serialize(array($data['text'])).'}';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'package_groups',
						'pkgGroupsAdd&serviceId='.$id,
						array('caption' => '{Call:Lang:modules:billing:dobavitgrupp}')
					),
					'package_groups'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'package_groups_list',
					array(
						'req' => array( 'package_groups', '*', "`service`='$service'", "`sort`" ),
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'text' => 'pkgGroupsData&serviceId='.$id
						),
						'action' => 'pkgGroupsActions&serviceId='.$id,
						'searchForm' => array(
							'searchFields' => array(
								'text' => '{Call:Lang:modules:billing:imia}',
								'name' => '{Call:Lang:modules:billing:identifikato3}',
							),
							'orderFields' => array(
								'text' => '{Call:Lang:modules:billing:imeni}',
								'name' => '{Call:Lang:modules:billing:identifikato12}',
							),
							'searchParams' => array(
								'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$id
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:gruppytarifo:'.Library::serialize(array($data['text'])).'}',
						'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl($this->func.'&id='.$id).'&table=package_groups'
					)
				)
			)
		);
	}

	protected function func_pkgGroupsAdd(){
		/*
			Добавляет группу
		 */

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		if(!$id) $this->values['show'] = 1;
		$sid = db_main::Quot($this->values['serviceId']);

		$service = $this->DB->cellFetch(array('services', 'name', "`id`='$sid'"));
		$this->values['service'] = $service;

		$this->isUniq(
			'package_groups',
			array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'),
			$id,
			"AND `service`='$service'"
		);

		return $this->typeIns(
			'package_groups',
			$this->fieldValues(array('name', 'text', 'service', 'main', 'pkg_table_mode', 'hide_if_none', 'compact_if_alike', 'sort')),
			'pkgGroups&id='.$sid
		);
	}

	protected function func_pkgGroupsActions(){
		/*
			В случае удаления группы все отнесенные к ней тарифы должны перетекать в основную группу
		*/

		$this->typeActions('package_groups', 'pkgGroups&id='.$this->values['serviceId']);
	}

	protected function func_pkgGroupsData(){
		/*
			Управление группой
		 */

		$sid = $this->values['serviceId'];
		$this->pathFunc = 'services';

		$this->typeModify(
			array('package_groups', '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			'package_groups',
			'pkgGroupsAdd&serviceId='.$sid,
			array(
				'formData' => array('extra' => 1),
				'params' => array('caption' => '{Call:Lang:modules:billing:gruppatext}')
			)
		);
	}



	/********************************************************************************************************************************************************************

																	Описание тарифных планов

	*********************************************************************************************************************************************************************/

	protected function func_pkgDescripts(){
		/*
			Управление формой описания тарифов

			Контекст в котором может быть использована составляющая:
				форма создания ТП админом (с указанием для каких групп ТП).
					0 - Не использовать
					1 - Использовать одинаково
					2 - Персональные настройки по группам

				Форма создания нового акка админом (с указанием для каких групп ТП).
					0 - Не использовать
					1 - Использовать одинаково
					2 - Персональные настройки по группам
					3 - Персональные настройки по тарифам

				Форма заказа ТП (с указанием для каких ТП / групп ТП).
					0 - Не использовать
					1 - Использовать одинаково
					2 - Персональные настройки по группам
					3 - Персональные настройки по тарифам

				форма модификации ТП (с указанием для каких ТП / группам),
					0 - Не использовать
					1 - Использовать одинаково
					2 - Персональные настройки по группам
					3 - Персональные настройки по тарифам

				Список ТП (с указанием по группам ТП),
					0 - Не использовать
					1 - Использовать одинаково (Всегда использовать значение из админки)
					2 - Персональные настройки по группам
		*/

		$sid = db_main::Quot(empty($this->values['serviceId']) ? $this->values['id'] : $this->values['serviceId']);
		$sData = $this->serviceDataById($sid);
		$extraValues = $extraBlocks = $extraFields = $matrixData = $cpList = $cp = $insert = array();

		$this->pathFunc = 'services';
		$this->funcName = '{Call:Lang:modules:billing:opisanietari:'.Library::serialize(array($sData['text'])).'}';

		//Создаем список доступных соединений
		if(!empty($sData['extension']) && (empty($this->values['field_action']) || $this->values['field_action'] != 'actions')){
			$eData = $this->Core->getModuleParams($sData['extension']);
			$cp = $this->DB->columnFetch(array('service_extensions_connect', array('name', 'extra'), 'mod', "`service`='{$eData['name']}'"));

			foreach($cp as $i => $e){
				$cp[$i]['extra'] = Library::unserialize($e['extra']);
				$cpList[$i] = $e['name'];
			}

			$matrixData['cp'] = $cp;
		}

		//Готовим доп. поля для случаев когде ане нужны
		if(!empty($this->values['field_action'])){
			if($this->values['field_action'] == 'modify' || $this->values['field_action'] == 'modify2'){
				$blocksList = array('apkg' => '{Call:Lang:modules:billing:sozdanietari}', 'aacc' => '{Call:Lang:modules:billing:sozdanieakka}', 'opkg' => '{Call:Lang:modules:billing:formazakaza}', 'mpkg' => '{Call:Lang:modules:billing:kalkuliatort}', 'pkg_list' => '{Call:Lang:modules:billing:postroeniias}');
				$id = $this->values['field_action'] == 'modify' ? $this->values['id'] : $this->values['modify'];
				$grps = $this->getPkgGroups($sData['name'], "AND `main`='1'");

				$bData = $this->DB->rowFetch(array('package_descripts', array('type', 'vars'), "`id`='$id'"));
				$bData['vars'] = Library::unserialize($bData['vars']);

				if($this->values['field_action'] == 'modify'){
					if(empty($bData['vars']['extra'])){
						$extraValues['apkg'] = $extraValues['pkg_list'] = $extraValues['mpkg'] = '1';
						$extraValues['aacc'] = $extraValues['opkg'] = '0';
					}
					$matrixData['type'] = $bData['type'];

					foreach($blocksList as $i => $e){
						$extraBlocks[] = array('matrix' => 'pkg_dcs_fields', 'name' => $e, 'formData' => array('groups' => $grps, 'bType' => $i, 'sType' => $sData['type']));
					}

					if($bData['type'] == 'select' || $bData['type'] == 'multiselect' || $bData['type'] == 'radio' || $bData['type'] == 'checkbox_array'){
						$extraValues['mpkg_price'] = isset($bData['vars']['extra']['mpkg_price']) ? Library::hash2block($bData['vars']['extra']['mpkg_price']) : '';
						$extraValues['mpkg_price_install'] = isset($bData['vars']['extra']['mpkg_price_install']) ? Library::hash2block($bData['vars']['extra']['mpkg_price_install']) : '';
						$extraValues['mpkg_price_unlimit'] = isset($bData['vars']['extra']['mpkg_price_unlimit']) ? Library::hash2block($bData['vars']['extra']['mpkg_price_unlimit']) : '';

						foreach($grps as $i1 => $e1){
							$extraValues['mpkg_price_'.$i1] = empty($bData['vars']['extra']['mpkg_price_'.$i1]) ? '' : Library::hash2block($bData['vars']['extra']['mpkg_price_'.$i1]);
							$extraValues['mpkg_price_install_'.$i1] = empty($bData['vars']['extra']['mpkg_price_install_'.$i1]) ? '' : Library::hash2block($bData['vars']['extra']['mpkg_price_install_'.$i1]);
							$extraValues['mpkg_price_unlimit_'.$i1] = empty($bData['vars']['extra']['mpkg_price_unlimit_'.$i1]) ? '' : Library::hash2block($bData['vars']['extra']['mpkg_price_unlimit_'.$i1]);
						}
					}
				}
				elseif($this->values['field_action'] == 'add' || $this->values['field_action'] == 'modify2'){
					$extraFields = array('apkg', 'aacc', 'opkg', 'mpkg', 'pkg_list', 'cp', 'use_if_no_conformity', 'use_if_no_panel');

					foreach($cpList as $i => $e) if(!empty($this->values['cp_conformity_'.$i])) $insert['cp'][] = $i;
					$insert['cp'] = !empty($insert['cp']) ? ','.implode(',', $insert['cp']).',' : '';

					if($this->values['field_action'] == 'modify2'){
						if($bData['type'] == 'select' || $bData['type'] == 'multiselect' || $bData['type'] == 'radio' || $bData['type'] == 'checkbox_array'){
							$insert['mpkg_price'] = empty($this->values['mpkg_price']) ? '' : Library::block2hash($this->values['mpkg_price']);
							$insert['mpkg_price_install'] = empty($this->values['mpkg_price_install']) ? '' : Library::block2hash($this->values['mpkg_price_install']);
							$insert['mpkg_price_unlimit'] = empty($this->values['mpkg_price_unlimit']) ? '' : Library::block2hash($this->values['mpkg_price_unlimit']);
							unset($this->values['mpkg_price'], $this->values['mpkg_price_install'], $this->values['mpkg_price_unlimit']);

							foreach($grps as $i1 => $e1){
								$insert['mpkg_price_'.$i1] = empty($this->values['mpkg_price_'.$i1]) ? '' : Library::block2hash($this->values['mpkg_price_'.$i1]);
								$insert['mpkg_price_install_'.$i1] = empty($this->values['mpkg_price_install_'.$i1]) ? '' : Library::block2hash($this->values['mpkg_price_install_'.$i1]);
								$insert['mpkg_price_unlimit_'.$i1] = empty($this->values['mpkg_price_unlimit_'.$i1]) ? '' : Library::block2hash($this->values['mpkg_price_unlimit_'.$i1]);
								unset($this->values['mpkg_price_'.$i1], $this->values['mpkg_price_install_'.$i1], $this->values['mpkg_price_unlimit_'.$i1]);
							}
						}
					}
				}
			}

			if($this->values['field_action'] == 'add' || $this->values['field_action'] == 'modify2'){
				$extraFields[] = 'service';
				$insert['service'] = $sData['name'];
			}
		}

		return $this->formFields(
			'package_descripts',
			array(
				'matrixParams' => array('caption' => '{Call:Lang:modules:billing:dobavitparam:'.Library::serialize(array($sData['text'])).'}'),
				'req' => array('package_descripts', array('id', 'name', 'text', 'type', 'show', 'sort', 'service', 'apkg', 'aacc', 'opkg', 'mpkg', 'pkg_list', 'cp', 'use_if_no_conformity', 'use_if_no_panel'), "`service`='{$sData['name']}'", "`sort`"),
				'listParams' => array(
					'caption' => '{Call:Lang:modules:billing:vseparametry:'.Library::serialize(array($sData['text'])).'}',
					'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl('pkgDescripts&serviceId='.$sid).'&table=package_descripts'
				),
				'listData' => array(
					'req' => array(2 => "`service`='{$sData['name']}'"),
					'form_actions' => array(
						'apkg' => 'Использовать при создании пакета',
						'aacc' => 'Использовать при создании аккаунта админом',
						'opkg' => 'Использовать при заказе',
						'mpkg' => 'Использовать в конструкторе',
						'pkg_list' => 'Использовать в таблице тарифов',
						'use_if_no_conformity' => 'Использовать если не определено соответствие панели',
						'use_if_no_panel' => 'Использовать если не определена панель',
						'not_apkg' => 'Не использовать при создании пакета',
						'not_aacc' => 'Не использовать при создании аккаунта админом',
						'not_opkg' => 'Не использовать при заказе',
						'not_mpkg' => 'Не использовать в конструкторе',
						'not_pkg_list' => 'Не использовать в таблице тарифов',
						'not_use_if_no_conformity' => 'Не использовать если не определено соответствие панели',
						'not_use_if_no_panel' => 'Не использовать если не определена панель'
					),
					'searchForm' => array(
						'searchFields' => array(
							'cp' => '{Call:Lang:modules:billing:panelupravle}',
							'apkg' => '{Call:Lang:modules:billing:ispolzuetsia}',
							'aacc' => '{Call:Lang:modules:billing:ispolzuetsia1}',
							'opkg' => '{Call:Lang:modules:billing:ispolzuetsia2}',
							'mpkg' => '{Call:Lang:modules:billing:ispolzuetsia3}',
							'pkg_list' => '{Call:Lang:modules:billing:ispolzuetsia4}',
							'use_if_no_conformity' => '{Call:Lang:modules:billing:ispolzuetsia5}',
							'use_if_no_panel' => '{Call:Lang:modules:billing:ispolzuetsia6}',
						),
						'searchMatrix' => array(
							'cp' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), Library::concatPrefixArrayKey($cpList, ',', ','))
							),
							'apkg' => array('type' => 'checkbox'),
							'aacc' => array('type' => 'checkbox'),
							'opkg' => array('type' => 'checkbox'),
							'mpkg' => array('type' => 'checkbox'),
							'pkg_list' => array('type' => 'checkbox'),
							'use_if_no_conformity' => array('type' => 'checkbox'),
							'use_if_no_panel' => array('type' => 'checkbox'),
						),
						'searchParams' => array(
							'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$sid
						)
					),
				),
				'actionFields' => array(
					'apkg' => array('apkg' => 1),
					'aacc' => array('aacc' => 1),
					'opkg' => array('opkg' => 1),
					'mpkg' => array('mpkg' => 1),
					'pkg_list' => array('pkg_list' => 1),
					'use_if_no_conformity' => array('use_if_no_conformity' => 1),
					'use_if_no_panel' => array('use_if_no_panel' => 1),
					'not_apkg' => array('apkg' => 0),
					'not_aacc' => array('aacc' => 0),
					'not_opkg' => array('opkg' => 0),
					'not_mpkg' => array('mpkg' => 0),
					'not_pkg_list' => array('pkg_list' => 0),
					'not_use_if_no_conformity' => array('use_if_no_conformity' => 0),
					'not_use_if_no_panel' => array('use_if_no_panel' => 0),
				),
				'matrixExtra' => 'pkg_dsc_fields_cp',
				'matrixData' => $matrixData,
				'extraValues' => $extraValues,
				'func' => 'pkgDescripts&serviceId='.$sid,
				'formName' => 'pkgDescripts',
				'extraBlocks' => $extraBlocks,
				'extraFields' => $extraFields,
				'insert' => $insert,
				'filter' => " AND `service`='{$sData['name']}'",
				'listFileTmpl' => $this->Core->getModuleTemplatePath($this->mod).'list.tmpl',
				'listEntryTmpl' => 'pkg_descript_list',
				'formTemplate' => 'big',
				'listTmpl' => 'big',
			)
		);
	}

	private function getPkgGroups($service, $extra = '', $emptyGrp = true){
		$return = $this->DB->columnFetch(array('package_groups', 'text', 'name', "`service`='".db_main::Quot($service)."' {$extra}", "`sort`"));
		if($emptyGrp) $return = Library::array_merge( array('' => '{Call:Lang:modules:billing:bezgruppy}'), $return );
		return $return;
	}

	private function getPkgs($service){
		return $this->DB->columnFetch(array('order_packages', 'text', 'name', "`service`='".db_main::Quot($service)."'"));
	}



	/********************************************************************************************************************************************************************

																				Валюты

	*********************************************************************************************************************************************************************/

	protected function func_currency(){
		/*
			Управление валютами
			При удалении валюты проверяется что она не присутствует в числе основных и что ее не использовали:
				1. При расчете цен на пакеты
				2. При оплатах
		 */

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'currency',
						'currencyNew',
						array(
							'caption' => '{Call:Lang:modules:billing:dobavitvaliu}'
						)
					),
					'currency',
					array( 'currency' => $this->getMainCurrencyName() )
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'currency_list',
					array(
						'req' => array( 'currency', '*', '', "`sort`" ),
						'actions' => array(
							'text' => 'currencyData',
							'default' => 'currencyDefault',
							'del' => 'currencyDel'
						),
						'searchForm' => array(
							'searchFields' => array(
								'text' => '{Call:Lang:modules:billing:nazvanie}',
								'name' => '{Call:Lang:modules:billing:identifikato3}',
								'exchange' => '{Call:Lang:modules:billing:kursk:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							),
							'orderFields' => array(
								'text' => '{Call:Lang:modules:billing:nazvaniiu}',
								'name' => '{Call:Lang:modules:billing:identifikato12}',
								'exchange' => '{Call:Lang:modules:billing:kursuk:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							),
							'searchMatrix' => array(
								'exchange' => array('type' => 'gap')
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny2}'
					)
				)
			)
		);
	}

	protected function func_currencyNew(){
		/*
			Добавляет валюту
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->values['name'] = strtolower($this->values['name']);
		$this->isUniq( 'currency', array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'), $id);
		return $this->typeIns('currency', 'currency', 'currency');
	}

	protected function func_currencyData(){
		$this->typeModify(
			array('currency', '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			'currency',
			'currencyNew',
			array(
				'caption' => '{Call:Lang:modules:billing:parametryval}',
				'formData' => array('currency' => $this->getMainCurrencyName(), 'extra' => '1')
			)
		);
	}

	protected function func_currencyDel(){
		/*
			Удаляет специфицированную валюту
		 */

		$id = db_main::Quot($this->values['id']);
		$data = $this->DB->rowFetch(array('currency', array('name', 'default'), "`id`='$id'"));

		if($data['default']){
			$this->back('currency', '{Call:Lang:modules:billing:vynemozheteu}');
			return false;
		}

		if($this->DB->cellFetch(array('order_packages', 'currency', "`currency`='{$data['name']}'"))){
			$this->back('currency', '{Call:Lang:modules:billing:vynemozheteu1}');
			return false;
		}

		if($this->DB->cellFetch(array('orders', 'currency', "`currency`='{$data['name']}'"))){
			$this->back('currency', '{Call:Lang:modules:billing:vynemozheteu2}');
			return false;
		}

		if($this->DB->cellFetch(array('payments', 'currency', "`currency`='{$data['name']}'"))){
			$this->back('currency', '{Call:Lang:modules:billing:vynemozheteu3}');
			return false;
		}

		if($this->DB->Del(array('currency', "`id`='$id'"))){
			$this->refresh('currency');
			return true;
		}
		else{
			$this->back('currency');
			return false;
		}
	}

	protected function func_currencyDefault(){
		/*
			Установка валюты по умолчанию
			Одна из валют в списке может быть выбрана как основная. Она может быть свободно переключена, при этом:
				1. Пересчитываются все текущие курсы валют в таблице валют
				2. Пересчитываются балансы пользователей (всегда хранятся в осн. валюте)
				3. Пересчитываются сведения о поступлении платежей
				4. Пересчитываются сведения необходимые для расчета бонусов и уровней доверия клиентам
				5. Цены на пакеты могут быть указаны для каждого тарифа - в своей валюте. Также может быть указано "По умолчанию",
					в этом случае цена пересчитывается при смене основной валюты в соответствии с текущим курсом соотношения старой и новой валют,
					то же самое для сведений о заказанных услугах, и о составляющих тарифов.
					В сериализованный логах (история например, кроме цен на составляющие тарифа) при этом всегда указывается используемая валюта (даже если она по умолчанию).
					Цены на составляющие тарифа пересчитываются
					Для тех услуг где используется валюта не по умолчанию - цены не пересчитываются
					Цены длжны быть пересчитаны если для услуги меняется валюта
				6. Все расчеты в счете до прихода оплаты хранятся в текущей валюте по умолчанию а при поступлении оплаты пересчитываются в валюту оплаты по курсу.
					При этом всегда указывается валюта в которой хранятся цены
				7. Все сведения которые хранятся не только в валюте по умолчанию должны иметь указанную валюту в которой шла оплата
		 */

		$id = db_main::Quot($this->values['id']);

		if($defaultCurData = $this->currencyParams($this->defaultCurrency())){
			if($defaultCurData['id'] == $this->values['id']){
				$this->refresh('currency');
				return false;
			}
		}

		$curData = $this->DB->rowFetch(array('currency', '*', "`id`='$id'"));
		$this->DB->trStart();

		//Балансы
		$this->DB->Upd(array(
			'clients',
			array(
				'balance' => '`balance` * '.$curData['exchange'],
				'all_payments' => '`all_payments` * '.$curData['exchange'],
				'all_payed_services' => '`all_payed_services` * '.$curData['exchange'],
				'#isExp' => array('balance' => true, 'all_payments' => true, 'all_payed_services' => true)
			)
		));

		//Сведения о движении денежных средств
		$this->DB->Upd(array('pays', array('sum' => '`sum` * '.$curData['exchange'], '#isExp' => array('sum' => true))));

		//Бонусы
		$this->DB->Upd(array('bonuses', array('min_sum' => 'min_sum * '.$curData['exchange'], '#isExp' => array('min_sum' => true))));
		$this->DB->Upd(array('bonuses', array('bonus' => 'bonus * '.$curData['exchange'], '#isExp' => array('bonus' => true)), "`bonus_type`='money'"));

		//Уровни доверия
		$this->DB->Upd(array(
			'loyalty_levels',
			array(
				'add_with_all_payments' => '`add_with_all_payments` * '.$curData['exchange'],
				'add_with_all_payed_services' => '`add_with_all_payed_services` * '.$curData['exchange'],
				'#isExp' => array('add_with_all_payments' => true, 'add_with_all_payed_services' => true)
			)
		));


		//Потарифно
		foreach($this->DB->columnFetch(array('order_packages', array('vars', 'name', 'service', 'server'), 'id', "`currency`=''")) as $i => $e){
			$e['vars'] = Library::unserialize($e['vars']);

			foreach($this->getPkgDescriptForm($e['service'], $e['name'], 'mpkg', '', '', $v, array($this->getConnectionCp($e['server']))) as $i1 => $e1){
				if(isset($e['vars']['extraDescript']['mpkg_price_'.$i1]) || isset($e['vars']['extraDescript']['mpkg_price_install_'.$i1])){
					$e['vars']['extraDescript']['mpkg_price_'.$i1] = $this->upCDPrice($e1['price'], $curData['exchange']);
					$e['vars']['extraDescript']['mpkg_price_install_'.$i1] = $this->upCDPrice($e1['price_install'], $curData['exchange']);
					$e['vars']['extraDescript']['mpkg_price_unlimit_'.$i1] = $this->upCDPrice($e1['price_unlimit'], $curData['exchange']);
					$e['vars']['extraDescript']['mpkg_price_install_unlimit_'.$i1] = $this->upCDPrice($e1['price_install_unlimit'], $curData['exchange']);
				}

			}

			//Обновляем сведения для пакетов
			$this->DB->Upd(array(
				'order_packages',
				array(
					'price' => '`price` * '.$curData['exchange'],
					'price2' => '`price2` * '.$curData['exchange'],
					'prolong_price' => '`prolong_price` * '.$curData['exchange'],
					'install_price' => '`install_price` * '.$curData['exchange'],
					'change_down_price' => '`change_down_price` * '.$curData['exchange'],
					'change_up_price' => '`change_up_price` * '.$curData['exchange'],
					'change_srv_price' => '`change_srv_price` * '.$curData['exchange'],
					'change_grp_price' => '`change_grp_price` * '.$curData['exchange'],
					'change_modify_price' => '`change_modify_price` * '.$curData['exchange'],
					'del_price' => '`del_price` * '.$curData['exchange'],
					'pause_start_price' => '`pause_start_price` * '.$curData['exchange'],
					'pause_stop_price' => '`pause_stop_price` * '.$curData['exchange'],
					'vars' => Library::serialize($e['vars']),
					'#isExp' => array(
						'pause_stop_price' => true,
						'pause_start_price' => true,
						'del_price' => true,
						'change_modify_price' => true,
						'change_grp_price' => true,
						'change_srv_price' => true,
						'change_up_price' => true,
						'change_down_price' => true,
						'install_price' => true,
						'prolong_price' => true,
						'price' => true,
						'price2' => true,
					)
				),
				"`id`='$i'"
			));

			$this->DB->Upd(array(
				'order_services',
				array(
					'price' => '`price` * '.$curData['exchange'],
					'modify_price' => '`modify_price` * '.$curData['exchange'],
					'all_payments' => '`all_payments` * '.$curData['exchange'],
					'#isExp' => array('price' => true, 'modify_price' => true, 'all_payments' => true)
				),
				"`id`='{$e['name']}'"
			));
		}


		//Описания тарифов
		foreach($this->DB->columnFetch(array('services', 'name')) as $i => $e){
			$grps = Library::array_merge(array('' => ''), $this->DB->columnFetch(array('package_groups', 'name', 'id', "`service`='$e'")));

			foreach($this->DB->columnFetch(array('package_descripts', array('vars', 'mpkg'), 'id')) as $i1 => $e1){
				if(!$e1['mpkg']) continue;
				$e1['vars'] = Library::unserialize($e1['vars']);

				if(isset($e1['vars']['extra']['mpkg_price'])) $e1['vars']['extra']['mpkg_price'] = $this->upCDPrice($e1['vars']['extra']['mpkg_price'], $curData['exchange']);
				if(isset($e1['vars']['extra']['mpkg_price_install'])) $e1['vars']['extra']['mpkg_price_install'] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_install'], $curData['exchange']);
				if(isset($e1['vars']['extra']['mpkg_price_unlimit'])) $e1['vars']['extra']['mpkg_price_unlimit'] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_unlimit'], $curData['exchange']);
				if(isset($e1['vars']['extra']['mpkg_price_install_unlimit'])) $e1['vars']['extra']['mpkg_price_install_unlimit'] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_install_unlimit'], $curData['exchange']);

				foreach($grps as $i2 => $e2){
					if(isset($e1['vars']['extra']['mpkg_price_'.$e2])) $e1['vars']['extra']['mpkg_price_'.$e2] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_'.$e2], $curData['exchange']);
					if(isset($e1['vars']['extra']['mpkg_price_install_'.$e2])) $e1['vars']['extra']['mpkg_price_install_'.$e2] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_install_'.$e2], $curData['exchange']);
					if(isset($e1['vars']['extra']['mpkg_price_unlimit_'.$e2])) $e1['vars']['extra']['mpkg_price_unlimit_'.$e2] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_unlimit_'.$e2], $curData['exchange']);
					if(isset($e1['vars']['extra']['mpkg_price_install_unlimit_'.$e2])) $e1['vars']['extra']['mpkg_price_install_unlimit_'.$e2] = $this->upCDPrice($e1['vars']['extra']['mpkg_price_install_unlimit_'.$e2], $curData['exchange']);
				}

				$this->DB->Upd(array('package_descripts', array('vars' => $e1['vars']), "`id`='$i1'"));
			}
		}

		$this->DB->Upd(array('currency', array('default' => '', 'exchange' => 'exchange / '.$curData['exchange'], '#isExp' => array('exchange' => true))));
		$this->DB->Upd(array('currency', array('default' => '1'), "`id`='$id'"));

		$this->DB->trEnd(true);
		$this->refresh('currency');
		return true;
	}

	private function upCDPrice($price, $cur){
		if(is_array($price)){
			foreach($price as $i => $e){
				$price[$i] = $e * $cur;
			}
		}
		elseif($price) $price = $price * $cur;

		return $price;
	}



	/********************************************************************************************************************************************************************

																		Cпособы оплаты

	*********************************************************************************************************************************************************************/

	protected function func_payments(){
		/*
			Управление способами оплаты

			Способы оплаты также как и услуги могут использовать расширения для приема платежей.
			На один способ оплаты используется 1 валюта. Если расширение умеет использовать несколько валют (например как в въебмани), должна быть предусмотрена
			возможность указать соответствие внутренней валюты и в ПС
		 */

		$curList = $this->getCurrency();
		$extList = $this->getPayExtensions();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'payments',
						'paymentsNew',
						array(
							'caption' => '{Call:Lang:modules:billing:dobavitsposo}'
						)
					),
					'payments',
					array(
						'currencyList' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vsegdavaliut}'), $curList),
						'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzuets}'), $extList)
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'payments_list',
					array(
						'req' => array('payments', '*', '', "`sort`"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:billing:skryt}',
							'unsuspend' => '{Call:Lang:modules:billing:otkryt}',
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'text' => 'paymentsData'
						),
						'action' => 'paymentsActions',
						'searchForm' => array(
							'searchFields' => array(
								'text' => '{Call:Lang:modules:billing:nazvanie}',
								'name' => '{Call:Lang:modules:billing:identifikato3}',
								'currency' => '{Call:Lang:modules:billing:valiuta}',
								'extension' => '{Call:Lang:modules:billing:rasshirenie}'
							),
							'orderFields' => array(
								'text' => '{Call:Lang:modules:billing:nazvaniiu}',
								'name' => '{Call:Lang:modules:billing:identifikato12}'
							),
							'searchMatrix' => array(
								'currency' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $curList)
								),
								'extension' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $extList)
								)
							),
							'isBe' => array('currency' => 1, 'extension' => 1)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny3}'
					)
				)
			)
		);
	}

	protected function func_paymentsNew(){
		$id = empty($this->values['modify']) ? 0 : $this->values['modify'];
		$this->isUniq( 'payments', array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'), $id);

		if($this->values['extension'] && $id){
			if(($this->values['vars'] = Library::serialize($this->callPaymentExtension($this->values['extension'], 'checkNewPaymentForm', $this->values['name']))) === false) return false;
		}

		$exch = false;
		if($id && ($this->values['currency'] != $this->currencyNameByPayment($this->values['name']))){
			$exch = $this->convertCurrency(1, $this->currencyNameByPayment($this->values['name']), $this->values['currency']);
		}

		if(($newId = $this->typeIns('payments', $this->fieldValues(array('name', 'text', 'extension', 'currency', 'show', 'sort', 'vars', 'comment')), 'payments')) && !$id){
			$this->redirect('paymentsData&id='.$newId);
		}
		elseif($newId && $exch){
			foreach($this->getComplexParams() as $i => $e){
				$e['vars']['pays'][$this->values['name']] = round($e['vars']['pays'][$this->values['name']] * $exch, 2);
				$this->DB->Upd(array('complex', $e, "`name`='{$i}'"));
			}
		}
	}

	protected function func_paymentsData(){
		$values = $this->DB->rowFetch(array('payments', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$form = $this->newForm(
			'payments',
			'paymentsNew',
			array(
				'caption' => '{Call:Lang:modules:billing:parametryopl:'.Library::serialize(array($values['text'])).'}'
			)
		);

		$this->addFormBlock(
			$form,
			'payments',
			array(
				'currencyList' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vsegdavaliut}'), $this->getCurrency()),
				'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzuets}'), $this->getPayExtensions()),
				'extra' => '1'
			)
		);

		if($values['extension']) $this->callPaymentExtension($values['extension'], 'setNewPaymentForm', $values['name'], array('fObj' => $form, 'values' => $values));
		$this->setContent($this->getFormText($form, $values, array('modify' => $this->values['id']), 'big'));
	}

	protected function func_paymentsActions(){
		return $this->typeActions('payments', 'payments');
	}


	/********************************************************************************************************************************************************************

																		Расширения способов оплаты

	*********************************************************************************************************************************************************************/

	protected function func_payExtensions(){
		/*
			Расширения способов оплаты
		*/

		$exList = Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getPayExtensions());
		$billMods = $this->Core->getModulesByType('billing');
		unset($billMods[$this->mod]);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'payExtensionsAdd',
						'payExtensionsAdd',
						array(
							'caption' => '{Call:Lang:modules:billing:ustanovitras}'
						)
					),
					'pay_extension',
					array('billMods' => $billMods)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'pay_extensions_list',
					array(
						'req' => array('payment_extensions', '*', "", "`sort`"),
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'name' => 'payExtensionData',
							'update' => 'upPayExtensionData',
						),
						'action' => 'payExtensionActions',
						'searchForm' => array(
							'searchFields' => array(
								'name' => '{Call:Lang:modules:billing:imiarasshire}',
								'module' => '{Call:Lang:modules:billing:identifikato11}'
							),
							'orderFields' => array(
								'name' => '{Call:Lang:modules:billing:imeni}',
								'mod' => '{Call:Lang:modules:billing:identifikato12}'
							),
							'searchAlias' => array('module' => 'mod')
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny4}'
					)
				)
			)
		);
	}

	protected function func_payExtensionData(){
		/*
			Параметры расширения соединения
		*/

		return $this->typeModify(
			array('payment_extensions', '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			'pay_extension',
			'payExtensionDataSet',
			array(
				'formData' => array('modify' => 1),
				'params' => array('caption' => '{Call:Lang:modules:billing:rasshirenien}')
			)
		);
	}

	protected function func_payExtensionDataSet(){
		$this->isUniq('payment_extensions', array('name' => '{Call:Lang:modules:billing:takoenazvani}'), $this->values['modify']);
		return $this->typeIns('payment_extensions', array('name' => $this->values['name']), 'payExtensions');
	}

	protected function func_payExtensionActions(){
		return $this->typeActions('payment_extensions', 'payExtensions');
	}

	protected function func_payExtensionsAdd(){
		/*
			Устанавливаем новое расширение
		*/

		if(!$this->check()) return false;

		list($tmpFolder, $exMod, $files, $params) = $this->loadPayExtensionParams();
		foreach($files as $e) if(file_exists(_W.'modules/billing/extensions/payments/'.$e)) $this->setError('extension', '{Call:Lang:modules:billing:uzhesuzhestv:'.Library::serialize(array($e)).'}');
		$this->isUniq('payment_extensions', array('mod' => '{Call:Lang:modules:billing:takojmoduluz}', 'name' => '{Call:Lang:modules:billing:takoeimiauzh}'), false, '', array('mod' => $exMod, 'name' => $this->values['name']));

		if(!$this->check()) return false;
		$this->refresh('payExtensions');
		return $this->installPayExtension(isset($this->values['bill_mods']) ? $this->values['bill_mods'] : array(), $tmpFolder, $exMod, $params);
	}

	protected function func_upPayExtensionData(){
		/*
			Обновление расширения
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'payExtensionsUpdate',
						'payExtensionsUpdate',
						array(
							'caption' => '{Call:Lang:modules:billing:obnovitrassh}'
						)
					),
					'connection_extension_file'
				),
				array(),
				array('id' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_payExtensionsUpdate(){
		/*
			Устанавливаем новое расширение
		*/

		if(!$this->check()) return false;
		list($tmpFolder, $exMod, $files, $params) = $this->loadPayExtensionParams();
		if($exMod && $exMod != regExp::lower($this->DB->cellFetch(array('payment_extensions', 'mod', "`id`='{$this->values['id']}'")))) $this->setError('extension', '{Call:Lang:modules:billing:nesovpadaiut}');
		if(!$this->check()) return false;

		$billMods = array();
		foreach($this->Core->getModulesByType('billing') as $i => $e){
			if($this->Core->callModule($i)->DB->cellFetch(array('payment_extensions', 'id', "`mod`='$exMod'"))) $billMods[$i] = 1;
		}

		$return = $this->installPayExtension($billMods, $tmpFolder, $exMod, $params, 'Upd');
		$this->refresh('payExtensions');
		return $return;
	}

	private function loadPayExtensionParams(){
		/*
			Считывает параметры расширения
		*/

		$files = $params = array();

		if(!$tmpFolder = $this->Core->extract2tmpArc(TMP.$this->values['extension'])) $this->setError('extension', '{Call:Lang:modules:billing:neudalosrasp}');
		else{
			$files = Files::readFolder($tmpFolder);
			foreach($files as $e) if(regExp::Match("/^pay(\w+)\.php$/", $e, true, true, $m)) break;
			if(empty($m[1])) $this->setError('extension', '{Call:Lang:modules:billing:nenajdenfajl}');
			else $params = $this->readPayExtensionParams($tmpFolder, $m['1']);
		}

		return array($tmpFolder, isset($m[1]) ? $m[1] : '', $files, $params);
	}

	private function readPayExtensionParams($folder, $mod){
		/*
			Считывает параметры расширения
		*/

		require_once($folder.'pay'.$mod.'.php');
		$params = call_user_func(array('pay'.$mod, 'getInstallParams'));
		return $params;
	}

	private function installPayExtension($billMods, $tmpFolder, $exMod, $params, $instType = 'Ins'){
		/*
			Ставит расширение для соединения
		*/

		$this->Core->setFlag('tmplLock');
		$this->Core->ftpCopy($tmpFolder, _W.'modules/billing/extensions/payments/');
		require_once(_W.'modules/billing/install.php');
		$billMods[$this->mod] = 1;

		foreach($billMods as $i => $e){
			$this->values['united_billing'] = $i;
			$iObj = new installModulesBilling($this->Core->DB, $this, $i);
			$iObj->setType = $instType;
			$iObj->setDefaultPaymentExtensions(array($exMod => $params[1]));
		}

		$this->Core->rmFlag('tmplLock');
		$this->Core->rmFlag('refreshed');
		$this->Core->rmHeader('Location');
	}


	/********************************************************************************************************************************************************************

																		Обслуживание SMS

	*********************************************************************************************************************************************************************/

	protected function func_sms_extensions(){
		/*
			Расширения для SMS
		*/

		return $this->installExtensions('sms_extensions', 'sms', array('path' => _W.'modules/billing/extensions/sms/'));
	}

	protected function func_sms(){
		/*
			Управление способами оплаты

			Способы оплаты также как и услуги могут использовать расширения для приема платежей.
			На один способ оплаты используется 1 валюта. Если расширение умеет использовать несколько валют (например как в въебмани), должна быть предусмотрена
			возможность указать соответствие внутренней валюты и в ПС
		 */

		$extList = $this->getSmsExtensions();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'sms',
						'smsNew',
						array(
							'caption' => '{Call:Lang:modules:billing:dobavitsposo}'
						)
					),
					'sms',
					array(
						'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzuets}'), $extList)
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'sms_list',
					array(
						'req' => array('sms', '*', '', "`sort`"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:billing:skryt}',
							'unsuspend' => '{Call:Lang:modules:billing:otkryt}',
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'text' => 'smsData',
							'smsNumbers' => 'smsNumbers'
						),
						'action' => 'smsActions',
						'searchForm' => array(
							'searchFields' => array(
								'text' => '{Call:Lang:modules:billing:nazvanie}',
								'name' => '{Call:Lang:modules:billing:identifikato3}',
								'extension' => '{Call:Lang:modules:billing:rasshirenie}'
							),
							'orderFields' => array(
								'text' => '{Call:Lang:modules:billing:nazvaniiu}',
								'name' => '{Call:Lang:modules:billing:identifikato12}'
							),
							'searchMatrix' => array(
								'extension' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $extList)
								)
							),
							'isBe' => array('extension' => 1)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny3}'
					)
				)
			)
		);
	}

	protected function func_smsNew(){
		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->isUniq('sms', array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoenazvani}'), $id);

		if($this->values['extension'] && $id){
			if(($this->values['vars'] = $this->callSmsExtension($this->values['extension'], 'checkNewSmsForm', $this->values['name'])) === false) return false;
		}

		if(($newId = $this->typeIns('sms', $this->fieldValues(array('name', 'text', 'extension', 'show', 'sort', 'vars')), 'sms')) && !$id){
			$this->redirect('smsData&id='.$newId);
		}
	}

	protected function func_smsData(){
		$values = $this->DB->rowFetch(array('sms', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$form = $this->newForm(
			'sms',
			'smsNew',
			array(
				'caption' => '{Call:Lang:modules:billing:parametryopl:'.Library::serialize(array($values['text'])).'}'
			)
		);

		$this->addFormBlock(
			$form,
			'sms',
			array(
				'currencyList' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vsegdavaliut}'), $this->getCurrency()),
				'extensions' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzuets}'), $this->getSmsExtensions()),
				'extra' => '1'
			)
		);

		if($values['extension']) $this->callSmsExtension($values['extension'], 'setNewSmsForm', $values['name'], array('fObj' => $form, 'values' => $values));
		$this->setContent($this->getFormText($form, $values, array('modify' => $this->values['id']), 'big'));
	}

	protected function func_smsActions(){
		return $this->typeActions('sms', 'sms');
	}


	/********************************************************************************************************************************************************************

																			Параметры SMS

	*********************************************************************************************************************************************************************/

	protected function func_smsNumbers(){
		/*
			Номера SMS
		*/

		$this->pathFunc = 'sms';
		$this->funcName = 'Добавить короткий номер';

		$id = isset($this->values['smsId']) ? $this->values['smsId'] : $this->values['id'];
		$data = $this->smsParamsById($id);

		$form = $this->addFormBlock($this->newForm('smsNumbers', 'smsNewNumber&smsId='.$id, array('caption' => 'Добавить номер смс')), 'sms_numbers', array('currency' => $this->getCurrency()));
		if($data['extension']) $this->callSmsExtension($data['extension'], 'setNewNumberForm', $data['name'], array('fObj' => $form, 'values' => $data));
		$this->setContent($this->getFormText($form));

		$this->setContent(
			$this->getListText(
				$this->newList(
					'sms_numbers_list',
					array(
						'req' => array('sms_numbers', '*', "`sms`='{$data['name']}'", "`sort`"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:billing:skryt}',
							'unsuspend' => '{Call:Lang:modules:billing:otkryt}',
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'number' => 'smsNumberData&smsId='.$id
						),
						'action' => 'smsNumberActions&smsId='.$id,
						'searchForm' => array(
							'searchFields' => array(
								'number' => 'Номер',
								'sum' => 'Зачисляемая сумма',
								'comment' => 'Комментарий',
								'show' => ''
							),
							'orderFields' => array(
								'number' => 'номеру',
								'sum' => 'сумме'
							),
							'searchMatrix' => array(
								'sum' => array('type' => 'gap')
							)
						)
					),
					array(
						'caption' => 'Номера СМС для "'.$data['text'].'"'
					)
				)
			)
		);
	}

	protected function func_smsNewNumber(){
		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$data = $this->smsParamsById($this->values['smsId']);
		$this->isUniq('sms_numbers', array('number' => 'Такой номер уже есть'), $id, " AND `sms`='{$data['name']}'");

		$fields = $this->fieldValues(array('number', 'sum', 'currency', 'comment', 'show', 'sort'));
		$fields['sms'] = $data['name'];
		if($data['extension']) if(($fields['vars'] = $this->callSmsExtension($data['extension'], 'checkNewNumberForm', $data['name'])) === false) return false;

		return $this->typeIns('sms_numbers', $fields, 'smsNumbers&id='.$this->values['smsId']);
	}

	protected function func_smsNumberData(){
		$values = $this->DB->rowFetch(array('sms_numbers', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values = Library::array_merge($values, Library::unserialize($values['vars']));
		$data = $this->smsParamsById($this->values['smsId']);

		$form = $this->addFormBlock($this->newForm('smsNumbers', 'smsNewNumber&smsId='.$this->values['smsId'], array('caption' => 'Изменить номер "'.$values['number'].'"')), 'sms_numbers', array('currency' => $this->getCurrency()));
		if($data['extension']) $this->callSmsExtension($data['extension'], 'setNewNumberForm', $data['name'], array('fObj' => $form, 'values' => $data));
		$this->setContent($this->getFormText($form, $values, array('modify' => $this->values['id']), 'big'));

		$this->pathFunc = 'sms';
		$this->funcName = 'Изменить номер "'.$values['number'].'"';
	}

	protected function func_smsNumberActions(){
		return $this->typeActions('sms_numbers', 'smsNumbers&id='.$this->values['smsId']);
	}


	/********************************************************************************************************************************************************************

																				Клиенты

	*********************************************************************************************************************************************************************/

	protected function func_clients(){
		/*
			Списак клеентоф
		 */

		$userGroups = $this->Core->getUserGroups();
		$loyalLevels = $this->getLoyaltyLevels();

		$this->setContent(
			$this->getListText(
				$this->newList(
					'clients',
					array(
						'req' => array('clients', '*'),
						'extraReqs' => array(
							array(
								'req' => array('users', array('group', 'date', 'login', 'code', 'name', 'eml', 'utc', 'comment', 'show')),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'user_id',
								'unitedFld2' => 'id',
								'prefix' => 'user_',
								'search' => array('login' => true, 'name' => true, 'eml' => true, 'code' => true, 'group' => true, 'comment' => true)
							)
						),
						'form_actions' => array(
							'ban' => '{Call:Lang:modules:billing:zablokirovat}',
							'unsuspend' => '{Call:Lang:modules:billing:razblokirova2}',
							'unclient' => '{Call:Lang:modules:billing:ubratizchisl}',
							'delete' => '{Call:Lang:modules:billing:udalit}',
						),
						'actions' => array(
							'data' => 'clientsData',
							'services' => 'servicesByClient',
							'orders' => 'billsByClient',
							'pays' => 'paysByClient',
						),
						'searchForm' => array(
							'searchFields' => array(
								'user_login' => '{Call:Lang:modules:billing:login}',
								'user_name' => '{Call:Lang:modules:billing:imiapolzovat}',
								'user_eml' => 'E-mail',
								'user_code' => '{Call:Lang:modules:billing:kodvosstanov}',
								'date' => '{Call:Lang:modules:billing:dataregistat}',
								'user_group' => '{Call:Lang:modules:billing:gruppa}',
								'loyal_level' => '{Call:Lang:modules:billing:urovenklient}',
								'user_comment' => '{Call:Lang:modules:billing:kommentarij}',
								'balance' => '{Call:Lang:modules:billing:balans1:'.Library::serialize(array($this->getMainCurrencyName())).'}',
								'all_payments' => '{Call:Lang:modules:billing:vsegoplatezh1:'.Library::serialize(array($this->getMainCurrencyName())).'}',
								'all_payed_services' => '{Call:Lang:modules:billing:vsegooplache:'.Library::serialize(array($this->getMainCurrencyName())).'}',
							),
							'searchMatrix' => array(
								'balance' => array('type' => 'gap'),
								'all_payments' => array('type' => 'gap'),
								'all_payed_services' => array('type' => 'gap'),
								'user_group' => array('type' => 'select', 'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $userGroups)),
								'loyal_level' => array('type' => 'select', 'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $loyalLevels)),
							),
							'isBe' => array('user_group' => 1, 'loyal_level' => 1),
							'orderFields' => array(
								'date' => '{Call:Lang:modules:billing:dateregistat}',
								'balance' => '{Call:Lang:modules:billing:balansu}',
								'all_payments' => '{Call:Lang:modules:billing:vsegoplatezh2}',
								'all_payed_services' => '{Call:Lang:modules:billing:vsegooplache1}',
							)
						),
						'action' => 'clientsActions'
					),
					array(
						'caption' => '{Call:Lang:modules:billing:spisokklient}'
					)
				),
				'big'
			)
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'addClient',
						'addClient',
						array('caption' => '{Call:Lang:modules:billing:dobavitklien}')
					),
					'add_client',
					array(
						'groups' => Library::array_merge(array('' => '{Call:Lang:modules:billing:nenaznachat}'), $userGroups),
						'clientLevels' => Library::array_merge(array('' => '{Call:Lang:modules:billing:nenaznachat}'), $loyalLevels),
					)
				),
				array(),
				array(),
				'big'
			)
		);
	}

	protected function func_addClient(){
		/*
			Добавляет клиента
		*/

		if($this->Core->DB->cellFetch(array('users', 'id', "`login`='{$this->values['login']}'"))){
			$this->setError('login', '{Call:Lang:modules:billing:takojloginuz}');
		}
		if(!$this->check()) return false;

		$userId = $this->Core->callModule('main')->addUser(Library::array_merge(array('show' => 1), $this->fieldValues(array('login', 'eml', 'pwd', 'utc', 'comment', 'name', 'date', 'group'))));
		if(!$userId){
			$this->back('clients', '{Call:Lang:modules:billing:oshibkaneuda}');
			return false;
		}

		$this->refresh('clients');
		return $this->addClient($userId, $this->fieldValues(array('loyal_level', 'date')));
	}

	protected function func_clientsActions(){
		if(empty($this->values['entry'])){
			$this->back('clients', '{Call:Lang:modules:billing:neotmechenon}');
			return false;
		}

		$where = $this->getEntriesWhere();
		$show = 1;

		switch($this->values['action']){
			case 'ban': $show = -1;
			case 'unsuspend':
				$return = $this->Core->DB->Upd(array('users', array('show' => $show), $this->getEntriesWhere($this->DB->columnFetch(array('clients', 'user_id', 'user_id', $where)))));
				break;

			case 'delete':
				$uList = $this->DB->columnFetch(array('clients', 'user_id', 'user_id', $where));
				$this->Core->DB->Del(array('users', $this->getEntriesWhere($uList)));
				foreach($uList as $i => $e) $this->Core->reauthUserSession($i);

			case 'unclient':
				$return = $this->DB->Del(array('clients', $where));
				break;
		}

		$this->refresh('clients');
		return $return;
	}

	protected function func_clientsData(){
		/*
			Данные клиента
		*/

		$mObj = $this->Core->callModule('main');
		$clientData = $this->DB->rowFetch(array('clients', array('user_id', 'loyal_level', 'date'), "`id`='{$this->values['id']}'"));
		$values = Library::array_merge($this->Core->getUserParamsById($clientData['user_id']), $clientData);

		$types = $this->Core->getUserFormTypes();
		$matrix = $mObj->getUserRegFormMatrix();
		if($lk = Library::LastKey($matrix)) $matrix[$lk]['post_text'] = (isset($matrix[$lk]['post_text']) ? $matrix[$lk]['post_text'] : '').
			'<script type="text/javascript">'."\n".'showTypeFields();'."\n".'</script>';

		foreach($types as $i => $e){
			foreach($mObj->getUserRegFormMatrix('', $i) as $i1 => $e1) $values[$i.'_'.$i1] = isset($values[$i1]) ? $values[$i1] : '';
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'clientsData2',
						'clientsData2',
						array('caption' => '{Call:Lang:modules:billing:dannyeklient:'.Library::serialize(array($values['login'])).'}')
					),
					array(
						'add_client',
						_W.'modules/core/forms/users.php',
						$matrix
					),
					array(
						'groups' => Library::array_merge(array('' => '{Call:Lang:modules:billing:nenaznachat}'), $this->Core->getUserGroups()),
						'clientLevels' => Library::array_merge(array('' => '{Call:Lang:modules:billing:nenaznachat}'), $this->getLoyaltyLevels()),
						'modify' => $this->values['id'],
						'formTypes' => $types
					),
					array('pwd', 'cpwd')
				),
				$values,
				array('modify' => $this->values['id']),
				'big'
			)
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'usersPwdChange',
						'usersPwdChange',
						array('caption' => '{Call:Lang:modules:billing:smenitparolp}')
					),
					'type_newpwd'
				),
				array(),
				array('id' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_usersPwdChange(){
		/*
			Меняет юзеру пороль
		*/

		if(!$this->check()) return false;
		$this->Core->setUserPassword($this->DB->cellFetch(array('clients', 'user_id', "`id`='{$this->values['id']}'")), $this->values['pwd']);
		$this->refresh('clientsData&id='.$this->values['id']);
	}

	protected function func_clientsData2(){
		/*
			Устанавливаем данные клиента
		*/

		if(!$this->check()) return false;

		$id = db_main::Quot($this->values['modify']);
		$userId = $this->DB->cellFetch(array('clients', 'user_id', "`id`='$id'"));
		$this->Core->DB->Upd(array('users', $this->Core->getUserModifyFormValues($this), "`id`=$userId"));

		$return = $this->DB->Upd(array('clients', $this->fieldValues(array('loyal_level', 'date')), "`id`=$id"));
		$this->refresh('clients');
		return $return;
	}

	protected function func_servicesByClient(){
		/*
			Услуги по клиенту
		*/

		foreach($this->getServicesByClient($this->values['id']) as $i => $e){
			$fk = Library::firstKey($e);
			$list = $this->newList(
				'user_services_list_'.$i,
				array(
					'arr' => $e,
					'entryTemplate' => 'ordered_services_list_by_client',
					'actions' => array(
						'params' => 'ordersData'
					),
					'action' => 'ordersActions&serviceId='.$e[$fk]['s_id'],
					'form_actions' => array(
						'prolong' => '{Call:Lang:modules:billing:prodlit}',
						'modify' => '{Call:Lang:modules:billing:smenittarif}',
						'transmit' => '{Call:Lang:modules:billing:peredat}',
						'suspend' => '{Call:Lang:modules:billing:zablokirovat}',
						'unsuspend' => '{Call:Lang:modules:billing:razblokirova2}',
						'delete' => '{Call:Lang:modules:billing:udalit}'
					),
				),
				array(
					'caption' => $e[$fk]['s_text']
				)
			);

			$this->setContent($this->getListText($list, 'big'));
		}
	}

	protected function func_billsByClient(){
		/*
			Заказы по клиенту
		*/

		$this->values['client_id'] = $this->values['id'];
		$this->values['in_search'] = 1;
		$this->values['id'] = '';
		$this->func_bills();
	}

	protected function func_paysByClient(){
		/*
			Платежи по клиенту
		*/

		$cData = $this->getUserByClientId($this->values['id']);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'pays',
					array(
						'req' => array('pays', '*', "`client_id`='{$this->values['id']}'"),
						'searchForm' => array(
							'searchFields' => array(
								'date' => '{Call:Lang:modules:billing:operatsiiais}',
								'sum' => '{Call:Lang:modules:billing:summa}',
								'foundation_type' => '{Call:Lang:modules:billing:tipoperatsii}',
								'foundation' => '{Call:Lang:modules:billing:kommentarij}',
							),
							'searchMatrix' => array(
								'sum' => array('type' => 'gap'),
								'foundation_type' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:billing:liubye}',
										'balance' => '{Call:Lang:modules:billing:popolnenieba}',
										'bonus' => '{Call:Lang:modules:billing:zachislenieb}',
										'service' => '{Call:Lang:modules:billing:spisaniezaus}',
										'wrong' => '{Call:Lang:modules:billing:oshibochnoez}',
										'return' => '{Call:Lang:modules:billing:vozvratsreds}',
										'other' => '{Call:Lang:modules:billing:drugoe}'
									)
								)
							),
							'orderFields' => array('date' => '{Call:Lang:modules:billing:data}', 'sum' => '{Call:Lang:modules:billing:summa}')
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:spisokplatez:'.Library::serialize(array($cData['name'])).'}'
					)
				)
			)
		);
	}



	/********************************************************************************************************************************************************************

																			Обслуживание счетов

	*********************************************************************************************************************************************************************/

	protected function func_bills(){
		/*
			Выводит список счетов
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'billData',
						'billData',
						array(
							'caption' => '{Call:Lang:modules:billing:vybratzakazp}'
						)
					),
					'bill_by_number'
				),
				array(),
				array(),
				'big'
			)
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'orders';
		$t2 = $p.'payment_transactions';
		$t3 = $p.'clients';
		$t4 = $p.'currency';
		$t5 = $p.'payments';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'bills_list',
					array(
						'req' => "SELECT t1.*, t2.pay AS paydate, t3.user_id, t4.name AS curname, t4.text AS curtextname, t4.exchange AS curexch, t5.name AS payname, t5.text AS paytextname
							FROM $t1 AS t1
							LEFT JOIN $t2 AS t2 ON t2.object_id=t1.id AND t2.object_type='orders'
							LEFT JOIN $t3 AS t3 ON t3.id=t1.client_id
							LEFT JOIN $t4 AS t4 ON t4.name=t2.currency OR (t2.currency IS NULL AND t4.default)
							LEFT JOIN $t5 AS t5 ON t5.name=t2.payment
							WHERE `step`>=3
							ORDER BY t1.date DESC",
						'countReq' => "SELECT COUNT(id) FROM $t1",
						'extraReqs' => array(
							array(
								'req' => array('users', array('id', 'login', 'name', 'eml')),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'user_id',
								'unitedFld2' => 'id',
								'prefix' => 'user_'
							)
						),
						'form_actions' => array(
							'1' => '{Call:Lang:modules:billing:pometitkakna}',
							'3' => '{Call:Lang:modules:billing:pometitkakna1}',
							'4' => '{Call:Lang:modules:billing:pometitkakna2}',
							'6' => '{Call:Lang:modules:billing:pometitkakza}',
							'-1' => '{Call:Lang:modules:billing:udalit}',
						),
						'actions' => array(
							'id_url' => 'billData'
						),
						'action' => 'billActions',
						'searchForm' => array(
							'searchFields' => array(
								'id' => '{Call:Lang:modules:billing:nomer}',
								'payment_transaction_id' => '{Call:Lang:modules:billing:idplatezha}',
								'client_id' => '{Call:Lang:modules:billing:idklienta}',
								'date' => '{Call:Lang:modules:billing:datasozdanii}',
								'ordered' => '{Call:Lang:modules:billing:dataformirov}',
								'paydate' => '{Call:Lang:modules:billing:dataoplaty}',
								'total' => '{Call:Lang:modules:billing:raschetnaias8}',
								'payed' => '{Call:Lang:modules:billing:oplacheno}',
								'curname' => '{Call:Lang:modules:billing:valiutaoplat}',
								'payname' => '{Call:Lang:modules:billing:sposoboplaty1}',
								'step' => '{Call:Lang:modules:billing:status}'
							),
							'orderFields' => array(
								'id' => '{Call:Lang:modules:billing:idscheta}',
								'client_id' => '{Call:Lang:modules:billing:idklienta}',
								'payment_transaction_id' => '{Call:Lang:modules:billing:idplatezha}',
								'date' => '{Call:Lang:modules:billing:datesozdanii}',
								'ordered' => '{Call:Lang:modules:billing:dateoformlen}',
								'paydate' => '{Call:Lang:modules:billing:dateoplaty}',
								'step' => '{Call:Lang:modules:billing:statusu}',
								'total' => '{Call:Lang:modules:billing:konechnojsum}',
								'payed' => '{Call:Lang:modules:billing:summeoplaty}'
							),
							'searchMatrix' => array(
								'ordered' => array('type' => 'calendar'),
								'paydate' => array('type' => 'calendar'),
								'total' => array('type' => 'gap'),
								'step' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:billing:vse}',
										'-1' => '{Call:Lang:modules:billing:udalennye}',
										'0' => '{Call:Lang:modules:billing:neudalennye}',
										'1' => '{Call:Lang:modules:billing:vstadiizakaz}',
										'3' => '{Call:Lang:modules:billing:vstadiivybor}',
										'4' => '{Call:Lang:modules:billing:oplachivaemy}',
										'6' => '{Call:Lang:modules:billing:provedennye}'
									)
								),
								'curname' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getCurrency())
								),
								'payname' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), $this->getPayment())
								)
							),
							'searchExpr' => array('step' => array('0' => '`step`>=0', '1' => '`step`>=0 AND `step`<3', '4' => '`step`>=4 AND `step`<=5')),
							'isBe' => array('id' => true, 'payment_transaction_id' => true, 'client_id' => true, 'curname' => true, 'payname' => true),
							'searchPrefix' => array('id' => 't1', 'payment_transaction_id' => 't1', 'client_id' => 't1', 'date' => 't1', 'ordered' => 't1', 'paydate' => 't2', 'total' => 't1', 'payed' => 't2', 'curname' => 't4', 'payname' => 't5'),
							'searchAlias' => array('paydate' => 'pay', 'curname' => 'name', 'payname' => 'name'),
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:sozdannyezak}'
					)
				),
				'big'
			)
		);
	}

	protected function func_billData(){
		/*
			Выводит счет с предложением его провести
			выводится расчет а также комбинированная форма внесения денег на счет и создания услуг
			Форма имеет вид multiblock, под каждую услугу отводится свой блок
			После генерации формы осуществляется обращение к зависимым модулям (всем) к методу billForm с целью сформировать форму окончательно
		*/

		$this->funcName = 'Заказ №"'.$this->values['id'].'"';
		$this->pathFunc = 'bills';
		$oData = $this->getOrderParams($this->values['id']);

		$this->setContent($this->showOrderedServices($this->values['id'], array('calcOnly' => true)));
		if($oData['step'] < 3) $this->setContent('{Call:Lang:modules:billing:ehtotzakazna}');
		elseif($oData['step'] > 4) $this->setContent('{Call:Lang:modules:billing:ehtotzakazuz:'.Library::serialize(array($this->path, $this->mod, $this->values['id'])).'}');
		$this->setContent($this->getFormText($this->generateBill($this->values['id']), array(), array(), 'multiblock'));
	}

	protected function func_billData2(){
		/*
			Зачисляет пользователю бабло на баланс, создает все заказанные услуги
		*/

		if(!$this->check()) return false;

		$this->enrollTransaction($this->getTransactionByObjectId($this->values['id'], 'orders'), $this->values['sum'], $this->values['currency'], $this->values['payment'], '', $this->values['date'], $this->getTransactionExtra());
		$this->enroll($this->values['id'], 'orders', $this->values);
		$this->back('billData&id='.$this->values['id'], '', '', '');

		return false;
	}

	protected function func_unenrollBill(){
		/*
			Снимает данные о проводке счета
		*/

		$this->DB->Upd(array('orders', array('step' => 4), "`id`='".db_main::Quot($this->values['id'])."'"));
		$this->refresh('billData&id='.$this->values['id']);
	}

	protected function func_billActions(){
		/*
			Массовые действия над счетами
		*/

		if(empty($this->values['entry'])){
			$this->back('bills', '', '', '{Call:Lang:modules:billing:nevybranonio}');
			return false;
		}

		$return = $this->DB->Upd(array('orders', array('step' => $this->values['action']), $this->getEntriesWhere(false, 'id')));
		$this->refresh('bills');
		return $return;
	}


	/********************************************************************************************************************************************************************

																		Обслуживание платежей

	*********************************************************************************************************************************************************************/

	protected function func_pays(){
		/*
			Принятые платежи
		*/

		$form = $this->addFormBlock($this->newForm('payNew', 'payNew', array('caption' => '{Call:Lang:modules:billing:vnestiplatez}')), 'pays');
		$this->setContent($this->getFormText($form));

		$p = $this->DB->getPrefix();
		$t1 = $p.'pays';
		$t2 = $p.'clients';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'pays',
					array(
						'req' => "SELECT t1.*, t2.user_id FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.client_id=t2.id ORDER BY t1.id DESC, t1.date DESC",
						'extraReqs' => array(
							array(
								'req' => array('users', array('login', 'name')),
								'DB' => $this->Core->DB,
								'unitedFld1' => 'user_id',
								'unitedFld2' => 'id',
								'prefix' => 'user_',
								'search' => array('login' => 1)
							)
						),
						'searchForm' => array(
							'searchFields' => array(
								'user_login' => '{Call:Lang:modules:billing:loginpolzova}',
								'date' => '{Call:Lang:modules:billing:operatsiiais}',
								'sum' => '{Call:Lang:modules:billing:summa}',
								'foundation_type' => '{Call:Lang:modules:billing:tipoperatsii}',
								'foundation' => '{Call:Lang:modules:billing:kommentarij}'
							),
							'searchMatrix' => array(
								'sum' => array('type' => 'gap'),
								'foundation_type' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:billing:liubye}',
										'balance' => '{Call:Lang:modules:billing:popolnenieba}',
										'bonus' => '{Call:Lang:modules:billing:zachislenieb}',
										'service' => '{Call:Lang:modules:billing:spisaniezaus}',
										'wrong' => '{Call:Lang:modules:billing:oshibochnoez}',
										'return' => '{Call:Lang:modules:billing:vozvratsreds}',
										'other' => '{Call:Lang:modules:billing:drugoe}'
									)
								)
							),
							'isBe' => array('foundation_type' => 1),
							'orderFields' => array('date' => '{Call:Lang:modules:billing:data}', 'sum' => '{Call:Lang:modules:billing:summa}'),
							'searchPrefix' => array('user_login' => 't2', 'date' => 't1', 'sum' => 't1', 'foundation_type' => 't1', 'foundation' => 't1')
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:spisokplatez1}'
					)
				)
			)
		);
	}

	protected function func_payNew(){
		/*
			Новый платеж
		*/

		if(!$clientId = $this->getClientByIdOrLogin($this->values['client_id'])) $this->setError('client_id', '{Call:Lang:modules:billing:nenajdenotak}');
		elseif($this->values['foundation_type'] == 'service' && $this->values['service_id'] && ($this->DB->cellFetch(array('order_services', 'client_id', "`id`='{$this->values['service_id']}'")) != $clientId)){
			$this->setError('service_id', 'Услуги с таким ID не найдено у этого клиента');
		}
		elseif($this->values['foundation_type'] == 'other' && !$this->DB->issetTable('other_type')) $this->setError('other_type', 'Такая таблица не найдена');

		if(!$this->check()) return false;

		$id = $sId = 0;
		$objectType = '';

		switch($this->values['foundation_type']){
			case 'balance':
				$this->enrollTransaction(
					$this->newPaymentTransaction($this->values['sum'], $this->values['currency'], '', 0, $clientId),
					$this->values['sum'],
					$this->values['currency'],
					$this->values['payment'],
					'',
					$this->values['date'],
					$this->getTransactionExtra(),
					$this->values['foundation']
				);

				$this->back('pays', '', '', '');
				return;

			case 'service':
				$objectType = 'order_services';
				$sId = $id = $this->values['service_id'];
				break;

			case 'other':
				$objectType = $this->values['other_type'];
				$id = $this->values['other_id'];
				break;
		}

		$this->upBalance($clientId, $this->values['sum'], $this->values['currency'], $this->values['foundation_type'], $this->values['date'], $id, $sId, $this->values['foundation'], $objectType);
		$this->back('pays', '', '', '');
		return $return;
	}


	/********************************************************************************************************************************************************************

																				Скидки

	*********************************************************************************************************************************************************************/

	protected function func_discounts(){
		/*
			Скидки бывают следующие:
				- В связи с заказом других услуг
				- В связи с особенностями заказа (особенности заполнения отдельных полей заказа) и как частные случаи:
					- За способ оплаты
					- Промо-коды
					- Указание номера партнерского акка по рекомендации которого совершен визит
				- Бонусы (подарочные деньги при определенной сумме в заказе)
				- За общую сумму заказа (устанавливаются персонально для каждого тарифа)
				- За срок заказа (устанавливаются персонально для каждого тариф)
				- За срок заказа на установку услуги (устанавливаются персонально для каждого тариф)
				- Скидки постоянным клиентам, устанавливаются персонально для каждой услуги. Могут возникать:
					- При достижении некоторого срока непрерывного пользования услугой
					- При достижении некоторой суммы потраченной на обслуживание данной услуги
					- При достижении некоторой суммы потраченной вообще на содержание аккаунта
					- При достижении определенного уровня постоянного клиента (уровня лояльности)
		*/

		$id = $this->values['id'];
		$sData = $this->serviceDataById($id);
		$this->pathFunc = 'services';
		$this->funcName = '{Call:Lang:modules:billing:skidkiuslugi:'.Library::serialize(array($sData['text'])).'}';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'discounts',
						'discountNew&serviceId='.$id,
						array(
							'caption' => '{Call:Lang:modules:billing:novaiaskidka}'
						)
					),
					'discounts'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'discounts_list',
					array(
						'req' => array('discounts', array('id', 'name', 'text', 'type', 'date', 'start', 'end', 'sort', 'in_pkg_list', 'show'), "`service`='{$sData['name']}'", '`sort`'),
						'form_actions' => array(
							'suspend' => '{Call:Lang:modules:billing:zablokirovat}',
							'unsuspend' => '{Call:Lang:modules:billing:razblokirova2}',
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'text' => 'discountData&serviceId='.$id
						),
						'action' => 'discountActions&serviceId='.$id,
						'searchForm' => array(
							'searchFields' => array(
								'name' => '{Call:Lang:modules:billing:identifikato3}',
								'text' => '{Call:Lang:modules:billing:imia}',
								'type' => '{Call:Lang:modules:billing:zachtoskidka}',
								'client_loyalty_levels' => '{Call:Lang:modules:billing:tipyklientov}',
								'show' => '',
								'date' => '{Call:Lang:modules:billing:sozdana}',
								'start' => '{Call:Lang:modules:billing:nachaloprime}',
								'end' => '{Call:Lang:modules:billing:konetsprimen}',
							),
							'searchMatrix' => array(
								'type' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:billing:vsetipy}',
										'term' => '{Call:Lang:modules:billing:srokzakaza1}',
										'order_sum' => '{Call:Lang:modules:billing:obshchuiusto}',
										'other_services' => '{Call:Lang:modules:billing:vsviaziszaka}',
										'promocode' => '{Call:Lang:modules:billing:ukazanieprom}',
										'baseless' => '{Call:Lang:modules:billing:bezosnovanij}',
										'other' => '{Call:Lang:modules:billing:inyeosobenno}'
									)
								),
								'client_loyalty_levels' => array(
									'type' => 'select',
									'additional' => Library::array_merge(
										array('' => '{Call:Lang:modules:billing:vse}'),
										Library::concatPrefixArrayKey(
											Library::array_merge(array('new-clients' => '{Call:Lang:modules:billing:novyeklienty}', 'old-clients' => '{Call:Lang:modules:billing:staryeklient}'), $this->getLoyaltyLevels()), ',', ','
										)
									)
								),
								'start' => array('type' => 'calendar'),
								'end' => array('type' => 'calendar'),
							),
							'orderFields' => array(
								'date' => '{Call:Lang:modules:billing:sozdana1}',
								'start' => '{Call:Lang:modules:billing:nachaloprime1}',
								'end' => '{Call:Lang:modules:billing:konetsprimen1}',
								'name' => '{Call:Lang:modules:billing:identifikato12}',
								'text' => '{Call:Lang:modules:billing:imeni}',
							),
							'searchParams' => array(
								'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$id
							)
						)
					),
					array(
						'caption' => '{Call:Lang:modules:billing:ustanovlenny5}'
					)
				)
			)
		);
	}

	protected function func_discountNew(){
		/*
			Выставляется новая скидка
		*/

		$sid = db_main::Quot($this->values['serviceId']);
		$id = empty($this->values['modify']) ? 0 : $this->values['modify'];
		$this->isUniq('discounts', array('name' => '{Call:Lang:modules:billing:takojidentif}', 'text' => '{Call:Lang:modules:billing:takoeimiauzh}'), $id);
		if(!$this->check()) return false;

		$fields = $this->fieldValues(array('date', 'start', 'end', 'text', 'name', 'type', 'sort', 'in_pkg_list', 'show'));
		$fields['vars'] = $this->fieldValues(array('basic_type', 'pkgs'));
		foreach($this->DB->columnFetch(array('discounts', 'text', 'name', "`id`!='$id'", '`sort`')) as $i => $e){
			if(isset($this->values['disable_discounts_'.$i])) $fields['vars']['disable_discounts_'.$i] = $this->values['disable_discounts_'.$i];
		}

		if($id){
			switch($this->values['type']){
				case 'term':
				case 'order_sum':
					$fields['vars']['discounts'] = Library::block2hash($this->values['discounts']);
					break;

				case 'other_services':
					$fields['vars']['other_services'] = $this->values['other_services'];
					$fields['vars']['discount'] = $this->values['discount'];
					$fields['vars']['discount_logic'] = $this->values['discount_logic'];

					foreach($this->getServices() as $i => $e){
						if(!empty($this->values['other_services_pkgs_'.$i])){
							$fields['vars']['discount_pkg_logic_'.$i] = $this->values['discount_pkg_logic_'.$i];
							$fields['vars']['other_services_pkgs_'.$i] = $this->values['other_services_pkgs_'.$i];

							foreach($fields['vars']['other_services_pkgs_'.$i] as $i1 => $e1){
								$fields['vars']['other_services_count_'.$i1.'_'.$i] = $this->values['other_services_count_'.$i1.'_'.$i];
								$fields['vars']['other_services_term_'.$i1.'_'.$i] = $this->values['other_services_term_'.$i1.'_'.$i];
							}
						}
					}
					break;

				case 'promocode':
					$fields['vars']['promocodegroup'] = $this->values['promocodegroup'];
					$fields['vars']['discount'] = $this->values['discount'];
					break;

				case 'baseless':
					$fields['vars']['discount'] = $this->values['discount'];
					break;

				case 'other':
					$fields['vars']['discount'] = $this->values['discount'];
					break;
			}
		}

		$fields['service'] = $this->DB->cellFetch(array('services', 'name', "`id`='$sid'"));
		$fields['client_loyalty_levels'] = Library::arrKeys2str($this->values['client_loyalty_levels']);

		if(($newId = $this->typeIns('discounts', $fields)) && !$id) $this->redirect('discountData&id='.$newId.'&serviceId='.$sid);
		$this->refresh('discounts&id='.$sid);
		return $newId;
	}

	protected function func_discountData(){
		/*
			Более точные параметры скидки
		*/

		$sid = $this->values['serviceId'];
		$id = db_main::Quot($this->values['id']);
		$sData = $this->serviceDataById($sid);

		$values = $this->DB->rowFetch(array('discounts', '*', "`id`='$id'"));
		$values['client_loyalty_levels'] = Library::str2arrKeys($values['client_loyalty_levels']);
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$this->pathFunc = 'services';
		$this->pathPoint = array('discounts&serviceId='.$sid => '{Call:Lang:modules:billing:skidkiuslugi:'.Library::serialize(array($sData['text'])).'}');

		switch($values['type']){
			case 'term':
			case 'order_sum':
				$values['discounts'] = empty($values['discounts']) ? '' : Library::hash2block($values['discounts']);
				break;
		}

		return $this->typeModify(
			array(),
			'discounts',
			'discountNew&serviceId='.$sid,
			array(
				'formData' => array(
					'clientGroups' => Library::array_merge(array('new-clients' => '{Call:Lang:modules:billing:novyeklienty}', 'old-clients' => '{Call:Lang:modules:billing:staryeklient}'), $this->getLoyaltyLevels()),
					'extra' => true,
					'type' => $values['type'],
					'baseTerm' => $sData['base_term'],
					'payments' => $this->getPayment(),
					'services' => $this->getServices(),
					'packages' => $this->getPackages($sData['name']),
					'discounts' => $this->DB->columnFetch(array('discounts', 'text', 'name', "`id`!='$id'", '`sort`')),
					'promocodegroups' => $this->DB->columnFetch(array('promocodegroups', 'name', 'id', "", "`sort`"))
				),
				'params' => array(
					'caption' => '{Call:Lang:modules:billing:parametryski}'
				),
				'tmplName' => 'big',
				'values' => $values
			)
		);
	}

	protected function func_discountActions(){
		$this->typeActions('discounts', 'discounts&id='.$this->values['serviceId']);
	}



	/********************************************************************************************************************************************************************

																				Промо-коды

	*********************************************************************************************************************************************************************/

	protected function func_promoCodeGroups(){
		/*
			Промо-коды
		*/

		$clientGroups = Library::array_merge(array('new-clients' => '{Call:Lang:modules:billing:novyeklienty}', 'old-clients' => '{Call:Lang:modules:billing:staryeklient}'), $this->getLoyaltyLevels());
		$this->typicalMain(
			array(
				'name' => 'promocodegroups',
				'func' => 'promoCodeGroups',
				'caption' => '{Call:Lang:modules:billing:dobavitgrupp1}',
				'formData' => array('clientGroups' => $clientGroups),
				'modifyData' => array(array('extract' => array('code_distrib_client_levels'))),
				'listParams' => array(
					'req' => array('promocodegroups', '*'),
					'searchForm' => array(
						'searchFields' => array(
							'name' => '{Call:Lang:modules:billing:imia}',
							'code_distrib_style' => '{Call:Lang:modules:billing:kodyrazdaiut}',
							'code_distrib_client_levels' => '{Call:Lang:modules:billing:tipyklientov}',
						),
						'searchMatrix' => array(
							'code_distrib_style' => array('type' => 'select', 'additional' => array('0' => '{Call:Lang:modules:billing:tolkoadminy}', '1' => '{Call:Lang:modules:billing:adminyisushc}')),
							'code_distrib_client_levels' => array('type' => 'select', 'additional' => Library::array_merge(array('' => '{Call:Lang:modules:billing:vse}'), Library::concatPrefixArrayKey($clientGroups, ',', ',')))
						),
						'orderFields' => array('name' => '{Call:Lang:modules:billing:imeni}')
					),
					'actions' => array(
						'name' => 'promoCodeGroups&type_action=modify',
						'code' => 'promoCodes'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:billing:vsegruppy}'
				)
			)
		);
	}

	protected function func_promoCodes(){
		/*
			Новые промокоды
		*/

		$grpId = db_main::Quot(empty($this->values['grpId']) ? $this->values['id'] : $this->values['grpId']);
		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'promocodes',
						'promoCodesNew&grpId='.$grpId,
						array(
							'caption' => '{Call:Lang:modules:billing:dobavitpromo}'
						)
					),
					'promocodes'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'promocodes_list',
					array(
						'req' => array('promocodes', '*', "`group`='$grpId'", "`date` DESC"),
						'form_actions' => array(
							'reserve' => '{Call:Lang:modules:billing:zarezervirov}',
							'suspend' => '{Call:Lang:modules:billing:obiavitispol}',
							'unsuspend' => '{Call:Lang:modules:billing:obiavitneisp}',
							'delete' => '{Call:Lang:modules:billing:udalit}'
						),
						'actions' => array(
							'code' => 'promoCodesData&grpId='.$grpId
						),
						'action' => 'promoCodesActions&grpId='.$grpId,
						'searchForm' => array(
							'searchFields' => array(
								'code' => '{Call:Lang:modules:billing:chastkoda}',
								'code_style' => '{Call:Lang:modules:billing:postiliurabo}',
								'show' => '{Call:Lang:modules:billing:posostoianii}',
								'date' => '{Call:Lang:modules:billing:vnesen}',
								'started' => '{Call:Lang:modules:billing:nachalodejst}',
								'actually' => '{Call:Lang:modules:billing:konetsdejstv}'
							),
							'searchMatrix' => array(
								'started' => array('type' => 'calendar'),
								'actually' => array('type' => 'calendar'),
								'code_style' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:billing:vse}',
										'1' => '{Call:Lang:modules:billing:odnorazovye}',
										'2' => '{Call:Lang:modules:billing:mnogorazovye}'
									)
								),
								'show' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:modules:billing:vse}',
										'1' => '{Call:Lang:modules:billing:neispolzovan}',
										'0' => '{Call:Lang:modules:billing:ispolzovanny}',
										'2' => '{Call:Lang:modules:billing:zarezervirov1}'
									)
								)
							),
							'orderFields' => array(
								'date' => '{Call:Lang:modules:billing:datevnesenii1}',
								'started' => '{Call:Lang:modules:billing:nachaludejst}',
								'actually' => '{Call:Lang:modules:billing:okonchaniiud}',
								'code' => '{Call:Lang:modules:billing:kodu}'
							),
							'searchParams' => array(
								'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&grpId='.$grpId
							),
							'isBe' => array('show' => true)
						),
					),
					array(
						'caption' => '{Call:Lang:modules:billing:vsepromokody}'
					)
				)
			)
		);
	}

	protected function func_promoCodesActions(){
		/*
			Изменение состояний для промокодов
		*/

		return $this->typeActions('promocodes', 'promoCodes&grpId='.$this->values['grpId'], array('reserve' => array('show' => 2)));
	}

	protected function func_promoCodesData(){
		/*
			Изменение параметров промокода
		*/

		$this->typeModify(
			array(),
			'promocodes',
			'promoCodesData2&grpId='.$this->values['grpId'],
			array(
				'formData' => array('extra' => 1),
				'params' => array(
					'caption' => '{Call:Lang:modules:billing:promokodcode}'
				),
				'tmplName' => 'big',
				'values' => $this->DB->rowFetch(array('promocodes', '*', "`id`='".db_main::Quot($this->values['id'])."'"))
			)
		);
	}

	protected function func_promoCodesData2(){
		/*
			Изменение промокода
		*/

		$fields = $this->fieldValues(array('code_style', 'code', 'started', 'actually', 'show'));
		$this->values['code'] = regExp::upper($this->values['code']);
		$this->isUniq( 'promocodes', array('code' => '{Call:Lang:modules:billing:takojkoduzhe}'), empty($this->values['modify']) ? '' : $this->values['modify']);
		return $this->typeIns('promocodes', $fields, 'promoCodes&grpId='.$this->values['grpId']);
	}

	protected function func_promoCodesNew(){
		/*
			Вносит новые комплект прмокодов
		*/

		$grpId = $this->values['grpId'];

		if($this->values['insert'] == 'auto'){
			if(!$this->values['codes_cnt']) $this->setError('codes_cnt', '{Call:Lang:modules:billing:neukazanokol}');
			else{
				$codes = array();
				for($i = 1; $i <= $this->values['codes_cnt']; $i ++) $codes[] = $this->inventPromoCode();
			}
		}
		elseif(!$this->values['codes']) $this->setError('codes', '{Call:Lang:modules:billing:neukazanonio}');
		else{
			$codes = regExp::Split("\n", db_main::Quot(trim($this->values['codes'])));

			foreach($codes as $i => $e){
				if(!($codes[$i] = trim($codes[$i]))) continue;

				if($this->DB->cellFetch(array('promocodes', 'code', "`code`='$e'"))){
					$this->setError('codes', '{Call:Lang:modules:billing:koduzhesushc:'.Library::serialize(array($e)).'}');
				}
			}
		}

		if(!$this->check()) return false;

		foreach($codes as $i => $e){
			$this->DB->Ins(
				array(
					'promocodes',
					array(
						'date' => time(),
						'started' => $this->values['started'],
						'actually' => $this->values['actually'],
						'group' => $grpId,
						'code_style' => $this->values['code_style'],
						'code' => $e
					)
				)
			);
		}

		$this->refresh('promoCodes&grpId='.$grpId);
		return true;
	}


	/********************************************************************************************************************************************************************

																				Бонусы

	*********************************************************************************************************************************************************************/

	protected function func_bonuses(){
		/*
			Бонусы для заказчиков
		*/

		$this->setContent('{Call:Lang:modules:billing:bonusyehtone}', 'top_comment');
		$fields = array();

		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'new'){
				$fields = $this->fieldValues(array('name', 'min_sum', 'bonus', 'bonus_type', 'start', 'end'));
				$fields['date'] = time();
				$fields['client_loyalty_levels'] = isset($this->values['client_loyalty_levels']) ? Library::arrKeys2str($this->values['client_loyalty_levels']) : "";
				$fields['start'] = !empty($fields['start']) ? $fields['start'] : '';
				$fields['end'] = !empty($fields['end']) ? $fields['end'] : '';
			}
		}

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:modules:billing:dobavitbonus}',
				'formData' => array(
					'clientGroups' => Library::array_merge(array('new-clients' => '{Call:Lang:modules:billing:novyeklienty}', 'old-clients' => '{Call:Lang:modules:billing:staryeklient}'), $this->getLoyaltyLevels())
				),
				'listParams' => array(
					'req' => array('bonuses', '*', "", "min_sum"),
					'searchForm' => array(
						'searchFields' => array(
							'name' => '{Call:Lang:modules:billing:imia}',
							'min_sum' => '{Call:Lang:modules:billing:minimalnaias1}',
							'bonus' => '{Call:Lang:modules:billing:bonus}',
							'bonus_type' => '{Call:Lang:modules:billing:bonusukazanv}',
							'date' => '{Call:Lang:modules:billing:vnesen}',
							'start' => '{Call:Lang:modules:billing:nachatoispol}',
							'end' => '{Call:Lang:modules:billing:ispolzovanie}'
						),
						'orderFields' => array(
							'name' => '{Call:Lang:modules:billing:imeni}',
							'min_sum' => '{Call:Lang:modules:billing:minimalnojsu}',
							'bonus' => '{Call:Lang:modules:billing:razmerubonus}',
							'date' => '{Call:Lang:modules:billing:datevnesenii1}',
							'start' => '{Call:Lang:modules:billing:datenachalai}',
							'end' => '{Call:Lang:modules:billing:dateokonchan}'
						),
						'searchMatrix' => array(
							'min_sum' => array('type' => 'gap'),
							'bonus' => array('type' => 'gap'),
							'bonus_type' => array(
								'type' => 'select',
								'additional' => array('' => '{Call:Lang:modules:billing:chemugodno}', 'percent' => '{Call:Lang:modules:billing:protsentakh}', 'money' => $this->getMainCurrencyName())
							),
							'date' => array('type' => 'calendar'),
							'start' => array('type' => 'calendar'),
							'end' => array('type' => 'calendar')
						)
					),
					'actions' => array(
						'name' => 'bonuses&type_action=modify'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:billing:vsevozmozhny}'
				),
				'modifyData' => array('extract' => array('client_loyalty_levels')),
				'fields' => $fields
			)
		);
	}


	/********************************************************************************************************************************************************************

																			Уровни постоянных клиентов

	*********************************************************************************************************************************************************************/

	protected function func_loyalLevels(){
		/*
			Уровень постоянных клиентов
		*/

		$this->typicalMain(
			array(
				'name' => 'loyalty_levels',
				'func' => 'loyalLevels',
				'caption' => '{Call:Lang:modules:billing:dobaviturove}',
				'listParams' => array(
					'req' => array('loyalty_levels', '*'),
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:modules:billing:imia}',
							'name' => '{Call:Lang:modules:billing:identifikato3}',
							'add_with_registry' => '',
							'add_with_all_payments' => '{Call:Lang:modules:billing:prisvaivaimy}',
							'add_with_all_payed_services' => '{Call:Lang:modules:billing:pridostizhen}',
						),
						'searchMatrix' => array(
							'add_with_registry' => array(
								'type' => 'radio',
								'additional' => array('1' => '{Call:Lang:modules:billing:prisvaimyepr}', '0' => '{Call:Lang:modules:billing:neprisvaimye}', '' => '{Call:Lang:modules:billing:vse}')
							),
							'add_with_all_payments' => array('type' => 'gap'),
							'add_with_all_payed_services' => array('type' => 'gap'),
						),
						'orderFields' => array(
							'add_with_all_payments' => '{Call:Lang:modules:billing:summepridost}',
							'add_with_all_payed_services' => '{Call:Lang:modules:billing:summepridost1}',
							'text' => '{Call:Lang:modules:billing:imeni}',
							'name' => '{Call:Lang:modules:billing:identifikato12}',
						)
					),
					'actions' => array(
						'text' => 'loyalLevels&type_action=modify'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:modules:billing:spisokurovne}'
				)
			)
		);
	}
}

?>