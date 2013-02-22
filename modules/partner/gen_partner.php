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



class gen_partner extends ModuleInterface{
	//Для партнеров
	private $regGrp;
	private $groups;
	private $groupParams;
	private $partnersData;
	private $partnersDataById;

	//Для банеров
	private $banners;
	private $bannersList;

	//Для сайтов
	private $siteGroups = false;

	//Базовые расценки
	private $estimations = array();

	//Для платежей
	private $currencyParams = false;
	private $defaultCurrency = false;
	private $currencyList = array();

	private $paymentParams = false;
	private $paymentList = false;
	private $payExtensions = array();

	//Для статистики
	private $partnerOrdersStat = array();

	//Отчисления
	private $prepareOrders = array();
	private $prepareOrdersByEntry = array();
	private $payOrders = array();
	private $payOrdersByEntry = array();


	protected function __init(){
		/*
			Дополнительная инициализация модуля
		 */

		$this->setContent('<link rel="stylesheet" type="text/css" href="'.$this->Core->getModuleTemplateUrl($this->mod).'style.css" />', 'head');
	}

	public function __ava____authUser($obj){
		/*
			Доп. аутентификация пользовтеля
		*/

		if($partnerData = $this->DB->rowFetch(array('partners', '*', "`user_id`='".$obj->getUserId()."' AND `status`>=-1"))){
			$c = $this->getMainCurrencyName();
			$mp = $this->Core->getModuleParams($this->mod);
			$partnerData = Library::array_merge(Library::unserialize($partnerData['extra']), $partnerData);
			$partnerData['vars'] = Library::cmpUnserialize($partnerData['vars']);

			switch($partnerData['status']){				case -1: $status = '{Call:Lang:modules:partner:zablokirovan1}'; break;
				case 0: $status = '{Call:Lang:modules:partner:ozhidaetprov}'; break;
				case 1: $status = '{Call:Lang:modules:partner:rabotaet1}'; break;
			}

			$obj->userInfoTemplateParams[$this->mod] = array(
				'name' => $mp['text'],
				'params' => array(
					'date' => array(
						'name' => '{Call:Lang:modules:partner:partnersozda}',
						'value' => Dates::dateTime($partnerData['date']),
					),
					'login' => array(
						'name' => '{Call:Lang:modules:partner:psevdonim}',
						'value' => $partnerData['login'],
					),
					'grp' => array(
						'name' => '{Call:Lang:modules:partner:gruppa}',
						'value' => $this->getPartnerGroupName($partnerData['grp']),
					),
					'balance' => array(
						'name' => '{Call:Lang:modules:partner:balans}',
						'value' => Library::HumanCurrency($partnerData['balance']).' '.$c,
					),
					'all_pays' => array(
						'name' => '{Call:Lang:modules:partner:vsegozachisl}',
						'value' => Library::HumanCurrency($partnerData['all_pays']).' '.$c,
					),
					'status' => array(
						'name' => '{Call:Lang:modules:partner:status}',
						'value' => $status,
					),
					'link' => array(
						'name' => '{Call:Lang:modules:partner:ssylka}',
						'value' => '<input type="text" value="'._D.'index.php?mod='.$this->mod.'&func=click&partner='.$partnerData['login'].'" />',
					)
				)
			);

			return array('partnerId' => $partnerData['id'], 'partnerData' => $partnerData);
		}
	}

	public function __ava____registration($obj, $params){
		/*
			Добавляет пользователю клиента
		*/

		if(($ref = $this->Core->getGPCVar('c', 'referedBy')) && $this->Core->getGPCVar('c', 'referedByMod') == $this->mod && $this->DB->cellFetch(array('partners', 'id', "`login`='$ref'"))){
			$this->addFormBlock($params['fObj'], 'billing_order', array('referer' => $ref));
		}
	}

	public function __ava____registrationCheck($obj){
		/*
			Добавляет пользователю клиента
		*/

		if(!empty($obj->values['refered_by']) && !$this->DB->cellFetch(array('partners', 'id', "`login`='{$obj->values['refered_by']}'"))){
			$obj->setError('refered_by', 'Указанный партнер не был найден');
		}
	}

	public function __ava____registrationAdd($obj, $userId){
		/*
			Добавляет пользователю клиента
		*/

		if(!empty($obj->values['refered_by'])){			$this->setUser2Partner($userId, $obj->values['refered_by']);		}
	}

	public function __ava____map($obj){
		/*
			Карта сайта
				- Регистрация в партнерке
		*/

		return array(array('name' => 'Регистрация в партнерке', 'link' => 'index.php?mod='.$this->mod.'&func=partnerReg'));
	}


	/********************************************************************************************************************************************************************

																		"Общение" с биллингом

	*********************************************************************************************************************************************************************/

	public function __billing_setOrderEntryPayParams($obj, $params){		/*
			Подготавливает предварительный расчет платежа за заказ. Вызывается из setOrderEntryPayParams
			В params передается:
				id = entry_id
		*/

		$eData = $obj->getOrderEntry($params['id'], true);
		$pkgData = $obj->serviceData($eData['service'], $eData['package']);

		if($eData['total'] > 0 && $ref = $this->getRefererByUserId($obj->getUserIdByClientId($eData['client_id']))){
			$mod = $obj->getMod();
			$fields = array('partner_id' => $ref, 'date' => $eData['date'], 'mod' => $mod, 'client_id' => $eData['client_id'], 'object_type' => 'order', 'object_id' => $params['id'], 'vars' => array('parts' => array()));

			if($pkgData['service_type'] == 'prolonged'){
				$baseTerm = Dates::term2sec($pkgData['base_term'], 1);
				$start = $eData['date'] + Dates::term2sec($pkgData['test_term'], 1);

				if($eData['entry_type'] == 'new'){
					$end = $start + $baseTerm;					$fields['vars']['parts']['price'][] = $this->getOrderPayPrepareParams($obj, $params['id'], $ref, 'price', $start, $end < $eData['paid_to'] ? $end : $eData['paid_to']);

					foreach($this->getOrderPayTerms($end, $eData['paid_to'], $baseTerm) as $i => $e){						$fields['vars']['parts']['price2'][] = $this->getOrderPayPrepareParams($obj, $params['id'], $ref, 'price2', $e['start'], $e['end']);					}
				}
				elseif($eData['entry_type'] == 'prolong'){					foreach($this->getOrderPayTerms($start, $eData['paid_to'], $baseTerm) as $i => $e){
						$fields['vars']['parts']['prolong_price'][] = $this->getOrderPayPrepareParams($obj, $params['id'], $ref, 'prolong_price', $e['start'], $e['end']);
					}
				}

				if($eData['modify_price'] > 0){
					foreach($this->getOrderPayTerms($start, $eData['paid_to'], $baseTerm) as $i => $e){
						$fields['vars']['parts']['modify_price'][] = $this->getOrderPayPrepareParams($obj, $params['id'], $ref, 'modify_price', $e['start'], $e['end']);
					}
				}
			}

			if($eData['install_price'] > 0) $fields['vars']['parts']['install_price'][] = $this->getOrderPayPrepareParams($obj, $params['id'], $ref, 'install_price');
			if($eData['modify_install_price'] > 0) $fields['vars']['parts']['modify_install_price'][] = $this->getOrderPayPrepareParams($obj, $params['id'], $ref, 'modify_install_price');

			$fields['sum'] = 0;
			$fields['base_sum'] = 0;

			foreach($fields['vars']['parts'] as $i => $e){				foreach($e as $i1 => $e1){					$fields['base_sum'] += $e1['base_sum'];
					$fields['sum'] += $e1['pay'];
				}			}

			if(!$this->DB->Ins(array('order_prepares', $fields))) $this->DB->Upd(array('order_prepares', $fields, "`mod`='$mod' AND `object_type`='order' AND `object_id`='{$params['id']}'"));
		}
	}

