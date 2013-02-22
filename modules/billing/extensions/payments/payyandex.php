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

class payYandex extends paymentsObject{
	protected function __init(){		if(!empty($this->settings['secretKey'])) $this->settings['secretKey'] = Library::decrypt($this->settings['secretKey']);
	}

	public function __ava__paymentForm($params){		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['secretKey'])){			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');			return false;		}

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payYandex',
						$this->settings['mode'] ? 'http://money.yandex.ru/select-wallet.xml' : 'http://demomoney.yandex.ru/select-wallet.xml'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'ShopId' => $this->settings['shopId'],
					'Sum' => $params['sum'],
					'scid' => $this->settings['scid'],
					'CustomerNumber' => $params['id']
				)
			)
		);

		return true;
	}

	public function __ava__payment($params){		/*
			Возвращает параметры платежа
		*/

		$data = $this->obj->getTransactionParams($this->obj->values['customerNumber']);

		if(!$data) return $this->out(0, 12);
		elseif($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['md5'])) return $this->out(0, 10);		elseif($data['status']) return $this->out(1, 21);
		elseif($this->obj->values['action'] == 'Check') return $this->out(1, 0, '{Call:Lang:modules:billing:predvariteln}');
		elseif($this->obj->values['action'] == 'PaymentSuccess'){
			return $this->out(
				1, 0, '',
				array(
					'sum' => $this->obj->values['orderSumAmount'],
					'uniqId' => $this->obj->values['invoiceId'],
					'vars' => array(
						'requestDatetime' => $this->obj->values['requestDatetime'],
						'orderCreatedDatetime' => $this->obj->values['orderCreatedDatetime'],
						'paymentDatetime' => $this->obj->values['paymentDatetime'],
						'paymentPayerCode' => $this->obj->values['paymentPayerCode']
					)
				)
			);
		}
		else return $this->out(0, 30);
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
				'mode' => array(
					'type' => 'radio',
					'text' => '',
					'warn' => '{Call:Lang:modules:billing:vyneukazalir}',
					'additional' => array('0' => '{Call:Lang:modules:billing:testovyjrezh}', '1' => '{Call:Lang:modules:billing:rabochijrezh}')
				),
				'secretKey' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sekretnyjkli}',
					'value' => empty($params['values']['secretKey']) ? '' : Library::decrypt($params['values']['secretKey']),
					'warn' => '{Call:Lang:modules:billing:vyneukazalis2}',
				),
				'shopId' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:idmagazina}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii1}',
				),
				'scid' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:idvitriny}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalii5}',
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
			'shopId' => $this->obj->values['shopId'],
			'scid' => $this->obj->values['scid'],
			'secretKey' => Library::crypt($this->obj->values['secretKey']),
			'mode' => $this->obj->values['mode'],
		);
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getPayHash($v){		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($v['orderIsPaid'].';'.$v['orderSumAmount'].';'.$v['orderSumCurrencyPaycash'].';'.$v['orderSumBankPaycash'].';'.$v['shopId'].';'.$v['invoiceId'].';'.$v['customerNumber'].';'.$this->settings['secretKey']));	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){		if($error && !$errorMsg){
			$errorMsg = $this->obj->getPayLogErrorMsg($error);
		}

		if($status){			$yCode = 0;
			$yAnswer = $this->obj->values['action'];		}
		else{
			switch($error){				case 10:
					$yCode = 1;
					$yAnswer = 'IncorrectHash';
					break;
				case 12:
					$yCode = 100;
					$yAnswer = 'CustomerNumberNotFound';
					break;

				case 30:
					$yCode = 200;
					$yAnswer = 'IncorrectRequest';
					break;

				default:
					$yCode = 1000;
					$yAnswer = 'ErrorFound';
					break;
			}
		}

		$out = regExp::utf8('<'.'?xml version="1.0" encoding="windows-1251"?'.'>'.
			'<response performedDatetime="'.date("Y-m-d\TH:i:s").'">'.
			'<result code="'.$yCode.'" action="'.$yAnswer.'" shopId="'.$this->settings['shopId'].'" invoiceId="'.$this->obj->values['invoiceId'].'"/>'.
			'</response>');

		$logId = $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg);
		return array(
			'id' => $this->obj->values['customerNumber'],
			'output' => $out,
			'logId' => $logId,
			'params' => $params
		);	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['customerNumber'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:iandeksdengi}', 'Yandex');
	}
}

?>