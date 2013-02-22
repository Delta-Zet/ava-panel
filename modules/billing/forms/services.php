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


$matrix['text']['text'] = '{Call:Lang:modules:billing:nazvanieuslu}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:neukazanonaz3}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato3}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazaniden1}';
$matrix['name']['warn_pattern'] = '^[A-Za-z][\w\-\.]+$';
$matrix['name']['comment'] = '{Call:Lang:modules:billing:identifikato10}';

$matrix['type']['text'] = '{Call:Lang:modules:billing:tipuslugi}';
$matrix['type']['type'] = 'select';
$matrix['type']['warn'] = '{Call:Lang:modules:billing:neukazantipu}';
$matrix['type']['additional'] = array('prolonged' => '{Call:Lang:modules:billing:prodliaemaia}', 'onetime' => '{Call:Lang:modules:billing:razovaia}');

$matrix['extension']['text'] = '{Call:Lang:modules:billing:modulrasshir}';
$matrix['extension']['type'] = 'select';
$matrix['extension']['comment'] = '{Call:Lang:modules:billing:dlianekotory}';
$matrix['extension']['additional'] = $extensions;

if(!empty($extra)){
	$matrix['name']['disabled'] = 1;
	$matrix['extension']['disabled'] = 1;
	$matrix['type']['disabled'] = 1;

	if($type != 'onetime'){
		$matrix['modify_install_type']['text'] = 'Способ расчета установочной стоимости при смене тарифа';
		$matrix['modify_install_type']['type'] = 'select';
		$matrix['modify_install_type']['additional'] = array(
			'full' => 'Пользователь платит полную установочную стоимость',
			'new' => 'Пользователь платит полную установочную стоимость новых параметров',
			'difference' => 'Пользователь оплачивает разницу в стоимости установки если стоимость установки нового тарифа больше',
			'' => 'При модификации установочная стоимость не взымается',
		);

		$matrix['modify_type']['text'] = '{Call:Lang:modules:billing:sposobperesc}';
		$matrix['modify_type']['type'] = 'select';
		$matrix['modify_type']['warn'] = '{Call:Lang:modules:billing:neukazanspos1}';
		$matrix['modify_type']['additional'] = array(
			'paidto' => '{Call:Lang:modules:billing:izmeniaetsia}',
			'paidtobyday' => 'Изменяется срок с точностью до суток, остаток возвращается на баланс',
			'balance' => '{Call:Lang:modules:billing:nedostatoksr}',
		);

		$matrix['modify_type2']['text'] = 'При пересчете срока дополнительные платежи';
		$matrix['modify_type2']['type'] = 'select';
		$matrix['modify_type2']['additional'] = array(
			'' => 'Включать в расчет нового тарифа и получать конечный срок с их учетом',
			'1' => 'Пропускать отдельной графой с требованием оплатить'
		);

		$matrix['modify_minus']['text'] = 'Если остаточный срок имеет отрицательное или нулевое значение';
		$matrix['modify_minus']['type'] = 'select';
		$matrix['modify_minus']['additional'] = array(
			'' => 'Пересчет производится как и с положительным остаточным сроком',
			'none' => 'Пересчет не производится, доплата за разницу срока не взымается',
			'disable' => 'Смена услуги с отрицательным остаточным сроком запрещена'
		);

		$matrix['modify_test_type']['text'] = 'При смене тарифа в режиме теста';
		$matrix['modify_test_type']['type'] = 'select';
		$matrix['modify_test_type']['additional'] = array(
			'' => 'Всегда используется старый срок',
			'new' => 'Всегда используется новый срок',
			'bigger' => 'Используется тот срок который больше',
			'smaller' => 'Используется тот срок который меньше',
			'normal' => 'Пересчитывать пропорционально стоимости',
			'down' => 'Пересчитывать пропорционально стоимости только в сторону уменьшения',
			'up' => 'Пересчитывать пропорционально стоимости только в сторону увеличения',
		);

		$matrix['modify_discount_type']['text'] = 'При смене тарифа проданного со скидкой';
		$matrix['modify_discount_type']['type'] = 'select';
		$matrix['modify_discount_type']['additional'] = array(
			'' => 'К новому заказу применяются те же скидки которые применялись к старому',
			'1' => 'Применяются те скидки которые должны были бы применяться к нему при заказе на тот же срок',
			'2' => 'Применяются те скидки которые должны были бы применяться к нему при заказе на оставшийся срок',
			'3' => 'Применяются те скидки которые должны были бы применяться к нему при заказе на пропорционально пересчитанный срок',
			'4' => 'Применяются те скидки которые должны были бы применяться к нему при заказе на пропорционально пересчитанный оставшийся срок',
			'5' => 'К новому заказу не применяется никаких скидок'
		);

		$matrix['modify_price_type']['text'] = 'При смене тарифа в расчете старого тарифа';
		$matrix['modify_price_type']['type'] = 'select';
		$matrix['modify_price_type']['additional'] = array(
			'' => 'Руководствоваться текущей ценой',
			'old' => 'Руководствоваться ценой на момент продажи',
			'bigger' => 'Руководствоваться той ценой которая больше',
			'smaller' => 'Руководствоваться той ценой которая меньше'
		);

		$matrix['modify_price_type_discount']['text'] = 'При смене тарифа в расчете старого тарифа';
		$matrix['modify_price_type_discount']['type'] = 'select';
		$matrix['modify_price_type_discount']['additional'] = array(
			'' => 'Руководствоваться текущей скидкой',
			'old' => 'Руководствоваться скидкой на момент продажи',
			'bigger' => 'Руководствоваться той скидкой которая больше',
			'smaller' => 'Руководствоваться той скидкой которая меньше'
		);

		$matrix['invoice_type']['text'] = '{Call:Lang:modules:billing:sposobotrazh}';
		$matrix['invoice_type']['type'] = 'select';
		$matrix['invoice_type']['warn'] = '{Call:Lang:modules:billing:neukazanspos2}';
		$matrix['invoice_type']['comment'] = '{Call:Lang:modules:billing:eslidokument}';
		$matrix['invoice_type']['additional'] = array('oneinmonth' => '{Call:Lang:modules:billing:dokumentyvys}', 'immediate' => '{Call:Lang:modules:billing:dokumentyvys1}');

		$matrix['base_term']['text'] = '{Call:Lang:modules:billing:bazovyjsrokd}';
		$matrix['base_term']['type'] = 'select';
		$matrix['base_term']['warn'] = '{Call:Lang:modules:billing:neukazanbazo}';
		$matrix['base_term']['additional'] = Dates::termsList();

		$matrix['test_term']['text'] = '{Call:Lang:modules:billing:bazovyjsrokd1}';
		$matrix['test_term']['type'] = 'select';
		$matrix['test_term']['warn'] = '{Call:Lang:modules:billing:neukazanbazo}';
		$matrix['test_term']['additional'] = Dates::termsList();

		$matrix['max_test_accs']['text'] = 'Максимальное число тестовых аккаунтов';
		$matrix['max_test_accs']['warn_function'] = 'regExp::digit';
		$matrix['max_test_accs']['type'] = 'text';
	}

	$matrix['pkg_table_mode']['text'] = '{Call:Lang:modules:billing:spiskitarifo}';
	$matrix['pkg_table_mode']['type'] = 'select';
	$matrix['pkg_table_mode']['additional'] = array('h' => '{Call:Lang:modules:billing:gorizontalno}', 'v' => '{Call:Lang:modules:billing:vertikalno}');

	$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
	$matrix['sort']['type'] = 'text';

	$matrix['hide_if_none']['text'] = '{Call:Lang:modules:billing:skryvatparam}';
	$matrix['hide_if_none']['type'] = 'checkbox';
	$matrix['hide_if_none']['comment'] = '{Call:Lang:modules:billing:pripostroeni}';

	$matrix['compact_if_alike']['text'] = '{Call:Lang:modules:billing:obediniatpar}';
	$matrix['compact_if_alike']['type'] = 'checkbox';
	$matrix['compact_if_alike']['comment'] = '{Call:Lang:modules:billing:pripostroeni1}';

	$matrix['show']['text'] = '{Call:Lang:modules:billing:uslugadostup}';
	$matrix['show']['type'] = 'checkbox';
}

?>