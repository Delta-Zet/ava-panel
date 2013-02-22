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



class gen_billing extends ModuleInterface{
	/*
		В качестве расширения модуль биллинга обращается к сторонним модулям, которые являются от него зависимыми:

		//При оформлении заказа
		public function setUserOrderMatrix($obj, $params = array()){}
		public function checkUserOrderMatrix($obj, $params = array()){}
		public function getUserOrderParams($obj, $params = array()){}

		//При проводке заказа админом
		public function setOrderMatrix($obj, $params = array()){}
		public function checkOrderMatrix($obj, $params = array()){}
		public function getOrderParams($obj, $params = array()){}

		//При регистрации клиента
		public function addClient($obj, $service, $params = array()){}

		//При изменении информации о клиенте
		public function modifyClient($obj, $service, $params = array()){}

		//При удалении клиента
		public function delClient($obj, $service, $params = array()){}

		//При удалении счета
		public function delOrder($obj, $service, $params = array()){}
	*/
	//Для платежей
	private $currencyParams = false;
	private $defaultCurrency = false;
	private $currencyList = array();

	private $paymentParams = false;
	private $paymentList = array();
	private $paymentListByType = array();
	private $payExtensions = array();

	private $smsParams = false;
	private $smsParamsById = array();
	private $smsList = array();
	private $smsListByType = array();
	private $smsExtensions = array();

	private $smsNumberParams = false;
	private $smsNumberParamsByNum = false;
	private $smsNumbers = array();
	private $smsNumbersByAgr = array();

	//Для услуг
	private $servicesData = false;
	private $servicesData2 = false;
	private $servicesByServer = array();

	private $servicesByEx = array();
	private $servicePackages = array();
	private $servicePackagesByGrp = array();

	private $servicePackagesByMainGrp = array();
	private $servicePackagesBySN = array();
	private $servicePackageNamesBySN = array();

	private $servicesList = array();
	private $servicesListById = array();
	private $servicesListByExtension = array();
	private $servicesListByIdByExtension = array();

	private $serviceObjects = array();
	private $serviceExtensions = array();
	private $terms = array();
	private $prolongTerms = array();
	private $pkgsByServerName = array();

	private $pkgGroups = false;
	private $pkgGroupsById = array();
	private $pkgGroupNames = array();

	//Для комплектов услуг
	private $complexParams = false;
	private $complexList = array();
	private $openComplexList = array();

	//Для соединений
	public $connections = array();
	protected $connectionParams = false;
	protected $connectionCp = false;
	protected $connectionMods = false;
	protected $connectionModServices = false;
	protected $connectionModServices2 = false;
	protected $connectionsByPkg = false;

	//Для счетов и транзакций
	private $discounts;
	private $discountsByType = array();
	private $currentDiscountParams;
	private $promoCodeParams = false;
	private $promoCodeGroups = false;
	private $promoCodeGroupParams = array();
	private $canUsePromoCode = array();

	//Для счетов и транзакций
	private $orders = array();
	private $transactions = array();
	private $oComplex = array();
	private $oComplexByOrder = array();

	//Для клиентов
	private $clients = array();
	private $clientsData = array();
	private $users = array();

	//Записи об услугах
	private $orderedServices = array();
	private $orderEntries = array();
	private $orderEntriesByEntry = array();
	private $orderEntriesByFilter = array();
	private $modifyOrders = array();
	private $modifyOrdersByMainOrder = array();
	private $modifyMainOrders = array();
	private $deleteOrders = array();
	private $suspendOrders = array();
	private $unsuspendOrders = array();
	private $transmitOrders = array();
	public $createdServiceParams = array();

	//Записи об операциях
	private $operations = array();

	//Уровни клиентов
	private $loyaltyLevels = false;


	protected function __init(){
		/*
			Дополнительная инициализация модуля
		 */

		$this->setContent('<link rel="stylesheet" type="text/css" href="'.$this->Core->getModuleTemplateUrl($this->mod).'style.css" />', 'head');
	}

	public function __ava____authUser($obj){		/*
			Доп. аутентификация пользовтеля
		*/

		if($clientData = $this->DB->rowFetch(array('clients', '*', "`user_id`='".$obj->getUserId()."'"))){
			$c = $this->getMainCurrencyName();
			$mp = $this->Core->getModuleParams($this->mod);

			$obj->userInfoTemplateParams[$this->mod] = array(
				'name' => $mp['text'],
				'params' => array(
					'balance' => array(
						'name' => '{Call:Lang:modules:billing:balans}',
						'value' => Library::HumanCurrency($clientData['balance']).' '.$c,
					),
					'all_payments' => array(
						'name' => '{Call:Lang:modules:billing:vsegoplatezh}',
						'value' => Library::HumanCurrency($clientData['all_payments']).' '.$c,
					),
					'all_payed_services' => array(
						'name' => '{Call:Lang:modules:billing:oplachenousl}',
						'value' => Library::HumanCurrency($clientData['all_payed_services']).' '.$c,
					)
				)
			);

			return array('clientId' => $clientData['id'], 'clientData' => $clientData);
		}	}

	public function __ava____map($obj){
		/*
			Карта сайта
				- Услуги (со ссылкой если есть ТП без группы)
					- Ссылки на таблицы тарифов по группам
						- Ссылки на тарифы отдельно
							- Ссылка заказать
		*/

		$return = array();
		$j = 0;

		foreach($this->getServices() as $i => $e){
			$subblock = array();
			foreach($this->getPackagesByGrp($i, '') as $i2 => $e2){				if($this->pkgIsOrdered($i, $i2)){
					$subblock[] = array('name' => $e2, 'link' => 'index.php?mod='.$this->mod.'&func=packages&service='.$i.'&pkg='.$i2);
					$subblock[] = array('name' => "Заказать $e2", 'link' => 'index.php?mod='.$this->mod.'&func=order&service='.$i.'&pkg_'.$i.'='.$i2);
				}
			}
			foreach($this->getPkgGroupNames($i) as $i1 => $e1){				if($this->getPackagesByGrp($i, $i1)){					$subblock2 = array();
					foreach($pkgs as $i2 => $e2){						if($this->pkgIsOrdered($i, $i2)){							$subblock2[] = array('name' => $e2, 'link' => 'index.php?mod='.$this->mod.'&func=packages&service='.$i.'&pkg='.$i2);
							$subblock2[] = array('name' => $e2, 'link' => 'index.php?mod='.$this->mod.'&func=order&service='.$i.'&pkg_'.$i.'='.$i2);						}					}

					if($subblock2) $subblock[] = array('name' => $e1, 'link' => 'index.php?mod='.$this->mod.'&func=packages&service='.$i.'&grp='.$i1, 'subblock' => $subblock2);				}
			}

			if($subblock) $return[$j] = array('name' => $e, 'link' => 'index.php?mod='.$this->mod.'&func=packages&service='.$i, 'subblock' => $subblock);
		}

		return $return;
	}

	public function __ava____registrationAdd($obj, $userId){		/*
			Добавляет пользователю клиента
		*/

		$this->addClient($userId);	}

	public function __ava__redirectToRegistration(){
		/*
			Перенаправляет пользователя на регистрацию
		*/

		if(!$clientId = $this->getClientId()){
			if(($aid = $this->Core->userIsAdmin()) && ($this->Core->getUserId() == $this->Core->DB->cellFetch(array('admins', 'user_id', "`id`='$aid'")))){
				$mMod = $this->Core->callModule('core');
				$callFunc = 'authAdminAsUser';
			}
			elseif($userId = $this->Core->getUserId()){
				if($this->Core->DB->cellFetch(array('admins', 'id', "`user_id`='{$userId}'"))){
					$this->back('main', '', 'main', 'Вы должны авторизоваться как администратор');
					return false;
				}

				$this->addClient($userId);
				$this->Core->User->authInModule($this->mod);
				$this->redirect2($this->getCallUrl());
				return false;
			}
			else{
				$mMod = $this->Core->callModule('main');
				$mMod->values['type_auth'] = 2;
				$mMod->values['in_module'][$this->mod] = 1;
				$callFunc = 'registration';

				if(!empty($this->values['service'])){
					if(!is_array($this->values['service'])) $this->values['service'] = array($this->values['service']);
					foreach($this->values['service'] as $e){
						if($mod = $this->getServiceMod($e)) $mMod->values['in_module'][$mod] = 1;
					}
				}
			}

			$mMod->values['redirect'] = $this->getCallUrl();
			$return = $mMod->callFunc($callFunc);
			$this->Core->contentMod2Mod($mMod, $this);
			return false;
		}

		return $clientId;
	}


	/********************************************************************************************************************************************************************

																	Счета и транзакции

	*********************************************************************************************************************************************************************/

	public function __ava__getOrderId($force = false){
		/*
			Возвращает id текущего заказа. Если его нет - создает новый
		*/

		if($force) unset($this->Core->User->tempParams['orderId']);
		elseif(!empty($this->Core->User->tempParams['orderId'])){
			$step = $this->DB->cellFetch(array('orders', 'step', "`id`='{$this->Core->User->tempParams['orderId']}' AND `client_id`='".$this->getClientId()."'"));
			if($step === '' || $step > 3 || $step < 0){				unset($this->Core->User->tempParams['orderId']);
			}
		}

		if(empty($this->Core->User->tempParams['orderId'])){
			$this->Core->User->tempParams['orderId'] = $this->DB->Ins(array('orders', array('client_id' => $this->getClientId(), 'date' => time())));
		}

		return $this->Core->User->tempParams['orderId'];
	}

	public function __ava__getComplexId($orderId, $complex = ''){		/*
			Возвращает ID комплексного заказа. При необходимости создает новый
		*/

		if((!$id = $this->DB->cellFetch(array('complex_orders', 'id', "`order_id`='{$orderId}'"))) && $complex){			$id = $this->DB->Ins(array('complex_orders', array('order_id' => $orderId, 'complex' => $complex)));
		}

		return $id;
	}

	public function __ava__newPaymentTransaction($sum, $currency = '', $oType = '', $oId = 0, $clientId = false){
		/*
			Возвращает ID платежной транзакции о номеру счета, либо создает новую
			sum = сумм в основной валюте
			type = order или complex
		*/

		if(!$oType || !($id = $this->DB->cellFetch(array('payment_transactions', 'id', "`object_type`='$oType' AND `object_id`='$oId'")))){			if(!$clientId) $clientId = $this->getClientId();
			if(!$id = $this->DB->Ins(array('payment_transactions', array('client_id' => $clientId, 'object_type' => $oType, 'object_id' => $oId, 'date' => time())))){
				throw new AVA_Exception('Не удалось создать платежную транзакцию');
			}
			$this->setPaymentTransactionSum($id, $sum, $currency);
		}

		return $id;
	}

	public function __ava__setPaymentTransactionSum($id, $sum, $currency = '', $payment = '', $payType = ''){		/*
			Устанавливает параметры платежной транзакции
		*/

		if(!$currency) $currency = $this->defaultCurrency();
		$this->DB->Upd(array('payment_transactions', array('sum' => $sum, 'currency' => $currency, 'payment' => $payment, 'payment_type' => $payType), "`id`='$id'"));
	}

	public function __ava__enrollTransaction($id, $sum, $currency = '', $payment = '', $payType = '', $date = false, $extra = array(), $foundation = ''){
		/*
			Получает ID транзакции и проводит ее
				- Получает сведения о транзакции
				- Проверяет не проводилась ли она
				- Зачисляет средства
				- Помечает транзакцию как проведенную
		*/

		$trData = $this->getTransactionParams($id);
		if($trData['status']){
			$this->setMessage('sum', '{Call:Lang:modules:billing:platezhnepri}', 'error');
			return false;
		}

		if($this->Core->getParam('autoConfirmReg', $this->mod)) $this->Core->confirmUser($this->getUserIdByClientId($trData['client_id']));
		$this->DB->Upd(array('payment_transactions', array('status' => 1), "`id`='$id'"));
		if(!$date) $date = time();

		if($this->upBalance($trData['client_id'], $sum, $currency, 'balance', $date, $id, $foundation)){
			$this->setPaymentTransactionSum($id, $sum, $currency, $payment, $payType);
			$this->DB->Upd(
				array(
					'payment_transactions',
					array('uniq' => empty($extra['uniqId']) ? '' : $extra['uniqId'], 'pay' => $date, 'vars' => empty($extra['payVars']) ? '' : $extra['payVars'], 'status' => 2),
					"`id`='$id' AND !`pay`"
				)
			);

			$this->setMessage('sum', '{Call:Lang:modules:billing:platezhprini}');
			return true;
		}
		else{
			$this->setMessage('sum', '{Call:Lang:modules:billing:platezhprini1}', 'error');
			return false;
		}
	}

	public function __ava__getOrderParams($id, $force = false){
		/*
			Возвращает параметры счета по ID
		*/

		if($force || !isset($this->orders[$id])) $this->orders[$id] = $this->DB->rowFetch(array('orders', '*', "`id`='$id'"));
		return $this->orders[$id];
	}

	public function __ava__getTransactionParams($id, $force = false){
		/*
			Возвращает параметры транзакции по ID
		*/

		if($force || !isset($this->transactions[$id])) $this->transactions[$id] = $this->DB->rowFetch(array('payment_transactions', '*', "`id`='$id'"));
		return $this->transactions[$id];
	}

	public function __ava__getComplexOrderParams($id, $force = false){
		/*
			Возвращает параметры транзакции по ID
		*/

		if($force || !isset($this->oComplex[$id])) $this->oComplex[$id] = $this->DB->rowFetch(array('complex_orders', '*', "`id`='$id'"));
		return $this->oComplex[$id];
	}

	public function __ava__getComplexOrderParamsByOrder($oId, $force = false){
		/*
			Возвращает параметры транзакции по ID
		*/

		if($force || !isset($this->oComplexByOrder[$oId])) $this->oComplexByOrder[$oId] = $this->DB->rowFetch(array('complex_orders', '*', "`order_id`='$oId'"));
		return $this->oComplexByOrder[$oId];
	}

	public function __ava__getTransactionByObjectId($id, $type){		/*
			ID транзакции по ID счета
		*/
		return $this->DB->cellFetch(array('payment_transactions', 'id', "`object_type`='$type' AND `object_id`='$id'"));	}

	public function __ava__getTransactionExtra($values = false){		/*
			Выделяет дополнительные параметры к транзакции
		*/

		if($values === false) $values = $this->values;
		return array('uniqId' => $values['uniq_id'], 'vars' => array());	}

	public function __ava__calcOrder($id){
		/*
			Расчитывает стоимость заказа с учетом всех скидок
			price хранится в основной на момент оформления валюте. При оплате пересчитывается в валюту оплаты
			возвращает total
		*/

		$oData = $this->getOrderParams($id);

		if($oData['step'] < 5){
			$os = $this->getOrderEntries($id);
			$sum = $discount = $total = 0;

			foreach($os as $i => $e){
				$pkgData = $this->serviceData($e['service'], $e['package']);
				list($os[$i]['modify_price'], $os[$i]['modify_install_price']) = $this->calcModifiedService($e['service'], $e['package'], $e['extra']['params3']);

				$os[$i]['price'] = $pkgData['price'];
				$os[$i]['price2'] = $pkgData['price2'];
				$os[$i]['prolong_price'] = $pkgData['prolong_price'];
				$os[$i]['install_price'] = $pkgData['install_price'];

				if(($e['entry_type'] != 'new') || ($e['pkgtest'] && !$e['term'] && empty($e['pay_test_install']))){
					$os[$i]['install_price'] = 0;
					$os[$i]['modify_install_price'] = 0;

					if($e['entry_type'] == 'prolong'){						$sData = $this->getOrderedService($e['order_service_id']);
						if(!$sData) throw new AVA_Exception("Не найдена продляемая услуга");
						$os[$i]['price'] = 0;
						$os[$i]['price2'] = 0;
						$os[$i]['prolong_price'] = $sData['price'];
						$os[$i]['modify_price'] = $sData['modify_price'];
					}
				}
			}

			foreach($os as $i => $e){
				$discounts = $this->getDiscounts($i, $e, $os);
				$os[$i]['sum'] = $this->getTotalPrice($e['price'], $e['price2'], $e['prolong_price'], $e['term'], $e['install_price'], $e['modify_price'], $e['modify_install_price'], $e['entry_type']);
				$os[$i]['total'] = $os[$i]['sum'] - $discounts['discountSum']['all'];

				$this->setOrderEntryPayParams($i, $e['price'], $e['price2'], $e['install_price'], $e['modify_price'], $e['modify_install_price'], $e['prolong_price'], $discounts['discountSum']['all'], $discounts, $os[$i]['sum'], $os[$i]['total']);
				$sum += $this->getSumInDefault($os[$i]['sum'], $e['pkgcurrency']);
				$discount += $this->getSumInDefault($discounts['discountSum']['all'], $e['pkgcurrency']);
				$total += $this->getSumInDefault($os[$i]['total'], $e['pkgcurrency']);
			}

			$this->DB->Upd(array('orders', array('sum' => $sum, 'discount' => $discount, 'total' => $total), "`id`='$id'"));
			$tId = $this->newPaymentTransaction($total, '', 'orders', $id);
			if($tId) $this->setPaymentTransactionSum($tId, $total);
		}
	}

	public function __ava__calcOrderEntry($eId, $part){
		/*
			Расчитывает базовую сумму с учетом всех скидок предназначенную для вычисления стоимости заказа. Расчет ведется на 1 базовую еденицу
		*/

		$eData = $this->getOrderEntry($eId);
		return $eData[$part];
	}

	public function __ava__getTotalPrice($price, $price2, $prolong, $term, $install, $modify, $modifyInstall, $style = 'new'){
		/*
			Общая цена за весь срок использования услуги
		*/

		if($style == 'prolong') $price = $price2 = $prolong;
		$term1 = $term >= 1 ? 1 : $term;
		$term2 = $term - $term1;

		$termPrice = (($price + $modify) * $term1) + (($price2 + $modify) * $term2);
		if($termPrice < 0) $termPrice = 0;

		$return = round($termPrice + $install + $modifyInstall, 2);
		if($return < 0) $return = 0;
		return $return;
	}

	public function __ava__showOrderedServices($id, $extraParams = array()){
		/*
			Создает список из всех заказанных услуг
		 */

		$filter = "";
		if(empty($extraParams['calcOnly'])) $filter = "AND (t2.step=1 OR t1.entry_type!='new')";
		if(!$entries = $this->getOrderEntries($id, $filter, 't1.id', true)) return '';

		$orderList = array();
		$curName = $this->getMainCurrencyName();
		$params = $this->getOrderParams($id);

		$params['curName'] = $curName;
		$params['agreeUrl'] = $this->path.'?mod='.$this->mod.'&func=orderAdd&id='.$id;
		$params['moreOrderUrl'] = $this->path.'?mod='.$this->mod.'&func=order';

		$params['cancelUrl'] = $this->path.'?mod='.$this->mod.'&func=orderDel&id='.$id;
		$params['newOrderUrl'] = $this->path.'?mod='.$this->mod.'&func=orderFinish&id='.$id;
		$params['modifyUrl'] = $this->path.'?mod='.$this->mod.'&func=orderModify&id='.$id;

		$params['allSum'] = $params['sum'].$curName;
		$params['allDiscount'] = $params['discount'].$curName;
		$params['allTotal'] = $params['total'].$curName;
		$params = Library::array_merge($params, $extraParams);

		foreach($entries as $id => $r){
			$m = $m1 = $m2 = $this->getPkgDescriptForm($r['service'], $r['package'], 'mpkg', '', '', $v, array($this->getConnectionCp($r['s_server'])));

			if($r['s_vars']){
				$r['modifyParamsBase'] = $this->getBasePkgDescript($this->getPkgBase($r['pkgvars']['params'], $m, ''), $id.'b');
				$r['modifyParamsExtra'] = $this->getBasePkgDescript($this->getPkgBase($r['s_vars'], $m1, ''), $id.'e');
				$r['modifyParamsTotal'] = $this->getBasePkgDescript($this->getPkgBase($this->sumParams($r['pkgvars']['params'], $r['s_vars'], $r['service']), $m2, ''), $id.'t');
			}

			$r['curName'] = $this->currencyName($r['pkgcurrency']);
			$r['price_mc'] = $this->getSumInDefault($r['price'], $r['pkgcurrency']);
			$r['price2_mc'] = $this->getSumInDefault($r['price2'], $r['pkgcurrency']);
			$r['prolong_price_mc'] = $this->getSumInDefault($r['prolong_price'], $r['pkgcurrency']);

			$r['install_price_mc'] = $this->getSumInDefault($r['install_price'], $r['pkgcurrency']);
			$r['modify_price_mc'] = $this->getSumInDefault($r['modify_price'], $r['pkgcurrency']);
			$r['modify_install_price_mc'] = $this->getSumInDefault($r['modify_install_price'], $r['pkgcurrency']);

			$r['discount_mc'] = $this->getSumInDefault($r['discount'], $r['pkgcurrency']);
			$r['sum'] = $this->getTotalPrice($r['price'], $r['price2'], $r['prolong_price'], $r['term'], $r['install_price'], $r['modify_price'], $r['modify_install_price'], $r['entry_type']);
			$r['sum_mc'] = $this->getTotalPrice($r['price_mc'], $r['price2_mc'], $r['prolong_price_mc'], $r['term'], $r['install_price_mc'], $r['modify_price_mc'], $r['modify_install_price_mc'], $r['entry_type']);

			$r['total'] = $r['sum'] - $r['discount'];
			$r['total_mc'] = $r['sum_mc'] - $r['discount_mc'];
			$orderList[] = $r;
		}

		return $this->getListText($this->newList('order', array('arr' => $orderList), $params, $this->Core->getModuleTemplatePath($this->mod).'order.tmpl'));
	}

	public function __ava__generateBill($id){
		/*
			Генерация формы для счета
		*/

		if(!$oData = $this->getOrderParams($id)) throw new AVA_NotFound_Exception('Заказ "'.$id.'" не найден');
		$tData = $this->getTransactionParams($this->getTransactionByObjectId($id, 'orders'));

		$t = time();
		$values = array('date' => $t, 'sum' => $tData['sum'], 'currency' => $tData['currency'], 'payment' => $tData['payment']);
		$form = $this->newForm('billData', 'billData2&id='.$id, array('caption' => '{Call:Lang:modules:billing:schet:'.Library::serialize(array($id)).'}'));

		$form->setParam('caption0', '{Call:Lang:modules:billing:obshchiedann}');
		$form->setParam('saveText', 'Выполнить заказ');
		$this->addFormBlock($form, 'balance', array(), array(), 'block0');

		$j = 1;
		foreach($this->getOrderEntries($id) as $i => $e){
			$this->setCreateServiceForm($form, $i, $i, $j, $t);
			$j ++;
		}

		$form->setValues($values);
		return $form;
	}

	public function __ava__setCreateServiceForm($form, $eId, $id, $j = 0, $t = false, $bName = false){		/*
			Создает форму для создания услуги по ID заказа
		*/
		$e = $this->getOrderEntry($eId, true);
		$sData = $this->serviceData($e['service'], $e['package']);
		if(!$t) $t = time();
		if(!$bName) $bName = 'block'.$j;

		$formData = array(
			'id' => $id,
			'baseTerm' => $sData['base_term'],
			'type' => $sData['service_type'],
			'currency' => $this->currencyName($sData['currency']),
			'servers' => $this->getConnections($sData['extension']),
			'fract' => true
		);

		foreach($e as $i1 => $e1){
			$form->setValue($i1.$id, $e[$i1]);
			$form->setValue('acc_'.$i1.$id, $e[$i1]);
		}

		if($sData['service_type'] != 'onetime'){
			if($e['entry_type'] == 'new') $form->setValue('paid_to'.$id, $t + Dates::term2sec($sData['base_term'], $e['term']) + Dates::term2sec($sData['test_term'], $e['test_term']));
			else{
				$s = $this->getOrderedService($e['order_service_id']);				$form->setValue('paid_to'.$id, $s['paid_to'] + Dates::term2sec($sData['base_term'], $e['term']));			}
		}

		$form->setParam('caption'.$j, $e['entry_caption']);
		$form->setValue('acc_auto'.$id, 1);

		if($e['entry_type'] == 'new'){
			$this->addFormBlock($form, 'add_service', $formData, array(), $bName);
			$this->setAccOrderMatrix($form, $eId, 'acc_', $id, $bName, $sData['server'], array('client_id' => $e['client_id']), true);
			$this->addFormBlock($form, array('acc_modify_capt'.$id => array('type' => 'caption', 'text' => 'Добавить')), $formData, array(), $bName);

			$this->orderConstructorForm2($form, $e['service'], $e['package'], 'mpkg_', $id, $bName, $j);
			$form->setValue('acc_server'.$id, $sData['server']);
			foreach($sData['vars'] as $i1 => $e1) $form->setValue('acc_'.$i1.$id, $e1);							//Параметры считающиеся предустановленными через настройки тарифа

			if(!empty($e['extra']['params1'])) foreach($e['extra']['params1'] as $i1 => $e1) if($e1) $form->setValue('acc_'.$i1.$id, $e1);
			if(!empty($e['extra']['params2'])) foreach($e['extra']['params2'] as $i1 => $e1) if($e1) $form->setValue('acc_'.$i1.$id, $e1);
			if(!empty($e['extra']['params3'])) foreach($e['extra']['params3'] as $i1 => $e1) if($e1) $form->setValue('mpkg_'.$i1.$id, $e1);
		}
		else{
			$this->addFormBlock($form, 'prolong_service', $formData, array(), $bName);
			$this->setAccProlongMatrix($form, $eId, 'acc_', $id, $bName, array(), true);
		}

		$this->callCoMods('__billing_setCreateServiceForm', array('fObj' => $form, 'id' => $eId), $j);
	}


	/********************************************************************************************************************************************************************

											Функции ответственные непосредственно за генерацию новой услуги и ее продление

	*********************************************************************************************************************************************************************/

	public function __ava__enrollOrder($id){		/*
			Проводит счет используя данные из прегенерированной формы
		*/

		$form = $this->generateBill($id);
		return $this->enroll($id, 'orders', $form->getValues());
	}

	public function __ava__enrollComplex($id){
		/*
			Принимает платеж за комплекс.
			Вначале снимает деньги за весь комплекс, затем выставляет всем услугам нулевой расчетный ценник, затем проводит заказ, как проводку счета
		*/

		$cData = $this->getComplexOrderParams($id);
		$cParams = $this->getComplexParams($cData['complex']);
		$oData = $this->getOrderParams($cData['order_id']);
		$tData = $this->getTransactionParams($this->getTransactionByObjectId($cData['order_id'], 'orders'), true);

		if($tData['payment_type'] == 's'){			$nParams = $this->getSmsNumberParams($cParams['vars']['smsPays'][$tData['payment']]);
			$sum = $nParams['sum'];
			$cur = $nParams['currency'];
		}
		else{			$sum = $cParams['vars']['pays'][$tData['payment']]['sum'];
			$cur = $this->getCurrencyByPayment($tData['payment']);		}

		if(!$sum){			$this->setMessage('sum', 'Этот комплекс услуг нельзя оплатить данным способом оплаты', 'error');
			return false;		}

		if(!$this->checkBalance($oData['client_id'], $sum, $cur)){
			$this->setMessage('sum', 'Услуги не созданы, т.к. недостаточно средств на балансе', 'error');
			return false;
		}
		else{
			$this->upBalance($oData['client_id'], -$sum, $cur, 'service', time(), $id, 0, 'Оплата комплекса услуг "'.$cParams['text'].'"', 'complex_orders');
			foreach($this->getOrderEntries($cData['order_id']) as $i => $e) $this->setOrderEntryPayParams($i);
			$form = $this->generateBill($cData['order_id']);
			return $this->enroll($cData['order_id'], 'orders', $form->getValues());
		}
	}

	public function __ava__enroll($id, $type, $values){		/*
			Проводит обработку записи. $type может быть orders, complex, modify_service_orders, delete_service_orders
		*/

		$t = time();
		$this->clearMessages();
		$result = false;

		switch($type){			case 'orders':
				$oData = $this->getOrderParams($id);
				$clientId = $oData['client_id'];

				if($entries = $this->checkOrder($id, $t, $values)){					$result = $this->setOrder($entries, $values);
					$this->DB->Upd(array('orders', array('step' => 6), "`id`='$id'"));
				}
				break;

			case 'modify_service_orders':
				$i;
				break;

			case 'delete_service_orders':
				break;

			default: throw new AVA_Exception('Неопределенный тип записи для проводки');
		}

		return $this->setOperation($id, $clientId, $type, $id, $result);	}

