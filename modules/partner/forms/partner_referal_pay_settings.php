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


$matrix['pay_referal_type']['text'] = '';
$matrix['pay_referal_type']['type'] = 'radio';
$matrix['pay_referal_type']['additional'] = array(
	'default' => '{Call:Lang:modules:partner:ispolzovatst}',
	'hand' => '{Call:Lang:modules:partner:vystavitpers}'
);
$matrix['pay_referal_type']['additional_style'] = array(
	'default' => 'onClick="hideFormBlock(\'forReferals\');"',
	'hand' => 'onClick="showFormBlock(\'forReferals\');"'
);
$values['pay_referal_type'] = 'default';

$last = 'pay_referal_type';
require(_W.'modules/partner/forms/referal_orders.php');

if($first){
	$matrix[$first]['pre_text'] = '<div id="forReferals" style="display: none;">';
	$matrix[$last]['post_text'] = '</div>';
}
else $matrix[$last]['post_text'] = '<div id="forReferals" style="display: none;"></div>';

$matrix[$last]['post_text'] .= '<script type="text/javascript">
	if(document.getElementById("pay_referal_type_hand").checked) showFormBlock(\'forReferals\');
</script>';

?>