	public function __billing_setCreateServiceForm($obj, $params, &$j){		/*
			Выводит таблицу где отображен расчет партнерского вознаграждения. Вызывается из setCreateServiceForm
			В params используется:
			 - fObj = объект формы
			 - id = entry_id
		*/

		if($opData = $this->getPrepareOrderByEntry($obj->getMod(), 'order', $params['id'])){
			$eData = $obj->getOrderEntry($params['id']);

			$this->addFormBlock(
				$params['fObj'],
				'billing_gen_entry',
				array('id' => $params['id'], 'prefix' => $this->mod, 'cur' => $obj->pkgCurrency($eData['service'], $eData['package']), 'opData' => $opData, 'style' => $this->getPriceParam($obj, $params['id'], 'pay_moment')),
				array(),
				'block'.$j
			);
		}
	}

	public function __billing_setEntry($obj, $params){		/*
			Модифицирует данные предварительного расчета оплаты основываясь на внесенных в форму значенияхъ.
			В params передается: id = entry_id, prefix, postfix, values
		*/

		if($opData = $this->getPrepareOrderByEntry($obj->getMod(), 'order', $params['id'])){
			$style = $this->getPriceParam($obj, $params['id'], 'pay_moment');
			$p = $params['prefix'].$this->mod;

			if($style == 'portioned-pre' || $style == 'portioned-post'){
				$fields = array('sum' => 0, 'vars' => array('parts' => array()));

				foreach($opData['vars']['parts'] as $i => $e){					foreach($e as $i1 => $e1){						$fields['vars']['parts'][$i][$i1]['pay'] = $params['values'][$p.'_'.$i.'_'.$i1.'_'.$params['postfix']];
						$fields['vars']['parts'][$i][$i1]['pay_moment'] = $params['values'][$p.'_'.$i.'_pay_'.$i1.'_'.$params['postfix']];
						$fields['sum'] += $fields['vars']['parts'][$i][$i1]['pay'];
					}				}			}
			else{				$fields = array('sum' => $params['values'][$p.'_sum_'.$params['postfix']]);			}

			$this->DB->Upd(array('order_prepares', $fields, "`mod`='".$this->mod."' AND `object_type`='order' AND `object_id`='{$params['id']}'"));
		}
	}

	public function __billing_payService($obj, $params){
		/*
			Вносит платеж партнеру. В $params входит: id = entry_id
		*/

		if($opData = $this->getPrepareOrderByEntry($obj->getMod(), 'order', $params['id'])){			$style = $this->getPriceParam($obj, $params['id'], 'pay_moment');
			if($style == 'portioned-pre' || $style == 'portioned-post'){
				foreach($opData['vars']['parts'] as $i => $e){					$e['order_prepare_id'] = $opData['id'];
					$e['partner_id'] = $opData['partner_id'];
					$e['date'] = $opData['date'];
					$e['mod'] = $opData['mod'];

					$e['client_id'] = $opData['client_id'];
					$e['object_type'] = $opData['object_type'];
					$e['object_id'] = $opData['object_id'];
					$this->DB->Ins(array('order', $e));
				}
			}
			else{
				$eData = $obj->getOrderEntry($params['id']);

				$this->DB->Ins(
					array(
						'order',
						array(
							'order_prepare_id' => $opData['id'],
							'partner_id' => $opData['partner_id'],
							'date' => $opData['date'],
							'mod' => $opData['mod'],
							'client_id' => $opData['client_id'],
							'object_type' => $opData['object_type'],
							'object_id' => $opData['object_id'],
							'base_sum' => $eData['total'],
							'pay_moment' => $opData['date'],
							'service' => $eData['service'],
							'pkg' => $eData['package']
						)
					)
				);
			}

			foreach($this->DB->columnFetch(array('order', 'id', array('pay'), "`date`<".time()." AND `order_prepare_id`={$opData['id']} AND `status`<1")) as $e){				if($this->setOrder($e)){					$obj->setMessage($this->mod.'_pay', 'Партнеру зачислено '.$pay);				}
				else{					$obj->setMessage($this->mod.'_pay', 'Возникли проблемы при зачислении средств партнеру, средства не зачислены', 'error');				}			}
		}
	}


	/********************************************************************************************************************************************************************

																		Отчисления за заказы

	*********************************************************************************************************************************************************************/

	public function __ava__setOrder($id){		/*
			Проводит зачисление средств по пользовательскому заказу
		*/

		$oData = $this->getPayOrder($id);
		return $this->setPartnerPay($oData['partner_id'], 'order', $oData['pay'], $id);
	}

	public function __ava__getPrepareOrder($id, $force = false){
		/*
			Возвращает сведения о предварительном заказе
		*/

		if(!isset($this->prepareOrders[$id]) || $force){
			$this->prepareOrders[$id] = $this->DB->rowFetch(array('order_prepares', '*', "`id`='$id'"));
			$this->prepareOrders[$id]['vars'] = Library::unserialize($this->prepareOrders[$id]['vars']);
			$this->prepareOrdersByEntry[$this->prepareOrders[$id]['mod']][$this->prepareOrders[$id]['order_type']][$this->prepareOrders[$id]['order_id']] = $this->prepareOrders[$id];
		}

		return $this->prepareOrders[$id];
	}

	public function __ava__getPrepareOrderByEntry($mod, $oType, $oId, $force = false){
		/*
			Возвращает сведения о предварительном заказе
		*/

		if(!isset($this->prepareOrdersByEntry[$mod][$oType][$oId]) || $force){
			if($this->prepareOrdersByEntry[$mod][$oType][$oId] = $this->DB->rowFetch(array('order_prepares', '*', "`mod`='$mod' AND `object_type`='$oType' AND `object_id`='$oId'"))){				$this->prepareOrdersByEntry[$mod][$oType][$oId]['vars'] = Library::unserialize($this->prepareOrdersByEntry[$mod][$oType][$oId]['vars']);
				$this->prepareOrders[$this->prepareOrdersByEntry[$mod][$oType][$oId]['id']] = $this->prepareOrdersByEntry[$mod][$oType][$oId];
			}
		}

		return $this->prepareOrdersByEntry[$mod][$oType][$oId];
	}

	public function __ava__getPayOrder($id, $force = false){
		/*
			Возвращает сведения о предварительном заказе
		*/

		if(!isset($this->payOrders[$id]) || $force){
			$this->payOrders[$id] = $this->DB->rowFetch(array('order', '*', "`id`='$id'"));
			$this->payOrdersByEntry[$this->payOrders[$id]['mod']][$this->payOrders[$id]['order_type']][$this->payOrders[$id]['order_id']] = $this->payOrders[$id];
		}

		return $this->payOrders[$id];
	}

