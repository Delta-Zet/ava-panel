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


$matrix['pay_type'.$bannerId]['text'] = '';
$matrix['pay_type'.$bannerId]['type'] = 'radio';
$matrix['pay_type'.$bannerId]['additional'] = array(
	'default' => '{Call:Lang:modules:partner:ispolzovatst}',
	'hand' => '{Call:Lang:modules:partner:vystavitpers}'
);
$matrix['pay_type'.$bannerId]['additional_style'] = array(
	'default' => 'onClick="hideFormBlock(\'payTypeBlock'.$bannerId.'\');"',
	'hand' => 'onClick="showFormBlock(\'payTypeBlock'.$bannerId.'\');"'
);
$values['pay_type'.$bannerId] = 'default';

$matrix['click_pay'.$bannerId]['pre_text'] = '<div id="payTypeBlock'.$bannerId.'" style="display: none;">';
$matrix['click_pay'.$bannerId]['text'] = '{Call:Lang:modules:partner:stoimostklik:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['click_pay'.$bannerId]['type'] = 'text';

$matrix['view_pay'.$bannerId]['text'] = '{Call:Lang:modules:partner:stoimostpoka:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['view_pay'.$bannerId]['type'] = 'text';

$matrix['special4groups'.$bannerId]['text'] = '{Call:Lang:modules:partner:vystavitosob}';
$matrix['special4groups'.$bannerId]['type'] = 'checkbox';
$matrix['special4groups'.$bannerId]['additional_style'] = 'onClick="if(this.checked) showFormBlock(\'payTypeGrpBlock'.$bannerId.'\'); else hideFormBlock(\'payTypeGrpBlock'.$bannerId.'\');"';

$first = false;
foreach($siteGroups as $i => $e){
	$matrix['grp_caption_'.$i.$bannerId]['text'] = $e;
	$matrix['grp_caption_'.$i.$bannerId]['type'] = 'caption';
	if(!$first) $first = 'grp_caption_'.$i.$bannerId;

	$matrix['click_pay_'.$i.$bannerId]['text'] = '{Call:Lang:modules:partner:stoimostklik:'.Library::serialize(array($this->getMainCurrencyName())).'}';
	$matrix['click_pay_'.$i.$bannerId]['type'] = 'text';

	$matrix['view_pay_'.$i.$bannerId]['text'] = '{Call:Lang:modules:partner:stoimostpoka:'.Library::serialize(array($this->getMainCurrencyName())).'}';
	$matrix['view_pay_'.$i.$bannerId]['type'] = 'text';
	$last = 'view_pay_'.$i.$bannerId;
}

if(!$first){
	$matrix['view_pay'.$bannerId]['post_text'] = '<div id="payTypeGrpBlock'.$bannerId.'"></div></div>';
	$last = 'view_pay'.$bannerId;
}
else{
	$matrix[$first]['pre_text'] = '<div id="payTypeGrpBlock'.$bannerId.'" style="display: none;">';
	$matrix[$last]['post_text'] = '</div></div>';
}

$matrix[$last]['post_text'] .= '<script type="text/javascript">
	if(document.getElementById(\'pay_type'.$bannerId.'_hand\').checked) showFormBlock(\'payTypeBlock'.$bannerId.'\');
	if(document.getElementById(\'special4groups'.$bannerId.'\').checked) showFormBlock(\'payTypeGrpBlock'.$bannerId.'\');
</script>';

?>