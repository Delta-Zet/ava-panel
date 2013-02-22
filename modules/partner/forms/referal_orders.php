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


$first = false;

if($levels){
	$matrix['click_caption']['text'] = '{Call:Lang:modules:partner:zaklikipoban}';
	$matrix['click_caption']['type'] = 'caption';
	if(!$first) $first = 'click_caption';

	for($i = 1; $i <= $levels; $i ++){
		$matrix['click_'.$i]['text'] = '{Call:Lang:modules:partner:otchisleniia:'.Library::serialize(array($i)).'}';
		$matrix['click_'.$i]['type'] = 'text';
	}

	$matrix['view_caption']['text'] = '{Call:Lang:modules:partner:zabaneropoka}';
	$matrix['view_caption']['type'] = 'caption';

	for($i = 1; $i <= $levels; $i ++){
		$matrix['view_'.$i]['text'] = '{Call:Lang:modules:partner:otchisleniia:'.Library::serialize(array($i)).'}';
		$matrix['view_'.$i]['type'] = 'text';
	}

	$matrix['order_caption']['text'] = '{Call:Lang:modules:partner:zazakazy}';
	$matrix['order_caption']['type'] = 'caption';

	for($i = 1; $i <= $levels; $i ++){
		$matrix['order_'.$i]['text'] = '{Call:Lang:modules:partner:otchisleniia:'.Library::serialize(array($i)).'}';
		$matrix['order_'.$i]['type'] = 'text';
		$last = 'order_'.$i;
	}
}

?>