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


$matrix['currency']['text'] = '{Call:Lang:modules:billing:valiutavkoto}';
$matrix['currency']['comment'] = '{Call:Lang:modules:billing:kazhdyjtarif}';
$matrix['currency']['type'] = 'select';
$matrix['currency']['additional'] = $currency;

if($type != 'onetime'){
	$matrix['price']['text'] = '{Call:Lang:modules:billing:tsenazapervy:'.Library::serialize(array(Dates::termsListVars($baseTerm, 'za'))).'}';
	$matrix['price']['type'] = 'text';
	$matrix['price']['warn'] = '{Call:Lang:modules:billing:vyneukazalit1}';
	$matrix['price']['warn_function'] = 'regExp::float';

	$matrix['price2']['text'] = 'Последующие '.Dates::termsListVars($baseTerm, 'multi').' первого заказа:';
	$matrix['price2']['type'] = 'text';
	$matrix['price2']['warn'] = 'Вы не указали цену за последующие периоды первого заказа';
	$matrix['price2']['warn_function'] = 'regExp::float';

	$matrix['prolong_price']['text'] = '{Call:Lang:modules:billing:tsenaprodlen:'.Library::serialize(array(Dates::termsListVars($baseTerm, 'za'))).'}';
	$matrix['prolong_price']['type'] = 'text';
	$matrix['prolong_price']['warn'] = '{Call:Lang:modules:billing:vyneukazalit2}';
	$matrix['prolong_price']['warn_function'] = 'regExp::float';
}

$matrix['install_price']['text'] = '{Call:Lang:modules:billing:tsenaustanov2}';
$matrix['install_price']['type'] = 'text';
$matrix['install_price']['warn_function'] = 'regExp::float';

if($type != 'onetime'){
	$matrix['change_down_price']['text'] = '{Call:Lang:modules:billing:tsenasmenyta}';
	$matrix['change_down_price']['type'] = 'text';
	$matrix['change_down_price']['warn_function'] = 'regExp::float';

	$matrix['change_up_price']['text'] = '{Call:Lang:modules:billing:tsenasmenyta1}';
	$matrix['change_up_price']['type'] = 'text';
	$matrix['change_up_price']['warn_function'] = 'regExp::float';

	$matrix['change_grp_price']['text'] = '{Call:Lang:modules:billing:tsenasmenyta2}';
	$matrix['change_grp_price']['type'] = 'text';
	$matrix['change_grp_price']['warn_function'] = 'regExp::float';

	$matrix['change_srv_price']['text'] = '{Call:Lang:modules:billing:tsenasmenyta3}';
	$matrix['change_srv_price']['type'] = 'text';
	$matrix['change_srv_price']['warn_function'] = 'regExp::float';

	$matrix['change_modify_price']['text'] = '{Call:Lang:modules:billing:tsenamodifik}';
	$matrix['change_modify_price']['type'] = 'text';
	$matrix['change_modify_price']['warn_function'] = 'regExp::float';

	$matrix['del_price']['text'] = '{Call:Lang:modules:billing:shtrafpridos}';
	$matrix['del_price']['type'] = 'text';
	$matrix['del_price']['warn_function'] = 'regExp::float';

	$matrix['pause_start_price']['text'] = '{Call:Lang:modules:billing:stoimostusta1}';
	$matrix['pause_start_price']['type'] = 'text';
	$matrix['pause_start_price']['warn_function'] = 'regExp::float';

	$matrix['pause_stop_price']['text'] = '{Call:Lang:modules:billing:stoimostsnia}';
	$matrix['pause_stop_price']['type'] = 'text';
	$matrix['pause_stop_price']['warn_function'] = 'regExp::float';

	$matrix['pay_test_install']['text'] = 'Взымать плату при установке тестового аккаунта';
	$matrix['pay_test_install']['type'] = 'checkbox';

	$matrix['pay_test_modify']['text'] = 'Взымать плату при смене тарифа в рамках теста';
	$matrix['pay_test_modify']['type'] = 'checkbox';
}

?>