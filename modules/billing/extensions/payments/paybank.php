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



$GLOBALS['Core']->loadExtension('billing', 'paymentsObject');

class payBank extends paymentsObject{
	public function __ava__paymentForm($params){		/*
			Создает форму оплаты
		*/

		return $this->setDocument($params, 'bank');
	}

	public function __ava__act($params){
		/*
			Создает акт выполненныхъ роботъ
		*/

		if(empty($this->settings['orgName'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu}');
			return false;
		}

		$replaces = Library::array_merge($this->settings, $params);
		$this->getCalculationByEntry($params['entry'], $params['period'], $replaces);
		$tmpl = $this->getTemplate();

		$this->obj->setNewContent('', 'caption');
		$this->obj->setNewContent($tmpl['extra']['bank_style'][0]['content'], 'head');
		$this->obj->setContent($GLOBALS['Core']->readBlockAndReplace($GLOBALS['Core']->getModuleTemplatePath($this->obj->getMod()).'blanks.tmpl', 'act', $this->obj, $replaces, 'cover'));

		return true;
	}

	public function __ava__invoice($params){
		/*
			Создает акт выполненныхъ роботъ
		*/

		if(empty($this->settings['orgName'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu1}');
			return false;
		}

		$replaces = Library::array_merge($this->settings, $params);
		$this->getCalculationByEntry($params['entry'], $params['period'], $replaces);
		$tmpl = $this->getTemplate();

		$this->obj->setNewContent('', 'caption');
		$this->obj->setNewContent($tmpl['extra']['bank_style'][0]['content'], 'head');
		$this->obj->setContent($GLOBALS['Core']->readBlockAndReplace($GLOBALS['Core']->getModuleTemplatePath($this->obj->getMod()).'blanks.tmpl', 'invoice', $this->obj, $replaces, 'cover'));

		return true;
	}



	/********************************************************************************************************************************************************************

																		Добавление способа оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__setNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'orgName' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:polnoenaimen}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalin}'
				),
				'orgNameShort' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sokrashchenn}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalis}'
				),
				'inn' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:inn}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii}'
				),
				'kpp' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:kpp}'
				),
				'ogrn' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:ogrniliogrni}'
				),
				'okved' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:okvehdcherez}'
				),
				'city' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:gorod}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalig}'
				),
				'address' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:iuridicheski}',
					'warn' => '{Call:Lang:modules:billing:vyneiuridich}'
				),
				'postAddress' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:pochtovyjadr}',
					'warn' => '{Call:Lang:modules:billing:vynepochtovy}'
				),
				'leaderName' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:fiorukovodit}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalif}'
				),
				'leaderTitle' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:dolzhnostruk}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalid}'
				),
				'bank' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:nazvanievash}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalin1}'
				),
				'bankAddress' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:adresvashego}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalia}'
				),
				'bankAccount' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:nomerbankovs}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalin2}'
				),
				'bankCorrAccount' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:nomerkorresp}',
					'comment' => '{Call:Lang:modules:billing:vkliuchaiana}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalin3}'
				),
				'bik' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:bik}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalib}'
				),
				'nds' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:nds}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalin4}'
				),
				'stampImage' => array(
					'type' => 'file',
					'text' => '{Call:Lang:modules:billing:izobrazhenii}',
					'additional' => array('allow_ext' => array('.jpg', '.gif'), 'dstFolder' => $this->obj->Core->getParam('defaultFolder'))
				),
			)
		);

		return true;
	}

	public function __ava__checkNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		if(!$this->obj->check()) return false;
		return array(
			'orgName' => $this->obj->values['orgName'],
			'orgNameShort' => $this->obj->values['orgNameShort'],
			'inn' => $this->obj->values['inn'],
			'kpp' => $this->obj->values['kpp'],
			'ogrn' => $this->obj->values['ogrn'],
			'okved' => $this->obj->values['okved'],
			'city' => $this->obj->values['city'],
			'address' => $this->obj->values['address'],
			'postAddress' => $this->obj->values['postAddress'],
			'leaderName' => $this->obj->values['leaderName'],
			'leaderTitle' => $this->obj->values['leaderTitle'],
			'bank' => $this->obj->values['bank'],
			'bankAddress' => $this->obj->values['bankAddress'],
			'bankAccount' => $this->obj->values['bankAccount'],
			'bankCorrAccount' => $this->obj->values['bankCorrAccount'],
			'bik' => $this->obj->values['bik'],
			'nds' => $this->obj->values['nds'],
			'stampImage' => $this->obj->values['stampImage'],
		);
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	protected function __ava__setDocument($params, $tmplName){		if(empty($this->settings['orgName'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$replaces = $this->settings;
		$replaces['id'] = $params['id'];
		$replaces['descript'] = $params['descript'];
		$this->getCalculation($params['id'], $replaces);

		$this->obj->setNewContent('', 'caption');
		$tmpl = $this->getTemplate();
		$this->obj->setContent($GLOBALS['Core']->readBlockAndReplace($GLOBALS['Core']->getModuleTemplatePath($this->obj->getMod()).'blanks.tmpl', $tmplName, $this->obj, $replaces, 'cover'));
		$this->obj->setContent($GLOBALS['Core']->readBlockAndReplace($GLOBALS['Core']->getModuleTemplatePath($this->obj->getMod()).'blanks.tmpl', 'bank_style', $this->obj, $replaces, 'extra'));

		return true;
	}

	protected function getTemplate(){		return $GLOBALS['Core']->getTemplatePage($GLOBALS['Core']->getModuleTemplatePath($this->obj->getMod()).'blanks.tmpl');	}

	protected function getCalculation($id, &$replaces, $filter = ''){		/*
			Принимает ID транзакции. Создает расчет
		*/

		$replaces['calculation'] = array();
		$replaces['orderData'] = $this->obj->getTransactionParams($id);
		$replaces['clientData'] = $this->obj->getUserByClientId($replaces['orderData']['client_id']);

		$cData = $this->obj->currencyParams($replaces['orderData']['currency']);
		$nds = 0;

		if(empty($replaces['orderData']['object_id'])){			$nds = Library::nds($this->settings['nds'], $replaces['orderData']['sum']);			$replaces['calculation'][] = array(
				'name' => '{Call:Lang:modules:billing:zachislenies:'.Library::serialize(array($replaces['clientData']['id'], $replaces['clientData']['login'], $replaces['clientData']['user_id'])).'}',
				'price' => $replaces['orderData']['sum'] - $nds,
				'count' => '1',
				'unit' => '-',
				'total' => $replaces['orderData']['sum'] - $nds,
				'nds' => $nds
			);		}
		elseif($replaces['orderData']['object_type'] == 'orders'){
			foreach($this->obj->getOrderEntries($replaces['orderData']['object_id']) as $i => $e){
				$total = $this->obj->getTotalPrice($e['price'], $e['price2'], $e['prolong_price'], $e['term'], $e['install_price'], $e['modify_price'], $e['modify_install_price'], $e['entry_type']) - $e['discount'];
				$ndsCount = Library::nds($this->settings['nds'], $total);

				$total -= $ndsCount;
				$nds += $ndsCount;
				$price = $total / $e['term'];

				$replaces['calculation'][] = array(
					'name' => $e['entry_caption'],
					'price' => $price.$cData['text'],
					'count' => $e['term'],
					'unit' => Dates::rightCaseTerm($this->obj->getBaseTerm($e['service']), 1, 2),
					'total' => $total.$cData['text']
				);			}		}

		$replaces['total2'] = Library::humanCurrency($replaces['orderData']['sum']).$cData['text'];
		$replaces['ndsCount'] = Library::humanCurrency($nds).$cData['text'];
		$replaces['total'] = Library::humanCurrency($replaces['orderData']['sum'] - $nds).$cData['text'];

		$replaces['printSum'] = Library::printMoney($replaces['orderData']['sum'], $cData['text'], $cData['coin']);
		$replaces['date'] = $replaces['orderData']['date'];
	}

	protected function getCalculationByEntry($entry, $period, &$replaces){		/*
			Выводит калькуляцию по записи из счета
		*/

		$oData = $this->obj->getOrderParams($entry['order_id']);
		$entry['total'] = $this->obj->getTotalPrice($entry['price'], $e['price2'], $entry['prolong_price'], $entry['term'], $entry['install_price'], $entry['modify_price'], $entry['modify_install_price'], $entry['entry_type']);

		if($period){			$sData = $this->obj->serviceData($entry['service']);
			$entry['total'] = $entry['total'] / dates::termConvert($sData['base_term'], 'month');

			$f = $this->obj->dateByMonth($period, 1, 0);
			if($entry['date'] > $f){				$f = $entry['date'];
				$entry['total'] = $entry['total'] / (30.5 - date('d', $f));			}

			$t = $this->obj->dateByMonth($period, 0, 1);
			if($entry['s_paid_to'] < $t){				$t = $entry['s_paid_to'];				$entry['total'] = $entry['total'] / (30.5 - date('d', $t));
			}

			$entry['entry_caption'] .= '{Call:Lang:modules:billing:spo:'.Library::serialize(array(date('d.m.Y', $f), date('d.m.Y', $t))).'}';
			$entry['term'] = 1;
			$unit = Dates::rightCaseTerm('month', 1, 2);
		}
		else{			$unit = Dates::rightCaseTerm($this->obj->getBaseTerm($e['service']), 1, 2);		}
		$entry['total'] = round($entry['total'], 2);
		$nds = Library::nds($this->settings['nds'], $entry['total']);
		$replaces['total'] = Library::humanCurrency($entry['total'] - $nds).$this->curParams['text'];

		$replaces['total2'] = Library::humanCurrency($entry['total']).$this->curParams['text'];
		$replaces['ndsCount'] = Library::humanCurrency($nds).$this->curParams['text'];
		$replaces['printSum'] = Library::printMoney($entry['total'], $this->curParams['text']);

		$replaces['calculation'][] = array(
			'name' => $entry['entry_caption'],
			'price' => $entry['total'].$this->curParams['text'],
			'count' => $entry['term'],
+			'unit' => $unit,
			'total' => $entry['total'].$this->curParams['text']
		);
	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:bankovskijpe}', 'Bank');
	}
}

?>