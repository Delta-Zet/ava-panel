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

class payWebMoney extends paymentsObject{

	protected function __init(){
		if(!empty($this->settings['secretKey'])) $this->settings['secretKey'] = Library::decrypt($this->settings['secretKey']);
	}

	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['purse'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}
		elseif(empty($this->settings['secretKey'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:oplata:'.Library::serialize(array($this->payParams['text'])).'}');
			if(!$this->payParams['comment']){
				$this->payParams['comment'] = '{Call:Lang:modules:billing:avtomatiches:'.Library::serialize(array($this->payParams['vars']['purse'])).'}';
			}
			$this->obj->setContent($this->payParams['comment']);
			return true;
		}

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payWm',
						'https://merchant.webmoney.ru/lmi/payment.asp'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'LMI_PAYEE_PURSE' => $this->settings['purse'],
					'LMI_PAYMENT_DESC' => $params['descript'],
					'LMI_PAYMENT_NO' => $params['id'],
					'LMI_SIM_MODE' => '0',
					'LMI_PAYMENT_AMOUNT' => $params['sum'],
					'LMI_RESULT_URL' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentNotify&payId='.$this->payParams['name'],
					'LMI_SUCCESS_URL' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentSuccess&payId='.$this->payParams['name'],
					'LMI_FAIL_URL' => _D.'index.php?mod='.$this->obj->getMod().'&func=paymentFail&payId='.$this->payParams['name'],
					'LMI_SUCCESS_METHOD' => '2',
					'LMI_FAIL_METHOD' => '2',
					'LMI_PAYMENT_CREDITDAYS' => $this->settings['credit']
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

		$data = $this->obj->getTransactionParams($this->obj->values['LMI_PAYMENT_NO']);

		if($data['status']) return $this->out(1, 21);
		elseif($this->wmIdIsBanned($this->obj->values['LMI_PAYER_WM'])) return $this->out(0, 4);
		elseif($this->obj->values['LMI_PAYEE_PURSE'] != $this->settings['purse']) return $this->out(0, 101);
		elseif(regExp::substr($this->settings['purse'], 0, 1) != regExp::substr($this->obj->values['LMI_PAYER_PURSE'], 0, 1)) return $this->out(0, 101);
		elseif(!empty($this->obj->values['LMI_PREREQUEST'])) return $this->out(1, 0, '{Call:Lang:modules:billing:predvariteln}');
		elseif($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['LMI_HASH'])) return $this->out(0, 10);

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['LMI_PAYMENT_AMOUNT'],
				'uniqId' => $this->obj->values['LMI_SYS_TRANS_NO'],
				'currency' => $this->curParams['name'],
				'vars' => array(
					'INVS_NO' => $this->obj->values['LMI_SYS_INVS_NO'],
					'TRANS_DATE' => $this->obj->values['LMI_SYS_TRANS_DATE'],
					'PAYER_PURSE' => $this->obj->values['LMI_PAYER_PURSE'],
					'PAYER_WM' => $this->obj->values['LMI_PAYER_WM'],
					'MODE' => $this->obj->values['LMI_MODE'],
					'PAYMER_NUMBER' => empty($this->obj->values['LMI_PAYMER_NUMBER']) ? '' : $this->obj->values['LMI_PAYMER_NUMBER'],
					'PAYMER_PAYMER_EMAIL' => empty($this->obj->values['LMI_PAYMER_EMAIL']) ? '' : $this->obj->values['LMI_PAYMER_EMAIL'],
					'PAYMER_EURONOTE_NUMBER' => empty($this->obj->values['LMI_EURONOTE_NUMBER']) ? '' : $this->obj->values['LMI_EURONOTE_NUMBER'],
					'PAYMER_EURONOTE_EMAIL' => empty($this->obj->values['LMI_EURONOTE_EMAIL']) ? '' : $this->obj->values['LMI_EURONOTE_EMAIL'],
					'PAYMER_TELEPAT_PHONENUMBER' => empty($this->obj->values['LMI_TELEPAT_PHONENUMBER']) ? '' : $this->obj->values['LMI_TELEPAT_PHONENUMBER'],
					'PAYMER_TELEPAT_ORDERID' => empty($this->obj->values['LMI_TELEPAT_ORDERID']) ? '' : $this->obj->values['LMI_TELEPAT_ORDERID'],
					'PAYMER_ATM_WMTRANSID' => empty($this->obj->values['LMI_ATM_WMTRANSID']) ? '' : $this->obj->values['LMI_ATM_WMTRANSID']
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
					'text' => '{Call:Lang:modules:billing:nomerkoshelk}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalin5}',
					'warn_pattern' => '/^[A-Za-z]\d{12}$/iUs'
				),
				'secretKey' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sekretnyjkod}',
					'value' => empty($params['values']['secretKey']) ? '' : Library::decrypt($params['values']['secretKey'])
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
			'purse' => $this->obj->values['purse'],
			'secretKey' => Library::crypt($this->obj->values['secretKey']),
			'credit' => 0
		);
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getPayHash($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($v['LMI_PAYEE_PURSE'].$v['LMI_PAYMENT_AMOUNT'].$v['LMI_PAYMENT_NO'].$v['LMI_MODE'].$v['LMI_SYS_INVS_NO'].$v['LMI_SYS_TRANS_NO'].$v['LMI_SYS_TRANS_DATE'].$this->settings['secretKey'].$v['LMI_PAYER_PURSE'].$v['LMI_PAYER_WM']));
	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 4: $errorMsg = 'Access denied by your WMID'; break;
				case 10: $errorMsg = 'Invalid secret key'; break;
				case 101: $errorMsg = 'Wrong purse'; break;
				case 102: $errorMsg = 'Payer purse and settings purse have different types'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$out = $status ? 'Yes' : 'Error '.$error.': '.$errorMsg;
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

		return $this->obj->savePayLog($this->obj->values['LMI_PAYMENT_NO'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);
	}

	private function wmIdIsBanned($wmid){
		return false;
	}

	public static function getInstallParams(){
		return array('WebMoney', 'WebMoney');
	}
}

?>