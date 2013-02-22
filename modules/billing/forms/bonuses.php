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


$matrix['name']['text'] = '{Call:Lang:modules:billing:imia}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazanoimi}';

$matrix['min_sum']['text'] = '{Call:Lang:modules:billing:minimalnaias:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['min_sum']['type'] = 'text';
$matrix['min_sum']['warn'] = '{Call:Lang:modules:billing:neukazanamin}';

$matrix['bonus']['text'] = '{Call:Lang:modules:billing:bonus}';
$matrix['bonus']['type'] = 'text';
$matrix['bonus']['warn'] = '{Call:Lang:modules:billing:neukazanbonu}';

$matrix['bonus_type']['text'] = '{Call:Lang:modules:billing:bonusukazanv}';
$matrix['bonus_type']['type'] = 'select';
$matrix['bonus_type']['additional'] = array(
	'percent' => '{Call:Lang:modules:billing:protsentakho}',
	'money' => $this->getMainCurrencyName()
);

$matrix['client_loyalty_levels']['text'] = '{Call:Lang:modules:billing:bonuspolucha}';
$matrix['client_loyalty_levels']['type'] = 'checkbox_array';
$matrix['client_loyalty_levels']['additional'] = $clientGroups;

$field = 'start';
$text = '{Call:Lang:modules:billing:ispolzovats}';
require(_W.'forms/type_calendar2.php');

$field = 'end';
$text = '{Call:Lang:modules:billing:po}';
require(_W.'forms/type_calendar2.php');

$matrix['show']['text'] = '{Call:Lang:modules:billing:bonusispolzu}';
$matrix['show']['type'] = 'checkbox';

?>