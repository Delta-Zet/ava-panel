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

class payRobox extends paymentsObject{
	protected function __init(){		if(!empty($this->settings['pwd1'])) $this->settings['pwd1'] = Library::decrypt($this->settings['pwd1']);
		if(!empty($this->settings['pwd2'])) $this->settings['pwd2'] = Library::decrypt($this->settings['pwd2']);
	}

	public function __ava__paymentForm($params){		/*
			Создает форму оплаты
		*/

		if(empty($this->settings['login']) || empty($this->settings['pwd1']) || empty($this->settings['pwd2'])){
			$this->obj->setMeta('{Call:Lang:modules:billing:ksozhaleniiu2}');			return false;		}

		$tData = $this->obj->getTransactionParams($params['id']);
		$uData = $this->obj->getUserByClientId($tData['client_id']);

		$this->obj->setContent(
			$this->obj->getFormText(
				$this->obj->addFormBlock(
					$this->obj->newForm(
						'payRobox',
						$this->settings['mode'] ? 'https://merchant.roboxchange.com/Index.aspx' : 'http://test.robokassa.ru/Index.aspx'
					),
					'pay'
				),
				array('_pay' => $params['sum'].' '.$this->curParams['text']),
				array(
					'MrchLogin' => $this->settings['login'],
					'OutSum' => $params['sum'],
					'InvId' => $params['id'],
					'Desc' => $params['descript'],
					'SignatureValue' => $this->getSign($params),
					'IncCurrLabel' => $this->settings['IncCurrLabel'],
					'Culture' => $this->settings['Culture'],
					'Email' => $uData['eml']
				)
			)
		);

		return true;
	}

	public function __ava__payment($params){		/*
			Возвращает параметры платежа
		*/

		$data = $this->obj->getTransactionParams($this->obj->values['InvId']);

		if($this->getSign2($this->obj->values) != regExp::lower($this->obj->values['SignatureValue'])) return $this->out(0, 10);		elseif($data['status']) return $this->out(1, 21);

		return $this->out(
			1, 0, '',
			array(
				'sum' => $this->obj->values['OutSum'],
				'uniqId' => $this->obj->values['InvId'],
				'vars' => array()
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

		$http = new httpClient('http://merchant.roboxchange.com/WebService/Service.asmx/GetCurrencies?MerchantLogin='.(isset($params['values']['login']) ? $params['values']['login'] : 'demo').'&Language=ru');
		$http->send();
		$result = XML::parseXml($http->getResponseBody(), $attr);
		$pays = array('' => 'По умолчанию');

		if($result){
			if(Library::isHash($result['CurrenciesList']['Groups']['Group'])){				$result['CurrenciesList']['Groups']['Group'] = array($result['CurrenciesList']['Groups']['Group']);
				$attr['CurrenciesList']['Groups']['Group'] = array($attr['CurrenciesList']['Groups']['Group']);
			}

			foreach($result['CurrenciesList']['Groups']['Group'] as $i => $e){
				if(!empty($e['Items']['Currency'])){					if(Library::isHash($e['Items']['Currency'])){						$e['Items']['Currency'] = array($e['Items']['Currency']);
						$attr['CurrenciesList']['Groups']['Group'][$i]['Items']['Currency'] = array($attr['CurrenciesList']['Groups']['Group'][$i]['Items']['Currency']);					}

					foreach($e['Items']['Currency'] as $i1 => $e1){						$pays[$attr['CurrenciesList']['Groups']['Group'][$i]['Items']['Currency'][$i1]['@attr']['Label']] = $attr['CurrenciesList']['Groups']['Group'][$i]['Items']['Currency'][$i1]['@attr']['Name'];					}
				}			}
		}

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'mode' => array(
					'type' => 'radio',
					'text' => '',
					'warn' => '{Call:Lang:modules:billing:vyneukazalir}',
					'additional' => array('0' => '{Call:Lang:modules:billing:testovyjrezh}', '1' => '{Call:Lang:modules:billing:rabochijrezh}')
				),
				'login' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:login}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalil}',
				),
				'pwd1' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:pervyjplatez}',
					'warn' => '{Call:Lang:modules:billing:vyneukazalip1}',
					'value' => empty($params['values']['pwd1']) ? '' : Library::decrypt($params['values']['pwd1'])
				),
				'pwd2' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:billing:vtorojplatez}',
					'warn' => '{Call:Lang:modules:billing:vyneukazaliv1}',
					'value' => empty($params['values']['pwd2']) ? '' : Library::decrypt($params['values']['pwd2'])
				),
				'IncCurrLabel' => array(
					'type' => 'select',
					'text' => 'Способ оплаты предлагаемый по умолчанию',
					'additional' => $pays
				),
				'Culture' => array(
					'type' => 'select',
					'text' => 'Язык',
					'additional' => array('ru' => 'Русский', 'en' => 'Английский')
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
			'mode' => $this->obj->values['mode'],
			'login' => $this->obj->values['login'],
			'pwd1' => Library::crypt($this->obj->values['pwd1']),
			'pwd2' => Library::crypt($this->obj->values['pwd2']),
			'IncCurrLabel' => $this->obj->values['IncCurrLabel'],
			'Culture' => $this->obj->values['Culture'],
		);
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	private function getSign($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($this->settings['login'].':'.$v['sum'].':'.$v['id'].':'.$this->settings['pwd1']));	}

	private function getSign2($v){
		/*
			Возвращает платежный хеш который должен быть
		*/

		return regExp::lower(md5($v['OutSum'].':'.$v['InvId'].':'.$this->settings['pwd2']));
	}

	private function out($status, $error = 0, $errorMsg = '', $params = false){		if($error && !$errorMsg){
			switch($error){
				case 4: $errorMsg = '{Call:Lang:modules:billing:netdostupakp}'; break;
				case 10: $errorMsg = '{Call:Lang:modules:billing:nepravilnyjs}'; break;
				case 101: $errorMsg = '{Call:Lang:modules:billing:nevernyjkosh}'; break;
				default: $errorMsg = $this->obj->getPayLogErrorMsg($error);
			}
		}

		$out = $status ? 'OK'.$this->obj->values['InvId'] : 'Error '.$error.' : '.$errorMsg;
		$logId = $this->save($out, $status, $params ? 1 : 0, $error, $errorMsg);

		return array(
			'id' => $this->obj->values['InvId'],
			'output' => $out,
			'logId' => $logId,
			'params' => $params
		);	}

	private function save($output, $answerStatus, $transactionStatus, $error = 0, $errorMsg = ''){
		/*
			Пересылает запрос на сохранение
		*/

		return $this->obj->savePayLog($this->obj->values['InvId'], $this->obj->printParams(), $output, $answerStatus, $transactionStatus, $error, $errorMsg);	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:platezhnyjsh2}', 'Robox');
	}
}

?>