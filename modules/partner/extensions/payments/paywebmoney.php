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

	/********************************************************************************************************************************************************************

																		Форма запроса оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__payReqForm($params){
		/*
			Форма запроса оплаты
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'payment' => array(
					'additional_style' => ' onClick="showFormBlock(\'wmblock\'); hideFormBlock(\'bankblock\');"'
				),
				'purse' => array(
					'pre_text' => '<div id="wmblock">',
					'post_text' => '</div>',
					'text' => '{Call:Lang:modules:partner:koshelek}',
					'type' => 'text',
					'warn' => '{Call:Lang:modules:partner:neukazankosh}'
				)
			)
		);
	}

	public function __ava__checkReqForm($params){
		return array(
			'purse' => $this->obj->values['purse']
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
			)
		);

		return true;
	}

	public function checkNewPaymentForm($params){
		/*
			Устанавливает форму для нового платежа
		*/

		return array(
		);
	}
}

?>