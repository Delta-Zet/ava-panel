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

class payRBC extends paymentsObject{
	protected function __init(){		if(!empty($this->settings['secretKey'])) $this->settings['secretKey'] = Library::decrypt($this->settings['secretKey']);
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
				'eshopId' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:idmagazina}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii4}'
				),
				'secretKey' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sekretnyjkli}',
					'warn' => 'Вы не указали секретный ключ',
					'value' => empty($params['values']['secretKey']) ? '' : Library::decrypt($params['values']['secretKey'])
				),
				'recipientCurrency' => array(
					'type' => 'select',
					'text' => '{Call:Lang:modules:billing:valiutamerch}',
					'additional' => array(
						'RUR' => 'Рубль',
						'UAH' => 'Гривна',
						'USD' => 'Доллар США',
						'EUR' => 'Евро',
						'EUR' => 'Английский фунт',
					)
				),
				'language' => array(
					'type' => 'select',
					'text' => 'Язык интерфейса по умолчанию',
					'additional' => array(
						'ru' => 'Русский',
						'en' => 'Английский',
					)
				)
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
			'eshopId' => $this->obj->values['eshopId'],
			'secretKey' => Library::crypt($this->obj->values['secretKey']),
			'recipientCurrency' => $this->obj->values['recipientCurrency'],
			'language' => $this->obj->values['language'],
		);
	}


	/********************************************************************************************************************************************************************

																			Форма оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['eshopId'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$tData = $this->obj->getTransactionParams($params['id']);

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payRbc',
						'https://rbkmoney.ru/acceptpurchase.aspx'
					),
					array(
						'pay',
						array(
							'language' => array(
								'type' => 'select',
								'text' => 'Язык интерфейса',
								'additional' => array(
									'ru' => 'Русский',
									'en' => 'English',
								),
								'value' => $this->settings['language']
							)
						)
					)
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'eshopId' => $this->settings['eshopId'],
					'orderId' => $params['id'],
					'version' => 2,
					'serviceName' => $params['descript'],
					'recipientAmount' => $params['sum'],
					'recipientCurrency' => $this->settings['recipientCurrency'],
					'user_email' => $this->obj->getClientEml($tData['client_id']),
					'successUrl' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentSuccess&payId='.$this->payParams['name'],
					'failUrl' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentFail&payId='.$this->payParams['name']
				)
			)
		);

		return true;
	}

	public function __ava__payment($params){		/*
			Возвращает параметры платежа
		*/

		$data = $this->obj->getTransactionParams($this->obj->values['orderId']);

		if($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['hash'])) return $this->out(0, 10);		elseif($data['status']) return $this->out(1, 21);
		elseif($this->obj->values['recipientCurrency'] != $this->settings['recipientCurrency']) return $this->out(0, 101);

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['recipientAmount'],
				'uniqId' => $this->obj->values['paymentId'],
				'vars' => array(
					'eshopAccount' => $this->obj->values['eshopAccount'],
					'recipientCurrency' => $this->obj->values['recipientCurrency'],
					'paymentStatus' => $this->obj->values['paymentStatus'],
					'userName' => $this->obj->values['userName'],
					'userEmail' => $this->obj->values['userEmail'],
					'paymentData' => $this->obj->values['paymentData']
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getPayHash($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($v['eshopId'].'::'.$v['orderId'].'::'.$v['serviceName'].'::'.$v['eshopAccount'].'::'.$v['recipientAmount'].'::'.$v['recipientCurrency'].'::'.$v['paymentStatus'].'::'.$v['userName'].'::'.$v['userEmail'].'::'.$v['paymentData'].'::'.$this->settings['secretKey']));
	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 10: $errorMsg = '{Call:Lang:modules:billing:nepravilnyjs}'; break;
				case 101: $errorMsg = 'Валюта оплаты и валюта настроек не соответствуют'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$out = $status ? 'OK' : 'Error '.$error.': '.$errorMsg;
		$logId = $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg);
		return array('id' => $this->obj->values['orderId'], 'output' => $out, 'logId' => $logId, 'params' => $params);
	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['orderId'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnaias3}', 'Rbc');
	}
}

?>