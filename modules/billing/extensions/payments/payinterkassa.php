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

class payInterkassa extends paymentsObject{

	protected function __init(){
		if(!empty($this->settings['secretKey'])) $this->settings['secretKey'] = Library::decrypt($this->settings['secretKey']);
	}

	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['secretKey'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payIk',
						'http://www.interkassa.com/lib/payment.php'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'ik_shop_id' => $this->settings['shopId'],
					'ik_payment_amount' => $params['sum'],
					'ik_payment_id' => $params['id'],
					'ik_payment_desc' => $params['descript'],
					'ik_paysystem_alias' => $this->settings['alias'],
					'ik_sign_hash' => $this->getPayFormHash($params),
					'ik_success_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentSuccess&payId='.$this->payParams['name'],
					'ik_success_method' => 'LINK',
					'ik_fail_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentFail&payId='.$this->payParams['name'],
					'ik_fail_method' => 'LINK',
					'ik_status_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentNotify&payId='.$this->payParams['name'],
					'ik_status_method' => 'POST'
				)
			)
		);

		$this->obj->Core->setParam('charset', 'windows-1251');
		return true;
	}

	public function __ava__payment($params){
		/*
			Возвращает параметры платежа
		*/

		$data = $this->obj->getTransactionParams($this->obj->values['ik_payment_id']);
		if($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['ik_sign_hash'])) return $this->out(0, 10);
		elseif($this->obj->values['ik_payment_state'] != 'success') return $this->out(0, 2);
		elseif($data['status']) return $this->out(1, 21);

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['ik_payment_amount'],
				'uniqId' => $this->obj->values['ik_trans_id'],
				'currency' => $this->curParams['name'],
				'vars' => array(
					'ik_paysystem_alias' => $this->obj->values['ik_paysystem_alias'],
					'ik_payment_timestamp' => $this->obj->values['ik_payment_timestamp'],
					'ik_trans_id' => $this->obj->values['ik_trans_id'],
					'ik_currency_exch' => $this->obj->values['ik_currency_exch'],
					'ik_fees_payer' => $this->obj->values['ik_fees_payer']
				)
			)
		);
	}



	/********************************************************************************************************************************************************************

																		Добавление способа оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__setNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		$http = new httpClient('http://www.interkassa.com/lib/paysystems.currencies.export.php?format=xml');
		$xml = XML::parseXML($http->SendAndGetBody(), $attr);
		$alias = array('' => '{Call:Lang:modules:billing:liuboj}');

		foreach($xml['paysystems']['paysystem'] as $i => $e){
			if(isset($attr['paysystems']['paysystem'][$i]['@attr']['alias'])){
				$alias[$attr['paysystems']['paysystem'][$i]['@attr']['alias']] = $e.' ('.$attr['paysystems']['paysystem'][$i]['@attr']['currencyName'].')';
			}
		}

		asort($alias);
		$alias = Library::array_merge(array('' => '{Call:Lang:modules:billing:liuboj}'), $alias);

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'shopId' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:idmagazina}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii1}',
				),
				'secretKey' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sekretnyjkod}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalis1}',
					'value' => empty($params['values']['secretKey']) ? '' : Library::decrypt($params['values']['secretKey'])
				),
				'alias' => array(
					'type' => 'select',
					'text' => '{Call:Lang:modules:billing:sposoboplaty}',
					'additional' => $alias
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
			'shopId' => $this->obj->values['shopId'],
			'alias' => $this->obj->values['alias'],
			'secretKey' => Library::crypt($this->obj->values['secretKey'])
		);
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getPayHash($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($v['ik_shop_id'].':'.$v['ik_payment_amount'].':'.$v['ik_payment_id'].':'.$v['ik_paysystem_alias'].'::'.$v['ik_payment_state'].':'.$v['ik_trans_id'].':'.$v['ik_currency_exch'].':'.$v['ik_fees_payer'].':'.$this->settings['secretKey']));
	}

	private function getPayFormHash($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($this->settings['shopId'].':'.$v['sum'].':'.$v['id'].':'.$this->settings['alias'].'::'.$this->settings['secretKey']));
	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 10: $errorMsg = 'Invalid secret key'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$out = $status ? 'OK' : 'Error '.$error.': '.$errorMsg;
		$logId = $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg);

		return array(
			'id' => $this->obj->values['ik_payment_id'],
			'output' => $out,
			'logId' => $logId,
			'params' => $params
		);
	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['ik_payment_id'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);
	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnyjsh}', 'Interkassa');
	}
}

?>