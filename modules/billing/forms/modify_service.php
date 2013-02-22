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
$matrix['dates_capt'.$id]['text'] = 'Даты';

$field = 'modified'.$id;
$text = 'Дата модификации';
require(_W.'forms/type_calendar2.php');

$field = 'new_paid_to'.$id;
$text = 'Услуга будет действовать до';
require(_W.'forms/type_calendar2.php');

$matrix['price_capt'.$id]['type'] = 'caption';
$matrix['price_capt'.$id]['text'] = 'Расценки';

$matrix['new_calculate'.$id]['text'] = 'Новая остаточная расчетная стоимость';
$matrix['new_calculate'.$id]['type'] = 'text';
$matrix['new_calculate'.$id]['warn_function'] = 'regExp::float';

$matrix['new_pay_stay'.$id]['text'] = 'Стоимость остаточного срока';
$matrix['new_pay_stay'.$id]['type'] = 'text';
$matrix['new_pay_stay'.$id]['warn_function'] = 'regExp::float';

$matrix['install_calculate'.$id]['text'] = 'Расчетная стоимость установки';
$matrix['install_calculate'.$id]['type'] = 'text';
$matrix['install_calculate'.$id]['warn_function'] = 'regExp::float';

$matrix['install_price'.$id]['text'] = 'Стоимость установки к списанию';
$matrix['install_price'.$id]['type'] = 'text';
$matrix['install_price'.$id]['warn_function'] = 'regExp::float';

$matrix['change_calculate'.$id]['text'] = 'Расчетная стоимость смены пакета';
$matrix['change_calculate'.$id]['type'] = 'text';
$matrix['change_calculate'.$id]['warn_function'] = 'regExp::float';

$matrix['change_price'.$id]['text'] = 'Стоимость смены пакета к списанию';
$matrix['change_price'.$id]['type'] = 'text';
$matrix['change_price'.$id]['warn_function'] = 'regExp::float';

$matrix['total'.$id]['text'] = 'Всего к списанию';
$matrix['total'.$id]['type'] = 'text';
$matrix['total'.$id]['warn_function'] = 'regExp::float';


$matrix['manage_capt'.$id]['type'] = 'caption';
$matrix['manage_capt'.$id]['text'] = 'Данные для управления новой услугой';

$matrix['base_prolong_price'.$id]['text'] = 'Основная стоимость применяемая для автопродления';
$matrix['base_prolong_price'.$id]['type'] = 'text';

$matrix['base_modify_price'.$id]['text'] = 'Стоимость модификаций для автопродления';
$matrix['base_modify_price'.$id]['type'] = 'text';

$matrix['server'.$id]['text'] = 'Сервер размещения';
$matrix['server'.$id]['type'] = 'select';
$matrix['server'.$id]['additional'] = Library::array_merge(array('' => '{Call:Lang:modules:billing:neispolzovat}'), $servers);

$matrix['auto'.$id]['text'] = 'Модифицировать на сервере автоматически';
$matrix['auto'.$id]['type'] = 'checkbox';
$values['auto'.$id] = true;

?>