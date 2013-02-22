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


$matrix[$prefix.'domain'.$id]['text'] = '{Call:Lang:modules:bill_hosting:domendliakot}';
$matrix[$prefix.'domain'.$id]['type'] = 'text';
$matrix[$prefix.'domain'.$id]['warn'] = '{Call:Lang:modules:bill_hosting:neukazandome}';
$values[$prefix.'domain'.$id] = empty($domain) ? '' : $domain;

$matrix[$prefix.'ident'.$id]['text'] = '{Call:Lang:modules:bill_hosting:loginkhostin}';
$matrix[$prefix.'ident'.$id]['comment'] = '{Call:Lang:modules:bill_hosting:ehtotloginbu}';
$matrix[$prefix.'ident'.$id]['warn'] = '{Call:Lang:modules:bill_hosting:neukazanlogi}';
$matrix[$prefix.'ident'.$id]['warn_function'] = 'regExp::ident';
$matrix[$prefix.'ident'.$id]['type'] = 'text';

$matrix[$prefix.'pwd'.$id]['text'] = '{Call:Lang:modules:bill_hosting:parolakkaunt}';
$matrix[$prefix.'pwd'.$id]['type'] = 'pwd';
$matrix[$prefix.'pwd'.$id]['comment'] = '{Call:Lang:modules:bill_hosting:esliostavite}';

$matrix[$prefix.'cpwd'.$id]['text'] = '{Call:Lang:modules:bill_hosting:podtverditpa}';
$matrix[$prefix.'cpwd'.$id]['type'] = 'pwd';

?>