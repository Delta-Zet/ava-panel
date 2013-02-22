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

class payBank extends paymentsObject{
	/********************************************************************************************************************************************************************

																		Форма запроса оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__payReqForm($params){
		/*
			Форма запроса оплаты
		*/

		return;

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'payment' => array(
					'additional_style' => ' onClick="showFormBlock(\'bankblock\'); hideFormBlock(\'wmblock\');"'
				),
				'account' => array(
					'pre_text' => '<div id="bankblock" style="display: none;">',
					'text' => '{Call:Lang:modules:partner:nomerbankovs}',
					'type' => 'text',
					'warn' => '{Call:Lang:modules:partner:neukazannome}'
				),
				'bank' => array(
					'text' => '{Call:Lang:modules:partner:naimenovanie}',
					'type' => 'text',
					'warn' => '{Call:Lang:modules:partner:neukazanonai}'
				),
				'person' => array(
					'text' => '{Call:Lang:modules:partner:poluchatelpl}',
					'type' => 'text',
					'warn' => '{Call:Lang:modules:partner:neukazanpolu}'
				),
				'inn' => array(
					'text' => '{Call:Lang:modules:partner:innpoluchate}',
					'type' => 'text',
				),
				'kpp' => array(
					'post_text' => '</div>',
					'text' => '{Call:Lang:modules:partner:kpppoluchate}',
					'type' => 'text',
				),
			)
		);
	}

	public function __ava__checkReqForm($params){
		return array(
			'account' => $this->obj->values['account'],
			'bank' => $this->obj->values['bank'],
			'person' => $this->obj->values['person'],
			'inn' => $this->obj->values['inn'],
			'kpp' => $this->obj->values['kpp'],
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
				'orgName' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:polnoenaimen}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalin}'
				),
				'orgNameShort' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:sokrashchenn}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalis}'
				),
				'inn' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:inn}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalii}'
				),
				'kpp' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:kpp}'
				),
				'ogrn' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:ogrniliogrni}'
				),
				'okved' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:okvehdcherez}'
				),
				'city' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:gorod}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalig}'
				),
				'address' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:iuridicheski}',
					'warn' => '{Call:Lang:modules:partner:vyneiuridich}'
				),
				'postAddress' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:pochtovyjadr}',
					'warn' => '{Call:Lang:modules:partner:vynepochtovy}'
				),
				'leaderName' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:fiorukovodit}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalif}'
				),
				'leaderTitle' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:dolzhnostruk}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalid}'
				),
				'bank' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:nazvanievash}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalin1}'
				),
				'bankAddress' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:adresvashego}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalia}'
				),
				'bankAccount' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:nomerbankovs}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalin2}'
				),
				'bankCorrAccount' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:nomerkorresp}',
					'comment' => '{Call:Lang:modules:partner:vkliuchaiana}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalin3}'
				),
				'bik' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:bik}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalib}'
				),
				'nds' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:partner:nds}',
					'warn' => '{Call:Lang:modules:partner:vyneukazalin4}'
				)
			)
		);

		return true;
	}

	public function __ava__checkNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		return array(
			'orgName' => $this->obj->values['orgName'],
			'orgNameShort' => $this->obj->values['orgNameShort'],
			'inn' => $this->obj->values['inn'],
			'kpp' => $this->obj->values['kpp'],
			'ogrn' => $this->obj->values['ogrn'],
			'okved' => $this->obj->values['okved'],
			'city' => $this->obj->values['city'],
			'address' => $this->obj->values['address'],
			'postAddress' => $this->obj->values['postAddress'],
			'leaderName' => $this->obj->values['leaderName'],
			'leaderTitle' => $this->obj->values['leaderTitle'],
			'bank' => $this->obj->values['bank'],
			'bankAddress' => $this->obj->values['bankAddress'],
			'bankAccount' => $this->obj->values['bankAccount'],
			'bankCorrAccount' => $this->obj->values['bankCorrAccount'],
			'bik' => $this->obj->values['bik'],
			'nds' => $this->obj->values['nds'],
		);
	}
}

?>