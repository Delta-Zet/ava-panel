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


class mod_partner extends gen_partner{


	/********************************************************************************************************************************************************************

																		Регистрация в партнерке

	*********************************************************************************************************************************************************************/

	protected function func_partnerReg(){
		/*
			Регистрация в партнерке. Если йузер не зареген и не авторизован, ему предлагается зарегисться или авторизоваться, после чего происходит рега.
		*/

		$this->setMeta('{Call:Lang:modules:partner:registratsii2}');

		if($this->getPartnerId()){
			throw new AVA_Exception('{Call:Lang:modules:partner:vyuzhezaregi}');
		}
		elseif(($aid = $this->Core->userIsAdmin()) && ($this->Core->getUserId() == $this->Core->DB->cellFetch(array('admins', 'user_id', "`id`='$aid'")))){
			$mMod = $this->Core->callModule('core');
			$callFunc = 'authAdminAsUser';
		}
		elseif(!$userId = $this->Core->getUserId()){
			$mMod = $this->Core->callModule('main');
			$mMod->values['type_auth'] = 2;
			$mMod->values['in_module'][$this->mod] = 1;
			$callFunc = 'registration';
		}
		else{
			list($matrix, $values) = $this->getMatrixArray(array('partner_reg_form', '*', "`show`", "`sort`"));
			$fObj = $this->addFormBlock($this->newForm('partnerReg2', 'partnerReg2'), array('partner_reg', $matrix), array('values' => $values));

			if($fObj->matrixIsEmpty()){
				$this->redirect('partnerReg2&ava_form_transaction_id='.$this->getFormId('partnerReg2').'&'.Library::deparseStr($fObj->getHiddens()));
			}
			else $this->setContent($this->getFormText($fObj));

			return true;
		}

		$mMod->values['redirect'] = $this->getCallUrl();
		$return = $mMod->callFunc($callFunc);
		$this->Core->contentMod2Mod($mMod, $this);
		return $return;
	}

	protected function func_partnerReg2(){
		/*
			Завершает регистрацию в партнерке
		*/

		if($this->getPartnerId()){
			throw new AVA_Exception('{Call:Lang:modules:partner:vyuzhezaregi}');
		}

		if(isset($this->values['login'])) $this->isUniq('partners', array('login' => '{Call:Lang:modules:partner:takojpsevdon1}'));
		if(!$this->check()) return false;

		if($return = $this->insertPartner($this->Core->getUserId(), $this->values['login'], $this->values)){
			$this->Core->User->authInModule($this->mod);
			$this->refresh('');
		}
		else $this->back('');
		return $return;
	}


	/********************************************************************************************************************************************************************

																				Переходы

	*********************************************************************************************************************************************************************/

