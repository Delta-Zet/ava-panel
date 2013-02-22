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



$GLOBALS['Core']->loadExtension('billing', 'serviceExtensionsObject');

class gen_bill_lease extends serviceExtensionsObject {
	/*
		Расширение по подключаемым удаленным панелям
		1. Управление формой создания новых соединений
		2. Проверка правильности соединения


		Расширения для отдельных услуг предполагают следующие возможности:
		1. Управление формой модификации услуги
		2. Управление формой описания ТП
		3. Управление формой модификации подключений
		4. Управление списком тарифов
		5. Управление списком заказов
		6. Участие в списке шаблонов для вывода списка тарифов
		7. Управление таблицами тарифов и услуг
	*/

	public function getAccOrderParams($obj, $service, $params = array()){
		/*
			Устанавливает  значения указанные при заказе
		*/

		$obj->DB->Upd(array('orders_'.$service, array('domain' => $obj->values['domain']), "`service_order_id`='".db_main::Quot($params['id'])."'"));
		return true;
	}

	public function getServiceParams($obj, $service, $params = array()){
		/*
			Устанавливает дополнительные параметры для услуги
		*/

		$tbl = 'orders_'.$service;
		if(!$obj->DB->issetField($tbl, 'domain')) $obj->DB->Alter(array($tbl, array('domain' => ''), 'ADD COLUMN'));
		return true;
	}
}

?>