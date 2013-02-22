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


if(!empty($terms)){
	$text = '{Call:Lang:modules:billing:srokzakaza1}';
	include(_W.'modules/billing/forms/term.php');
}

if(!empty($pTerms)){
	include(_W.'modules/billing/forms/prolong_style.php');
}

if($usePromoCode){
	$matrix[$prefix.'promo_code'.$id]['text'] = '{Call:Lang:modules:billing:promokod}';
	$matrix[$prefix.'promo_code'.$id]['type'] = 'text';
}

include(_W.'modules/billing/forms/ident.php');

?>