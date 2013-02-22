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

class payMoneyBookers extends paymentsObject{
	protected function __init(){
		if(!empty($this->settings['pwd'])) $this->settings['pwd'] = Library::decrypt($this->settings['pwd']);
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
					'text' => 'E-mail',
					'warn' => 'Вы не указали e-mail',
				),
				'pwd' => array(
					'type' => 'pwd',
					'text' => 'Секретное слово',
					'warn' => 'Вы не указали секретное слово',
					'value' => empty($params['values']['pwd']) ? '' : Library::decrypt($params['values']['pwd']),
				),
				'language' => array(
					'type' => 'select',
					'text' => 'Язык',
					'additional' => array(
						'RU' => 'Русский',
						'EN' => 'Английский',
						'DE' => 'Немецкий',
						'ES' => 'Испанский',
						'FR' => 'Французский',
						'IT' => 'Итальянский',
						'PL' => 'Польский',
						'GR' => 'Греческий',
						'RO' => 'Румынский',
						'TR' => 'Турецкий',
						'CN' => 'Китайский',
						'CZ' => 'Чешский',
						'NL' => 'Нидерландский',
						'DA' => 'Датский',
						'SV' => 'Шведский',
						'FI' => 'Финский'
					)
				),
				'mb_currency' => array(
					'type' => 'select',
					'text' => 'Валюта внутри Moneybookers',
					'additional' => array(
						'EUR' => 'Euro',
						'USD' => 'U.S. Dollar',
						'GBP' => 'British Pound',
						'CZK' => 'Czech Koruna',
						'TWD' => 'Taiwan Dollar',
						'THB' => 'Thailand Baht',
						'HKD' => 'Hong Kong Dollar',
						'HUF' => 'Hungarian Forint',
						'SGD' => 'Singapore Dollar',
						'SKK' => 'Slovakian Koruna',
						'JPY' => 'Japanese Yen',
						'EEK' => 'Estonian Kroon',
						'CAD' => 'Canadian Dollar',
						'BGN' => 'Bulgarian Leva',
						'AUD' => 'Australian Dollar',
						'PLN' => 'Polish Zloty',
						'CHF' => 'Swiss Franc',
						'ISK' => 'Iceland Krona',
						'DKK' => 'Danish Krone',
						'INR' => 'Indian Rupee',
						'SEK' => 'Swedish Krona',
						'LVL' => 'Latvian Lat',
						'NOK' => 'Norwegian Krone',
						'KRW' => 'South-Korean Won',
						'ILS' => 'Israeli Shekel',
						'ZAR' => 'South-African Rand',
						'MYR' => 'Malaysian Ringgit',
						'RON' => 'Romanian Leu',
						'NZD' => 'New Zealand Dollar',
						'HRK' => 'Croatian Kuna',
						'TRY' => 'Turkish Lira',
						'LTL' => 'Lithuanian Litas',
						'AED' => 'Utd. Arab Emir. Dirham',
						'JOD' => 'Jordanian Dinar',
						'MAD' => 'Moroccan Dirham',
						'OMR' => 'Omani Rial',
						'QAR' => 'Qatari Rial',
						'RSD' => 'Serbian dinar',
						'SAR' => 'Saudi Riyal',
						'TND' => 'Tunisian Dinar'
					)
				)
			)
		);
	}

	public function __ava__checkNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		if(!$this->obj->check()) return false;
		return array(
			'eml' => $this->obj->values['eml'],
			'pwd' => Library::crypt($this->obj->values['pwd']),
			'language' => $this->obj->values['language'],
			'mb_currency' => $this->obj->values['mb_currency'],
		);
	}


	/********************************************************************************************************************************************************************

																			Оплата

	*********************************************************************************************************************************************************************/

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
						'payMB',
						'https://www.moneybookers.com/app/payment.pl'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'pay_to_email' => $this->settings['eml'],
					'recipient_description' => $this->obj->Core->Site->params['name'],
					'language' => $this->settings['language'],
					'detail1_description' => 'Payment number: ',
					'detail1_text' => $params['id'],
					'transaction_id' => $params['id'],
					'return_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentSuccess&payId='.$this->payParams['name'],
					'return_url_text' => 'Back to merchant',
					'cancel_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentFail&payId='.$this->payParams['name'],
					'status_url' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentNotify&payId='.$this->payParams['name'],
					'amount' => $params['sum'],
					'currency' => $this->settings['mb_currency'],
				)
			)
		);
	}

	public function __ava__payment($params){
		/*
			Возвращает параметры платежа
		*/

		$data = $this->obj->getTransactionParams($this->obj->values['transaction_id']);

		if($this->obj->values['status'] != 2) return $this->out(0, 102);
		elseif($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['md5sig'])) return $this->out(0, 10);
		elseif($this->obj->values['pay_to_email'] != $this->settings['eml']) return $this->out(0, 101);

		return $this->out(1, 0, '', array('sum' => $this->obj->values['mb_amount'], 'uniqId' => $this->obj->values['mb_transaction_id'], 'vars' => $this->obj->values));
	}


	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getSign($v){
		/*
			Формирует signature
		*/

		return regExp::lower(md5($v['merchant_id'].$v['transaction_id'].$this->settings['pwd'].$v['mb_amount'].$v['mb_currency'].'2'));
	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 10: $errorMsg = '{Call:Lang:modules:billing:nepravilnyjs}'; break;
				case 101: $errorMsg = 'E-mail получателя платежа не соответствует'; break;
				case 102: $errorMsg = 'Transaction status error'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		return array('id' => $this->obj->values['transaction_id'], 'output' => '', 'logId' => $this->save('', $status, $params ? 1 : 0, $error, $errorMsg), 'params' => $params);
	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['transaction_id'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);
	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnaias}', 'MoneyBookers');
	}
}

?>