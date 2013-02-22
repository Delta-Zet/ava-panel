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


$matrix[$prefix.'_caption_'.$id]['text'] = $GLOBALS['Core']->getModuleName($prefix);
$matrix[$prefix.'_caption_'.$id]['type'] = 'caption';

if($style == 'portioned-pre' || $style == 'portioned-post'){
	$matrix[$prefix.'_caption_'.$id]['text'] .= '. Начисление партнеру осуществляется порциями';
	$matrix[$prefix.'_caption_'.$id]['type'] = 'caption';

	foreach($opData['vars']['parts'] as $i => $e){
		foreach($e as $i1 => $e1){
			switch($i){
				case 'price': $matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] = 'От первого основного платежа'; break;
				case 'price2': $matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] = 'От последующих основных платежей'; break;
				case 'prolong_price': $matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] = 'От основного платежа'; break;
				case 'modify_price': $matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] = 'От модификации'; break;
				case 'install_price': $matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] = 'От установочной платы'; break;
				case 'modify_install_price': $matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] = 'От установочной платы за модификации'; break;
			}

			$matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['text'] .= ', '.$cur;
			$matrix[$prefix.'_'.$i.'_'.$i1.'_'.$id]['type'] = 'text';
			$values[$prefix.'_'.$i.'_'.$i1.'_'.$id] = $e1['pay'];

			$matrix[$prefix.'_'.$i.'_pay_'.$i1.'_'.$id]['text'] = 'Начислить';
			$matrix[$prefix.'_'.$i.'_pay_'.$i1.'_'.$id]['type'] = 'calendar2';
			$values[$prefix.'_'.$i.'_pay_'.$i1.'_'.$id] = $e1['pay_moment'];

			$matrix[$prefix.'_caption_'.$i.'_'.$i1.'_'.$id]['text'] = '';
			$matrix[$prefix.'_caption_'.$i.'_'.$i1.'_'.$id]['type'] = 'caption';
		}
	}
}
else{
	$matrix[$prefix.'_sum_'.$id]['text'] = 'Отчислить партнеру, '.$cur;
	$matrix[$prefix.'_sum_'.$id]['type'] = 'text';
	$values[$prefix.'_sum_'.$id] = $opData['sum'];
}

?>