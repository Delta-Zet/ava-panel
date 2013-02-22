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


$matrix['price_sms_caption']['text'] = 'Оплата СМС';
$matrix['price_sms_caption']['type'] = 'caption';

foreach($smsPays as $i => $e){
	if(!empty($smsNumbers[$i])){
		$matrix['price_sms_'.$i]['text'] = 'Номер для оплаты через SMS "'.$e.'"';
		$matrix['price_sms_'.$i]['type'] = 'select';
		$matrix['price_sms_'.$i]['additional'] = Library::array_merge(array('' => 'Не использовать'), $smsNumbers[$i]);
	}
}

$matrix['price_caption']['text'] = 'Общие способы оплаты';
$matrix['price_caption']['type'] = 'caption';

foreach($pays as $i => $e){
	$matrix['price_'.$i]['text'] = 'Стоимость пакета при оплате "'.$e['text'].'", '.$currency[$e['currency']];
	$matrix['price_'.$i]['type'] = 'text';
	$matrix['price_'.$i]['comment'] = 'Если оставить поле пустым, оплата этим способом будет невозможна.';
	$matrix['price_'.$i]['warn_function'] = 'regExp::float';
}

?>