	protected function func_partnerSites(){
		/*
			Сайты партнера
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:sajty}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'partnerSiteAdd',
						'partnerSiteAdd',
						array('caption' => '{Call:Lang:modules:partner:dobavitsajt}')
					),
					'sites'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'user_sites',
					array(
						'req' => array('sites', '*', "`partner_id`='{$this->Core->User->extraParams['partners']['partnerData']['login']}' AND `status`>=-1", "`sort`"),
						'actions' => array(
							'show' => 'partnerSiteModify'
						),
						'action' => 'partnerSitesAction',
						'form_actions' => array(
							'delete' => '{Call:Lang:modules:partner:udalit}'
						)
					),
					array(
						'caption' => '{Call:Lang:modules:partner:vsesajty}'
					)
				),
				'users'
			)
		);
	}

	protected function func_partnerSiteAdd(){
		/*
			Добавляем партнерский сайт
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:sajty}');

		$fields = $this->fieldValues(array('name', 'url'));
		$fields['partner_id'] = $this->Core->User->extraParams['partners']['partnerData']['login'];
		$fields['date'] = time();
		$fields['status'] = $this->Core->getParam('partnerSiteRegFree', $this->mod);

		$this->isUniq('sites', array('name' => '{Call:Lang:modules:partner:takoeimiauzh}', 'url' => '{Call:Lang:modules:partner:sajtstakimur}'), empty($this->values['modify']) ? 0 : $this->values['modify']);
		return $this->typeIns('sites', $fields, 'partnerSites');
	}

	protected function func_partnerSiteModify(){
		/*
			Мудификация сайта
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:sajty}');

		$this->typeModify(
			array('sites', '*', "`id`='".db_main::Quot($this->values['id'])."' AND `partner_id`='{$this->Core->User->extraParams['partners']['partnerData']['login']}'"),
			'sites',
			'partnerSiteAdd',
			array(
				'caption' => '{Call:Lang:modules:partner:sajtname}'
			)
		);
	}

	protected function func_partnerSitesAction(){
		/*
			Удаление сайта
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:sajty}');
		return $this->typeActions('sites', 'partnerSites', array(), " AND `partner_id`='{$this->Core->User->extraParams['partners']['partnerData']['login']}'");
	}


	/********************************************************************************************************************************************************************

																					Банеры

	*********************************************************************************************************************************************************************/

	protected function func_partnerBanners(){
		/*
			Банеры
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:banery}');
		$this->setContent($this->Core->readAndReplace($this->Core->getModuleTemplatePath($this->mod).'banners.tmpl', $this, array('banners' => $this->getBannerParams())));
	}


	/********************************************************************************************************************************************************************

																			Запросы оплаты

	*********************************************************************************************************************************************************************/

	protected function func_partnerPay(){
		/*
			Запрос оплаты партнерки
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:zaprosoplaty}');

		$fObj = $this->newForm('partnerPay2', 'partnerPay2');
		$this->addFormBlock($fObj, 'partner_pay', array('payments' => $this->getPayment()));

		foreach($this->fetchPayments() as $i => $e){
			if($e['extension']){
				$this->callPaymentExtension($e['extension'], 'payReqForm', $i, array('fObj' => $fObj));
			}
		}

		$this->setContent($this->getFormText($fObj));
	}

	protected function func_partnerPay2(){
		/*
			Запрос оплаты партнерки
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('{Call:Lang:modules:partner:zaprosoplaty}');

//		if($this->values['sum'] > $this->Core->User->params['balance']) $this->setError('sum', 'У вас недостаточно средств на счету');
		$vars = $this->callPaymentExtension($this->paymentExtension($this->values['payment']), 'checkReqForm', $this->values['payment']);
		if(!$this->check()) return false;

		$return = $this->DB->Ins(
			array(
				'pay_orders',
				array(
					'login' => $this->Core->User->params['login'],
					'date' => time(),
					'sum' => $this->values['sum'],
					'payment' => $this->values['payment'],
					'vars' => $vars,
					'init' => 'u'
				)
			)
		);

		$this->refresh('');
		return $return;
	}

	/********************************************************************************************************************************************************************

																		Статистика партнера

	*********************************************************************************************************************************************************************/

	protected function func_clientsStat(){
		/*
			Списки клиентов относящихся к данному партнеру
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId()) || !$this->canView('Clients')) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('Клиенты пришедшие по вашей рекомендации');

		$this->setContent(
			$this->getListText(
				$this->newList(
					'user_clients_stat',
					array(
						'req' => array(
							'users',
							'*',
							$this->getEntriesWhere(
								$this->DB->columnFetch(
									array(
										'partner_users',
										'user_id',
										'user_id',
										"`partner_id`='{$this->Core->User->extraParams['partners']['partnerData']['login']}'"
									)
								)
							),
							"`date` DESC"
						),
						'DB' => $this->Core->DB,
						'actions' => array(
							'services' => 'clientsServicesStat'
						)
					)
				),
				'users'
			)
		);
	}

	protected function func_clientsServicesStat(){
		/*
			Услуги клиента (статистика)
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId()) || !$this->canView('Clients')) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		if(!$this->canViewThisUser($this->values['id'])) throw new AVA_Access_Exception('Вы не можете просматривать данные этого пользователя');
		$userData = $this->Core->getUserParamsById($this->values['id']);
		$this->setMeta('Услуги пользователя "'.($userData['name'] ? $userData['name'] : $userData['login']).'"');

		foreach($GLOBALS['Core']->getCoUnitedModulesByType('billing', $this->mod, 'cms') as $i => $e){
			$bObj = $this->Core->callModule($i);

			if($clientId = $bObj->getClientByUserId($this->values['id'])){
				if($services = $bObj->getServicesByClient($clientId, "AND (`step`=2 OR `step`=-1)")){
					foreach($services as $i1 => $e1){
						$fk = Library::firstKey($e1);

						$this->setContent(
							$this->getListText(
								$this->newList(
									'user_services_list_'.$i1,
									array(
										'arr' => $e1,
										'entryTemplate' => 'user_clients_orders_stat',
									),
									array(
										'caption' => $e1[$fk]['s_text'],
										'bObj' => $bObj,
										'clientId' => $clientId
									)
								),
								'users'
							)
						);
					}
				}
			}
		}
	}

	protected function func_referalsStat(){
		/*
			Списки партнеров-рефералов
		*/
	}

	protected function func_bannersStat(){
		/*
			Статистика банеров
		*/

		if(!($userId = $this->Core->User->getUserId()) || !($partnerId = $this->getPartnerId())) throw new AVA_Access_Exception('{Call:Lang:modules:partner:vyneavtorizo}');
		$this->setMeta('Переходы по банерам');

		$p = $this->DB->getPrefix();
		$t1 = $p.'pays';
		$t2 = $p.'order';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'user_banners_stat',
					array(
						'req' => "SELECT t1.id AS p_id, t1.date AS p_date, t1.sum AS p_sum, t1.descript AS p_descript, t2.* ".
							"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.entry_id=t2.id WHERE t1.type='click' AND t1.partner_id='{$partnerId}' ORDER BY t1.date DESC"
					)
				),
				'usertable'
			)
		);
	}


