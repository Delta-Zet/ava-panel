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


$field = 'date';
$text = '{Call:Lang:modules:billing:datapriemapl}';
require(_W.'forms/type_calendar2.php');

$matrix['sum']['text'] = '{Call:Lang:modules:billing:summakzachis}';
$matrix['sum']['type'] = 'text';
$matrix['sum']['comment'] = 'Для списания указывайте отрицательную сумму. Если сумма положительная - она будет зачислена на баланс вне зависимости от назначения платежа.';
$matrix['sum']['warn'] = '{Call:Lang:modules:billing:neukazanosko}';
$matrix['sum']['warn_function'] = 'regExp::float';

$matrix['bonus']['text'] = '{Call:Lang:modules:billing:bonus}';
$matrix['bonus']['type'] = 'text';
$matrix['bonus']['warn_function'] = 'regExp::float';

$matrix['currency']['text'] = '{Call:Lang:modules:billing:valiuta}';
$matrix['currency']['type'] = 'select';
$matrix['currency']['warn'] = '{Call:Lang:modules:billing:neukazanaval}';
$matrix['currency']['additional'] = $this->getCurrency();

$matrix['payment']['text'] = '{Call:Lang:modules:billing:sposoboplaty1}';
$matrix['payment']['type'] = 'select';
$matrix['payment']['warn'] = '{Call:Lang:modules:billing:neukazanspos}';
$matrix['payment']['additional'] = $this->getPayment();

$matrix['uniq_id']['text'] = '{Call:Lang:modules:billing:nomerplatezh}';
$matrix['uniq_id']['type'] = 'text';

?>