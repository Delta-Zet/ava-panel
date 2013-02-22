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


define('IN_ADMIN', '1');
define('IN_CRON', '1');
ob_start();

if(file_exists('./../install/index.php')) die('You must delete directory "install" or <a href="install/index.php">make installation</a>.');
if(!file_exists('./../install_complete.php')) die('File install_complete.php not found. It must exist in site directory.');

require_once('../settings.php');
require_once(_W.'core/core.php');

$Core = new Core('admin');
echo '<textarea style="width: 600px; height: 300px;">'.library::crypt($_GET['pwd']).'</textarea>';
exit;

?>