	/********************************************************************************************************************************************************************

																				Переходы

	*********************************************************************************************************************************************************************/

	protected function func_click(){
		/*
			Происходит при переходе по партнерской ссылке
			Система сохраняет сведения о клике, начисляет $, выставляет куку и перенаправляет нах
		*/

		if(
			!empty($this->values['partner']) &&
			!$this->DB->cellFetch(
				array(
					'click',
					'id',
					db_main::q(
						"`partner_id`=#0 AND `ip`=#1 AND `date`>='".(time() - $this->Core->getParam('partnerClickInterval', $this->mod))."'",
						array($this->values['partner'], $this->Core->getGPCVar('s', 'REMOTE_ADDR'))
					)
				)
			)
		){
			$this->setClick($this->values['partner'], empty($this->values['banner']) ? false : $this->values['banner']);
			if(!$this->Core->getGPCVar('c', 'referedBy') || $this->Core->getParam('partnerClientMemory', $this->mod) == 'last'){
				$t = time() + ($this->Core->getParam('partnerCookieLife', $this->mod) * 60 * 60 * 24);
				$this->Core->setCookie('referedBy', $this->values['partner'], $t);
				$this->Core->setCookie('referedByMod', $this->mod, $t);
			}
		}

		$this->Core->setHeader('Location', empty($this->values['banner']) ? $this->path : $this->getBannerUrl($this->values['banner']));
	}


	/********************************************************************************************************************************************************************

																Задания для партнерки выполняемые по cron

	*********************************************************************************************************************************************************************/

	protected function func_partnersOrderPays(){
		/*
			Начисляет средства партнерам при распределении выплат
		*/

		$filter = array();
		$cData = array();
		$cObj = array();

		$t = time();
		$params = $this->getEstimations('orders');
		$orders = $this->DB->columnFetch(array('order', '*', 'id', "`status`=0 AND `object_type`='order' AND `period_start`<$t"));

		foreach($orders as $i => $e){
			if(!isset($cObj[$e['mod']])) $cObj[$e['mod']] = $this->Core->callModule($e['mod']);;
			if(!isset($cData[$e['client_id']])) $cData[$e['client_id']] = $cObj[$e['mod']]->getClientData($e['client_id']);

			if(($e['period_end'] > $t) && ($this->getPayMomentStyle($e['mod'], $cData[$e['client_id']], $e['service'], $e['pkg']) == 'portioned-post')){
				unset($orders[$i]);
				continue;
			}
			$filter[$e['mod']][$e['object_id']] = 1;
		}

		foreach($filter as $i => $e){
			foreach($cObj[$i]->DB->columnFetch(array('order_services', array('id', 'step'), 'id', $this->getEntriesWhere($e))) as $i1 => $e1){
				if($e1['step'] != 2 && $e1['step'] != -1){
					foreach($orders as $i2 => $e2){
						if($e2['object_id'] == $i1) unset($orders[$i2]);
					}
				}
			}
		}

		$return = array();
		foreach($orders as $i => $e){
			$return[$i] = $this->endSetOrder($i, $cData[$e['client_id']], false, $e);
		}

		return $return;
	}
}

?>