	public function __ava__getPayOrderByEntry($mod, $oType, $oId, $force = false){
		/*
			Возвращает сведения о предварительном заказе
		*/

		if(!isset($this->payOrdersByEntry[$mod][$oType][$oId]) || $force){
			$this->payOrdersByEntry[$mod][$oType][$oId] = $this->DB->rowFetch(array('order', '*', "`mod`='$mod' AND `object_type`='$oType' AND `object_id`='$oId'"));
			$this->payOrders[$this->payOrdersByEntry[$mod][$oType][$oId]['id']] = $this->payOrdersByEntry[$mod][$oType][$oId];
		}

		return $this->payOrdersByEntry[$mod][$oType][$oId];
	}

	public function __ava__getOrderPayPrepareParams($obj, $eId, $partnerId, $part, $start = 0, $end = 0){		/*
			Возвращает массив параметров для внесения записи по зачислению партнеру
		*/

		$eData = $obj->getOrderEntry($eId);
		$base = ($start ? Dates::sec2term($obj->getBaseTerm($eData['service']), $end - $start) : 1) * $obj->calcOrderEntry($eId, $part);
		$style = $this->getPriceParam($obj, $eId, 'pay_moment');

		if($style == 'portioned-pre' && $start) $pm = $start;
		elseif($style == 'portioned-post' && $end) $pm = $end;
		else $pm = $eData['date'];

		return array('part' => $part, 'base_sum' => $base, 'period_start' => $start, 'period_end' => $end, 'pay_moment' => $pm, 'service' => $eData['service'], 'pkg' => $eData['package'], 'pay' => $this->calculateOrderPays($obj, $eId, $base, $part, $partnerId));	}

	public function __ava__getOrderPayTerms($start, $end, $base){
		/*
			Возвращает список периодов для заказа в виде начала и конца в timestamp
		*/

		$return = array();
		for($i = $start; $i < $end; $i += $base){
			$return[] = array('start' => $i, 'end' => ($i + $base < $end) ? $i + $base : $end);		}

		return $return;
	}

	public function __ava__calculateOrderPays($obj, $eId, $sum, $part, $partnerId){		/*
			По сумме возвращает сколько полагается партнеру
		*/
		return $sum * $this->getPriceParam($obj, $eId, 'price_'.$part) / 100;
	}

	public function __ava__getPriceParam($obj, $eId, $prefix){		/*
			Возвращает параметр калькуляции для данной записи об услуге
		*/

		if($price = $this->getEstimations('orders')){
			$eData = $obj->getOrderEntry($eId);
			$m = $obj->getMod();
			list($grp, $t) = $this->getClientGrp($m, $eData['client_id']);

			if($price['pay_service_style_'.$m.'_'.$eData['service'].'_'.$t.'_'.$grp] == 'pkg') return $price[$prefix.$m.'_'.$eData['service'].'_'.$eData['package'].'_'.$t.'_'.$grp];
			elseif($price['pay_service_style_'.$m.'_'.$eData['service'].'_'.$t.'_'.$grp] == 'service') return $price[$prefix.$m.'_'.$eData['service'].'_'.$t.'_'.$grp];
		}

		return '';
	}

	public function __ava__getClientGrp($mod, $clientData){
		/*
			Возвращает тип клиента
		*/

		$params = $this->getEstimations('orders');

		switch($params['settings_style_by_client_'.$mod]){
			case 'uni':
				$type = $grp = '';
				break;

			case 'group':
				$type = '_settings';
				if(!$grp = $clientData['loyal_level']){
					if($clientData['all_payments'] == 0) $grp = 'new-clients';
					else $grp = 'old-clients';
				}
				break;
		}

		return array($grp, $type);
	}


































































	/********************************************************************************************************************************************************************

																		"Общение" с биллингом

	*********************************************************************************************************************************************************************/

	public function __billing_generateBill($obj, $params, &$cnt){
		/*
			Устанавливает параметры партнерских отчислений возникающих в процессе проводки счета
		*/

		$oData = $obj->getOrderParams($params['id']);

		if($pid = $this->getRefererByUserId($obj->getUserIdByClientId($oData['client_id']))){
			$this->addFormBlock(
				$params['fObj'],
				array(
					array(
						'partner_capt' => array(
							'type' => 'caption',
							'text' => '{Call:Lang:modules:partner:dliapartnera}'
						)
					),
					'billing_order',
				),
				array('referer' => $pid),
				array(),
				'block0'
			);

			$refList = $this->getTopReferals($pid);
			$refPrice = $this->getEstimations('referals');
			$mod = $obj->getMod();

			$cData = $obj->getClientData($oData['client_id']);
			$ps = $this->getOrderPayStyle($mod, $cData);
			$p = $obj->DB->Count(array('pays', "`client_id`='{$oData['client_id']}' AND `foundation_type`='balance'")) > 0 ? '2' : '';

			if($ps == 'pay'){
				$this->addFormBlock(
					$params['fObj'],
					'billing_pay',
					array(
						'sum' => $this->orderIsPayed($mod, 'pay', $oData['payment_transaction_id'], $p) ? 0 : $this->getPaySum($oData['total'], $mod, $cData, $p),
						'cur' => $obj->getMainCurrencyName(),
						'refs' => $refList,
						'refPrice' => $refPrice,
						'fromOrder' => 1
					),
					array(),
					'block0'
				);
			}
			elseif($ps == 'order'){				$j = 1;
				$hiddens = array('serviceEntries' => array(), 'oldPeriodEnd' => array());
				foreach($obj->getOrderEntries($params['id'], " AND t2.step!=0 AND t2.step!=-3") as $i => $e){					$hiddens['serviceEntries'][$e['order_service_id']] = $i;
					if($e['entry_type'] == 'prolong') $hiddens['oldPeriodEnd'][$e['order_service_id']] = $e['s_paid_to'];

					if($this->getPayOrders($mod, $cData, $e['service'], $e['package']) == 'new' && $obj->DB->count(array('order_services', "`client_id`='{$oData['client_id']}' AND `id`!='{$e['order_service_id']}' AND (`step`=2 OR `step`=-1 OR `step`=-2)"))){
						$termSum = $term2Sum = $prolongSum = $modifySum = $installSum = 0;
					}
					else{
						$termSum = $this->orderIsPayed($mod, 'service', $e['order_service_id'], '_term', $e['s_last_paid']) ? 0 :
							round($this->getPaySum($e['price'] - $e['discounts']['discountSum']['term'], $mod, $cData, '_term', $e['service'], $e['package']), 2);

						$term2Sum = $this->orderIsPayed($mod, 'service', $e['order_service_id'], '_term2', $e['s_last_paid']) ? 0 :
							round($this->getPaySum(($e['prolong_price'] * ($e['term'] - 1)) - $e['discounts']['discountSum']['term2'], $mod, $cData, '_term2', $e['service'], $e['package']), 2);

						$prolongSum = $this->orderIsPayed($mod, 'service', $e['order_service_id'], '_prolong', $e['s_last_paid']) ? 0 :
							round($this->getPaySum(($e['prolong_price'] * $e['term']) - $e['discounts']['discountSum']['term2'], $mod, $cData, '_prolong', $e['service'], $e['package']), 2);

						$modifySum = $this->orderIsPayed($mod, 'service', $e['order_service_id'], '_modify', $e['s_last_paid']) ? 0 :
							round($this->getPaySum(($e['modify_price'] * $e['term']) - $e['discounts']['discountSum']['modify'], $mod, $cData, '_modify', $e['service'], $e['package']), 2);

						$installSum = $this->orderIsPayed($mod, 'service', $e['order_service_id'], '_install', $e['s_last_paid']) ? 0 :
							round($this->getPaySum($e['install_price'] + $e['modify_install_price'] - $e['discounts']['discountSum']['install'], $mod, $cData, '_install', $e['service'], $e['package']), 2);
					}

					$this->addFormBlock(
						$params['fObj'],
						array(
							array(
								'partner_capt' => array(
									'type' => 'caption',
									'text' => '{Call:Lang:modules:partner:dliapartnera}'
								)
							),
							'billing_gen_bill',
						),
						array(
							'pid' => $pid,
							'termSum' => $termSum,
							'term2Sum' => $term2Sum,
							'prolongSum' => $prolongSum,
							'modifySum' => $modifySum,
							'installSum' => $installSum,
							'sum' => $e['entry_type'] == 'prolong' ? ($prolongSum + $modifySum) : ($termSum + $term2Sum + $modifySum + $installSum),
							'entryType' => $e['entry_type'],
							'cur' => $obj->getMainCurrencyName(),
							'refs' => $refList,
							'refPrice' => $refPrice,
							'id' => $e['id']
						),
						array(),
						'block'.$j
					);

					$j ++;
				}

				$params['fObj']->setHiddens($hiddens);			}
		}
	}

