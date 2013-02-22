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

class payPayPal extends paymentsObject{
	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['eml'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payPayPal',
						$this->settings['mode'] ? 'https://www.paypal.com/us/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/us/cgi-bin/webscr'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'cmd' => '_xclick',
					'notify_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentNotify&payId='.$this->payParams['name'],
					'return' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentSuccess&payId='.$this->payParams['name'],
					'cancel_return' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentFail&payId='.$this->payParams['name'],
					'business' => $this->settings['eml'],
					'item_name' => Library::cyr2translit($params['descript']),
					'item_number' => $params['id'],
					'upload' => '1',
					'amount' => $params['sum'],
					'currency_code' => $this->settings['pp_currency'],
				)
			)
		);

		return true;
	}

	public function __ava__payment($params){
		/*
			Возвращает параметры платежа
		*/

		$tData = $this->obj->getTransactionParams($this->obj->values['txn_id']);
		$data = $this->obj->Core->getGPCArr('p');
		$data['cmd'] = '_notify-validate';

		$http = new httpClient($this->settings['mode'] ? 'https://www.paypal.com/us/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/us/cgi-bin/webscr', 'POST');
		$http->setVars($data);
		$http->send();

		$result = $http->getResponseBody();
		if($this->obj->values['receiver_email'] != $this->settings['eml']) return $this->out(0, 101);
		elseif($data['status']) return $this->out(1, 21);
		elseif(!regExp::Match('VERIFIED', regExp::Lower($result))) return $this->out(0, 102, 'Ответ PayPal "'.$result.'"');

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['amount'],
				'vars' => $this->obj->Core->getGPCArr('p')
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
				'eml' => array(
					'type' => 'text',
					'text' => 'E-mail в PayPal',
					'warn' => 'Вы не указали e-mail',
				),
				'pp_currency' => array(
					'type' => 'select',
					'text' => 'Валюта внутри PayPal',
					'additional' => array(
						'USD' => 'U.S. Dollar',
						'EUR' => 'Euro',
						'AUD' => 'Australian Dollar',
						'BRL' => 'Brazilian Real',
						'CAD' => 'Canadian Dollar',
						'CZK' => 'Czech Koruna',
						'DKK' => 'Danish Krone',
						'HKD' => 'Hong Kong Dollar',
						'HUF' => 'Hungarian Forint',
						'ILS' => 'Israeli Sheqel',
						'JPY' => 'Japanese Yen',
						'MYR' => 'Malaysian Ringgit',
						'MXN' => 'Mexican Peso',
						'NOK' => 'Norwegian Krone',
						'NZD' => 'New Zealand Dollar',
						'PHP' => 'Philippine Peso',
						'PLN' => 'Polish Zloty',
						'GBP' => 'Pound Sterling',
						'SGD' => 'Singapore Dollar',
						'SEK' => 'Swedish Krona',
						'CHF' => 'Swiss Franc',
						'TWD' => 'Taiwan Dollar',
						'THB' => 'Thai Baht',
					),
				),
				'mode' => array(
					'type' => 'radio',
					'text' => 'Режим работы PayPal',
					'warn' => 'Вы не указали режим работы',
					'additional' => array(
						'0' => 'Тестовый',
						'1' => 'Рабочий',
					),
				),
			)
		);
	}

	public function __ava__checkNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		if(!$this->obj->check()) return false;
		return array('eml' => $this->obj->values['eml'], 'mode' => $this->obj->values['mode'], 'pp_currency' => $this->obj->values['pp_currency']);
	}


	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function out($status, $error = 0, $errorMsg = '', $params = false){		if($error && !$errorMsg){
			switch($error){
				case 101: $errorMsg = 'Неверный e-mail получателя'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		return array('id' => $this->obj->values['txn_id'], 'output' => '', 'logId' => $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg), 'params' => $params);	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['txn_id'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnaias2}', 'PayPal');
	}
}

?>