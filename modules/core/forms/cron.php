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


$matrix['command']['text'] = '{Call:Lang:core:core:komanda}';
$matrix['command']['type'] = 'textarea';
$matrix['command']['warn'] = '{Call:Lang:core:core:neukazanakom}';
$matrix['command']['comment'] = '{Call:Lang:core:core:zdesukazyvae}';

$matrix['comment']['text'] = '{Call:Lang:core:core:poiasnenie}';
$matrix['comment']['type'] = 'textarea';
$matrix['comment']['comment'] = '{Call:Lang:core:core:zdesmozhnona}';

$matrix['name']['text'] = '{Call:Lang:core:core:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
$matrix['name']['comment'] = '{Call:Lang:core:core:zdesmozhnona}';
if(!empty($this->values['id'])) $matrix['name']['disabled'] = '1';

$text = '{Call:Lang:core:core:poslednijmom}';
$field = 'limit';
include(_W.'forms/type_calendar2.php');
$matrix[$field]['comment'] = '{Call:Lang:core:core:vukazannyjmo}';

$matrix['month']['text'] = '{Call:Lang:core:core:mesiatsyzapu}';
$matrix['month']['type'] = 'text';
$matrix['month']['warn'] = '{Call:Lang:core:core:neukazanovka}';
$matrix['month']['warn_function'] = 'regExp::cronTime';
$matrix['month']['comment'] = '{Call:Lang:core:core:mozhnoukazat}';
$values['month'] = '*';

$matrix['day']['text'] = '{Call:Lang:core:core:dnimesiatsa}';
$matrix['day']['type'] = 'text';
$matrix['day']['warn'] = '{Call:Lang:core:core:neukazanovka1}';
$matrix['day']['warn_function'] = 'regExp::cronTime';
$values['day'] = '*';

$matrix['week']['text'] = '{Call:Lang:core:core:dninedeli}';
$matrix['week']['type'] = 'text';
$matrix['week']['comment'] = '{Call:Lang:core:core:dninedeliuka}';
$matrix['week']['warn'] = '{Call:Lang:core:core:neukazanovka2}';
$matrix['week']['warn_function'] = 'regExp::cronTime';
$values['week'] = '*';

$matrix['hour']['text'] = '{Call:Lang:core:core:chasy1}';
$matrix['hour']['type'] = 'text';
$matrix['hour']['warn'] = '{Call:Lang:core:core:neukazanovka3}';
$matrix['hour']['warn_function'] = 'regExp::cronTime';
$values['hour'] = '*';

$matrix['minute']['text'] = '{Call:Lang:core:core:minuty1}';
$matrix['minute']['type'] = 'text';
$matrix['minute']['warn'] = '{Call:Lang:core:core:neukazanovka4}';
$matrix['minute']['warn_function'] = 'regExp::cronTime';
$values['minute'] = '*';

$matrix['tick']['text'] = '{Call:Lang:core:core:tikovoeispol}';
$matrix['tick']['type'] = 'checkbox';

$matrix['eval']['text'] = 'Исполнить однократно команду для проверки';
$matrix['eval']['type'] = 'checkbox';
$values['eval'] = 1;

?>