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


$matrix['partner_set_type']['text'] = 'Зачислить премию партнеру';
$matrix['partner_set_type']['comment'] = 'Начисление премии будет только если партнер по рекомендации которого пришел клиент существует';
$matrix['partner_set_type']['type'] = 'select';
$matrix['partner_set_type']['additional'] = array(
	'' => 'Не начислять',
	'auto' => 'Вычислить автоматически',
	'manual' => 'Указать',
);
$matrix['partner_set_type']['additional_style'] = ' onChange="return setPartnerPay();"';
$values['partner_set_type'] = 'auto';

$matrix['pay2partner_']['text'] = 'Сумма зачисляемая партнеру';
$matrix['pay2partner_']['type'] = 'text';
$matrix['pay2partner_']['warn_function'] = 'regExp::float';
$matrix['pay2partner_']['value'] = isset($sum) ? $sum : '';

if(!empty($refs)){
	foreach($refs as $i => $e){
		$matrix['pay2partnerRef'.$i.'_']['text'] = '{Call:Lang:modules:partner:otchislitref:'.Library::serialize(array($i, $e['login'], $cur)).'}';
		$matrix['pay2partnerRef'.$i.'_']['type'] = 'text';
		$matrix['pay2partnerRef'.$i.'_']['value'] = $sum * $refPrice['order_'.$i] / 100;
	}
}

if(!empty($fromOrder)){
	$matrix['partner_set_type']['type'] = 'hidden';
	$matrix['partner_set_type']['value'] = 'manual';
}
else{
	$matrix['partner_set_type']['pre_text'] = '<div id="partner_blk">';
	$matrix['pay2partner_']['pre_text'] = '<div id="partner_pay_blk">';
	$matrix[Library::lastKey($matrix)]['post_text'] = '</div></div><script type="text/javascript">
		function setPartnerPay(){
			if(document.getElementById("partner_set_type").value == "manual") showFormBlock("partner_pay_blk");
			else hideFormBlock("partner_pay_blk");
		}

		function setPartner(){
			if(!document.getElementById("foundation_type") || document.getElementById("foundation_type").value == "balance") showFormBlock("partner_blk");
			else hideFormBlock("partner_blk");
		}

		setPartnerPay();
		setPartner();
	</script>';

	$matrix['foundation_type']['additional_style'] = ' onChange="setBalanceBlk(); setPartner();"';
}

?>