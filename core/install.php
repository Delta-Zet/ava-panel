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



class installCoreDefault extends InstallModuleObject implements InstallModuleInterface{	/*
		Единственная задача класса - установка дефолтных значений
	*/

	public function Install(){
		$this->createAllTables();
		$this->DB->Ins(array('version', array('name' => 'core')));
		$sId = $this->DB->Ins(array('sites', array('default' => '1', 'url' => $this->obj->values['url'], 'name' => $this->obj->values['name'])));

		$this->iObj->values['sites'][$sId] = 1;
		$t = time();
		$c = Library::inventStr(16);
		$passHash = Library::getPassHash($this->obj->values['login'], $this->obj->values['pwd'], $c);

		$id = $this->DB->Ins(
			array(
				'users',
				array(
					'date' => $t,
					'login' => $this->obj->values['login'],
					'pwd' => $passHash,
					'code' => $c,
					'utc' => 28800,
					'eml' => $this->obj->values['eml'],
					'show' => '1'
				)
			)
		);

		$this->DB->Ins(
			array(
				'admins',
				array(
					'user_id' => $id,
					'date' => $t,
					'login' => $this->obj->values['login'],
					'pwd' => $passHash,
					'eml' => $this->obj->values['eml'],
					'root' => 1,
					'show' => '1'
				)
			)
		);

		$this->setAllDefaults($this->obj->values);
		return true;
	}

	public function prepareInstall(){
		return true;
	}

	public function checkInstall(){
		return true;
	}

	public function Uninstall(){
		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update($oldVersion, $newVersion){
		$this->updateAllTables();
		$this->updateAllDefaults($this->obj->values);

		switch(true){			case !Library::versionCompare('3.0.0.0.10', $oldVersion):
				$this->DB->Alter(array('modules', array('modify' => array('db' => ''))));		}

		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}

	public function getTables(){		/*
			Создает или восстанавливает таблицы
		*/

		$return = array();
		$return['version'] = array(
			array(
				'name' => 'VARCHAR(8)',
				'version' => 'VARCHAR(32)',
			),
			array(
				'uni' => array(array('name'))
			)
		);

		$return['sites'] = array(
			array(
				'id' => '',
				'url' => '',
				'name' => '',
				'sort' => '',
				'access' => 'TINYINT',
				'default' => 'CHAR(1)'
			),
			array(
				'uni' => array(array('url'))
			)
		);

		$return['settings_block_names'] = array(
			array(
				'name' => '',
				'text' => '',
				'sort' => ''
			)
		);

		$return['settings'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',
				'value' => '',
				'crypt' => 'TINYINT',
				'var_type' => 'VARCHAR(4)',
				'type' => 'VARCHAR(32)',
				'text' => '',
				'vars' => '',
				'sort' => '',
				'show' => '',
				'block' => '',
				'module' => 'VARCHAR(64)',
				'site' => 'INT'
			),
			array(
				'uni' => array(
					array('name', 'module', 'site')
				)
			)
		);


		/*
				Сессии
		*/
		$return['session'] = array(
			array(
				'id' => '',
				'sessid' => '',
				'user_id' => 'INT',
				'date' => '',
				'vars' => ''
			),
			array(
				'uni' => array(
					array('sessid')
				)
			)
		);

		/*
		Запуск плагинов
		*/
		$return['plugins'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => 'VARCHAR(16)',
				'code' => 'TEXT',
				'settings_code' => 'TEXT',		//Код вызова при настройке
				'set_code' => 'TEXT',			//Код вызова при установке
				'vars' => '',
				'settings' => 'TEXT',
				'version' => '',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);


		/*
		Точки запуска плагинов
		*/
		$return['plugin_points'] = array(
			array(
				'id' => '',
				'mod' => 'VARCHAR(64)',
				'name' => 'VARCHAR(64)',
				'text' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('mod', 'name'),
					array('mod', 'text'),
				)
			)
		);

		/*
			Формы
		*/
		$return['forms'] = array(
			array(
				'id' => '',
				'date' => '',
				'name' => '',
				'vars' => 'MEDIUMTEXT'
			)
		);

		/*
			Пользователи, администраторы
		*/
		$return['users'] = array(
			array(
				'id' => '',
				'group' => '',
				'type' => '',
				'date' => '',
				'login' => '',
				'pwd' => 'VARCHAR(256)',
				'guest_pwd' => 'VARCHAR(256)',
				'code' => 'VARCHAR(16)',
				'name' => '',
				'eml' => '',
				'utc' => 'INT',
				'rights' => 'TEXT',
				'vars' => 'TEXT',
				'comment' => 'TEXT',
				'show' => ''								//1 - проверен, 0 - не проверен, -1 - забанен
			),
			array(
				'uni' => array(
					array('login')
				)
			)
		);