	public function __billing_pay($obj){		/*
			Установки в форму пополнения баланса
		*/

		$obj->addFormBlock(false, _W.'modules/partner/forms/billing_pay.php');
	}

	public function __billing_enrollPay($obj, $params, &$cnt){		/*
			Зачисляет средства на партнерский баланс при проведении платежа
		*/

		return;

		$m = $obj->getMod();
		$ref = $this->getRefererByUserId($obj->getUserIdByClientId($params['clientId']));
		if(!$ref) return false;

		$est = $this->getEstimations('orders');
		$cData = $obj->getClientData($params['clientId']);
		$ps = $this->getOrderPayStyle($m, $cData);
		$finish = array();

		if($params['foundationType'] == 'balance' && $ps == 'pay'){			//Если пополняется баланс

			if(!isset($obj->values['partner_set_type']) || $obj->values['partner_set_type'] != ''){				$finish['pay']['id'] = $this->setOrder($ref, $m, 'pay', $params['objectId'], $params['clientId'], $params['sum'], array('part' => $obj->DB->Count(array('pays', "`client_id`='{$params['clientId']}' AND `id`!='{$params['payId']}' AND `foundation_type`='balance'")) > 0 ? '2' : ''));

				if($obj->values['partner_set_type'] == 'manual'){					$finish['pay']['sum'] = $obj->values['pay2partner_'];				}
			}
			else return false;
		}		elseif($params['foundationType'] == 'service' && $ps == 'order'){
			//Если заказывается услуга

			$eId = isset($obj->values['serviceEntries'][$params['objectId']]) ? $obj->values['serviceEntries'][$params['objectId']] : '';
			$sP = $obj->getOrderedService($params['objectId'], true);
			$eP = $obj->getOrderEntry($eId, true);

			$pkgData = $obj->serviceData($sP['service'], $sP['package']);
			$payMom = $this->getPayMomentStyle($m, $cData, $sP['service'], $sP['package']);

			if($this->getPayOrders($m, $cData, $sP['service'], $sP['package']) == 'new' && $obj->DB->count(array('order_services', "`client_id`='{$params['clientId']}' AND `id`!='{$params['objectId']}' AND (`step`=2 OR `step`=-1 OR `step`=-2)")) > 0){				return false;			}

			$bt = Dates::term2sec($pkgData['base_term'], 1);
			$term = ($sP['paid_to'] - $sP['created']) / $bt;
			if($term < 0) $term = 0;

			$ft = ($sP['created'] != $sP['last_paid']) ? 0 : ($term > 1 ? 1 : $term);
			$pt = $term - $ft;
			list($grp, $type) = $this->getClientGrp($m, $cData);

			//Получаем базовые расценки
			$ip = isset($obj->values['install_price'.$eId]) ?
				$obj->values['install_price'.$eId] + $obj->values['modify_install_price'.$eId] :
				($eP['install_price'] !== '' ? ($eP['install_price'] + $eP['modify_install_price']) : ($pkgData['install_price'] + $pkgData['modify_install_price']));

			$p = (isset($obj->values['price'.$eId]) ?
				$obj->values['price'.$eId] : ($eP['price'] !== '' ? $eP['price'] : $pkgData['price'])) * $ft;

			$pp = (isset($obj->values['prolong_price'.$eId]) ?
				$obj->values['prolong_price'.$eId] : ($eP['prolong_price'] !== '' ? $eP['prolong_price'] : $pkgData['prolong_price'])) * $pt;

			$mp = isset($obj->values['modify_price'.$eId]) ?
				$obj->values['modify_price'.$eId] : ($eP['modify_price'] !== '' ? $eP['modify_price'] : false);
			$mp = $mp === false ? ($params['sum'] - $ip - $p - $pp) : ($mp * $term);

			$p2i = isset($obj->values['installSum2partner_'.$eId]) ? $obj->values['installSum2partner_'.$eId] : false;
			$p2t = isset($obj->values['orderSum2partnerTerm_'.$eId]) ? $obj->values['orderSum2partnerTerm_'.$eId] : false;
			$p2t2 = isset($obj->values['orderSum2partnerTerm2_'.$eId]) ? $obj->values['orderSum2partnerTerm2_'.$eId] : false;
			$p2p = isset($obj->values['orderSum2partnerProlong_'.$eId]) ? $obj->values['orderSum2partnerProlong_'.$eId] : false;
			$p2m = isset($obj->values['modifySum2partner_'.$eId]) ? $obj->values['modifySum2partner_'.$eId] : false;


			//Базовые настройки
			$extraParams = array('period_start' => $sP['last_paid'], 'period_end' => $sP['paid_to'], 'service' => $sP['service'], 'pkg' => $sP['package'], 'part' => '_install');
			if($eP['entry_type'] == 'prolong') $extraParams['period_start'] = $obj->values['oldPeriodEnd'][$params['objectId']];
			$staySum = -$params['sum'];
			if($staySum < 0) $staySum = 0;

			if($sP['created'] == $sP['last_paid']){				//Инсталляция				if($ip > $staySum) $ip = $staySum;

				if($ip > 0 && $p2i > 0){
					$staySum = $staySum - $ip;
					$finish['_install']['id'] = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $ip, $extraParams);
					if($p2i !== false) $finish['_install']['sum'] = $p2i;
				}

				//Первый срок заказа
				if($p > $staySum) $p = $staySum;
				if($p > 0 && $p2t > 0){					$staySum = $staySum - $p;					$extraParams['period_end'] = $bt >= 1 ? $extraParams['period_start'] + $bt : $sP['paid_to'];
					$extraParams['part'] = '_term';

					$finish['_term']['id'] = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $p, $extraParams);
					if($p2t !== false) $finish['_term']['sum'] = $p2t;
					$extraParams['period_start'] = $extraParams['period_end'];
				}
			}

