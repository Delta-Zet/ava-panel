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


$matrix['number']['text'] = 'Короткий номер';
$matrix['number']['type'] = 'text';
$matrix['number']['warn'] = 'Вы не указали номер';
$matrix['number']['warn_pattern'] = '^\d{3,7}$';

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['sum']['text'] = 'Сумма зачисляемая при отправке СМС.';
$matrix['sum']['comment'] = 'Если оставить поле пустым сумма будет определяться автоматически.';
$matrix['sum']['type'] = 'text';
$matrix['sum']['warn_function'] = 'regExp::float';

$matrix['currency']['text'] = 'Валюта';
$matrix['currency']['type'] = 'select';
$matrix['currency']['additional'] = $currency;

$matrix['comment']['text'] = 'Комментарий для платежа';
$matrix['comment']['comment'] = '[nocall]Здесь вы можете написать текст который будет использован при выводе предложения отправить смс. В тескте можно использовать последовательности {num} и {msg} означающие соответственно короткий номер для отправки смс и текст смс.[/nocall]';
$matrix['comment']['type'] = 'textarea';

$matrix['show']['text'] = 'Номер доступен';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>