		$return['users_groups'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'rights' => 'TEXT',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['users_form_types'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'auto_reg_group' => '',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['user_reg_form'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => '',
				'vars' => '',
				'in_reg' => 'CHAR(1)',
				'in_account' => 'CHAR(1)',
				'in_admin' => 'CHAR(1)',
				'form_types' => '',
				'sort' => '',
				'show' => '',
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['admins_groups'] = array(
			array(
				'id' => '',
				'name' => '',
				'rights' => 'TEXT',
				'ip_access_type' => '',
				'show' => 'CHAR(1)'
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['admins'] = array(
			array(
				'id' => '',
				'user_id' => 'INT',
				'group' => 'INT',
				'date' => '',
				'login' => 'VARCHAR(32)',
				'pwd' => '',
				'eml' => '',
				'data' => '',
				'root' => 'CHAR(1)',
				'ip_access_type' => '',
				'show' => 'CHAR(1)',
				'rights' => 'TEXT'
			),
			array(
				'uni' => array(
					array('user_id'),
					array('login')
				)
			)
		);

		$return['admin_stat'] = array(
			array(
				'id' => '',
				'admins_id' => 'INT',
				'date' => '',
				'ip' => '',
				'action_type' => '',
				'action_descript' => '',
				'action_mod' => '',
				'action_object' => '',
				'action_id' => 'INT',
				'vars' => ''
			)
		);

		$return['admin_access_ip'] = array(
			array(
				'id' => '',
				'ip' => '',
				'type' => '',
				'admins_groups_id' => 'INT',
				'admins_id' => 'INT'
			)
		);

		$return['site_access_ip'] = array(
			array(
				'id' => '',
				'ip' => ''
			)
		);

		$return['api_access_ip'] = array(
			array(
				'id' => '',
				'ip' => '',
				'type' => '',
				'user_id' => 'INT'
			)
		);

		//Установленные модули
		$return['isset_modules'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'version' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		//Активные копии модулей
		$return['modules'] = array(
			array(
				'id' => '',
				'url' => '',
				'text' => '',
				'name' => '',
				'db' => '',
				'united_modules' => '',
				'sites' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('url')
				)
			)
		);

		$return['module_links'] = array(
			array(
				'id' => '',
				'mod' => '',
				'name' => 'VARCHAR(64)',		//Идентификатор
				'parent' => '',
				'text' => '',					//Текст ссылки
				'url' => '',
				'eval' => 'TEXT',				//Если есть и вычисляется в true, ссылка будет, иначе - нет
				'access' => 'CHAR(1)',			//0 - все, 1 - юзеры, 2 - админы
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('mod', 'name')
				)
			)
		);


		/*
		  Установленные языки
		*/
		$return['languages'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(32)',
				'text' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);


		/*
		  Шаблоны
		*/
		$return['templates'] = array(
			array(
				'id' => '',
				'name' => '',
				'folder' => 'VARCHAR(32)',
				'type' => 'VARCHAR(64)',
				'language' => '',
				'tech_name' => '',
				'version' => '',
				'vars' => '',
				'show' => 'CHAR(1)',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('folder', 'type')
				)
			)
		);

		/*
		  Страницы шаблонов
		*/
		$return['template_pages'] = array(
			array(
				'id' => '',
				'template' => 'VARCHAR(32)',
				'template_type' => 'VARCHAR(64)',
				'name' => '',
				'url' => 'VARCHAR(64)',
				'type' => 'VARCHAR(8)',
				'body' => ''
			),
			array(
				'uni' => array(
					array('template', 'template_type', 'url', 'type')
				)
			)
		);

		/*
		  Блоки внутри шаблонов
		*/
		$return['template_blocks'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'body' => '',
				'template' => 'VARCHAR(32)',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('template', 'name')
				)
			)
		);

		/*
		  Дополнительные базы данных
		*/
		$return['databases'] = array(
			array(
				'id' => '',
				'ident' => 'VARCHAR(64)',
				'name' => 'VARCHAR(64)',
				'host' => 'VARCHAR(64)',
				'user' => 'VARCHAR(64)',
				'pwd' => '',
				'prefix' => 'VARCHAR(16)',
				'driver' => 'VARCHAR(8)',
				'vars' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('ident'),
					array('name', 'host', 'user', 'prefix', 'driver')
				)
			)
		);

