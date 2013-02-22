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


$matrix['text']['text'] = '{Call:Lang:modules:billing:imia}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:neukazanoimi}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato3}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazaniden1}';
$matrix['name']['warn_function'] = 'regExp::ident';

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['add_capt']['text'] = '{Call:Lang:modules:billing:avtomatiches1}';
$matrix['add_capt']['type'] = 'caption';

$matrix['add_with_registry']['text'] = '{Call:Lang:modules:billing:registratsii}';
$matrix['add_with_registry']['type'] = 'checkbox';

$matrix['add_with_all_payments']['text'] = '{Call:Lang:modules:billing:dostizheniia:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['add_with_all_payments']['type'] = 'text';

$matrix['add_with_logic']['text'] = '{Call:Lang:modules:billing:logika}';
$matrix['add_with_logic']['type'] = 'select';
$matrix['add_with_logic']['comment'] = '{Call:Lang:modules:billing:opredeliaetk}';
$matrix['add_with_logic']['additional'] = array('OR' => '{Call:Lang:modules:billing:ili}', 'AND' => 'И');

$matrix['add_with_all_payed_services']['text'] = '{Call:Lang:modules:billing:dostizheniia1:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['add_with_all_payed_services']['type'] = 'text';

if(!empty($modify)){
	$matrix['name']['disabled'] = '1';
}

?>