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


$matrix['price_capt'.$id]['type'] = 'caption';
$matrix['price_capt'.$id]['text'] = '{Call:Lang:modules:billing:dannyedliara}';

$matrix['prolong_price'.$id]['text'] = 'Стоимость продления, за '.Dates::termsListVars($baseTerm);
$matrix['prolong_price'.$id]['type'] = 'text';
$matrix['prolong_price'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanaras}';

$matrix['modify_price'.$id]['text'] = '{Call:Lang:modules:billing:raschetnaias2:'.Library::serialize(array(Dates::termsListVars($baseTerm), $currency)).'}';
$matrix['modify_price'.$id]['type'] = 'text';
$matrix['modify_price'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanaras1}';

$matrix['ind_price'.$id]['text'] = '{Call:Lang:modules:billing:politikapere}';
$matrix['ind_price'.$id]['type'] = 'select';
$matrix['ind_price'.$id]['additional'] = array(
	'0' => '{Call:Lang:modules:billing:nepereschity}',
	'1' => '{Call:Lang:modules:billing:pereschityva}',
	'2' => '{Call:Lang:modules:billing:pereschityva1}',
);

$matrix['sum'.$id]['text'] = '{Call:Lang:modules:billing:itogovaiasto1}';
$matrix['sum'.$id]['type'] = 'text';
$matrix['sum'.$id]['disabled'] = '1';

$matrix['discount'.$id]['text'] = '{Call:Lang:modules:billing:skidka1}';
$matrix['discount'.$id]['type'] = 'text';

$matrix['total'.$id]['text'] = '{Call:Lang:modules:billing:konechnaiasu1}';
$matrix['total'.$id]['type'] = 'text';
$matrix['total'.$id]['comment'] = '{Call:Lang:modules:billing:ehtasummabud}';
$matrix['total'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanakon}';

$matrix['dates_capt'.$id]['type'] = 'caption';
$matrix['dates_capt'.$id]['text'] = '{Call:Lang:modules:billing:daty}';

$field = 'last_paid'.$id;
$text = 'Дата продления';
require(_W.'forms/type_calendar2.php');
$values['last_paid'.$id] = time();

$field = 'paid_to'.$id;
$text = '{Call:Lang:modules:billing:oplachenado}';
require(_W.'forms/type_calendar2.php');

$matrix['acc_auto'.$id]['text'] = 'Продлить на удаленном сервере автоматически';
$matrix['acc_auto'.$id]['type'] = 'checkbox';
$values['acc_auto'.$id] = 1;

$matrix['other_capt'.$id]['type'] = 'caption';
$matrix['other_capt'.$id]['text'] = 'Прочие параметры';

?>