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


$matrix['name']['text'] = '{Call:Lang:modules:billing:imiagruppypr}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazanoimi3}';

$matrix['code_distrib_style']['text'] = '{Call:Lang:modules:billing:kodymogutraz}';
$matrix['code_distrib_style']['type'] = 'select';
$matrix['code_distrib_style']['warn'] = '{Call:Lang:modules:billing:neukazanokto}';
$matrix['code_distrib_style']['additional_style'] = 'onChange="if(this.value == \'1\') showFormBlock(\'client_groups\'); else hideFormBlock(\'client_groups\');"';
$matrix['code_distrib_style']['additional'] = array(
	'0' => '{Call:Lang:modules:billing:tolkoadminy}',
	'1' => '{Call:Lang:modules:billing:adminyisushc}'
);

$matrix['code_distrib_client_levels']['type'] = 'checkbox_array';
$matrix['code_distrib_client_levels']['additional'] = isset($clientGroups) ? $clientGroups : array();
$matrix['code_distrib_client_levels']['pre_text'] = '<div id="client_groups">';
$matrix['code_distrib_client_levels']['post_text'] = '</div><script type="text/javascript">
	if(document.getElementById(\'code_distrib_style\').value == \'1\') showFormBlock(\'client_groups\'); else hideFormBlock(\'client_groups\');
</script>';

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

?>