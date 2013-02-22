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



switch($step){	case '1':		$matrix['license']['type'] = 'textarea';
		$matrix['license']['additional_entry_style'] = ' style="width: 960px;"';
		$matrix['license']['additional_style'] = ' style="height: 300px;"';
		$values['license'] = Files::read(_W.'license.txt');

		$matrix['agree_license']['text'] = '{Call:Lang:core:core:iasoglasensl}';
		$matrix['agree_license']['type'] = 'checkbox';
		$matrix['agree_license']['warn'] = '{Call:Lang:core:core:dliaprodolzh}';

		break;

	case '2':
		$dbDrivers = $GLOBALS['Core']->getDBDrivers();
		$matrix['db_capt']['text'] = '{Call:Lang:core:core:parametrybaz}';
		$matrix['db_capt']['type'] = 'caption';

		$matrix['db_driver']['text'] = '{Call:Lang:core:core:tipbazydanny}';
		$matrix['db_driver']['type'] = 'select';
		$matrix['db_driver']['warn'] = '{Call:Lang:core:core:neukazantipb}';
		$matrix['db_driver']['additional'] = $dbDrivers;

		$matrix['db_name']['text'] = '{Call:Lang:core:core:imiabazydann}';
		$matrix['db_name']['type'] = 'text';
		$matrix['db_name']['warn'] = '{Call:Lang:core:core:neukazanoimi1}';

		$matrix['db_prefix']['text'] = '{Call:Lang:core:core:prefikstabli}';
		$matrix['db_prefix']['type'] = 'text';
		$values['db_prefix'] = 'ava_';

		$matrix['db_host']['text'] = '{Call:Lang:core:core:khostbazydan}';
		$matrix['db_host']['type'] = 'text';
		$matrix['db_host']['warn'] = '{Call:Lang:core:core:neukazankhos}';
		$values['db_host'] = 'localhost';

		$matrix['db_user']['text'] = '{Call:Lang:core:core:polzovatelba}';
		$matrix['db_user']['type'] = 'text';
		$matrix['db_user']['warn'] = '{Call:Lang:core:core:neukazanpolz}';

		$matrix['db_pwd']['text'] = '{Call:Lang:core:core:parolbazydan}';
		$matrix['db_pwd']['type'] = 'pwd';
		$matrix['db_pwd']['warn'] = '{Call:Lang:core:core:neukazanparo1}';

		$switchs = array();
		foreach($dbDrivers as $i => $e){
			list($m2, $v2) = call_user_func(array('db_'.$i, 'getConnectMatrix'));
			$matrix = library::array_merge($matrix, $m2);
			$values = library::array_merge($values, $v2);

			$matrix[library::firstKey($m2)]['pre_text'] = '<div id="block_'.$i.'" style="display: none;">';
			$matrix[library::lastKey($m2)]['post_text'] = '</div>';
			$switchs[$i] = array('block_'.$i => 1);
			$switchs['blocks']['block_'.$i] = 1;
		}

		$switchCode = "switchByValue('driver', ".library::jsHash($switchs).");";
		$matrix['db_driver']['additional_style'] = " onChange=\"$switchCode\"";
		$matrix[library::lastKey($matrix)]['post_text'] .= "<script type=\"text/javascript\">
			$switchCode;
		</script>";

		$matrix['file_capt']['text'] = '{Call:Lang:core:core:putifajlovoj}';
		$matrix['file_capt']['type'] = 'caption';

		$path = regexp::Replace('install/'.basename($_SERVER['SCRIPT_FILENAME']), '', regexp::Replace("\\", "/", $_SERVER['SCRIPT_FILENAME']));
		$path = regExp::Replace("/^\w\:/", "", $path, true);

		$matrix['_W']['text'] = '{Call:Lang:core:core:putnaservere}';
		$matrix['_W']['type'] = 'text';
		$matrix['_W']['warn'] = '{Call:Lang:core:core:neukazanputn}';
		$values['_W'] = $path;

		$matrix['TMPL_FOLDER']['text'] = '{Call:Lang:core:core:imiapapkisha}';
		$matrix['TMPL_FOLDER']['type'] = 'text';
		$matrix['TMPL_FOLDER']['warn'] = '{Call:Lang:core:core:neukazanoimi2}';
		$values['TMPL_FOLDER'] = 'templates';

		$matrix['ADMIN_FOLDER']['text'] = '{Call:Lang:core:core:papkaadminis}';
		$matrix['ADMIN_FOLDER']['type'] = 'text';
		$matrix['ADMIN_FOLDER']['warn'] = '{Call:Lang:core:core:neukazanapap}';
		$values['ADMIN_FOLDER'] = 'admin';

		$matrix['API_FOLDER']['text'] = '{Call:Lang:core:core:papkaapi}';
		$matrix['API_FOLDER']['type'] = 'text';
		$matrix['API_FOLDER']['warn'] = '{Call:Lang:core:core:neukazanapap1}';
		$values['API_FOLDER'] = 'api';

		$matrix['TMP']['text'] = '{Call:Lang:core:core:papkadliavre}';
		$matrix['TMP']['type'] = 'text';
		$matrix['TMP']['warn'] = '{Call:Lang:core:core:neukazanapap2}';
		$values['TMP'] = $path.'tmp/';


		$matrix['ftp_capt']['text'] = '{Call:Lang:core:core:parametryftp}';
		$matrix['ftp_capt']['type'] = 'caption';
		$matrix['ftp_capt']['post_text'] = '{Call:Lang:core:core:vbolshinstve}';

		$matrix['ftp_user']['text'] = '{Call:Lang:core:core:ftppolzovate1}';
		$matrix['ftp_user']['type'] = 'text';

		$matrix['ftp_pwd']['text'] = '{Call:Lang:core:core:parolftppolz}';
		$matrix['ftp_pwd']['type'] = 'pwd';

		$matrix['ftp_host']['text'] = '{Call:Lang:core:core:ftpkhost1}';
		$matrix['ftp_host']['type'] = 'text';

		$matrix['ftp_port']['text'] = '{Call:Lang:core:core:ftpport1}';
		$matrix['ftp_port']['type'] = 'text';
		$values['ftp_port'] = '21';

		$matrix['ftp_folder']['text'] = '{Call:Lang:core:core:putotkorniaf}';
		$matrix['ftp_folder']['type'] = 'text';


		$matrix['other_capt']['text'] = '{Call:Lang:core:core:dannyedliado}';
		$matrix['other_capt']['type'] = 'caption';

		require_once(_W.'forms/type_newlogin.php');
		require_once(_W.'forms/type_newpwd.php');
		require_once(_W.'forms/type_neweml.php');

		$matrix['key']['text'] = '{Call:Lang:core:core:sluchajnyesi}';
		$matrix['key']['comment'] = '{Call:Lang:core:core:vveditesiuda}';
		$matrix['key']['type'] = 'text';
		$matrix['key']['warn'] = '{Call:Lang:core:core:vyneukazalis}';

		if(function_exists('mcrypt_module_open')){
			$matrix['CRYPT_INTERFACE']['text'] = '{Call:Lang:core:core:algoritmshif1}';
			$matrix['CRYPT_INTERFACE']['type'] = 'select';
			$matrix['CRYPT_INTERFACE']['additional'] = array(
				'' => '{Call:Lang:core:core:vnutrennij}',
				'blowfish' => 'Blowfish',
				'des' => 'DES',
				'tripledes' => '3DES',
				'crypt' => 'Crypt',
				'cast-128' => 'Cast 128',
				'cast-256' => 'Cast 256'
			);
		}

		$matrix['site_capt']['text'] = '{Call:Lang:core:core:dannyesajta}';
		$matrix['site_capt']['type'] = 'caption';

		$matrix['url']['text'] = '{Call:Lang:core:core:urldostupaks}';
		$matrix['url']['type'] = 'text';
		$matrix['url']['warn'] = '{Call:Lang:core:core:neukazanurld}';
		$values['url'] = 'http://'.$_SERVER['HTTP_HOST'].regExp::Replace('/install', '', Files::dirname($_SERVER['REQUEST_URI']));

		$matrix['name']['text'] = '{Call:Lang:core:core:imiasajta}';
		$matrix['name']['type'] = 'text';
		$matrix['name']['warn'] = '{Call:Lang:core:core:neukazanoimi3}';

		$matrix['lang']['text'] = 'Основной язык';
		$matrix['lang']['type'] = 'select';
		$matrix['lang']['warn'] = 'Вы не указали основной язык';
		$matrix['lang']['additional'] = $langsList;

		break;
}

?>