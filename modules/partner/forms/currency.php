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


$matrix['name']['text'] = '{Call:Lang:modules:partner:identifikato1}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:partner:neukazaniden}';
$matrix['name']['warn_pattern'] = '^[A-Za-z]{1,3}$';
$matrix['name']['comment'] = '{Call:Lang:modules:partner:vnimanieiden}';

$matrix['text']['text'] = '{Call:Lang:modules:partner:nazvanievali}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:partner:neukazanonaz}';

$matrix['exchange']['text'] = '{Call:Lang:modules:partner:kursk:'.Library::serialize(array($currency)).'}';
$matrix['exchange']['comment'] = '{Call:Lang:modules:partner:zdessleduetu:'.Library::serialize(array($currency)).'}';
$matrix['exchange']['type'] = 'text';
$matrix['exchange']['warn'] = '{Call:Lang:modules:partner:neukazankurs}';

foreach($billMods as $i => $e){
	$matrix['exchange_'.$i]['text'] = '{Call:Lang:modules:partner:kursk1:'.Library::serialize(array($e, $GLOBALS['Core']->callModule($i)->getMainCurrencyName())).'}';
	$matrix['exchange_'.$i]['type'] = 'text';
	$matrix['exchange_'.$i]['warn'] = '{Call:Lang:modules:partner:neukazankurs1:'.Library::serialize(array($e)).'}';
}

$matrix['sort']['text'] = '{Call:Lang:modules:partner:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

if(!empty($extra)){
	$matrix['name']['disabled'] = '1';
}

?>