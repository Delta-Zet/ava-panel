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


$GLOBALS['Core']->loadExtension('billing', 'smsObject');

class smsa1 extends smsObject{
	protected function __init(){
		if(!empty($this->settings['secretKey'])) $this->settings['secretKey'] = Library::decrypt($this->settings['secretKey']);
	}

	public function __ava__setNewSmsForm($params){
		/*
			Форма для нового способа оплаты SMS
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'prefix1' => array(
					'type' => 'text',
					'text' => 'Первый префикс',
					'warn' => 'Вы не указали первый префикс'
				),
				'prefix2' => array(
					'type' => 'text',
					'text' => 'Второй префикс',
					'warn' => 'Вы не указали второй префикс'
				),
				'secretKey' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:sekretnyjkod}',
					'value' => empty($params['values']['secretKey']) ? '' : Library::decrypt($params['values']['secretKey'])
				),
				'charset' => array(
					'type' => 'select',
					'text' => 'Кодировка ответа',
					'additional' => array(
						'UTF-8' => 'UTF-8',
						'WINDOWS-1251' => 'Windows-1251'
					)
				),
				'ranStatus' => array(
					'type' => 'select',
					'text' => 'Принимать платежи с',
					'additional' => array(
						'' => 'Любых номеров',
						'middle' => 'Средних и надежных',
						'safe' => 'Только надежных'
					)
				),
				'currencyCorr' => array(
					'type' => 'select',
					'text' => 'Валюта соответствующая рублю в ваших настройках',
					'comment' => 'Сведения о платеже приходят в рублях. Вам необходимо указать какая валюта в ваших настройках соответствует рублю.',
					'additional' => $this->obj->getCurrency()
				)
			)
		);

		return true;
	}

	public function __ava__checkNewSmsForm($params){
		/*
			Проверка нового способа оплаты SMS
		*/

		if(!$this->obj->check()) return false;
		return array(
			'prefix1' => $this->obj->values['prefix1'],
			'prefix2' => $this->obj->values['prefix2'],
			'secretKey' => Library::crypt($this->obj->values['secretKey']),
			'charset' => $this->obj->values['charset'],
			'ranStatus' => $this->obj->values['ranStatus'],
			'currencyCorr' => $this->obj->values['currencyCorr'],
		);
	}

	public function __ava__acceptSms($params){
		/*
			Возвращает параметры платежа
		*/

		if(empty($this->obj->values['msg']) || !($msgText = $this->getMsg($this->obj->values['msg'], $prefix))) return $this->out(0, 30);
		if($prefix != $this->settings['prefix1'].$this->settings['prefix2']) return $this->out(0, 30);
		$data = $this->obj->getTransactionParams($msgText);

		if(!$data) return $this->out(0, 20);
		elseif($data['status']) return $this->out(1, 21);
		elseif(($this->settings['ranStatus'] == 'middle' && $this->obj->values['ran'] < 5) || ($this->settings['ranStatus'] == 'safe' && $this->obj->values['ran'] < 8)) return $this->out(0, 4);
		elseif((md5($this->settings['secretKey']) != $this->obj->values['skey']) || ($this->getPayHash($this->obj->values) != regExp::lower($this->obj->values['sign']))) return $this->out(0, 10);

		if(!$numParams = $this->obj->getSmsNumberParamsByNum($this->payId, $this->obj->values['num'])) return out(0, 101);

		return $this->out(
			1, 0, '', $msgText,
			array(
				'sum' => $numParams['sum'] ? $numParams['sum'] : $this->obj->values['cost_rur'],
				'number' => $this->obj->values['num'],
				'uniqId' => $this->obj->values['smsid'],
				'currency' => $numParams['currency'],
				'vars' => array(
					'date' => $this->obj->values['date'],
					'country_id' => $this->obj->values['country_id'],
					'msg' => $this->obj->values['msg'],
					'msg_trans' => $this->obj->values['msg_trans'],
					'operator_id' => $this->obj->values['operator_id'],
					'user_id' => $this->obj->values['user_id'],
					'cost' => $this->obj->values['cost'],
					'test' => $this->obj->values['test'],
					'num' => $this->obj->values['num'],
					'try' => $this->obj->values['try'],
					'ran' => $this->obj->values['ran']
				)
			)
		);
	}

	public function __ava__getSmsComment($params){
		/*
			Возвращает текст-комментарий для уведомления пользователя о необходимости отправить SMS
		*/

		if(empty($params['number']['comment'])){
			$params['number']['comment'] = 'Отправте СМС с текстом <nobr>"{msg}"</nobr> на номер "{num}"';
		}

		if(!$params['transactionId']) throw new AVA_Exception('Не определен номер платежной транзакции');
		$params['msg'] = $this->settings['prefix1'].$this->settings['prefix2'].' '.$params['transactionId'];
		return $this->obj->Core->Replace($params['number']['comment'], $this->obj, $params);
	}



	/********************************************************************************************************************************************************************

																		Служебные функции

	*********************************************************************************************************************************************************************/

	private function getPayHash($p){
		return md5($p['date'].$p['msg_trans'].$p['operator_id'].$p['user_id'].$p['smsid'].$p['cost_rur'].$p['ran'].$p['test'].$p['num'].$p['country_id'].$this->settings['secretKey']);
	}

	private function getMsg($msg, &$prefix = ''){
		$msg = regExp::split("|\s|", $msg, true);
		$prefix = $msg[0];
		return $msg[1];
	}

	private function out($status, $error = 0, $errorMsg = '', $id = false, $params = false){
		if($error && !$errorMsg){
			switch($error){
				case 4: $errorMsg = 'Access denied for your phone'; break;
				case 10: $errorMsg = 'Invalid secret key'; break;
				case 101: $errorMsg = 'Invalid short number'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$params = array('id' => $id, 'params' => $params);
		$params['output'] = $status ? "smsid: {$this->obj->values['smsid']}\nstatus: reply\n\n".$this->getSmsText($params) : 'Error '.$error.': '.$errorMsg;
		$params['logId'] = $this->obj->savePayLog($this->obj->values['smsid'], $this->obj->printParams(), $params['output'], $status, $params['params'] ? 1 : 0, $error, $errorMsg);

		return $params;
	}

	public static function getInstallParams(){
		return array('A1Agregator', 'a1');
	}
}

?>