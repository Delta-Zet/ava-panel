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


class installModulesTicket extends InstallModuleObject implements InstallModuleInterface{

	public function Install(){
		/*
		  Пункты меню админки для CMS
		*/

		$this->createAllTables();
		$this->setAllDefaults($this->obj->values);
		$this->setAllSupports(array(1 => array('name' => $this->obj->values['login'], 'departments' => ',@all,')));

		$this->setAllDepartments($this->getAllDepartments($this->obj->values));
		$this->setAllStatuses($this->getAllStatuses($this->obj->values));
		return true;
	}

	public function prepareInstall(){
		return true;
	}

	public function checkInstall(){
		return true;
	}

	public function Uninstall(){
		$this->dropAllTables();
		$this->dropAllDefaults();
		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update($oldVersion, $newVersion){
		switch(true){
			case !Library::versionCompare('0.0.1.9', $oldVersion):
				$this->iObj->DB->Alter(array('tickets', array('modify' => array('status' => 'VARCHAR(256)'))));
				$this->iObj->DB->Upd(array('tickets', array('status' => 'noanswer'), "`status`='0'"));
		}

		$v = $this->obj->values;
		$v['sites'] = $this->iObj->Core->getModuleSites($this->prefix);
		$this->updateAllTables();

		$this->updateAllDefaults($v);
		$this->setAllStatuses($this->getAllStatuses($this->obj->values));
		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}

	public function getTables(){
		/*
			Создает таблицы
		*/

		$return['departments'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(32)',
				'text' => '',
				'transmit_type' => 'TINYINT',
				'access_type' => 'TINYINT',		//Тип доступа: 0 - все, 1 - пользователи, 2 - специальные настройки
				'access' => 'TEXT',
				'sort' => '',
				'show' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text')
				)
			)
		);

		$return['tickets'] = array(
			array(
				'id' => '',
				'user_id' => 'INT',
				'eml' => '',
				'support_id' => 'INT',
				'department' => '',
				'date' => '',
				'name' => '',
				'status' => 'VARCHAR(256)',
				'status_by' => 'VARCHAR(8)',			//user или support
				'status_priv' => 'INT(1)',
				'code' => '',
				'vars' => '',
				'show' => ''							//Пока стоит - виден, как убрали - удален.
			)
		);

		$return['messages'] = array(
			array(
				'id' => '',
				'ticket_id' => '',
				'date' => '',
				'author' => 'INT',						//ID автора (user_id или support_id)
				'author_type' => 'VARCHAR(8)',			//user или support
				'text' => 'TEXT',
				'attaches' => 'TEXT'
			)
		);

		$return['message_status'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'use_support' => 'CHAR(1)',
				'use_user' => 'CHAR(1)',
				'auto_set_open' => 'CHAR(1)',				//Выставлять автоматом: при создании тикета;
				'auto_set_show_user' => 'CHAR(1)',			//при просмотре тикета пользователем;
				'auto_set_show_support' => 'CHAR(1)',		//при просмотре тикета админом;
				'auto_set_answer_user' => 'CHAR(1)',		//при внесении ответа администратором;
				'auto_set_answer_support' => 'CHAR(1)',		//при внесении ответа пользователем;
				'rights' => '',								//Права если статус выставлен: пользователю можно просмотреть тикет, ответить на тикет
				'superpriv' => 'INT(1)',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['message_form'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => '',
				'vars' => '',
				'style' => 'TINYINT',			//Стиль: 0 - все, 1 - только незарегистрированные пользователи, 2 - только зарегистрированные пользователи, 3 - специальные настройки (по департаментам), 4 - саппорт
				'show_style' => 'TINYINT',		//0 - Показывать введенное значение, 1 - Показывать только саппорту, 2 - Не показывать
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['supports'] = array(
			array(
				'id' => '',
				'admin_id' => 'INT',
				'date' => '',
				'name' => '',
				'departments' => '',
				'status' => 'TINYINT',					//Статус: 0 - не работает, 1 - работает, 2 - "в отпуске"
				'auto_status_change' => 'CHAR(1)',		//Может сам себя вернуть из отпуска
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('admin_id')
				)
			)
		);

		return $return;
	}

	public function getDefaultSettings($params){
		$return = array();

		foreach($params['sites'] as $i => $e){
			$return[$i][$this->prefix]['']['supportMessagesAttachFolder'] = array(
				'value' => 'storage/attaches/',
				'text' => '{Call:Lang:modules:ticket:papkadliaatt}',
			);

			$return[$i][$this->prefix]['']['attachesOnForm'] = array(
				'value' => '2',
				'text' => 'Число приаттаченных файлов в форме',
			);

			$return[$i][$this->prefix]['']['captchaStyle'] = array(
				'text' => 'Запрашивать CAPTCHA при отправке новых запросов',
				'type' => 'select',
				'value' => 'anonymous',
				'vars' => array(
					'matrix' => array(
						'additional' => array(
							'' => 'Нет',
							'anonymous' => 'Только для не зарегистрированных пользователей',
							'all' => 'Для всех'
						)
					)
				),
			);

			$return[$i][$this->prefix]['']['notifyUser'] = array(
				'value' => '1',
				'text' => 'Оповещать пользователя о новых сообщениях на e-mail',
				'type' => 'checkbox'
			);

			$return[$i][$this->prefix]['']['notifyAdmin'] = array(
				'value' => '1',
				'text' => 'Оповещать администратора о новых сообщениях на e-mail',
				'type' => 'checkbox'
			);
		}

		return $return;
	}

