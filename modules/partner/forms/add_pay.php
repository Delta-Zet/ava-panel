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


$matrix['login']['text'] = '{Call:Lang:modules:partner:psevdonimpar}';
$matrix['login']['type'] = 'text';
$matrix['login']['warn'] = '{Call:Lang:modules:partner:neukazanpsev}';

$matrix['date']['text'] = '{Call:Lang:modules:partner:datavnesenii}';
$matrix['date']['type'] = 'calendar2';
$matrix['date']['warn'] = '{Call:Lang:modules:partner:neukazanadat}';
$values['date'] = time();

$matrix['sum']['text'] = '{Call:Lang:modules:partner:summa:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['sum']['type'] = 'text';
$matrix['sum']['warn'] = '{Call:Lang:modules:partner:neukazanasum}';

?>