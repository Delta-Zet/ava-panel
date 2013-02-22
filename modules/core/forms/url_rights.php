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


$matrix['text']['text'] = 'Имя';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = 'Не указано имя';

$matrix['name']['text'] = 'Идентификатор';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Не указан идентификатор';
if(!empty($modify)) $matrix['name']['disabled'] = true;

$matrix['type']['text'] = 'Тип';
$matrix['type']['type'] = 'select';
$matrix['type']['warn'] = 'Не указан тип';
$matrix['type']['additional_style'] = ' onChange="setTypeBlk();"';
$matrix['type']['additional'] = array(
//	'drop' => 'Отмена имени модуля и функции в URL',
//	'replace' => 'Замена имени модуля и функции',
	'dropvars' => 'Исключение имен переменных из URL',
//	'unusetext' => 'Включение неиспользуемых конструкций в URL',
//	'free' => 'Свободная обработка URL',
);

$matrix['mod']['text'] = 'Используемый модуль';
$matrix['mod']['type'] = 'select';
$matrix['mod']['warn'] = 'Не указан используемый модуль';
$matrix['mod']['additional'] = $modules;

$matrix['func']['text'] = 'Используемая функция';
$matrix['func']['warn'] = 'Не указана используемая функция';
$matrix['func']['type'] = 'text';

$matrix['replaceRight']['pre_text'] = '<div id="replaceBlk">';
$matrix['replaceRight']['text'] = 'Паттерн';
$matrix['replaceRight']['comment'] = 'Паттерн при котором обнаруживается принадлежность URL к указанному модулю и функции (Perl-совместимый), в месте где должна находиться основная часть URL (исключая имя модуля и функции) укажите {url}';
$matrix['replaceRight']['type'] = 'text';
$matrix['replaceRight']['post_text'] = '</div>';

$matrix['dropVarsDlm']['pre_text'] = '<div id="dropvarsBlk">';
$matrix['dropVarsDlm']['text'] = 'Разделитель';
$matrix['dropVarsDlm']['comment'] = 'Переменные будут разделены указанным символом';
$matrix['dropVarsDlm']['type'] = 'text';
$values['dropVarsDlm'] = '/';

$matrix['dropVarsList']['text'] = 'Список переменных';
$matrix['dropVarsList']['comment'] = 'Для указанных переменных будут использоваться только значения, имена будут опущены. Указываются в том порядке в ктором будут размещены в URL, каждая с новой строки.';
$matrix['dropVarsList']['type'] = 'textarea';

$matrix['dropVarsLastDlm']['text'] = 'Использовать разделитель в конце строки';
$matrix['dropVarsLastDlm']['type'] = 'checkbox';
$values['dropVarsLastDlm'] = 1;

$matrix['dropVarsDlm2']['text'] = 'Разделитель отделяющий преобразованные переменные';
$matrix['dropVarsDlm2']['comment'] = 'Указанной конструкцией будут отделены преобразованные в соответствии с правилом переменные ото всех остальных';
$matrix['dropVarsDlm2']['type'] = 'text';
$values['dropVarsDlm2'] = ':';

$matrix['dropVarsEmpty']['text'] = 'Заменитель пустых переменных';
$matrix['dropVarsEmpty']['comment'] = 'Если переменная не существует или имеет пустое значение, она будет заменена указанным символом';
$matrix['dropVarsEmpty']['type'] = 'text';
$matrix['dropVarsEmpty']['post_text'] = '</div>';

$matrix['unuseTextPre']['pre_text'] = '<div id="unusetextBlk">';
$matrix['unuseTextPre']['text'] = 'Текст перед URL';
$matrix['unuseTextPre']['comment'] = 'Любой текст. Он будет вставлен после блока модуля и функции, но перед блоком переменных, будет просто исключен при разборе URL, может применяться просто для совместимости с SEO. Текст будет urlencoded.';
$matrix['unuseTextPre']['type'] = 'text';

$matrix['unuseTextPost']['text'] = 'Текст после URL';
$matrix['unuseTextPost']['type'] = 'text';
$matrix['unuseTextPost']['post_text'] = '</div>';

$matrix['evalGetUrl']['pre_text'] = '<div id="freeBlk">';
$matrix['evalGetUrl']['text'] = 'Eval`d код который будет выполнен для получения URL';
$matrix['evalGetUrl']['comment'] = 'Выполняется как undefined функция, должен вернуть URL.';
$matrix['evalGetUrl']['type'] = 'textarea';

$matrix['evalGetVars']['text'] = 'Eval`d код который будет выполнен для разбора URL';
$matrix['evalGetVars']['comment'] = 'Выполняется как undefined функция в которую в качестве единственного параметра передается вызванный URL. Должна вернуть ассоциативный массив параметров.';
$matrix['evalGetVars']['type'] = 'textarea';
$matrix['evalGetVars']['post_text'] = '</div><script type="text/javascript">
	function setTypeBlk(){
		hideFormBlock("replaceBlk");
		hideFormBlock("dropvarsBlk");
		hideFormBlock("unusetextBlk");
		hideFormBlock("freeBlk");
		showFormBlock(ge("type").value + "Blk");
	}
	setTypeBlk();
</script>';

$matrix['show']['text'] = 'Правило используется';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>