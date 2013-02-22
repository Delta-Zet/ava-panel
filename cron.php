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



define('IN_CRON', '1');

ob_start();
ignore_user_abort(true);
if(!ini_get('safe_mode')) set_time_limit(7200);
putEnv("SCRIPT_FILENAME=".__FILE__);

require_once(dirname(__FILE__).'/settings.php');
if(file_exists(_W.'install/index.php')) die('You must delete directory "install" or <a href="install/index.php">make installation</a>.');
if(!file_exists(_W.'install_complete.php')) die('File install_complete.php not found. It must exist in site directory.');
require_once(_W.'core/core.php');

$Core = new Core();
$Core->loadDB();
$Core->runPlugins('start');

if(SHOW_HWT > 0) $debId = $Core->debugStart();
$Core->loadAllData();
$Core->runPlugins('access');

$Cron = new Cron();
$Cron->dieOldTasks();
$Cron->runCron();
$Cron->runTasks();

$Core->runPlugins('callFunction');
$Core->runPlugins('templateGen');

$Core->flushHeaders();
$Core->runPlugins('headers');
$buffer = ob_get_contents();
ob_end_clean();

$Core->runPlugins('content');
$Core->runPlugins('contentTransform');
echo $buffer;

if(SHOW_HWT > 0) $Core->debugMsg('{Call:Lang:core:core:vremiaraboty}', '', $debId);
exit;

?>