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

if($owners){
	$matrix[$prefix.'domain_owner'.$id]['text'] = '{Call:Lang:modules:bill_domains:vladeletsdom}';
	$matrix[$prefix.'domain_owner'.$id]['type'] = 'select';
	$matrix[$prefix.'domain_owner'.$id]['additional'] = $owners;

	if(empty($pkg) || ($pkg != 'ru' && $pkg != 'su' && $pkg != 'рф')){
		$matrix[$prefix.'domain_owner_a'.$id]['text'] = '{Call:Lang:modules:bill_domains:administrati}';
		$matrix[$prefix.'domain_owner_a'.$id]['type'] = 'select';
		$matrix[$prefix.'domain_owner_a'.$id]['additional'] = $owners;

		$matrix[$prefix.'domain_owner_b'.$id]['text'] = '{Call:Lang:modules:bill_domains:billingovyjk}';
		$matrix[$prefix.'domain_owner_b'.$id]['type'] = 'select';
		$matrix[$prefix.'domain_owner_b'.$id]['additional'] = $owners;

		$matrix[$prefix.'domain_owner_t'.$id]['text'] = '{Call:Lang:modules:bill_domains:tekhnicheski}';
		$matrix[$prefix.'domain_owner_t'.$id]['type'] = 'select';
		$matrix[$prefix.'domain_owner_t'.$id]['additional'] = $owners;
	}
}

?>