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


if(!isset($mailTemplates)) $mailTemplates = array();
if(!isset($admins)) $admins = array();

$matrix['notify_settings_type']['template'] = 'width100';
$matrix['notify_settings_type']['type'] = 'radio';
$matrix['notify_settings_type']['additional'] = array(
	'useMain' => '{Call:Lang:modules:billing:ispolzovatob}',
	'usePersonal' => '{Call:Lang:modules:billing:ispolzovatin}',
);

$matrix['capt_notify_new']['text'] = '{Call:Lang:modules:billing:uvedomoenieo}';
$matrix['capt_notify_new']['type'] = 'caption';

$matrix['mail_tmpl_new']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_new']['type'] = 'select';
$matrix['mail_tmpl_new']['additional'] = $mailTemplates;

$matrix['mail_tmpl_admin_new']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_admin_new']['type'] = 'select';
$matrix['mail_tmpl_admin_new']['additional'] = $mailTemplates;

$matrix['mail_tmpl_admin_new_fail']['text'] = 'Шаблон письма администратору о неудаче';
$matrix['mail_tmpl_admin_new_fail']['type'] = 'select';
$matrix['mail_tmpl_admin_new_fail']['additional'] = $mailTemplates;

$matrix['new_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['new_rcpt_admin']['type'] = 'select';
$matrix['new_rcpt_admin']['additional'] = $admins;

$matrix['notify_new']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_new']['type'] = 'checkbox';

$matrix['notify_admin_new']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_new']['type'] = 'checkbox';

$matrix['notify_fail_admin_new']['text'] = 'Уведомлять администратора если в процессе возникли проблемы';
$matrix['notify_fail_admin_new']['type'] = 'checkbox';

$matrix['capt_notify_modify']['text'] = 'Уведомление о изменении услуги';
$matrix['capt_notify_modify']['type'] = 'caption';

$matrix['mail_tmpl_modify']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_modify']['type'] = 'select';
$matrix['mail_tmpl_modify']['additional'] = $mailTemplates;

$matrix['mail_tmpl_modify_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_modify_admin']['type'] = 'select';
$matrix['mail_tmpl_modify_admin']['additional'] = $mailTemplates;

$matrix['mail_tmpl_modify_admin_fail']['text'] = 'Шаблон письма администратору о неудаче';
$matrix['mail_tmpl_modify_admin_fail']['type'] = 'select';
$matrix['mail_tmpl_modify_admin_fail']['additional'] = $mailTemplates;

$matrix['modify_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['modify_rcpt_admin']['type'] = 'select';
$matrix['modify_rcpt_admin']['additional'] = $admins;

$matrix['notify_modify']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_modify']['type'] = 'checkbox';

$matrix['notify_admin_modify']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_modify']['type'] = 'checkbox';

$matrix['notify_fail_admin_modify']['text'] = 'Уведомлять администратора если в процессе возникли проблемы';
$matrix['notify_fail_admin_modify']['type'] = 'checkbox';

$matrix['capt_notify_prolong']['text'] = 'Уведомление о продлении услуги';
$matrix['capt_notify_prolong']['type'] = 'caption';

$matrix['mail_tmpl_prolong']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_prolong']['type'] = 'select';
$matrix['mail_tmpl_prolong']['additional'] = $mailTemplates;

$matrix['mail_tmpl_prolong_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_prolong_admin']['type'] = 'select';
$matrix['mail_tmpl_prolong_admin']['additional'] = $mailTemplates;

$matrix['mail_tmpl_prolong_admin_fail']['text'] = 'Шаблон письма администратору о неудаче';
$matrix['mail_tmpl_prolong_admin_fail']['type'] = 'select';
$matrix['mail_tmpl_prolong_admin_fail']['additional'] = $mailTemplates;

$matrix['prolong_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['prolong_rcpt_admin']['type'] = 'select';
$matrix['prolong_rcpt_admin']['additional'] = $admins;

$matrix['notify_prolong']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_prolong']['type'] = 'checkbox';

$matrix['notify_admin_prolong']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_prolong']['type'] = 'checkbox';

$matrix['notify_fail_admin_prolong']['text'] = 'Уведомлять администратора если в процессе возникли проблемы';
$matrix['notify_fail_admin_prolong']['type'] = 'checkbox';

$matrix['capt_notify_transmit']['text'] = 'Уведомление о передаче услуги в другой аккаунт';
$matrix['capt_notify_transmit']['type'] = 'caption';

$matrix['mail_tmpl_transmit']['text'] = 'Шаблон письма старого хозяина услуги';
$matrix['mail_tmpl_transmit']['type'] = 'select';
$matrix['mail_tmpl_transmit']['additional'] = $mailTemplates;

$matrix['mail_tmpl_transmit_new_client']['text'] = 'Шаблон письма получателя услуги';
$matrix['mail_tmpl_transmit_new_client']['type'] = 'select';
$matrix['mail_tmpl_transmit_new_client']['additional'] = $mailTemplates;

$matrix['mail_tmpl_transmit_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_transmit_admin']['type'] = 'select';
$matrix['mail_tmpl_transmit_admin']['additional'] = $mailTemplates;

$matrix['transmit_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['transmit_rcpt_admin']['type'] = 'select';
$matrix['transmit_rcpt_admin']['additional'] = $admins;

$matrix['notify_transmit']['text'] = 'Уведомлять старого пользователя';
$matrix['notify_transmit']['type'] = 'checkbox';

$matrix['notify_transmit_new_client']['text'] = 'Уведомлять нового пользователя';
$matrix['notify_transmit_new_client']['type'] = 'checkbox';

$matrix['notify_admin_transmit']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_transmit']['type'] = 'checkbox';

$matrix['capt_notify_term_finish']['text'] = '{Call:Lang:modules:billing:uvedomlenieo}';
$matrix['capt_notify_term_finish']['type'] = 'caption';

$matrix['mail_tmpl_term_finish']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_term_finish']['type'] = 'select';
$matrix['mail_tmpl_term_finish']['additional'] = $mailTemplates;

$matrix['mail_tmpl_term_finish_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_term_finish_admin']['type'] = 'select';
$matrix['mail_tmpl_term_finish_admin']['additional'] = $mailTemplates;

$matrix['term_finish_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['term_finish_rcpt_admin']['type'] = 'select';
$matrix['term_finish_rcpt_admin']['additional'] = $admins;

$matrix['term_finish_notify']['text'] = '{Call:Lang:modules:billing:zaskolkosuto}';
$matrix['term_finish_notify']['comment'] = '{Call:Lang:modules:billing:ukazyvaetsia1}';
$matrix['term_finish_notify']['type'] = 'text';

$matrix['notify_term_finish']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_term_finish']['type'] = 'checkbox';

$matrix['notify_admin_term_finish']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_term_finish']['type'] = 'checkbox';

$matrix['capt_notify_suspend']['text'] = '{Call:Lang:modules:billing:uvedomlenieo1}';
$matrix['capt_notify_suspend']['type'] = 'caption';

$matrix['mail_tmpl_suspend']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_suspend']['type'] = 'select';
$matrix['mail_tmpl_suspend']['additional'] = $mailTemplates;

$matrix['mail_tmpl_suspend_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_suspend_admin']['type'] = 'select';
$matrix['mail_tmpl_suspend_admin']['additional'] = $mailTemplates;

$matrix['suspend_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['suspend_rcpt_admin']['type'] = 'select';
$matrix['suspend_rcpt_admin']['additional'] = $admins;

$matrix['term_finish_suspend']['text'] = '{Call:Lang:modules:billing:cherezskolko}';
$matrix['term_finish_suspend']['comment'] = '{Call:Lang:modules:billing:esliuslugane}';
$matrix['term_finish_suspend']['type'] = 'text';

$matrix['notify_suspend']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_suspend']['type'] = 'checkbox';

$matrix['notify_admin_suspend']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_suspend']['type'] = 'checkbox';

$matrix['capt_notify_unsuspend']['text'] = 'Уведомление о разблокировании услуги';
$matrix['capt_notify_unsuspend']['type'] = 'caption';

$matrix['mail_tmpl_unsuspend']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_unsuspend']['type'] = 'select';
$matrix['mail_tmpl_unsuspend']['additional'] = $mailTemplates;

$matrix['mail_tmpl_unsuspend_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_unsuspend_admin']['type'] = 'select';
$matrix['mail_tmpl_unsuspend_admin']['additional'] = $mailTemplates;

$matrix['unsuspend_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['unsuspend_rcpt_admin']['type'] = 'select';
$matrix['unsuspend_rcpt_admin']['additional'] = $admins;

$matrix['notify_unsuspend']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_unsuspend']['type'] = 'checkbox';

$matrix['notify_admin_unsuspend']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_unsuspend']['type'] = 'checkbox';

$matrix['capt_notify_delete']['text'] = '{Call:Lang:modules:billing:uvedomlenieo2}';
$matrix['capt_notify_delete']['type'] = 'caption';

$matrix['mail_tmpl_delete']['text'] = '{Call:Lang:modules:billing:shablonpisma}';
$matrix['mail_tmpl_delete']['type'] = 'select';
$matrix['mail_tmpl_delete']['additional'] = $mailTemplates;

$matrix['mail_tmpl_delete_admin']['text'] = 'Шаблон письма администратору';
$matrix['mail_tmpl_delete_admin']['type'] = 'select';
$matrix['mail_tmpl_delete_admin']['additional'] = $mailTemplates;

$matrix['delete_rcpt_admin']['text'] = 'Администратор - получатель уведомлений';
$matrix['delete_rcpt_admin']['type'] = 'select';
$matrix['delete_rcpt_admin']['additional'] = $admins;

$matrix['term_finish_del']['text'] = '{Call:Lang:modules:billing:cherezskolko1}';
$matrix['term_finish_del']['comment'] = '{Call:Lang:modules:billing:esliuslugadl}';
$matrix['term_finish_del']['type'] = 'text';

$matrix['notify_delete']['text'] = '{Call:Lang:modules:billing:uvedomliatpo}';
$matrix['notify_delete']['type'] = 'checkbox';

$matrix['notify_admin_delete']['text'] = '{Call:Lang:modules:billing:uvedomliatad}';
$matrix['notify_admin_delete']['type'] = 'checkbox';

?>