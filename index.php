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


define('IN_SITE', '1');
ob_start();

if(file_exists('install/index.php')) die('You must delete directory "install" or <a href="install/index.php">make installation</a>.');
if(!file_exists('install_complete.php')) die('File install_complete.php not found. It must exist in site directory.');

require_once('settings.php');
require_once(_W.'core/core.php');

$Core = new Core();
$Core->loadDB();
$Core->runPlugins('start');
if(SHOW_HWT > 0) $debId = $Core->debugStart();

$Core->loadAllData();
$Core->User->userAccess();
$Core->runPlugins('access');

if(!$Core->Site->isOpen()){
	$Core->setMod('main');
	$Core->setFunc('closeStub');
	$Core->runPlugins('closeSite');
}

$Core->callMainModule();
$Core->runPlugins('callFunction');
$Core->runTemplateGenerator();
$Core->runPlugins('templateGen');

$Core->flushHeaders();
$Core->runPlugins('headers');
if($buffer = ob_get_contents()) ob_end_clean();
$buffer = $Core->getOutput(true).$buffer;

$Core->runPlugins('contentTransform');
$Core->setDebugInterval('Время работы скрипта до отправки данных', 0);
echo $buffer;

if(SHOW_HWT > 0) $Core->debugMsg('{Call:Lang:core:core:vremiaraboty}', '', $debId);
exit;

?>