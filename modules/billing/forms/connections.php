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


$matrix['text']['text'] = '{Call:Lang:modules:billing:imiasoedinen}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:neukazanoimi1}';
$matrix['text']['comment'] = '{Call:Lang:modules:billing:ehtoimiabude}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato1}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';

$matrix['extension']['text'] = '{Call:Lang:modules:billing:vneshniaiapa}';
$matrix['extension']['type'] = 'select';
$matrix['extension']['warn'] = '{Call:Lang:modules:billing:neukazanavne}';
$matrix['extension']['additional'] = $connectionMods;

if(!empty($extra)){
	$matrix['name']['disabled'] = '1';

	$matrix['login']['text'] = '{Call:Lang:modules:billing:logindostupa}';
	$matrix['login']['type'] = 'text';
	$matrix['login']['warn'] = '{Call:Lang:modules:billing:neukazanlogi}';

	$matrix['comment']['text'] = '{Call:Lang:modules:billing:kommentarij}';
	$matrix['comment']['type'] = 'textarea';

	$matrix['pwd']['text'] = '{Call:Lang:modules:billing:paroldostupa}';
	$matrix['pwd']['type'] = 'pwd';
	$matrix['pwd']['warn'] = '{Call:Lang:modules:billing:neukazanparo}';

	$matrix['host']['text'] = '{Call:Lang:modules:billing:urldostupa}';
	$matrix['host']['type'] = 'text';
	$matrix['host']['warn'] = '{Call:Lang:modules:billing:neukazanurld}';
	$matrix['host']['comment'] = '{Call:Lang:modules:billing:urlhttpapipo}';

	$matrix['login_host']['text'] = '{Call:Lang:modules:billing:urldliavkhod}';
	$matrix['login_host']['type'] = 'text';
	$matrix['login_host']['comment'] = '{Call:Lang:modules:billing:ukazhitezdes}';
}

?>