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



class smsObject extends objectInterface{
	protected $payId;
	protected $settings = array();
	protected $payParams = array();
	protected $curParams = array();
	protected $obj;

	public function __construct($obj, $payId){
		$this->obj = $obj;
		$this->payId = $payId;

		if(!empty($this->payId)){
			$this->payParams = $this->obj->smsParams($this->payId);
			$this->settings = $this->payParams['vars'];
		}

		if(method_exists($this, '__init') || method_exists($this, '__ava____init')) $this->__init();
	}

	public function __ava__setNewSmsForm($params){		/*
			Форма для нового способа оплаты SMS
		*/
	}

	public function __ava__setNewNumberForm($params){
		/*
			Форма для нового номера SMS
		*/
	}

	public function __ava__checkNewSmsForm($params){		/*
			Проверка нового способа оплаты SMS
		*/

		return array();	}

	public function __ava__checkNewNumberForm($params){
		/*
			Проверка нового способа оплаты SMS
		*/

		return array();
	}

	public function __ava__getSmsComment($params){
		/*
			Возвращает текст-комментарий для уведомления пользователя о необходимости отправить SMS
		*/
	}

	public function __ava__acceptSms($params){		/*
			Возвращает параметры платежа
		*/	}


	/********************************************************************************************************************************************************************

																		Служебные функции

	*********************************************************************************************************************************************************************/

	protected function __ava__getSmsText($params){		/*
			Возвращает текст отправляемой SMS
			По идее должен вытаскивать все услуги из счета, пробегать по ним и каждой пытаться получить данные. Пока просто тупо возвращает "платеж принят"
		*/

		return 'Платеж принят';	}
}

?>