	public function __ava__checkOrder($id, $t = false, $values = false){
		/*
			Проверяет параметры заказа на корректность. Если что-то неправильно, возвращает false. Если все нормально, возвращает список записей для обновления
		*/

		$oData = $this->getOrderParams($id);
		if(!$t) $t = time();
		if($values === false) $values = $this->values;

		if($oData['step'] > 4){
			$this->setMessage('date', '{Call:Lang:modules:billing:vynemozhetep}', 'error');
			return false;
		}
		elseif($oData['step'] < 3){
			$this->setMessage('date', '{Call:Lang:modules:billing:vynemozhetep1}', 'error');
			return false;
		}

		$this->DB->Upd(array('orders', array('step' => 5), "`id`='$id'"));
		$return = array();

		foreach($this->getOrderEntries($id) as $i => $e){
			$return[$i] = $this->checkEntry($i, $i, $t, $values);
		}

		return $return;
	}

	public function __ava__checkEntry($eId, $id = '', $t = false, $values = false){
		/*
			Проверяет данную конкретную запись. Возвращает набор данных для включения в params2
		*/

		if(!$t) $t = time();
		if($values === false) $values = $this->values;

		$e = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($e['service'], $e['package']);

		if($e['status'] > 1){
			$this->setMessage('price'.$id, 'Вы не можете выполнить операцию "'.($e['entry_type'] == 'new' ? 'создания' : 'продления').'" услуги "'.$pkgData['service_textname'].'" по тарифу "'.$pkgData['text'].'" для "'.$e['ident'].'", т.к. она уже выполнялась ранее', 'error');
			return false;
		}
		elseif($e['status'] < 1){
			$this->setMessage('price'.$id, 'Вы не можете выполнить операцию "'.($e['entry_type'] == 'new' ? 'создания' : 'продления').'" услуги "'.$pkgData['service_textname'].'" по тарифу "'.$pkgData['text'].'" для "'.$e['ident'].'", т.к. ее заказ еще не закончен', 'error');
			return false;
		}
		elseif($e['status'] < 0){
			$this->setMessage('price'.$id, 'Вы не можете выполнить операцию "'.($e['entry_type'] == 'new' ? 'создания' : 'продления').'" услуги "'.$pkgData['service_textname'].'" по тарифу "'.$pkgData['text'].'" для "'.$e['ident'].'", т.к. она была удалена', 'error');
			return false;
		}

		$this->setOrderEntryPayParams(
			$eId,
			isset($values['price'.$id]) ? $values['price'.$id] : $e['price'],
			isset($values['price2'.$id]) ? $values['price2'.$id] : $e['price2'],
			isset($values['install_price'.$id]) ? $values['install_price'.$id] : $e['install_price'],
			isset($values['modify_price'.$id]) ? $values['modify_price'.$id] : $e['modify_price'],
			isset($values['modify_install_price'.$id]) ? $values['modify_install_price'.$id] : $e['modify_install_price'],
			$values['prolong_price'.$id],
			$values['discount'.$id],
			$e['discounts'],
			$values['total'.$id] - $values['discount'.$id],
			$values['total'.$id]
		);

		if($e['entry_type'] == 'new'){			$this->checkAccOrderMatrix($eId, 'acc_', $id, array(), $values, true);

			$extra = array(
				'params1' => $this->getServiceCreateParams($eId, 'acc_', $id, array(), $values, true),
				'params3' => !empty($values['modified'.$id]) ? $this->getPkgParams($e['service'], 'mpkg', 'mpkg_', $id, array($this->getConnectionCp($pkgData['server'])), $_, $values) : array(),
			);

			$this->setOrderEntryUserParams(
				$eId,
				isset($values['term'.$id]) ? $values['term'.$id] : $e['term'],
				$values['acc_ident'.$id],
				isset($values['auto_prolong'.$id]) ? $values['auto_prolong'.$id] : $e['auto_prolong'],
				isset($values['auto_prolong_fract'.$id]) ? $values['auto_prolong_fract'.$id] : $e['auto_prolong_fract'],
				isset($values['acc_promo_code'.$id]) ? $values['acc_promo_code'.$id] : $e['promo_code'],
				$extra,
				isset($values['test_term'.$id]) ? $values['test_term'.$id] : $e['test_term']
			);
		}
		else{
			$this->checkAccUserProlongMatrix($eId, 'acc_', $id, array(), $values, true);
			$this->setServiceProlongEntry($eId, 'acc_', $id, array(), $values, true);
		}

		$this->setOrderEntryCreateParams($eId, isset($values['paid_to'.$id]) ? $values['paid_to'.$id] : $t, $t);
	}

	public function __ava__setOrder($entries, $values = false){
		/*
			Принимает данные заказа как список параметров, полученных из checkOrder
		*/

		if($values === false) $values = $this->values;
		$return = array();
		foreach($entries as $i => $e) $return[$i] = $this->setEntry($i, $i, $values);
		return $return;
	}

	public function __ava__setEntry($eId, $id = '', $values = false){
		/*
			Проводит запись из заказа
		*/

		$e = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($e['service'], $e['package']);
		$say = $e['entry_type'] == 'new' ? 'создана' : 'продлена';
		if($values === false) $values = $this->values;

		if(!$this->checkBalance($e['client_id'], $e['total'], $pkgData['currency'])){
			$this->setMessage('price'.$eId, 'Услуга "'.$pkgData['service_textname'].'" по тарифу "'.$pkgData['text'].'" для "'.$e['ident'].'" не '.$say.', т.к. недостаточно средств на балансе', 'error');
			return false;
		}

		$this->callCoMods('__billing_setEntry', array('values' => $values, 'prefix' => '', 'postfix' => $id, 'id' => $id));

		if(!$pkgData['server'] || empty($values['acc_auto'.$id])){
			$this->setMessage('price'.$eId, '{Call:Lang:modules:billing:uslugapotari2:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $e['ident'], $say)).'}', 'error');
		}
		elseif($this->callServiceObj($e['entry_type'] == 'new' ? 'addAcc' : 'prolongAcc', $e['service'], array('id' => $eId, 'server' => $pkgData['server']))){
			$this->setMessage('price'.$eId, '{Call:Lang:modules:billing:uslugapotari3:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $e['ident'], $this->path, $this->mod, $this->getConnectionResultId($pkgData['server']), $say)).'}');
		}
		else{
			$this->setMessage('price'.$eId, '{Call:Lang:modules:billing:uslugapotari4:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $e['ident'], $this->path, $this->mod, $this->getConnectionResultId($pkgData['server']), $say)).'}', 'error');
			if($e['entry_type'] == 'new') $this->sendCreateServiceMails($eId, false);
			else $this->sendProlongServiceMails($eId, false);
			return false;
		}

		if($values === false) $values = $this->values;
		if($e['entry_type'] == 'new') return $this->createService($eId, $values['created'.$id]);
		else return $this->prolongService($eId, $e['enrolled']);
	}

	private function createService($eId, $created){
		/*
			Создает новую услугу. Все данные получает самостоятельно используя $eId (ID записи в счете)
			$byCron указывает что услуга создается по расписанию
		*/

		$eData = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);

		$sId = $this->addNewService($eId, $created);
		$this->setMessage('ident'.$eId, $this->getOrderedServiceInfo($eId));
		$this->payServiceByOrder($eId, $created, $eData['paid_to'], $eData['total']);

		$this->setMessage('price'.$eId, '{Call:Lang:modules:billing:vnesenysvede1:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $eData['ident'])).'}');
		$this->serviceHistory($sId, 'add', '{Call:Lang:modules:billing:uslugarazmes}');
		$this->sendCreateServiceMails($eId);

