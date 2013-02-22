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


if(!empty($modify)){
	$matrix['subj']['text'] = '{Call:Lang:core:core:tema}';
	$matrix['subj']['type'] = 'text';
	$matrix['subj']['warn'] = '{Call:Lang:core:core:vyneukazalit}';
}

$matrix['text']['text'] = '{Call:Lang:core:core:nazvanieshab}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:core:core:vyneukazalin}';

if(!empty($modify)){
	$matrix['body']['text'] = '{Call:Lang:core:core:tekstpisma}';
	$matrix['body']['type'] = 'textarea';
	$matrix['body']['additional_style'] = 'style="height: 300px;"';
}

$matrix['name']['text'] = '{Call:Lang:core:core:identifikato1}';
$matrix['name']['type'] = 'text';
$matrix['name']['comment'] = '{Call:Lang:core:core:ehtotekhnich}';
$matrix['name']['warn'] = '{Call:Lang:core:core:vydolzhnyuka1}';
$matrix['name']['warn_function'] = 'regExp::Ident';

$matrix['mod']['text'] = '{Call:Lang:core:core:modulispolzu}';
$matrix['mod']['type'] = 'select';
$matrix['mod']['warn'] = '{Call:Lang:core:core:vydolzhnyuka4}';
$matrix['mod']['additional'] = $modules;

if(!empty($modify)){
	$matrix['name']['disabled'] = true;

	$matrix['format']['text'] = '{Call:Lang:core:core:formatpisma}';
	$matrix['format']['type'] = 'select';
	$matrix['format']['warn'] = '{Call:Lang:core:core:vyneukazalif}';
	$matrix['format']['additional'] = array(
		'text/html' => 'HTML',
		'text/plain' => '{Call:Lang:core:core:obychnyjteks}',
		'multipart/related' => '{Call:Lang:core:core:priattachenn}'
	);

	$matrix['sender_eml']['text'] = '{Call:Lang:core:core:emailotpravi2}';
	$matrix['sender_eml']['type'] = 'text';
	$matrix['sender_eml']['warn'] = '{Call:Lang:core:core:vyneukazalie}';

	$matrix['sender']['text'] = '{Call:Lang:core:core:imiaotpravit}';
	$matrix['sender']['type'] = 'text';

	$matrix['notify_caption']['text'] = '{Call:Lang:core:core:dannyedliauv}';
	$matrix['notify_caption']['type'] = 'caption';

	$matrix['notify_success_body']['text'] = '{Call:Lang:core:core:tekstuvedoml1}';
	$matrix['notify_success_body']['type'] = 'textarea';

	$matrix['notify_success']['text'] = '{Call:Lang:core:core:uvedomliatad}';
	$matrix['notify_success']['type'] = 'checkbox';

	$matrix['notify_success_subj']['text'] = '{Call:Lang:core:core:temauvedomle}';
	$matrix['notify_success_subj']['type'] = 'text';

	$matrix['notify_fail']['text'] = '{Call:Lang:core:core:uvedomliatad1}';
	$matrix['notify_fail']['type'] = 'select';
	$matrix['notify_fail']['additional'] = array(
		'0' => '{Call:Lang:core:core:neuvedomliat}',
		'1' => '{Call:Lang:core:core:uvedomliatpr}',
		'2' => '{Call:Lang:core:core:uvedomliatto}',
	);

	$matrix['notify_fail_subj']['text'] = '{Call:Lang:core:core:temauvedomle1}';
	$matrix['notify_fail_subj']['type'] = 'text';

	$matrix['notify_fail_body']['text'] = '{Call:Lang:core:core:tekstuvedoml2}';
	$matrix['notify_fail_body']['type'] = 'textarea';

	$matrix['notify_eml']['text'] = '{Call:Lang:core:core:emaildlianap}';
	$matrix['notify_eml']['type'] = 'text';

	$matrix['notify_sender_eml']['text'] = '{Call:Lang:core:core:emailotpravi3}';
	$matrix['notify_sender_eml']['type'] = 'text';
	$matrix['notify_sender_eml']['comment'] = '{Call:Lang:core:core:ehtotemailbu}';

	$matrix['notify_sender']['text'] = '{Call:Lang:core:core:imiaotpravit1}';
	$matrix['notify_sender']['type'] = 'text';
	$matrix['notify_sender']['comment'] = '{Call:Lang:core:core:ehtoimiabude}';
}

?>