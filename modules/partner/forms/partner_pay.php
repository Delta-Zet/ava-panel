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


$matrix['sum']['text'] = '{Call:Lang:modules:partner:summakoplate:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['sum']['type'] = 'text';
$matrix['sum']['warn'] = '{Call:Lang:modules:partner:neukazanasum1}';
$matrix['sum']['warn_function'] = 'regExp::float';

$matrix['payment']['text'] = '{Call:Lang:modules:partner:sposoboplaty}';
$matrix['payment']['type'] = 'select';
$matrix['payment']['warn'] = '{Call:Lang:modules:partner:neukazanspos}';
$matrix['payment']['additional'] = $payments;

?>