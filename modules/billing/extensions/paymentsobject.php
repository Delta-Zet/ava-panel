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



class paymentsObject extends objectInterface{
	protected $payId;
	protected $settings = array();
	protected $payParams = array();
	protected $curParams = array();
	protected $obj;

	public function __construct($obj, $payId){
		$this->obj = $obj;
		$this->payId = $payId;

		if(!empty($this->payId)){
			$this->payParams = $this->obj->paymentParams($this->payId);
			$this->curParams = $this->obj->currencyByPayment($this->payId);
			$this->settings = $this->payParams['vars'];
		}

		if(method_exists($this, '__init') || method_exists($this, '__ava____init')) $this->__init();
	}

	/********************************************************************************************************************************************************************

																	Создание нового способа оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__setNewPaymentForm($params){		/*
			Устанавливает форму для нового платежа
		*/

		$this->obj->setContent('<p>'.$this->payParams['comment'].'</p>');
		return true;	}
	public function __ava__checkNewPaymentForm($params){
		/*
			Проверяет форму для нового платежа
		*/

		return true;
	}


	/********************************************************************************************************************************************************************

																Генерация формы для приема оплаты

	*********************************************************************************************************************************************************************/

	public function __ava__paymentForm($params){		/*
			Генерация формы
		*/

		return true;	}


	/********************************************************************************************************************************************************************

																		Оповещение о платеже

	*********************************************************************************************************************************************************************/

	public function __ava__payment($params){
		/*
			Вызывается при приеме платежа
			Обязана возвратить массив:
				id - ID транзакции
				output - вывод
				logId - id записи в логе
				params - false | array
					- sum - сумма в валюте оплаты
					- uniqId - уникальный внутренний идентификатор (если есть)
					- vars - прочие сохраняемые параметры (если есть)
		*/

		return array();
	}

	public function __ava__success($params){		/*
			Возврат пользователя в результате удачного платежа
		*/

		$this->obj->setContent('{Call:Lang:modules:billing:platezhuspes}', 'caption');		return true;
	}

	public function __ava__fail($params){
		/*
			Возврат пользователя в результате неудачного платежа
		*/

		$this->obj->setContent('{Call:Lang:modules:billing:ksozhaleniiu3}', 'caption');
		return true;
	}
}

?>