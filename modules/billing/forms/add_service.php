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


$matrix['dates_capt'.$id]['type'] = 'caption';
$matrix['dates_capt'.$id]['text'] = '{Call:Lang:modules:billing:daty}';
require(_W.'modules/billing/forms/add_service_terms.php');

$matrix['price_capt'.$id]['type'] = 'caption';
$matrix['price_capt'.$id]['text'] = '{Call:Lang:modules:billing:dannyedliara}';
require(_W.'modules/billing/forms/add_service_price.php');

$matrix['acc_create_capt'.$id]['type'] = 'caption';
$matrix['acc_create_capt'.$id]['text'] = '{Call:Lang:modules:billing:dannyedliaso}';

$matrix['acc_server'.$id]['text'] = '{Call:Lang:modules:billing:sozdatsispol}';
$matrix['acc_server'.$id]['type'] = 'select';
$matrix['acc_server'.$id]['additional'] = Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzovat}'), $servers);

$matrix['acc_auto'.$id]['text'] = '{Call:Lang:modules:billing:sozdatavtoma}';
$matrix['acc_auto'.$id]['type'] = 'checkbox';

$matrix['acc_ident'.$id]['text'] = '{Call:Lang:modules:billing:identifitsir}';
$matrix['acc_ident'.$id]['type'] = 'text';
$matrix['acc_ident'.$id]['warn'] = '{Call:Lang:modules:billing:vyneukazalik}';

?>