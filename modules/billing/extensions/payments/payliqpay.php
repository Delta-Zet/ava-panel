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

class payLiqpay extends paymentsObject{

	protected function __init(){
		if(!empty($this->settings['pwd'])) $this->settings['pwd'] = Library::decrypt($this->settings['pwd']);
	}

	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['merchant_id'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$xml = XML::getXML(
			array(
				'request' => array(
					'version' => '1.2',
					'merchant_id' => $this->settings['merchant_id'],
					'result_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentSuccess&payId='.$this->payParams['name'],
					'server_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentNotify&payId='.$this->payParams['name'],
					'order_id' => $params['id'],
					'amount' => $params['sum'],
					'currency' => $this->settings['mrch_currency'],
					'description' => Library::cyr2translit($params['descript']),
					'default_phone' => '',
					'pay_way' => implode(',', array_keys($this->settings['pay_way'])),
					'goods_id' => $params['id'],
					'exp_time' => $this->settings['exp_time'],
				)
			)
		);

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payLiq',
						'https://www.liqpay.com/?do=clickNbuy'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'operation_xml' => base64_encode($xml),
					'signature' => base64_encode(sha1($this->settings['pwd'].$xml.$this->settings['pwd'], 1)),
				)
			)
		);

		return true;
	}

	public function __ava__payment($params){
		/*
			Возвращает параметры платежа
		*/

		$xml = $this->obj->values['operation_xml'];
		$orderParams = XML::ParseXML($xml);
		$data = $this->obj->getTransactionParams($orderParams['response']['order_id']);

		if(base64_encode(sha1($this->settings['pwd'].$xml.$this->settings['pwd'], 1)) != $this->obj->values['signature']) return $this->out(0, 10);
		elseif($data['status']) return $this->out(1, 21);
		elseif($orderParams['response']['status'] != 'success') return $this->out(0, '100'.$orderParams['response']['code']);

		return $this->out(
			1, 0, '',
			array(
				'sum' => $orderParams['response']['amount'],
				'uniqId' => $orderParams['response']['transaction_id'],
				'currency' => $this->curParams['name'],
				'vars' => $orderParams['response']
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

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'merchant_id' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:idmerchantam}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii2}',
				),
				'pwd' => array(
					'type' => 'text',
					'text' => 'Подпись для операций с мерчантом',
					'comment' => 'Указывайте подпись для остальных операций (не для send money)',
					'value' => empty($params['values']['pwd']) ? '' : Library::decrypt($params['values']['pwd'])
				),
				'pay_way' => array(
					'type' => 'checkbox_array',
					'text' => 'Доступные способы оплаты',
					'warn' => 'Вы не указали ни одного доступного способа оплаты',
					'additional' => array(
						'card' => 'С карты',
						'liqpay' => 'С телефона',
						'delayed' => 'Наличными',
					)
				),
				'mrch_currency' => array(
					'type' => 'select',
					'text' => '{Call:Lang:modules:billing:valiutamerch}',
					'warn' => '{Call:Lang:modules:billing:vyneukazaliv}',
					'additional' => array(
						'RUR' => 'RUR',
						'UAH' => 'UAH',
						'USD' => 'USD',
						'EUR' => 'EUR',
					)
				),
				'exp_time' => array(
					'type' => 'text',
					'text' => 'Время в течение которого можно оплатить товар в автоматах самообслуживания, в часах',
					'warn' => 'Вы не указали время в течение которого можно оплатить товар в автоматах самообслуживания',
					'value' => empty($params['values']['exp_time']) ? '36' : $params['values']['exp_time']
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
			'merchant_id' => $this->obj->values['merchant_id'],
			'pwd' => Library::crypt($this->obj->values['pwd']),
			'mrch_currency' => $this->obj->values['mrch_currency'],
			'pay_way' => $this->obj->values['pay_way'],
			'exp_time' => $this->obj->values['exp_time'],
		);
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function out($status, $error = 0, $errorMsg = '', $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 10: $errorMsg = 'Invalid signature'; break;
				case 101: $errorMsg = 'Wrong merchant ID'; break;
				default: $errorMsg = '{Call:Lang:modules:billing:vnutrenniaia:'.Library::serialize(array($error)).'}';
			}
		}

		$out = $status ? 'Success' : 'Error '.$error.': '.$errorMsg;
		$logId = $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg);

		return array(
			'id' => $this->obj->values['LMI_PAYMENT_NO'],
			'output' => $out,
			'logId' => $logId,
			'params' => $params
		);
	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['order_id'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);
	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnyjsh1}', 'Liqpay');
	}
}

?>