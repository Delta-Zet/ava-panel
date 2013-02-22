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

class payOSMP extends paymentsObject{
	protected function __init(){
		if(!empty($this->settings['pwd'])) $this->settings['pwd'] = Library::decrypt($this->settings['pwd']);
	}

	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['from'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payQIWI',
						'https://w.qiwi.ru/setInetBill_utf.do'
					),
					array(
						'pay',
						array(
							'to' => array(
								'type' => 'text',
								'text' => 'Ваш ID в системе QIWI (номер мобильного телефона)',
								'comment' => 'Указывайте его без ведущей восьмерки, например 9161234567',
								'warn' => 'Вы не указали номер телефона',
								'warn_function' => 'regExp::digit',
							)
						)
					)
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'from' => $this->settings['from'],
					'summ' => $params['sum'],
					'com' => $params['descript'],
					'lifetime' => $this->settings['lifetime'],
					'check_agt' => $this->settings['check_agt'],
					'txn_id' => $params['id'],
				)
			)
		);

		return true;
	}

	public function __ava__payment($params){
		/*
			Возвращает параметры платежа
		*/

		$data = XML::parseXML(file_get_contents('php://input'));
		$data = $data['soap:Envelope']['soap:Body']['ns2:updateBill'];
		$tData = $this->obj->getTransactionParams($data['txn']);

		if($this->getPayHash($data) != regExp::lower($data['password'])) return $this->out($data, 0, 150);
		elseif($data['status'] != 60) return $this->out($data, 0, $data['status']);
		elseif($tData['status']) return $this->out($data, 1, 21);

		return $this->out($data, 1, 0, '', array('sum' => $tData['sum'], 'uniqId' => $data['txn'], 'vars' => $data));
	}


	/********************************************************************************************************************************************************************

																		Добавление способа оплаты

	*********************************************************************************************************************************************************************/

	public function setNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'from' => array(
					'type' => 'text',
					'text' => 'Ваш ID в системе QIWI',
					'warn' => 'Вы не ID',
					'warn_function' => 'regExp::digit',
				),
				'pwd' => array(
					'type' => 'pwd',
					'text' => 'Пароль магазина',
					'comment' => 'Если не указать пароль, прием платежей будет происходить, однако автоматически проводиться они не будут',
					'value' => empty($params['values']['pwd']) ? '' : Library::decrypt($params['values']['pwd'])
				),
				'lifetime' => array(
					'type' => 'text',
					'text' => 'Время жизни счета, часов',
					'comment' => 'Максимально допустимое время - 45 суток (1080 часов)'
				),
				'check_agt' => array(
					'type' => 'radio',
					'text' => 'Если пользователь не зарегистрирован в QIWI',
					'warn' => 'Вы не указали режим работы',
					'additional' => array(
						'0' => 'Отказывать ему в выставлении счета',
						'1' => 'Регистрировать его в QIWI',
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
		return array(
			'from' => $this->obj->values['from'],
			'lifetime' => $this->obj->values['lifetime'],
			'check_agt' => $this->obj->values['check_agt'],
			'pwd' => Library::crypt($this->obj->values['pwd']),
		);
	}


	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getPayHash($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($this->settings['pwd']));
	}

	private function out($data, $status, $error = 0, $errorMsg = '', $params = false){		if($error){
			switch($error){
				case 150: $errorMsg = 'Счет отклонен'; break;
				case 160: $errorMsg = 'Счет не прошел'; break;
				case 161: $errorMsg = 'Истекло время жизненного цикла счета'; break;
			}
		}

		$this->obj->Core->setHeader('Content-type', 'text/xml; charset=UTF-8');
		$out = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://client.ishop.mw.ru/">'.
			'<SOAP-ENV:Body><ns1:updateBillResponse><updateBillResult>'.$error.'</updateBillResult></ns1:updateBillResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>';

		return array(
			'id' => $data['txn'],
			'output' => $out,
			'logId' => $this->save($data['txn'], $out, $status, $params ? 1 : 0, $error, $errorMsg),
			'params' => $params
		);	}

	private function save($id, $output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($id, $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);	}

	public static function getInstallParams(){
		return array('QIWI-кошелек', 'OSMP');
	}
}

?>