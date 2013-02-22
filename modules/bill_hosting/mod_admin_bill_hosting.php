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


class mod_admin_bill_hosting extends gen_bill_hosting{
	protected function func_filter(){
		/*
			Фильтр доменных зон
		*/

		$this->typicalMain(
			array(
				'caption' => 'Добавить доменную зону',
				'isUniq' => array('zone' => 'Такая доменная зона уже есть в фильтре'),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array('zone' => 'Доменная зона'),
						'orderFields' => array('zone' => 'доменной зоне')
					)
				)
			)
		);
	}
}

?>