	public function getDefaultMailTemplates($params){
		$return[$this->prefix]['newMessage'] = array(
			'text' => 'Оповещение пользователя об ответе в службу поддержки',
			'subj' => 'Запрос #{id}: {name}. Поступило новое сообщение.',
			'body' => "Здравствуйте!\n\nНа ваш запрос с темой {name} есть новое сообщение. Для просмотра пройдите по ссылке {link}",
		);

		$return[$this->prefix]['newMessage4admin'] = array(
			'text' => 'Оповещение администратора об ответе в службу поддержки',
			'subj' => 'Запрос #{id}. Поступило новое сообщение.',
			'body' => 'Есть новое сообщение в теме "{name}". Для просмотра пройдите по ссылке: {link}',
		);

		return $return;
	}

	public function getDefaultAdminMenu($params){
		/*
			Дефолтные настройки уровня ядра
		*/

		$return[] = array(
			'text' => $params['text_'.$this->params['name']],
			'pkg' => $this->prefix,
			'submenu' => array(
				array('text' => '{Call:Lang:modules:ticket:zaprosy}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=tickets'),
				array('text' => '{Call:Lang:modules:ticket:razdely}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=departments'),
				array('text' => '{Call:Lang:modules:ticket:statusyzapro}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=message_status'),
				array('text' => '{Call:Lang:modules:ticket:formazaprosa}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=message_form'),
				array('text' => '{Call:Lang:modules:ticket:sotrudnikipo}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=supports'),
			)
		);

		return $return;
	}

	public function getDefaultModuleLinks($params){
		return array(
 			array(
				'name' => 'ticket',
				'text' => '{Call:Lang:modules:ticket:podderzhka}',
				'mod' => $this->prefix,
				'url' => 'index.php?mod='.$this->prefix.'&func=ticket',
				'usedCmsLevel' => array('mainmenu', 'menu1')
			)
		);
	}

	public function getAllDepartments($params){
		return array(
			'presale' => '{Call:Lang:modules:ticket:predprodazhn}',
			'pay' => '{Call:Lang:modules:ticket:voprosyoplat}',
			'support' => '{Call:Lang:modules:ticket:voprosypousl}'
		);
	}

	public function getAllStatuses($params){
		return array(
			'noanswer' => array(
				'text' => 'Не отвечен',
				'auto_set_open' => 1,
				'auto_set_answer_user' => 1
			),
			'wait' => array(
				'text' => 'Просмотрен, в ожидании',
				'auto_set_show_support' => 1
			),
			'answer' => array(
				'text' => 'Отвечен, не прочитан',
				'auto_set_answer_support' => 1
			),
			'view_user' => array(
				'text' => 'Прочитан пользователем',
				'auto_set_show_user' => 1
			),
			'close' => array(
				'text' => 'Закрыт',
				'use_support' => 1,
				'use_user' => 1,
				'rights' => ',answer,',
				'superpriv' => 1
			),
		);
	}

	public function setAllDepartments($params){
		$params = $this->paramReplaces($params);
		$j = $this->iObj->DB->cellFetch(array('departments', 'sort', "", "`sort` DESC")) + 1;

		foreach($params as $i => $e){
			if(!is_array($e)) $e = array('text' => $e);

			$e['name'] = $i;
			$e['sort'] = $j;
			$e['show'] = 1;

			$this->iObj->DB->Ins(array('departments', $e, "`name`='$i'"));
			$j ++;
		}
	}

	public function setAllSupports($params){
		$params = $this->paramReplaces($params);
		$t = time();
		$j = $this->iObj->DB->cellFetch(array('supports', 'sort', "", "`sort` DESC")) + 1;

		foreach($params as $i => $e){
			if(!is_array($e)) $e = array('name' => $e);

			$e['admin_id'] = $i;
			$e['date'] = $t;
			$e['status'] = 1;
			$e['sort'] = $j;

			$this->iObj->DB->Ins(array('supports', $e, "`name`='$i'"));
			$j ++;
		}
	}

	public function setAllStatuses($params){
		$params = $this->paramReplaces($params);
		$j = $this->iObj->DB->cellFetch(array('message_status', 'sort', "", "`sort` DESC")) + 1;

		foreach($params as $i => $e){
			if(!is_array($e)) $e = array('text' => $e);
			if(!isset($e['use_support'])) $e['use_support'] = 1;
			$e['name'] = $i;

			$e['sort'] = $j;
			$this->iObj->DB->Ins(array('message_status', $e, "`name`='$i'"));
			$j ++;
		}
	}
}

?>