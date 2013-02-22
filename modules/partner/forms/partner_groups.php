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


$matrix['text']['text'] = '{Call:Lang:modules:partner:imiagruppy}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:partner:neukazanoimi}';

$matrix['name']['text'] = '{Call:Lang:modules:partner:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:partner:neukazaniden}';

$matrix['add_auto']['text'] = '{Call:Lang:modules:partner:prisvaivatav}';
$matrix['add_auto']['type'] = 'checkbox';
$matrix['add_auto']['additional_style'] = 'onClick="addAutoParamsChecker()"';
$matrix['add_auto']['post_text'] = '<div id="addAutoParams">';

$matrix['add_reg']['text'] = '{Call:Lang:modules:partner:prisvaivatpr}';
$matrix['add_reg']['type'] = 'checkbox';

$matrix['add_refs']['text'] = '{Call:Lang:modules:partner:prisvaivates}';
$matrix['add_refs']['type'] = 'text';

$matrix['add_orders']['text'] = '{Call:Lang:modules:partner:prisvaivates1}';
$matrix['add_orders']['type'] = 'text';

$matrix['add_clicks']['text'] = '{Call:Lang:modules:partner:prisvaivates2}';
$matrix['add_clicks']['type'] = 'text';

$matrix['add_pays']['text'] = '{Call:Lang:modules:partner:prisvaivates3:'.Library::serialize(array($this->getMainCurrencyName())).'}';
$matrix['add_pays']['type'] = 'text';

$matrix['add_time']['text'] = '{Call:Lang:modules:partner:prisvaivates4}';
$matrix['add_time']['type'] = 'text';

$matrix['add_logic']['text'] = '{Call:Lang:modules:partner:logikaispolz}';
$matrix['add_logic']['type'] = 'select';
$matrix['add_logic']['post_text'] = '</div>';
$matrix['add_logic']['additional'] = array(
	'OR' => '{Call:Lang:modules:partner:ili}',
	'AND' => 'И'
);

$matrix['sort']['text'] = '{Call:Lang:modules:partner:pozitsiiavso}';
$matrix['sort']['type'] = 'text';
$matrix['sort']['post_text'] = '<script type="text/javascript">
	function addAutoParamsChecker(){
		if(document.getElementById(\'add_auto\').checked) showFormBlock(\'addAutoParams\');
		else hideFormBlock(\'addAutoParams\');
	}

	addAutoParamsChecker();
</script>';

?>