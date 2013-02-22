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

}

?>