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


$matrix['ip']['text'] = '{Call:Lang:core:core:ipadresilieg}';
$matrix['ip']['type'] = 'text';
$matrix['ip']['comment'] = '{Call:Lang:core:core:eslivamnadoz}';
$matrix['ip']['warn'] = '{Call:Lang:core:core:neukazanipad}';
$matrix['ip']['warn_pattern'] = '^[\d\.\*]+$';

$matrix['type']['type'] = 'radio';
$matrix['type']['additional'] = array('allow' => '{Call:Lang:core:core:razreshitdos}', 'disallow' => '{Call:Lang:core:core:zapretitdost}');
$matrix['type']['warn'] = '{Call:Lang:core:core:neukazanokak}';

?>