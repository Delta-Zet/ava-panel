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



class Install extends moduleInterface{

	public $DB;

	protected function func_step1(){		/*
			1 шаг инсталляции. Спрашивается согласие с лицензией
		*/

		$this->setMeta('{Call:Lang:core:core:ustanovkaava}');
		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'install',
						'step2',
						array('caption' => '{Call:Lang:core:core:ustanovkaava}'),
						_W.'templates/admin/default/form.tmpl',
						false
					),
					_W.'install/install_form.php',
					array('step' => 1)
				),
				array(),
				array(),
				'install'
			)
		);
	}

	protected function func_step2(){		/*
			2 шаг инсталляции. Указание всех параметров установки
		*/

		if(!$this->check()) return false;
		$this->setMeta('{Call:Lang:core:core:ustanovkaava1}');
		$langsList = array();

		$XML1 = XML::parseXML(Files::Read(_W.'install/install.xml'));
		foreach($XML1['install']['installation'] as $e){			$XML2 = XML::parseXML(Files::Read(_W.$e.'descript.xml'), $a, $h, 'descript');
			if($XML2['type'] == 'languages') $langsList[$XML2['name']] = $XML2['text'];		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'install',
						'step3',
						array('caption' => '{Call:Lang:core:core:ustanovkaava1}'),
						_W.'templates/admin/default/form.tmpl',
						false
					),
					_W.'install/install_form.php',
					array(
						'step' => 2,
						'langsList' => $langsList
					)
				),
				array(),
				$this->values,
				'install'
			)
		);
	}

	protected function func_step3(){
		/*
			3 шаг инсталляции
		*/

		$dbClass = 'db_'.$this->values['db_driver'];
		if(!Library::callClass(
			$dbClass,
			'checkConnect',
			array(
				$this->values['db_host'],
				$this->values['db_user'],
				$this->values['db_pwd'],
				$this->values['db_name']
			),
			true)
		){			$this->setError('db_user', '{Call:Lang:core:core:neudalospodk}');		}

		if(!file_exists($this->values['_W'].'index.php') || !file_exists($this->values['_W'].'install/index.php')){			$this->setError('_W', '{Call:Lang:core:core:nepravilnyjp}');		}
		else{
			if(!file_exists($this->values['_W'].$this->values['TMPL_FOLDER']) || !is_dir($this->values['_W'].$this->values['TMPL_FOLDER'])){
				$this->setError('TMPL_FOLDER', '{Call:Lang:core:core:nepravilnouk}');
			}
			if(!file_exists($this->values['_W'].$this->values['ADMIN_FOLDER']) || !is_dir($this->values['_W'].$this->values['ADMIN_FOLDER'])){
				$this->setError('ADMIN_FOLDER', '{Call:Lang:core:core:nepravilnouk1}');
			}
			if(!file_exists($this->values['_W'].$this->values['API_FOLDER']) || !is_dir($this->values['_W'].$this->values['API_FOLDER'])){
				$this->setError('API_FOLDER', '{Call:Lang:core:core:nepravilnouk2}');
			}
		}

		if(!file_exists($this->values['TMP']) || !is_dir($this->values['TMP'])){
			$this->setError('TMP', '{Call:Lang:core:core:nepravilnouk3}');
		}
		elseif(!Files::isWritable($this->values['TMP'])){
			$this->setError('TMP', '{Call:Lang:core:core:netpravzapis}');
		}

		if($this->values['ftp_host']){			$ftp = new ftpClient($this->values['ftp_host'], $this->values['ftp_user'], $this->values['ftp_pwd'], $this->values['ftp_port']);
			if(!$ftp->connect()){
				$this->setError('ftp_host', '{Call:Lang:core:core:neudalospodk1}');
			}
			elseif(!$ftp->setFolder($this->values['ftp_folder'])){				$this->setError('ftp_folder', '{Call:Lang:core:core:neudalosusta1}');			}
		}
		else{			if(!Files::isWritable($this->values['_W'])){				$this->setError('_W', 'У вас нет прав для записи в эту папку. Необходимо установить права на запись, либо указать данные для записи с использованием FTP');			}		}

		if(!$this->check()) return false;

		define('INSTALLATION_RUN', 1);
		$this->setMeta('{Call:Lang:core:core:ustanovkaava2}');

		$this->values['CRYPT_INTERFACE'] = empty($this->values['CRYPT_INTERFACE']) ? '' : $this->values['CRYPT_INTERFACE'];
		define('CRYPT_INTERFACE', $this->values['CRYPT_INTERFACE']);
		define('AVA_KEY', $this->values['key']);
		$dbParams = call_user_func(array($dbClass, 'getConnectParams'), $this, $this->values);

		$this->Core->setParam('ftpHost', $this->values['ftp_host']);
		$this->Core->setParam('ftpUser', $this->values['ftp_user']);
		$this->Core->setParam('ftpPwd', $this->values['ftp_pwd']);
		$this->Core->setParam('ftpFolder', $this->values['ftp_folder']);
		$this->Core->setParam('ftpPort', $this->values['ftp_port']);

		Files::write($this->values['TMP'].'settings.php', '<'.'?'."\n\n".
			"define('TEST_MODE', '');					//Включить режим отладки\n".
			"define('SHOW_DB_REQS', '');				//Отображать MySQL-запросы к базе данных\n".
			"define('SHOW_HWT', '');					//Отслеживание времени работы блоков\n".
			"define('SHOW_HTTP_REQS', '');				//Отслеживание http-запросов\n".
			"define('SHOW_TMPL_DEBUG_DATA', '');		//Отслеживание обработки шаблонов\n".
			"define('SHOW_MAIL_DEBUG_DATA', '');		//Отслеживание отправки писем\n\n".
			"define('AVA_DB_HOST', '{$this->values['db_host']}');		//Хост БД\n".
			"define('AVA_DB_USER_ADMIN', '{$this->values['db_user']}');		//Пользователь БД\n".
			"define('AVA_DB_PWD_ADMIN', '".Library::crypt($this->values['db_pwd'])."');	//Пароль пользователя БД\n".
			"define('AVA_DB_NAME', '{$this->values['db_name']}');				//Имя БД\n\n".
			"define('AVA_DB_DRIVER', '{$this->values['db_driver']}');			//Драйвер БД\n".
			"define('AVA_DB_PCONNECT', '');			//Использовать постоянные соединения с БД\n".
			"define('AVA_DB_PREF', '{$this->values['db_prefix']}');				//Префикс таблиц БД\n\n".
			'$GLOBALS["AVA_DB_PARAMS"] = '.Library::arr2str($dbParams).";\n\n".
			'$GLOBALS["AVA_ERROR_LOG_PARAMS"] = array('."\n".
				"\t".'"errorLog" => false,			//file - в файл компактно, fileFull - в файл полностью, multifiles - в разные файлы, db - в базу'."\n".
				"\t".'"errorTypes" => array(10, 20, 21, 30, 40, 50, 60, 100),		//Типы ошибок'."\n".
				"\t".'"errorLogPath" => "'.$this->values['_W'].'storage/security/log/",	//Папка куда писать'."\n".
				"\t".'"errorLogLife" => "",				//Срок жизни файлов лога, в сутках, если пусто или 0 - не ограничен'."\n".
				"\t".'"errorLogClearStyle" => "delete"		//Способ очистки лога, delete - удалять, arc - архивировать, copy - копировать без архивации'."\n".
			');'."\n\n".
			"define('AVA_KEY', '{$this->values['key']}');\n".
			"define('_W', '{$this->values['_W']}');		//Путь до скриптов\n".
			"define('TMPL', '{$this->values['_W']}{$this->values['TMPL_FOLDER']}/');		//Папка шаблонов - полный путь\n".
			"define('TMPL_FOLDER', '{$this->values['TMPL_FOLDER']}');		//Имя папки шаблонов\n".
			"define('TMP', '{$this->values['TMP']}');		//Имя папки для временных файлов\n".
			"define('ADMIN_FOLDER', '{$this->values['ADMIN_FOLDER']}');			//Папка администратора\n".
			"define('API_FOLDER', '{$this->values['API_FOLDER']}');			//Папка API\n".
			"define('CRYPT_INTERFACE', '{$this->values['CRYPT_INTERFACE']}');			//Тип шифрования\n".
			"define('LANGUAGE', '{$this->values['lang']}');			//Язык по умолчанию\n".
			"\n?".">"
		);

		if(!$this->Core->ftpCopy($this->values['TMP'].'settings.php', _W.'settings.php')){			$this->setContent('Не удалось записать файл "'.$this->values['_W'].'settings.php'.'"');
			return false;		}
		Files::rm($this->values['TMP'].'settings.php');

		$this->DB = new $dbClass(
			$this->values['db_host'],
			$this->values['db_user'],
			$this->values['db_pwd'],
			$dbParams
		);

		$this->DB->setDB($this->values['db_name'], $this->values['db_prefix']);
		@require_once(_W.'settings.php');

		$this->Core->loadDB();
		$this->Core->setTemplateType('main');
		$this->Core->setTemplateName('default');

		$instObj = $this->Core->callModule('core');
		$instObj->values = Library::array_merge($this->values, array('source' => 4, 'archieve_path' => _W, 'install_path' => _W.'install/install.xml'));
		$instObj->pkgInstallEnd();

		$this->setContent($instObj->getContentVar('body'));
		$this->setContent('{Call:Lang:core:core:installiatsi1}');

		Files::write($this->values['TMP'].'install_complete.php', "Package installed");
		$this->Core->ftpCopy($this->values['TMP'].'install_complete.php', _W.'install_complete.php');
		Files::rm($this->values['TMP'].'install_complete.php');
	}
}

?>