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



/*
  Глобальный инсталлятор
	На 1 шаге заставляет юзера согласиться с лицензией
	На 2 шаге предлагает указать параметры сайта, данные DB, данные локального FTP и пр.
	После приема всех этих данных:
		1. Создает файл настроек
		2. Создает все таблицы ядра
		3. Прописует все инсталлируемые модули
		4. Последовательно вызывает инсталляторы для всех инсталлируемых модулей, создавая активные копии
		5. Последовательно вызывает инсталляторы для всех инсталлируемых шаблонов
		6. Устанавливает все языки
		7. Удаляет саму сибя
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);
if(file_exists('../install_complete.php')) die('Script already installed. For new installation delete install_complete.php file.');

if(class_exists('Crypt')) exit;
define('IN_INSTALLATOR', '1');
define('IN_ADMIN', '1');

ob_start();
if(!ini_get('safe_mode')) set_time_limit(3600);

require_once('settings.php');
require_once(_W.'core/core.php');
require_once(_W.'install/install.php');

$_GET['mod'] = 'install';
if(empty($_GET['func'])) $_GET['func'] = 'step1';

$Core = new Core('admin');
$Core->sessStart();
$Core->callMainModule($Core->getGPCArr('callData'));

$template = Files::read('template.tmpl');
echo $GLOBALS['Core']->prepareFinalTmplText($GLOBALS['Core']->replace($template, $Core->getMainModObj(), $Core->getMainModObj()->getContent()));

header('Content-type: text/html; charset=utf-8');
$buffer = ob_get_contents();

ob_end_clean();
echo $buffer;
exit;

?>