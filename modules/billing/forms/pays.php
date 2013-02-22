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


$matrix['client_id']['text'] = '{Call:Lang:modules:billing:idpolzovatel}';
$matrix['client_id']['type'] = 'text';
$matrix['client_id']['warn'] = 'Вы не указали ID пользователя';

$matrix['foundation_type']['text'] = '{Call:Lang:modules:billing:tipoperatsii}';
$matrix['foundation_type']['type'] = 'select';
$matrix['foundation_type']['warn'] = 'Не указан тип операции';
$matrix['foundation_type']['additional_style'] = ' onChange="return setBalanceBlk();"';

$matrix['foundation_type']['additional'] = array(
	'balance' => '{Call:Lang:modules:billing:popolnenieba}',
	'bonus' => '{Call:Lang:modules:billing:zachislenieb}',
	'service' => '{Call:Lang:modules:billing:spisaniezaus}',
	'wrong' => '{Call:Lang:modules:billing:oshibochnoez}',
	'return' => '{Call:Lang:modules:billing:vozvratsreds}',
	'other' => '{Call:Lang:modules:billing:drugoe}'
);

$matrix['foundation']['text'] = '{Call:Lang:modules:billing:naznachenie}';
$matrix['foundation']['type'] = 'text';

$values['date'] = time();
require(_W.'modules/billing/forms/balance.php');
$matrix['payment']['pre_text'] = '<div id="balance_blk">';
unset($matrix['bonus']);

$matrix['bonus_set_type']['text'] = 'Начислить бонус';
$matrix['bonus_set_type']['comment'] = 'Этот параметр будет применен только если осуществляется пополнение баланса';
$matrix['bonus_set_type']['type'] = 'select';
$matrix['bonus_set_type']['additional'] = array(
	'' => 'Не начислять',
	'auto' => 'Вычислить автоматически',
	'manual' => 'Указать',
);
$matrix['bonus_set_type']['additional_style'] = ' onChange="return setBonus();"';
$values['bonus_set_type'] = 'auto';

$matrix['bonus']['pre_text'] = '<div id="bonus_blk">';
$matrix['bonus']['text'] = '{Call:Lang:modules:billing:bonus}';
$matrix['bonus']['type'] = 'text';
$matrix['bonus']['warn_function'] = 'regExp::float';
$matrix['bonus']['post_text'] = '</div></div>';

$matrix['service_id']['pre_text'] = '<div id="service_blk">';
$matrix['service_id']['text'] = 'ID заказанной услуги';
$matrix['service_id']['type'] = 'text';
$matrix['service_id']['post_text'] = '</div>';

$matrix['other_type']['pre_text'] = '<div id="other_blk">';
$matrix['other_type']['text'] = 'Таблица основания платежа';
$matrix['other_type']['type'] = 'text';

$matrix['other_id']['text'] = 'ID записи';
$matrix['other_id']['type'] = 'text';
$matrix['other_id']['post_text'] = '</div><script type="text/javascript">
	function setBonus(){
		if(document.getElementById("bonus_set_type").value == "manual") showFormBlock("bonus_blk");
		else hideFormBlock("bonus_blk");
	}

	function setBalanceBlk(){
		hideFormBlock("balance_blk");
		hideFormBlock("service_blk");
		hideFormBlock("other_blk");
		showFormBlock(document.getElementById("foundation_type").value + "_blk");
	}

	setBalanceBlk();
	setBonus();
</script>';

?>