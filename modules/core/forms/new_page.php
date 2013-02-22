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


$matrix['name']['text'] = '{Call:Lang:core:core:nazvanieshab1}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:vyneukazalin1}';

$matrix['url']['text'] = '{Call:Lang:core:core:urlimia}';
$matrix['url']['type'] = 'text';
$matrix['url']['warn'] = '{Call:Lang:core:core:neukazanourl1}';
$matrix['url']['warn_function'] = 'regExp::Folder';
$matrix['url']['comment'] = '{Call:Lang:core:core:urlimiaehtoi}';

$matrix['type']['text'] = '{Call:Lang:core:core:tipstranitsy}';
$matrix['type']['type'] = 'select';
$matrix['type']['warn'] = '{Call:Lang:core:core:neukazantips}';
$matrix['type']['additional'] = array(
	'.tmpl' => '{Call:Lang:core:core:obychnaiastr}',
	'blocks' => '{Call:Lang:core:core:kollektsiiab}',
	'.css' => '{Call:Lang:core:core:fajllistovst}',
	'.js' => '{Call:Lang:core:core:fajljavascri}',
	'other' => '{Call:Lang:core:core:drugoj}'
);
$matrix['type']['additional_style'] = 'onChange="if(this.value == \'other\') showFormBlock(\'extension\'); else hideFormBlock(\'extension\');"';

$matrix['ext']['text'] = '{Call:Lang:core:core:rasshirenie}';
$matrix['ext']['type'] = 'text';
$matrix['ext']['comment'] = '{Call:Lang:core:core:rasshirenief}';
$matrix['ext']['additional_entry_style'] = ' id="extension" style="display: none;"';
$matrix['ext']['additional_text'] = '<script type="text/javascript">'."\n".'if(document.getElementById(\'type\').value == \'other\') showFormBlock(\'extension\');'."\n".'</script>';

?>