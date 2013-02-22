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


$matrix['newBilling']['text'] = '{Call:Lang:core:core:perenestiizs}';
$matrix['newBilling']['type'] = 'select';
$matrix['newBilling']['additional'] = Library::array_merge($GLOBALS['Core']->getModulesByType('billing'), array('' => '{Call:Lang:core:core:neperenosit}'));

$matrix['newCms']['text'] = '{Call:Lang:core:core:perenestiizs1}';
$matrix['newCms']['type'] = 'select';
$matrix['newCms']['additional'] = Library::array_merge($GLOBALS['Core']->getModulesByType('cms'), array('' => '{Call:Lang:core:core:neperenosit}'));

$matrix['newPartner']['text'] = '{Call:Lang:core:core:perenestiizs2}';
$matrix['newPartner']['type'] = 'select';
$matrix['newPartner']['additional'] = Library::array_merge($GLOBALS['Core']->getModulesByType('partner'), array('' => '{Call:Lang:core:core:neperenosit}'));

$matrix['newSupport']['text'] = '{Call:Lang:core:core:perenestiizs3}';
$matrix['newSupport']['type'] = 'select';
$matrix['newSupport']['additional'] = Library::array_merge($GLOBALS['Core']->getModulesByType('ticket'), array('' => '{Call:Lang:core:core:neperenosit}'));

$matrix['path']['text'] = '{Call:Lang:core:core:polnyjputdok}';
$matrix['path']['warn'] = '{Call:Lang:core:core:neukazanputd}';
$matrix['path']['type'] = 'text';

?>