			switch($payMom){				case 'immediate':
					//Если зачисление средств происходит сразу

					$extraParams['period_end'] = $sP['paid_to'];
					if($pp > $staySum) $pp = $staySum;

					if($pp > 0 && ($p2t2 > 0 || $p2p > 0)){
						$staySum = $staySum - $pp;

						if($sP['created'] == $sP['last_paid']){
							//Последующие срока при первой оплате

							$extraParams['part'] = '_term2';
							$finish['_term2']['id'] = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $pp, $extraParams);
	 						if($p2t2 !== false) $finish['_term2']['sum'] = $p2t2;
						}
						else{
							//Продление

							$extraParams['part'] = '_prolong';
							$finish['_prolong']['id'] = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $pp, $extraParams);
	 						if($p2p !== false) $finish['_prolong']['sum'] = $p2p;
						}
					}

					if($mp > $staySum) $mp = $staySum;
					if($mp > 0 && $p2m > 0){
						//Модификация

						$extraParams['part'] = '_modify';
						$finish['_modify']['id'] = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $mp, $extraParams);
						if($p2m !== false) $finish['_modify']['sum'] = $p2m;
					}
					break;

				case 'portioned-post':
					unset($finish['_term']);

				case 'portioned-pre':
					if($pp > $staySum) $pp = $staySum;

					if($sP['created'] == $sP['last_paid']){
						//Если это первый заказ
						if($pp > 0){							//Сам заказ

							if($cnt = round(($sP['paid_to'] - $extraParams['period_start']) / $bt)){
								$averSum = round($pp / $cnt, 2);
		 						$staySum = $staySum - $pp;

								for($i = 0; $i < $cnt; $i ++){
									$extraParams['period_end'] = $extraParams['period_start'] + $bt;
									$extraParams['part'] = '_term2';
									$this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $averSum, $extraParams);
									$extraParams['period_start'] = $extraParams['period_end'];
								}
							}
						}

						if($mp > $staySum) $mp = $staySum;
						if($mp > 0 && ($p2p > 0 || $p2m > 0)){							//Модификация

							$extraParams['period_start'] = $sP['last_paid'];
							$cnt = round(($sP['paid_to'] - $extraParams['period_start']) / $bt);
							$averSum = round($mp / $cnt, 2);

							if($mp || $p2m > 0){
								for($i = 0; $i < $cnt; $i ++){
									$extraParams['period_end'] = $extraParams['period_start'] + $bt;
									$extraParams['part'] = '_modify';
									$r = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $averSum, $extraParams);
									$extraParams['period_start'] = $extraParams['period_end'];

									if(!isset($finish['_modify']) && $payMom == 'portioned-pre'){
										$finish['_modify']['id'] = $r;
										if($p2m !== false) $finish['_modify']['sum'] = $p2m;
									}
								}
							}
						}
					}
					else{						//Если это продление
						if($pp > 0){
							//Сам заказ

							$cnt = round(($sP['paid_to'] - $extraParams['period_start']) / $bt);
							$averSum = round($pp / $cnt, 2);
	 						$staySum = $staySum - $pp;

							if($mp > $staySum) $mp = $staySum;
							$averModSum = round($mp / $cnt, 2);

							if($averSum > 0 && ($p2p > 0 || $p2m > 0)){								for($i = 0; $i < $cnt; $i ++){
									$extraParams['period_end'] = $extraParams['period_start'] + $bt;
									$extraParams['part'] = '_prolong';
									$this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $averSum, $extraParams);
									$extraParams['period_start'] = $extraParams['period_end'];

									if($averModSum > 0 || $p2m > 0){										$extraParams['period_end'] = $extraParams['period_start'] + $bt;
										$extraParams['part'] = '_modify';
										$r = $this->setOrder($ref, $m, 'order', $params['objectId'], $params['clientId'], $averSum, $extraParams);
										$extraParams['period_start'] = $extraParams['period_end'];

										if(!isset($finish['_modify']) && $payMom == 'portioned-pre'){
											$finish['_modify']['id'] = $r;
											if($p2m !== false) $finish['_modify']['sum'] = $p2m;
										}
									}
								}
							}


						}
					}

					break;
			}
		}

		foreach($finish as $i => $e) $this->endSetOrder($e['id'], $cData, isset($e['sum']) ? $e['sum'] : false);
		return $finish;
	}


	/********************************************************************************************************************************************************************

																		Вспомогательные функции

	*********************************************************************************************************************************************************************/

	public function __ava__getOrderPayStyle($mod, $clientData){
		/*
			Возвращает способ зачисления средств партнерам (с платежа или с расхода на услугу)
		*/

		$params = $this->getEstimations('orders');
		list($grp, $type) = $this->getClientGrp($mod, $clientData);
		return isset($params['pay_style_'.$mod.'_'.$type.'_'.$grp]) ? $params['pay_style_'.$mod.'_'.$type.'_'.$grp] : '';
	}

	public function __ava__getPayMomentStyle($mod, $clientData, $service, $pkg){
		/*
			Возвращает способ зачисления средств партнерам (с платежа или с расхода на услугу)
		*/

		$params = $this->getEstimations('orders');
		list($grp, $type) = $this->getClientGrp($mod, $clientData);

		if($params['pay_service_style_'.$mod.'_'.$service.'_'.$type.'_'.$grp] == 'service'){			return $params['pay_moment'.$mod.'_'.$service.'_'.$type.'_'.$grp];		}
		elseif($params['pay_service_style_'.$mod.'_'.$service.'_'.$type.'_'.$grp] == 'pkg'){			return $params['pay_moment'.$mod.'_'.$service.'_'.$pkg.'_'.$type.'_'.$grp];
		}

		return false;
	}

	public function __ava__getPayOrders($mod, $clientData, $service, $pkg){
		/*
			Возвращает с каких заказов оплата (новые / все)
		*/

		$params = $this->getEstimations('orders');
		list($grp, $type) = $this->getClientGrp($mod, $clientData);

		if($params['pay_service_style_'.$mod.'_'.$service.'_'.$type.'_'.$grp] == 'service'){
			return $params['pay_order'.$mod.'_'.$service.'_'.$type.'_'.$grp];
		}
		elseif($params['pay_service_style_'.$mod.'_'.$service.'_'.$type.'_'.$grp] == 'pkg'){
			return $params['pay_order'.$mod.'_'.$service.'_'.$pkg.'_'.$type.'_'.$grp];
		}

		return false;
	}

	public function __ava__getPaySum($sum, $mod, $clientData, $payPart = '', $service = '', $pkg = ''){		/*
			Возвращает сумму которую положено выдать партнеру
		*/

		$price = $this->getEstimations('orders');

		if(!empty($price)){
			list($grp, $type) = $this->getClientGrp($mod, $clientData);

			if($service && $pkg){				if($price['pay_service_style_'.$mod.'_'.$service.'_'.$type.'_'.$grp] == 'pkg') $base = $price['price'.$payPart.$mod.'_'.$service.'_'.$pkg.'_'.$type.'_'.$grp];
				elseif($price['pay_service_style_'.$mod.'_'.$service.'_'.$type.'_'.$grp] == 'service') $base = $price['price'.$payPart.$mod.'_'.$service.'_'.$type.'_'.$grp];
				else $base = 0;
			}
			elseif(!$service && !$pkg){				$base = isset($price['balance_price'.$payPart.'_'.$mod.'_'.$type.'_'.$grp]) ? $price['balance_price'.$payPart.'_'.$mod.'_'.$type.'_'.$grp] : 0;			}
			else throw new AVA_Exception('Неверно переданы значения услуги и тарифа для определения партнерского вознаграждения');

			return $sum * $base / 100;
		}

		return 0;
	}

	protected function __ava__orderIsPayed($mod, $objectType, $objectId, $part = '', $pStart = 0){		/*
			Проверяет что данный заказ уже оплачен
		*/

		return $this->DB->cellFetch(array('order', 'id', "`mod`='{$mod}' AND `object_type`='{$objectType}' AND `object_id`='{$objectId}' AND `period_start`='{$pStart}' AND (`part`='{$part}' OR `part`='')"));
	}

	protected function __ava__setUser2Partner($userId, $partnerId){		/*
			Добавляет клиента партнеру
		*/

		if($this->DB->cellFetch(array('partners', 'id', "`login`='{$partnerId}'"))){
			return $this->DB->Ins(array('partner_users', array('user_id' => $userId, 'partner_id' => $partnerId)));
		}

		return false;	}

	private function fetchPartnerGroups(){		if(!is_array($this->groups)){			$this->groupParams = $this->DB->columnFetch(array('partner_groups', '*', 'name', "", "`sort`"));
			foreach($this->groupParams as $i => $e){				$this->groupParams[$i]['vars'] = Library::cmpUnserialize($this->groupParams[$i]['vars']);
				$this->groups = $e['text'];			}		}
	}

	public function __ava__getPartnerGroupParams($grp){		$this->fetchPartnerGroups();
		return $this->groupParams[$grp];	}

	public function __ava__getPartnerId(){
		/*
			Возвращает ID текущего клеента
		*/

		return empty($this->Core->User->extraParams[$this->mod]['partnerId']) ? false : $this->Core->User->extraParams[$this->mod]['partnerId'];
	}

	public function __ava__getRegGrp(){		/*
			Возвращает имя партнерской группы присваиваемой при регистрации
		*/

		if(!$this->regGrp) $this->regGrp = $this->DB->cellFetch(array('partner_groups', 'name', "`add_auto` AND `add_reg`"));
		return $this->regGrp;	}

	public function __ava__getPartnerGroups(){		/*
			Возвращает все группы партнеров
		*/

		$this->fetchPartnerGroups();
		return $this->groups;	}

	public function __ava__getPartnerGroupName($name){		/*
			Возвращает имя группы партнера
		*/

		if(!$name) return '{Call:Lang:modules:partner:net}';
		$this->fetchPartnerGroups();
		return $this->groups[$name];
	}

	public function __ava__getReferer(){		/*
			Возвращает текущего реферера
		*/

		return $this->Core->getGPCVar('c', 'referedBy');	}

	public function __ava__getRefererByUserId($userId){		/*
			Возвращает ID партнера который привел этого клиента
		*/

		return $this->DB->cellFetch(array('partner_users', 'partner_id', "`user_id`='{$userId}'"));	}

	public function __ava__getPartnerData($login){		/*
			Возвращает данные партнерского аккаунта
		*/
		if(empty($this->partnersData[$login])){			$this->partnersData[$login] = $this->DB->rowFetch(array('partners', '*', "`login`='$login'"));
			$this->partnersData[$login]['vars'] = Library::cmpUnserialize($this->partnersData[$login]['vars']);			$this->partnersDataById[$this->partnersData[$login]['id']] = $this->partnersData[$login];
		}

		return $this->partnersData[$login];
	}

	public function __ava__getPartnerDataById($id){
		/*
			Возвращает данные партнерского аккаунта
		*/

		if(empty($this->partnersDataById[$id])){
			$this->partnersDataById[$id] = $this->DB->rowFetch(array('partners', '*', "`id`='$id'"));
			$this->partnersDataById[$id]['vars'] = Library::cmpUnserialize($this->partnersDataById[$id]['vars']);
			$this->partnersData[$this->partnersDataById[$id]['login']] = $this->partnersDataById[$id];
		}

		return $this->partnersDataById[$id];
	}

	public function __ava__getUserByPartner($login){		$data = $this->getPartnerData($login);
		return $data['user_id'];	}

	public function __ava__getUserByPartnerId($id){
		$data = $this->getPartnerDataById($id);
		return $data['user_id'];
	}

	public function __ava__insertPartner($userId, $login, $params = array()){		/*
			Добавляет партнера
		*/

		$ins = array(
			'user_id' => $userId,
			'login' => $login,
			'grp' => isset($params['grp']) ? $params['grp'] : $this->getRegGrp(),
			'refered_by' => isset($params['refered_by']) ? $params['refered_by'] : $this->getReferer(),
			'date' => time(),
			'status' => isset($params['status']) ? $params['status'] : $this->Core->getParam('partnerRegFree', $this->mod)
		);

		$ins['extra'] = $this->getGeneratedFormValues(array('partner_reg_form', '*'), $params);
		foreach($ins['extra'] as $i => $e){			if($this->DB->issetField('partners', $i)){				$ins[$i] = $e;
				unset($ins['extra'][$i]);			}		}

		if(!$return = $this->DB->Ins(array('partners', $ins))) throw new AVA_Exception('Ошибка создания нового партнера');
		return $return;	}

	public function __ava__getEstimations($type){		/*
			Возвращает базовые расценки
		*/

		if(!$this->estimations){			$this->estimations = $this->DB->columnFetch(array('order_pays_estimations', 'vars', "name"));
			foreach($this->estimations as $i => $e){				$this->estimations[$i] = Library::cmpUnserialize($e);			}		}

		return $this->estimations[$type];	}


	/********************************************************************************************************************************************************************

																Клики / показы / отчисления за заказы

	*********************************************************************************************************************************************************************/

	public function __ava__setClick($login, $banner = false, $setReferals = true){
		/*
			Вызывается при клике
				2. Определяем стоимость клика
				3. Вносим все данные в таблицу clicks
				4. Пополняем баланс партнера
				5. Определяем суммы отчислений рефералам и пополняем их балансы
		*/

		$pData = $this->getPartnerData($login);
		$bpData = $this->getEstimations('banners');
		$price = 0;

		if($banner){			if(!empty($pData['vars']['type_'.$banner])) $price = $pData['vars']['click_pay_'.$banner];
			elseif($pData['grp'] && ($gData = $this->getPartnerGroupParams()) && !empty($gData['vars']['type_'.$banner])) $price = $gData['vars']['click_pay_'.$banner];
			else{				$bData = $this->getBannerPrice($banner);
				$price = $bData['click_pay'];			}
		}
		else{
			if(!empty($pData['vars']['usePersonalSettings'])) $price = $pData['vars']['partnerClickDefaultSum'];
			elseif($pData['grp'] && ($gData = $this->getPartnerGroupParams()) && !empty($gData['vars']['usePersonalSettings'])) $price = $gData['vars']['partnerClickDefaultSum'];
			else $price = $bpData['banner_click'];
		}

		$this->DB->trStart();
		$return = $this->DB->Ins(
			array(
				'click',
				array(
					'partner_id' => $login,
					'date' => time(),
					'referer' => Library::getUrlPart($this->Core->getGPCVar('s', 'HTTP_REFERER')),
					'banner' => $banner,
					'ip' => $this->Core->getGPCVar('s', 'REMOTE_ADDR')
				)
			)
		);

		if($return && $price > 0) $this->setPartnerPay($login, 'click', $price, $return);
		$this->DB->trEnd(true);
		if($return && $price && $setReferals) $this->setReferalsBonuses($login, $price, 'click', $return);		return $return;
	}

	public function __ava__setReferalsBonuses($login, $sum, $type, $payId){		/*
			Устанавливает реферальский вознаграждения
			$login здесь логин пользователя а не реферала.
		*/

		if($payData = $this->getEstimations('referals')){
			foreach($this->getTopReferals($login) as $i => $e){				if($insSum = $payData[$type.'_'.$i] * $sum / 100){					$this->setReferalSum($e['login'], $login, $i, $insSum, $type, $payId);
				}			}
		}
	}

	public function __ava__setReferalSum($login, $refId, $level, $sum, $type, $payId){		/*
			Устанавливает вознаграждение для данного конкретного реферала
		*/

		$this->DB->trStart();
		$return = $this->DB->Ins(
			array(
				'referals',
				array(
					'partner_id' => $login,
					'referal_id' => $refId,
					'pay_type' => $type,
					'pay_id' => $payId,
					'date' => time(),
					'level' => $level
				)
			)
		);

		if($return && $sum > 0 && $this->setPartnerPay($login, 'referals', $sum, $return)) $this->DB->trEnd(true);
		else $this->DB->trEnd(false);
		return $return;
	}

	protected function __ava__setPartnerPay($login, $type, $sum, $id = 0, $date = false){		/*
			Добавляет партнерский платеж
		*/

		$return = $this->DB->Ins(array('pays', array('partner_id' => $login, 'type' => $type, 'entry_id' => $id, 'date' => $date === false ? time() : $date, 'sum' => $sum)));

		if($return){
			$this->DB->Upd(
				array(
					'partners',
					array(
						'balance' => "`balance` + ".(float)$sum,
						'all_pays' => "`all_pays` + ".(float)$sum,
						'#isExp' => array('balance' => true, 'all_pays' => true)
					),
					"`login`='".db_main::quot($login)."'"
				)
			);
		}

		return $return;	}

	public function getReferals($login){		/*
			Возвращает сведения о всех нижних рефералах данного партнера, т.е. тех кто пришел по его рекомендации
		*/

		return array();	}

	public function __ava__getTopReferals($login, $limit = 'settings'){
		/*
			Возвращает сведения о всех рефералах верхнего уровня данного партнера, т.е. тех кто получает от него отчисления.
		*/

		if($limit == 'settings') $limit = $this->Core->getParam('partnerReferals', $this->mod);
		$return = array();

		if($by = $this->DB->cellFetch(array('partners', 'refered_by', "`login`='$login'"))){			$list = $this->DB->columnFetch(array('partners', array('user_id', 'id', 'grp', 'refered_by'), 'login', "`id`<=(SELECT id FROM ".$this->DB->getPrefix()."partners WHERE `login`='$by')", "`id` DESC"));
			$j = 1;

			while($by && ($limit === false || $j <= $limit)){				$return[$j] = $list[$by];
				$by = empty($list[$return[$j]['refered_by']]) ? '' : $return[$j]['refered_by'];
				$j ++;			}
		}

		return $return;
	}

	private function fetchBanners(){		/*
			Извлекает параметры банеров
		*/

		if(!$this->banners){			$this->banners = $this->DB->columnFetch(array('banners', '*', "name", "`show`", "`sort`"));
			foreach($this->banners as $i => $e){
				$this->bannersList[$i] = $e['text'];
			}		}	}

	public function getBanners(){
		/*
			Возвращает список банеров
		*/

		$this->fetchBanners();
		return $this->bannersList;
	}

	public function getBannerParams(){		/*
			Возвращает параметры банеров
		*/

		$this->fetchBanners();
		return $this->banners;	}

	private function fetchSiteGroups(){
		if($this->siteGroups === false){
			$this->siteGroups = $this->DB->columnFetch(array('site_groups', 'text', "name", "", "`sort`"));
		}
	}

	public function getSiteGroups(){
		$this->fetchSiteGroups();
		return $this->siteGroups;
	}



	/********************************************************************************************************************************************************************

															Формирвание данных по валютам и способам оплаты

	*********************************************************************************************************************************************************************/

	public function getMainCurrencyName(){
		$this->fetchCurrencies();
		return $this->currencyList[$this->defaultCurrency];
	}

	public function defaultCurrency(){
		/*
			Возвращает валюту по умолчанию
		*/

		$this->fetchCurrencies();
		return $this->defaultCurrency;
	}

	public function __ava__currencyName($name){
		/*
			Возвращает имя валюты по идентификатору
		*/

		$this->fetchCurrencies();
		if(!$name) return $this->currencyList[$this->defaultCurrency];
		else return $this->currencyList[$name];
	}

	public function getCurrency(){
		/*
			Возвращает список всех валют
		*/

		$this->fetchCurrencies();
		return $this->currencyList;
	}

	public function __ava__currencyParams($cur){
		/*
			Возвращает все параметры валюты
		 */

		if(!$cur) $cur = $this->defaultCurrency();
		else $this->fetchCurrencies();
		return $this->currencyParams[$cur];
	}

	public function __ava__getCurrencyNameByPkg($service, $pkg){
		/*
			Возвращает имя валюты для пакета
		*/

		$this->fetchServicesData($service);
		return empty($this->servicesData[$service][$pkg]['currency']) ? $this->getMainCurrencyName() : $this->currencyName($this->servicesData[$service][$pkg]['currency']);
	}

	public function fetchCurrencies(){
		/*
			Извлекает сведения обо всех валютах
		*/

		if(empty($this->currencyParams)){
			$this->currencyParams = $this->DB->columnFetch(array('currency', '*', 'name', '', "`sort`"));
			foreach($this->currencyParams as $i => $e){
				$this->currencyList[$i] = $e['text'];
				if($e['default']){
					$this->defaultCurrency = $i;
				}
			}
		}

		return $this->currencyParams;
	}

	public function getPayment(){
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

	public function __ava__currencyByPayment($payment){
		/*
			Возвращает данные валюты по способу оплаты
		*/

		$this->fetchCurrencies();
		$this->fetchPayments();
		return $this->currencyParams[$this->paymentParams[$payment]['currency']];
	}

	public function fetchPayments(){
		/*
			Извлекает сведения обо всех валютах
		*/

		if(empty($this->paymentParams)){
			$this->paymentParams = $this->DB->columnFetch(array('payments', '*', 'name', '', "`sort`"));
			foreach($this->paymentParams as $i => $e){
				$this->paymentParams[$i]['vars'] = Library::unserialize($this->paymentParams[$i]['vars']);
				$this->paymentList[$i] = $e['text'];
			}
		}

		return $this->paymentParams;
	}

	public function __ava__getPayExtensions(){
		if(!$this->payExtensions){
			$this->payExtensions = $this->DB->columnFetch(array('payment_extensions', 'mod', 'name', "", "`name`"));
		}
		return $this->payExtensions;
	}

	public function __ava__callPaymentExtension($ext, $func, $payId, $params = array()){
		/*
			Обращение к функции расширения для платежей
		*/

		$this->Core->loadExtension('partner', 'payments/'.$ext);
		$pObj = new $ext($this, $payId);
		if(method_exists($pObj, $func) || method_exists($pObj, '__ava__'.$func)) return $pObj->$func($params);
		else return false;
	}

	public function __ava__getSumInDefault($sum, $currency){
		/*
			Возвращает стоимость в валюте по умолчанию
		*/

		$params = $this->currencyParams($currency);
		return round($sum / $params['exchange'], 2);
	}

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

																				Статистика

	*********************************************************************************************************************************************************************/

	public function __ava__getOrderStat($partnerId = false, $mod = false, $clientId = false, $serviceId = false, $from = false, $to = false){		/*
			Формирует статистику отчисления по партнеру
			Выдает (по базовым суммам и отчислениям):
			 - начисленные отчисления
			 - условные отчисления
			 - отчисления за определенную часть заказа (за установку, за модификации и т.п.)
			 - отчисления по отдельным услугам и пакетам
			 - отчисления по определенным типам объекта (за заказ, за пополнение баланса)
		*/

		if($partnerId === false) $partnerId = $this->getPartnerId();
		if(!isset($this->partnerOrdersStat[$partnerId])){			$cFilter = array();
			$this->partnerOrdersStat[$partnerId]['orderEntries'] = array();

			$p = $this->DB->getPrefix();
			$t1 = $p.'pays';
			$t2 = $p.'order';

			foreach($this->DB->columnFetch("SELECT t1.id AS p_id, t1.date AS p_date, t1.sum AS p_sum, t1.descript AS p_descript, t2.* ".
				"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.entry_id=t2.id WHERE t1.type='order' AND t1.partner_id='{$partnerId}' ORDER BY t1.date DESC") as $e)
			{				$this->partnerOrdersStat[$partnerId]['orderEntries'][$e['id']] = $e;
				$cFilter[$e['mod']][$e['client_id']] = true;			}

			foreach($cFilter as $i => $e){				$this->partnerOrdersStat[$partnerId]['clients'][$i] = $this->Core->callModule($i)->DB->columnFetch(array('clients', '*', $this->getEntriesWhere($e)));			}
		}

		$return = array(
			'base' => array('enroll' => 0, 'byPart' => array(), 'byService' => array(), 'byObject' => array()),
			'pay' => array('enroll' => 0, 'byPart' => array(), 'byService' => array(), 'byObject' => array())
		);

		foreach($this->partnerOrdersStat[$partnerId]['orderEntries'] as $i => $e){			if(
				(!$clientId || ($clientId == $e['client_id'] && $mod == $e['mod'])) &&
				(!$from || $from <= $e['p_date']) && (!$to || $to >= $e['p_date']) &&
				(!$serviceId || ($serviceId == $e['object_id'] && $e['object_type'] == 'order'))
			){				if(!isset($return['pay']['byPart'][$e['part']])){
					$return['base']['byPart'][$e['part']] = 0;
					$return['pay']['byPart'][$e['part']] = 0;
				}

				if(!isset($return['pay']['byService'][$e['service']])){
					$return['base']['byService'][$e['service']] = 0;
					$return['pay']['byService'][$e['service']] = 0;
				}

				if(!isset($return['pay']['byObject'][$e['object_type']])){
					$return['base']['byObject'][$e['object_type']] = 0;
					$return['pay']['byObject'][$e['object_type']] = 0;
				}

				$return['base']['enroll'] += $e['base_sum'];
				$return['base']['byPart'][$e['part']] += $e['base_sum'];
				$return['base']['byService'][$e['service']] += $e['base_sum'];
				$return['base']['byObject'][$e['object_type']] += $e['base_sum'];

				$return['pay']['enroll'] += $e['p_sum'];
				$return['pay']['byPart'][$e['part']] += $e['p_sum'];
				$return['pay']['byService'][$e['service']] += $e['p_sum'];
				$return['pay']['byObject'][$e['object_type']] += $e['p_sum'];
			}		}

		return $return;	}


	/********************************************************************************************************************************************************************

																		Прочие служебные функции

	*********************************************************************************************************************************************************************/

	public function __ava__canView($type, $partnerId = false){		if(!$partnerId) $partnerId = $GLOBALS["Core"]->User->extraParams[$this->mod]["partnerId"];
		if(isset($GLOBALS["Core"]->User->extraParams[$this->mod]['partnerData']['vars']['usePersonalSettings']) && $GLOBALS["Core"]->User->extraParams[$this->mod]['partnerData']['vars']['usePersonalSettings'] == 'hand'){			return !empty($GLOBALS["Core"]->User->extraParams[$this->mod]['partnerData']['vars']['view'.$type]);		}
		elseif($GLOBALS["Core"]->User->extraParams[$this->mod]['partnerData']['grp']){			$grpData = $this->getPartnerGroupParams($GLOBALS["Core"]->User->extraParams[$this->mod]['partnerData']['grp']);
			if(isset($grpData['vars']['usePersonalSettings']) && $grpData['vars']['usePersonalSettings'] == 'hand'){
				return !empty($grpData['vars']['view'.$type]);
			}
		}

		return $this->Core->getParam('view'.$type, $this->mod);
	}

	public function __ava__canViewThisUser($userId, $partnerId = false){		/*
			Проверяет что пользователь ходит под указанным партнером
		*/
		if(!$partnerId) $partnerId = $GLOBALS["Core"]->User->extraParams[$this->mod]['partnerData']['login'];
		return (bool)$this->DB->cellFetch(array('partner_users', 'user_id', "`user_id`='$userId' AND `partner_id`='$partnerId'"));
	}


	/********************************************************************************************************************************************************************

																		Связка с другими модулями

	*********************************************************************************************************************************************************************/

	public function __ava__ticketAccessForm($obj, $params = array()){
		$obj->addFormBlock(
			$params['fObj'],
			_W.'modules/partner/forms/ticket_access.php',
			array(
				'mod' => $this->getMod(),
				'modName' => $this->Core->getModuleName($this->getMod()),
				'groups' => Library::array_merge(array('' => '{Call:Lang:modules:partner:neimeiushchi}'), $this->getPartnerGroups())
			)
		);
	}
}

?>