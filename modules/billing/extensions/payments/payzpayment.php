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

class payZPayment extends paymentsObject{
	protected function __init(){		if(!empty($this->settings['secretKey'])) $this->settings['secretKey'] = Library::decrypt($this->settings['secretKey']);
		if(!empty($this->settings['pwd'])) $this->settings['pwd'] = Library::decrypt($this->settings['pwd']);
	}

	public function __ava__paymentForm($params){		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['purse'])){			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');			return false;		}

		$tData = $this->obj->getTransactionParams($params['id']);
		$clientData = $this->obj->getUserByClientId($tData['client_id']);
		$params['sum'] = Library::bankCurrency($params['sum']);

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payZP',
						'https://z-payment.ru/merchant.php'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'LMI_PAYEE_PURSE' => $this->settings['purse'],
					'LMI_PAYMENT_AMOUNT' => $params['sum'],
					'LMI_PAYMENT_DESC' => $params['descript'],
					'LMI_PAYMENT_NO' => $params['id'],
					'ZP_SIGN' => $this->getSign($params),
					'CLIENT_MAIL' => $clientData['eml']
				)
			)
		);

		$this->obj->Core->setParam('charset', 'windows-1251');
		return true;
	}

	public function __ava__payment($params){		/*
			Возвращает параметры платежа
		*/

		$data = $this->obj->getTransactionParams($this->obj->values['LMI_PAYMENT_NO']);
		if(!empty($this->obj->values['LMI_PREREQUEST'])) return $this->out(1, 0, '{Call:Lang:modules:billing:predvariteln}');
		elseif($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['LMI_HASH'])) return $this->out(0, 10);		elseif($data['status']) return $this->out(1, 21);

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['LMI_PAYMENT_AMOUNT'],
				'uniqId' => $this->obj->values['LMI_SYS_TRANS_NO'],
				'vars' => array(
					'INVS_NO' => $this->obj->values['LMI_SYS_INVS_NO'],
					'TRANS_DATE' => $this->obj->values['LMI_SYS_TRANS_DATE'],
					'PAYER_PURSE' => $this->obj->values['LMI_PAYER_PURSE'],
					'PAYER_WM' => $this->obj->values['LMI_PAYER_WM']
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

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'purse' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:identifikato}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii3}',
				),
				'secretKey' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sekretnyjkli1}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalis1}',
					'value' => empty($params['values']['secretKey']) ? '' : Library::decrypt($params['values']['secretKey'])
				),
				'pwd' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:parolinitsia}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalip}',
					'value' => empty($params['values']['pwd']) ? '' : Library::decrypt($params['values']['pwd'])
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
			'purse' => $this->obj->values['purse'],
			'secretKey' => Library::crypt($this->obj->values['secretKey']),
			'pwd' => Library::crypt($this->obj->values['pwd']),
		);
	}


	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getSign($v){		/*
			Формирует signature
		*/

		return md5($this->settings['purse'].$v['id'].$v['sum'].$this->settings['pwd']);	}

	private function getPayHash($v){		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($v['LMI_PAYEE_PURSE'].$v['LMI_PAYMENT_AMOUNT'].$v['LMI_PAYMENT_NO'].'0'.$v['LMI_SYS_INVS_NO'].$v['LMI_SYS_TRANS_NO'].$v['LMI_SYS_TRANS_DATE'].$this->settings['secretKey'].$v['LMI_PAYER_PURSE'].$v['LMI_PAYER_WM']));	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){		if($error && !$errorMsg){
			switch($error){
				case 10: $errorMsg = '{Call:Lang:modules:billing:nepravilnyjs}'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$out = $status ? 'YES' : 'Error '.$error.': '.$errorMsg;
		$logId = $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg);

		return array(
			'id' => $this->obj->values['LMI_PAYMENT_NO'],
			'output' => $out,
			'logId' => $logId,
			'params' => $params
		);	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['LMI_PAYMENT_NO'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnaias4}', 'ZPayment');
	}
}

?>