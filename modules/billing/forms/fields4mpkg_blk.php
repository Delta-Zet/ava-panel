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


if($type == 'text'){
	if($sType != 'onetime'){
		$matrix[$prefix.'mpkg_price'.$postfix]['text'] = '{Call:Lang:modules:billing:tsenazaedeni}';
		$matrix[$prefix.'mpkg_price'.$postfix]['type'] = 'text';

		$matrix[$prefix.'mpkg_price_unlimit'.$postfix]['text'] = '{Call:Lang:modules:billing:tsenazabezli}';
		$matrix[$prefix.'mpkg_price_unlimit'.$postfix]['comment'] = '{Call:Lang:modules:billing:esliostavite}';
		$matrix[$prefix.'mpkg_price_unlimit'.$postfix]['type'] = 'text';
	}

	$matrix[$prefix.'mpkg_price_install'.$postfix]['text'] = '{Call:Lang:modules:billing:tsenaustanov}';
	$matrix[$prefix.'mpkg_price_install'.$postfix]['type'] = 'text';

	$matrix[$prefix.'mpkg_price_install_unlimit'.$postfix]['text'] = '{Call:Lang:modules:billing:tsenaustanov1}';
	$matrix[$prefix.'mpkg_price_install_unlimit'.$postfix]['type'] = 'text';

	$matrix[$prefix.'mpkg_min'.$postfix]['text'] = '{Call:Lang:modules:billing:minimalnodop}';
	$matrix[$prefix.'mpkg_min'.$postfix]['type'] = 'text';
	$matrix[$prefix.'mpkg_min'.$postfix]['comment'] = '{Call:Lang:modules:billing:budetprovere}';

	$matrix[$prefix.'mpkg_max'.$postfix]['text'] = '{Call:Lang:modules:billing:maksimalnodo}';
	$matrix[$prefix.'mpkg_max'.$postfix]['type'] = 'text';
	$matrix[$prefix.'mpkg_max'.$postfix]['comment'] = '{Call:Lang:modules:billing:budetprovere1}';
}
elseif($type == 'select' || $type == 'radio'){
	if($sType != 'onetime'){
		$matrix[$prefix.'mpkg_price'.$postfix]['text'] = '{Call:Lang:modules:billing:tsena}';
		$matrix[$prefix.'mpkg_price'.$postfix]['type'] = 'textarea';
		$matrix[$prefix.'mpkg_price'.$postfix]['comment'] = '{Call:Lang:modules:billing:ukazyvaetsia}';
	}

	$matrix[$prefix.'mpkg_price_install'.$postfix]['text'] = '{Call:Lang:modules:billing:tsenaustanov2}';
	$matrix[$prefix.'mpkg_price_install'.$postfix]['type'] = 'textarea';
	$matrix[$prefix.'mpkg_price_install'.$postfix]['comment'] = '{Call:Lang:modules:billing:ukazyvaetsia}';

	$matrix[$prefix.'mpkg_min'.$postfix]['text'] = '{Call:Lang:modules:billing:poledolzhnob}';
	$matrix[$prefix.'mpkg_min'.$postfix]['type'] = 'checkbox';
	$matrix[$prefix.'mpkg_min'.$postfix]['comment'] = '{Call:Lang:modules:billing:budetprovere2}';
}
else{
	if($sType != 'onetime'){
		$matrix[$prefix.'mpkg_price'.$postfix]['text'] = '{Call:Lang:modules:billing:tsena}';
		$matrix[$prefix.'mpkg_price'.$postfix]['type'] = 'text';
	}

	$matrix[$prefix.'mpkg_price_install'.$postfix]['text'] = '{Call:Lang:modules:billing:tsenaustanov2}';
	$matrix[$prefix.'mpkg_price_install'.$postfix]['type'] = 'text';

	$matrix[$prefix.'mpkg_min'.$postfix]['text'] = '{Call:Lang:modules:billing:poledolzhnob}';
	$matrix[$prefix.'mpkg_min'.$postfix]['type'] = 'checkbox';
	$matrix[$prefix.'mpkg_min'.$postfix]['comment'] = '{Call:Lang:modules:billing:budetprovere2}';
}

$matrix[$prefix.'mpkg_sort'.$postfix]['text'] = '{Call:Lang:modules:billing:parametrsort}';
$matrix[$prefix.'mpkg_sort'.$postfix]['type'] = 'text';

$matrix[$prefix.'mpkg_value'.$postfix]['text'] = '{Call:Lang:modules:billing:znacheniepou}';
$matrix[$prefix.'mpkg_value'.$postfix]['type'] = 'text';
$matrix[$prefix.'mpkg_value'.$postfix]['comment'] = '{Call:Lang:modules:billing:vformerasche}';

if($type == 'text'){
	$matrix[$prefix.'mpkg_unlimit'.$postfix]['text'] = '{Call:Lang:modules:billing:dopuskaetsia}';
	$matrix[$prefix.'mpkg_unlimit'.$postfix]['type'] = 'checkbox';
}
elseif($type == 'checkbox'){
	$matrix[$prefix.'mpkg_value'.$postfix]['text'] = '{Call:Lang:modules:billing:poumolchanii}';
	$matrix[$prefix.'mpkg_value'.$postfix]['type'] = 'checkbox';
}

$matrix[$prefix.'mpkg_hidden'.$postfix]['text'] = '{Call:Lang:modules:billing:priznakiavli}';
$matrix[$prefix.'mpkg_hidden'.$postfix]['type'] = 'checkbox';

?>