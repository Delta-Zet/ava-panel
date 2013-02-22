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



$GLOBALS['Core']->loadExtension('billing', 'payments/paybank');

class paySber extends payBank{
	public function __ava__paymentForm($params){		/*
			Создает форму оплаты
		*/

		$tData = $this->obj->getTransactionParams($params['id']);
		$cData = $this->obj->getUserByClientId($tData['client_id']);

		if(empty($tData['object_id'])) $params['descript'] = '{Call:Lang:modules:billing:zachislenies:'.Library::serialize(array($cData['id'], $cData['login'], $cData['user_id'])).'}';
		elseif($tData['object_type'] == 'orders') $params['descript'] = 'Оплата информационных услуг. Заказ №'.$tData['object_id'];
		return $this->setDocument($params, 'receipt');
	}

	public static function getInstallParams(){
		return array('{Call:Lang:modules:billing:kvitantsiias}', 'Sber');
	}
}

?>