		return $sId;
	}

	private function prolongService($eId, $payed){
		/*
			Продление услуги
		*/

		$eData = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		$this->payServiceByOrder($eId, $payed, $eData['paid_to'], $eData['total']);

		$this->setMessage('price'.$eId, '{Call:Lang:modules:billing:vnesenysvede1:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $eData['ident'])).'}');
		$this->serviceHistory($eData['order_service_id'], 'prolong', '{Call:Lang:modules:billing:uslugarazmes}');
		$this->sendProlongServiceMails($eId);

		return true;
	}

	private function sendCreateServiceMails($eId, $result = true){		/*
			Отправляет письма о создании услуги
		*/

		$eData = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		$userEml = $this->getClientEml($eData['client_id']);
		$admin = false;

		$mailParams = array(
			'eData' => $eData,
			'userData' => $this->getUserByClientId($eData['client_id']),
			'pkgData' => $pkgData,
			'connectData' => $pkgData['server'] ? $this->getConnectionParams($pkgData['server']) : array(),
			'log' => $this->messages['price'.$eId]
		);

		if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal'){			if($result){				$userTmpl = $pkgData['notify_rights']['mail_tmpl_new'];
				$adminTmpl = $pkgData['notify_rights']['mail_tmpl_admin_new'];
				if($pkgData['notify_rights']['notify_admin_new']) $admin = $pkgData['notify_rights']['new_rcpt_admin'];
				if(!$pkgData['notify_rights']['notify_new']) $userEml = false;
			}
			elseif($pkgData['notify_rights']['notify_fail_admin_new']){				$adminTmpl = $pkgData['notify_rights']['mail_tmpl_admin_new_fail'];
				$admin = $pkgData['notify_rights']['new_rcpt_admin'];
			}
		}
		else{			if($result){
				$userTmpl = 'newService';
				$adminTmpl = 'newServiceAdmin';
				if($this->Core->getParam('addAccsSuccessMail', $this->mod)) $admin = $this->Core->getParam('notifyBillAdmin', $this->mod);
			}
			elseif($this->Core->getParam('addAccsFailMail', $this->mod)){
				$adminTmpl = 'newServiceFailAdmin';
				$admin = $this->Core->getParam('notifyBillAdmin', $this->mod);
			}
		}

		if($result && !empty($userEml)) $this->mail($userEml, $this->getTmplParams($userTmpl), $mailParams);
		if($admin !== false) $this->mail($this->Core->getAdminEml($admin ? $admin : $this->Core->getRoot()), $this->getTmplParams($adminTmpl), $mailParams);
	}

	private function sendProlongServiceMails($eId, $result = true){		/*
			Отправляет письма о продлении услуги
		*/

		$eData = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		$userEml = $this->getClientEml($eData['client_id']);
		$admin = false;

		$mailParams = array(
			'eData' => $eData,
			'sData' => $this->getOrderedService($eData['order_service_id']),
			'userData' => $this->getUserByClientId($eData['client_id']),
			'pkgData' => $pkgData,
			'log' => $this->messages['price'.$eId]
		);

		if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal'){
			if($result){
				$userTmpl = $pkgData['notify_rights']['mail_tmpl_prolong'];
				$adminTmpl = $pkgData['notify_rights']['mail_tmpl_prolong_admin'];
				if($pkgData['notify_rights']['notify_admin_prolong']) $admin = $pkgData['notify_rights']['prolong_rcpt_admin'];
				if(!$pkgData['notify_rights']['notify_prolong']) $userEml = false;
			}
			elseif($pkgData['notify_rights']['notify_fail_admin_prolong']){
				$adminTmpl = $pkgData['notify_rights']['mail_tmpl_prolong_admin_fail'];
				$admin = $pkgData['notify_rights']['prolong_rcpt_admin'];
			}
		}
		else{
			if($result){
				$userTmpl = 'prolongService';
				$adminTmpl = 'prolongServiceAdmin';
				if($this->Core->getParam('prolongServiceAdminMail', $this->mod)) $admin = $this->Core->getParam('notifyBillAdmin', $this->mod);
			}
			elseif($this->Core->getParam('prolongServiceFailAdminMail', $this->mod)){
				$adminTmpl = 'prolongServiceFailAdmin';
				$admin = $this->Core->getParam('notifyBillAdmin', $this->mod);
			}
		}

		if($result && !empty($userEml)) $this->mail($userEml, $this->getTmplParams($userTmpl), $mailParams);
		if($admin !== false) $this->mail($this->Core->getAdminEml($admin ? $admin : $this->Core->getRoot()), $this->getTmplParams($adminTmpl), $mailParams);
	}

	private function sendDeleteServiceMails($dId){
		/*
			Отправляет письма о продлении услуги
		*/

		$dData = $this->getDeleteOrder($dId, true);
		$sData = $this->getOrderedService($dData['service_order_id']);
		$pkgData = $this->serviceData($sData['service'], $sData['package']);

		$mailParams = array(
			'dData' => $dData,
			'sData' => $sData,
			'userData' => $this->getUserByClientId($sData['client_id']),
			'pkgData' => $pkgData,
			'log' => $this->messages['total']
		);

		$userEml = $this->getClientEml($sData['client_id']);
		$admin = false;

		if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal'){
			$userTmpl = $pkgData['notify_rights']['mail_tmpl_delete'];
			$adminTmpl = $pkgData['notify_rights']['mail_tmpl_delete_admin'];
			if($pkgData['notify_rights']['notify_admin_delete']) $admin = $pkgData['notify_rights']['delete_rcpt_admin'];
			if(!$pkgData['notify_rights']['notify_delete']) $userEml = false;
		}
		else{
			$userTmpl = 'deleteService';
			$adminTmpl = 'deleteServiceAdmin';
			if($this->Core->getParam('deleteServiceAdminMail', $this->mod)) $admin = $this->Core->getParam('notifyBillAdmin', $this->mod);
		}

		$this->mail($userEml, $this->getTmplParams($userTmpl), $mailParams);
		if($admin !== false) $this->mail($this->Core->getAdminEml($admin ? $admin : $this->Core->getRoot()), $this->getTmplParams($adminTmpl), $mailParams);
	}

	public function __ava__getOrderedServiceInfo($eId){
		/*
			Выдает инфу для заказанной услуги
		*/

		$params['eData'] = $this->getOrderEntry($eId, true);
		$params['pkgData'] = $this->serviceData($params['eData']['service'], $params['eData']['package']);
		if($params['pkgData']['server']) $params['connectData'] = $this->getConnectionParams($params['pkgData']['server']);

		if(!$return = $this->callServiceObj('getOrderedServiceInfo', $params['eData']['service'], $params)){
			$return = $this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'new_service.tmpl', 'result', $this, $params);		}
		return $return;
	}

	public function __ava__addNewService($eId, $created){		/*
			Создает новую услугу
		*/

		$e = $this->getOrderEntry($eId, true);
		if(!empty($e['order_service_id'])) return $e['order_service_id'];
		else{
			$pkgData = $this->serviceData($e['service'], $e['package']);

			$e['extra']['params2']['service_order_id'] = $this->DB->Ins(
				array(
					'order_services',
					array(
						'client_id' => $e['client_id'],
						'service' => $e['service'],
						'package' => $e['package'],
						'ident' => $e['ident'],
						'server' => $pkgData['server'],
						'created' => $created,
						'vars' => $e['extra']['params3'],
						'extra' => $e['extra']['params1'],
						'date' => time(),
						'price' => $e['prolong_price'],
						'modify_price' => $e['modify_price'],
						'ind_price' => $e['ind_price'],
						'auto_prolong' => $e['auto_prolong'],
						'auto_prolong_fract' => $e['auto_prolong_fract'],
						'step' => 1
					)
				)
			);

			$this->DB->Ins(array('orders_'.$e['service'], $e['extra']['params2']));
			$this->DB->Upd(array('order_entries', array('order_service_id' => $e['extra']['params2']['service_order_id'], 'status' => 2), "`id`='$eId'"));
		}

		return $e['extra']['params2']['service_order_id'];
	}

	public function __ava__setServiceProlongParams($sId, $autoProlong, $autoProlongFract){
		/*
			Устанавливает параметры услуги
		*/

		$this->DB->Upd(array('order_services', array('auto_prolong' => $autoProlong, 'auto_prolong_fract' => $autoProlongFract), "`id`='$sId'"));
	}

	public function __ava__payServiceByOrder($eId, $date, $payTo, $sum){
		/*
			Принимает оплату за услугу основываясь на записи в счете ($eId)
		*/

		$eData = $this->getOrderEntry($eId, true, 2);
		$sData = $this->getOrderedService($eData['order_service_id'], true);
		$pkgData = $this->serviceData($sData['service'], $sData['package']);

		$pId = $sum > 0 ? $this->upBalance($sData['client_id'], -$sum, $pkgData['currency'], 'service', $date, $eId, $eData['order_service_id']) : 0;
		return $this->changeServiceTerm($eData['order_service_id'], $payTo, $date, 'order_entries', $eId, $pId);
	}

	public function __ava__changeServiceTerm($sId, $to, $date, $objectType, $objectId, $pId = 0){
		/*
			Изменяет срок действия услуги
		*/

		$sData = $this->getOrderedService($sId, true);
		$this->DB->Upd(array('order_services', array('paid_to' => $to), "`id`='$sId'"));

		if($sData['step'] == 0 && $sData['suspend_reason'] == 'term' && $to > time()){			$list[$this->addUnsuspendOrder($sId, 'term')] = $this->getActionServiceValues($sId, array('auto'.$sId => 1, 'notify'.$sId => 1));
			$this->setUnsuspendServiceList($sData['service'], $list);
		}

		return $this->DB->Ins(array('order_service_terms', array('service_id' => $sId, 'pay_id' => $pId, 'object_type' => $objectType, 'object_id' => $objectId, 'date' => $date, 'real_date' => time(), 'old_term' => $sData['paid_to'] ? $sData['paid_to'] : $sData['created'], 'new_term' => $to)));
	}

	public function __ava__getTermRemainPeriods($sId, $t = false){
		/*
			Возвращает сколько осталось по периодам: цену, цену со скидкой и примененные скидки.
			Если была модификация ранее, возвращаются только данные по этой модификации
		*/

		if($t === false) $t = time();

		$sData = $this->getOrderedService($sId, true);
		$pkgData = $this->serviceData($sData['service'], $sData['package']);
		$return = array();

		foreach($this->DB->columnFetch(array('order_service_terms', '*', 'id', "`service_id`='$sId' AND `new_term`>=$t AND `old_term`<={$sData['paid_to']} AND `object_type`='order_entries'", "`real_date`")) as $i => $e){
			$eData = $this->getOrderEntry($e['object_id']);

			if($eData['entry_type'] == 'new'){
				$return['test'][$e['object_id']] = array('from' => $e['old_term'], 'to' => $e['old_term'] + dates::term2sec($pkgData['test_term'], $eData['test_term']));
				$return['price'][$e['object_id']] = array('from' => $return['test'][$e['object_id']]['to'], 'to' => $return['test'][$e['object_id']]['to'] + dates::term2sec($pkgData['base_term'], 1));
				$return['price2'][$e['object_id']] = array('from' => $return['price'][$e['object_id']]['to'], 'to' => $e['new_term']);
			}
			elseif($eData['entry_type'] == 'prolong'){
				$return['prolong_price'][$e['object_id']] = array('from' => $e['old_term'], 'to' => $e['new_term']);
			}
		}

		foreach($return as $i => $e){			foreach($e as $i1 => $e1){				if($e1['to'] <= $t || $e1['from'] >= $sData['paid_to']) unset($return[$i][$i1]);
				else{
					if($e1['to'] > $sData['paid_to']) $return[$i][$i1]['to'] = $sData['paid_to'];
					if($e1['from'] < $t) $return[$i][$i1]['from'] = $t;
				}			}		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																		Сведения о заказах модификации услуг

	*********************************************************************************************************************************************************************/

	public function __ava__getServiceMainModifyData($id, $force = false){
		/*
			Основная инфа о модификации (пакет)
		*/

		if(empty($this->modifyMainOrders[$id]) || $force){			$this->modifyMainOrders[$id] = $this->DB->rowFetch(array('modify_service_main_orders', '*', "`id`='$id'"));
			$this->modifyMainOrders[$id]['vars'] = Library::unserialize($this->modifyMainOrders[$id]['vars']);		}
		return $this->modifyMainOrders[$id];
	}

	public function __ava__getServiceMainModifyOrders($id, $force = false){
		/*
			Основная инфа о модификации (пакет)
		*/

		if(empty($this->modifyOrdersByMainOrder[$id]) || $force){
			$this->modifyOrdersByMainOrder[$id] = array();
			foreach($this->DB->columnFetch($this->getServiceModifyReq()." WHERE t1.main_id='$id'") as $i => $e){				$this->modifyOrders[$e['id']] = $this->extractServiceModifyData($e);
				$this->modifyOrdersByMainOrder[$id][$e['id']] = $e['id'];			}
		}

		return $this->modifyOrdersByMainOrder[$id];
	}

	public function __ava__getServiceModifyData($id, $force = false){
		/*
			Возвращает параметры запроса на модификацию
		*/

		if(empty($this->modifyOrders[$id]) || $force){
			$this->modifyOrders[$id] = $this->extractServiceModifyData($this->DB->rowFetch($this->getServiceModifyReq()." WHERE t1.id='$id'"));
		}

		return $this->modifyOrders[$id];
	}

	private function getServiceModifyReq(){		$p = $this->DB->getPrefix();
		$t1 = $p.'modify_service_orders';
		$t2 = $p.'modify_service_main_orders';
		$t3 = $p.'order_services';
		$t4 = $p.'order_packages';

		return "SELECT t1.*, t2.init, t2.service, t2.pkg, t2.vars, t3.client_id, t3.ident, t3.server, t3.ind_price, t3.auto_prolong, t3.auto_prolong_fract, t4.currency ".
			"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.main_id=t2.id LEFT JOIN $t3 AS t3 ON t1.service_order_id=t3.id LEFT JOIN $t4 AS t4 ON t2.pkg=t4.name AND t2.service=t4.service";
	}

	private function extractServiceModifyData($arr){		$arr['old_vars'] = Library::unserialize($arr['old_vars']);
		$arr['vars'] = Library::unserialize($arr['vars']);
		$arr['extra'] = Library::unserialize($arr['extra']);
		return $arr;
	}


	/********************************************************************************************************************************************************************

																		Формы заказов на модификацию

	*********************************************************************************************************************************************************************/

	protected function __ava__setAccModifyMatrix($fObj, $service, $pkg = '', $bName = 'form', $inAdmin = false){
		/*
			Форма модификации тарифа. Шаг первый.
		*/

		if(!$pkgs = $this->canUseChangePkgsList($service, $pkg, $inAdmin)) throw new AVA_Exception('{Call:Lang:modules:billing:vynemozhetes}');
		$this->addFormBlock($fObj, 'mypkg_change', array('pkgs' => $pkgs), array(), $bName);
		$this->callServiceObj('setAccModifyMatrix', $service, array('fObj' => $fObj, 'service' => $service, 'pkg' => $pkg));
		if($inAdmin) $this->callServiceObj('setAccModifyMatrixAdmin', $service, array('fObj' => $fObj, 'service' => $service, 'pkg' => $pkg));
	}

	protected function __ava__setAccModifyMatrix2($fObj, $mmId, $bName = 'form', $inAdmin = false){
		/*
			Форма модификации тарифа. Шаг второй. Форма выбора инд. настроек ТП
		*/

		$mmData = $this->getServiceMainModifyData($mmId);
		$pkgData = $this->serviceData($mmData['service'], $mmData['pkg']);
		$this->callServiceObj('setAccModifyMatrix', $mmData['service'], array('fObj' => $fObj, 'id' => $mmId));

		if($inAdmin || !empty($pkgData['rights']['modify'])) $this->orderConstructorForm2($fObj, $mmData['service'], $mmData['pkg'], '', '', $bName);
		if($inAdmin) $this->callServiceObj('setAccModifyMatrixAdmin2', $mmData['service'], array('fObj' => $fObj, 'id' => $mmId));
	}

	protected function __ava__checkAccModifyMatrix($service, $pkg = '', $inAdmin = false){
		/*
			Форма модификации тарифа. Шаг первый.
		*/

		$this->callServiceObj('checkAccModifyMatrix', $service, array('pkg' => $pkg));
	}

	protected function __ava__checkAccModifyMatrix2($id, $inAdmin = false){
		/*
			Форма модификации тарифа. Шаг первый.
		*/

		$mmData = $this->getServiceMainModifyData($id);
		$this->callServiceObj('checkAccModifyMatrix2', $mmData['service'], array('id' => $id));
	}

	public function __ava__showModifyConfirmForm($mId){
		/*
			Отображает расчет модификации услуги
		*/

		$this->DB->Upd(array('modify_service_orders', array('status' => 2), "`id`='$mId' AND `status`<2 AND `status`>=0"));
		$mParams = $this->getServiceModifyData($mId);
		$mParams['oldService'] = $this->getOrderedService($mParams['service_order_id']);

		$mParams['oPkgData'] = $this->serviceData($mParams['oldService']['service'], $mParams['oldService']['package']);
		$mParams['nPkgData'] = $this->serviceData($mParams['service'], $mParams['pkg']);
		$mParams['oPkgData']['curName'] = $this->currencyName($mParams['oPkgData']['currency']);
		$mParams['nPkgData']['curName'] = $this->currencyName($mParams['nPkgData']['currency']);

		$mParams['cancelUrl'] = 'index.php?mod='.$this->mod.'&func=cancelModify&id='.$mId;
		if($this->checkBalance($mParams['oldService']['client_id'], $mParams['total'])) $mParams['okUrl'] = 'index.php?mod='.$this->mod.'&func=modifyByBalance&id='.$mId;
		return $this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl', 'change', $this, $mParams, 'cover');
	}

	public function __ava__generateModifyOrderSimpleForm($id, $recalc = true){
		/*
			Создает форму для модификации состоящей только из одной услуги. Передается ID из modify_service_orders
		*/

		$mData = $this->getServiceModifyData($id);
		$form = $this->newForm('modifyForm', 'modifyEnd&id='.$id, array('caption' => 'Заявка на модификацию №'.$id));
		$this->generateModifyOrderFormEntry($form, $id, '', 'form', $recalc);
		$this->generateModifyOrderFormCommon($form, $mData['main_id']);
		return $form;
	}

	public function __ava__generateModifyOrderMultiForm($id, $recalc = true){
		/*
			Создает форму для модификации состоящей из всех услуг заказа. Передается ID из modify_service_orders
		*/

		$form = $this->newForm('modifyServices4', 'modifyServices4', array('caption' => 'Смена тарифного плана. Расценки.'));
		$mmData = $this->getServiceMainModifyData($id, true);

		$this->generateModifyOrderFormCommon($form, $id, 'block0');
		$form->setParam('caption0', 'Общие настройки');
		$j = 1;

		foreach($this->getServiceMainModifyOrders($id) as $e){			$mData = $this->getServiceModifyData($e);
			$this->generateModifyOrderFormEntry($form, $e, $e, 'block'.$j);
			$form->setParam('caption'.$j, $mData['ident']);
			$j ++;		}

		return $form;
	}

	public function __ava__generateModifyOrderFormEntry($form, $id, $postfix = '', $block = 'form', $recalc = true){
		/*
			Создает запись в форму для модификации
		*/

		$mData = $this->getServiceModifyData($id);
		$serviceData = $this->serviceData($mData['service']);
		$this->addFormBlock($form, 'modify_service', array('id' => $postfix, 'servers' => $this->getConnections($serviceData['extension'])), array(), $block);

		if($recalc && ($mData['status'] < 5 && $mData['status'] > 1)){
			$this->prepareServiceModifyPayData($id);
			$mData = $this->getServiceModifyData($id, true);
		}

		foreach($mData as $i => $e) if(isset($form->matrix[$i.$postfix])) $values[$i.$postfix] = $e;
		if(!$values['modified'.$postfix]) $values['modified'.$postfix] = time();
		$form->setValues($values);
	}

	public function __ava__generateModifyOrderFormCommon($form, $id, $block = 'form'){
		/*
			Общие данные (для всех изменяемых услуг)
		*/

		$mData = $this->getServiceMainModifyData($id);
		$this->addFormBlock($form, array('extra_caption' => array('type' => 'caption', 'text' => 'Дополнительные параметры')), array(), array(), $block);
		$this->setAccModifyMatrix($form, $mData['service'], '', $block, true);

		$this->addFormBlock($form, array('extra_caption2' => array('type' => 'caption', 'text' => 'Параметры модификации пакета')), array(), array(), $block);
		$this->setAccModifyMatrix2($form, $id, $block, true);
		$form->setValues(Library::array_merge($mData['vars']['params1'], $mData['vars']['params3']));
		$form->setValue('pkg', $mData['pkg']);
	}


	/********************************************************************************************************************************************************************

																		Новые заказы на модификацию

	*********************************************************************************************************************************************************************/

	public function __ava__newServiceMainModify($service, $pkg, $sIds = array(), $init = 'u', $t = false){
		/*
			Создает новую основную запись о модификации услуги
		*/

		if($t === false) $t = time();
		$return = $this->DB->Ins(array('modify_service_main_orders', array('service' => $service, 'pkg' => $pkg, 'init' => $init, 'date' => $t)));

		if($sIds && !is_array($sIds)) $sIds = array($sIds => true);
		foreach($sIds as $i => $e) $this->newServiceModify($return, $i);
		return $return;
	}

	public function __ava__newServiceModify($mmId, $sId){
		/*
			Создает новую основную запись о модификации услуги
		*/

		return $this->DB->Ins(array('modify_service_orders', array('service_order_id' => $sId, 'main_id' => $mmId)));
	}

	public function __ava__setServiceModifyExtraParams($mmId, $values = false, $inAdmin = false){
		/*
			Дополнительные параметры сформированные в формах модификации, общие для всех модификаций
		*/

		if($values === false) $values = $this->values;

		$mmData = $this->getServiceMainModifyData($mmId, true);
		$pkgData = $this->serviceData($mmData['service'], $mmData['pkg']);

		$fObj = new Form('modifyParams', '', '', '');
		$this->setAccModifyMatrix($fObj, $mmData['service'], $mmData['pkg'], 'form', $inAdmin);
		$this->setAccModifyMatrix2($fObj, $mmId, 'form', $inAdmin);

		$extra['params1'] = $this->getGeneratedFormValuesByMatrix($fObj->getMatrix(), $values);
		$extra['params3'] = empty($values['modify']) ? array() : $this->getPkgParams($mmData['service'], 'mpkg', '', '', array($this->getConnectionCp($pkgData['server'])));
		foreach($extra['params3'] as $i => $e) unset($extra['params1'][$i]);
		unset($extra['params1']['pkg'], $extra['params1']['modified']);

		$this->DB->Upd(array('modify_service_main_orders', array('vars' => $extra), "`id`='$mmId'"));
		$this->DB->Upd(array('modify_service_orders', array('status' => 1), "`main_id`='$mmId' AND `status`<1"));

		foreach($this->getServiceMainModifyOrders($mmId) as $e){			$this->prepareServiceModifyBasePrice($e);
			$this->prepareServiceModifyPayData($e, false, $inAdmin);		}
	}

	public function __ava__prepareServiceModifyBasePrice($mId){
		/*
			Устанавливает базовые цены нового ТП
		*/

		$mData = $this->getServiceModifyData($mId, true);
		$pkgData = $this->serviceData($mData['service'], $mData['pkg']);
		$sData = $this->getOrderedService($mData['service_order_id']);

		//Расчет установки модификации
		switch($pkgData['service_modify_install_type']){
			case 'full2': list($mp, $mip) = $this->calcModifiedService($mData['service'], $mData['pkg'], $this->differenceParams($extra['params3'], $sData['vars'], $mData['service'])); break;
			case 'full': case 'difference': list($mp, $mip) = $this->calcModifiedService($mData['service'], $mData['e_pkg'], $extra['params3']); break;
			default: $mp = $mip = 0;
		}

		$this->setServiceModifyBasePrice($mId, $pkgData['price'], $pkgData['price2'], $pkgData['prolong_price'], $mp, $pkgData['install_price'], $mip);
	}

	public function __ava__setServiceModifyBasePrice($mId, $price, $price2, $pPrice, $mPrice, $iPrice, $imPrice){
		/*
			Устанавливает базовые цены нового ТП
		*/

		$this->DB->Upd(
			array(
				'modify_service_orders',
				array(
					'base_price' => $price,
					'base_price2' => $price2,
					'base_prolong_price' => $pPrice,
					'base_modify_price' => $mPrice,
					'base_install_price' => $iPrice,
					'base_install_modify_price' => $imPrice
				),
				"`id`='$mId'"
			)
		);
	}

	public function __ava__prepareServiceModifyPayData($mId, $modified = false, $inAdmin = false){
		/*
			Получает сведения на основании тарифа. Вносит сведения о модифицируемой услуге в БД
		*/

		if($modified === false) $modified = time();

		$mData = $this->getServiceModifyData($mId, true);
		$sData = $this->getOrderedService($mData['service_order_id']);
		$oPkgData = $this->serviceData($sData['service'], $sData['package']);
		$nPkgData = $this->serviceData($sData['service'], $mData['pkg']);


		//Расчет установки модификации
		switch($nPkgData['service_modify_install_type']){
			case 'full': $ifp = $mData['base_modify_install_price'] + $mData['base_install_price']; break;
			case 'full2': $ifp = $mData['base_modify_install_price'] + (($mData['pkg'] != $sData['package']) ? $mData['base_install_price'] : 0); break;
			case 'difference':
				if(($oldMfp = $this->DB->cellFetch(array('modify_service_orders', 'install_price', "`service_order_id`='{$mData['service_order_id']}' AND `status`=6", "`id` DESC"))) === ''){
					$oldm = $this->DB->rowFetch(array('order_entries', array('install_price', 'modify_install_price'), "`service_order_id`='{$mData['service_order_id']}' AND `entry_type`='new'"));
					$oldMfp = $oldm['install_price'] + $oldm['modify_install_price'];
				}

				$ifp = $mData['base_modify_install_price'] + $mData['base_install_price'] - $oldMfp;
				if($ifp < 0) $ifp = 0;
				break;
			default: $ifp = 0;
		}


		//Расчитываем цену
		$periods = $this->getTermRemainPeriods($mData['service_order_id'], $modified);
		$oldPayStay = 0;
		$newPayFull = 0;

		foreach($periods as $i => $e){
			if($i != 'test'){
				foreach($e as $i1 => $e1){
					//Получение сведений о прежних сроках и о ценнике
					$eData = $this->getOrderEntry($i1);
					$periods[$i][$i1]['old_term'] = Dates::sec2term($oPkgData['base_term'], $e1['to'] - $e1['from'], false);
					$periods[$i][$i1]['new_price'] = ($mData['base_'.$i] + $mData['base_modify_price']) * $periods[$i][$i1]['old_term'];


					//Получаем базовый ценник старого тарифа
					$p1 = $eData[$i] + $eData['modify_price'];
					if($i == 'prolong_price') $p2 = $sData['price'] + $sData['modify_price'];
					else $p2 = $oPkgData[$i] + $sData['modify_price'];

					switch($oPkgData['service_modify_price_type']){
						case 'old': $periods[$i][$i1]['old_price'] = $p1 * $periods[$i][$i1]['old_term']; break;
						case 'bigger': $periods[$i][$i1]['old_price'] = ($p1 > $p2 ? $p1 : $p2) * $periods[$i][$i1]['old_term']; break;
						case 'smaller': $periods[$i][$i1]['old_price'] = ($p1 < $p2 ? $p1 : $p2) * $periods[$i][$i1]['old_term']; break;
						default: $periods[$i][$i1]['old_price'] = $p2 * $periods[$i][$i1]['old_term'];
					}


					//Получаем скидки
					if($oPkgData['service_modify_discount_type'] != 5){
						$eData2 = $eData;
						if($i == 'prolong_price') $term = $e1['old_term'];
						else $term = (isset($periods['price'][$i1]['old_term']) ? $periods['price'][$i1]['old_term'] : 0) + (isset($periods['price2'][$i1]['old_term']) ? $periods['price2'][$i1]['old_term'] : 0);

						if($oPkgData['service_modify_discount_type'] == 2) $eData2['term'] = floor($term);
						elseif($oPkgData['service_modify_discount_type'] == 3) $eData2['term'] = floor($eData['term'] * ($e1['old_price'] / $e1['new_price']));
						elseif($oPkgData['service_modify_discount_type'] == 4) $eData2['term'] = floor($term * ($e1['old_price'] / $e1['new_price']));
						elseif($oPkgData['service_modify_discount_type'] != 1) $periods[$i][$i1]['discounts'] = $eData['discounts'];

						if($oPkgData['service_modify_discount_type'] >= 1 && $oPkgData['service_modify_discount_type'] <= 4){
							$eData2['price'] = isset($periods['price'][$i1]['old_term']) ? $periods['price'][$i1]['old_term'] : 0;
							$eData2['price2'] = isset($periods['price'][$i1]['old_term']) ? $periods['price'][$i1]['old_term'] : 0;
							$eData2['prolong_price'] = isset($periods['price'][$i1]['old_term']) ? $periods['price'][$i1]['old_term'] : 0;
							$periods[$i][$i1]['discounts'] = $this->getDiscounts($i1, $eData2, array($i1 => $eData2));
						}
					}


					//Вычисляем ценники со скидкой
					if($i == 'price') $dscType = 'term';
					elseif($i == 'price2') $dscType = 'term2';
					elseif($i == 'prolong_price') $dscType = 'prolong';

					$dsco = isset($eData['discounts'][$dscType]) ? $eData['discounts'][$dscType] : 0;
					$dscn = isset($periods[$i][$i1]['discounts'][$dscType]) ? $periods[$i][$i1]['discounts'][$dscType] : 0;
					$periods[$i][$i1]['old_price2'] = $periods[$i][$i1]['old_price'] - ($periods[$i][$i1]['old_price'] * $dsco / 100);
					$periods[$i][$i1]['new_price2'] = $periods[$i][$i1]['new_price'] - ($periods[$i][$i1]['new_price'] * $dscn / 100);

					$oldPayStay += $periods[$i][$i1]['old_price2'];
					$newPayFull += $periods[$i][$i1]['new_price2'];
				}
			}
		}


		//Получаем стоимость смены тарифа
		$chPrice = 0;
		if($oldPayStay > $newPayFull) $chPrice += $oPkgData['change_down_price'];
		else $chPrice += $oPkgData['change_up_price'];

		if($oPkgData['server'] != $nPkgData['server']) $chPrice += $oPkgData['change_srv_price'];
		if($oPkgData['main_group'] != $nPkgData['main_group']) $chPrice += $oPkgData['change_grp_price'];
		if($mData['base_modify_price'] && $oPkgData['name'] == $nPkgData['name']) $chPrice += $oPkgData['change_modify_price'];


		//вычисляем остаточный срок
		$newPayStay = $newTerm = 0;
		$payCounter = $oldPayStay;
		$chPrice2 = $chPrice;
		$ifp2 = $ifp;

		if($oPkgData['service_modify_type2'] != 1){
			$payCounter -= $chPrice + $ifp;
			$chPrice = $ifp = 0;
		}

		foreach($periods as $i => $e){
			if($i != 'test'){
				foreach($e as $i1 => $e1){
					if($payCounter - $periods[$i][$i1]['new_price2'] > 0 || $oPkgData['service_modify_type'] == 'balance') $periods[$i][$i1]['new_term'] = $periods[$i][$i1]['old_term'];
					elseif($payCounter > 0){
						$periods[$i][$i1]['new_term'] = $payCounter * $periods[$i][$i1]['old_term'] / $periods[$i][$i1]['new_price2'];
						$periods[$i][$i1]['new_price2'] = $payCounter;
					}
					else{
						$periods[$i][$i1]['new_term'] = 0;
						$periods[$i][$i1]['new_price2'] = 0;
					}

					if($periods[$i][$i1]['new_price2'] > 0) $last = $i;
					$payCounter -= $periods[$i][$i1]['new_price2'];
					$newPayStay += $periods[$i][$i1]['new_price2'];
					$newTerm += $periods[$i][$i1]['new_term'];
				}
			}
		}


		//Если идет по сути увеличение срока, добавляем исчо период
		if($payCounter > 0){
			$periods['prolong_price']['add'] = array(
				'from' => $sData['paid_to'],
				'to' => $sData['paid_to'],
				'old_term' => 0,
				'new_price' => $payCounter,
				'old_price' => 0,
				'discounts' => array(),
				'old_price2' => 0,
				'new_price2' => $payCounter,
				'new_term' => $payCounter / ($mData['base_prolong_price'] + $mData['base_modify_price'])
			);

			$newPayStay += $periods['prolong_price']['add']['new_price2'];
			$newTerm += $periods['prolong_price']['add']['new_term'];
			$last = 'prolong_price';
		}

		$newTerm = Dates::term2sec($oPkgData['base_term'], $newTerm, false);


		//Пересчитвыаем срок, если изменение с точностью до суток
		if($oPkgData['service_modify_type'] == 'paidtobyday'){
			$stay = ($modified + $newTerm - $sData['created']) % 86400;
			$newTerm -= $stay;
			$newPayStay -= Dates::sec2term($oPkgData['base_term'], $stay, false) * $mData['base_'.$last];
		}


		//Получаем тестовый срок
		if(!empty($periods['test'])){
			$tId = Library::firstKey($periods['test']);
			$k = $oldPayStay / $newPayFull;

			$ott = $periods['test'][$tId]['to'] - $periods['test'][$tId]['from'];
			$ntt = $sData['created'] - $modified;
			if($ntt < 0) $ntt = 0;

			switch($oPkgData['service_modify_test_type']){
				case 'new': $newTerm += $ntt; break;
				case 'bigger': $newTerm += ($ntt > $ott) ? $ntt : $ott; break;
				case 'smaller': $newTerm += ($ntt < $ott) ? $ntt : $ott; break;
				case 'normal': $newTerm += $ntt * $k; break;
				case 'down': $newTerm += ($k < 1) ? $ntt * $k : $ntt; break;
				case 'up': $newTerm += ($k > 1) ? $ntt * $k : $ntt; break;
				default: $newTerm += $ott;
			}
		}

		$this->setServiceModifyPayData(
			$mId,
			$sData['paid_to'],
			$modified + $newTerm,
			$sData['paid_to'] - $modified,
			$newTerm,
			$oldPayStay,
			$newPayFull,
			$newPayStay,
			$newPayStay - $oldPayStay,
			$ifp,
			$ifp2,
			$chPrice,
			$chPrice2,
			$newPayStay - $oldPayStay + $ifp2 + $chPrice2
		);

		$this->setServiceModifyPeriods($mId, $periods);
	}

	public function __ava__setServiceModifyPayData($mId, $oPayTo, $nPayTo, $oTermStay, $nTermStay, $oPayStay, $nCalc, $nPayStay, $diff, $iCalc, $installPrice, $chCalc, $changePrice, $total){
		/*
			Устанавливает параметры оплаты модификации
		*/

		$this->DB->Upd(
			array(
				'modify_service_orders',
				array(
					'old_paid_to' => $oPayTo,
					'new_paid_to' => $nPayTo,
					'old_term_stay' => $oTermStay,
					'new_term_stay' => $nTermStay,
					'old_pay_stay' => $oPayStay,
					'new_calculate' => $nCalc,
					'new_pay_stay' => $nPayStay,
					'difference' => $diff,
					'install_calculate' => $iCalc,
					'install_price' => $installPrice,
					'change_calculate' => $chCalc,
					'change_price' => $changePrice,
					'total' => $total
				),
				"`id`='{$mId}'"
			)
		);
	}

	public function __ava__setServiceModifyPeriods($mId, $extra){
		/*
			Устанавливает сведения о периодах расчета
		*/

		$this->DB->Upd(array('modify_service_orders', array('extra' => $extra), "`id`='{$mId}'"));
	}


	/********************************************************************************************************************************************************************

																			Завершение модификации

	*********************************************************************************************************************************************************************/

	public function __ava__setModify($id, $postfix, $values = false){		/*
			Устанавливает параметры модификации по админской форме
		*/

		if($values === false) $values = $this->values;
		if(!is_array($postfix)) $postfix = array($postfix => '');
		$this->setServiceModifyExtraParams($id, $values, true);
		$return = array();

		foreach($postfix as $i => $e){
			$mData = $this->getServiceModifyData($i);
			$this->setServiceModifyBasePrice(
				$i,
				$mData['base_price'],
				$mData['base_price2'],
				$values['base_prolong_price'.$e],
				$values['base_modify_price'.$e],
				$mData['base_install_price'],
				$mData['base_install_modify_price']
			);

			$this->setServiceModifyPayData(
				$i,
				$mData['old_paid_to'],
				$values['new_paid_to'.$e],
				$mData['old_term_stay'],
				$values['new_paid_to'.$e] - $values['modified'.$e],
				$mData['old_pay_stay'],
				$values['new_calculate'.$e],
				$values['new_pay_stay'.$e],
				$values['new_pay_stay'.$e] - $mData['old_pay_stay'],
				$values['install_calculate'.$e],
				$values['install_price'.$e],
				$values['change_calculate'.$e],
				$values['change_price'.$e],
				$values['total'.$e]
			);

			$return[$i] = array(
				'server' => $values['server'.$e],
				'auto' => !empty($values['auto'.$e])
			);		}

		return $return;	}

	public function __ava__modifyServicesEnd($id, $extra = array()){
		/*
			Модификация списка услуг по main_id
		*/

		$list = $this->getServiceMainModifyOrders($id);
		$this->DB->Upd(array('modify_service_orders', array('status' => 5), $this->getEntriesWhere($list)));

		$fObj = new Form('modifyParams', '', '', '');
		$this->generateModifyOrderFormEntry($fObj, $id, '', 'form', false);

		$mmData = $this->getServiceMainModifyData($id);
		$idList2 = array();
		$result = array();

		foreach($list as $i => $e){
			$mData = $this->getServiceModifyData($i, true);
			$extra[$i]['ident'] = $mData['ident'];

			if(!$this->checkBalance($mData['client_id'], $mData['total'], $mData['currency'])){
				$this->setMessage('total'.$i, 'Услуга для "'.$mData['ident'].'" не изменена, т.к. недостаточно средств на балансе', 'error');
			}
			elseif(!$extra[$i]['server'] || !$extra[$i]['auto']){
				$this->setMessage('total', 'Услуга для "'.$mData['ident'].'" не изменена на сервере, т.к. это не предусмотрено', 'error');
				$idList2[''][$i] = $extra[$i];
				$result[''][$i] = true;
			}
			else $idList2[$mData['server']][$i] = $extra[$i];		}

		foreach($idList2 as $i => $e){
			if($i) $result[$i] = $this->callServiceObj('modifyAcc', $mmData['service'], array('accs' => $e, 'server' => $i, 'id' => $id));
		}

		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){
				$mData = $this->getServiceModifyData($i1);

				if($e1){
					$this->setMessage('total', 'Тариф для "'.$mData['ident'].'" модифицирован на сервере. <a href="'.$this->path.'?mod='.$this->mod.'&func=connectionResult&id='.$this->getConnectionResultId($i).'" target="_blank">Смотреть результат выполнения</a>.');
					$this->setModifyService($i1, $idList2[$i][$i1]);
					$this->sendModifyServiceMails($i1);
				}
				else{
					$this->setMessage('total', 'Тариф для "'.$mData['ident'].'" не модифицирован на сервере. <a href="'.$this->path.'?mod='.$this->mod.'&func=connectionResult&id='.$this->getConnectionResultId($i).'" target="_blank">Смотреть результат выполнения</a>.', 'error');
					$this->sendModifyServiceMails($i1, false);
				}
			}
		}
	}

	public function __ava__setModifyService($id, $values = false){
		/*
			Выполняет модификацию услуги
		*/

		$mData = $this->getServiceModifyData($id, true);
		$sData = $this->getOrderedService($mData['service_order_id'], true);
		$oPkgData = $this->serviceData($sData['service'], $sData['package']);
		$nPkgData = $this->serviceData($mData['service'], $mData['pkg']);

		$this->DB->Upd(
			array(
				'order_services',
				array(
					'package' => $mData['pkg'],
					'server' => $values['server'],
					'price' => $mData['base_prolong_price'],
					'modify_price' => $mData['base_modify_price'],
					'vars' => $mData['vars']['params3']
				),
				"`id`='{$mData['service_order_id']}'"
			)
		);

		$pId = $mData['total'] == 0 ? 0 : $this->upBalance($mData['client_id'], -$mData['total'], $nPkgData['currency'], 'service', $mData['modified'], $id, $mData['service_order_id'], 'Изменение тарифа', 'modify_service_orders');
		$this->changeServiceTerm($mData['service_order_id'], $mData['new_paid_to'], $mData['modified'], 'modify_service_orders', $id, $pId);
		$this->DB->Upd(array('modify_service_orders', array('status' => 6), "`id`='$id'"));
		$this->sendModifyServiceMails($id);
	}

	public function __ava__sendModifyServiceMails($id){
		/*
			Отправляет письмо о модификации
		*/
	}


	/********************************************************************************************************************************************************************

																			Операции с балансом

	*********************************************************************************************************************************************************************/

	public function __ava__checkBalance($clientId, $sum, $currency = ''){
		$cData = $this->getClientData($clientId, true);
		if($currency) $sum = $this->getSumInDefault($sum, $currency);
		if($cData['balance'] < $sum) return false;
		return true;
	}

	public function __ava__upBalance($clientId, $sum, $currency, $foundationType, $date, $objectId = 0, $sId = 0, $foundation = '', $objectType = ''){
		/*
			Пополняет баланс на указанную сумму

			$sum - сумма в валюте оплаты
			$currency - валюта оплаты
			$payment - способ оплаты

			$foundationType - тип основания добавления денег:
				balance - пополнение баланса				$objectType - payment_transactions
				bonus - установка бонуса за оплату			$objectType - payment_transactions
				service - списание на оплату услуги			$objectType - order_services , если $sum положительная - возврат средств на баланс в связи с изменением срока услуги (напр. отказ, смена ТП и т.п.)
				wrong - ошибочное зачисление
				return - возврат средств пользователю на кошелек
		*/

		//Получаем сумму в дефолтной валюте на момент оплаты
		$defSum = $this->getSumInDefault($sum, $currency);
		$defCur = $this->getMainCurrencyName();
		$cData = $this->getClientData($clientId);

		$this->DB->trStart();

		if(!$foundation){
			switch($foundationType){
				case 'balance': $foundation = '{Call:Lang:modules:billing:popolnenieba}'; break;
				case 'bonus': $foundation = '{Call:Lang:modules:billing:zachislenieb}'; break;
				case 'service': $foundation = '{Call:Lang:modules:billing:predostavlen}'; break;
				case 'wrong': $foundation = '{Call:Lang:modules:billing:oshibochnoez}'; break;
				case 'return': $foundation = '{Call:Lang:modules:billing:vozvratsreds}'; break;
			}
		}

		if(!$objectType && $objectId){
			switch($foundationType){
				case 'balance': $objectType = 'payment_transactions'; break;
				case 'bonus': $objectType = 'payment_transactions'; break;
				case 'service': $objectType = 'order_entries'; break;
			}
		}

		//Пополняем баланс
		$fields = array('#isExp' => array('balance' => true), 'balance' => "`balance` + ".$defSum);

		if($foundationType != 'service'){
			$fields['#isExp']['all_payments'] = true;
			$fields['all_payments'] = "`all_payments` + ".$defSum;
		}
		elseif($foundationType == 'service'){
			$fields['#isExp']['all_payed_services'] = true;
			$fields['all_payed_services'] = "`all_payed_services` - ".$defSum;
		}

		$this->DB->Upd(array('clients', $fields, "`id`='$clientId'"));
		if($foundationType == 'service' && $sId){
			$this->DB->Upd(array('order_services', array('last_paid' => $date, 'all_payments' => '`all_payments` - '.$sum, '#isExp' => array('all_payments' => true)), "`id`='$sId'"));		}

		$ubd = $this->DB->rowFetch(array('clients', array('balance', 'all_payments'), "`id`='".db_main::Quot($clientId)."'"));
		if($defSum > 0) $this->setMessage('sum', '{Call:Lang:modules:billing:balanspolzov:'.Library::serialize(array($defSum, $ubd['balance'], $defCur, $ubd['all_payments'], $defCur)).'}');
		elseif($defSum < 0) $this->setMessage('sum', '{Call:Lang:modules:billing:sbalansapolz:'.Library::serialize(array(-$defSum, $ubd['balance'], $defCur, $ubd['all_payments'], $defCur)).'}');

		//Добавляем запись о транзакции
		$params = $mailParams = array(
			'client_id' => $clientId,
			'service_id' => $sId,
			'object_id' => $objectId,
			'object_type' => $objectType,
			'date' => $date,
			'real_date' => time(),
			'sum' => $defSum,
			'foundation' => $foundation,
			'foundation_type' => $foundationType,
		);

		$payId = $this->DB->Ins(array('pays', $params));
		$this->setMessage('sum', '{Call:Lang:modules:billing:vnesenysvede:'.Library::serialize(array($foundation)).'}');
		$this->DB->trEnd(true);

		$mailParams['sum'] = $sum;
		$mailParams['currency'] = $this->currencyName($currency);
		$mailParams['defSum'] = $defSum;

		$mailParams['defCurrency'] = $defCur;
		$mailParams['cData'] = $this->getClientData($clientId, true);
		$mailParams['uData'] = $this->getUserByClientId($clientId);

		$this->callCoMods('__billing_enrollPay', compact('payId', 'clientId', 'sum', 'currency', 'foundationType', 'date', 'objectId', 'sId', 'foundation', 'objectType'));
		if($this->Core->getParam('balanceMotionUserMail', $this->mod)) $this->mail($this->getClientEml($clientId), $this->getTmplParams('balanceMotion'), $mailParams);
		if($this->Core->getParam('balanceMotionAdminMail', $this->mod)) $this->mail($this->getAdminNotifyEml(), $this->getTmplParams('balanceMotionAdmin'), $mailParams);
		if($uid = $this->getUserIdByClientId($clientId)) $this->Core->reauthUserSession($uid);

		return true;
	}


	/********************************************************************************************************************************************************************

																			Работа с записями счета

	*********************************************************************************************************************************************************************/

	public function __ava__addNewOrderEntry($type, $clientId, $service, $pkg, $orderId = 0, $sId = 0){
		/*
			Добавляет новую запись о заказе в счет и услугу с пометкой "в стадии заказа"
		*/

		if(!$eId = $this->DB->Ins(array('order_entries', array('entry_type' => $type, 'client_id' => $clientId, 'service' => $service, 'package' => $pkg, 'order_id' => $orderId, 'order_service_id' => $sId, 'date' => time())))){
			throw new AVA_Exception('Не удалось внести запись в счет');
		}
		return $eId;
	}

	public function __ava__upOrderEntry($eId, $params){
		/*
			Свободное обновление сведений в записи
		*/

		$eData = $this->DB->rowFetch(array('order_entries', '*', "`id`='$eId'"));
		$eData['extra'] = Library::unserialize($eData['extra']);
		$this->DB->Upd(array('order_entries', Library::array_merge($eData, $params), "`id`='$eId'"));
	}

	public function __ava__setOrderEntryNewParams($eId, $ident, $term, $test_term, $auto_prolong, $auto_prolong_fract, $ind_price, $promo_code, $extra, $paid_to = false){
		/*
			Параметры для заказа новой услуги
		*/

		if($paid_to === false){
			$eData = $this->getOrderEntry($eId);
			$pkgData = $this->serviceData($eData['service'], $eData['package']);

			if($eData['entry_type'] == 'new') $paid_to = $eData['date'] + Dates::term2sec($pkgData['base_term'], $term) + Dates::term2sec($pkgData['test_term'], $test_term);
			elseif($eData['entry_type'] == 'prolong'){
				$sData = $this->getOrderedService($eData['service_order_id']);
				$paid_to = $sData['paid_to'] + Dates::term2sec($pkgData['base_term'], $term) + Dates::term2sec($pkgData['test_term'], $test_term);			}
		}

		$this->DB->Upd(array('order_entries', compact('ident', 'term', 'test_term', 'auto_prolong', 'auto_prolong_fract', 'ind_price', 'promo_code', 'extra', 'paid_to'), "`id`='$eId'"));
	}

	public function __ava__setOrderEntryIdent($eId, $ident){
		/*
			Параметры для заказа новой услуги
		*/

		$this->DB->Upd(array('order_entries', array('ident' => $ident), "`id`='$eId'"));
	}

	public function __ava__setOrderEntryProlongParams($eId, $term, $promo_code, $extra){
		/*
			Параметры для продления услуги
		*/

		$this->DB->Upd(array('order_entries', compact('term', 'promo_code', 'extra'), "`id`='$eId'"));
	}

	public function __ava__setOrderEntryUserParams($eId, $term, $ident, $autoProlong = '', $autoProlongFract = '', $promoCode = '', $extra = array(), $test = false, $paid_to = false){
		/*
			Устанавливает параметры записи для счета, которые пользователь менять в состоянии
		*/

		$eData = $this->getOrderEntry($eId, true);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		if($test === false && ($pkgData['inner_test'] || !$term)) $test = $pkgData['test'];

		if(!isset($extra['params1'])) $extra['params1'] = array();
		if(!isset($extra['params2'])) $extra['params2'] = array();
		if(!isset($extra['params3'])) $extra['params3'] = array();

		if($eData['entry_type'] == 'new') $this->setOrderEntryNewParams($eId, $ident, $term, $test, $autoProlong, $autoProlongFract, $this->Core->getParam('recalculatePayPrice', $this->mod), $promoCode, $extra, $paid_to);		elseif($eData['entry_type'] == 'prolong') $this->setOrderEntryProlongParams($eId, $term, $promoCode, $extra);
		$this->DB->Upd(array('order_entries', array('status' => 1), "`id`='$eId'"));
		$this->setOrderEntryCaption($eId);
	}

	public function __ava__setServiceProlongEntry($eId, $prefix, $id, $extParams = array(), $values = false, $inAdmin = false){
		/*
			Новая запись о продлении услуги. Возвращает ID вновь созданной записи
		*/

		$eData = $this->getOrderEntry($eId);
		if($values === false) $values = $this->values;
		$fObj = new Form('prolongParams', '', '', '');

		$this->setAccProlongMatrix($fObj, $eId, '', '', 'form', $extParams, $inAdmin);
		$extra['params1'] = $this->getGeneratedFormValuesByMatrix($fObj->getMatrix(), $values, $prefix, $id);
		$extra['params2'] = $extra['params3'] = array();

		$this->setOrderEntryProlongParams($eId, isset($values['term'.$id]) ? $values['term'.$id] : $eData['term'], isset($values['promo_code'.$id]) ? $values['promo_code'.$id] : '', $extra);
		$this->DB->Upd(array('order_entries', array('status' => 1), "`id`='$eId'"));
		$this->setOrderEntryCaption($eId);
	}

	public function __ava__setOrderEntryCaption($eId, $caption = false){		/*
			Устанавливает заголовок заказа
		*/

		if($caption === false){			$eData = $this->getOrderEntry($eId, true);
			$pkgData = $this->serviceData($eData['service'], $eData['package']);

			if(!$caption = $this->callServiceObj('getOrderEntry', $eData['service'], array('id' => $eId))){				if($eData['entry_type'] == 'new') $caption = '{Call:Lang:modules:billing:tarifdlia:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $eData['ident'])).'}';
				elseif($eData['entry_type'] == 'prolong'){
					$sData = $this->getOrderedService($eData['order_service_id']);					$caption = 'Продление услуги '.$pkgData['service_textname'].' для '.$sData['ident'];				}
			}		}

		$this->DB->Upd(array('order_entries', array('entry_caption' => $caption), "`id`='{$eId}'"));	}

	public function __ava__setOrderEntryPayParams($eId, $price, $pPrice, $iPrice, $mPrice, $mIPrice, $apPrice, $discount, $discounts, $sum, $total){
		/*
			Устанавливает платежные параметры для услуги. Устанавливаются в момент расчета счета
		*/

		$this->DB->Upd(
			array(
				'order_entries',
				array(
					'price' => $price,
					'price2' => $pPrice,
					'install_price' => $iPrice,
					'modify_price' => $mPrice,
					'modify_install_price' => $mIPrice,
					'prolong_price' => $apPrice,
					'discount' => $discount,
					'discounts' => $discounts,
					'sum' => $sum,
					'total' => $total
				),
				"`id`='{$eId}'"
			)
		);

		$this->callCoMods('__billing_setOrderEntryPayParams', array('id' => $eId), $_);
	}

	public function __ava__setOrderEntryCreateParams($eId, $paidTo, $t = false){		/*
			Устанавливает параметры закрывающие запись
		*/

		if(!$t) $t = time();
		$this->DB->Upd(array('order_entries', array('paid_to' => $paidTo, 'enrolled' => $t), "`id`='{$eId}'"));	}


	/********************************************************************************************************************************************************************

																			Данные о записях счета

	*********************************************************************************************************************************************************************/

	public function __ava__getOrderEntry($eId, $force = false){		/*
			Возвращает запись об услуге
		*/

		if($force || !isset($this->orderEntriesByEntry[$eId])){			if(!$this->orderEntriesByEntry[$eId] = $this->DB->rowFetch($this->getEntrySelectReq("t1.id='$eId'"))) throw new AVA_Exception('Заказа '.$eId.' не найдено');
			$this->orderEntriesByEntry[$eId]['discounts'] = Library::unserialize($this->orderEntriesByEntry[$eId]['discounts']);
			$this->orderEntriesByEntry[$eId]['extra'] = Library::unserialize($this->orderEntriesByEntry[$eId]['extra']);
		}

		return $this->orderEntriesByEntry[$eId];
	}

	public function __ava__getPkgByOrderEntry($eId, $force = false){		$eData = $this->getOrderEntry($eId, $force);
		return $this->serviceData($eData['service'], $eData['package']);
	}

	public function __ava__getOrderEntries($orderId, $force = false){
		/*
			Возвращает все записи счета
		*/
		if($force || !isset($this->orderEntries[$orderId])) $this->orderEntries[$orderId] = $this->getOrderEntriesByFilter("t1.order_id='$orderId' AND t1.status>0", true);
		return $this->orderEntries[$orderId];
	}

	public function __ava__getOrderEntriesByFilter($filter, $force = false, $order = 't1.id'){
		/*
			Возвращает все записи счета
		*/

		$fId = regExp::lower(trim($filter));
		if($force || !isset($this->orderEntries[$fId])){
			$this->orderEntriesByFilter[$fId] = array();

			foreach($this->DB->columnFetch($this->getEntrySelectReq($filter, $order)) as $e){
				$e['discounts'] = Library::unserialize($e['discounts']);
				$e['extra'] = Library::unserialize($e['extra']);
				$e['s_vars'] = Library::unserialize($e['s_vars']);
				$e['pkgvars'] = Library::unserialize($e['pkgvars']);
				$this->orderEntriesByFilter[$fId][$e['id']] = $this->orderEntriesByEntry[$e['id']] = $e;
			}
		}
		return $this->orderEntriesByFilter[$fId];
	}

	private function getEntrySelectReq($filter = '', $order = 't1.id'){
		$p = $this->DB->getPrefix();

		$t1 = $p.'order_entries';
		$t2 = $p.'order_services';
		$t3 = $p.'order_packages';
		$t4 = $p.'services';

		return "SELECT
			t1.*,
			t2.server AS s_server,
			t2.ident AS s_ident,
			t2.date AS s_date,
			t2.created AS s_created,
			t2.last_paid AS s_last_paid,
			t2.paid_to AS s_paid_to,
			t2.step AS s_step,
			t2.vars AS s_vars,
			t3.currency AS pkgcurrency,
			t3.text AS pkgname,
			t3.test AS pkgtest,
			t3.vars AS pkgvars,
			t4.text AS sname,
			t4.extension AS ex,
			t4.base_term AS bt,
			t4.test_term AS tt
		FROM
			$t1 AS t1
			LEFT JOIN $t2 AS t2 ON t1.order_service_id=t2.id
			LEFT JOIN $t3 AS t3 ON t1.package=t3.name AND t1.service=t3.service
			LEFT JOIN $t4 AS t4 ON t1.service=t4.name
		WHERE $filter ORDER BY $order";
	}

	public function __ava__getOrderedService($id, $force = false){
		/*
			Возвращает данные о заказанной услуге
		*/

		if(empty($this->orderedServices[$id]) || $force){
			$this->orderedServices[$id] = $this->extractOrderServiceData($this->DB->rowFetch(array('order_services', '*', "`id`='$id'")));
			foreach($this->DB->rowFetch(array('orders_'.$this->orderedServices[$id]['service'], '*', "`service_order_id`='$id'")) as $i => $e){
				$this->orderedServices[$id]['s_'.$i] = $e;
			}
		}

		return $this->orderedServices[$id];
	}

	public function __ava__getParamByServiceId($id, $param){		/*
			Параметр по ID услуги
		*/

		$sData = $this->getOrderedService($id);
		$pkgData = $this->serviceData($sData['service'], $sData['package']);

		switch($param){			case 'currency': return $pkgData['currency'];
			case 'currencyName': return $this->currencyName($pkgData['currency']);		}
	}

	public function __ava__getOrderedServicesByEntries($service, $entries = false){
		/*
			Возвращает данные о заказанной услуге
		*/

		$p = $this->DB->getPrefix();
		$t1 = $p.'order_services';
		$t2 = $p.'orders_'.$service;

		$t2Flds = '';
		foreach($this->DB->getFields('orders_'.$service) as $i => $e) $t2Flds .= ", t2.{$i} AS s_{$i}";
		$return = array();

		$db = $this->DB->Req("SELECT t1.*{$t2Flds} FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.id=t2.service_order_id WHERE ".$this->getEntriesWhere($entries, 'id', 't1.'));
		while($r = $db->Fetch()){			$this->orderedServices[$r['id']] = $this->extractOrderServiceData($r);
			$return[$r['id']] = $this->orderedServices[$r['id']];		}

		return $return;
	}

	private function extractOrderServiceData($row){
		if(!$row) throw new AVA_Exception('qqqq');
		$row['history'] = Library::unserialize($row['history']);
		$row['vars'] = Library::unserialize($row['vars']);
		$row['extra'] = Library::unserialize($row['extra']);
		return $row;	}

	public function __ava__canUseTest($service, $pkg, $clientId){
		/*
			Проверяет что пользователь может юзать тестовый акк
		*/

		if(!$clientId) throw new AVA_Exception('Не удалось получить ID клиента');
		$pkgData = $this->serviceData($service, $pkg);

		if(empty($this->servicesData[$service][$pkg]['test'])) return false;
		elseif($this->DB->Count(array('order_entries', "`test_term`>0 AND `status`=2 AND `client_id`='{$clientId}'")) >= $this->Core->getParam('maximumTestAccs', $this->mod)) return false;
		elseif($pkgData['max_test_accs'] && ($this->DB->Count(array('order_entries', "`test_term`>0 AND `status`=1 AND package='$pkg' AND `client_id`='{$clientId}'")) >= $pkgData['max_test_accs'])) return false;

		return true;
	}

	public function canUseAction($action, $sId, &$msg = ''){
		/*
			Проверяет что с услугой можно выполнять продление, удаление и т.п. действия
		*/

		$sData = $this->getOrderedService($sId);
		$pkgData = $this->serviceData($sData['service'], $sData['package']);

		switch($action){			case 'prolong':
				if(empty($pkgData['rights']['prolong'])) $msg = "Нельзя продлить услугу {$pkgData['service_textname']} по тарифу {$pkgData['text']} для {$sData['ident']}";
				break;

			case 'suspend':
				if(empty($pkgData['rights']['pause'])) $msg = "Нельзя остановить услугу {$pkgData['service_textname']} по тарифу {$pkgData['text']} для {$sData['ident']}";
				break;

			case 'delete':
				if(empty($pkgData['rights']['del'])) $msg = "Нельзя удалить услугу {$pkgData['service_textname']} по тарифу {$pkgData['text']} для {$sData['ident']}";
				break;
		}

		if(!$msg) return true;
		return false;
	}


	/********************************************************************************************************************************************************************

																	Создание новой услуги, продление

	*********************************************************************************************************************************************************************/

	protected function __ava__orderServiceForm($fObj, $eId, $id, $cnt, $extParams = array(), $disableModify = false){
		/*
			Генерация формы заказа новой услуги
		*/

		$eData = $this->getOrderEntry($eId);
		if(!($pkgData = $this->serviceData($eData['service'], $eData['package'])) || !$pkgData['show'] || empty($pkgData['rights']['new'])){
			throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:tarifanenajd:'.Library::serialize(array($pkg)).'}');
		}

		$fObj->setParam('currency'.$cnt, $this->getCurrencyNameByPkg($eData['service'], $eData['package']));
		$fObj->setParam('pkgData'.$cnt, $pkgData);
		$fObj->setParam('caption'.$cnt, $pkgData['service_textname'].', {Call:Lang:modules:billing:tarif1:'.Library::serialize(array($pkgData['text'])).'}');

		if(!$disableModify && !empty($pkgData['rights']['modify'])) $this->orderConstructorForm($fObj, $eId, 'mpkg_', $id, 'calculate'.$cnt, $cnt);
		$this->setAccOrderMatrix($fObj, $eId, '', $id, 'order'.$cnt, array('cnt' => $cnt));
		foreach($pkgData['vars'] as $i => $e) $fObj->setValue($i.$id, $e);						//Параметры считающиеся предустановленными через настройки тарифа
	}

	protected function __ava__orderConstructorForm($fObj, $eId, $prefix = '', $id = '', $bName = 'form', $cnt = 0){
		/*
			Расчета ТП (калькулятор) по id заказа
		*/

		$eData = $this->getOrderEntry($eId);
		$fObj->setValues($this->orderConstructorForm2($fObj, $eData['service'], $eData['package'], $prefix, $id, $bName, $cnt));
	}

	protected function __ava__orderConstructorForm2($fObj, $service, $pkg, $prefix = '', $id = '', $bName = 'form', $cnt = 0){
		/*
			Расчета ТП (калькулятор) по услуге и пакету
		*/

		$pkgData = $this->serviceData($service, $pkg);
		$matrix = $this->getPkgDescriptForm($service, $pkg, 'mpkg', $prefix, $id, $values, array($this->getConnectionCp($pkgData['server'])));

		foreach($matrix as $i => $e){
			$matrix[$i]['template'] = 'calculate';
			$matrix[$i]['blockId'] = $cnt;
			if($e['type'] == 'text') $matrix[$i]['warn_function'] = 'gen_billing::isValidOrderedValue';
		}

		$fObj->setParam('sData'.$cnt, $this->serviceData($service));
		$fObj->setParam('base_limits'.$cnt, $this->getBasePkgDescript($this->getPkgBase($pkgData['vars'], $matrix, $prefix, $id), $cnt));
		$fObj->setParam('jsCalcHash'.$cnt, $this->getJsCalcHash($matrix, $pkgData['price'], $pkgData['install_price']));

		$this->addFormBlock($fObj, $matrix, array(), array(), $bName);
		$fObj->setHidden('modified'.$id, 1);
		return $values;
	}

	protected function __ava__setAccOrderMatrix($fObj, $eId, $prefix, $id, $bName, $extParams = array(), $inAdmin = false){
		/*
			Возвращает матрицу формы заказа
				- Срок
				- Правила автопродления
				- Промо-код
				- Дополнительные параметры возвращаемые модулем услуги
		*/

		$eData = $this->getOrderEntry($eId);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);

		$params = compact('fObj', 'eId', 'prefix', 'id', 'bName');
		$params['server'] = $pkgData['server'];
		$params = Library::array_merge($params, $extParams);

		$cp = $this->getConnectionCp($params['server']);
		$formData = $params;
		$formData['usePromoCode'] = $this->canUsePromoCode($eData['service']);

		if($pkgData['service_type'] == 'prolonged'){			$formData['terms'] = $this->getTermsList($eData['service'], $eData['package'], $eData['client_id']);
			$formData['pTerms'] = $this->getProlongTermsList($eData['service'], $eData['package']);
			$formData['fract'] = $pkgData['fract_prolong'];
		}

		$this->addFormBlock($fObj, 'order', $formData, array(), $bName);
		$params = Library::array_merge($params, $extParams);
		$this->callServiceObj('setAccOrderMatrix', $eData['service'], $params);
		if($inAdmin) $this->callServiceObj('setAddAccMatrix', $eData['service'], $params);

		$this->addFormBlock($fObj, $this->getPkgDescriptForm($eData['service'], $eData['package'], 'opkg', $prefix, $id, $values, $cp), array(), array(), $bName);
		if($inAdmin) $this->addFormBlock($fObj, $this->getPkgDescriptForm($eData['service'], $eData['package'], 'aacc', $prefix, $id, $values, $cp), $formData, array(), $bName);
		$fObj->setValues($values);
	}

	protected function __ava__setAccProlongMatrix($fObj, $eId, $prefix, $id, $bName, $extParams = array(), $inAdmin = false){
		/*
			Возвращает матрицу формы заказа
				- Срок
				- Правила автопродления
				- Промо-код
				- Дополнительные параметры возвращаемые модулем услуги
		*/

		$eData = $this->getOrderEntry($eId);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		$params = compact('fObj', 'eId', 'prefix', 'id', 'bName');

		$params['server'] = $pkgData['server'];
		$params = Library::array_merge($params, $extParams);
		$params['text'] = $inAdmin ? 'Срок продления' : '{Call:Lang:modules:billing:akkauntpotar:'.Library::serialize(array($pkgData['name'], $eData['ident'])).'}';

		$params['terms'] = $this->getTermsList($eData['service'], $eData['package'], $eData['client_id']);
		$this->addFormBlock($fObj, 'term', $params, array(), $bName);
		$this->callServiceObj('setAccUserProlongMatrix', $eData['service'], $params);

		if($inAdmin){			$this->callServiceObj('setProlongAccMatrix', $eData['service'], $params);
			$this->addFormBlock($fObj, array($prefix.'term'.$id => array('type' => 'text')), $params, array(), $bName);		}
	}

	protected function __ava__checkAccOrderMatrix($eId, $prefix, $id, $extParams = array(), $values = false, $inAdmin = false){
		/*
			Собственно проверка формы заказа
		*/

		$eData = $this->getOrderEntry($eId);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		if($values === false) $values = $this->values;

		$params = Library::array_merge(array('eId' => $eId, 'server' => $pkgData['server'], 'prefix' => $prefix, 'id' => $id, 'values' => $values), $extParams);
		$this->callServiceObj('checkAccOrderMatrix', $eData['service'], $params);
		if($inAdmin) $this->callServiceObj('checkAddAccMatrix', $eData['service'], $params);
	}

	protected function __ava__checkAccUserProlongMatrix($eId, $prefix, $id, $extParams = array(), $values = false, $inAdmin = false){
		/*
			Собственно проверка формы заказа
		*/

		$eData = $this->getOrderEntry($eId);
		$pkgData = $this->serviceData($eData['service'], $eData['package']);
		if($values === false) $values = $this->values;

		$params = Library::array_merge(array('eId' => $eId, 'server' => $pkgData['server'], 'prefix' => $prefix, 'id' => $id, 'values' => $values), $extParams);
		$this->callServiceObj('checkAccUserProlongMatrix', $eData['service'], $params);
		if($inAdmin) $this->callServiceObj('checkProlongAccMatrix', $eData['service'], $params);
	}

	protected function __ava__getServiceCreateParams($eId, $prefix, $id, $extParams = array(), $values = false, $inAdmin = false){
		/*
			Возвращает список параметров необходимых для создания услуги
		*/

		$eData = $this->getOrderEntry($eId);
		if($values === false) $values = $this->values;

		$fObj = new Form('createParams', '', '', '');
		$this->setAccOrderMatrix($fObj, $eId, '', '', 'form', $extParams, $inAdmin);
		return $this->getGeneratedFormValuesByMatrix($fObj->getMatrix(), $values, $prefix, $id);
	}

	public function getPkgDescriptForm($service, $pkg, $type, $varPrefix = '', $varPostfix = '', &$values = array(), $cp = array()){
		/*
			Выдает форму затрагиваемую в описании тарифов
		*/

		if(!is_array($cp)) $cp = array($cp);
		$dbObj = $this->DB->Req(array('package_descripts', '*', $this->getPkgDscFilter($cp)."`service`='".db_main::Quot($service)."' AND `show` AND `$type`>0", "`sort`"));
		$matrix = array();
		$intSort = array();

		$values2 = array();
		$j = 0;
		$cnt = $dbObj->getRows() + 2;
		$pkgData = $this->serviceData($service, $pkg);

		while($r = $dbObj->Fetch()){
			$r['vars'] = Library::unserialize($r['vars']);
			$var = $varPrefix.$r['name'].$varPostfix;
			$matrix[$var] = $this->getMatrixField($r, $values2);
			$pf = '';

			if($r[$type] == 2){
				$pf = '_'.$pkgData['main_group'];
			}
			elseif($r[$type] == 3){
				$pf = '_'.$r['name'];
				$r['vars']['extra'] = $pkgData['extraDescript'];
			}

			$intSort[((empty($r['vars']['extra'][$type.'_sort'.$pf]) ? 0 : $r['vars']['extra'][$type.'_sort'.$pf]) * $cnt) + $j] = $var;
			if(!empty($r['vars']['extra']['use_unlimit'.$pf])) $matrix[$var]['template'] = 'unlimit';
			if(!empty($r['vars']['extra'][$type.'_hidden'.$pf])) $matrix[$var]['type'] = 'hidden';
			if(!empty($r['vars']['extra'][$type.'_value'.$pf])) $values2[$r['name']] = $r['vars']['extra'][$type.'_value'.$pf];

			if($type == 'mpkg'){
				if(!empty($r['vars']['extra']['mpkg_unlimit'.$pf])){
					$matrix[$var]['template'] = 'unlimit';
					if(empty($r['vars']['extra']['mpkg_max'.$pf])) $matrix[$var]['unlimit'] = true;
				}
				else $matrix[$var]['template'] = '';

				$matrix[$var]['price'] = empty($r['vars']['extra']['mpkg_price'.$pf]) ? 0 : $r['vars']['extra']['mpkg_price'.$pf];
				$matrix[$var]['price_unlimit'] = empty($r['vars']['extra']['mpkg_price_unlimit'.$pf]) ? 0 : $r['vars']['extra']['mpkg_price_unlimit'.$pf];
				$matrix[$var]['price_install'] = empty($r['vars']['extra']['mpkg_price_install'.$pf]) ? 0 : $r['vars']['extra']['mpkg_price_install'.$pf];

				$matrix[$var]['price_install_unlimit'] = empty($r['vars']['extra']['mpkg_price_install_unlimit'.$pf]) ? 0 : $r['vars']['extra']['mpkg_price_install_unlimit'.$pf];
				$matrix[$var]['min'] = empty($r['vars']['extra']['mpkg_min'.$pf]) ? 0 : $r['vars']['extra']['mpkg_min'.$pf];
				$matrix[$var]['max'] = empty($r['vars']['extra']['mpkg_max'.$pf]) ? 0 : $r['vars']['extra']['mpkg_max'.$pf];

				if(empty($matrix[$var]['min']) && !empty($pkgData['vars'][$r['name']])) unset($matrix[$var]['warn']);
			}
			elseif(($type == 'aacc' || $type == 'opkg') && isset($pkgData['vars'][$r['name']])) $values2[$var] = $pkgData['vars'][$r['name']];

			$j ++;
		}

		ksort($intSort);
		$matrix = Library::syncArraySeq($matrix, $intSort);
		foreach($values2 as $i => $e) $values[$varPrefix.$i.$varPostfix] = $e;

		return $matrix;
	}


	/********************************************************************************************************************************************************************

																	Сохраниение параметров о операциях

	*********************************************************************************************************************************************************************/

	public function __ava__getOperation($id, $force = false){		/*
			Данные о операции
		*/

		if($force || !isset($this->operations[$id])){			$this->operations[$id] = $this->DB->rowFetch(array('operations', '*', "`id`='$id'"));
			$this->operations[$id]['log'] = Library::unserialize($this->operations[$id]['log']);
			$this->operations[$id]['result'] = Library::unserialize($this->operations[$id]['result']);
		}

		return $this->operations[$id];	}

	public function __ava__setOperation($id, $clientId, $objType, $objId, $result, $log = false){
		/*
			Устанавливает параметры операции
		*/

		$this->Core->reauthUserSession($this->getUserIdByClientId($clientId));
		return $this->DB->Ins(
			array(
				'operations',
				array(
					'client_id' => $clientId,
					'object_type' => $objType,
					'object_id' => $objId,
					'date' => time(),
					'result' => Library::serialize($result),
					'log' => Library::serialize($log !== false ? $log : $this->messages)
				)
			)
		);
	}

	public function __ava__getOperationResultText($opId){		/*
			Возвращает результат операции в виде текста пригодного для помещения на страницу
		*/

		$opData = $this->getOperation($opId);
		$tmpl = $this->Core->getModuleTemplatePath($this->mod).'new_service.tmpl';
		$return = '';

		if($opData['result']){
			foreach($opData['result'] as $i => $e){
				$eData = $this->getOrderEntry($i);
				$params = array('pkgData' => $this->serviceData($eData['service'], $eData['package']), 'eData' => $eData);
				if($params['pkgData']['server']) $params['connectData'] = $this->getConnectionParams($params['pkgData']['server']);

				if($eData['entry_type'] == 'new'){
					if($e){
						$params['result'] = $opData['log']['ident'.$i];
						$return .= $this->Core->readBlockAndReplace($tmpl, 'success', $this, $params);
					}
					else{
						$params['result'] = $opData['log']['price'.$i];
						$return .= $this->Core->readBlockAndReplace($tmpl, 'fail', $this, $params);
					}
				}
				elseif($eData['entry_type'] == 'prolong'){					if($e) $return .= $this->Core->readBlockAndReplace($tmpl, 'prolong_result_success', $this, $params);
					else{
						$params['result'] = $opData['log']['price'.$i];
						$return .= $this->Core->readBlockAndReplace($tmpl, 'prolong_result_fail', $this, $params);
					}
				}
			}
		}
		else{
			$params['result'] = $this->getPrintMessages($opData['log']);
			$return = $this->Core->readBlockAndReplace($tmpl, 'global_fail', $this, $params);
		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																			Блокирование услуг

	*********************************************************************************************************************************************************************/

	public function __ava__getSuspendOrder($id){
		/*
			Сведения о заказе на удаление услуги
		*/

		if(empty($this->suspendOrders[$id])) $this->suspendOrders[$id] = $this->DB->rowFetch(array('suspend_service_orders', '*', "`id`='$id'"));
		return $this->suspendOrders[$id];
	}

	public function __ava__addSuspendOrder($sId, $type = '', $reason = ''){
		/*
			Добавляет заявку на удаление
		*/

		$sData = $this->getOrderedService($sId);
		if(!$sData) throw new AVA_Exception('Услуги №'.$sId.' не найдено');
		elseif($sData['step'] == 0){
			$this->setMessage('suspend'.$sId, 'Услуга для '.$sData['ident'].' уже заблокирована', 'error');
			return false;
		}
		elseif($sData['step'] < 0){
			$this->setMessage('suspend'.$sId, 'Услуга для '.$sData['ident'].' удалена', 'error');
			return false;
		}
		elseif($this->DB->cellFetch(array('suspend_service_periods', 'id', "`service_order_id`='$sId' AND `status`=0"))){			$this->setMessage('suspend'.$sId, 'Для '.$sData['ident'].' имеется не закрытый период блокировки', 'error');
			return false;
		}

		$id = $this->DB->Ins(array('suspend_service_orders', array('service_order_id' => $sId, 'date' => time())));
		$this->setSuspendReason($id, $type, $reason);
		return $id;
	}

	public function __ava__setSuspendReason($id, $type = '', $reason = ''){
		/*
			Устанавливает причины блокировки
		*/

		if(!$reason){
			switch($type){
				case 'accord': $reason = 'Добровольный отказ'; break;
				case 'term': $reason = 'Истечение срока оплаты'; break;
				case 'policy': $reason = 'Нарушение действующих правил'; break;
			}
		}
		return $this->DB->Upd(array('suspend_service_orders', array('type' => $type, 'reason' => $reason), "`id`=$id"));
	}

	public function __ava__setSuspendServiceList($service, $idList){
		/*
			Модификация списка услуг
		*/

		$this->DB->Upd(array('suspend_service_orders', array('status' => 1), $this->getEntriesWhere($idList)));
		$idList2 = array();
		$result = array();

		foreach($idList as $i => $e){
			$dData = $this->getSuspendOrder($i);
			$sData = $this->getOrderedService($dData['service_order_id']);

			if($dData['total'] > 0 && !$this->checkBalance($sData['client_id'], $dData['total'], $this->getParamByServiceId($dData['service_order_id'], 'currency'))){
				$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" не заблокирована, т.к. недостаточно средств на балансе', 'error');
			}
			else{
				if(!$sData['server'] || !$e['auto']){
					$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" не заблокирована на сервере, т.к. это не предусмотрено', 'error');
					$result[''][$i] = $e;
				}
				else{
					$e['ident'] = $sData['ident'];
					$idList2[$sData['server']][$i] = $e;
				}
			}
		}

		$sResults = array();
		foreach($idList2 as $i => $e){
			if($i){
				$result[$i] = $this->callServiceObj('suspendAcc', $service, array('accs' => $e, 'server' => $i));
				$sResults[$i] = $this->getConnectionResultId($i);
			}
		}

		$link = $this->path.'?mod='.$this->mod.'&func=connectionResult&id=';
		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){
				if($e1){
					$this->setSuspendService($i1);
					$this->setMessage('suspend'.$dData['service_order_id'], 'Услуга для "'.$sData['ident'].'" заблокирована на сервере.');
				}
				else $this->setMessage('suspend'.$dData['service_order_id'], 'Услуга для "'.$sData['ident'].'" не заблокирована на сервере.', 'error');
				$this->setMessage('suspend'.$dData['service_order_id'], '<a href="'.$link.$sResults[$i].'" target="_blank">Смотреть результат выполнения</a>');
			}
		}
	}

	public function __ava__setSuspendService($id, $date = false){
		/*
			Завершает удаление услуги
		*/

		$dData = $this->getSuspendOrder($id);
		$sData = $this->getOrderedService($dData['service_order_id']);
		if($date === false) $date = time();

		$pId = $this->DB->Ins(array('suspend_service_periods', array('service_order_id' => $dData['service_order_id'], 'run' => $date)));
		$this->DB->Upd(array('suspend_service_orders', array('period_id' => $pId, 'status' => 2), "`id`='$id'"));
		$this->DB->Upd(array('order_services', array('step' => 0, 'suspend_reason' => $dData['type'], 'suspend_reason_descript' => $dData['reason']), "`id`='{$dData['service_order_id']}'"));

		$this->setMessage('suspend'.$dData['service_order_id'], 'Услуга для "'.$sData['ident'].'" заблокирована в биллинге');
		$this->mail($this->Core->getUserEml($this->getUserIdByClientId($sData['client_id'])), $this->getTmplParams('suspendService'), array('data' => $sData, 'values' => $dData));
		return $pId;
	}

	public function __ava__showSuspendConfirmForm($list, $cancelFunc = 'myServices', $delFunc = 'suspendServices2'){
		/*
			Отображает расчет модификации услуги
		*/

		$params = array(
			'orders' => array(),
			'okUrl' => $delFunc ? 'index.php?mod='.$this->mod.'&func='.$delFunc : '',
			'cancelUrl' => $cancelFunc ? 'index.php?mod='.$this->mod.'&func='.$cancelFunc : '',
			'price' => 0
		);

		foreach($list as $i => $e){
			$params['orders'][$i] = array(
				'sData' => $this->getOrderedService($i),
				'dData' => $this->getSuspendOrder($e)
			);

			$params['price'] += $params['orders'][$i]['dData']['total'];
		}

		return $this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl', 'suspend', $this, $params, 'cover');
	}


	/********************************************************************************************************************************************************************

																		Разблокирование услуг

	*********************************************************************************************************************************************************************/

	public function __ava__getUnsuspendOrder($id){
		/*
			Сведения о заказе на удаление услуги
		*/

		if(empty($this->unsuspendOrders[$id])) $this->unsuspendOrders[$id] = $this->DB->rowFetch(array('unsuspend_service_orders', '*', "`id`='$id'"));
		return $this->unsuspendOrders[$id];
	}

	public function __ava__addUnsuspendOrder($sId, $type = '', $reason = ''){
		/*
			Добавляет заявку на удаление
		*/

		$sData = $this->getOrderedService($sId);
		if(!$sData) throw new AVA_Exception('Услуги №'.$sId.' не найдено');
		elseif($sData['step'] == 1){
			$this->setMessage('unsuspend'.$sId, 'Услуга для '.$sData['ident'].' не заблокирована', 'error');
			return false;
		}
		elseif($sData['step'] < 0){
			$this->setMessage('unsuspend'.$sId, 'Услуга для '.$sData['ident'].' удалена', 'error');
			return false;
		}
		elseif(!$pId = $this->DB->cellFetch(array('suspend_service_periods', 'id', "`service_order_id`='$sId' AND `status`=0"))){
			$this->setMessage('suspend'.$sId, 'Для '.$sData['ident'].' не найден открытый период блокировки', 'error');
			return false;
		}

		$id = $this->DB->Ins(array('unsuspend_service_orders', array('service_order_id' => $sId, 'date' => time(), 'period_id' => $pId)));
		$this->setUnsuspendReason($id, $type, $reason);
		return $id;
	}

	public function __ava__setUnsuspendReason($id, $type = '', $reason = ''){
		/*
			Устанавливает причины блокировки
		*/

		if(!$reason){
			switch($type){
				case 'accord': $reason = 'Добровольный отказ'; break;
				case 'term': $reason = 'Истечение срока оплаты'; break;
				case 'policy': $reason = 'Нарушение действующих правил'; break;
			}
		}
		return $this->DB->Upd(array('unsuspend_service_orders', array('type' => $type, 'reason' => $reason), "`id`=$id"));
	}

	public function __ava__setUnsuspendServiceList($service, $idList){
		/*
			Модификация списка услуг
		*/

		$this->DB->Upd(array('unsuspend_service_orders', array('status' => 1), $this->getEntriesWhere($idList)));
		$idList2 = array();
		$result = array();

		foreach($idList as $i => $e){
			$dData = $this->getUnsuspendOrder($i);
			$sData = $this->getOrderedService($dData['service_order_id']);

			if($dData['total'] > 0 && !$this->checkBalance($sData['client_id'], $dData['total'], $this->getParamByServiceId($dData['service_order_id'], 'currency'))){
				$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" не разблокирована, т.к. недостаточно средств на балансе', 'error');
			}
			else{				if(!$sData['server'] || !$e['auto']){
					$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" не разблокирована на сервере, т.к. это не предусмотрено', 'error');
					$result[''][$i] = $e;
				}
				else{
					$e['ident'] = $sData['ident'];
					$idList2[$sData['server']][$i] = $e;
				}
			}
		}

		$sResults = array();
		foreach($idList2 as $i => $e){
			if($i){				$result[$i] = $this->callServiceObj('unsuspendAcc', $service, array('accs' => $e, 'server' => $i));
				$sResults[$i] = $this->getConnectionResultId($i);			}
		}

		$link = $this->path.'?mod='.$this->mod.'&func=connectionResult&id=';
		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){
				if($e1){					$this->setUnsuspendService($i1);					$this->setMessage('unsuspend'.$dData['service_order_id'], 'Услуга для "'.$sData['ident'].'" разблокирована на сервере.');
				}
				else $this->setMessage('unsuspend'.$dData['service_order_id'], 'Услуга для "'.$sData['ident'].'" не разблокирована на сервере.', 'error');
				$this->setMessage('unsuspend'.$dData['service_order_id'], '<a href="'.$link.$sResults[$i].'" target="_blank">Смотреть результат выполнения</a>');
			}
		}
	}

	public function __ava__setUnsuspendService($id, $date = false){
		/*
			Завершает удаление услуги
		*/

		$dData = $this->getUnsuspendOrder($id);
		$sData = $this->getOrderedService($dData['service_order_id']);
		if($date === false) $date = time();

		$this->DB->Upd(array('suspend_service_periods', array('end' => $date, 'status' => 1), "`id`='{$dData['period_id']}'"));
		$this->DB->Upd(array('unsuspend_service_orders', array('status' => 2), "`id`='$id'"));
		$this->DB->Upd(array('order_services', array('step' => 1, 'suspend_reason' => '', 'suspend_reason_descript' => ''), "`id`='{$dData['service_order_id']}'"));

		$this->setMessage('suspend'.$dData['service_order_id'], 'Услуга для "'.$sData['ident'].'" разблокирована в биллинге');
		$this->mail($this->Core->getUserEml($this->getUserIdByClientId($sData['client_id'])), $this->getTmplParams('unsuspendService'), array('data' => $sData, 'values' => $dData));
		return $dData['period_id'];
	}

	public function __ava__showUnsuspendConfirmForm($list, $cancelFunc = 'myServices', $delFunc = 'unsuspendServices2'){
		/*
			Отображает расчет модификации услуги
		*/

		$params = array(
			'orders' => array(),
			'okUrl' => $delFunc ? 'index.php?mod='.$this->mod.'&func='.$delFunc : '',
			'cancelUrl' => $cancelFunc ? 'index.php?mod='.$this->mod.'&func='.$cancelFunc : '',
			'price' => 0
		);

		foreach($list as $i => $e){
			$params['orders'][$i] = array(
				'sData' => $this->getOrderedService($i),
				'dData' => $this->getUnsuspendOrder($e)
			);

			$params['price'] += $params['orders'][$i]['dData']['total'];
		}

		return $this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl', 'unsuspend', $this, $params, 'cover');
	}


	/********************************************************************************************************************************************************************

																			Удаление услуг

	*********************************************************************************************************************************************************************/

	public function __ava__getDeleteOrder($id){		/*
			Сведения о заказе на удаление услуги
		*/

		if(empty($this->deleteOrders[$id])) $this->deleteOrders[$id] = $this->DB->rowFetch(array('delete_service_orders', '*', "`id`='$id'"));
		return $this->deleteOrders[$id];	}

	public function __ava__addDeleteOrder($sId, $type = '', $reason = ''){		/*
			Добавляет заявку на удаление
		*/

		$sData = $this->getOrderedService($sId);
		if(!$sData) throw new AVA_Exception('Услуги №'.$sId.' не найдено');
		elseif($sData['step'] < 0){			$this->setMessage('delete'.$sId, 'Услуга для '.$sData['ident'].' уже удалена', 'error');			return false;		}

		$t = time();
		$id = $this->DB->Ins(array('delete_service_orders', array('service_order_id' => $sId)));

		$this->addDeleteOrderPays($id, $this->calcStay($sId, $t), $this->getDeleteServicePrice($sData['service'], $sData['package']), $t);
		$this->addDeleteOrderReason($id, $type, $reason);
		return $id;	}

	public function __ava__addDeleteOrderPays($id, $stay, $price, $date = false){		/*
			Параметры заявки на удаление (деньги)
		*/

		if($date === false) $date = time();
		$this->DB->Upd(array('delete_service_orders', array('stay' => $stay, 'delete_price' => $price, 'total' => $stay - $price, 'date' => $date), "`id`='$id'"));
	}

	public function __ava__addDeleteOrderReason($id, $type = '', $reason = ''){		/*
			Параметры заявки на удаление
		*/

		if(!$reason){			switch($type){				case 'accord': $reason = 'Добровольный отказ'; break;
				case 'term': $reason = 'Истечение срока оплаты'; break;
				case 'policy': $reason = 'Нарушение действующих правил'; break;
			}		}

		$this->DB->Upd(array('delete_service_orders', array('type' => $type, 'reason' => $reason), "`id`='$id'"));	}

	public function __ava__calcStay($sId, $date = false){		/*
			Расчет остаточной стоимости заказа
		*/
		if($date === false) $date = time();
		$periods = $this->getTermRemainPeriods($sId, $date);
		$return = 0;

		foreach($periods as $i => $e){
			if($i != 'test'){
				foreach($e as $i1 => $e1){
					$eData = $this->getOrderEntry($i1);
					$pkgData = $this->serviceData($eData['service'], $eData['package']);

					$periods[$i][$i1]['term'] = Dates::sec2term($pkgData['base_term'], $e1['to'] - $e1['from'], false);
					$periods[$i][$i1]['price'] = ($eData[$i] + $eData['modify_price']) * $periods[$i][$i1]['term'];
					$return += $periods[$i][$i1]['price'];
				}
			}
		}

		return $return;
	}

	public function __ava__getDeleteServicePrice($service, $pkg){		/*
			Возвращает стоимость удаления (штраф за удаление)
		*/

		$sData = $this->serviceData($service, $pkg);
		return $sData['del_price'];	}

	public function __ava__getActionServiceValues($id, $values = false){		/*
			Возвращает данные для массовых действий над услугами
		*/

		if($values === false) $values = $this->values;
		return array('auto' => !empty($values['auto'.$id]) ? $values['auto'.$id] : false, 'notify' => !empty($values['notify'.$id]) ? $values['notify'.$id] : false);
	}

	public function __ava__setDeleteServiceId($id, $auto = true, $notify = true){		/*
			Удаление по id
		*/

		return $this->setDeleteServiceList($id, array('auto' => $auto, 'notify' => $notify));
	}

	public function __ava__setDeleteServiceList($service, $idList){
		/*
			Удаление списка услуг
		*/

		$this->DB->Upd(array('delete_service_orders', array('status' => 1), $this->getEntriesWhere($idList)." AND `status`<1"));
		$idList2 = array();
		$result = array();

		foreach($idList as $i => $e){
			$dData = $this->getDeleteOrder($i);
			$sData = $this->getOrderedService($dData['service_order_id']);

			if($dData['total'] > 0 && !$this->checkBalance($sData['client_id'], $dData['total'], $this->getParamByServiceId($dData['service_order_id'], 'currency'))){
				$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" не удалена, т.к. недостаточно средств на балансе', 'error');
			}
			else{
				if(!$sData['server'] || !$e['auto']){
					$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" не удалена на сервере, т.к. это не предусмотрено', 'error');
					$result[''][$i] = $e;
				}
				else{
					$e['ident'] = $sData['ident'];					$idList2[$sData['server']][$i] = $e;				}
			}
		}

		foreach($idList2 as $i => $e){
			if($i) $result[$i] = $this->callServiceObj('delAcc', $service, array('accs' => $e, 'server' => $i));
		}

		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){
				if($e1) $this->setDeleteService($i1);
			}
		}
	}

	public function __ava__setDeleteService($id){		/*
			Завершает удаление услуги
		*/

		$dData = $this->getDeleteOrder($id);
		if($dData['status'] > 1) throw new AVA_Exception('Заказ на удаление этой услуги уже выполнен');
		$sData = $this->getOrderedService($dData['service_order_id']);

		$pkgData = $this->serviceData($sData['service'], $sData['package']);
		$this->upBalance($sData['client_id'], $dData['total'], $pkgData['currency'], 'return', $dData['date'], $id, $dData['service_order_id'], 'Возврат средств в связи с удалением услуги', 'delete_service_orders');
		$this->DB->Upd(array('delete_service_orders', array('status' => 2), "`id`='$id'"));

		$this->DB->Upd(array('order_services', array('step' => -1), "`id`='{$dData['service_order_id']}'"));
		$this->setMessage('total', 'Услуга для "'.$sData['ident'].'" удалена в биллинге');
		$this->sendDeleteServiceMails($id);
	}

	public function __ava__getDeleteServiceReasonDescript($reason){		/*
			Возвращает текстовое описание причины удаления
		*/
		switch($reason){
			case 'policy'; return 'нарушение правил';
			case 'term'; return 'истечение срока оплаты';
			case 'accord'; return 'добровольный отказ';
		}
	}

	public function __ava__showDeleteConfirmForm($list, $cancelFunc = 'myServices', $delFunc = 'deleteServices2'){
		/*
			Отображает расчет модификации услуги
		*/

		$params = array(
			'orders' => array(),
			'okUrl' => $delFunc ? 'index.php?mod='.$this->mod.'&func='.$delFunc : '',
			'cancelUrl' => $cancelFunc ? 'index.php?mod='.$this->mod.'&func='.$cancelFunc : '',
			'price' => 0
		);

		foreach($list as $i => $e){			$params['orders'][$i] = array(
				'sData' => $this->getOrderedService($i),
				'dData' => $this->getDeleteOrder($e)
			);

			$params['price'] += $params['orders'][$i]['dData']['total'];
		}

		return $this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl', 'delete', $this, $params, 'cover');
	}


	/********************************************************************************************************************************************************************

																			Передача услуг в другой аккаунт

	*********************************************************************************************************************************************************************/

	public function __ava__getTransmitOrder($id){
		/*
			Сведения о заказе на смену владельца услуги
		*/

		if(empty($this->transmitOrders[$id])) $this->transmitOrders[$id] = $this->DB->rowFetch(array('transmit_service_orders', '*', "`id`='$id'"));
		return $this->transmitOrders[$id];
	}

	public function __ava__addTransmitOrder($sId, $newClientId){
		/*
			Добавляет заявку на удаление
		*/

		$sData = $this->getOrderedService($sId);
		$cData = $this->getClientData($newClientId);

		if(!$sData) throw new AVA_Exception('Услуги №'.$sId.' не найдено');
		elseif($sData['step'] < 0){
			$this->setMessage('transmit'.$sId, 'Услуга для '.$sData['ident'].' удалена. Нельзя передать удаленную услугу.', 'error');
			return false;
		}
		elseif(!$cData) throw new AVA_Exception('Клиент не найден');
		elseif($sData['client_id'] == $newClientId){
			$this->setMessage('transmit'.$sId, 'Старый и новый клиенты для "'.$sData['ident'].'" совпадают. Передача не выполнялась.', 'error');
			return false;
		}

		$id = $this->DB->Ins(array('transmit_service_orders', array('service_order_id' => $sId, 'old_client_id' => $sData['client_id'], 'new_client_id' => $newClientId, 'date' => time())));
		return $id;
	}

	public function __ava__endTransmitOrder($tId){		/*
			Меняет владельца услуги
		*/

		$tData = $this->getTransmitOrder($tId);
		$sData = $this->getOrderedService($tData['service_order_id']);

		$this->DB->Upd(array('transmit_service_orders', array('status' => 1), "`id`='$tId'"));
		$this->DB->Upd(array('order_services', array('client_id' => $tData['new_client_id']), "`id`='{$tData['service_order_id']}'"));
		$this->setMessage('transmit'.$tId, 'Услуга для "'.$sData['ident'].'" передана.');
	}















































































































	public function getPkgParams($service, $type, $varPrefix = '', $varPostfix = '', $cp = array(), &$types = array(), $params = false){
		/*
			Создает список параметров для пакета
		 */

		$typeFilter = '';
		if($type) $typeFilter = " AND `$type`>0";
		if($params === false) $params = $this->values;

		$list = $this->DB->columnFetch(array('package_descripts', 'type', 'name', $this->getPkgDscFilter($cp)."`service`='".db_main::Quot($service)."' AND `show`{$typeFilter}", '`sort`'));
		$return = array();

		foreach($list as $i => $e){
			$return[$i] = isset($params[$varPrefix.$i.$varPostfix]) ? $params[$varPrefix.$i.$varPostfix] : '';
			$types[$i] = $e;
		}

		return $return;
	}

	public function getPkgBase($vars, &$matrix, $prefix = 'mpkg_', $postfix = ''){
		/*
			Возврщает базовое описание пакета
		*/

		$return = array();
		foreach($vars as $i => $e){
			if(!empty($e) && !empty($matrix[$prefix.$i.$postfix]['text']) && !empty($matrix[$prefix.$i.$postfix]['type'])){
				$return[] = array('type' => $matrix[$prefix.$i.$postfix]['type'], 'text' => $matrix[$prefix.$i.$postfix]['text'], 'value' => $e);
				if($matrix[$prefix.$i.$postfix]['type'] == 'checkbox' || $this->isUnlimit($e)) unset($matrix[$prefix.$i.$postfix]);
			}
		}

		return $return;
	}

	public function __ava__getBasePkgDescript($base, $cnt = 0){
		/*
			Возвращает базовый расчет
		*/

		return $base ? $this->Core->readBlockAndReplace($this->Core->getModuleTemplatePath($this->mod).'constructor.tmpl', 'base', $this, array('base_limits' => $base, 'cnt' => $cnt), 'extra') : '';
	}

	protected function __ava__getCreateData($serviceData, $params = false, $id = '', $prefix = 'acc_'){		/*
			Возвращает параметры создания услуги
		*/

		$pkgData = $this->serviceData($serviceData['service'], $serviceData['package']);

		return array(
			'clientData' => $this->getUserByClientId($serviceData['client_id']),
			'pkgData' => $pkgData,
			'installValues' => $this->sumParams($pkgData['vars'], $serviceData['vars'], $pkgData['service']),
			'installData' => Library::array_merge($this->getPkgParams($pkgData['service'], 'aacc', $prefix, $id, array($this->getConnectionCp($serviceData['server']))), $params),
			'id' => $id,
			'prefix' => $prefix,
			'serviceId' => $serviceData['id'],
			'server' => $serviceData['server'],
			'modify' => (bool)$serviceData['vars']
		);	}



	public function __ava__prolongService1($clientId, $serviceId, $server, $sum, $paidDate, $paidTo, $auto, $entryId, $data = false, $installParams = false, $byCron = false){
		/*
			Продляет услугу
		*/

		$serviceData = $this->getOrderedService($serviceId);
		$pkgData = $this->serviceData($serviceData['service'], $serviceData['package']);
		if($installParams === false) $installParams = $this->values;
		if($data === false) $data = $serviceData;

		if(!$server || !$auto){
			$this->messages[] = array('service', 'error', 'Услуга "'.$pkgData['service_textname'].'" по тарифу "'.$pkgData['text'].'" для "'.$serviceData['ident'].'" не продлена на удаленном сервере, т.к. это не предусмотрено")');
		}
		elseif($this->Core->getParam('addAccsWithQueue', $this->mod) && $auto && $server && !$byCron){
			Cron::addTask(
				'$modObj = $GLOBALS["Core"]->callModule("'.$this->mod.'");'."\n".
				'$modObj->values = Library::unserialize(\''.regExp::Slashes(Library::serialize($this->values)).'\');'."\n".
				'$modObj->prolongService("'.$clientId.'", "'.$serviceId.'", "'.$server.'", "'.$sum.'", "'.$paidDate.'", "'.$paidTo.'", "'.$auto.'", "'.$entryId.'", Library::unserialize("'.Library::serialize($data).'"), Library::unserialize("'.Library::serialize($installParams).'"), true);',
				'Продление услуги "'.$sData['text'].'" по тарифу "'.$pkgData['text'].'" для "'.$data['ident'].'"'
			);

			$this->messages[] = array('service', 'success', '{Call:Lang:modules:billing:uslugapotari:'.Library::serialize(array($sData['text'], $pkgData['text'], $data['ident'])).'}');
			return true;
		}
		elseif($this->callServiceObj('prolongAcc', $serviceData['service'], $this->getCreateData($serviceData, $installParams, $entryId), array('id' => $serviceId, 'type' => 'order_services'))){
			$this->messages[] = array('service', 'success', "Услуга {$pkgData['service_textname']} по тарифу {$pkgData['text']} для {$serviceData['ident']} продлена");
		}
		else{
			$this->messages[] = array('service', 'error', "Услуга {$pkgData['service_textname']} по тарифу {$pkgData['text']} для {$serviceData['ident']} не продлена");
			return false;
		}

		$this->DB->Upd(
			array(
				'order_services',
				array(
					'last_paid' => $paidDate,
					'paid_to' => $paidTo,
					'all_payments' => "`all_payments` + ".$sum,
					'in_test' => 0,
					'#isExp' => array('all_payments' => true)
				),
				"`id`='$serviceId'"
			)
		);

		$this->upBalance($clientId, -$sum, $pkgData['currency'], 'service', $paidDate, $serviceId);
		$this->messages[] = array('service', 'success', '{Call:Lang:modules:billing:vnesenysvede1:'.Library::serialize(array($pkgData['service_textname'], $pkgData['text'], $serviceData['ident'])).'}');
		$this->serviceHistory($serviceId, 'prolong', 'Услуга продлена до "'.Dates::dateTime($paidTo).'"');
		$userEml = $this->getClientEml($clientId);

		if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal'){
			$userTmpl = $pkgData['notify_rights']['mail_tmpl_new'];
			if(empty($pkgData['notify_rights']['notify_new'])) $userEml = false;
		}
		else $userTmpl = 'prolongService';

		if(!empty($userEml)) $this->mail($userEml, $this->getTmplParams($userTmpl), array('data' => $data, 'installParams' => $installParams));
		return true;
	}


	/********************************************************************************************************************************************************************

															Формирвание данных по валютам и способам оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__getMainCurrencyName(){		$this->fetchCurrencies();
		return $this->currencyList[$this->defaultCurrency];	}

	public function __ava__defaultCurrency(){		/*
			Возвращает валюту по умолчанию
		*/

		$this->fetchCurrencies();
		return $this->defaultCurrency;	}

	public function __ava__currencyName($name){
		/*
			Возвращает имя валюты по идентификатору
		*/

		$this->fetchCurrencies();
		if(!$name) return $this->currencyList[$this->defaultCurrency];
		else return $this->currencyList[$name];
	}

	public function __ava__getCurrency(){		/*
			Возвращает список всех валют
		*/

		$this->fetchCurrencies();
		return $this->currencyList;
	}
	public function __ava__currencyParams($cur){		/*
			Возвращает все параметры валюты
		 */

		if(!$cur) $cur = $this->defaultCurrency();
		else $this->fetchCurrencies();

		if(!is_string($cur)) throw new AVA_Exception('qqqq');

		return $this->currencyParams[$cur];
	}

	public function __ava__getCurrencyNameByPkg($service, $pkg){		/*
			Возвращает имя валюты для пакета
		*/

		$this->fetchServicesData($service);
		return empty($this->servicesData[$service][$pkg]['currency']) ? $this->getMainCurrencyName() : $this->currencyName($this->servicesData[$service][$pkg]['currency']);	}

	public function __ava__fetchCurrencies(){		/*
			Извлекает сведения обо всех валютах
		*/

		if(empty($this->currencyParams)){			$this->currencyParams = $this->DB->columnFetch(array('currency', '*', 'name', '', "`sort`"));
			foreach($this->currencyParams as $i => $e){				$this->currencyList[$i] = $e['text'];
				if($e['default']){					$this->defaultCurrency = $i;
				}			}
		}

		return $this->currencyParams;	}

	public function __ava__getPayment(){
		/*
			Возвращает список всех способов оплаты
		*/

		$this->fetchPayments();
		return $this->paymentList;
	}

	public function __ava__paymentParams($payment){
		/*
			Возвращает все параметры спопоба оплаты
		 */

		$this->fetchPayments();
		return $this->paymentParams[$payment];
	}

	public function __ava__paymentExtension($payment){
		/*
			Возвращает расширение способа оплаты
		 */

		$this->fetchPayments();
		return $this->paymentParams[$payment]['extension'];
	}

	public function __ava__paymentsByExtension($extension){
		/*
			Возвращает все способы оплаты под данное расширение
		 */

		$this->fetchPayments();
		return $this->paymentListByType[$extension];
	}

	public function __ava__currencyByPayment($payment){		/*
			Возвращает данные валюты по способу оплаты
		*/
		$this->fetchCurrencies();
		$this->fetchPayments();
		if(!$cName = $this->paymentParams[$payment]['currency']) $cName = $this->defaultCurrency();
		return $this->currencyParams[$cName];
	}

	public function __ava__currencyNameByPayment($payment){
		/*
			Возвращает данные валюты по способу оплаты
		*/

		$cParams = $this->currencyByPayment($payment);
		return $cParams['name'];
	}

	public function __ava__fetchPayments($onlyShowed = false){
		/*
			Извлекает сведения обо всех валютах
		*/

		if(!is_array($this->paymentParams)){
			$this->paymentParams = $this->DB->columnFetch(array('payments', '*', 'name', '', "`sort`"));
			foreach($this->paymentParams as $i => $e){				$this->paymentParams[$i]['vars'] = Library::unserialize($this->paymentParams[$i]['vars']);
				$this->paymentList[$i] = $e['text'];
				$this->paymentListByType[$e['extension']][$i] = $e['text'];
			}
		}

		if($onlyShowed){
			$return = array();			foreach($this->paymentParams as $i => $e) if($e['show']) $return[$i] = $e;
			return $return;		}

		return $this->paymentParams;
	}

	public function __ava__getSms(){
		/*
			Возвращает список всех способов оплаты через sms
		*/

		$this->fetchSms();
		return $this->smsList;
	}

	public function __ava__smsParams($sms){
		/*
			Возвращает все параметры спопоба оплаты
		 */

		$this->fetchSms();
		return $this->smsParams[$sms];
	}

	public function __ava__smsParamsById($id){
		/*
			Возвращает все параметры спопоба оплаты
		 */

		$this->fetchSms();
		return $this->smsParamsById[$id];
	}

	public function __ava__smsExtension($sms){
		/*
			Возвращает расширение способа оплаты
		 */

		$this->fetchSms();
		return $this->smsParams[$sms]['extension'];
	}

	public function __ava__smsByExtension($extension){
		/*
			Возвращает все способы оплаты под данное расширение
		 */

		$this->fetchSms();
		return $this->smsListByType[$extension];
	}

	public function __ava__getSmsNumbers(){		$this->fetchSmsNumbers();
		return $this->smsNumbers;	}

	public function __ava__getSmsNumbersByAgr($agr = false){
		$this->fetchSmsNumbers();
		return $agr ? $this->smsNumbersByAgr[$agr] : $this->smsNumbersByAgr;
	}

	public function __ava__getSmsNumberParams($num){
		$this->fetchSmsNumbers();
		return $this->smsNumberParams[$num];
	}

	public function __ava__getSmsNumberParamsByNum($agr, $num){
		$this->fetchSmsNumbers();
		return $this->smsNumberParamsByNum[$agr][$num];
	}

	public function __ava__currencyBySmsNumber($num){
		$this->fetchSmsNumbers();
		return $this->smsNumberParams[$num]['currency'];
	}

	private function fetchSmsNumbers(){		/*
			Извлекает номера SMS
		*/

		if($this->smsNumberParams === false){			$this->fetchSms();
			$this->smsNumberParams = $this->DB->columnFetch(array('sms_numbers', '*', 'id', '', "`sort`"));

			foreach($this->smsNumberParams as $i => $e){				$this->smsNumbers[$i] = $e['number'].' ('.$e['sum'].' '.$this->currencyName($e['currency']).')';
				$this->smsNumbersByAgr[$e['sms']][$i] = $this->smsNumbers[$i];
				$this->smsNumberParamsByNum[$e['sms']][$e['number']] = $e;
			}
		}
	}

	public function __ava__fetchSms($onlyShowed = false){
		/*
			Извлекает сведения обо всех валютах
		*/

		if(!is_array($this->smsParams)){
			$this->smsParams = $this->DB->columnFetch(array('sms', '*', 'name', '', "`sort`"));
			foreach($this->smsParams as $i => $e){
				$this->smsParams[$i]['vars'] = Library::unserialize($this->smsParams[$i]['vars']);
				$this->smsParamsById[$e['id']] = $this->smsParams[$i];
				$this->smsList[$i] = $e['text'];
				$this->smsListByType[$e['extension']][$i] = $e['text'];
			}
		}

		if($onlyShowed){
			$return = array();
			foreach($this->smsParams as $i => $e) if($e['show']) $return[$i] = $e;
			return $return;
		}

		return $this->smsParams;
	}

	public function __ava__callPaymentExtensionByPayId($payId, $func, $params = array()){		if($payId) $payParams = $this->paymentParams($this->values['payId']);
		else $payParams = array('extension' => false);
		return $this->callPaymentExtension($payParams['extension'], $func, $payId, $params);
	}

	public function __ava__callPaymentExtension($ext, $func, $payId, $params = array()){		return $this->callExtension($ext, $func, $payId, 'paymentsObject', 'pay', 'payments/', $params);	}

	public function __ava__callSmsExtension($ext, $func, $payId, $params = array()){
		return $this->callExtension($ext, $func, $payId, 'smsObject', 'sms', 'sms/', $params);
	}

	public function __ava__callExtension($ext, $func, $payId, $objName, $prefix, $path, $params = array()){
		/*
			Обращение к функции расширения для платежей
		*/

		if($ext){
			$ext = $prefix.$ext;
			$this->Core->loadExtension('billing', $path.$ext);
			$pObj = new $ext($this, $payId);
		}
		else{
			$this->Core->loadExtension('billing', $objName);
			$pObj = new paymentsObject($this, $payId);
		}

		if(method_exists($pObj, $func) || method_exists($pObj, '__ava__'.$func)) return $pObj->$func($params);
		else return false;
	}

	public function __ava__getPayLogErrorMsg($error){		switch($error){
			case 2: $errorMsg = '{Call:Lang:modules:billing:neopredelenn}'; break;
			case 4: $errorMsg = '{Call:Lang:modules:billing:netdostupakp1}'; break;
			case 10: $errorMsg = '{Call:Lang:modules:billing:nepravilnyjk}'; break;
			case 11: $errorMsg = '{Call:Lang:modules:billing:zaprosnesush}'; break;
			case 12: $errorMsg = '{Call:Lang:modules:billing:idschetanena}'; break;
			case 20: $errorMsg = '{Call:Lang:modules:billing:tranzaktsiia}'; break;
			case 21: $errorMsg = '{Call:Lang:modules:billing:tranzaktsiia1}'; break;
			case 30: $errorMsg = '{Call:Lang:modules:billing:nekorrektnyj2}'; break;
		}

		return $errorMsg;
	}

	public function __ava__savePayLog($trId, $params, $output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Сохраняет параметры запроса
		*/

		if($error && !$errorMsg){
			$errorMsg = $this->getPayLogErrorMsg($error);
		}

		return $this->DB->Ins(
			array(
				'payment_log',
				array(
					'pay_id' => $this->values['payId'],
					'transaction_id' => $trId,
					'date' => time(),
					'ip' => $this->Core->getGPCVar('s', 'REMOTE_ADDR'),
					'params' => $params,
					'output' => $output,
					'answer_status' => $answerStatus,
					'transaction_status' => $transactionStatus,
					'error' => $error,
					'error_msg' => $errorMsg
				)
			)
		);
	}

	protected function __ava__saveExtraLog($id, $out){
		/*
			Сохраняет все че еще было подготовлено к выводу помимо основного вывода
		*/

		return $this->DB->Upd(array('payment_log', array('extra_output' => $out), "`id`='$id'"));
	}

	public function __ava__getSumInDefault($sum, $currency){		/*
			Возвращает стоимость в валюте по умолчанию
		*/

		$params = $this->currencyParams($currency);
		return round($sum / $params['exchange'], 2);	}

	public function __ava__getSumInCurrency($sum, $currency){
		/*
			Пересчитыает сумму из валюты по умолчанию в искомую
		*/

		$params = $this->currencyParams($currency);
		return round($sum * $params['exchange'], 2);
	}

	public function __ava__convertCurrency($sum, $currencyIn, $currencyOut){
		/*
			Пересчитыает сумму из одной валюты в другую
		*/

		if($currencyIn == $currencyOut) return $sum;
		elseif(!$currencyIn) return $this->getSumInCurrency($sum, $currencyOut);
		elseif(!$currencyOut) return $this->getSumInDefault($sum, $currencyIn);

		$paramsIn = $this->currencyParams($currencyIn);
		$paramsOut = $this->currencyParams($currencyOut);
		return $sum / $paramsIn['exchange'] * $paramsOut['exchange'];
	}



	/********************************************************************************************************************************************************************

																		Обслуживание соебинений

	*********************************************************************************************************************************************************************/

	private function fetchConnections(){		/*
			Извлекает все соединения
		*/

		if($this->connectionParams === false){
			$this->fetchServicesData();
			$sec = $this->DB->columnFetch(array('service_extensions_connect', '*', 'mod', "", "`sort`"));
			$this->connectionParams = $this->DB->columnFetch(array('connections', '*', 'name', "", "`sort`"));

			foreach($sec as $i => $e){				$sec[$i]['extra'] = Library::unserialize($e['extra']);
				$this->connectionMods[$e['mod']] = $e['name'];
				$this->connectionModServices2[$e['service']][$e['mod']] = $e['name'];
			}

			foreach($this->connectionParams as $i => $e){				$this->connectionParams[$i] = Library::arrayMergeWithPrefix($this->connectionParams[$i], array('ex_' => $sec[$e['extension']]));
				$this->connectionParams[$i]['vars'] = Library::unserialize($e['vars']);
				$this->connectionParams[$i]['pwd'] = Library::decrypt($e['pwd']);
				$this->connectionCp[$i] = $e['extension'];
			}

			foreach($this->servicesData2 as $i => $e){
				if($e['extension']){					$this->connectionModServices[$e['extension']] = array();
					$mod = $this->Core->getModuleTechName($e['extension']);

					foreach($this->connectionParams as $i1 => $e1){						if($e1['ex_service'] == $mod){							$this->connectionModServices[$e['extension']][$i1] = $e1['text'];						}					}
				}
			}

			foreach($this->servicesData as $i => $e){
				foreach($e as $i1 => $e1){
					if(!$e1['server']) continue;
					$this->connectionsByPkg[$i][$e1['server_name']][$e1['server']] = isset($this->connectionParams[$e1['server']]['text']) ? $this->connectionParams[$e1['server']]['text'] : '';
				}
			}
		}	}

	public function __ava__loadServerConnectById($serverId, $mod, $sObj){
		if(!$serverId) return true;
		$this->fetchConnections();
		if(!isset($this->connectionParams[$serverId])) throw new AVA_Exception('Не существует подключенного сервера "'.$serverId.'"');
		return $this->loadServerConnect($serverId, $mod, $this->connectionParams[$serverId]['extension'], $this->connectionParams[$serverId], $sObj);
	}

	public function __ava__loadServerConnect($serverId, $mod, $extension, $params = array(), $sObj = false){		$exName = 'servconnect'.$extension;
		$this->Core->loadExtension($mod, $exName);
		$this->connections[$serverId] = new $exName($params, $serverId, $extension, $this, $sObj);
		return $this->connections[$serverId];
	}

	public function __ava__getConnectionName($server){
		/*
			Возвращает имя соединения
		*/

		$this->fetchConnections();
		return $this->connectionParams[$server]['text'];
	}

	public function __ava__getConnectionParams($server){		/*
			Возвращает параметры соединения
		*/

		$this->fetchConnections();
		return $this->connectionParams[$server];
	}

	public function __ava__getConnectionCp($serverId = false){
		/*
			Возвращает параметры соединения
		*/

		$this->fetchConnections();
		if($serverId === false) return $this->connectionCp;
		return empty($this->connectionCp[$serverId]) ? '' : $this->connectionCp[$serverId];
	}

	public function __ava__getConnectionResultId($serverId){		return empty($this->connections[$serverId]) ? 0 : $this->connections[$serverId]->getConnectionResultId();
	}

	public function __ava__getConnectMods($service = false){		/*
			Список расширений по модулю
		*/

		$this->fetchConnections();
		return $service ? $this->connectionModServices2[$service] : $this->connectionMods;
	}

	public function __ava__getConnectModName($mod){
		/*
			Список расширений по модулю
		*/

		$this->fetchConnections();
		return $this->connectionMods[$mod];
	}

	public function __ava__getServiceByServer($server, $pkgName){
		/*
			Выявляет имя пакета и услуги по имени сервера и пакета на сервере
		*/

		$this->fetchServicesData();
		return $this->servicesByServer[$server][$pkgName];
	}

	public function __ava__getConnectionsByPkg($service, $pkg){
		/*
			Возвращает список всех серверов к которым можно подключиться с данным пакетом
		*/

		$this->fetchConnections();
		return empty($this->connectionsByPkg[$service][$pkg]) ? array() : $this->connectionsByPkg[$service][$pkg];
	}

	public function __ava__getConnections($sExt){
		/*
			Возвращает все соединения способные работать с указанным модулем
		*/

		$this->fetchConnections();
		return empty($this->connectionModServices[$sExt]) ? array() : $this->connectionModServices[$sExt];
	}


	/********************************************************************************************************************************************************************

																	Формирвание данных по услугам

	*********************************************************************************************************************************************************************/

	private function fetchComplex(){		if($this->complexParams === false){			$this->complexParams = $this->DB->columnFetch(array('complex', '*', 'name', '', '`sort`'));
			foreach($this->complexParams as $i => $e){				$this->complexParams[$i]['vars'] = Library::unserialize($this->complexParams[$i]['vars']);
				$this->complexList[$i] = $e['text'];
				if($e['show']) $this->openComplexList[$i] = $e['text'];			}		}	}

	public function __ava__getComplexParams($complex = false){		$this->fetchComplex();
		return $complex === false ? $this->complexParams : $this->complexParams[$complex];	}

	public function __ava__getComplexList($open = false){
		$this->fetchComplex();
		return $open ? $this->openComplexList : $this->complexList;
	}

	public function __ava__getTermsList($service, $pkg, $clientId = false){
		/*
			Возвращает списко всех сроков взятых из terms в соответствии с base
		*/

		if(empty($this->terms[$service][$pkg])){
			$this->fetchServicesData($service);
			foreach($this->servicesData[$service][$pkg]['terms'] as $i => $e){
				$this->terms[$service][$pkg][$e] = Dates::rightCaseTerm($this->servicesData[$service][$pkg]['base_term'], $e);
			}

			if($this->canUseTest($service, $pkg, $clientId)){
				$this->terms[$service][$pkg][0] = '{Call:Lang:modules:billing:tolkotest:'.Library::serialize(array(Dates::rightCaseTerm($this->servicesData[$service][$pkg]['test_term'], $this->servicesData[$service][$pkg]['test']))).'}';
			}
		}

		return $this->terms[$service][$pkg];
	}

	public function __ava__getProlongTermsList($service, $pkg){
		/*
			Возвращает списко всех сроков взятых из terms в соответствии с base
		*/

		if(empty($this->prolongTerms[$service][$pkg])){
			if($this->servicesData === false){
				$this->fetchServicesData($service);
			}

			foreach($this->servicesData[$service][$pkg]['prolong_terms'] as $i => $e){
				$this->prolongTerms[$service][$pkg][$e] = Dates::rightCaseTerm($this->servicesData[$service][$pkg]['base_term'], $e);
			}
		}

		return $this->prolongTerms[$service][$pkg];
	}

	public function __ava__pkgIsOrdered($service, $pkg, $userId = false){
		/*
			Проверяет что услугу по этому тарифу можно заказать
		*/
		$params = $this->serviceData($service, $pkg);		if(empty($params['rights']['new'])) return false;
		if($userId === false) $userId = $this->Core->getUserId();

		if(!empty($params['restrictions'])){
			$t = time();
			if($params['restrictions']['time_restrict'] && ($params['restrictions']['time_restrict'] > $t)) return false;
			if($params['restrictions']['time_restrict_to'] && ($params['restrictions']['time_restrict_to'] < $t)) return false;

			if(!empty($params['restrictions']['user_types_restrict']) && $userId){
				$userData = $this->Core->getUserParamsById($userId);
				$byTypes = true;
				$byGroups = true;
				$byLoyaltyLevels = true;

				if(!empty($params['restrictions']['user_types_restrict']['types']) && empty($params['restrictions']['types_restrict'][$userData['type']])) $byTypes = false;
				if(!empty($params['restrictions']['user_types_restrict']['groups']) && empty($params['restrictions']['groups_restrict'][$userData['group']])) $byGroups = false;
				if(!empty($userData['clientData']) && !empty($params['restrictions']['user_types_restrict']['client_levels']) && empty($params['restrictions']['client_levels_restrict'][$userData['clientData']['loyalty_level']])) $byLoyaltyLevels = false;

				if($params['restrictions']['user_types_restrict_logic'] == 'OR' && (($byTypes && !empty($params['restrictions']['user_types_restrict']['types'])) || ($byGroups && !empty($params['restrictions']['user_types_restrict']['groups'])) || ($byLoyaltyLevels && !empty($params['restrictions']['user_types_restrict']['client_levels'])))) return true;
				elseif($params['restrictions']['user_types_restrict_logic'] == 'AND' && $byTypes && $byGroups && $byLoyaltyLevels) return true;
				else return false;
			}
		}

		return true;
	}

	public function __ava__fetchServicesData($service = ''){		/*
			Извлекает все данные по услугам и по всем отнесенным к ним тарифам
		*/

		if((!$service && ($this->servicesData === false)) || ($service && empty($this->servicesData2[$service]))){			$this->servicesData = array();
			$this->servicesList = array();
			$this->servicesData2 = array();
			$this->servicePackages = array();
			$p = $this->DB->getPrefix();
			$t1 = $p.'services';
			$t2 = $p.'service_extensions';

			$obj = $this->DB->Req("SELECT t1.id, t1.name, t1.text, t1.extension, t1.type, t1.base_term, t1.test_term, t1.vars, t1.show
				FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.extension=t2.name ORDER BY t1.sort"
			);

			while($r = $obj->Fetch()){
				$this->servicesListById[$r['id']] = $r['text'];
				$this->servicesList[$r['name']] = $r['text'];
				$this->servicesData2[$r['name']] = Library::array_merge(Library::unserialize($r['vars']), $r);

				if($r['extension']){					$this->servicesListByIdByExtension[$r['extension']][$r['id']] = $r['text'];
					$this->servicesListByExtension[$r['extension']][$r['name']] = $r['text'];
				}
			}

			foreach($this->DB->columnFetch(array('order_packages', '*', "id", "", "`sort`")) as $i => $r){				$r['vars'] = Library::unserialize($r['vars']);
				$r['rights'] = empty($r['vars']['rights']) ? array() : $r['vars']['rights'];
				$r['notify_rights'] = empty($r['vars']['notify_rights']) ? array() : $r['vars']['notify_rights'];

				$r['restrictions'] = empty($r['vars']['restrictions']) ? array() : $r['vars']['restrictions'];
				$r['extraDescript'] = empty($r['vars']['extraDescript']) ? array() : $r['vars']['extraDescript'];
				$r['vars'] = empty($r['vars']['params']) ? array() : $r['vars']['params'];

				$r['groups'] = Library::str2arrKeys($r['groups']);
				$r['terms'] = regExp::Split(',', $r['terms']);
				$r['prolong_terms'] = regExp::Split(',', $r['prolong_terms']);

				if(!$r['currency']) $r['currency'] = $this->defaultCurrency();
				if($r['server']) $this->servicesByServer[$r['server']][$r['server_name']] = array( 'service' => $r['service'], 'pkg' => $r['name'] );

				$r['service_id'] = $this->servicesData2[$r['service']]['id'];
				$r['service_name'] = $this->servicesData2[$r['service']]['name'];
				$r['service_textname'] = $this->servicesData2[$r['service']]['text'];

				$r['extension'] = $this->servicesData2[$r['service']]['extension'];
				$r['service_type'] = $this->servicesData2[$r['service']]['type'];
				$r['service_modify_type'] = isset($this->servicesData2[$r['service']]['modify_type']) ? $this->servicesData2[$r['service']]['modify_type'] : '';

				$r['service_modify_type2'] = isset($this->servicesData2[$r['service']]['modify_type2']) ? $this->servicesData2[$r['service']]['modify_type2'] : '';
				$r['service_modify_minus'] = isset($this->servicesData2[$r['service']]['modify_minus']) ? $this->servicesData2[$r['service']]['modify_minus'] : '';
				$r['service_modify_install_type'] = isset($this->servicesData2[$r['service']]['modify_install_type']) ? $this->servicesData2[$r['service']]['modify_install_type'] : '';

				$r['service_modify_test_type'] = isset($this->servicesData2[$r['service']]['modify_test_type']) ? $this->servicesData2[$r['service']]['modify_test_type'] : '';
				$r['service_modify_discount_type'] = isset($this->servicesData2[$r['service']]['modify_discount_type']) ? $this->servicesData2[$r['service']]['modify_discount_type'] : '';
				$r['service_modify_price_type'] = isset($this->servicesData2[$r['service']]['modify_price_type']) ? $this->servicesData2[$r['service']]['modify_price_type'] : '';

				$r['service_modify_price_type_discount'] = isset($this->servicesData2[$r['service']]['modify_price_type_discount']) ? $this->servicesData2[$r['service']]['modify_price_type_discount'] : '';
				$r['service_invoice_type'] = isset($this->servicesData2[$r['service']]['invoice_type']) ? $this->servicesData2[$r['service']]['invoice_type'] : '';

				$r['base_term'] = $this->servicesData2[$r['service']]['base_term'];
				$r['test_term'] = $this->servicesData2[$r['service']]['test_term'];
				$r['show'] = $this->servicesData2[$r['service']]['show'];

				if($r['groups']) foreach($r['groups'] as $i1 => $e1) $this->servicePackagesByGrp[$r['service']][$i1][$r['name']] = $r['text'];
				else $this->servicePackagesByGrp[$r['service']][''][$r['name']] = $r['text'];
				$this->servicePackagesByMainGrp[$r['service']][$r['main_group']][$r['name']] = $r['text'];

				$this->servicesData[$r['service']][$r['name']] = $r;
				$this->servicePackages[$r['service']][$r['name']] = $r['text'];
				$this->servicePackagesBySN[$r['service']][$r['server_name']] = $r['server_name'];
				$this->servicePackageNamesBySN[$r['service']][$r['server_name']][$r['name']] = $r['text'];
			}
		}

		return $service ? (empty($this->servicesData[$service]) ? array() : $this->servicesData[$service]) : $this->servicesData;
	}

	public function __ava__fetchServicesData2(){		$this->fetchServicesData();
		return $this->servicesData2;
	}

	public function __ava__getServices(){
		/*
			Список всех услуг
		*/

		$this->fetchServicesData();
		return $this->servicesList;
	}

	public function __ava__getServicesById(){
		/*
			Список всех услуг
		*/

		$this->fetchServicesData();
		return $this->servicesListById;
	}

	public function __ava__getServicesByExtension($ext){
		/*
			Список всех услуг
		*/

		$this->fetchServicesData();
		return $this->servicesListByExtension[$ext];
	}

	public function __ava__getServiceExtension($service){
		/*
			Расширение услуги по ее имени
		*/

		$this->fetchServicesData();
		return $this->servicesData2[$service]['extension'];
	}

	public function __ava__getServicesByIdByExtension($ext){
		/*
			Список всех услуг
		*/

		$this->fetchServicesData();
		return $this->servicesListByIdByExtension[$ext];
	}

	public function __ava__getServiceName($service){
		/*
			Имя услуг
		*/

		$this->fetchServicesData();
		return $this->servicesList[$service];
	}

	public function __ava__getPackages($service){
		/*
			Список пакетов по услуге
		*/

		$this->fetchServicesData($service);
		return empty($this->servicePackages[$service]) ? array() : $this->servicePackages[$service];
	}

	public function __ava__getPackagesByGrp($service, $grp){
		/*
			Список пакетов по услуге
		*/

		$this->fetchServicesData($service);
		return empty($this->servicePackagesByGrp[$service][$grp]) ? array() : $this->servicePackagesByGrp[$service][$grp];
	}

	public function __ava__getPackagesByMainGrp($service, $grp){
		/*
			Список пакетов по услуге
		*/

		$this->fetchServicesData($service);
		return empty($this->servicePackagesByMainGrp[$service][$grp]) ? array() : $this->servicePackagesByMainGrp[$service][$grp];
	}

	public function __ava__getPackageName($service, $pkg){
		/*
			Список пакетов по услуге
		*/

		$this->fetchServicesData($service);
		return empty($this->servicePackages[$service][$pkg]) ? false : $this->servicePackages[$service][$pkg];
	}

	public function __ava__getAllPackages(){
		/*
			Список пакетов по всем услугам
		*/

		$this->fetchServicesData();
		return $this->servicePackages;
	}

	public function __ava__getServerPackages($service){
		/*
			Список имен пакетов на сервере по услуге
		*/

		$this->fetchServicesData($service);
		return empty($this->servicePackagesBySN[$service]) ? array() : $this->servicePackagesBySN[$service];
	}

	public function __ava__getServerPackagesByPkg($service, $pkg){
		/*
			Список имен пакетов на сервере по услуге
		*/

		$this->fetchServicesData($service);
		return empty($this->servicePackageNamesBySN[$service][$pkg]) ? array() : $this->servicePackageNamesBySN[$service][$pkg];
	}

	public function __ava__getExtensions(){
		if(!$this->serviceExtensions){
			$this->serviceExtensions = $this->DB->columnFetch(array('service_extensions', 'name', 'mod', "", "`name`"));
		}
		return $this->serviceExtensions;
	}

	private function fetchPayExtensions(){		if(!$this->payExtensions){
			$this->payExtensions = $this->DB->columnFetch(array('payment_extensions', 'name', 'mod', "", "`name`"));
		}
	}

	public function __ava__getPayExtensions(){
		$this->fetchPayExtensions();
		return $this->payExtensions;
	}

	public function __ava__getPayExtName($ext){
		$this->fetchPayExtensions();
		return $this->payExtensions[$ext];
	}

	private function fetchSmsExtensions(){
		if(!$this->smsExtensions){
			$this->smsExtensions = $this->DB->columnFetch(array('sms_extensions', 'name', 'mod', "", "`name`"));
		}
	}

	public function __ava__getSmsExtensions(){
		$this->fetchSmsExtensions();
		return $this->smsExtensions;
	}

	public function __ava__getSmsExtName($ext){
		$this->fetchSmsExtensions();
		return $this->smsExtensions[$ext];
	}

	public function __ava__serviceData($service, $pkg = false){		/*
			Возвращает данные по услуге
		*/

		$this->fetchServicesData($service);
		if($pkg) return $this->servicesData[$service][$pkg];		else return $this->servicesData2[$service];	}

	public function __ava__serviceDataById($serviceId){
		/*
			Возвращает данные по услуге
		*/

		$this->fetchServicesData();
		foreach($this->servicesData2 as $i => $e){			if($e['id'] == $serviceId) return $e;		}

		return array();
	}

	public function __ava__getPkgsByServerName($service, $pkg){
		/*
			Список всех пакетов и соединений по имени пакета на сервере
		*/

		if(empty($this->pkgsByServerName[$service][$pkg])){
			$this->fetchConnections();

			foreach($this->servicesData as $i => $e){
				foreach($e as $i1 => $e1){
					foreach($e as $e2){
						if($e2['server_name'] == $e1['server_name']){							$this->pkgsByServerName[$i][$e2['server_name']][$i1] = isset($this->connectionsByPkg[$i][$e2['server_name']][$e1['server']]) ? $this->connectionsByPkg[$i][$e2['server_name']][$e1['server']] : $e2['text'];
						}					}
				}
			}
		}

		return $this->pkgsByServerName[$service][$pkg];
	}

	public function __ava__pkgParam($service, $pkg, $param){		return $this->servicesData[$service][$pkg][$param];	}

	public function __ava__pkgCurrency($service, $pkg){
		return $this->currencyName($this->pkgParam($service, $pkg, 'currency'));
	}

	public function __ava__packageDataById($service, $pkgId){
		/*
			Возвращает данные по услуге
		*/

		$this->fetchServicesData($service);
		foreach($this->servicesData[$service] as $i => $e){
			if($e['id'] == $pkgId) return $e;
		}

		return array();
	}

	public function __ava__callServiceObj($func, $service, $params = array(), $saveParams = array()){		/*
			Создает объект некоторой услуги и обращается к его методу для расширения работы биллинга
		*/

		if(empty($this->serviceObjects[$service])){
			$this->fetchServicesData($service);
			if(empty($this->servicesData2[$service]['extension'])) return true;
			$this->serviceObjects[$service] = $this->Core->callModule($this->servicesData2[$service]['extension']);
		}

		$serverId = isset($params['server']) ? $params['server'] : (isset($this->values['server']) ? $this->values['server'] : 0);
		if(method_exists($this->serviceObjects[$service], $func) || method_exists($this->serviceObjects[$service], '__ava__'.$func)) $return = $this->serviceObjects[$service]->$func($this, $service, $params, $saveParams);
		else $return = $this->serviceObjects[$service]->callServerExtension($serverId, $func, $this, $service, $params, $saveParams);

		return $return;
	}

	public function __ava__getServiceMod($service){		/*
			Возвращает имя модуля услуги.
		*/

		$this->fetchServicesData();
		return $this->servicesData2[$service]['extension'];	}

	public function __ava__callServiceByExt($func, $extension, $params = array()){
		/*
			Создает объект некоторой услуги и обращается к его методу для расширения работы биллинга
		*/

		$extFullName = 'mod_'.$extension;
		$obj = new $extFullName(false, $extension, false);
		$return = $obj->$func($this, $params);
		return $return;
	}

	public function __ava__getBaseTerm($service){
		/*
			Возвращает базовый срок для услуги
		*/

		if($this->servicesData === false) $this->fetchServicesData($service);
		return $this->servicesData2[$service]['base_term'];
	}

	public function __ava__getTestTerm($service){
		/*
			Возвращает тестовый базовый срок для услуги
		*/

		if($this->servicesData === false) $this->fetchServicesData($service);
		return $this->servicesData2[$service]['test_term'];
	}

	public function __ava__addClient($userId = false, $params = array()){		/*
			Добевляет клиента в таблицу клеентов
		*/

		if(!$userId && !($userId = $this->Core->User->getUserId())){			throw new AVA_Exception('{Call:Lang:modules:billing:neudalosdoba}');		}
		elseif($this->Core->DB->cellFetch(array('admins', 'id', "`user_id`='$userId'"))){			throw new AVA_Exception('Попытка сделать администратора клиентом');		}

		$params['user_id'] = $userId;
		if(!isset($params['date'])) $params['date'] = time();
		if(!$return = $this->DB->cellFetch(array('clients', 'id', "`user_id`='$userId'"))) $return = $this->DB->Ins(array('clients', $params));
		return $return;	}

	public function getPkgElementForm($service, $pkg, $varPrefix = '', $varPostfix = '', &$values = array(), $cp = array()){		/*
			Дополнительная форма для настройки элементов персонально под каждый ТП
		*/

		$dbObj = $this->DB->Req(array('package_descripts', '*', $this->getPkgDscFilter($cp)."`service`='".db_main::Quot($service)."' AND `show` AND (`aacc`=3 OR `opkg`=3 OR `mpkg`=3 OR `pkg_list`=2)", "`sort`"));
		$pkgData = $this->serviceData($service, $pkg);
		$return = array();
		$intSort = array();

		while($r = $dbObj->Fetch()){			foreach(array('aacc', 'opkg', 'mpkg', 'pkg_list') as $e){
				if($r[$e] == '3' || ($e == 'pkg_list' && $r[$e] == '2')) $this->insPkgElementForm($return[$e], $e, $r, $pkgData, $varPrefix, $varPostfix);			}		}

		return $return;
	}

	public function insPkgElementForm(&$matrix, $bType, $params, $pkgData, $prefix = '', $postfix = ''){		/*
			Вставляет элемент доп. настройки тарифа в мацрицу
		*/

		$postfix = '_'.$postfix.$params['name'];
		$sType = $pkgData['service_type'];

		if($bType == 'pkg_list'){
			$params['extra'] = Library::unserialize($params['extra']);

			if($params['extra']['pkg_list_ind_pkg_value_'.$pkgData['main_group']] == 'ind'){
				$matrix[$prefix.'pkg_list_value2'.$postfix]['text'] = '{Call:Lang:modules:billing:znacheniedli:'.Library::serialize(array($params['text'])).'}';
				$matrix[$prefix.'pkg_list_value2'.$postfix]['type'] = 'text';
			}
		}
		else{
			$type = $params['type'];

			$matrix[$bType.$prefix.'_capt_'.$params['name']]['text'] = $params['text'];
			$matrix[$bType.$prefix.'_capt_'.$params['name']]['type'] = 'caption';
			require(_W.'modules/billing/forms/fields4'.$bType.'_blk.php');
		}
	}

	public function __ava__getExtraDescript($service, $pkg, $type, $prefix = '', $postfix = '', $cp = array()){		/*
			Возвращает расширенное описание
		*/

		$return = array();
		foreach($this->getPkgElementForm($service, $pkg, '', '', $values, $cp) as $i => $e){			foreach($e as $i1 => $e1){				if($e1['type'] == 'caption') continue;				$return[$i1] = empty($this->values[$prefix.$i1.$postfix]) ? '' : $this->values[$prefix.$i1.$postfix];			}		}

		return $return;
	}

	public function __ava__getJsCalcHash($matrix, $price, $installPrice, $params = array()){		/*
			Создает объект для расчета стоимости через javascript
		*/

		$jsArr = array(
			'price' => $price,
			'installPrice' => $installPrice,
			'constructor' => array()
		);

		foreach($matrix as $i => $e){			$jsArr['constructor'][$i] = array(
				'price' => $e['price'],
				'price_unlimit' => $e['price_unlimit'],
				'price_install' => $e['price_install'],
				'price_install_unlimit' => $e['price_install_unlimit'],
				'type' => $e['type']
			);		}

		return Library::jsHash($jsArr);	}

	private function getPkgDscFilter($cp){		foreach($cp as $e){
			if(!$e) continue;
			$cpFilter[] = "`cp` REGEXP (',$e,')";
		}

		return empty($cpFilter) ? '`use_if_no_panel` AND' : '('.implode(' OR ', $cpFilter).' OR `use_if_no_conformity`) AND ';
	}


	/********************************************************************************************************************************************************************

																			Группы тарифов

	*********************************************************************************************************************************************************************/

	private function fetchPkgGroups(){
		/*
			Извлекает теги
		*/

		if($this->pkgGroups === false){
			foreach($this->DB->columnFetch(array('package_groups', '*', "", "", "`sort`")) as $e){
				$this->pkgGroups[$e['service']][$e['name']] = $e;
				$this->pkgGroupsById[$e['service']][$e['id']] = $e;
				$this->pkgGroupNames[$e['service']][$e['name']] = $e['text'];
			}
		}
	}

	public function __ava__getPkgGroup($service, $name = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchPkgGroups();
		if(!isset($this->pkgGroups[$service])) return array();
		return $name ? $this->pkgGroups[$service][$name] : $this->pkgGroups[$service];
	}

	public function __ava__getPkgGroupById($service, $id = false){
		/*
			Возвращает список тегов
		*/

		$this->fetchPkgGroups();
		if(!isset($this->pkgGroupsById[$service])) return array();
		return $id ? $this->pkgGroupsById[$service][$id] : $this->pkgGroupsById[$service];
	}

	public function __ava__getPkgGroupNames($service){
		/*
			Возвращает список тегов
		*/

		$this->fetchPkgGroups();
		if(!isset($this->pkgGroupNames[$service])) return array();
		return $this->pkgGroupNames[$service];
	}


	/********************************************************************************************************************************************************************

																				Скидки

	*********************************************************************************************************************************************************************/

	public function __ava__fetchDiscounts(){		/*
			Извлекает все списки
		*/

		if(!is_array($this->discounts)){
			foreach($this->DB->columnFetch(array('discounts', '*', 'name', "`show`", "`sort`")) as $i => $e){
				$this->discounts[$e['service']][$i] = $e;				$this->discounts[$e['service']][$i]['vars'] = Library::unserialize($e['vars']);
				$this->discounts[$e['service']][$i]['client_loyalty_levels'] = Library::str2arrKeys($e['client_loyalty_levels']);
				$this->discountsByType[$e['type']][$e['service']][$i] = $this->discounts[$e['service']][$i];
			}
		}
	}

	public function __ava__discountsByType($type = false, $service = false){
		$this->fetchDiscounts();
		return $type === false ?
			$this->discountsByType :
				($service === false ?
					(isset($this->discountsByType[$type]) ? $this->discountsByType[$type] : array()) :
					(isset($this->discountsByType[$type][$service]) ? $this->discountsByType[$type][$service] : array())
				);
	}

	public function __ava__discountParams($service, $dsc = false){		$this->fetchDiscounts();
		return $dsc === false ? (isset($this->discounts[$service]) ? $this->discounts[$service] : array()) : (isset($this->discounts[$service][$dsc]) ? $this->discounts[$service][$dsc] : array());	}

	public function __ava__getDiscounts($entryId, $entry, $entries){
		/*
			Возвращает расчет скидки для услуги
		*/

		$this->fetchDiscounts();
		$return = array('discounts' => array(), 'discountDescripts' => array());

		if(!empty($this->discounts[$entry['service']])){
			foreach($this->discounts[$entry['service']] as $i => $e){				if(empty($e['vars']['pkgs'][$entry['package']])) continue;
				$d = 0;

				switch($e['type']){					case 'term':
						$d = $this->getTermDiscount($entry['term'], $e['vars']['discounts']);
						break;

					case 'order_sum':						$d = $this->getOrderSumDiscount($entries, $e['vars']['discounts']);
						break;

					case 'other_services':
						$d = $this->getOtherServiceDiscount($entries, $entry, $e['vars']);
						break;

					case 'promocode':
						if($entry['promo_code'] && $this->promoCodeIsUsable($entry['promo_code']) && $this->getPromoCodeGroup($entry['promo_code']) == $e['vars']['promocodegroup']){							$d = $e['vars']['discount'];						}
						break;

					case 'baseless':
						$d = $e['vars']['discount'];
						break;
				}
				if($d){
					$return['discountDescripts'][$i] = array('discount' => $d, 'descript' => $e['text']);

					foreach($e['vars']['basic_type'] as $i1 => $e1){
						if($e1){
							if(!isset($return['discounts'][$i1])) $return['discounts'][$i1] = 0;
							$return['discounts'][$i1] += $d;
						}
					}
				}
			}
		}

		foreach($return['discounts'] as $i => $e) if($e > 100) $return['discounts'][$i] = 100;
		$return['discountSum'] = $this->getDiscountSums($entry, $return['discounts']);
		return $return;
	}

	public function __ava__getTermDiscount($term, $discounts){
		/*
			Возвращает размер скидки, %
		*/

		for($i = $term; $i > 0; $i --){
			if(isset($discounts[$i])) return $discounts[$i];
		}
		return 0;
	}

	public function __ava__getOrderSumDiscount($entries, $discounts){
		/*
			Возвращает размер скидки, %
		*/

		$orderSum = 0;
		foreach($entries as $i => $e){
			$orderSum += $this->getTotalPrice($e['price'], $e['price2'], $e['prolong_price'], $e['term'], $e['install_price'], $e['modify_price'], $e['modify_install_price'], $e['entry_type']);		}

		for($i = $orderSum; $i > 0; $i --){
			if(isset($discounts[$i])) return $discounts[$i];
		}
		return 0;
	}

	public function __ava__getOtherServiceDiscount($entries, $entry, $discounts){		/*
			Скидка за заказ других услуг
		*/

		foreach($entries as $i => $e){			if(
				$e['id'] != $entry['id'] &&
				empty($this->currentDiscountParams[$e['id']]) &&
				$this->otherServiceKit($entries, $e, $discounts, $currentKit)
			){
				foreach($currentKit as $i1 => $e1){					foreach($e1 as $i2 => $e2){						foreach($e2 as $i3 => $e3) $this->currentDiscountParams[$i3] = true;
					}				}

				return $discounts['discount'];
			}		}

		return 0;
	}

	private function otherServiceKit($entries, $e, $discounts, &$currentKit = array()){		/*
			Проверяет что сформирован набор услуг минимально необходимый для получения скидки
		*/

		//Вставляем сведения о услугах позволяющих получить скидку в текущий список
		if(
			!empty($discounts['other_services'][$e['service']]) && !empty($discounts['other_services_pkgs_'.$e['service']][$e['package']]) &&
			(count($currentKit[$e['service']][$e['package']]) < $discounts['other_services_count_'.$e['package'].'_'.$e['service']]) &&
			($e['term'] >= $discounts['other_services_term_'.$e['package'].'_'.$e['service']])
		){			$currentKit[$e['service']][$e['package']][$e['id']] = $e;		}

		//Выясняем позволяет ли текущий список получить скидку
		if(
			(
				($discounts['discount_logic'] == 'OR' && !empty($currentKit)) ||
				($discounts['discount_logic'] == 'AND' && $this->allServices4discount($discounts['other_services'], $currentKit))
			)
		){			foreach($currentKit as $i1 => $e1){				if(!(
					($discounts['discount_pkg_logic_'.$i1] == 'OR' && !empty($discounts['other_services_pkgs_'.$i1][$e['package']])) ||
					($discounts['discount_pkg_logic_'.$i1] == 'AND' && $this->allServices4discount($discounts['other_services_pkgs_'.$i1], $e1))
				)){					return false;				}			}

			return true;		}

		return false;	}

	private function allServices4discount($discounts, $currentKit){		/*
			Проверяет что есть все услуги необходимые для получения скидки при методе AND
		*/

		foreach($discounts as $i => $e){			if($e && empty($currentKit[$i])) return false;		}

		return true;	}

	public function __ava__getDiscountSums($entry, $discounts){		/*
			Возвращает суммы скидок по всем типам
		*/

		$return = array(
			'install' => ($entry['install_price'] + $entry['modify_install_price']) * (empty($discounts['install']) ? 0 : $discounts['install']) / 100,
			'term' => $entry['price'] * (empty($discounts['term']) ? 0 : $discounts['term']) / 100,
			'term2' => $entry['price2'] * (empty($discounts['term2']) ? 0 : $discounts['term2']) * ($entry['term'] - 1) / 100,
			'prolong' => $entry['prolong_price'] * (empty($discounts['prolong']) ? 0 : $discounts['prolong']) * $entry['term'] / 100,
			'modify' => $entry['modify_price'] * (empty($discounts['modify']) ? 0 : $discounts['modify']) * $entry['term'] / 100,
		);

		$return['all'] = ($entry['entry_type'] == 'new') ? $return['install'] + $return['term'] + $return['term2'] + $return['modify'] : $return['prolong'] + $return['modify'];
		return $return;
	}

	public function __ava__canUsePromoCode($service, $force = false){		/*
			Проверяет можно ли ваще использовать промакод для этай услуги
		*/

		if($force || !isset($this->canUsePromoCode[$service])){
			if($vars = $this->DB->cellFetch(array('discounts', 'vars', "`service`='$service' AND `type`='promocode' AND `show`"))){				$vars = Library::unserialize($vars);
				$t = time();

				$p = $this->DB->getPrefix();
				$t1 = $p.'promocodegroups';
				$t2 = $p.'promocodes';
				$t3 = $p.'order_entries';

				$dbO = $this->DB->Req("SELECT t2.code FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.id=t2.group LEFT JOIN $t3 AS t3 ON t2.code=t3.promo_code ".
					"WHERE t1.id='{$vars['promocodegroup']}' AND (!t2.actually OR t2.actually>=$t) AND (!t2.started OR t2.started<=$t) AND (t3.promo_code IS NULL OR t3.status<0) LIMIT 1");

				$c = $dbO->Fetch();
				$this->canUsePromoCode[$service] = $c['code'] ? true : false;
			}
			else $this->canUsePromoCode[$service] = false;
		}

		return $this->canUsePromoCode[$service];
	}

	public function __ava__promoCodeIsNotUsed($code, $eId = false){		/*
			Проверяет что промокод не использовался ранее
		*/

		$params = $this->getPromoCodeParams($code);
		if($params['code_style'] == 2 || ($params['code_style'] == 1 && !$this->DB->cellFetch(array('order_entries', 'id', "`promo_code`='$code' AND `status`>=0")))) return true;
		elseif($eId){			$eData = $this->getOrderEntry($eId, true);
			if($eData['promo_code'] == $code) return true;		}

		return false;	}

	private function fetchPromoCodeGroups(){
		if($this->promoCodeGroupParams == false){
			$this->promoCodeGroupParams = $this->DB->columnFetch(array('promocodegroups', '*', 'id', '', '`sort`'));
			foreach($this->promoCodeGroupParams as $i => $e){				$this->promoCodeGroups[$i] = $e['name'];
			}
		}
	}

	public function __ava__getPromoCodeGroups(){		$this->fetchPromoCodeGroups();
		return $this->promoCodeGroups;	}

	public function __ava__getPromoCodeGroupParams($grp){
		$this->fetchPromoCodeGroups();
		return $this->promoCodeGroupParams[$grp];
	}

	private function fetchPromoCodes(){		if($this->promoCodeParams === false){			$this->promoCodeParams = $this->DB->columnFetch(array('promocodes', '*', 'code'));		}	}

	public function __ava__promoCodeIsUsable($code){		/*
			Проверяет тот факт что промо-код вообще можно использовать
		*/

		$this->fetchPromoCodes();
		$t = time();
		if(isset($this->promoCodeParams[$code]) && ($this->promoCodeParams[$code]['started'] < $t) && (!$this->promoCodeParams[$code]['actually'] || $this->promoCodeParams[$code]['actually'] > $t)){			return true;		}
	}

	public function __ava__getPromoCodeGroup($code){		/*
			Возвращает размер скидки по промо-коду
		*/
		$this->fetchPromoCodes();
		return $this->promoCodeParams[$code]['group'];	}

	public function __ava__getPromoCodeParams($code){
		/*
			Возвращает размер скидки по промо-коду
		*/

		$this->fetchPromoCodes();
		return $this->promoCodeParams[$code];
	}


	/********************************************************************************************************************************************************************

																	Работа с клиентами

	*********************************************************************************************************************************************************************/

	public function __ava__getUserByClientId($clientId){
		/*
			Выбирает данные пользователя по $clientId
		*/

		if(empty($this->clients[$clientId])){
			$this->clients[$clientId] = $this->DB->rowFetch(array('clients', '*', "`id`='".db_main::Quot($clientId)."'"));
			$this->clients[$clientId] = Library::array_merge($this->Core->getUserParamsById($this->clients[$clientId]['user_id']), $this->clients[$clientId]);
		}

		return $this->clients[$clientId];
	}

	public function __ava__getUserLoginByClientId($clientId){
		/*
			Выбирает данные пользователя по $clientId
		*/

		$cData = $this->getUserByClientId($clientId);
		return $cData['login'];
	}

	public function __ava__getClientByUserId($userId){
		/*
			Выбирает $clientId по ID пользователя
		*/

		if(empty($this->users[$userId])){
			$this->users[$userId] = $this->DB->cellFetch(array('clients', 'id', "`user_id`='$userId'"));
		}

		return $this->users[$userId];
	}

	public function __ava__getClientByIdOrLogin($id){
		/*
			Выбирает $clientId по ID пользователя или логину
		*/

		return $this->getClientByUserId($this->Core->DB->cellFetch(array('users', 'id', "`id`='$id' OR `login`='$id'")));
	}

	public function __ava__getUserIdByClientId($id){		$cData = $this->getUserByClientId($id);
		return $cData['user_id'];	}

	public function __ava__getClientId(){		/*
			Возвращает ID текущего клеента
		*/

		return empty($this->Core->User->extraParams[$this->mod]['clientId']) ? false : $this->Core->User->extraParams[$this->mod]['clientId'];	}

	public function __ava__getClientEml($clientId){		$userParams = $this->getUserByClientId($clientId);
		return $userParams['eml'];	}

	public function __ava__getAdminNotifyEml(){		return $this->Core->getAdminEml(($p = $this->Core->getParam('notifyBillAdmin', $this->mod)) ? $p : $this->Core->getRoot());
	}

	public function __ava__getClientData($clientId, $force = false){
		/*
			Возвращает ID текущего клеента
		*/

		if(empty($this->clientsData[$clientId]) || $force) $this->clientsData[$clientId] = $this->DB->rowFetch(array('clients', '*', "`id`='$clientId'"));
		return $this->clientsData[$clientId];
	}

	public function __ava__getClientBalance($clientId, $force = false){
		/*
			Возвращает ID текущего клеента
		*/

		$cData = $this->getClientData($clientId, $force);
		return $cData['balance'];
	}

	private function fetchLoyaltyLevels(){		if($this->loyaltyLevels === false) $this->loyaltyLevels = $this->DB->columnFetch(array('loyalty_levels', 'text', 'name', "", "`sort`"));	}

	public function __ava__getLoyaltyLevelName($level){		/*
			Название уровня клиента
		*/

		$this->fetchLoyaltyLevels();
		return isset($this->loyaltyLevels[$level]) ? $this->loyaltyLevels[$level] : '';	}

	public function __ava__getLoyaltyLevels(){
		/*
			Название уровня клиента
		*/

		$this->fetchLoyaltyLevels();
		return $this->loyaltyLevels;
	}


	/********************************************************************************************************************************************************************

																Работа с услугами

	*********************************************************************************************************************************************************************/

	public function __ava__presetService($service, $pkg, $clientId, $uniq, $orderId = 0){		/*
			Предварительное внесение услуги. Возвращает ID в списке
		*/

		if($id = $this->DB->cellFetch(array('order_services', 'id', "`uniq_id`='$uniq'"))) return $id;
		if(!($pkgData = $this->serviceData($service, $pkg)) || !$pkgData['show'] || empty($pkgData['rights']['new'])){
			throw new AVA_NotFound_Exception('{Call:Lang:modules:billing:tarifanenajd:'.Library::serialize(array($pkg)).'}');
		}

		$return = $this->DB->Ins(
			array(
				'order_services',
				array(
					'service' => $service,
					'client_id' => $clientId,
					'package' => $pkg,
					'date' => time(),
					'uniq_id' => $uniq
				)
			)
		);

		if(!$return) throw new AVA_Exception('{Call:Lang:modules:billing:neudalosusta}');
		$this->DB->Ins(array('orders_'.$service, array('service_order_id' => $return)));
		return $return;	}

	public function __ava__addService($service, $entry, $ident, $serverId = 0, $vars = array(), $vars2 = array(), $extra = array()){		/*
			Добавляет услугу в список заказанных услуг.
		*/

		if(!$ident) throw new AVA_Exception('Пустой идентификатор заказа');
		$return = $this->DB->Upd(array('order_services', array('ident' => $ident, 'server' => $serverId, 'extra' => $extra, 'vars' => $vars, 'step' => 1), "`id`='$entry'"));		if($vars2) $this->DB->Upd(array('orders_'.$service, $vars2, "`service_order_id`='$entry'"));
		$this->serviceHistory($entry, 'new');
		return $return;
	}

	public function __ava__presetOrderEntry($service, $pkg, $orderId, $serviceId, $type, $uniq = false){		/*
			Вносит запись в счет
		*/

		if(!$uniq) $uniq = $service.'-'.$pkg.'-'.$orderId.'-'.$serviceId;
		if($id = $this->DB->cellFetch(array('order_entries', 'id', "`uniq_id`='$uniq'"))) return $id;

		$return = $this->DB->Ins(
			array(
				'order_entries',
				array(
					'entry_type' => $type,
					'order_id' => $orderId,
					'order_service_id' => $serviceId,
					'service' => $service,
					'package' => $pkg,
					'date' => time(),
					'uniq_id' => $uniq
				)
			)
		);

		if(!$return) throw new AVA_Exception('{Call:Lang:modules:billing:neudalosusta}');
		return $return;	}

	public function __ava__addEntry2Order($entry, $term, $caption = '', $extra = false, $pc = false){
		/*
			Добавляет запись в таблицу записей заказа
		*/

		$values = array('term' => $term);
		if($caption) $values['entry_caption'] = $caption;
		if($extra !== false) $values['extra'] = $extra;
		if($pc !== false) $values['promo_code'] = $pc;

		return $this->DB->Upd(array('order_entries', $values, "`id`='$entry'"));
	}

	public function __ava__serviceHistory($serviceId, $operation = '', $operationDescript = '', $additional = array()){		/*
			Добавляет запись в историю услуги
			operation - тип операции:
				add - добавлена
				prolong - продлена
				modify - изменены параметры
				suspend - залочена
				unsuspend - разлочена
				delete - удалена
				пустое значение в иных случаях
		*/

		if(!$serviceId) return;

		if(!$operationDescript){
			switch($operation){				case 'add': $operationDescript = '{Call:Lang:modules:billing:uslugadobavl}'; break;
				case 'prolong': $operationDescript = '{Call:Lang:modules:billing:uslugaprodle}'; break;
				case 'modify': $operationDescript = '{Call:Lang:modules:billing:izmenenypara}'; break;
				case 'suspend': $operationDescript = '{Call:Lang:modules:billing:uslugazablok}'; break;
				case 'unsuspend': $operationDescript = '{Call:Lang:modules:billing:uslugarazblo}'; break;
				case 'delete': $operationDescript = '{Call:Lang:modules:billing:uslugaudalen}'; break;
			}
		}

		$serviceId = db_main::Quot($serviceId);
		$params = $this->DB->rowFetch(array('order_services', '*', "`id`='$serviceId'"));
		$history = Library::unserialize($params['history']);
		unset($params['history']);

		$history[time()] = array(
			'params' => $params,
			'operationDescript' => $operationDescript,
			'operation' => $operation,
			'additional' => $additional
		);

		return $this->DB->Upd(array('order_services', array('history' => Library::serialize($history)), "`id`='$serviceId'"));	}

	public function __ava__calcModifiedService($service, $pkg, $params){		/*
			Расчет стоимости индивидуального ТП
		*/

		$pkgData = $this->serviceData($service, $pkg);
		$ipkgMatrix = $this->getPkgDescriptForm($service, $pkgData['name'], 'mpkg', '', '', $values, array($this->getConnectionCp($pkgData['server'])));
		$price = $iPrice = 0;

		foreach($ipkgMatrix as $i => $e){			if(!empty($params[$i])){				switch($e['type']){					case 'hidden':
					case 'select':
					case 'radio':
					case 'text':
						if($this->isUnlimit($params[$i])){							$price += $e['price_unlimit'];
							$iPrice += $e['price_install_unlimit'];						}
						elseif(is_array($e['price'])){							$price += empty($e['price'][$params[$i]]) ? 0 : $e['price'][$params[$i]];
							$iPrice += empty($e['price_install'][$params[$i]]) ? 0 : $e['price_install'][$params[$i]];
						}
						else{
							$price += $e['price'] * $params[$i];
							$iPrice += $e['price_install'] * $params[$i];						}
						break;

					case 'checkbox':
						$price += $e['price'];
						$iPrice += $e['price_install'];
						break;
				}			}
		}

		return array($price, $iPrice);	}


	public function __ava__getTotalTerm($service, $pkg, $term, $testTerm){
		/*
			Общая цена за весь срок использования услуги
		*/

		$data = $this->serviceData($service, $pkg);
		return dates::term2sec($data['base_term'], $term) + ((!$data['base_term'] || $data['inner_test']) ? dates::term2sec($data['test_term'], $testTerm) : 0);
	}

	public function __ava__getTermDifference($to, $price, $newPrice){
		/*
			Возвращает разницу в сроке
		*/

		return time() + ((($to - time()) * $price) / $newPrice);
	}

	public function __ava__getPayDifference($baseTerm, $paidTo, $oldTotal, $newTotal, $time = false){
		/*
			Возвращает разницу в сумме платежа от $time до $paidTo
		*/

		return round(Dates::sec2term($baseTerm, $paidTo - ($time ? $time : time())), false) * ($newTotal - $oldTotal);
	}



	/********************************************************************************************************************************************************************

																		Проведение заказа

	*********************************************************************************************************************************************************************/


	public function __ava__getSelectedServicesEntries($service, $filter = '', $entries = false){		/*
			Выбирает записи услуг выбранных в списке
		*/

		$return = array();
		$p = $this->DB->getPrefix();

		$t1 = $p.'order_services';
		$t2 = $p.'orders_'.$service;
		$t3 = $p.'clients';

		$dbObj = $this->DB->Req("SELECT t1.id, t1.client_id, t1.ident, t1.package, t1.server, t1.paid_to, t1.step, t2.*, t3.user_id
			FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.id=t2.service_order_id LEFT JOIN $t3 AS t3 ON t1.client_id=t3.id
			WHERE ".$this->getEntriesWhere($entries, 't1`.`id').$filter);

		while($r = $dbObj->Fetch()){
			$return[$r['server']][$r['id']] = $r;
			$return[$r['server']][$r['id']]['pkgParams'] = $this->serviceData($service, $r['package']);
		}

		return $return;
	}

	public function __ava__canUseChangePkg($service, $pkg, $newPkg){		/*
			Проверяет что этот тариф можно минять.
		*/

		$pkgs = $this->canUseChangePkgsList($service, $pkg);
		if(!empty($pkgs[$newPkg])) return true;
		return false;
	}

	public function __ava__canUseModifyPkg($service, $pkg){
		/*
			Проверяет что этотому тарифу можно докупить опций
		*/

		$data = $this->serviceData($service, $pkg);
		return !empty($data['rights']['modify']);
	}

	public function __ava__canUseChangePkgsList($service, $pkg = ''){
		/*
			Списек всех пакетов на которые можно менять искомый
		*/

		$return = $this->getPackages($service);

		if($pkg){
			$data = $this->serviceData($service, $pkg);
			if(empty($data['rights']['modify'])) unset($return[$pkg]);

			foreach($this->fetchServicesData($service) as $i => $e){				if($i == $pkg) continue;				if(empty($data['rights']['changeGrp']) && $e['main_group'] != $data['main_group']) unset($return[$i]);
				if(empty($data['rights']['changeSrv']) && $e['server'] != $data['server']) unset($return[$i]);
				if(empty($data['rights']['changeDn']) && $e['prolong_price'] < $data['prolong_price']) unset($return[$i]);
				if(empty($data['rights']['changeUp']) && $e['prolong_price'] >= $data['prolong_price']) unset($return[$i]);
			}
		}

		return $return;
	}

	public function __ava__getServicesByClient($clientId, $filter = "AND `step`>-1", $servList = false){		/*
			Выбирает все услуги по ID клеента
		*/

		if($servList !== false) $filter .= " AND (".$this->getEntriesWhereByValue($servList, 'service').")";
		$services = array();
		$where = array();

		foreach($this->DB->columnFetch(array('order_services', array('service', 'ident', 'package', 'server', 'date', 'created', 'last_paid', 'paid_to', 'price', 'modify_price', 'ind_price', 'all_payments', 'step', 'suspend_reason', 'suspend_reason_descript', 'auto_prolong', 'auto_prolong_fract'), 'id', "`client_id`='$clientId' ".$filter)) as $i => $e){
			$where[$e['service']][$i] = "`service_order_id`='$i'";
			$pkgData = $this->serviceData($e['service'], $e['package']);

			$services[$e['service']][$i] = Library::arrayMergeWithPrefix(
				$e,
				array(
					's_' => $this->serviceData($e['service']),
					'pkg_' => $pkgData,
					'cur_' => $this->currencyParams($pkgData['currency'])
				)
			);
		}

		foreach($services as $i => $e){
			$e = Library::array_merge($e, $this->DB->columnFetch(array('orders_'.$i, '*', 'service_order_id', implode(' OR ', $where[$i]))));
		}

		return $services;
	}

	public function __ava__sumParams($base, $modify, $service){		/*
			Суммирует параметры для заказа, возвращает результат
		*/

		$return = array();
		$types = $this->DB->columnFetch(array("package_descripts", "type", "name", "`service`='$service'"));

		foreach($base as $i => $e){			if(!empty($types[$i]) && $types[$i] == 'checkbox'){				if(!empty($e) || !empty($modify[$i])) $return[$i] = 1;			}
			else{
				$modify[$i] = isset($modify[$i]) ? $modify[$i] : 0;				if($this->isUnlimit($e) || $this->isUnlimit($modify[$i])) $return[$i] = 'Unlimit';
				elseif(is_array($e) || is_array($modify[$i])) $return[$i] = Library::array_merge($e, $modify[$i]);
				elseif(!regExp::digit($e) || !regExp::digit($modify[$i])) $return[$i] = empty($modify[$i]) ? $e : $modify[$i];
				else $return[$i] = $e + $modify[$i];
			}		}

		return $return;	}

	public function __ava__differenceParams($base, $subtract, $service){		/*
			Находит разницу между группами параметров
		*/

		$return = array();
		$types = $this->DB->columnFetch(array("package_descripts", "type", "name", "`service`='$service'"));

		foreach($base as $i => $e){
			if(!empty($types[$i]) && $types[$i] == 'checkbox'){
				if(!empty($e) || empty($subtract[$i])) $return[$i] = 1;
			}
			else{
				$subtract[$i] = isset($subtract[$i]) ? $subtract[$i] : 0;
				if($this->isUnlimit($e)) $return[$i] = $e;
				elseif($this->isUnlimit($subtract[$i]) || $subtract[$i] >= $e) $return[$i] = 0;
				else $return[$i] = $e - $subtract[$i];
			}
		}

		return $return;
	}

	protected function __ava__suspendOrderedServices($entries, $service, $unsuspend = false){
		/*
			Блокирует заказы
		*/

		return;

		$sData = $this->serviceData($service);
		$srvFunc = $unsuspend == 1 ? 'unsuspendAcc' : ($unsuspend == 2 ? 'delAcc' : 'suspendAcc');
		$step = $unsuspend == 1 ? 2 : ($unsuspend == 2 ? -2 : -1);
		$say = $unsuspend == 1 ? '{Call:Lang:modules:billing:razblokirova1}' : ($unsuspend == 2 ? '{Call:Lang:modules:billing:udalena}' : '{Call:Lang:modules:billing:zablokirovan1}');
		$sayType = $unsuspend == 1 ? 'unsuspend' : ($unsuspend == 2 ? 'delete' : 'suspend');

		$result = array();
		foreach($entries as $i => $e){
			$result[$i] = Library::arrayFill($e, true);
			if(!$i) continue;
			if($this->values['action_type'] == 'server') $result[$i] = $this->callServiceObj($srvFunc, $sData['name'], array('accs' => $e, 'server' => $i));
		}

		$filter = array();
		$link = $this->path.'?mod='.$this->mod.'&func=connectionResult&id=';

		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){
				if($e1){
					$this->setContent('{Call:Lang:modules:billing:uslugadlia:'.Library::serialize(array($sData['text'], $entries[$i][$i1]['ident'], $say)).'}', 'refresh_msg');
					$this->serviceHistory($i1, $sayType, empty($this->values['reason']) ? '' : '{Call:Lang:modules:billing:uslugapoosno:'.Library::serialize(array($say, $this->values['reason'])).'}');
					$filter[] = "`id`='$i1'";
					$this->sendUserNotify($entries[$i][$i1], $sayType);
				}
				else $this->setError('', '{Call:Lang:modules:billing:uslugadliane:'.Library::serialize(array($sData['text'], $entries[$i][$i1]['ident'], $say)).'}');
			}

			if($i) $this->setContent('{Call:Lang:modules:billing:smotretrezul:'.Library::serialize(array($link, $this->getConnectionResultId($i))).'}', 'refresh_msg');
		}

		if($filter) $this->DB->Upd(array('order_services', array('step' => $step), implode(' OR ', $filter)));
		$this->sendAdminNotify($entries, $result, $sayType);
		return $result;
	}

	protected function __ava__unsuspendOrderedServices($entries, $service){
		/*
			Разблокирует заказы
		*/

		return $this->suspendOrderedServices($entries, $service, 1);
	}

	protected function __ava__deleteOrderedServices($entries, $service){
		/*
			Разблокирует заказы
		*/

		return $this->suspendOrderedServices($entries, $service, 2);
	}

	protected function __ava__prolongOrderedServices($entries, $service){
		/*
			Продление заказов
		*/

		$sData = $this->serviceData($service);
		$t = time();
		$result = array();
		$unsuspend = array();

		$adminNotify = array(
			'values' => $this->values,
			'entries' => array(),
			'result' => array(),
		);

		foreach($entries as $i => $e){			foreach($e as $i1 => $e1){				if($this->prolongService(
					$e1['client_id'],
					$i1,
					$i,
					$this->values['sum'],
					$t,
					$e1['paid_to'] + ($this->values['days'] * 86400),
					$this->values['action_type'] == 'server' ? true : false,
					''
				)){					$result[$i][$i1] = true;					$this->sendUserNotify($entries[$i][$i1], 'prolong');
					if($e1['step'] < 2) $unsuspend[$i][$i1] = $e1;				}
				else $result[$i][$i1] = false;			}
		}

		$this->sendAdminNotify($entries, $result, 'prolong');
		$this->suspendOrderedServices($unsuspend, $service, true);
		return $result;
	}

	protected function __ava__transmitOrderedServices($entries, $service){
		/*
			Передача услуги от клиента к клиенту
			Уведомляет если передан e-mail
		*/

		$clientData = $this->Core->getUserParamsById($this->values['new_owner']);
		$eml = !empty($this->values['notify']) ? '' : $clientData['eml'];
		$oldClientEml = '';
		$result = array();

		foreach($entries as $i => $e){			foreach($e as $i1 => $e1){				$this->transmitService($e1['id'], $clientData['id'], $e1);
				$this->messages[] = array('service', 'success', 'Аккаунт "'.$e1['ident'].'" передан новому пользователю.');
				$result[$i][$i1] = true;			}		}

		$this->sendAdminNotify($entries, $result, 'transmit');
		return $result;
	}

	protected function __ava__transmitService($serviceId, $newClientId, $entry = array()){		/*
			Передает указанную услугу
		*/

		$return = $this->DB->Upd(array('order_services', array('client_id' => $newClientId), "`id`='".db_main::Quot($serviceId)."'"));
		$this->sendUserNotify($entry, 'transmit');

		$pkgData = $this->serviceData($entry['pkgParams']['service'], $entry['package']);
		if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal' && $pkgData['notify_rights']['notify_transmit_new_client']) $tmpl = $pkgData['notify_rights']['mail_tmpl_new_client'];
		elseif($pkgData['notify_rights']['notify_settings_type'] != 'usePersonal') $tmpl = 'transmitServiceNewClient';
		else return $return;

		$this->mail($this->getClientEml($newClientId), $this->getTmplParams($tmpl), array('data' => $entry, 'values' => $this->values));
		return $return;
	}

	protected function __ava__modifyOrderedSefvicesById($id){		$this->DB->Upd(array('modify_service_orders', array('status' => 5), "`id`='$id'"));
		$mData = $this->getServiceModifyData($id);
		$pkgData = $this->serviceData($mData['service'], $mData['old_pkg']);

		$this->values['action_type'] = 'server';
		$this->values['pkg'] = $mData['new_pkg'];

		if($pkgData['service_modify_type'] == 'paidto') $this->values['recalc'] = 'proportional';
		elseif($pkgData['service_modify_type'] == 'paidtobyday'){			$this->values['recalc'] = 'fixingdate';
			$this->values['date_to'] = $mData['new_paid_to'];
		}
		elseif($pkgData['service_modify_type'] == 'balance'){			$this->values['recalc'] = 'fixing';
			$this->values['recalc_prop'] = 1;
		}
		else throw new AVA_Exception('Неопределенный стиль пересчета срока');

		if($mData['new_pkg_vars']){
			$this->values['isModify'] = 1;
			foreach($mData['new_pkg_vars'] as $i => $e){				$this->values['mpkg_'.$i] = $e;			}
		}

		$rslt = $this->modifyOrderedServices($this->getSelectedServicesEntries($mData['service'], '', array($mData['id'] => 1)), $mData['service']);

		if($rslt[$mData['server']][$mData['id']]){
			$this->DB->Upd(array('modify_service_orders', array('modified' => time(), 'status' => 6), "`id`='{$id}'"));
			return true;
		}
		return false;
	}

	protected function __ava__modifyOrderedServices($entries, $service){		/*
			Изменение заказа
		*/

		$sData = $this->serviceData($service);
		$result = array();

		$t = time();
		$pkgData = $this->serviceData($sData['name'], $this->values['pkg']);
		$link = $this->path.'?mod='.$this->mod.'&func=connectionResult&id=';

		foreach($entries as $i => $e){
			$result[$i] = Library::arrayFill($e, true);
			if(!$i) continue;
			$entries['pkgMod'] = empty($this->values['isModify']) ? array() : $this->getPkgParams($sData['name'], 'mpkg', 'mpkg_', '', array($this->getConnectionCp($i)));
			list($entries['pkgModificationPrice'], $entries['pkgModificationInstallPrice']) = $this->calcModifiedService($sData['name'], $this->values['pkg'], $entries['pkgMod']);

			if($this->values['action_type'] == 'server'){				$result[$i] = $this->callServiceObj(
					'modifyAcc',
					$sData['name'],
					array(
						'accs' => $e,
						'pkgData' => $pkgData,
						'installValues' => $this->sumParams($pkgData['vars'], $entries['pkgMod'], $sData['name']),
						'server' => $i,
						'modify' => !empty($this->values['isModify'])
					)
				);
			}
		}

		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){
				switch($this->values['recalc']){					case 'fixing':
						$expr = '((`paid_to` - '.$t.') * '.$this->values['recalc_prop'].') + '.$t;
						break;

					case 'fixingdate':
						$expr = $this->values['date_to'];
						break;

					case 'proportional':
						$expr = '((`paid_to` - '.$t.') * ((`price` + `modify_price`) / '.($pkgData['price'] + $entries['pkgModificationPrice']).')) + '.$t;
						break;

					default:
						throw new AVA_Exception('Неопределенный стиль пересчета срока');
				}
				if($e1){					$this->DB->Upd(
						array(
							'order_services',
							array(
								'package' => $this->values['pkg'],
								'paid_to' => $expr,
								'price' => $pkgData['price'],
								'modify_price' => $entries['pkgModificationPrice'],
								'ind_price' => 0,
								'vars' => $entries['pkgMod'],
								'#isExp' => array('paid_to' => true)
							),
							"`id`='$i1'"
						)
					);
					$this->serviceHistory($i1, 'modify', '{Call:Lang:modules:billing:izmenenietar:'.Library::serialize(array($pkgData['name'])).'}');
					$this->setContent('{Call:Lang:modules:billing:tarifdliaizm:'.Library::serialize(array($entries[$i][$i1]['ident'])).'}', 'refresh_msg');
					$this->sendUserNotify($entries[$i][$i1], 'modify', array('newPkgData' => $pkgData));
					if($i) $this->setContent('{Call:Lang:modules:billing:smotretrezul:'.Library::serialize(array($link, $this->getConnectionResultId($i))).'}', 'refresh_msg');
				}
				else{					$this->setError('', '{Call:Lang:modules:billing:tarifdlianei:'.Library::serialize(array($entries[$i][$i1]['ident'])).'}');
					if($i) $this->setError('', '{Call:Lang:modules:billing:smotretrezul1:'.Library::serialize(array($link, $this->getConnectionResultId($i))).'}');				}
			}
		}

		$this->sendAdminNotify($entries, $result, 'modify', array('newPkgData' => $pkgData));
		if(!empty($this->values['unsuspend'])) $this->suspendOrderedServices($entries, $service, true);
		return $result;
	}

	protected function __ava__sendUserNotify($entryData, $type, $extra = array()){		/*
			Отправка уведомления пользователю
		*/
		$pkgData = $this->serviceData($entryData['pkgParams']['service'], $entryData['package']);

		if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal' && $pkgData['notify_rights']['notify_'.$type]) $tmpl = $pkgData['notify_rights']['mail_tmpl_'.$type];
		elseif($pkgData['notify_rights']['notify_settings_type'] != 'usePersonal') $tmpl = $type.'Service';
		else return false;

		return $this->mail($this->getClientEml($entryData['client_id']), $this->getTmplParams($tmpl), array('data' => $entryData, 'values' => $this->values, 'extra' => $extra));
	}

	protected function __ava__sendAdminNotify($entries, $result, $type, $extra = array()){		/*
			Отправка уведомления админу о массовых действиях над услугами
		*/
		$adminNotify = array();

		foreach($result as $i => $e){
			foreach($e as $i1 => $e1){				$pkgData = $this->serviceData($entries[$i][$i1]['pkgParams']['service'], $entries[$i][$i1]['package']);
				$eml = false;

				if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal' && $pkgData['notify_rights']['notify_admin_'.$type]){					$eml = $this->Core->getAdminEml($pkgData['notify_rights'][$type.'_rcpt_admin']);					$tmpl = $pkgData['notify_rights'][$type.'_rcpt_admin'];
				}
				elseif($pkgData['notify_rights']['notify_settings_type'] != 'usePersonal' && $this->Core->getParam($type.'ServiceAdminMail', $this->mod)){					$eml = $this->getAdminNotifyEml();					$tmpl = $type.'ServiceAdmin';
				}

				if($eml){					$adminNotify[$eml][$tmpl]['entries'][$i1] = $entries[$i][$i1];
					$adminNotify[$eml][$tmpl]['result'][$i1] = $e1;
					$adminNotify[$eml][$tmpl]['resultId'][$i1] = $this->getConnectionResultId($i);
					$adminNotify[$eml][$tmpl]['values'] = $this->values;
					$adminNotify[$eml][$tmpl]['extra'] = $extra;
				}
			}
		}

		$return = array();
		foreach($adminNotify as $i => $e){			foreach($e as $i1 => $e1) $return[$i][$i1] = $this->mail($i, $i1, $e1);		}

		return $return;
	}



	/********************************************************************************************************************************************************************

																				Отправка e-mail

	*********************************************************************************************************************************************************************/

	public function __ava__sendDelServiceMail($eml, $params = array()){
		/*
			Уведомление о удалении
		*/
	}

	public function __ava__sendTransmitServiceMail($eml, $params = array()){
		/*
			Уведомление о добавлении услуги путем передачи
		*/
	}

	public function __ava__sendTransmitServiceMailOldOwner($eml, $params = array()){
		/*
			Уведомление о добавлении услуги путем передачи бывшему владельцу
		*/
	}


	/********************************************************************************************************************************************************************

																		Связка с другими модулями

	*********************************************************************************************************************************************************************/

	public function __ava__ticketAccessForm($obj, $params = array()){		$obj->addFormBlock(
			$params['fObj'],
			_W.'modules/billing/forms/ticket_access.php',
			array(
				'mod' => $this->getMod(),
				'modName' => $this->Core->getModuleName($this->getMod()),
				'services' => $this->getServices(),
				'packages' => $this->getAllPackages(),
				'levels' => Library::array_merge(array('' => '{Call:Lang:modules:billing:neimeiushchi}'), $this->getLoyaltyLevels())
			)
		);	}


	/********************************************************************************************************************************************************************

																		Функции встроенной проверки форм

	*********************************************************************************************************************************************************************/

	public function isValidPkgValue($str, $var, $obj){		/*
			Проверяет что значение может быть валидным значением характеристики пакета
		*/

		if((!regExp::float($str) || $str < 0) && regExp::Lower($str) != 'unlimit') return false;
		return true;	}

	public function isValidOrderedValue($str, $var, $obj){
		/*
			Проверяет что значение может быть валидным значением характеристики пакета
		*/

		if($return = self::isValidPkgValue($str, $var, $obj)){			if(!empty($obj->matrix[$var]['min']) && $obj->matrix[$var]['min'] > $str) $obj->setError($var, 'Минимум - '.$obj->matrix[$var]['min']);
			elseif(!empty($obj->matrix[$var]['max']) && $obj->matrix[$var]['max'] < $str) $obj->setError($var, 'Максимум - '.$obj->matrix[$var]['max']);
			elseif(regExp::Lower($str) == 'unlimit' && empty($obj->matrix[$var]['unlimit'])) $obj->setError($var, 'Заказ безлимита не разрешен');
		}

		return $return;
	}

	public function modifyIsEmpty($vars){		/*
			Проверяет есть ли параметры для модификации
		*/

		foreach($vars as $i => $e) if($e) return false;		return true;	}

	public function isUnlimit($str){		if(is_string($str) && regExp::lower($str) == 'unlimit') return true;
		return false;	}

	public function inventPromoCode(){		/*
			Придумывает промо-код
		*/

		$return = array();
		for($i = 0; $i < 5; $i ++){			$return[] = regExp::upper(Library::inventStr(5));		}

		return implode('-', $return);	}


	/********************************************************************************************************************************************************************

																		Задачи выполняемые по Cron

	*********************************************************************************************************************************************************************/

	protected function func_autoProlongAccs(){
		/*
			Автоматическое продлуние аккаунтов
		*/

		$this->Core->setFlag('rawOutput');
		$lim = (int)(time() + ($this->Core->getParam('termAutoProlong', $this->mod) * 86400));

		foreach($this->DB->columnFetch(array('order_services', '*', 'id', "`step`=1 AND `paid_to`<=$lim AND `auto_prolong`>0", "`paid_to`")) as $i => $e){
			//Получаем срок, проверяем баланс
			$term = $e['auto_prolong'];
			$sum = ($e['price'] + $e['modify_price']) * $term;

			if(!$this->checkBalance($e['client_id'], $sum, $this->pkgParam($e['service'], $e['package'], 'currency'))){
				$balance = $this->getClientBalance($e['client_id'], true);

				if($e['auto_prolong_fract'] && $balance > 0){
					$term = $balance / ($e['price'] + $e['modify_price']);
					$sum = $balance;
				}
				else continue;
			}

			$pkgData = $this->serviceData($e['service'], $e['package']);
			$term2 = Dates::term2sec($pkgData['base_term'], $term);


			//Продляем
			$eId = $this->addNewOrderEntry('prolong', $e['client_id'], $e['service'], $e['package'], 0, $i);
			$this->setOrderEntryCaption($eId, 'Автоматическое продление услуги для "'.$e['ident'].'"');
			$this->setOrderEntryUserParams($eId, $term, $e['ident']);

			$this->setOrderEntryPayParams($eId, '', $e['price'], '', $e['modify_price'], '', $e['price'], '', '', array(), $sum, $sum);
			$this->setOrderEntryCreateParams($eId, $e['paid_to'] + $term2);
			$this->setEntry($eId, $i);
		}
	}

	public function func_sendNotifies(){
		/*
			Рассылает уведомления юзерам
		*/

		$this->Core->setFlag('rawOutput');
		$t = time();

		$baseTerms = regExp::split(",", $this->Core->getParam('termFinishNotify', $this->mod));
		$termsByPkg = array();
		$filter = array();

		$mailTemplates = array();
		$adminMailTemplates = array();
		$adminEmails = array();

		$rootEml = $this->getAdminNotifyEml();
		$notifyTmpl = $this->getTmplParams('termFinishService');
		$rootNotifyTmpl = $this->Core->getParam('termFinishMail', $this->mod) ? $this->getTmplParams('termFinishServiceAdmin') : false;


		//Готовимъ ссылкi
		foreach($this->getAllPackages() as $i => $e){
			foreach($e as $i1 => $e1){
				$pkgData = $this->serviceData($i, $i1);
				if($pkgData['service_type'] == 'prolonged'){
					if($pkgData['notify_rights']['notify_settings_type'] == 'usePersonal'){
						$termsByPkg[$i][$i1] = regExp::split(",", $pkgData['notify_rights']['term_finish_notify']);
						if($pkgData['notify_rights']['notify_term_finish']) $mailTemplates[$i][$i1] = $this->getTmplParams($pkgData['notify_rights']['mail_tmpl_term_finish']);

						if($pkgData['notify_rights']['notify_admin_term_finish']){
							$adminMailTemplates[$i][$i1] = $this->getTmplParams($pkgData['notify_rights']['mail_tmpl_term_finish_admin']);
							$adminEmails[$i][$i1] = $this->Core->getAdminEml($pkgData['notify_rights']['term_finish_rcpt_admin']);
						}
					}
					else{
						$termsByPkg[$i][$i1] = $baseTerms;
						$mailTemplates[$i][$i1] = $notifyTmpl;

						if($rootNotifyTmpl){
							$adminMailTemplates[$i][$i1] = $rootNotifyTmpl;
							$adminEmails[$i][$i1] = $rootEml;
						}
					}

					if(empty($mailTemplates[$i][$i1]) && empty($adminMailTemplates[$i][$i1])) unset($termsByPkg[$i][$i1]);
				}
			}
		}


		//Фильтр услуг которым отправим уведомленiе
		foreach($termsByPkg as $i => $e){
			foreach($e as $i1 => $e1){
				$filter2 = array();
				foreach($e1 as $i2 => $e2){
					$e2 = (int)trim($e2);
					if($e2 !== '') $filter2[$i2] = "ROUND((t1.paid_to - $t) / 86400)=$e2";
				}

				if($filter2) $filter[$i.'_'.$i1] = "(t1.service='$i' AND t1.package='$i1' AND (t1.step=1 OR t1.step=0) AND (".implode(' OR ', $filter2)."))";
			}
		}


		//Получаем акки по которым отправить уведомления
		$accs = array();
		$usersFilter = array();
		$sendResults = array('user' => array(), 'admin' => array());


		if($filter){
			$p = $this->DB->getPrefix();
			$t1 = $p.'order_services';
			$t2 = $p.'clients';
			$t3 = $p.'connections';

			$req = $this->DB->Req("SELECT t1.id, t1.client_id, t1.ident, t1.service, t1.package, t1.paid_to, t1.server, t2.user_id, t3.login_host ".
				"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.client_id=t2.id LEFT JOIN $t3 AS t3 ON t1.server=t3.name ".
				"WHERE ".implode(' OR ', $filter));

			while($r = $req->fetch()){
				$accs[$r['id']] = $r;
				$usersFilter[$r['user_id']] = 1;
			}
		}


		//Отправляем уведомления
		if($usersFilter){
			$users = $this->Core->DB->columnFetch(array('users', array('eml', 'login'), 'id', $this->getEntriesWhere($usersFilter)));

			foreach($accs as $i => $e){
				$e['user_eml'] = $users[$e['user_id']]['eml'];
				$e['user_login'] = $users[$e['user_id']]['login'];
				$sendResults['user'][$i] = $this->mail($e['user_eml'], $mailTemplates[$e['service']][$e['package']], $e);

				if(!empty($adminEmails[$e['service']][$e['package']])){					$sendResults['admin'][$i] = $this->mail($adminEmails[$e['service']][$e['package']], $adminMailTemplates[$e['service']][$e['package']], $e);				}
			}
		}

		return $sendResults;
	}

	public function func_suspendAccs(){
		/*
			Блокировка и удаление должников
		*/

		$this->Core->setFlag('rawOutput');
		$t = time();

		$baseDelTerm = $this->Core->getParam('termFinishDel', $this->mod);
		$baseSuspTerm = $this->Core->getParam('termFinishSuspend', $this->mod);
		$filter = $suspTerm = $delTerm = $suspend = $deleted = array();

		foreach($this->getServices() as $i => $e){
			$sData = $this->serviceData($i);

			foreach($this->getPackages($i) as $i1 => $e1){
				$pData = $this->serviceData($i, $i1);
				$suspTerm[$i][$i1] = ($pData['notify_rights']['notify_settings_type'] == 'usePersonal') ? $pData['notify_rights']['term_finish_suspend'] : $baseSuspTerm;
				$delTerm[$i][$i1] = ($pData['notify_rights']['notify_settings_type'] == 'usePersonal') ? $pData['notify_rights']['term_finish_del'] : $baseDelTerm;

				$lim = $t - ((($suspTerm[$i][$i1] > $delTerm[$i][$i1]) ? $delTerm[$i][$i1] : $suspTerm[$i][$i1]) * 86400);
				$filter[] = "(`service`='".db_main::Quot($i)."' AND `package`='".db_main::Quot($i1)."' AND `paid_to`<=$lim)";
			}
		}

		$filter = '('.implode(' OR ', $filter).') AND (`step`=0 OR `step`=1)';

		foreach($this->DB->columnFetch(array('order_services', array('service', 'package', 'paid_to', 'step', 'server', 'ident'), 'id', $filter, 'paid_to')) as $i => $e){
			if($e['paid_to'] <= ($t - ($delTerm[$e['service']][$e['package']] * 86400))){
				$deleted[$e['service']][$i] = $e['ident'];
			}
			elseif(($e['step'] == 1) && ($e['paid_to'] <= ($t - ($suspTerm[$e['service']][$e['package']] * 86400)))){
				$suspend[$e['service']][$i] = $e['ident'];
			}
		}

		foreach($deleted as $i => $e){
			if($e){
				$list = array();
				foreach($e as $i1 => $e1){
					if($sId = $this->addDeleteOrder($i1, 'term')){
						$list[$sId] = array('auto' => true, 'notify' => true);
					}
				}

				$this->setDeleteServiceList($i, $list);
			}
		}

		foreach($suspend as $i => $e){
			if($e){
				$list = array();
				foreach($e as $i1 => $e1){
					if($sId = $this->addSuspendOrder($i1, 'term')){
						$list[$sId] = array('auto' => true, 'notify' => true);					}				}

				$this->setSuspendServiceList($i, $list);
			}
		}
	}
}

?>