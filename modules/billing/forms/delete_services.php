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


foreach($accs as $i => $e){
	$matrix['capt'.$i]['type'] = 'caption';
	$matrix['capt'.$i]['text'] = $e;

	$matrix['date'.$i]['type'] = 'calendar2';
	$matrix['date'.$i]['text'] = 'Дата удаления';
	$values['date'.$i] = time();

	$matrix['type'.$i]['type'] = 'select';
	$matrix['type'.$i]['text'] = '{Call:Lang:modules:billing:prichinaudal}';
	$matrix['type'.$i]['additional'] = array(
		'policy' => 'Нарушение правил',
		'term' => 'Истечение срока оплаты',
		'accord' => 'Добровольный отказ',
	);

	$matrix['reason'.$i]['type'] = 'text';
	$matrix['reason'.$i]['text'] = 'Комментарий к причине удаления';

	$matrix['stay'.$i]['type'] = 'text';
	$matrix['stay'.$i]['comment'] = 'Отрицательная сумма будет списана с баланса в качестве штрафа';
	$matrix['stay'.$i]['text'] = 'Вернуть на баланс, '.$this->getParamByServiceId($i, 'currencyName');

	$matrix['delete'.$i]['type'] = 'checkbox';
	$matrix['delete'.$i]['text'] = 'Удалить аккаунт';
	$matrix['delete'.$i]['value'] = 1;

	$matrix['auto'.$i]['type'] = 'checkbox';
	$matrix['auto'.$i]['text'] = 'В т.ч. удалить на сервере';
	$matrix['auto'.$i]['value'] = 1;

	$matrix['notify'.$i]['type'] = 'checkbox';
	$matrix['notify'.$i]['text'] = '{Call:Lang:modules:billing:uvedomitpolz}';
	$matrix['notify'.$i]['value'] = 1;
}

?>