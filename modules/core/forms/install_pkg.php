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


$matrix['source']['type'] = 'radio';
$matrix['source']['warn'] = '{Call:Lang:core:core:neukazanisto1}';
$matrix['source']['additional'] = array(
	'2' => '{Call:Lang:core:core:zagruzitizar}',
	'3' => '{Call:Lang:core:core:uzhezagruzhe}',
	'4' => '{Call:Lang:core:core:uzhezagruzhe1}'
);
$matrix['source']['additional_style']['2'] = 'onClick="showFormBlock(\'arc2\'); hideFormBlock(\'arc3\'); hideFormBlock(\'arc4\');"';
$matrix['source']['additional_style']['3'] = 'onClick="showFormBlock(\'arc3\'); hideFormBlock(\'arc2\'); hideFormBlock(\'arc4\');"';
$matrix['source']['additional_style']['4'] = 'onClick="showFormBlock(\'arc4\'); hideFormBlock(\'arc2\'); hideFormBlock(\'arc3\');"';

$matrix['archieve']['text'] = '{Call:Lang:core:core:arkhivdliaza}';
$matrix['archieve']['type'] = 'file';
$matrix['archieve']['additional']['allow_ext'] = array('.zip', '.gz', '.tar', '.bz2');
$matrix['archieve']['additional_entry_style'] = ' id="arc2" style="display: none;"';

$matrix['archieve_path']['text'] = '{Call:Lang:core:core:putdoarkhiva}';
$matrix['archieve_path']['type'] = 'text';
$matrix['archieve_path']['additional_entry_style'] = ' id="arc3" style="display: none;"';
$values['archieve_path'] = TMP;

$matrix['install_path']['additional_entry_style'] = ' id="arc4" style="display: none;"';
$matrix['install_path']['type'] = 'text';
$matrix['install_path']['text'] = '{Call:Lang:core:core:putkfajluins}';

$matrix['install_path']['additional_text'] = '<script type="text/javascript">'."\n".
	'if(document.getElementById(\'source_2\').checked){'."\n".
		'showFormBlock(\'arc2\'); hideFormBlock(\'arc3\'); hideFormBlock(\'arc4\');'."\n".
	'}'."\n".
	'else if(document.getElementById(\'source_3\').checked){'."\n".
		'showFormBlock(\'arc3\'); hideFormBlock(\'arc2\'); hideFormBlock(\'arc4\');'."\n".
	'}'."\n".
	'else if(document.getElementById(\'source_4\').checked || document.getElementById(\'source_5\').checked){'."\n".
		'showFormBlock(\'arc4\'); hideFormBlock(\'arc3\'); hideFormBlock(\'arc2\');'."\n".
	'}'."\n".
	'</script>';

?>