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


$matrix['price_capt']['type'] = 'caption';
$matrix['price_capt']['text'] = '{Call:Lang:modules:billing:dannyedliara1}';

if(isset($baseTerm)) $matrix['price']['text'] = '{Call:Lang:modules:billing:raschetnaias5:'.Library::serialize(array(Dates::termsListVars($baseTerm), $currency)).'}';
$matrix['price']['type'] = 'text';

if(isset($baseTerm)) $matrix['modify_price']['text'] = '{Call:Lang:modules:billing:raschetnaias2:'.Library::serialize(array(Dates::termsListVars($baseTerm), $currency)).'}';
$matrix['modify_price']['type'] = 'text';

$matrix['ind_price']['text'] = '{Call:Lang:modules:billing:politikapere1}';
$matrix['ind_price']['type'] = 'select';
$matrix['ind_price']['additional'] = array(
	'0' => '{Call:Lang:modules:billing:nepereschity}',
	'1' => '{Call:Lang:modules:billing:pereschityva}',
	'2' => '{Call:Lang:modules:billing:pereschityva1}',
);


$matrix['create_capt']['type'] = 'caption';
$matrix['create_capt']['text'] = '{Call:Lang:modules:billing:dannyedliaob}';

$matrix['ident']['text'] = '{Call:Lang:modules:billing:identifitsir}';
$matrix['ident']['type'] = 'text';

$matrix['server']['text'] = '{Call:Lang:modules:billing:soedinenie}';
$matrix['server']['type'] = 'select';
if(isset($servers)) $matrix['server']['additional'] = $servers;

$matrix['suspend_reason_descript']['text'] = '{Call:Lang:modules:billing:kommentarijo}';
$matrix['suspend_reason_descript']['type'] = 'textarea';

$matrix['step']['text'] = '{Call:Lang:modules:billing:schitatuslug}';
$matrix['step']['type'] = 'select';
$matrix['step']['additional'] = array(
	'1' => '{Call:Lang:modules:billing:rabotaiushch}',
	'0' => '{Call:Lang:modules:billing:zablokirovan}',
	'-1' => '{Call:Lang:modules:billing:udalennoj}'
);

$matrix['suspend_reason']['text'] = '{Call:Lang:modules:billing:ukazatkakpri}';
$matrix['suspend_reason']['type'] = 'select';
$matrix['suspend_reason']['additional'] = array(
	'' => '{Call:Lang:modules:billing:net}',
	'accord' => '{Call:Lang:modules:billing:dobrovolno}',
	'term' => '{Call:Lang:modules:billing:istecheniesr}',
	'policy' => '{Call:Lang:modules:billing:narushenie}',
	'other' => '{Call:Lang:modules:billing:drugaiaprich}',
);

$matrix['auto_prolong']['text'] = 'Автоматически продлять на';
$matrix['auto_prolong']['type'] = 'select';
if(isset($pTerms)) $matrix['auto_prolong']['additional'] = $pTerms;

$matrix['auto_prolong_fract']['text'] = 'Разрешить дробить срок при автопродлении при недостатке средств';
$matrix['auto_prolong_fract']['type'] = 'checkbox';

?>