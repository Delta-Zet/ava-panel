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


$matrix['partnerClickDefaultSum']['text'] = 'Стоимость клика по умолчанию';
$matrix['partnerClickDefaultSum']['type'] = 'text';
$matrix['partnerClickDefaultSum']['warn_function'] = 'regExp::float';

if($banners){
	foreach($banners as $bannerId => $e){
		$matrix['capt_'.$bannerId]['text'] = '{Call:Lang:modules:partner:banner:'.Library::serialize(array($e)).'}';
		$matrix['capt_'.$bannerId]['type'] = 'caption';
		require(_W.'modules/partner/forms/banner_price.php');
	}
}

?>