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


$matrix['subj']['text'] = '{Call:Lang:core:core:tema}';
$matrix['subj']['type'] = 'text';

if(empty($users)){
	$matrix['eml']['text'] = '{Call:Lang:core:core:emailpolucha}';
	$matrix['eml']['type'] = 'text';
	$matrix['eml']['warn'] = '{Call:Lang:core:core:neukazanemai1}';
}
else $hiddens['users'] = $users;

$matrix['body']['text'] = '{Call:Lang:core:core:telopisma}';
$matrix['body']['type'] = 'textarea';
$matrix['body']['template'] = 'big';

$matrix['extra']['text'] = '{Call:Lang:core:core:shapkaheader}';
$matrix['extra']['type'] = 'textarea';

$matrix['sender']['text'] = '{Call:Lang:core:core:otpravitel}';
$matrix['sender']['type'] = 'text';
$matrix['sender']['warn'] = '{Call:Lang:core:core:neukazanotpr}';
$values['sender'] = $GLOBALS['Core']->Site->params['name'];

$matrix['sender_eml']['text'] = '{Call:Lang:core:core:emailotpravi}';
$matrix['sender_eml']['type'] = 'text';
$matrix['sender_eml']['warn'] = '{Call:Lang:core:core:neukazanemai2}';
$values['sender_eml'] = $GLOBALS['Core']->getParam('defaultEml');

$matrix['format']['text'] = '{Call:Lang:core:core:format}';
$matrix['format']['type'] = 'select';
$matrix['format']['additional'] = array(
	'text/plain' => 'text/plain',
	'text/html' => 'text/html',
	'multipart/form-data' => 'multipart/form-data',
);

$matrix['extra_capt']['text'] = '{Call:Lang:core:core:dopolnitelno}';
$matrix['extra_capt']['type'] = 'caption';

$matrix['notify_success']['text'] = '{Call:Lang:core:core:uvedomitadmi}';
$matrix['notify_success']['type'] = 'checkbox';

$matrix['notify_fail']['text'] = '{Call:Lang:core:core:uvedomitadmi1}';
$matrix['notify_fail']['type'] = 'checkbox';

$matrix['notify_eml']['text'] = '{Call:Lang:core:core:otpravituved}';
$matrix['notify_eml']['type'] = 'text';
$values['notify_eml'] = $GLOBALS['Core']->getUserEml();

$matrix['notify_sender']['text'] = '{Call:Lang:core:core:otpraviteluv}';
$matrix['notify_sender']['type'] = 'text';
$values['notify_sender'] = $GLOBALS['Core']->Site->params['name'];

$matrix['notify_sender_eml']['text'] = '{Call:Lang:core:core:emailotpravi1}';
$matrix['notify_sender_eml']['type'] = 'text';
$values['notify_sender_eml'] = $GLOBALS['Core']->getParam('defaultEml');

?>