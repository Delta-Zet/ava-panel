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



define('IN_API', '1');
ob_start();

if(file_exists('./../install/index.php')) die('You must delete directory "install" or <a href="./../install/index.php">make installation</a>.');
if(!file_exists('./../install_complete.php')) die('File install_complete.php not found. It must exist in site directory.');

require_once('../settings.php');
require_once(_W.'core/core.php');

$Core = new Core();
$Core->loadDB();
$Core->runPlugins('start');

if(SHOW_HWT > 0) $debId = $Core->debugStart();
$Core->loadAllData();

$API = new API();										//Создаем API
$API->setXMLInput(file_get_contents("php://input"));	//Устанавливает ввод созданный с использованием XML
$API->auth();
$Core->runPlugins('access');

$result = $API->callFunc();				//Обращаемся к функционалу
$Core->runPlugins('callFunction');
$Core->runPlugins('templateGen');

$Core->flushHeaders();
$Core->runPlugins('headers');
$buffer = ob_get_contents();
ob_end_clean();

$buffer = $API->getXMLOutput($result, $buffer);		//Формируем вывод
$Core->runPlugins('contentTransform');
echo $buffer;							//Отправляем вывод

if(SHOW_HWT > 0) $Core->debugMsg('{Call:Lang:core:core:vremiaraboty}', '', $debId);
exit;

?>