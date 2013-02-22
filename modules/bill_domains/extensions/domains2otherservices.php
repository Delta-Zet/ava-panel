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



class domains2otherServices extends objectInterface{
	public $orderedDomains = false;

	public function __ava__getEmptyDomain($obj, $orderId){
		/*
			Возвращает дмен который для данного заказа еще не использовался как привязка к хостингу
		*/

		if(!$orderId) return '';
		if(!is_array($this->orderedDomains)) $this->fetchOrderedDomains($obj, $orderId);

		$domain = reset($this->orderedDomains);
		unset($this->orderedDomains[$domain]);

		return $domain;
	}

	public function __ava__fetchOrderedDomains($obj, $orderId){
		/*
			Выявляет список всех заказанных доменов
		*/

		$this->orderedDomains = array();
		$p = $obj->DB->getPrefix();
		$t1 = $p.'order_services';
		$t2 = $p.'order_entries';

		$dbObj = $obj->DB->Req("SELECT t1.id, t1.service, t1.extra FROM $t1 AS t1, $t2 AS t2 WHERE t2.order_id='".db_main::Quot($orderId)."' AND t1.id=t2.order_service_id");
		while($r = $dbObj->Fetch()){
			$sData = $obj->serviceData($r['service']);
			if($sData['extension'] == 'domains'){				if($domain = $obj->DB->cellFetch(array('orders_'.$sData['name'], 'domain', "`service_order_id`='{$r['id']}'"))){					$this->orderedDomains[$domain] = $domain;
				}			}
			else{				$extra = Library::unserialize($r['extra']);
				if(!empty($extra['domain'])) unset($this->orderedDomains[$extra['domain']]);			}		}
	}}

?>