		/*
			Папочки
		*/
		$return['folders'] = array(
			array(
				'id' => '',
				'name' => '',
				'path' => '',
				'main_standart' => '',
				'standarts' => '',
				'modules' => '',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('path'),
				)
			)
		);

		$return['image_standarts'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'width' => 'SMALLINT',
				'height' => 'SMALLINT',
				'rotate' => 'TINYINT',							//угол поворота
				'rotate_color' => '',							//цвет заполнения пустого пространства
				'rotate_color_transparent' => 'CHAR(1)',
				'rotate_moment' => 'CHAR(1)',					//момент повортоа
				'enlarge' => 'CHAR(1)',							//Увеличить если меньше стандарта
				'resize_style' => 'TINYINT',					//Стиль уменьшения: 0 - пропорционально, 1 - фиксированно левый верх, 2 - фиксированно левый центр, 3 - фиксированно левый низ, 4 - фиксированно центр верх, 5 - фиксированно центр центр, 6 - фиксированно центр низ, 7 - фиксированно правый верх,, 8 - фиксированно правый центр,, 9 - фиксированно правый низ,
				'quality' => 'TINYINT',							//Качество изображения
				'watermarks' => 'TEXT',							//Водяные знаки
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['watermarks'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => 'VARCHAR(8)',			//image или text
				'hpos' => 'SMALLINT',
				'vpos' => 'SMALLINT',
				'hcorner' => 'CHAR(1)',			//По горизонтали r|l
				'vcorner' => 'CHAR(1)',			//По вертикали t|b
				'transparency' => 'TINYINT',	//Прозрачность
				'file' => '',
				'content' => '',				//URL изображения либо текст надписи
				'font' => '',					//Шрифт надписи
				'font_size' => 'SMALLINT',
				'color' => '',					//Цвят надписи бял
				'corner' => 'SMALLINT',			//Угол поворота надписи
				'moment' => 'CHAR(1)',			//момент наложения
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['fonts'] = array(
			array(
				'id' => '',
				'name' => '',
				'file' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('file'),
				)
			)
		);

		/*
			Капча
		*/
		$return['captcha'] = array(
			array(
				'id' => '',
				'date' => '',
				'code' => '',
				'captcha_standart' => '',
				'vars' => ''
			)
		);

		$return['captcha_standarts'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'backgrounds' => 'TEXT',
				'captcha_type' => 'CHAR(1)',			//t - текстовая, m - математическая
				'len' => 'INT',
				'len_to' => 'INT',
				'symbols' => 'VARCHAR(255)',
				'direction' => 'CHAR(1)',
				'register_depend' => 'CHAR(1)',			//Если выставлено - регистрозависим
				'math_nums' => 'INT',
				'math_nums_to' => 'INT',
				'math_len' => 'INT',
				'math_len_to' => 'INT',
				'math_actions' => 'VARCHAR(16)',
				'start_position' => 'INT',
				'start_position_to' => 'INT',
				'start_position_vertical' => 'INT',
				'start_position_vertical_to' => 'INT',
				'fonts' => 'VARCHAR(255)',
				'font_size' => 'INT',
				'font_size_to' => 'INT',
				'font_blur' => 'INT',
				'font_blur_to' => 'INT',
				'angle' => 'INT',
				'angle_to' => 'INT',
				'letter_offset' => 'INT',
				'letter_offset_to' => 'INT',
				'letter_vertical_offset' => 'INT',
				'letter_vertical_offset_to' => 'INT',
				'color' => 'VARCHAR(6)',
				'color_to' => 'VARCHAR(6)',
				'transparent' => 'INT',
				'transparent_to' => 'INT',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		/*
			Лог исключений
		*/
		$return['exceptions'] = array(
			array(
				'id' => '',
				'date' => '',
				'code' => 'INT(4)',
				'file' => '',
				'line' => '',
				'body' => ''
			)
		);

		/*
		  Кэш
		*/
		$return['datacache'] = array(
			array(
				'id' => '',
				'date' => '',
				'mod' => 'VARCHAR(64)',
				'name' => 'VARCHAR(64)',
				'value' => 'MEDIUMTEXT'
			),
			array(
				'uni' => array(
					array('mod', 'name')
				)
			)
		);


		/************************************************************************************************************************************************************************

																Таблицы относящиеся к модулю admin_main

		*************************************************************************************************************************************************************************/

		/*
		  Настройки служебных кнопок админов
		*/
		$return['admin_buttons'] = array(
			array(
				'id' => '',
				'url' => '',
				'name' => '',
				'target' => 'VARCHAR(8)',
				'admin' => 'INT',
				'sort' => ''
			)
		);

		/*
		Меню
		*/
		$return['admin_menu'] = array(
			array(
				'id' => '',
				'parent_id' => 'INT',
				'pkg' => '',
				'text' => '',
				'url' => '',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('text', 'parent_id')
				)
			)
		);

		/*
			Крон
		*/
		$return['cron'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',
				'module' => 'VARCHAR(64)',
				'last_work' => 'INT',
				'month' => '',
				'day' => '',
				'week' => '',
				'hour' => '',
				'minute' => '',
				'tick' => 'CHAR(1)',
				'del_forbid' => 'CHAR(1)',
				'limit' => 'INT',
				'command' => 'TEXT',
				'comment' => 'TEXT',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name', 'module')
				)
			)
		);

		$return['tasks'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',					//Имя из cron (может отсутствовать)
				'added' => 'INT',							//Внесено
				'runned' => 'INT',							//Когда началось выполнение
				'execute' => 'INT',							//Когда закончилось выполнение
				'status' => 'INT(1)',						//0 - ждет, 1 - начато выполнение, 2 - закончено выполнение, 3 - отклонена по таймауту
				'result' => 'INT(1)',						//0 или 1
				'result_text' => 'TEXT',
				'command' => 'TEXT',
				'comment' => 'TEXT'
			)
		);

		//Универсальный URL-преобразователь
		$return['urls'] = array(
			array(
				'id' => '',
				'site' => '',
				'url' => '',
				'rewrited' => ''				//Переписываемый URL
			),
			array(
				'uni' => array(
					array('rewrited')
				)
			)
		);

		$return['url_gen_rights'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(32)',
				'text' => '',
				'type' => 'VARCHAR(8)',
				'mod' => 'VARCHAR(32)',
				'func' => 'VARCHAR(32)',
				'vars' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
					array('mod', 'func', 'type')
				)
			)
		);

		//Шпиен пользовательских действий
		$return['user_actions'] = array(
			array(
				'id' => '',
				'sess_id' => '',
				'user_id' => 'INT',
				'mark1' => '',
				'mark_type1' => '',
				'mark2' => '',
				'mark_type2' => '',
				'mark3' => '',
				'mark_type3' => '',
				'date' => '',
				'action' => '',
				'mod' => '',
				'func' => ''
			)
		);

		/*
		  Отправка писем
		*/
		$return['mail_templates'] = array(
			array(
				'id' => '',
				'mod' => '',
				'name' => '',
				'text' => '',
				'format' => '',					// text/html, text/plain etc.
				'subj' => '',
				'body' => 'TEXT',
				'sender_eml' => '',				//мыло отправителя
				'sender' => '',					//отправитель
				'extra' => 'TEXT',				//Аттачи, хеадеры и пр. полезная extra
				'notify_success' => 'CHAR(1)',	//Уведомлять о удачно отправленных письмах
				'notify_fail' => 'CHAR(1)',		//Уведомлять о неудачно отправленных письмах 1 - в конце очереди, 2 - о любой попытке
				'notify_success_subj' => '',
				'notify_success_body' => 'TEXT',
				'notify_fail_subj' => '',
				'notify_fail_body' => 'TEXT',
				'notify_eml' => '',
				'notify_sender_eml' => '',
				'notify_sender' => '',
				'notify_success_extra' => 'TEXT',
				'notify_fail_extra' => 'TEXT',
				'system' => 'CHAR(1)',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['mails'] = array(
			array(
				'id' => '',
				'date' => '',					//Когда внесено
				'in_work' => 'DECIMAL(17, 7)',	//Когда взято из очереди на отправку
				'senddate' => 'INT',			//Когда отправлено
				'attempts' => 'INT',			//Число попыток отправки
				'status' => 'TINYINT',			// 0 - в очереди, 1 - отправлено, 2 - не отправлено, из очереди удалено
				'mod' => '',
				'func' => '',
				'format' => '',
				'eml' => '',					//Мыло получателя
				'subj' => '',					//Тема
				'body' => 'TEXT',				//Текст
				'sender_eml' => '',
				'sender' => '',
				'extra' => 'TEXT',				//Хеадерсы и аттачи
				'notify_success' => 'CHAR(1)',	//Уведомить
				'notify_fail' => 'CHAR(1)',
				'notify_success_subj' => '',
				'notify_success_body' => 'TEXT',
				'notify_fail_subj' => '',
				'notify_fail_body' => 'TEXT',
				'notify_eml' => '',
				'notify_sender_eml' => '',
				'notify_sender' => '',
				'notify_success_extra' => 'TEXT',
				'notify_fail_extra' => 'TEXT',
			)
		);

		return $return;
	}

	public function getDefaultSettingsCaptions($params){
		return array(
			'site' => '{Call:Lang:core:core:sajt}',
			'template' => '{Call:Lang:core:core:shablony}',
			'admins' => '{Call:Lang:core:core:adminka}',
			'url' => '{Call:Lang:core:core:obrabotkaurl}',
			'session' => '{Call:Lang:core:core:sessii}',
			'users' => '{Call:Lang:core:core:registratsii}',
			'mails' => '{Call:Lang:core:core:otpravkapise}',
			'ftp' => 'FTP',
			'folders' => '{Call:Lang:core:core:papki}',
			'captcha' => 'CAPTCHA',
			'images' => '{Call:Lang:core:core:rabotasizobr1}',
			'date' => '{Call:Lang:core:core:dataivremia}',
			'clear' => '{Call:Lang:core:core:ochistka}',
			'cron' => '{Call:Lang:core:core:vypolnenieza}',
			'api' => 'API'
		);	}

	public function getDefaultSettings($params){		/*
		  Дефолтные настройки уровня ядра
		  В поле vars -	matrix = дополнительные параметры генерируемой матрицы
		  				eval = Дополнительный выполняемый код. Return этого кода присоединяется к матрице
		  				setEval = Дополнительный код выполняемый когда вносятся изменения в форму

		  Для каждого сайта и связанного с ним модуля можно выставить персональные настройки, в т.ч. и для core (т.е. общие по всему сайту).
		  Если не специфицирован сайт, считается что настройка единая для всех сайтов и модулей
		  Для случаев когда сайт не специфицирован - он относится к определенному блоку настроек
		*/

		$return = array();
		$return[0]['core']['template']['adminTemplate'] = array(
			'value' => 'default',
			'type' => 'select',
			'text' => '{Call:Lang:core:core:shablonadmin}',
			'vars' => array(
				'matrix' => array(
					'warn' => '{Call:Lang:core:core:neukazanshab}'
				),
				'eval' => 'return array("additional" => $GLOBALS["Core"]->DB->columnFetch(array("templates", "name", "folder", "`type`=\'admin\'")));'
			)
		);

		$return[0]['core']['template']['adminLanguage'] = array(
			'type' => 'select',
			'text' => 'Язык администратора',
			'vars' => array('eval' => 'return array("additional" => Library::array_merge(array("" => "По умолчанию"), $GLOBALS["Core"]->getLangs()));')
		);

		$return[0]['core']['template']['templateSource'] = array(
			'value' => 'folder',
			'type' => 'radio',
			'text' => '{Call:Lang:core:core:khranitshabl}',
			'vars' => array(
				'matrix' => array(
					'warn' => '{Call:Lang:core:core:neukazanisto}',
					'additional' => array(
						'db' => '{Call:Lang:core:core:bazedannykh}',
						'folder' => '{Call:Lang:core:core:papke}'
					),
					'template' => 'line'
				),
				'setEval' => 'if($GLOBALS["Core"]->getParam("templateSource") != $params["core_0_templateSource"]) return mod_admin_core::insertTemplateIntoSource($this, $params["core_0_templateSource"]);'	//Перемещаем шаблоны между БД и папками
			),
		);

		$return[0]['core']['admins']['adminAccessType'] = array(
			'value' => 'allow',
			'type' => 'radio',
			'vars' => array(
				'matrix' => array(
					'warn' => '{Call:Lang:core:core:neukazanspos}',
					'additional' => array(
						'allow' => '{Call:Lang:core:core:dostupvadmin}',
						'disallow' => '{Call:Lang:core:core:dostupvadmin1}'
					)
				)
			)
		);

		$return[0]['core']['admins']['listEntry'] = array(
			'value' => 30,
			'text' => '{Call:Lang:core:core:kolichestvoz}',
			'vars' => array(
				'matrix' => array(
					'warn_pattern' => '^\d+$',
					'warn' => '{Call:Lang:core:core:neukazanokol}'
				)
			)
		);

		$return[0]['core']['admins']['inBlock'] = array(
			'value' => 15,
			'text' => '{Call:Lang:core:core:kolichestvos}',
			'vars' => array(
				'matrix' => array(
					'warn_pattern' => '^\d+$',
					'comment' => '{Call:Lang:core:core:kogdapripost}'
				)
			)
		);

		$return[0]['core']['users']['confirmRegistration'] = array(
			'value' => 1,
			'text' => '{Call:Lang:core:core:podtverzhden}',
			'vars' => array(
				'matrix' => array(
					'additional' => array(
						'0' => '{Call:Lang:core:core:netrebuetsia}',
						'1' => '{Call:Lang:core:core:sispolzovani}',
						'2' => '{Call:Lang:core:core:podtverzhdae}'
					),
					'template' => 'line'
				)
			)
		);

		$return[0]['core']['users']['registrationCaptcha'] = array(
			'value' => 1,
			'type' => 'checkbox',
			'text' => '{Call:Lang:core:core:trebuetsiavv}'
		);

		$return[0]['core']['mails']['mailInterface'] = array(
			'value' => 'mail',
			'text' => '{Call:Lang:core:core:interfejsotp}',
			'vars' => array(
				'matrix' => array(
					'additional' => array(
						'mail' => '{Call:Lang:core:core:funktsiiamai}',
						'smtp' => '{Call:Lang:core:core:smtpserver}'
					),
					'template' => 'line'
				)
			)
		);

		$return[0]['core']['mails']['smtpHost'] = 'Хост SMTP-сервера';
		$return[0]['core']['mails']['smtpPort'] = 'Порт SMTP-сервера';
		$return[0]['core']['mails']['smtpUser'] = 'Пользователь SMTP-сервера';
		$return[0]['core']['mails']['smtpPwd'] = array(
			'text' => 'Пароль пользователя SMTP-сервера',
			'crypt' => 1
		);

		$return[0]['core']['mails']['mailFormat'] = array(
			'value' => 'text/plain',
			'text' => '{Call:Lang:core:core:formatotprav}',
			'vars' => array(
				'matrix' => array(
					'additional' => array(
						'text/html' => 'text/html',
						'text/plain' => 'text/plain',
					)
				)
			)
		);

		$return[0]['core']['mails']['mailCharset'] = array(
			'value' => 'windows-1251',
			'text' => '{Call:Lang:core:core:kodirovkaotp}',
			'vars' => array(
				'matrix' => array(
					'additional' => array(
						'utf8' => 'UTF-8',
						'windows-1251' => 'Windows-1251',
						'koi8r' => 'KOI-8r'
					)
				)
			)
		);

		$return[0]['core']['mails']['defaultEml'] = array(
			'value' => empty($params['eml']) ? '' : $params['eml'],
			'text' => '{Call:Lang:core:core:adresotpravi}'
		);

		$return[0]['core']['mails']['queueWait'] = array(
			'value' => 7200,
			'text' => '{Call:Lang:core:core:periodvremen}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:eslipismoneb}',
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['mails']['mailAttempts'] = array(
			'value' => 3,
			'text' => '{Call:Lang:core:core:chislopopyto}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:eslichislopo}',
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['mails']['mailInSession'] = array(
			'value' => 3,
			'text' => '{Call:Lang:core:core:chislopisemo}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:esliispolzue}',
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['mails']['mailInSessionOnOneEmail'] = array(
			'value' => '2',
			'text' => '{Call:Lang:core:core:chislopisemo1}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:maksimalnoec}',
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['mails']['timeBetweenMails'] = array(
			'value' => 120,
			'text' => '{Call:Lang:core:core:promezhutokm}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:naodinitotzh}',
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['mails']['mailsLive'] = array(
			'text' => 'Продолжительность хранения сведений об отправленных письмах, секунд',
			'vars' => array(
				'matrix' => array(
					'comment' => 'Если оставить поле пустым, срок хранения не ограничен'
				)
			)
		);

		$return[0]['core']['mails']['mailQueue'] = array(
			'type' => 'checkbox',
			'text' => '{Call:Lang:core:core:otpravliaemy}'
		);

		$return[0]['core']['ftp']['ftpUser'] = array(
			'value' => empty($params['ftp_user']) ? '' : $params['ftp_user'],
			'text' => '{Call:Lang:core:core:ftppolzovate}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:kakpravilovl}'
				)
			)
		);

		$return[0]['core']['ftp']['ftpPwd'] = array(
			'value' => empty($params['ftp_pwd']) ? '' : $params['ftp_pwd'],
			'crypt' => 1,
			'text' => '{Call:Lang:core:core:ftpparol}'
		);

		$return[0]['core']['ftp']['ftpHost'] = array(
			'value' => empty($params['ftp_host']) ? '' : $params['ftp_host'],
			'text' => '{Call:Lang:core:core:ftpkhost}'
		);

		$return[0]['core']['ftp']['ftpPort'] = array(
			'value' => empty($params['ftp_port']) ? '' : $params['ftp_port'],
			'text' => '{Call:Lang:core:core:ftpport}'
		);

		$return[0]['core']['ftp']['ftpFolder'] = array(
			'value' => empty($params['ftp_folder']) ? '' : $params['ftp_folder'],
			'text' => '{Call:Lang:core:core:putkpapkespa}'
		);

		$return[0]['core']['folders']['defaultFolder'] = array(
			'value' => 'storage/uploads/',
			'text' => '{Call:Lang:core:core:obshchaiapap}'
		);

		$return[0]['core']['folders']['defaultFolder'] = array(
			'value' => 'storage/uploads/',
			'text' => '{Call:Lang:core:core:obshchaiapap}'
		);

		$return[0]['core']['folders']['securityFolder'] = array(
			'value' => 'storage/security/',
			'text' => 'Папка хранения файлов не для скачивания'
		);

		$return[0]['core']['folders']['captchaFolder'] = array(
			'value' => 'storage/captcha/',
			'text' => '{Call:Lang:core:core:papkakhranen1}'
		);

		$return[0]['core']['folders']['watermarksFolder'] = array(
			'value' => 'storage/watermarks/',
			'text' => '{Call:Lang:core:core:papkakhranen2}'
		);

		$return[0]['core']['folders']['fontsFolder'] = array(
			'value' => 'storage/fonts/',
			'text' => 'Папка хранения шрифтов'
		);

		$return[0]['core']['images']['thumbWh'] = array(
			'value' => '300',
			'text' => '{Call:Lang:core:core:maksimalnaia}'
		);

		$return[0]['core']['images']['thumbHt'] = array(
			'value' => '100',
			'text' => '{Call:Lang:core:core:maksimalnaia1}'
		);

		$return[0]['core']['images']['thumbQuality'] = array(
			'value' => '70',
			'text' => 'Качество маленькой копии'
		);

		$return[0]['core']['clear']['clearForms'] = array(
			'value' => 86400,
			'text' => '{Call:Lang:core:core:srokzhiznisv}'
		);

		$return[0]['core']['clear']['clearFiles'] = array(
			'value' => 3600,
			'text' => '{Call:Lang:core:core:srokzhiznivr}'
		);

		$return[0]['core']['clear']['clearStat'] = array(
			'value' => 14,
			'text' => 'Очищать статистику администратора через, суток'
		);

		$return[0]['core']['cron']['cronCallType'] = array(
			'value' => 'http',
			'type' => 'radio',
			'text' => '{Call:Lang:core:core:sposobzapusk}',
			'vars' => array(
				'matrix' => array(
					'warn' => '{Call:Lang:core:core:neukazanspos1}',
					'template' => 'line',
					'comment' => '{Call:Lang:core:core:prihttpobras}',
					'additional' => array(
						'http' => '{Call:Lang:core:core:obrashchenie}',
						'shell' => '{Call:Lang:core:core:komandojshel}',
						'external' => '{Call:Lang:core:core:vneshnij}'
					)
				)
			)
		);

		$return[0]['core']['cron']['cronInterval'] = array(
			'value' => 60,
			'text' => '{Call:Lang:core:core:promezhutokv}',
			'vars' => array(
				'matrix' => array(
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['cron']['cronTasksLimit'] = array(
			'value' => 15,
			'text' => '{Call:Lang:core:core:maksimalnoek}',
			'vars' => array(
				'matrix' => array(
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['cron']['cronTaskTimeout'] = array(
			'value' => 60,
			'text' => '{Call:Lang:core:core:periodpovtor}',
			'vars' => array(
				'matrix' => array(
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['cron']['cronTaskQueueTimeout'] = array(
			'value' => 7200,
			'text' => '{Call:Lang:core:core:perioddeaktu}',
			'vars' => array(
				'matrix' => array(
					'comment' => '{Call:Lang:core:core:cherezukazan}',
					'warn_pattern' => '^\d+$'
				)
			)
		);

		$return[0]['core']['cron']['phpPath'] = array(
			'value' => '/usr/local/bin/php',
			'text' => 'Путь до интерпретатора PHP'
		);

		$return[0]['core']['cron']['tasksLive'] = array(
			'value' => '864000',
			'text' => 'Срок хранения сведений о заданиях, секунд',
			'vars' => array(
				'matrix' => array(
					'comment' => 'Если оставить поле пустым, срок хранения не ограничен'
				)
			)
		);


		/*
			По сайтам
		*/

		foreach($this->DB->columnFetch(array('sites', 'name', 'id')) as $i => $e){
			$return[$i]['core']['site']['charset'] = array(
				'value' => 'UTF-8',
				'text' => '{Call:Lang:core:core:kodirovkaotp1}',
				'vars' => array(
					'matrix' => array(
						'warn' => '{Call:Lang:core:core:neukazanakod}'
					)
				)
			);

			$return[$i]['core']['template']['template'] = array(
				'value' => 'default',
				'text' => '{Call:Lang:core:core:shablonpoumo}',
				'type' => 'select',
				'vars' => array(
					'matrix' => array(
						'warn' => '{Call:Lang:core:core:neukazanshab1}'
					),
					'eval' => 'return array("additional" => $GLOBALS["Core"]->DB->columnFetch(array("templates", "name", "folder", "`type`=\'main\'")));'
				)
			);

			$return[$i]['core']['template']['language'] = array(
				'text' => 'Язык сайта',
				'type' => 'select',
				'vars' => array('eval' => 'return array("additional" => Library::array_merge(array("" => "По умолчанию"), $GLOBALS["Core"]->getLangs()));')
			);

			$return[$i]['core']['url']['useSef'] = array(
				'value' => 'mod_rewrite',
				'text' => '{Call:Lang:core:core:ispolzovatse}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:core:core:sefurlsearch}',
						'additional' => array(
							'' => '{Call:Lang:core:core:neispolzovat}',
							'mod_rewrite' => '{Call:Lang:core:core:ispolzovatna}',
							'append_path' => '{Call:Lang:core:core:ispolzovatpu}',
							'append_query' => '{Call:Lang:core:core:ispolzovatur}'
						)
					)
				)
			);

			$return[$i]['core']['url']['sefUrlPattern'] = array(
				'value' => '($1/)($2/)($3)',
				'text' => '{Call:Lang:core:core:patterndliao}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:core:core:zdesvymozhet}'
					)
				)
			);

			$return[$i]['core']['url']['sefUrlVarPattern'] = array(
				'value' => '($1).($2)/',
				'text' => '{Call:Lang:core:core:patterndliap}',
				'vars' => array(
					'matrix' => array(
						'comment' => '{Call:Lang:core:core:spisokvsekhp}'
					)
				)
			);

			$return[$i]['core']['session']['sessVar'] = array(
				'value' => 'SESSID',
				'text' => '{Call:Lang:core:core:imiaperemenn}',
				'vars' => array(
					'matrix' => array(
						'warn' => '{Call:Lang:core:core:neukazanoimi}',
						'warn_pattern' => '^[A-Za-z]+\w+$'
					)
				)
			);

			$return[$i]['core']['session']['sessSend'] = array(
				'value' => 'cook',
				'text' => '{Call:Lang:core:core:sposobotprav}',
				'vars' => array(
					'matrix' => array(
						'warn' => '{Call:Lang:core:core:neukazanspos2}',
						'additional' => array(
							'cook' => '{Call:Lang:core:core:tolkocookie}',
							'get' => '{Call:Lang:core:core:tolkovstroke}',
							'both' => '{Call:Lang:core:core:ivcookieivst}'
						)
					)
				)
			);

			$return[$i]['core']['session']['sessLive'] = array(
				'value' => 3600,
				'text' => '{Call:Lang:core:core:srokzhiznise}',
				'vars' => array(
					'matrix' => array(
						'warn' => '{Call:Lang:core:core:neukazansrok}',
						'warn_pattern' => '^\d+$'
					)
				)
			);

			$return[$i]['core']['session']['sessCookieLive'] = array(
				'value' => 86400,
				'text' => '{Call:Lang:core:core:srokzhiznico}',
				'vars' => array(
					'matrix' => array(
						'warn' => '{Call:Lang:core:core:neukazancrok}',
						'warn_pattern' => '^\d+$'
					)
				)
			);

			$return[$i]['core']['date']['dateFormat'] = array(
				'value' => 'd.m.Y',
				'text' => '{Call:Lang:core:core:formatdaty}'
			);

			$return[$i]['core']['date']['timeFormat'] = array(
				'value' => 'H:i:s',
				'text' => '{Call:Lang:core:core:formatvremen}'
			);

			$return[$i]['core']['date']['UTC'] = array(
				'value' => 60 * 60 * 3,
				'text' => '{Call:Lang:core:core:chasovojpoia1}',
				'type' => 'select',
				'vars' => array(
					'eval' => 'return array("additional" => dates::UTCList());'
				)
			);

			$return[$i]['core']['api']['apiAccessType'] = array(
				'value' => 'allow',
				'type' => 'select',
				'text' => '{Call:Lang:core:core:ogranichenie}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'0' => '{Call:Lang:core:core:dostupakapin}',
							'allow' => '{Call:Lang:core:core:dostupkapira}',
							'disallow' => '{Call:Lang:core:core:dostupkapiza1}'
						)
					)
				)
			);

			$return[$i]['core']['api']['apiAccessUsers'] = array(
				'value' => 'users',
				'type' => 'select',
				'text' => '{Call:Lang:core:core:ogranichenie1}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'all' => '{Call:Lang:core:core:vse}',
							'users' => '{Call:Lang:core:core:tolkopolzova}',
							'admins' => '{Call:Lang:core:core:tolkoadminis}',
							'root' => '{Call:Lang:core:core:tolkosuperad}'
						)
					)
				)
			);

			$return[$i]['core']['api']['apiAccessAuthType'] = array(
				'value' => 'both',
				'type' => 'select',
				'text' => '{Call:Lang:core:core:autentifikat}',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'hash' => '{Call:Lang:core:core:tolkopokhesh}',
							'pwd' => '{Call:Lang:core:core:tolkopoparol}',
							'both' => '{Call:Lang:core:core:pokheshuilip}'
						)
					)
				)
			);
		}

		return $return;
	}
	public function getDefaultAdminMenu($params){
		/*
			Дефолтные настройки уровня ядра
		*/

		//Пакет суперадмина
		$return[] = array(
			'text' => '{Call:Lang:core:core:sistema}',
			'pkg' => 'core',
			'submenu' => array(
				array(
					'text' => '{Call:Lang:core:core:administriro}',
					'pkg' => 'core',
					'submenu' => array(
						array(
							'text' => '{Call:Lang:core:core:administrato}',
							'pkg' => 'core',
							'submenu' => array(
								array('text' => '{Call:Lang:core:core:gruppyadmini}', 'pkg' => 'core', 'url' => '?mod=core&func=adminsGroups'),
								array('text' => '{Call:Lang:core:core:administrato}', 'pkg' => 'core', 'url' => '?mod=core&func=admins'),
							)
						),
						array(
							'text' => '{Call:Lang:core:core:polzovateli}',
							'pkg' => 'core',
							'submenu' => array(
								array('text' => '{Call:Lang:core:core:gruppypolzov}', 'pkg' => 'core', 'url' => '?mod=core&func=userGroups'),
								array('text' => '{Call:Lang:core:core:polzovateli}', 'pkg' => 'core', 'url' => '?mod=core&func=users'),
								array('text' => 'Типы анкет пользователей', 'pkg' => 'core', 'url' => '?mod=core&func=userFormTypes'),
								array('text' => '{Call:Lang:core:core:formaregistr}', 'pkg' => 'core', 'url' => '?mod=core&func=regForm'),
							)
						),
						array('text' => '{Call:Lang:core:core:sessii}', 'pkg' => 'core', 'url' => '?mod=core&func=sessions'),
						array(
							'text' => '{Call:Lang:core:core:ogranichenie2}',
							'pkg' => 'core',
							'submenu' => array(
								array('text' => '{Call:Lang:core:core:vadminku}', 'pkg' => 'core', 'url' => '?mod=core&func=admin_access_ip'),
								array('text' => '{Call:Lang:core:core:nasajt}', 'pkg' => 'core', 'url' => '?mod=core&func=sitesAccess'),
							)
						)
					)
				),
				array(
					'text' => '{Call:Lang:core:core:sajty}',
					'pkg' => 'core',
					'url' => '?mod=core&func=sites'
				),
				array(
					'text' => '{Call:Lang:core:core:rasshireniia}',
					'pkg' => 'core',
					'submenu' => array(
						array('text' => '{Call:Lang:core:core:moduli}', 'pkg' => 'core', 'url' => '?mod=core&func=modules'),
						array('text' => '{Call:Lang:core:core:iazyki}', 'pkg' => 'core', 'url' => '?mod=core&func=languages'),
						array(
							'text' => '{Call:Lang:core:core:shablony}',
							'pkg' => 'core',
							'submenu' => array(
								array('text' => '{Call:Lang:core:core:sajta}', 'pkg' => 'core', 'url' => '?mod=core&func=templates&type=main'),
								array('text' => '{Call:Lang:core:core:adminki}', 'pkg' => 'core', 'url' => '?mod=core&func=templates&type=admin'),
								array('text' => '{Call:Lang:core:core:otdelnykhmod}', 'pkg' => 'core', 'url' => '?mod=core&func=templates&type=module'),
								array('text' => '{Call:Lang:core:core:sistemnye}', 'pkg' => 'core', 'url' => '?mod=core&func=templates&type=system'),
							)
						),
						array('text' => '{Call:Lang:core:core:plaginy}', 'pkg' => 'core', 'url' => '?mod=core&func=plugins'),
						array('text' => '{Call:Lang:core:core:ustanovit}', 'pkg' => 'core', 'url' => '?mod=core&func=packages'),
					)
				),
				array('text' => '{Call:Lang:core:core:dopolnitelny}', 'pkg' => 'core', 'url' => '?mod=core&func=database'),
				array(
					'text' => '{Call:Lang:core:core:zagruzkafajl}',
					'pkg' => 'core',
					'submenu' => array(
						array('text' => '{Call:Lang:core:core:papki}', 'pkg' => 'core', 'url' => '?mod=core&func=folders'),
						array('text' => '{Call:Lang:core:core:standartyizo}', 'pkg' => 'core', 'url' => '?mod=core&func=image_standarts'),
						array('text' => '{Call:Lang:core:core:shrifty}', 'pkg' => 'core', 'url' => '?mod=core&func=fonts'),
						array('text' => '{Call:Lang:core:core:vodianyeznak}', 'pkg' => 'core', 'url' => '?mod=core&func=watermarks'),
					)
				),
				array(
					'text' => 'CAPTCHA',
					'pkg' => 'core',
					'submenu' => array(
						array('text' => '{Call:Lang:core:core:standarty}', 'pkg' => 'core', 'url' => '?mod=core&func=captcha_standarts'),
						array('text' => '{Call:Lang:core:core:fony}', 'pkg' => 'core', 'url' => '?mod=core&func=captchaBackgrounds')
					)
				),
				array(
					'text' => '{Call:Lang:core:core:pisma}',
					'pkg' => 'core',
					'submenu' => array(
						array('text' => '{Call:Lang:core:core:pisma}', 'pkg' => 'core', 'url' => '?mod=core&func=mails'),
						array('text' => '{Call:Lang:core:core:shablonypise}', 'pkg' => 'core', 'url' => '?mod=core&func=mailTemplates'),
						array('text' => '{Call:Lang:core:core:rassylka}', 'pkg' => 'core', 'url' => '?mod=core&func=mailsSend'),
					)
				),
				array(
					'text' => '{Call:Lang:core:core:zadachi}',
					'pkg' => 'core',
					'submenu' => array(
						array('text' => '{Call:Lang:core:core:raspisanie}', 'pkg' => 'core', 'url' => '?mod=core&func=cron'),
						array('text' => '{Call:Lang:core:core:rezultatvypo}', 'pkg' => 'core', 'url' => '?mod=core&func=tasks'),
					)
				),
				array('text' => '{Call:Lang:core:core:dostupkapi}', 'pkg' => 'core', 'url' => '?mod=core&func=api'),
				array(
					'text' => '{Call:Lang:core:core:upravlenieur}',
					'pkg' => 'core',
					'submenu' => array(
						array('text' => '{Call:Lang:core:core:pravilaformi}', 'pkg' => 'core', 'url' => '?mod=core&func=urlGenRights'),
						array('text' => '{Call:Lang:core:core:pravilazamen}', 'pkg' => 'core', 'url' => '?mod=core&func=url'),
					)
				),
				array('text' => '{Call:Lang:core:core:migratsiia}', 'pkg' => 'core', 'url' => '?mod=core&func=migration'),
				array('text' => '{Call:Lang:core:core:otpravitzapr}', 'pkg' => 'core', 'url' => '?mod=core&func=db'),
				array('text' => '{Call:Lang:core:core:nastrojkisis}', 'pkg' => 'core', 'url' => '?mod=core&func=settings'),
				array('text' => '{Call:Lang:core:core:oprogramme}', 'pkg' => 'core', 'url' => '?mod=core&func=about'),
			)
		);

		$return['main'] = array(
			'text' => '{Call:Lang:core:core:lichnyenastr}',
			'pkg' => 'main',
			'submenu' => array(
				array('text' => '{Call:Lang:core:core:nastrojkiadm}', 'pkg' => 'main', 'url' => '?mod=main&func=prefs'),
				array('text' => '{Call:Lang:core:core:sluzhebnyekn}', 'pkg' => 'main', 'url' => '?mod=main&func=admin_buttons'),
				array('text' => '{Call:Lang:core:core:statistikapo}', 'pkg' => 'main', 'url' => '?mod=main&func=myStat'),
			)
		);

		return $return;
	}

	public function getDefaultMailTemplates($params){		/*
			Шаблоны писем
		*/
		$return['main']['registrationCode'] = array(
			'text' => '{Call:Lang:core:core:otpravkakoda}',
			'subj' => '{Call:Lang:core:core:vyvodnomshag}',
			'body' => '{Call:Lang:core:core:zdrastvujten}',
		);

		$return['main']['registration'] = array(
			'text' => '{Call:Lang:core:core:registratsii1}',
			'subj' => '{Call:Lang:core:core:vashiregistr}',
			'body' => '{Call:Lang:core:core:zdrastvujten1}',
		);

		$return['main']['recoverPwdLink'] = array(
			'text' => '{Call:Lang:core:core:ssylkadliavo}',
			'subj' => '{Call:Lang:core:core:vashnovyjpar}',
			'body' => '{Call:Lang:core:core:zdrastvujten2}',
		);

		$return['main']['recoverPwd'] = array(
			'text' => '{Call:Lang:core:core:vosstanovlen}',
			'subj' => '{Call:Lang:core:core:vashnovyjpar}',
			'body' => '{Call:Lang:core:core:zdrastvujten3}',
		);

		return $return;
	}

	public function getDefaultFonts($params){		return array('dejavusans.ttf' => 'Deja Vu Sans');	}

	public function setDefaultLinks($DB, $type, $params){		/*
			Дефолтные типовые ссылки
		*/

		$cmsObj = $GLOBALS['Core']->callModule($params['cms'], false, array(), true);
		$cmsObj->insertMenuLinks(
			array(
				'ident' => 'cabinate',
				'mod' => 'main',
				'name' => '{Call:Lang:core:core:parametry}',
				'link' => 'index.php?mod=main&func=cabinate',
				'show' => '1',
				'usersonly' => '1'
			)
		);	}

	public function getDefaultCaptchas($params){		return array(
			'main' => array(
				'text' => '{Call:Lang:core:core:osnovnoj}',
				'backgrounds' => ',captchabg.jpg,',
				'captcha_type' => 't',
				'len' => '3',
				'len_to' => '6',
				'direction' => 'l',
				'start_position' => '10',
				'start_position_to' => '20',
				'start_position_vertical' => '40',
				'start_position_vertical_to' => '110',
				'fonts' => ',dejavusans.ttf,',
				'font_size' => '12',
				'font_size_to' => '18',
				'font_blur' => '4',
				'font_blur_to' => '8',
				'angle' => '-45',
				'angle_to' => '45',
				'letter_offset' => '-10',
				'letter_offset_to' => '10',
				'letter_vertical_offset' => '-10',
				'letter_vertical_offset_to' => '20',
				'color' => '000000',
				'color_to' => 'FFFFFF',
				'transparent' => '20',
				'transparent_to' => '50'
			)
		);	}

	public function getDefaultCronjobs($params){		/*
			крон-задачи
		*/

		$return['core']['calendar'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '0,4,8,12,16,20',
			'minute' => '30',
			'command' => 'return $GLOBALS["Core"]->clear();'."\n",
			'comment' => '{Call:Lang:core:core:chistkaotmus}',
		);

		$return['core']['mailer'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '*',
			'minute' => '*',
			'tick' => '1',
			'command' => 'return $GLOBALS["Core"]->mailer();'."\n",
			'comment' => '{Call:Lang:core:core:rassylkapise}',
		);

		return $return;
	}
}

?>