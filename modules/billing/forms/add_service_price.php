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


if($type != 'onetime'){
	$matrix['price'.$id]['text'] = '{Call:Lang:modules:billing:raschetnaias:'.Library::serialize(array(Dates::termsListVars($baseTerm), $currency)).'}';
	$matrix['price'.$id]['type'] = 'text';
	$matrix['price'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanaras}';

	$matrix['price2'.$id]['text'] = '{Call:Lang:modules:billing:raschetnaias1:'.Library::serialize(array(Dates::termsListVars($baseTerm, 'multi'), $currency)).'}';
	$matrix['price2'.$id]['type'] = 'text';
	$matrix['price2'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanaras}';

	$matrix['modify_price'.$id]['text'] = '{Call:Lang:modules:billing:raschetnaias2:'.Library::serialize(array(Dates::termsListVars($baseTerm), $currency)).'}';
	$matrix['modify_price'.$id]['type'] = 'text';
	$matrix['modify_price'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanaras1}';
}

$matrix['install_price'.$id]['text'] = '{Call:Lang:modules:billing:stoimostusta:'.Library::serialize(array($currency)).'}';
$matrix['install_price'.$id]['type'] = 'text';
$matrix['install_price'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanasto}';

$matrix['modify_install_price'.$id]['text'] = '{Call:Lang:modules:billing:raschetnaias3:'.Library::serialize(array($currency)).'}';
$matrix['modify_install_price'.$id]['type'] = 'text';
$matrix['modify_install_price'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanaras2}';

$matrix['sum'.$id]['text'] = '{Call:Lang:modules:billing:itogovaiasto:'.Library::serialize(array($currency)).'}';
$matrix['sum'.$id]['type'] = 'text';
$matrix['sum'.$id]['disabled'] = '1';

$matrix['discount'.$id]['text'] = '{Call:Lang:modules:billing:skidka:'.Library::serialize(array($currency)).'}';
$matrix['discount'.$id]['type'] = 'text';

$matrix['total'.$id]['text'] = '{Call:Lang:modules:billing:konechnaiasu:'.Library::serialize(array($currency)).'}';
$matrix['total'.$id]['type'] = 'text';
$matrix['total'.$id]['comment'] = '{Call:Lang:modules:billing:ehtasummabud}';
$matrix['total'.$id]['warn'] = '{Call:Lang:modules:billing:neukazanakon}';

$matrix['prolong_price'.$id]['text'] = 'Стоимость применяемая для продления';
$matrix['prolong_price'.$id]['type'] = 'text';
$matrix['prolong_price'.$id]['warn'] = 'Вы не указали стоимость применяемую при продлении';

$matrix['ind_price'.$id]['text'] = '{Call:Lang:modules:billing:politikapere}';
$matrix['ind_price'.$id]['type'] = 'select';
$matrix['ind_price'.$id]['additional'] = array(
	'0' => '{Call:Lang:modules:billing:nepereschity}',
	'1' => '{Call:Lang:modules:billing:pereschityva}',
	'2' => '{Call:Lang:modules:billing:pereschityva1}',
);

if($type != 'onetime'){
	$matrix['test_term'.$id]['text'] = 'Тестовый срок';
	$matrix['test_term'.$id]['type'] = 'text';
	$matrix['test_term'.$id]['warn'] = '{Call:Lang:modules:billing:neukazansrok1}';

	$prefix = '';
	$terms = $pTerms = array();
	$text = 'Срок оплаты';

	include(_W.'modules/billing/forms/term.php');
	include(_W.'modules/billing/forms/prolong_style.php');
	$matrix[$prefix.'term'.$id]['type'] = $matrix[$prefix.'auto_prolong'.$id]['type'] = 'text';

	$exclude = array('acc_term'.$id, 'acc_auto_prolong'.$id, 'acc_auto_prolong_fract'.$id);
}

?>