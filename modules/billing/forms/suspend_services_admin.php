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


foreach($accs as $i => $e){
	$matrix['capt'.$i]['type'] = 'caption';
	$matrix['capt'.$i]['text'] = $e;

	$matrix['reason'.$i]['text'] = '{Call:Lang:modules:billing:kommentarijo}';
	$matrix['reason'.$i]['type'] = 'textarea';

	$matrix['type'.$i]['text'] = '{Call:Lang:modules:billing:ukazatkakpri}';
	$matrix['type'.$i]['type'] = 'select';
	$matrix['type'.$i]['additional'] = array(
		'' => '{Call:Lang:modules:billing:net}',
		'accord' => '{Call:Lang:modules:billing:dobrovolno}',
		'term' => '{Call:Lang:modules:billing:istecheniesr}',
		'policy' => '{Call:Lang:modules:billing:narushenie}',
		'other' => '{Call:Lang:modules:billing:drugaiaprich}',
	);

	$matrix['suspend'.$i]['type'] = 'checkbox';
	$matrix['suspend'.$i]['text'] = 'Заблокировать аккаунт';
	$matrix['suspend'.$i]['value'] = 1;

	$matrix['auto'.$i]['type'] = 'checkbox';
	$matrix['auto'.$i]['text'] = 'В т.ч. заблокировать на сервере';
	$matrix['auto'.$i]['value'] = 1;

	$matrix['notify'.$i]['type'] = 'checkbox';
	$matrix['notify'.$i]['text'] = '{Call:Lang:modules:billing:uvedomitpolz}';
	$matrix['notify'.$i]['value'] = 1;
}

?>