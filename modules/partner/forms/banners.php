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


$matrix['text']['text'] = '{Call:Lang:modules:partner:imiabanera}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:partner:neukazanoimi}';

$matrix['name']['text'] = '{Call:Lang:modules:partner:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:partner:neukazaniden}';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['settings_caption']['text'] = '{Call:Lang:modules:partner:nastrojki}';
$matrix['settings_caption']['type'] = 'caption';

$matrix['link']['text'] = '{Call:Lang:modules:partner:ssylkadliape}';
$matrix['link']['type'] = 'text';
$matrix['link']['warn'] = '{Call:Lang:modules:partner:neukazanassy}';

$matrix['click_style']['text'] = '{Call:Lang:modules:partner:uchityvatper}';
$matrix['click_style']['type'] = 'select';
$matrix['click_style']['additional'] = array(
	'0' => '{Call:Lang:modules:partner:nastrojkipou}',
	'1' => '{Call:Lang:modules:partner:tolkosprover}',
	'2' => '{Call:Lang:modules:partner:sliubykhsajt}',
	'3' => '{Call:Lang:modules:partner:otkudaugodno}',
);

$matrix['type']['text'] = '{Call:Lang:modules:partner:tipbanera}';
$matrix['type']['type'] = 'radio';
$matrix['type']['warn'] = '{Call:Lang:modules:partner:neukazantipb}';
$matrix['type']['additional'] = array(
	'text' => '{Call:Lang:modules:partner:tekstovyj}',
	'image' => '{Call:Lang:modules:partner:graficheskij}'
);
$matrix['type']['additional_style'] = array(
	'text' => 'onClick="showFormBlock(\'textCodeBlock\'); hideFormBlock(\'imageCodeBlock\');"',
	'image' => 'onClick="hideFormBlock(\'textCodeBlock\'); showFormBlock(\'imageCodeBlock\');"'
);

$matrix['code_gen_type']['text'] = '{Call:Lang:modules:partner:kodsgeneriro}';
$matrix['code_gen_type']['type'] = 'radio';
$matrix['code_gen_type']['warn'] = '{Call:Lang:modules:partner:vyneukazalik}';
$matrix['code_gen_type']['additional'] = array(
	'auto' => '{Call:Lang:modules:partner:avtomatiches}',
	'js' => '{Call:Lang:modules:partner:avtomatiches1}',
	'manual' => '{Call:Lang:modules:partner:vruchnuiu}'
);
$matrix['code_gen_type']['additional_style'] = array(
	'auto' => 'onClick="showFormBlock(\'autoCodeBlock\'); hideFormBlock(\'manualCodeBlock\');"',
	'js' => 'onClick="showFormBlock(\'autoCodeBlock\'); hideFormBlock(\'manualCodeBlock\');"',
	'manual' => 'onClick="hideFormBlock(\'autoCodeBlock\'); showFormBlock(\'manualCodeBlock\');"',
);

$matrix['content']['pre_text'] = '<div id="autoCodeBlock"><div id="textCodeBlock">';
$matrix['content']['text'] = '{Call:Lang:modules:partner:tekstbanera}';
$matrix['content']['type'] = 'textarea';
$matrix['content']['post_text'] = '</div>';

$matrix['image']['pre_text'] = '<div id="imageCodeBlock">';
$matrix['image']['text'] = '{Call:Lang:modules:partner:kartinkabane}';
$matrix['image']['type'] = 'file';
$matrix['image']['additional'] = array('allow_ext' => array('.gif', '.jpg', '.png', '.pcx'), 'dstFolder' => $this->Core->getParam('partnerBannerFolder', $this->mod));
$matrix['image']['post_text'] = '</div></div>';
if(empty($modify)){
	$matrix['image']['warn'] = '{Call:Lang:modules:partner:nezagruzhenf}';
	$matrix['image']['checkConditions']['type'] = 'image';
	$matrix['image']['checkConditions']['code_gen_type'] = array('auto', 'js');
}

$matrix['code']['pre_text'] = '<div id="manualCodeBlock">';
$matrix['code']['text'] = '{Call:Lang:modules:partner:kodbanera}';
$matrix['code']['type'] = 'textarea';
$matrix['code']['comment'] = '{Call:Lang:modules:partner:vkodebanerau}';
$matrix['code']['post_text'] = '</div>';

$matrix['sort']['text'] = '{Call:Lang:modules:partner:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:modules:partner:banerdostupe}';
$matrix['show']['type'] = 'checkbox';
$matrix['show']['post_text'] = '<script type="text/javascript">
	if(!document.getElementById(\'type_text\').checked) hideFormBlock(\'textCodeBlock\');
	if(!document.getElementById(\'type_image\').checked) hideFormBlock(\'imageCodeBlock\');
	if(!document.getElementById(\'code_gen_type_auto\').checked && !document.getElementById(\'code_gen_type_js\').checked) hideFormBlock(\'autoCodeBlock\');
	if(!document.getElementById(\'code_gen_type_manual\').checked) hideFormBlock(\'manualCodeBlock\');
</script>';

$matrix['price_caption']['text'] = '{Call:Lang:modules:partner:rastsenki}';
$matrix['price_caption']['type'] = 'caption';

$bannerId = '';
require(_W.'modules/partner/forms/banner_price.php');

?>