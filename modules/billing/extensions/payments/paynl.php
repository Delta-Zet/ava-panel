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

class payNL extends paymentsObject{

	protected function __init(){
		if(!empty($this->settings['pwd'])) $this->settings['pwd'] = Library::decrypt($this->settings['pwd']);
		if(!empty($this->settings['ip'])) $this->settings['ip'] = regExp::split("|\s+|", trim($this->settings['ip']), true);
	}


	/********************************************************************************************************************************************************************

																				Платеж

	*********************************************************************************************************************************************************************/

	public function __ava__paymentForm($params){
		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['url'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');
			return false;
		}

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payNL',
						$this->settings['url']
					),
					array(
						'pay',
						array(
							'phone' => array(
								'text' => 'Номер мобильного телефона без восьмерки',
								'type' => 'text'
							)
						)
					)
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array('number' => $params['id'], 'amount' => $params['sum'])
			)
		);

		return true;
	}

	public function __ava__payment($params){
		/*
			Возвращает параметры платежа
		*/

		if($this->settings['login'] != $_SERVER['PHP_AUTH_USER'] || $this->settings['pwd'] != $_SERVER['PHP_AUTH_PW']){
			header('WWW-Authenticate: authentication failed');
			header('HTTP/1.0 401 Unauthorized');
			exit;
		}
		elseif(!$_SERVER['REMOTE_ADDR'] || !in_array($_SERVER['REMOTE_ADDR'], $this->settings['ip'])) die('Unexpected IP address');

		if(!regExp::Match("|^\d+$|", $this->obj->values['account'], true)) return $this->out(0, 4);
		elseif(!$data = $this->obj->getTransactionParams($this->obj->values['account'])) return $this->out(0, 5);
		elseif($data['status']) return $this->out(1, 21);
		elseif($this->obj->values['command'] != 'pay') return $this->out(1, 0, 'Предварительный запрос');

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['sum'],
				'uniqId' => $this->obj->values['txn_id'],
				'currency' => $this->curParams['name'],
				'vars' => array(
					'txn_date' => $this->obj->values['txn_date'],
					'account' => $this->obj->values['account'],
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
				'login' => array(
					'type' => 'text',
					'text' => 'Логин аутентификации',
					'warn' => 'Вы не указали логин',
				),
				'pwd' => array(
					'type' => 'pwd',
					'text' => 'Пароль аутентификации',
					'warn' => 'Вы не указали пароль',
					'value' => empty($params['values']['pwd']) ? '' : Library::decrypt($params['values']['pwd'])
				),
				'ip' => array(
					'type' => 'textarea',
					'text' => 'Список IP с которых разрешен запрос',
					'warn' => 'Вы не указали ни одного IP',
					'comment' => 'Каждый с новой строки',
				),
				'url' => array(
					'type' => 'text',
					'text' => 'URL web-формы для подключения магазина',
					'warn' => 'Вы не указали URL для подключения',
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
			'ip' => $this->obj->values['ip'],
			'url' => $this->obj->values['url'],
			'login' => $this->obj->values['login'],
			'pwd' => Library::crypt($this->obj->values['pwd']),
		);
	}


	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function out($status, $error = 0, $errorMsg = '', $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 4: $errorMsg = 'Неверный формат идентификатора абонента'; break;
				case 5: $errorMsg = 'Идентификатор абонента не найден (Ошиблись номером)'; break;
				case 7: $errorMsg = 'Прием платежа запрещен Партнером'; break;
				case 8: $errorMsg = 'Прием платежа запрещен по техническим причинам'; break;
				case 79: $errorMsg = 'Счет абонента не активен'; break;
				case 241: $errorMsg = 'Сумма слишком мала'; break;
				case 242: $errorMsg = 'Сумма слишком велика'; break;
				case 243: $errorMsg = 'Невозможно проверить состояние счета'; break;
				case 300: $errorMsg = 'Другая ошибка'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$out = XML::getXML(
			array(
				'response' => array(
					'neoline_txn_id' => $this->obj->values['txn_id'],
					'prv_txn' => $this->obj->values['account'],
					'sum' => $this->obj->values['sum'],
					'result' => (!$error || $error == 21) ? 0 : $error,
					'comment' => $errorMsg,
				)
			)
		);

		return array('id' => $this->obj->values['account'], 'output' => $out, 'logId' => $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg), 'params' => $params);
	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['order_id'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);
	}

	public static function getInstallParams(){
		return array('NeoLine', 'nl');
	}
}

?>