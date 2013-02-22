<?


  /******************************************************************************************************
  *** Package: AVA-Panel Version 3.0
  *** Copyright (c) 2006, Anton A. Rassypaeff. All rights reserved
  *** Copyright (c) 2013, Alexander S. Maslov. All rights reserved
  *** License: GNU General Public License v3
  *** Author: Anton A. Rassypaeff | Рассыпаев Антон Александрович
  *** Author: Alexander S. Maslov | Маслов Александр Сергеевич
  *** Contacts:
  ***   Site: https://github.com/Delta-Zet/ava-panel
  ***   E-mail: info@delta-zet.com
  ******************************************************************************************************/


define('IN_ADMIN', '1');
ob_start();

if (file_exists('./../install/index.php') && !file_exists('./../install/development.env')) {
  die('You must delete directory "install" or <a href="./../install/index.php">make installation</a>.');
}
if (!file_exists('./../install_complete.php')) {
  die('File install_complete.php not found. It must exist in site directory.');
}
require_once('../settings.php');
require_once(_W.'core/core.php');

$Core = new Core('admin');
$Core->loadDB();
$Core->runPlugins('start');
if(SHOW_HWT > 0) $debId = $Core->debugStart();

$Core->loadAllData();
$Core->User->adminAccess();
$Core->runPlugins('access');

//Проверяем аутентификацию админа
if (!$Core->userIsAdmin()) {
  $Core->setMod('main');
  $Core->setFunc('authAdmin');
}

$Core->callMainModule();
$Core->runPlugins('callFunction');

$Core->runTemplateGenerator();
$Core->runPlugins('templateGen');

$Core->flushHeaders();
$Core->runPlugins('headers');
$buffer = ob_get_contents();
ob_end_clean();

$buffer = $Core->getOutput().$buffer;
$Core->runPlugins('contentTransform');
$Core->setDebugInterval('Время работы скрипта до отправки данных', 0);
echo $buffer;

if (SHOW_HWT > 0) {
  $Core->debugMsg('{Call:Lang:core:core:vremiaraboty}', '', $debId);
}
exit;

?>