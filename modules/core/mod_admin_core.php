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


class mod_admin_core extends gen_core{

	protected function func_main(){
	}


	/********************************************************************************************************************************************************************

																		Группы админов и их права

	*********************************************************************************************************************************************************************/

	protected function func_adminsGroups(){
		/*
			Группы админов и их права на доступ
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'admins_groups_new',
						'adminsGroupsNew',
						array('caption' => '{Call:Lang:core:core:dobavitgrupp}')
					),
					'admins_groups'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'admins_groups_list',
					array(
						'req' => array( 'admins_groups', array('id', 'name', 'show', 'ip_access_type'), '', "`name`" ),
						'form_actions' => array(
							'suspend' => '{Call:Lang:core:core:zakrytdostup}',
							'unsuspend' => '{Call:Lang:core:core:vosstanovitd}',
							'delete' => '{Call:Lang:core:core:udalit}'
						),
						'actions' => array(
							'name' => 'adminsGroupsData',
							'rights' => 'adminsGroupsRights',
							'ip' => 'adminsGroupsIp'
						),
						'action' => 'adminsGroupsActions',
						'searchForm' => array(
							'searchFields' => array('name' => '{Call:Lang:core:core:poimeni}', 'show' => '{Call:Lang:core:core:podostupuvad}'),
							'orderFields' => array('name' => '{Call:Lang:core:core:imeni}')
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:vsegruppyadm}'
					)
				)
			)
		);
	}

	protected function func_adminsGroupsNew(){
		/*
			Нвая группа
		 */

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->isUniq('admins_groups', array('name' => '{Call:Lang:core:core:takoeimiauzh}'), $id);
		$return = $this->typeIns('admins_groups', $this->fieldValues(array('name', 'show', 'ip_access_type')), 'adminsGroups');

		foreach($this->DB->columnFetch(array('admins', 'user_id', '', "`group`='$id'")) as $userId){
			$this->Core->reauthUserSession($userId);
		}

		if(!$id) $this->redirect('adminsGroupsRights&id='.$return);
		return $return;
	}

	protected function func_adminsGroupsData(){
		/*
			Изменить данные
		 */

		$this->pathFunc = 'adminsGroups';

		$this->typeModify(
			array('admins_groups', '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			'admins_groups',
			'adminsGroupsNew',
			array('caption' => '{Call:Lang:core:core:izmenitparam}')
		);
	}

	protected function func_adminsGroupsActions(){
		/*
			Массовые действия над списком групп
		 */

		if($return = $this->typeActions('admins_groups', 'adminsGroups')){
			foreach($this->values['entry'] as $i => $e){
				foreach($this->DB->columnFetch(array('admins', 'user_id', '', "`group`='$i'")) as $id){
					$this->Core->reauthUserSession($id);
				}
			}
		}
		return $return;
	}

	protected function func_adminsGroupsIp(){
		/*
			Ограничение доступа по IP
		*/

		$id = db_main::Quot($this->values['id']);
		$name = $this->DB->cellFetch(array('admins_groups', 'name', "`id`='$id'"));
		$fields = array();

		if(!empty($this->values['ip'])){
			$fields = array(
				'ip' => $this->values['ip'],
				'type' => $this->values['type'],
				'admins_groups_id' => $id
			);
		}

		$this->typicalMain(
			array(
				'name' => 'admin_access_ip',
				'func' => 'adminsGroupsIp&id='.$id,
				'caption' => '{Call:Lang:core:core:dobavitipvsp}',
				'listParams' => array(
					'req' => array('admin_access_ip', '*', "`admins_groups_id`='$id'"),
					'searchForm' => array(
						'searchFields' => array(
							'ip' => '{Call:Lang:core:core:ipiliegochas}',
							'type' => array('' => '{Call:Lang:core:core:vse}', 'allow' => '{Call:Lang:core:core:razreshennye}', 'disallow' => '{Call:Lang:core:core:zapreshchenn}')
						),
						'orderFields' => array('ip' => 'IP'),
						'searchParams' => array(
							'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$id
						)
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokipskot:'.Library::serialize(array($name)).'}'
				),
				'fields' => $fields
			)
		);

		$this->pathFunc = 'adminsGroups';
		$this->funcName = '{Call:Lang:core:core:ogranichenie3:'.Library::serialize(array($name)).'}';
	}

	protected function func_adminsGroupsRights(){
		/*
			Права администратора
			Права ограничиваются по сайтам, модулям и внутримодульно в зависимости от настроек. Выставляются как:
			0 Прав нет.
			1 Право доступа и просмотра (гость).
			2 Право изменения информации.
			3 Право добавлять пользователей имеющих доступ в этот раздел и предоставлять им права.

			права хранятся в БД как сериализованный массив:
			$rights[site] = 0 | 1 | 2 | 3    -     Права на доступ к любому модулю сайта с соответствующим статусом
			$rights[site][mod] = 0 | 1 | 2 | 3    -     Права на доступ к модулю
			$rights[site][mod][intIdent] = 0 | 1 | 2 | 3      -      Права для соответствующей внутренней позиции

			ключевые слова в поле rights: as_group (права как у группы)
		 */

		$data = $this->DB->rowFetch(array('admins_groups', array('name', 'rights'), "`id`='{$this->values['id']}'"));
		$this->pathFunc = 'adminsGroups';
		$this->funcName = '{Call:Lang:core:core:ustanovitpra:'.Library::serialize(array($data['name'])).'}';

		return $this->getRightsTable(
			'adminsGroupsRights2',
			array('caption' => $this->funcName),
			$this->getRightsValues(Library::unserialize($data['rights'])),
			array('id' => $this->values['id'])
		);
	}

	protected function func_adminsGroupsRights2(){
		if(!$this->check()) return false;
		$return = $this->DB->Upd(array('admins_groups', array('rights' => $this->getRightsArray()), "`id`='{$this->values['id']}'"));
		$this->refresh('adminsGroups');

		foreach($this->DB->columnFetch(array('admins', 'user_id', '', "`group`='{$this->values['id']}'")) as $id){
			$this->Core->reauthUserSession($id);
		}
		return $return;
	}


	/********************************************************************************************************************************************************************

																	Обслуживание прав пользователей

	*********************************************************************************************************************************************************************/

	private function getRightsValues($values){
		/*
			Создает список значений для формы прав из массива хранящегося в БД
		*/

		$return = array();
		foreach($values as $i => $e){
			if(!is_array($e)) $return['rights_'.$i] = $e;
			else{
				$return['rights_'.$i] = 4;
				foreach($e as $i1 => $e1){
					$return['rights_'.$i.'_'.$i1] = $e1;
				}
			}
		}

		return $return;
	}

	private function getRightsArray(){
		/*
			Создает список значений для формы прав из массива хранящегося в БД
		*/

		$return = array();
		foreach($this->Core->getModules() as $i => $e){
			if(!isset($this->values['rights_'.$i])) $this->values['rights_'.$i] = 0;
			if($this->values['rights_'.$i] != 4) $return[$i] = $this->values['rights_'.$i];
			else{
				foreach($this->Core->callModule($i)->getFuncs() as $e1){
					$e1 = regExp::Replace("/^func_/", '', $e1, true);
					if(isset($this->values['rights_'.$i.'_'.$e1])) $return[$i][$e1] = $this->values['rights_'.$i.'_'.$e1];
				}
			}
		}

		return $return;
	}

	private function getRightsTable($func, $params, $values = array(), $hiddens = array()){
		/*
			Создает таблицу прав пользователей. Используется многоблочная таблица с разбивкой по сайтам.

			Права могут быть выставлены:
				0 Прав нет.
				1 Право доступа и просмотра (гость).
				2 Право изменения информации.
				3 Право добавлять пользователей имеющих доступ в этот раздел и предоставлять им права.

			Могут выставлятся:
				На весь сайт
				На пакеты относящиеся к этому сайту
				На отдельные виды действий
		 */

		$form = $this->newForm('rightsList', $func, $params);
		$j = 0;

		foreach($this->Core->getModules() as $i => $e){
			$methods = array();
			foreach($this->Core->callModule($i)->getFuncs() as $e1){
				if($e1 == 'runPlugin' || $e1 == 'sortListParams') continue;
				$f = regExp::Replace("/^func_/", '', $e1, true);
				if($e1 != $f){
					$methods[$f] = $f;
				}
			}

			$form->setParam('caption'.$j, $e);
			$this->addFormBlock($form, 'admin_rights', array('funcs' => $methods, 'module' => $i, 'func' => $this->func), array(), 'block'.$j);
			$j ++;
		}

		$this->setContent($this->getFormText($form, $values, $hiddens, 'multiblock'));
	}




	/********************************************************************************************************************************************************************

																				Управление администраторами

	*********************************************************************************************************************************************************************/

	protected function func_admins(){
		/*
			Добавляет нового администратора. Администратору может соответствовать пользователь из списка пользователей, однако для доступа в админку используется
			собственный логин и пароль админа. При управлении профилем при этом возможности пользовательской и админской частей доступны раздельно.
		*/

		$groups = $this->getAdminsGroups();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'adminsNew',
						'adminsNew',
						array('caption' => '{Call:Lang:core:core:novyjadminis}')
					),
					'admins',
					array('groups' => $groups)
				)
			)
		);

		$p = $this->DB->getPrefix();
		$t1 = $p.'admins';
		$t2 = $p.'admins_groups';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'admins_list',
					array(
						'req' => "SELECT t1.*, t2.name AS group_name, t2.show AS group_show FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.group=t2.id ".
							(empty($this->values['in_search']) ? 'ORDER BY t1.login' : ''),
						'form_actions' => array(
							'suspend' => '{Call:Lang:core:core:zakrytdostup}',
							'unsuspend' => '{Call:Lang:core:core:vosstanovitd}',
							'delete' => '{Call:Lang:core:core:udalit}'
						),
						'actions' => array(
							'login' => 'adminsData',
							'stat' => 'adminsStat',
							'rights' => 'adminsRights',
							'ip' => 'adminsIp'
						),
						'action' => 'adminsActions',
						'searchForm' => array(
							'searchFields' => array(
								'login' => '{Call:Lang:core:core:login}',
								'eml' => 'E-mail',
								'date' => '{Call:Lang:core:core:dataregistra}',
								'group' => '{Call:Lang:core:core:gruppa}',
								'show' => '{Call:Lang:core:core:dostupvadmin2}'
							),
							'orderFields' => array(
								'login' => '{Call:Lang:core:core:loginu}',
								'eml' => 'e-mail',
								'date' => '{Call:Lang:core:core:dateregistra}',
								'group_name' => '{Call:Lang:core:core:gruppe}'
							),
							'searchPrefix' => 't1',
							'searchMatrix' => array(
								'group' => array(
									'type' => 'select',
									'additional' => Library::array_merge($groups, array('' => '{Call:Lang:core:core:vse}', '0' => '{Call:Lang:core:core:bezgruppy}'))
								)
							)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:vseadministr}'
					)
				)
			)
		);
	}

	protected function func_adminsNew(){
		/*
			Добавляет в список админстратора
		*/

		$login = db_main::Quot($this->values['login']);
		$pwd = isset($this->values['pwd']) ? db_main::Quot($this->values['pwd']) : '';
		$eml = db_main::Quot($this->values['eml']);
		$id = db_main::Quot(empty($this->values['modify']) ? '' : $this->values['modify']);

 		if($this->DB->cellFetch(array('admins', 'id', "`login`='$login' AND `id`!='$id'"))){
			$errors = $this->setError('login', '{Call:Lang:core:core:takojloginad}');
		}

		if(!$this->check()){
			return false;
		}

		$fields = array(
			'eml' => $eml,
			'show' => empty($this->values['show']) ? 0 : $this->values['show'],
			'group' => $this->values['group'],
			'ip_access_type' => $this->values['ip_access_type']
		);

		$userLogin = db_main::Quot(empty($this->values['user_login']) ? $this->values['login'] : $this->values['user_login']);
		if(!$userId = $this->DB->cellFetch(array('users', 'id', "`login`='$userLogin'"))){
			$mMod = $this->Core->callModule('main');
			$userId = $mMod->addUser(array('login' => $userLogin, 'eml' => $this->values['eml'], 'pwd' => $this->values['pwd'], 'show' => 1));
		}

		if($pwd) $fields['pwd'] = Library::getPassHash($login, $pwd, $this->DB->cellFetch(array('users', 'code', "`login`='$userLogin'")));
		if($login) $fields['login'] = $login;

		if(!$userId){
			$this->back('admins', '{Call:Lang:core:core:oshibkaneuda}');
			return false;
		}

		$fields['user_id'] = $userId;
		if(!$id){
			$fields['rights'] = array();
			if($fields['group']) foreach($this->Core->getModules() as $i => $e) $fields['rights'][$i] = "5";
			$fields['date'] = time();
			$reqType = 'Ins';
		}
		else{
			$reqType = 'Upd';
		}

		$id = $this->DB->$reqType(array('admins', $fields, "`id`='$id'"));
		if($reqType == 'Ins' && !$fields['group']){
			$this->redirect('adminsRights&id='.$id);
		}
		else{
			$this->refresh('admins');
			$this->Core->reauthUserSession($userId);
		}

		return $id;
	}

	protected function func_adminsActions(){
		/*
			Выполняет определенные действия над админами
		*/

		$users = $this->DB->columnFetch(array('admins', 'user_id', "id", $this->getEntriesWhere()));

		if($return = $this->typeActions('admins', 'admins')){
			if($this->values['action'] == 'delete' && $users) $this->DB->Del(array('users', $this->getEntriesWhere(Library::arrayValues2keys($users, 1))));

			foreach($this->values['entry'] as $i => $e){
				if($this->values['action'] != 'delete') $this->Core->reauthUserSession($users[$i]);
				else $this->Core->unsetUserSession($users[$i]);
			}
		}
		return $return;
	}

	protected function func_adminsData(){
		/*
			Управление данными пользовательского аккаунта
			1. Изменение имени и e-mail пользователя
			2. Изменение пароля пользователя
			3. Отображение статистики
			4. Ссылки на Блокирование / Разблокирование / Удаление / Статистику
		*/

		$id = db_main::Quot($this->values['id']);
		$t1 = AVA_DB_PREF.'admins';
		$t2 = AVA_DB_PREF.'users';

		$values = $this->DB->rowFetch("SELECT t1.*, t2.login AS user_login FROM $t1 AS t1, $t2 AS t2 WHERE t1.id='$id' AND t1.user_id=t2.id LIMIT 1");
		$this->funcName = '{Call:Lang:core:core:administrato4:'.Library::serialize(array($values['login'])).'}';
		$this->pathFunc = 'admins';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'admins',
						'adminsNew',
						array('caption' => '{Call:Lang:core:core:dannye:'.Library::serialize(array($values['login'])).'}')
					),
					array('admins', array('login' => array('disabled' => 1))),
					array('groups' => $this->getAdminsGroups()),
					array('pwd', 'cpwd')
				),
				$values,
				array('modify' => $id),
				'big'
			)
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'admins_newpwd',
						'adminsPwd',
						array('caption' => '{Call:Lang:core:core:smenaparolia}')
					),
					'type_newpwd'
				),
				array(),
				array('modify' => $id),
				'big'
			)
		);
	}

	protected function func_adminsPwd(){
		if(!$this->check()){
			return false;
		}

		$id = db_main::Quot($this->values['modify']);
		$pwd = $this->values['pwd'];
		$p = $this->DB->getPrefix();
		$r = $this->DB->rowFetch("SELECT t1.login, t2.code FROM {$p}admins AS t1 LEFT JOIN {$p}users AS t2 ON t1.user_id=t2.id WHERE t1.id='$id' LIMIT 1");

		$fields['pwd'] = Library::getPassHash($r['login'], $pwd, $r['code']);
		$this->DB->Upd(array('admins', $fields, "id='$id'"));
		$this->Core->unsetUserSession($id);
		$this->refresh('adminsData&id='.$id);
	}

	protected function func_adminsStat(){
		$this->funcName = '{Call:Lang:core:core:statistika}';
		$this->pathFunc = 'admins';

		$modObj = $this->Core->callModule('main', false, $this->values);
		$modObj->showStat($this->values['id'], $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$this->values['id']);

		$content = $modObj->getContentVar('admin_stat_list');
		$this->setContent($content);
		$this->setContent($content, 'admin_stat_list');
	}

	protected function func_adminsRights(){
		/*
			Права админов
		*/

		$data = $this->DB->rowFetch(array('admins', array('login', 'rights'), "`id`='{$this->values['id']}'"));
		$this->pathFunc = 'admins';
		$this->funcName = '{Call:Lang:core:core:ustanovitpra1:'.Library::serialize(array($data['login'])).'}';

		$this->getRightsTable(
			'adminsRights2',
			array('caption' => $this->funcName),
			$this->getRightsValues(Library::unserialize($data['rights'])),
			array('id' => $this->values['id'])
		);
	}

	protected function func_adminsRights2(){
		if(!$this->check()) return false;
		$return = $this->DB->Upd(array('admins', array('rights' => $this->getRightsArray()), "`id`='{$this->values['id']}'"));
		$this->refresh('admins');
		$this->Core->reauthUserSession($this->DB->cellFetch(array('admins', 'user_id', "`id`='{$this->values['id']}'")));
		return $return;
	}

	protected function func_adminsIp(){
		/*
			Ограничение доступа по IP
		*/

		$id = db_main::Quot($this->values['id']);
		$name = $this->DB->cellFetch(array('admins', 'login', "`id`='$id'"));
		$fields = array();

		if(!empty($this->values['ip'])){
			$fields = array(
				'ip' => $this->values['ip'],
				'type' => $this->values['type'],
				'admins_id' => $id
			);
		}

		$this->typicalMain(
			array(
				'name' => 'admin_access_ip',
				'func' => 'adminsIp&id='.$id,
				'caption' => '{Call:Lang:core:core:dobavitipvsp}',
				'listParams' => array(
					'req' => array('admin_access_ip', '*', "`admins_id`='$id'"),
					'searchForm' => array(
						'searchFields' => array(
							'ip' => '{Call:Lang:core:core:poipiliegoch}',
							'type' => array('' => '{Call:Lang:core:core:vse}', 'allow' => '{Call:Lang:core:core:razreshennye}', 'disallow' => '{Call:Lang:core:core:zapreshchenn}')
						),
						'orderFields' => array('name' => 'IP')
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokipskot1:'.Library::serialize(array($name)).'}'
				),
				'fields' => $fields
			)
		);

		$this->pathFunc = 'admins';
		$this->funcName = '{Call:Lang:core:core:ogranichenie3:'.Library::serialize(array($name)).'}';
	}


	/********************************************************************************************************************************************************************

																			Управление пользователями

	*********************************************************************************************************************************************************************/

	protected function func_userGroups(){
		/*
			Группы пользователей
		*/

		$this->typicalMain(
			array(
				'func' => 'userGroups',
				'name' => 'users_groups',
				'table' => 'users_groups',
				'caption' => '{Call:Lang:core:core:dobavitgrupp}',
				'listParams' => array(
					'req' => array('users_groups', '*', "", "`name`"),
					'actions' => array('text' => 'userGroups&type_action=modify'),
					'searchForm' => array('searchFields' => array('name' => 'Имя', 'text' => 'Идентификатор'))
				),
				'listParams2' => array('caption' => '{Call:Lang:core:core:vsegruppypol}')
			)
		);

		$this->funcName = '{Call:Lang:core:core:gruppypolzov}';
	}

	protected function func_userFormTypes(){
		/*
			Группы пользователей
		*/

		$this->typicalMain(
			array(
				'func' => 'userFormTypes',
				'name' => 'users_form_types',
				'table' => 'users_form_types',
				'caption' => 'Новый тип анкеты',
				'listParams' => array(
					'req' => array('users_form_types', '*', "", "`name`"),
					'actions' => array('text' => 'userFormTypes&type_action=modify'),
					'searchForm' => array('searchFields' => array('name' => 'Имя', 'text' => 'Идентификатор'))
				),
				'listParams2' => array('caption' => 'Типы анкет пользователя')
			)
		);

		$this->funcName = '{Call:Lang:core:core:gruppypolzov}';
	}

	protected function func_users(){
		/*
			Группы пользователей
		*/

		$fields = array();
		$values = array();
		$mObj = $this->Core->callModule('main');

		$groups = $this->DB->columnFetch(array('users_groups', 'name', 'id', "", "`name`"));
		$types = $this->Core->getUserFormTypes();
		$matrix = $mObj->getUserRegFormMatrix();
		if($lk = Library::LastKey($matrix)) $matrix[$lk]['post_text'] = (isset($matrix[$lk]['post_text']) ? $matrix[$lk]['post_text'] : '').
			'<script type="text/javascript">'."\n".'showTypeFields();'."\n".'</script>';

		if(empty($this->values['type_action'])){
			$this->setContent(
				$this->getFormText(
					$this->addFormBlock(
						$this->newForm(
							'authAdminAsUser2',
							'authAdminAsUser2',
							array('caption' => '{Call:Lang:core:core:vojtikakpolz}')
						),
						'auth_as_user'
					),
					array(),
					array('redirect' => _D),
					'big'
				)
			);
		}
		elseif($this->values['type_action'] == 'new'){
			$fields = $this->Core->getUserModifyFormValues($this);
		}
		elseif($this->values['type_action'] == 'modify'){
			$values = $this->DB->rowFetch(array('users', '*', "`id`='{$this->values['id']}'"));
			$values = Library::array_merge(Library::unserialize($values['vars']), $values);
			foreach($types as $i => $e) foreach($mObj->getUserRegFormMatrix('', $i) as $i1 => $e1) $values[$i.'_'.$i1] = isset($values[$i1]) ? $values[$i1] : '';
		}

		$p = $this->DB->getPrefix();
		$t1 = $p.'users';
		$t2 = $p.'users_groups';

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitpolzo}',
				'formTemplName' => 'big',
				'listTemplName' => 'big',
				'form' => array('users', $matrix),
				'formData' => array(
					'groups' => Library::array_merge(array('0' => '{Call:Lang:core:core:net}'), $groups),
					'formTypes' => $types
				),
				'modifyData' => array(
					'values' => $values,
				),
				'listParams' => array(
					'req' => "SELECT t1.*, t2.name AS `group_name`, t2.show AS `group_show` FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.group=t2.id ".
						(empty($this->values['in_search']) ? 'ORDER BY t1.date DESC' : ''),
					'actions' => array(
						'act' => 'users&type_action=modify',
						'pwd' => 'usersPwdChange'
					),
					'form_actions' => array(
						'unsuspend' => '{Call:Lang:core:core:aktivirovat}',
						'ban' => '{Call:Lang:core:core:zabanit}',
						'delete' => '{Call:Lang:core:core:udalit}',
					),
					'action' => 'usersActions',
					'searchForm' => array(
						'searchFields' => array(
							'login' => '{Call:Lang:core:core:login}',
							'eml' => 'E-mail',
							'group' => '{Call:Lang:core:core:gruppa}',
							'show' => '{Call:Lang:core:core:status}',
							'date' => '{Call:Lang:core:core:datavnesenii}',
							'utc' => '{Call:Lang:core:core:chasovojpoia2}',
							'name' => '{Call:Lang:core:core:imia}',
							'code' => '{Call:Lang:core:core:kodaktivatsi}',
							'comment' => '{Call:Lang:core:core:kommentarij}'
						),
						'orderFields' => array(
							'login' => '{Call:Lang:core:core:loginu}',
							'eml' => 'e-mail',
							'date' => '{Call:Lang:core:core:datevnesenii}',
							'utc' => '{Call:Lang:core:core:chasovomupoi}'
						),
						'searchMatrix' => array(
							'group' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:liubaia}', '0' => '{Call:Lang:core:core:net}'), $groups)
							),
							'utc' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:liuboj}'), Dates::UTCList())
							),
							'show' => array(
								'type' => 'select',
								'additional' => array('' => '{Call:Lang:core:core:liuboj}', '1' => '{Call:Lang:core:core:aktiven}', '0' => '{Call:Lang:core:core:neaktiven}', '-1' => '{Call:Lang:core:core:zabanen}')
							),
						),
						'searchPrefix' => 't1',
						'isBe' => array('group' => 1, 'show' => 1, 'utc' => 1)
					)
				),
				'listParams2' => array('caption' => '{Call:Lang:core:core:vsepolzovate}'),
				'fields' => $fields
			)
		);
	}

	protected function func_usersActions(){
		$return = $this->typeActions('users', 'users', array('ban' => array('show' => -1)));
		if(isset($this->values['entry'])){
			if($this->values['action'] == 'delete') foreach($this->values['entry'] as $i => $e) $this->Core->unsetUserSession($i);
			else foreach($this->values['entry'] as $i => $e) $this->Core->reauthUserSession($i);
		}
		return $return;
	}

	protected function func_usersPwdChange(){
		/*
			Меняет юзеру пороль
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'usersPwdChange2',
						'usersPwdChange2',
						array('caption' => '{Call:Lang:core:core:smenitparolp}')
					),
					'users_pwd'
				),
				array(),
				array('id' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_usersPwdChange2(){
		/*
			Меняет юзеру пороль
		*/

		if(!$this->check()) return false;
		$this->Core->setUserPassword($this->values['id'], $this->values['pwd']);
		$userData = $this->Core->getUserParamsById($this->values['id']);

		if(!empty($this->values['send'])) $this->mail($userData['eml'], $this->getTmplParams('recoverPwd', 'main'), array('login' => $userData['login'], 'pwd' => $this->values['pwd']));
		$this->refresh('users');
		return true;
	}

	protected function func_regForm(){
		/*
			Форма регистрации пользователя
		*/

		return $this->formFields(
			'user_reg_form',
			array(
				'matrixExtra' => 'user_form',
				'extraFields' => array('in_reg', 'in_account', 'in_admin', 'form_types'),
				'extract' => array('form_types'),
				'insert' => array('form_types' => isset($this->values['form_types']) ? Library::arrKeys2str($this->values['form_types']) : '')
			),
			array('table' => 'users')
		);
	}



	/********************************************************************************************************************************************************************

																		Доступ в раздел админа и на сервер

	*********************************************************************************************************************************************************************/

	protected function func_sessions(){
		/*
			Пользовательские сессии
		*/

		$p = $this->DB->getPrefix();
		$t1 = $p.'session';
		$t2 = $p.'users';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'sessions_list',
					array(
						'req' => "SELECT t1.*, t2.name AS `u_name`, t2.date AS `u_date`, t2.login AS `u_login`, t2.show AS `u_show` ".
							"FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.user_id=t2.id ORDER BY t1.date",
						'form_actions' => array(
							'delete' => '{Call:Lang:core:core:udalit}',
							'reload' => 'Перечитать',
						),
						'actions' => array(
							'vars' => 'showVars'
						),
						'action' => 'sessionActions',
						'searchForm' => array(
							'searchFields' => array(
								'sessid' => 'ID сессии',
								'date' => 'Дата выставления',
								'u_name' => 'Имя пользователя',
								'u_login' => 'Логин',
								'u_date' => 'Дата регистрации пользователя'
							),
							'searchMatrix' => array(
								'u_date' => array('type' => 'calendar')
							),
							'searchPrefix' => array('sessid' => 't1', 'date' => 't1', 'u_name' => 't2', 'u_login' => 't2', 'u_date' => 't2'),
							'searchAlias' => array('u_login' => 'login', 'u_name' => 'name', 'u_date' => 'date'),
							'orderFields' => array('date' => 'дате установки', 'sessid' => 'ID сессии')
						)
					),
					array(
						'caption' => 'Список пользовательских сессий'
					)
				)
			)
		);
	}

	protected function func_sessionActions(){
		/*
			Перечитывает сессии
		*/

		if(empty($this->values['entry'])){
			$this->back($back, '', '', '{Call:Lang:core:core:neotmechenni}');
			return false;
		}

		switch($this->values['action']){
			case 'delete':
				return $this->typeActions('session', 'sessions', array(), '', 'sessid');

			case 'reload':
				foreach($this->DB->columnFetch(array('session', 'user_id', "user_id", $this->getEntriesWhere(false, 'sessid'))) as $i => $e){
					$this->Core->reauthUserSession($i);
				}

				$this->refresh('sessions');
				return true;
		}
	}



	/********************************************************************************************************************************************************************

																		Доступ в раздел админа и на сервер

	*********************************************************************************************************************************************************************/

	protected function func_admin_access_ip(){
		/*
			Управление доступом в раздел админа. Должно быть предусмотрено:
			Установка .htpasswd и доступа через него
			Ограничение доступа по IP в раздел админа (allow и disallow ip)
		*/

		$this->setContent('{Call:Lang:core:core:ukazaniezdes}', 'top_comment');

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitipvsp}',
				'listParams' => array(
					'req' => array('admin_access_ip', '*', " !`admins_groups_id` AND !`admins_id` "),
					'searchForm' => array(
						'searchFields' => array(
							'ip' => '{Call:Lang:core:core:poipiliegoch}',
							'type' => array('' => '{Call:Lang:core:core:vse}', 'allow' => '{Call:Lang:core:core:razreshennye}', 'disallow' => '{Call:Lang:core:core:zapreshchenn}')
						),
						'orderFields' => array('ip' => 'IP'),
						'isBe' => array('type' => true)
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokipskot2}',
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																					Сайты

	*********************************************************************************************************************************************************************/

	protected function func_sites(){
		/*
			Управление сайтами
			Сайту можно:
				Назначить свой собственный URL (обязательно)
				Ограничить доступ админов по сайту
				Назначить свои настройки
				Ограничить используемые сайтом модули
				Ограничить используемые сайтом шаблоны

			Админка всегда цепляется к основному сайту, модули main и core работают только с основной базой данных, все остальные могут использовать свою базу, либо основную.
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'sites',
						'sitesNew',
						array('caption' => '{Call:Lang:core:core:dobavitsajt}')
					),
					'sites'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'sites_list',
					array(
						'req' => array( 'sites', '*', '', "`sort`" ),
						'form_actions' => array(
							'suspend1' => '{Call:Lang:core:core:zakrytdostup1}',
							'suspend2' => '{Call:Lang:core:core:zakrytdostup2}',
							'unsuspend' => '{Call:Lang:core:core:otkrytdostup}',
							'delete' => '{Call:Lang:core:core:udalit}'
						),
						'actions' => array(
							'name' => 'sitesModify'
						),
						'action' => 'sitesActions',
						'searchForm' => array(
							'searchFields' => array('url' => '{Call:Lang:core:core:urlsajta}', 'name' => '{Call:Lang:core:core:imia}'),
							'orderFields' => array('url' => 'URL', 'name' => '{Call:Lang:core:core:imeni}')
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:spisoksajtov}'
					)
				)
			)
		);
	}

	protected function func_sitesNew(){
		/*
			Добавить новый сайт
		*/

		$errors = array();
		$id = db_main::Quot(empty($this->values['modify']) ? '' : $this->values['modify']);
		$this->isUniq('sites', array('url' => '{Call:Lang:core:core:takojurluzhe}'), $id);

		if(!$this->check($errors)) return false;

		if(!empty($default)) $this->DB->Upd(array('sites', array('default' => '')));
		$return = $this->typeIns('sites', $this->fieldValues(array('url', 'name', 'sort', 'default', 'access')), 'sites');

		if(!$id && $return){
			require_once(_W.'core/install.php');
			$iObj = new installCoreDefault($this->Core->DB, $this, 'core');
			$settings = $iObj->getDefaultSettings($this->values);
			$iObj->setDefaultSettings(array($return => $settings[$return]));
		}

		$this->refresh('sites');
		return $return;
	}

	protected function func_sitesModify(){
		/*
			Изменение настроек сайта
		*/

		$id = db_main::Quot($this->values['id']);
		$values = $this->DB->rowFetch(array('sites', '*', "id='$id'"));
		$this->funcName = '{Call:Lang:core:core:sajt1:'.Library::serialize(array($values['name'])).'}';
		$this->pathFunc = 'sites';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'sites',
						'sitesNew',
						array('caption' => '{Call:Lang:core:core:sajt1:'.Library::serialize(array($values['name'])).'}')
					),
					'sites'
				),
				$values,
				array('modify' => $id),
				'big'
			)
		);
	}

	protected function func_sitesActions(){
		/*
			Списочные действия над сайтами
		*/

		if(empty($this->values['entry'])){
			$this->back('sites');
			return false;
		}

		$where = $this->getEntriesWhere();
		$show = '1';

		switch($this->values['action']){
			case 'delete':
				if($this->DB->cellFetch(array('sites', 'default', "`default` AND (".$where.")"))){
					$this->back('sites', '{Call:Lang:core:core:vynemozheteu}');
					return false;
				}
				$reqType = 'Del';
				break;

			case 'unsuspend':
				$show = 0;
				$reqType = 'Upd';
				break;

			case 'suspend1':
				$show = 1;
				$reqType = 'Upd';
				break;

			case 'suspend2':
				$show = 2;
				$reqType = 'Upd';
				break;

			default:
				$this->back('sites');
				return false;
		}

		$return = $this->DB->$reqType(array('table' => 'sites', 'fields' => array('access' => $show), 'where' => $where));
		$this->refresh('sites');
		return $return;
	}

	protected function func_sitesAccess(){
		/*
			Ограничение доступа по IP
		*/

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitip}',
				'form' => 'site_access',
				'table' => 'site_access_ip',
				'name' => 'site_access_ip',
				'listParams' => array(
					'req' => array('site_access_ip', '*', '', '`id` DESC'),
					'searchForm' => array(
						'searchFields' => array('ip' => '{Call:Lang:core:core:ipiliegochas}'),
						'orderFields' => array('ip' => 'IP')
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokipskot3}'
				)
			)
		);
	}



	/********************************************************************************************************************************************************************

																Установка и клонирование пакетов

	*********************************************************************************************************************************************************************/

	protected function func_packages(){
		/*
			Пакеты.

			Установка пакетов происходит в 2 этапа:
				Копирование всех модулей пакета (просто копируются файлы)
				Клонирование модулей пакета в систему (создается персональная для пакета копия таблиц БД со своими настройками и присваивается персональный URL),
					Для пакета также ставится указание о существовании клона.

			Настройками может быть указано, что клон модуля может размещаться на определенных сайтах из набора
			Модулю может быть назначена своя персональная БД, в т.ч. на другом хосте
			Если модуль требует установки других модулей, установка зависимого модуля возможна только в ту-же БД что и основного

			Пакет - по сути лишь комплексная установка модулей

			Если модули входящие в пакет требуют чтобы уже были установлены какие-то другие модули, то должен быть указан клон к которому привязывается устанавливаемый модуль
			При удалении клона модуля происходит проверка что нет ни одного установленного зависимого модуля

			При удалении файлов модуля (удаление из системы) происходит проверка, что в системе отсутствуют клоны этого модуля, а также что данный модуль не входит
				в како-то другой установленный пакет.

			При обновлении проверяется что новая версия выше старой. Если какие-то из установленных зависимых модулей по своей версии несовместимы с обновлением,
				выдается предупреждение.
		 */

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'new_pkg',
						'pkgInstall',
						array('caption' => '{Call:Lang:core:core:ustanovitpak}')
					),
					'install_pkg'
				),
				array(),
				array(),
				'big'
			)
		);
	}


	/********************************************************************************************************************************************************************

																				Инсталляция

	*********************************************************************************************************************************************************************/

	protected function func_pkgInstall(){
		/*
			Принимает сведения об инсталлируемом объекте.
				- Распаковывает архив
				- Проводит предварительную проверку совместимости
				- Копирует все модули и прочие объекты какие есть
				- Передает управление  pkgInstallNext

			XML-файл инсталлятора может содержать предустановленные параметры. Пользователь должен иметь возможность еще до начала установки указать использовать
				предустановленные параметры или указать свои
		*/

		if(!$this->check()) return false;
		if($this->values['source'] == 4) $this->values['archieve_path'] = _W;

		switch($this->values['source']){
			case '2':
				//Устанавливаем тип 3 для архива
				$this->values['archieve_path'] = $this->values['archieve'];
				$this->values['source'] = 3;

			case '3':
				//Распаковываем архив, устанавливаем ему тип 4
				if(empty($this->values['archieve_path'])) $this->setError($this->field, '{Call:Lang:core:core:ustanovochny}');
				else{
					$tmpFolder = Files::getEmptyFolder(TMP.basename($this->values['archieve'])).'/';
					Files::mkDir($tmpFolder);

					if(Arc::extract(TMP.$this->values['archieve'], $tmpFolder)){
						$this->values['install_path'] = $tmpFolder.'install.xml';
						$this->values['archieve_path'] = $tmpFolder.'upload/';
					}
					else{
						$this->setError(empty($this->values['archieve']) ? 'archieve' : 'archieve_path', '{Call:Lang:core:core:neudalosrasp}');
						break;
					}
				}

			case '4':
				$XML = $this->getInstallParams($this->values['install_path'], $this->values['archieve_path']);

				if($this->values['source'] == 3 && !file_exists($this->values['archieve_path'])){
					$this->setError('archieve_path', '{Call:Lang:core:core:nenajdenapap1:'.Library::serialize(array($this->values['archieve_path'])).'}');
				}
				elseif(!file_exists($this->values['install_path'])){
					$this->setError('install_path', '{Call:Lang:core:core:nenajdenfajl1:'.Library::serialize(array($this->values['install_path'])).'}');
				}
				elseif(!XML::isXML(Files::read($this->values['install_path']))){
					$this->setError('install_path', '{Call:Lang:core:core:nekorrektnyj4:'.Library::serialize(array($this->values['install_path'])).'}');
				}
				elseif($this->values['source'] == 3){
					foreach($this->getInstallatorObjects() as $e){
						if($e != 'languages' && !empty($XML[$e])){
							foreach($XML[$e] as $i1 => $e1){
								if(!Library::versionCompare($this->getVersion($e, $e1, $XML['defaults']), $e1['version'])){
									switch($e){
										case 'core': $msg = '{Call:Lang:core:core:iadroimeetve}'; break;
										case 'modules': $msg = '{Call:Lang:core:core:modulimeetve:'.Library::serialize(array($e1['name'])).'}'; break;
										case 'templates': $msg = '{Call:Lang:core:core:shablonimeet:'.Library::serialize(array($e1['name'])).'}'; break;
										case 'plugins': $msg = '{Call:Lang:core:core:plaginimeetv:'.Library::serialize(array($e1['name'])).'}'; break;
										case 'languages': $msg = '{Call:Lang:core:core:iazykimeetve:'.Library::serialize(array($e1['name'])).'}'; break;
										default: throw new AVA_Exception('{Call:Lang:core:core:neizvestnyjt:'.Library::serialize(array($e)).'}');
									}
									$this->setError('source', $msg);
								}
							}
						}
					}
				}

				break;

			default:
				throw new AVA_Exception('{Call:Lang:core:core:nevernyjtipi}');
		}

		if(!$this->check()) return false;

		$fObj = $this->addFormBlock(
			$this->newForm(
				'pkgInstallEnd',
				'pkgInstallEnd',
				array('caption' => empty($XML['text']) ? '{Call:Lang:core:core:ustanovkapak}' : '{Call:Lang:core:core:ustanovkapak1:'.Library::serialize(array($XML['text'])).'}')
			),
			'install',
			array(
				'langList' => $this->DB->columnFetch(array('languages', 'text', 'name', '', "`sort`")),
				'sites' => $this->DB->columnFetch(array('sites', 'name', 'id', '', "`sort`")),
				'dbList' => $this->Core->getDatabases(true),
				'params' => $XML,
				'dependSysTmp' => $this->Core->getAllTemplates('system', false, true),
				'dependModuleTmps' => $this->Core->getAllModuleTemplates(true),
				'cmsModules' => $this->Core->getModulesByType('cms')
			)
		);

		$m = $fObj->matrix;
		unset($m['db'], $m['sites']);

		if($m){
			$this->setContent($this->getFormText($fObj, $XML['defaults'], $this->values, 'big'));
			return true;
		}

		return $this->pkgInstallEnd();
	}

	protected function func_pkgInstallEnd(){
		/*
			Завершает установку
		*/

		$XML = $this->getInstallParams($this->values['install_path'], $this->values['archieve_path']);
		$this->values = Library::array_merge($XML['defaults'], $this->values);
		$this->Core->innerPreParams($this, $XML);

		foreach($this->getInstallatorObjects() as $e){
			if(!empty($XML[$e])){
				foreach($XML[$e] as $e1){
					if(!empty($e1['installator'])){
						if(!$this->getVersion($e, $e1, $this->values)) $this->getInstallInstance($e, $e1, $this->values)->checkInstall();
						else $this->getInstallInstance($e, $e1, $this->values)->checkUpdate($this->getVersion($e, $e1, $this->values), $e1['version']);
					}
				}
			}
		}

		if(!$this->check()) return false;
		return $this->pkgInstallEnd();
	}

	public function pkgInstallEnd(){
		/*
			Завершает инсталляцию:
				- если необходимо, копирует информацию
				- проводит обработку всех объектов
		*/

		$this->Core->setFlag('tmplLock');

		switch($this->values['source']){
			case '3':
				//Проверяем устанавливаемые объекты на совместимость, копируем их в соответсвующие папки

				$this->Core->ftpCopy($this->values['archieve_path'], _W);
				$this->values['source'] = 4;

			case '4':
				$XML = $this->getInstallParams($this->values['install_path'], _W);
				$this->values = Library::array_merge($XML['defaults'], $this->values);

				foreach($this->getInstallatorObjects() as $e){
					if(!empty($XML[$e])){
						foreach($XML[$e] as $e1){
							$this->{$e.'Install'}($e1, (bool)$this->getVersion($e, $e1, $this->values));
						}
					}

					if($e == 'core') $this->Core->innerPreParams($this, $XML);
				}

				$this->Core->rmFlag('refreshed');
				$this->Core->rmFlag('tmplLock');
				$this->Core->rmHeader('Location');
				$this->refresh('packages', '{Call:Lang:core:core:ustanovkazav}');

				return true;
		}
	}

	protected function __ava__getInstallInstance($type, $params, $values = array()){
		/*
			Возвращает объект инсталлятора
		*/

		if(!empty($params['installator'])){
			$cName = 'install'.$type.$params['installator'];

			switch($type){
				case 'core':
					require_once(_W.'core/install.php');
					$inst = new $cName($this->Core->DB, $this, $params['name'], $params);
					break;

				case 'modules':
					require_once(_W.$type.'/'.$params['name'].'/install.php');
					$inst = new $cName($this->Core->DB, $this, $values['name_'.$params['installator']], $params);
					break;

				case 'plugins':
					if(file_exists(_W.$type.'/'.$params['name'].'/install.php')){
						require_once(_W.$type.'/'.$params['name'].'/install.php');
						$inst = new $cName($this->Core->DB, $this, $values['name_'.$params['installator']], $params);
					}
					else $inst = new InstallPluginObject($this->Core->DB, $this, $values['name_'.$params['installator']], $params);
					break;

				case 'templates':
					require_once(_W.$type.'/'.$params['tmplType'].'/'.$this->values['folder_'.$params['installator']].'/install.php');
					$inst = new $cName($this->Core->DB, $this, $values['name_'.$params['installator']], $values['folder_'.$params['installator']], $params);
					break;

				default:
					$inst = false;
			}
		}
		else $inst = false;

		return $inst;
	}

	protected function __ava__coreInstall($params, $update = false){
		/*
			Инстоллирует порамитры едра
		*/

		$obj = $this->getInstallInstance('core', $params);
		if(!$update) $obj->Install();
		else $obj->Update($this->getVersion('core', $params, $this->values), $params['version']);
		$this->Core->DB->Upd(array('version', array('version' => $params['version']), "`name`='core'"));
	}

	protected function __ava__modulesInstall($params, $update = false, $duplicateMod = '', $duplicateStyle = ''){
		/*
			Инсталлирует модуль
			Проверяется есть ли такой установленный модуль. Если нет, он устанавливается. Если да но он старее устанавливаемого - происходит обновление.
			В иных случаях - просто возврат.
		*/

		if(!$update){
			$obj = $this->getInstallInstance('modules', $params, $this->values);
			$unitedModules = array();

			if(!empty($params['requirements']['requiredModules'])){
				foreach($params['requirements']['requiredModules'] as $i => $e){
					$unitedModules[] = empty($this->values['united_'.$i]) ? $this->values['name_'.$this->getInstallModuleParamByName($i, 'installator')] : $this->values['united_'.$i];
				}
			}

			$obj->install();

			if(!$duplicateStyle && !$duplicateMod){
				$this->DB->Ins(array('isset_modules', array('name' => $params['name'], 'text' => $params['text'], 'version' => $params['version'])));
			}
			elseif($duplicateStyle && $duplicateMod){
				$srcModData = $this->Core->DB->columnFetch(array('settings', '*', '', "`module`='$duplicateMod' && `site`='".$this->Core->adminSite->getSiteId()."'"));

				foreach($this->values['sites'] as $i => $e){
					foreach($srcModData as $i =>$e){
						unset($e['module'], $e['id']);
						$this->Core->DB->Upd(array('settings', $e, "`mod`='".$this->values['name_'.$params['installator']]."' AND `site`='$i' AND `value`='{$e['value']}'"));
					}
				}

				if($duplicateStyle == 'duplicate'){
					$dMod = $this->Core->callModule($duplicateMod);
 					$nMod = $this->Core->callModule($this->values['name_'.$params['installator']]);

					foreach($dMod->DB->getTables() as $i => $e){
						$i = regExp::Replace("/^".$dMod->DB->getPrefix()."/iUs", '', $i, true);
						$nMod->DB->truncate($i);
						foreach($dMod->DB->columnFetch(array($i, '*', '')) as $e){
							$nMod->DB->Ins(array($i, $e));
						}
					}
				}
			}

			$this->DB->Ins(
				array(
					'modules',
					array(
						'url' => $this->values['name_'.$params['installator']],
						'db' => $this->values['db'],
						'text' => $this->values['text_'.$params['installator']],
						'name' => $params['name'],
						'united_modules' => ','.implode(',', $unitedModules).',',
						'sites' => Library::arrKeys2str($this->values['sites']),
						'show' => empty($this->values['show']) ? '' : $this->values['show']
					)
				)
			);
		}
		else{
			foreach($this->Core->getModulesByType($params['name']) as $i => $e){
				$obj = $this->getInstallInstance('modules', $params, array('name_'.$params['installator'] => $i));
				$obj->Update($this->getVersion('modules', $params, $this->values), $params['version']);
			}
			$this->DB->Upd(array('isset_modules', array('version' => $params['version']), "`name`='{$params['name']}'"));
		}

		$this->Core->loadModulesList(true);
		return true;
	}

	protected function __ava__languagesInstall($params, $update = false){
		/*
			Инсталлирует йезыг
		*/

		if($update) $reqType = 'Upd';
		else $reqType = 'Ins';
		return $this->DB->$reqType(array('languages', array('name' => $params['name'], 'text' => $params['text']), "`name`='".db_main::Quot($params['name'])."'"));
	}

	protected function __ava__templatesInstall($params, $update = false){
		/*
			Инсталлирует шоблон
		*/

		if(!$update){
			$obj = $this->getInstallInstance('templates', $params, $this->values);
			$obj->install();

			$fields = array(
				'name' => $this->values['name_'.$params['installator']],
				'folder' => $this->values['folder_'.$params['installator']],
				'type' => $params['tmplType'],
				'version' => $params['version'],
				'tech_name' => $params['name']
			);

			if(($params['tmplType'] != 'main' && $params['tmplType'] != 'admin') || !empty($params['tmplType'])) $fields['show'] = 1;

			if($params['tmplType'] == 'main' || $params['tmplType'] == 'admin'){
				$fields['vars']['dependTemplates'] = array(
					'sys_depend_tmp' => empty($this->values['sys_depend_tmp_'.$params['installator']]) ?
						$this->getFirstTemplate('system') :
						$this->values['sys_depend_tmp_'.$params['installator']],
				);

				$modTmps = $this->Core->getAllModuleTemplates(true);
				foreach($modTmps['dependModuleTmps'] as $i => $e){
					$fields['vars']['dependTemplates']['depend_tmp_'.$i] = empty($this->values['depend_tmp_'.$i.'_'.$params['installator']]) ?
						key($e) :
						$this->values['depend_tmp_'.$i.'_'.$params['installator']];
				}

				$fields['language'] = $this->values['language_'.$params['installator']];
			}

			if(!$tmplId = $this->DB->Ins(array('templates', $fields))) return true;

			if(isset($params['pages'])){
				foreach($params['pages'] as $e){
					$this->DB->Ins(
						array(
							'template_pages',
							array(
								'template' => $this->values['folder_'.$params['installator']],
								'template_type' => $params['tmplType'],
								'name' => $e['name'],
								'url' => $e['url'],
								'type' => $e['type']
							)
						)
					);

					if($this->Core->getParam('templateSource') == 'db'){
						$this->insertTemplateIntoSource($this, 'db', $this->values['folder_'.$params['installator']], $e['tmplType']);
					}
				}
			}
		}
		else{
			foreach($this->Core->getTemplatesByTechName($params['tmplType'], $params['name']) as $i => $e){
				$obj = $this->getInstallInstance('templates', $params, array('name_'.$params['installator'] => $e, 'folder_'.$params['installator'] => $i));
				$obj->update($this->getVersion('templates', $params, $this->values), $params['version']);
				$this->DB->Upd(array('templates', array('version' => $params['version']), "`folder`='{$params['name']}' AND `type`='{$params['type']}'"));
			}
		}

		return true;
	}

	protected function pluginsInstall($obj, $params, $update = false){
		/*
			Инсталлирует плагин
		*/

		$obj = $this->getInstallInstance('plugins', $params, $this->values);

		if(!$update){
			$installParams['text'] = $params['text'];
			$installParams['name'] = $params['name'];
			$installParams['show'] = 1;

			$installParams['version'] = $params['version'];
			$installParams['type'] = $params['pluginType'];
			$installParams = Library::array_merge($installParams, $obj->install());

			$this->DB->Ins(array('plugins', $installParams));
		}
		else{
			$obj->update($this->getVersion('plugins', $params, $this->values), $params['version']);
			$this->DB->Upd(array('plugins', array('version' => $params['version']), "`name`='{$params['name']}'"));
		}

		return true;
	}

	public function getInstallatorObjects(){
		return array('core', 'modules', 'languages', 'templates', 'plugins');
	}

	public function __ava__getVersion($objType, $params, $values){
		/*
			Проверяет текущую версию
		*/

		switch($objType){
			case 'core': return $this->Core->DB->issetTable('version') ? $this->Core->DB->cellFetch(array('version', 'version', "`name`='core'")) : 0;
			case 'modules': return $this->Core->DB->issetTable('isset_modules') ? $this->Core->DB->cellFetch(array('isset_modules', 'version', "`name`='".db_main::Quot($params['name'])."'")) : 0;
			case 'templates': return $this->Core->DB->issetTable('templates') ? $this->Core->DB->cellFetch(array('templates', 'version', "`folder`='".db_main::Quot($values['folder_'.$params['installator']])."' AND `type`='".db_main::Quot($params['tmplType'])."'")) : 0;
			case 'plugins': return $this->Core->DB->issetTable('plugins') ? $this->Core->DB->cellFetch(array('plugins', 'version', "`name`='".db_main::Quot($params['name'])."'")) : 0;
			case 'languages': return 0;
			default: throw new AVA_Exception('{Call:Lang:core:core:neizvestnyjt1:'.Library::serialize(array($objType)).'}');
		}
	}

	public function __ava__getInstallModuleParamByName($name, $param, $file = false, $path = false){
		/*
			Возвращает параметры для данного конкретного инсталлируемого объекта
		*/

		if($file && $path) $this->getInstallParams($file, $path);
		elseif(empty($this->installParams)) throw new AVA_Exception('{Call:Lang:core:core:netschitanny}');

		foreach($this->installParams['modules'] as $i => $e){
			if($e['name'] == $name) return $e[$param];
		}

		return false;
	}

	public function __ava__getFirstTemplate($type){
		/*
			Возвращает первый шаблон указанного типа
		*/

		return $this->DB->cellFetch(array('templates', 'folder', "`type`='".db_main::Quot($type)."'", "`sort`, `id`"));
	}

	public function __ava__getInstallParams($file, $path){
		/*
			Считывает XML-файл инсталлятора
		*/

		if(empty($this->installParams)){
			if(!$XML = XML::parseXML(Files::read($file), $a, $h, 'install')) return false;
			if(empty($this->installParams['defaults'])) $this->installParams['defaults'] = array();
			if(!is_array($XML['installation']) || Library::isHash($XML['installation'])) $XML['installation'] = array($XML['installation']);

			foreach($XML['installation'] as $e){
				$modXML = XML::parseXML(Files::read($path.$e.'descript.xml'), $a, $h, 'descript');
				$this->installParams['defaults'] = Library::array_merge(
					isset($this->installParams['defaults']) ? $this->installParams['defaults'] : array(),
					isset($modXML['defaults']) ? $modXML['defaults'] : array()
				);

				if($modXML['type'] == 'templates' && isset($modXML['pages']) && library::isHash($modXML['pages'])) $modXML['pages'] = array($modXML['pages']);
				$this->installParams[$modXML['type']][] = $modXML;
			}
		}

		return $this->installParams;
	}



	/********************************************************************************************************************************************************************

																		Работа с плагинами

	*********************************************************************************************************************************************************************/

	protected function func_plugins(){
		/*
			Списки всех встроенных в систему плагинов, а также возможность самому добавить плагин
				Плагины бывают следующих типов:
					- Вызываемые при обращении к определенной точке по номеру											- point
					- Вызываемые взамен определенной функции функционала модуля (можно не существующей)					- func
					- Вызываемые взамен несуществующей функции любого класса вообще	через __call						- noExistFunc
					- Вызываемые просто по имени через runPlugin модуля core (в основном для вставки на страницу)		- simple
					- Являющиеся надстройками (extends) на функциональные объекты
		*/

		$modifyData = array();
		$fields = array();

		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'modify'){
				$modifyData = array('values' => $this->DB->rowFetch(array("plugins", '*', "`id`='".db_main::Quot($this->values['id'])."'")));
				$modifyData['values'] = Library::array_merge($modifyData['values'], Library::unserialize($modifyData['values']['vars']));
			}
			elseif($this->values['type_action'] == 'new'){
				$fields = $this->fieldValues(array('name', 'text', 'type', 'code', 'settings_code', 'set_code', 'sort', 'show'));
				$fields['vars'] = $this->fieldValues(array('services', 'point', 'modulePointMod', 'modulePoint', 'functionMod', 'function', 'noExistFuncClass', 'noExistFunc', 'position'));
			}
		}

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitplagi}',
				'formTemplName' => 'big',
				'modifyReq' => '',
				'modifyData' => $modifyData,
				'fields' => $fields,
				'listParams' => array(
					'req' => array('plugins', '*'),
					'actions' => array(
						'text' => 'plugins&type_action=modify',
						'settings' => 'pluginSettings',
					),
					'form_actions' => array(
						'suspend' => '{Call:Lang:core:core:zakryt}',
						'unsuspend' => '{Call:Lang:core:core:otkryt}',
						'delete' => '{Call:Lang:core:core:udalit}'
					),
					'searchForm' => array(
						'searchFields' => array(
							'ip' => '{Call:Lang:core:core:ipiliegochas}',
							'type' => array('' => '{Call:Lang:core:core:vse}', 'allow' => '{Call:Lang:core:core:razreshennye}', 'disallow' => '{Call:Lang:core:core:zapreshchenn}')
						),
						'orderFields' => array('ip' => 'IP'),
						'searchParams' => array(
							'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='
						)
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokplagin}'
				)
			)
		);
	}



	/********************************************************************************************************************************************************************

																	Работа с копиями модулей

	*********************************************************************************************************************************************************************/

	protected function func_modules(){
		/*
			Все установленные активные копии
		*/

		$t1 = $this->DB->getPrefix().'modules';
		$t2 = $this->DB->getPrefix().'isset_modules';

		$this->setContent(
			$this->getListText(
				$this->newList(
					'installed_packages_list',
					array(
						'req' => "SELECT t1.*, t2.name AS orig_name, t2.text AS orig_textname, t2.version
							FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.name=t2.name ".(empty($this->values['in_search']) ? 'ORDER BY t1.sort' : ''),
						'actions' => array(
							'clone' => 'cloneModule',
							'text' => 'moduleParams',
							'uninstall' => 'uninstallModule'
						),
						'form_actions' => array(
							'suspend' => '{Call:Lang:core:core:sdelatnedost}',
							'unsuspend' => '{Call:Lang:core:core:sdelatdostup}'
						),
						'action' => 'modulesActions',
						'searchForm' => array(
							'searchFields' => array(
								'sites' => '{Call:Lang:core:core:sajt}',
								'url' => '{Call:Lang:core:core:urlimia}',
								'text' => '{Call:Lang:core:core:nazvanie}',
								'name' => '{Call:Lang:core:core:tekhnichesko}',
								'db' => 'База данных'
							),
							'orderFields' => array(
								'name' => '{Call:Lang:core:core:tekhnichesko1}',
								'url' => '{Call:Lang:core:core:urlimeni}',
								'text' => '{Call:Lang:core:core:nazvaniiu}'
							),
							'searchPrefix' => 't1',
							'searchMatrix' => array(
								'sites' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), Library::concatPrefixArrayKey($this->Core->getSites(), ',', ','))
								),
								'db' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}', '~nobody' => 'Основная'), $this->Core->getDatabases(false))
								)
							),
							'isBe' => array('db' => 1)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:spisokmodule}',
						'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc=modules&table=modules'
					)
				),
				'big'
			)
		);
	}

	protected function func_cloneModule(){
		/*
			Клонирование одного модуля
		*/

		$params = $this->Core->getModuleParamsById($this->values['id']);
		$XML = XML::parseXML(Files::read(_W.'modules/'.$params['name'].'/descript.xml'), $a, $h, 'descript');
		$this->pathFunc = 'modules';
		$this->funcName = '{Call:Lang:core:core:klonirovanie:'.Library::serialize(array($params['text'])).'}';

		$form = $this->addFormBlock(
			$this->newForm('cloneModule2', 'cloneModule2', array('caption' => $this->funcName)),
			'clone_pkg',
			array(
				'dbList' => $this->Core->getDatabases(true),
				'sites' => $this->Core->getSites(),
				'requiredModules' => $XML['requirements']['requiredModules'],
				'modName' => $XML['name']
			)
		);

		$this->getInstallInstance('modules', $XML, array('name_'.$XML['installator'] => $params['url']))->prepareInstall();
		$this->setContent($this->getFormText($form, array(), array('id' => $this->values['id']), 'big'));
	}

	protected function func_cloneModule2(){
		/*
			Проверка уникальности URL и прочих данных модуля
		*/

		$params = $this->Core->getModuleParamsById($this->values['id']);
		$XML = XML::parseXML(Files::read(_W.'modules/'.$params['name'].'/descript.xml'), $a, $h, 'descript');
		$this->installParams[$XML['type']][] = $XML;

		$this->pathFunc = 'modules';
		$this->funcName = '{Call:Lang:core:core:klonirovanie:'.Library::serialize(array($params['text'])).'}';

		if($this->DB->cellFetch(array('modules', 'id', "`url`='".db_main::Quot($this->values['name_'.$XML['installator']])."'"))){
			$this->setError('name_'.$XML['installator'], '{Call:Lang:core:core:takoeurlimia}');
		}

		if($this->DB->cellFetch(array('modules', 'id', "`text`='".db_main::Quot($this->values['text_'.$XML['installator']])."'"))){
			$this->setError('text_'.$XML['installator'], '{Call:Lang:core:core:takoenazvani}');
		}

		$this->values['depend_tmp_'.$this->values['name_'.$XML['installator']].'_'.$XML['installator']] = $this->Core->getModuleTemplateName($params['url']);
		$this->Core->innerPreParams($this, array('modules' => array($XML)));
		$instObj = $this->getInstallInstance('modules', $XML, $this->values);
		$instObj->checkInstall();

		if(!$this->check()) return false;

		$this->Core->setFlag('tmplLock');
		$this->values = Library::array_merge($XML['defaults'], $this->values);
		$return = $this->modulesInstall($XML, false, $params['url'], $this->values['settings']);

		$aTmplParams = Library::unserialize($this->Core->DB->cellFetch(array('templates', 'vars', "`type`='admin' AND `folder`='".$this->Core->getParam('adminTemplate')."'")));
		$sTmplParams = Library::unserialize($this->Core->DB->cellFetch(array('templates', 'vars', "`type`='main' AND `folder`='".$this->Core->getParam('template')."'")));

		$aTmplParams['dependTemplates']['depend_tmp_'.$this->values['name_'.$XML['installator']]] = $aTmplParams['dependTemplates']['depend_tmp_'.$params['url']];
		$sTmplParams['dependTemplates']['depend_tmp_'.$this->values['name_'.$XML['installator']]] = $sTmplParams['dependTemplates']['depend_tmp_'.$params['url']];

		$this->Core->DB->Upd(array('templates', array('vars' => Library::serialize($aTmplParams)), "`type`='admin' AND `folder`='".$this->Core->getParam('adminTemplate')."'"));
		$this->Core->DB->Upd(array('templates', array('vars' => Library::serialize($sTmplParams)), "`type`='main' AND `folder`='".$this->Core->getParam('template')."'"));

		$this->Core->rmFlag('tmplLock');
		$this->Core->rmFlag('refreshed');
		$this->Core->rmHeader('Location');

		if($this->values['settings'] == 'duplicate'){
			$tbls = $this->DB->getTables();
			sort($tbls);
			foreach($tbls as $e) $this->DB->Truncate($e);
			$this->DB->CopyTable($tbls, $tbls, $this->Core->callModule($params['url'])->DB);
		}
		elseif($this->values['settings'] == 'isset'){
			foreach($this->Core->DB->columnFetch(array('settings', '*', 'id', "`module`='{$params['url']}'")) as $i => $e){
				unset($e['id'], $e['module'], $e['site']);
				$this->Core->DB->Upd(array('settings', $e, "`module`='".$this->values['name_'.$XML['installator']]."' AND `name`='{$e['name']}'"));
			}
		}

		$this->refresh('modules', '{Call:Lang:core:core:klonirovanie1}');
		return $return;
	}

	protected function func_moduleParams(){
		/*
			Параметры модуля
		*/

		$id = db_main::Quot($this->values['id']);
		$v = $this->DB->rowFetch(array('modules', array('name', 'url', 'text', 'sites', 'show'), "`id`='$id'"));

		$values = array(
			'sites' => Library::str2arrKeys($v['sites']),
			'text_'.$v['name'] => $v['text'],
			'name_'.$v['name'] => $v['url'],
			'show' => $v['show']
		);

		$this->pathFunc = 'packages';
		$this->funcName = '{Call:Lang:core:core:parametrymod:'.Library::serialize(array($v['text'])).'}';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'moduleParams2',
						'moduleParams2',
						array('caption' => $this->funcName)
					),
					'clone_pkg',
					array(
						'modify' => $id,
						'sites' => $this->Core->getSites(),
						'modName' => $v['name']
					)
				),
				$values,
				array('modify' => $id),
				'big'
			)
		);
	}

	protected function func_moduleParams2(){
		if(false !== ($return = $this->typeIns('modules', array('show' => $this->values['show'], 'sites' => Library::arrKeys2str($this->values['sites'])), 'modules'))){
			$params = $this->Core->getModuleParamsById($this->values['modify']);
			$XML = XML::parseXML(Files::read(_W.'modules/'.$params['name'].'/descript.xml'), $a, $h, 'descript');
			$instObj = $this->getInstallInstance('modules', $XML, array('name_'.$XML['installator'] => $params['url']));

			if(method_exists($instObj, 'getDefaultSettings')) $instObj->setDefaultSettings($instObj->getDefaultSettings($this->values));
			foreach($this->Core->getSites() as $i => $e){
				if(empty($this->values['sites'][$i])) $this->Core->DB->Del(array('settings', "`module`='{$params['url']}' AND `site`='$i'"));
			}
		}

		return $return;
	}

	protected function func_modulesActions(){
		$this->typeActions('modules', 'modules');
	}

	protected function func_uninstallModule(){
		/*
			Деинсталлирует модуль
		*/

		$params = $this->Core->getModuleParamsById($this->values['id']);
		if($this->Core->getUnitedDownModules($params['url'])) $this->setError('', '{Call:Lang:core:core:nelziaudalit}');

		$XML = XML::parseXML(Files::read(_W.'modules/'.$params['name'].'/descript.xml'), $a, $h, 'descript');
		$instObj = $this->getInstallInstance('modules', $XML, array('name_'.$XML['installator'] => $params['url']));
		$instObj->checkUninstall();

		if($this->errorMessages){
			$this->back('modules');
			return false;
		}

		$this->Core->setFlag('tmplLock');
		$instObj->Uninstall();
		$this->DB->Del(array('modules', "`url`='{$params['url']}'"));

		$this->Core->rmFlag('tmplLock');
		$this->Core->rmFlag('refreshed');
		$this->Core->rmHeader('Location');

		$this->refresh('modules');
		return true;
	}


	/********************************************************************************************************************************************************************

																					Языки

	*********************************************************************************************************************************************************************/

	protected function func_languages(){
		/*
			Установка и обновление пакетов в системе
		 */

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'new_lang',
						'languageAdd',
						array('caption' => '{Call:Lang:core:core:dobavitpodde}')
					),
					'new_lang'
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'languages_list',
					array(
						'req' => array( 'languages', '*', '', "`sort`" ),
						'form_actions' => array(
							'delete' => '{Call:Lang:core:core:udalitpodder}'
						),
						'actions' => array(
							'text' => 'languageModify',
							'pkgs' => 'languagePackages'
						),
						'action' => 'languagesActions',
						'searchForm' => array(
							'searchFields' => array(
								'text' => '{Call:Lang:core:core:nazvanie}',
								'name' => '{Call:Lang:core:core:identifikato}'
							),
							'orderFields' => array(
								'text' => '{Call:Lang:core:core:nazvaniiu}',
								'name' => '{Call:Lang:core:core:identifikato5}'
							)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:vsepodderzhi}'
					)
				)
			)
		);
	}

	protected function func_languageAdd(){
		/*
			Добавляет поддержку языка
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->isUniq( 'languages', array('text' => '{Call:Lang:core:core:takoeimiauzh}', 'name' => '{Call:Lang:core:core:takojidentif}'), $id);

		if($return = $this->typeIns('languages', $this->fieldValues(array('name', 'text', 'sort')), 'languages')){
			if(!file_exists(_W.'languages/'.$this->values['name'])){
				Files::mkDir(_W.'languages/'.$this->values['name']);
				Files::write(
					_W.'languages/'.$this->values['name'].'/descript.xml',
					XML::getXML(
						array(
							'descript' => array(
								'type' => 'languages',
								'name' => $this->values['name'],
								'text' => $this->values['text'],
							)
						)
					)
				);
			}
		}
		return $return;
	}

	protected function func_languagesActions(){
		$langList = $this->DB->columnFetch(array('languages', '*', '', $this->getEntriesWhere()));
		if($return = $this->typeActions('languages', 'languages')){
			if($this->values['action'] == 'delete'){
				foreach($langList as $e) $this->Core->ftpRm(_W.'languages/'.$e['name']);
			}
		}

		return $return;
	}

	protected function func_languageModify(){
		$this->pathFunc = 'languages';

		$this->typeModify(
			array('languages', '*', "`id`='".db_main::Quot($this->values['id'])."'"),
			'new_lang',
			'languageAdd',
			array(
				'caption' => '{Call:Lang:core:core:nastrojkiiaz}',
			)
		);
	}

	protected function func_languagePackages(){
		/*
			Список всех языковых пакетов
		*/

		$langData = $this->Core->getLangParamsById($this->values['id']);
		$this->pathFunc = 'languages';
		$this->funcName = '{Call:Lang:core:core:paketyiazyka:'.Library::serialize(array($langData['text'])).'}';

		$packages = array();
		if(file_exists(_W.'languages/'.$langData['name'].'/core/core/lang.php')){
			$packages[] = Library::array_merge(XML::parseXML(Files::read(_W.'core/descript.xml'), $a, $h, 'descript'), array('id' => _W.'languages/'.$langData['name'].'/core/core/lang.php'));
		}

		if(file_exists(_W.'languages/'.$langData['name'].'/modules/')){
			foreach(Files::readFolder(_W.'languages/'.$langData['name'].'/modules/') as $e){
				if(file_exists(_W.'languages/'.$langData['name'].'/modules/'.$e.'/lang.php')){
					$packages[] = Library::array_merge($this->Core->getModuleParamsByTechName($e), array('id' => _W.'languages/'.$langData['name'].'/modules/'.$e.'/lang.php'));
				}
			}
		}

		if(file_exists(_W.'languages/'.$langData['name'].'/templates/')){
			foreach(Files::readFolder(_W.'languages/'.$langData['name'].'/templates/') as $e){
				foreach(Files::readFolder(_W.'languages/'.$langData['name'].'/templates/'.$e) as $e1){
					if(file_exists(_W.'languages/'.$langData['name'].'/templates/'.$e.'/'.$e1.'/lang.php')){
						$packages[] = Library::array_merge(XML::parseXML(Files::read(_W.'templates/'.$e.'/'.$e1.'/descript.xml'), $a, $h, 'descript'), array('id' => _W.'languages/'.$langData['name'].'/templates/'.$e.'/'.$e1.'/lang.php'));
					}
				}
			}

			if(file_exists(_W.'languages/'.$langData['name'].'/templates/modules/')){
				foreach(Files::readFolder(_W.'languages/'.$langData['name'].'/templates/modules/') as $e){
					foreach(Files::readFolder(_W.'languages/'.$langData['name'].'/templates/modules/'.$e) as $e1){
						if(file_exists(_W.'languages/'.$langData['name'].'/templates/modules/'.$e.'/'.$e1.'/lang.php')){
							$packages[] = Library::array_merge(XML::parseXML(Files::read(_W.'templates/modules/'.$e.'/'.$e1.'/descript.xml'), $a, $h, 'descript'), array('id' => _W.'languages/'.$langData['name'].'/templates/modules/'.$e.'/'.$e1.'/lang.php'));
						}
					}
				}
			}
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'language_packages_list',
					array(
						'arr' => $packages,
						'actions' => array(
							'uninstall' => 'languagePackageUninstall&langId='.$this->values['id'],
							'refresh' => 'languagePackageUpdate&langId='.$this->values['id'],
						)
					),
					array('caption' => $this->funcName)
				),
				'big'
			)
		);
	}



	/********************************************************************************************************************************************************************

																		Работа с шаблонами

	*********************************************************************************************************************************************************************/

	protected function func_templates(){
		/*
			Общее управление шаблонами:
			1. Установка шаблона - загрузить архив / поставить уже загруженный / загрузить с сайта производителя (в т.ч. платный).
				В зависимости от действующих системных настроек шаблон ставится в базу или в папку. Во втором слуаче должна проверяться возможность записи.
			2. Связка шаблонов и сайтов
			3. Управление шаблоном в зависимости от его типа
			4. Удаление шаблона
			5. Шаблонизатор (правка шаблона)
		*/

		$type = db_main::Quot($this->values['type']);

		switch($type){
			case 'main': $typeText = 'сайта'; break;
			case 'admin': $typeText = 'админки'; break;
			case 'system': $typeText = 'системный'; break;
			case 'module': $typeText = 'модуля'; break;
		}

		$addit = array('type' => $type);
		if($type == 'admin' || $type == 'main'){
			$addit['dependSysTmp'] = $this->Core->getAllTemplates('system', false, true);
			$addit['languages'] = $this->Core->getLangs();
			$addit = Library::array_merge($addit, $this->Core->getAllModuleTemplates(true));
		}
		elseif($type == 'module'){
			$addit['modules'] = $this->DB->columnFetch(array('isset_modules', 'text', 'name', '', '`text`'));
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'templateModify2',
						'templateModify2',
						array('caption' => '{Call:Lang:core:core:sozdatpustoj:'.Library::serialize(array($typeText)).'}')
					),
					'new_template',
					$addit
				),
				array(),
				array('type' => $type),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'templates_list',
					array(
						'req' => array('templates', '*', $type == 'module' ? "`type` REGEXP('modules/')" : "`type`='$type'", "`sort`"),
						'form_actions' => array(
							'suspend' => '{Call:Lang:core:core:skryt}',
							'unsuspend' => '{Call:Lang:core:core:otkryt}'
						),
						'actions' => array(
							'name' => 'templateModify',
							'pages' => 'pagesManage',
							'clone' => 'cloneTemplate',
							'uninstall' => 'uninstallTemplate',
							'widgets' => 'widgets',
						),
						'action' => 'templatesActions&type='.$type,
						'searchForm' => array(
							'searchFields' => array(
								'name' => '{Call:Lang:core:core:identifikato}',
								'folder' => '{Call:Lang:core:core:papka}',
								'language' => '{Call:Lang:core:core:iazyk}'
							),
							'orderFields' => array(
								'name' => '{Call:Lang:core:core:identifikato5}',
								'folder' => '{Call:Lang:core:core:papke1}'
							),
							'searchMatrix' => array(
								'language' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), $this->DB->columnFetch(array('languages', 'text', 'name', "", "`name`")))
								)
							),
							'searchParams' => array('action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&type='.$type)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:spisokshablo}',
						'sortAction' => $this->path.'?mod='.$this->mod.'&func=sortListParams&backFunc='.library::encodeUrl('templates&type='.$type).'&table=templates'
					)
				),
				'big'
			)
		);
	}

	protected function func_templateModify(){
		/*
			Настройки шаблона
		*/

		$id = db_main::Quot($this->values['id']);
		$data = $this->DB->rowFetch(array('templates', '*', "`id`='$id'"));
		$vars = Library::unserialize($data['vars']);
		$type = $data['type'];

		$addit['modify'] = 1;
		$addit['matrix']['folder']['disabled'] = 1;
		$addit['type'] = $type;

		if($type == 'admin' || $type == 'main'){
			$addit['dependSysTmp'] = $this->Core->getAllTemplates('system', false, true);
			$addit['languages'] = $this->Core->getLangs();
			$addit = Library::array_merge($addit, $this->Core->getAllModuleTemplates(true));
			if(isset($vars['dependTemplates'])) $data = Library::array_merge($data, $vars['dependTemplates']);
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'templateModify2',
						'templateModify2',
						array('caption' => '{Call:Lang:core:core:nastrjkashab:'.Library::serialize(array($data['name'])).'}')
					),
					'new_template',
					$addit
				),
				$data,
				array('modify' => $id),
				'big'
			)
		);
	}

	protected function func_templateModify2(){
		/*
			Внесение изменений в шаблон
		 */

		$vars = array();

		if(!empty($this->values['modify'])){
			$id = db_main::Quot($this->values['modify']);
			$data = $this->DB->rowFetch(array('templates', array('type', 'vars'), "`id`='$id'"));
			$type = $data['type'];

			$vars = Library::unserialize($data['vars']);
			$this->isUniq('templates', array('name' => '{Call:Lang:core:core:takoeimiauzh}'), $id, " AND `type`='$type'");
			if(!$this->check()) return false;
		}
		else{
			if($id = $this->Core->createTemplate($this)){
				$type = $this->values['type'];
				if($type == 'module'){
					$err2 = !$this->Core->ftpSave(TMPL.'modules/'.$this->values['module'].'/'.$this->values['folder'].'/descript.xml', $this->Core->getTemplateXml($this->values));
					$err3 = !$this->Core->ftpSave(TMPL.'modules/'.$this->values['module'].'/'.$this->values['folder'].'/install.php', $this->Core->getInstallFileText($type.$this->values['folder']));
				}
				else{
					$err2 = !$this->Core->ftpSave(TMPL.$type.'/'.$this->values['folder'].'/descript.xml', $this->Core->getTemplateXml($this->values));
					$err3 = !$this->Core->ftpSave(TMPL.$type.'/'.$this->values['folder'].'/install.php', $this->Core->getInstallFileText($type.$this->values['folder']));
				}
			}
			else $err1 = true;

			if(!empty($err1) || !empty($err2) || !empty($err3)){
				$this->back('templates&type='.$this->values['type']);
				return false;
			}
		}

		if($type == 'admin' || $type == 'main'){
			$modTmps = $this->Core->getAllModuleTemplates(true);
			$vars['dependTemplates'] = array('sys_depend_tmp' => $this->values['sys_depend_tmp']);

			foreach($modTmps['dependModuleTmps'] as $i => $e){
				$vars['dependTemplates']['depend_tmp_'.$i] = $this->values['depend_tmp_'.$i];
			}
		}

		$fields = array(
			'name' => $this->values['name'],
			'vars' => $vars,
			'show' => isset($this->values['show']) ? $this->values['show'] : ''
		);

		$this->DB->Upd(array('templates', $fields, "`id`='$id'"));

		if(regExp::Match('modules/', $type)) $type = 'module';
		$this->refresh('templates&type='.$type);
		return true;
	}

	protected function func_cloneTemplate(){
		/*
			Клонирует шаблон, т.е. создает пустой шаблон и вносит в него копии всех страниц шаблона-донора
		*/

		$addit = array('type' => $this->values['type']);
		if($this->values['type'] == 'admin' || $this->values['type'] == 'main'){
			$addit['dependSysTmp'] = $this->Core->getAllTemplates('system', false, true);
			$addit['languages'] = $this->Core->getLangs();
			$addit = Library::array_merge($addit, $this->Core->getAllModuleTemplates(true));
		}

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'new_template',
						'cloneTemplate2',
						array('caption' => '{Call:Lang:core:core:kopirovatsha:'.Library::serialize(array($this->DB->cellFetch(array('templates', 'name', "`id`='".db_main::Quot($this->values['id'])."'")))).'}')
					),
					'new_template',
					$addit
				),
				array(),
				array('type' => $this->values['type'], 'id' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_cloneTemplate2(){
		/*
			Клонирует шаблон, т.е. создает пустой шаблон и вносит в него копии всех страниц шаблона-донора
		*/

		$data = $this->DB->rowFetch(array('templates', array('folder', 'type', 'vars'), "`id`='".db_main::Quot($this->values['id'])."'"));
		if(regExp::Match('modules/', $data['type'])){
			$t2 = regExp::Split('/', $data['type']);
			$this->values['module'] = $t2[1];
		}

		if(!$id = $this->Core->createTemplate($this)) return false;
		$this->DB->Upd(array('templates', array('vars' => $data['vars']), "`id`='$id'"));

		if($this->Core->getParam('templateSource') == 'folder'){
			if(regExp::Match('modules/', $data['type'])){
				$path1 = TMPL.'modules/'.$this->values['module'].'/'.$data['folder'].'/';
				$path2 = TMPL.'modules/'.$this->values['module'].'/'.$this->values['folder'].'/';
			}
			else{
				$path1 = TMPL.$data['type'].'/'.$data['folder'].'/';
				$path2 = TMPL.$this->values['type'].'/'.$this->values['folder'].'/';
			}

			$this->Core->ftpCopy($path1, $path2);
		}
		elseif($this->Core->getParam('templateSource') == 'db'){
			foreach($this->DB->columnFetch(array('template_pages', '*', 'id', "`template`='{$data['folder']}' AND `template_type`='".db_main::Quot($this->values['type'])."'")) as $i => $e){
				$e['template'] = $this->values['folder'];
				unset($e['id']);
				$this->DB->Ins(array('template_pages', $e));
			}
		}
		else throw new AVA_Exception('{Call:Lang:core:core:neopredeleno1}');

		if($this->values['type'] == 'main'){
			foreach($this->DB->columnFetch(array('template_blocks', '*', 'id', "`template`='{$data['folder']}'")) as $i => $e){
				$e['template'] = $this->values['folder'];
				unset($e['id']);
				$this->DB->Ins(array('template_blocks', $e));
			}
		}

		$this->refresh('templates&type='.$this->values['type']);
		return $id;
	}

	protected function func_templatesActions(){
		return $this->typeActions('templates', 'templates&type='.$this->values['type']);
	}

	protected function func_uninstallTemplate(){
		/*
			Деинсталляция шаблона. Должно быть предусмотрено, что нельзя удалить шаблон используемый по умолчанию хоть одним сайтом
		*/

		$data = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['id'])."'"));

		if(!file_exists(TMPL.$data['type'].'/'.$data['folder'].'/') || $this->Core->ftpRm(TMPL.$data['type'].'/'.$data['folder'].'/')){
			$this->DB->Del(array('template_pages', "`template`='{$data['folder']}' AND `template_type`='{$data['type']}'"));
			$this->DB->Del(array('templates', "`folder`='{$data['folder']}' AND `type`='{$data['type']}'"));
			if($data['type'] == 'main') $this->DB->Del(array('template_blocks', "`template`='{$data['folder']}'"));

			if(regExp::Match('modules/', $data['type'])) $data['type'] = 'module';
			$this->refresh('templates&type='.$data['type']);
			return true;
		}

		if(regExp::Match('modules/', $data['type'])) $data['type'] = 'module';
		$this->back('templates&type='.$data['type']);
		return false;
	}



	/********************************************************************************************************************************************************************

																		Страницы шаблонов

	*********************************************************************************************************************************************************************/

	protected function func_pagesManage(){
		/*
			Управление страницами шаблона
		*/

		$data = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['id'])."'"));
		$this->pathFunc = 'templates&type='.$data['type'];
		$this->funcName = '{Call:Lang:core:core:stranitsysha:'.Library::serialize(array($data['name'])).'}';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'new_page',
						'pagesManageNew',
						array('caption' => '{Call:Lang:core:core:dobavitstran}')
					),
					'new_page'
				),
				array(),
				array('templateId' => $this->values['id'])
			)
		);

		$listParams = array(
			'form_actions' => array(
				'delete' => '{Call:Lang:core:core:udalit}'
			),
			'actions' => array(
				'name' => 'pagesManageModify&templateId='.$this->values['id'],
				'clone' => 'pagesManageClone&templateId='.$this->values['id'],
				'correct' => 'pagesManageCorrect&templateId='.$this->values['id']
			),
			'action' => 'pagesManageActions&templateId='.$this->values['id']
		);

		if($this->Core->getParam('templateSource') == 'db'){
			$listParams['req'] = array('template_pages', '*', "`template`='{$data['folder']}' AND `template_type`='{$data['type']}'", "`url`");
		}
		elseif($this->Core->getParam('templateSource') == 'folder'){
			$listParams['arr'] = $this->Core->getTemplatePagesByXML($data['type'], $data['folder']);
		}

		$this->setContent($this->getListText($this->newList('template_pages', $listParams, array('caption' => '{Call:Lang:core:core:vsestranitsy:'.Library::serialize(array($data['name'])).'}'))));
	}

	protected function func_pagesManageNew(){
		/*
			Создание новой страницы
		*/

		$src = $this->Core->getParam('templateSource');
		$data = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));

		if(
			($src == 'folder' && file_exists(TMPL.$data['type'].'/'.$data['folder'].'/'.$this->values['url']) && $this->values['url'] != $this->values['modify']) ||
			($src == 'db' && $this->DB->cellFetch(array(
				'template_pages',
				'id',
				db_main::q(
					"`id`!=#0 AND `url`=#1 AND `template`=#2 AND `template_type`=#3",
					array($this->values['id'], $this->values['url'], $data['folder'], $data['type'])
				)
			)))
		){
			$this->setError('url', '{Call:Lang:core:core:takoeurlimia}');
		}

		if(!$this->check()) return false;

		if($src == 'folder'){
			$XML = $this->Core->readTemplateXML($data['type'], $data['folder']);
			if(!empty($this->values['modify'])){
				foreach($XML['descript']['pages'] as $i => $e){
					if($e['url'] == $this->values['modify']){
						$XML['descript']['pages'][$i] = array('url' => $this->values['url'], 'name' => $this->values['name'], 'type' => $this->values['type']);
						break;
					}
				}

				if($this->values['url'] != $this->values['modify']){
					$this->Core->ftpRename($this->Core->getTemplatePath($data['type'], $data['folder']).$this->values['modify'], $this->Core->getTemplatePath($data['type'], $data['folder']).$this->values['url']);
				}
			}
			else{
				$XML['descript']['pages'][] = array('url' => $this->values['url'], 'name' => $this->values['name'], 'type' => $this->values['type']);
				if(!empty($this->values['cloneId'])) $this->Core->ftpCopy($this->Core->getTemplatePath($data['type'], $data['folder']).$this->values['cloneId'], $this->Core->getTemplatePath($data['type'], $data['folder']).$this->values['url']);
				else $this->Core->ftpSave($this->Core->getTemplatePath($data['type'], $data['folder']).$this->values['url'], '');
			}

			$this->Core->ftpSave($this->Core->getTemplatePath($data['type'], $data['folder']).'descript.xml', XML::getXML($XML));
			$return = $this->values['url'];
		}
		elseif($src == 'db'){
			$fields = $this->fieldValues('url', 'name', 'type');
			if(!empty($this->values['cloneId'])) $fields['body'] = $this->DB->cellFetch(array('template_pages', 'body', "`id`='{$this->values['cloneId']}'"));
			$return = $this->typeIns('template_pages', $fields);
		}
		else throw new AVA_Exception('{Call:Lang:core:core:neopredeleno1}');

		if(!empty($this->values['modify'])) $this->refresh('pagesManage&id='.$this->values['templateId']);
		else $this->redirect('pagesManageCorrect&templateId='.$this->values['templateId'].'&id='.$this->values['url']);
		return $return;
	}

	protected function func_pagesManageModify(){
		/*
			Управление собственно страницей
		 */

		$data = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$this->pathFunc = 'templates&type='.$data['type'];
		$this->pathPoint = array('pagesManage&id='.$this->values['templateId'] => '{Call:Lang:core:core:stranitsysha:'.Library::serialize(array($data['name'])).'}');
		$values = $this->Core->getTemplatePageData($data['type'], $data['folder'], $this->values['id'], '*');

		$this->typeModify(
			false,
			'new_page',
			'pagesManageNew',
			array(
				'params' => array('caption' => '{Call:Lang:core:core:izmenitstran:'.Library::serialize(array($values['name'])).'}'),
				'hiddens' => array('modify' => $this->values['id'], 'templateId' => $this->values['templateId']),
				'values' => $values
			)
		);
	}

	protected function func_pagesManageClone(){
		/*
			Создает копию страницы
		*/

		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$data = $this->Core->getTemplatePageData($tData['type'], $tData['folder'], $this->values['id'], array('name', 'type'));

		$this->pathFunc = 'templates&type='.$tData['type'];
		$this->funcName = '{Call:Lang:core:core:kopirovatstr:'.Library::serialize(array($data['name'])).'}';
		$this->pathPoint = array('pagesManage&id='.$this->values['templateId'] => '{Call:Lang:core:core:stranitsysha:'.Library::serialize(array($tData['name'])).'}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'new_page',
						'pagesManageNew',
						array('caption' => $this->funcName)
					),
					'new_page'
				),
				array('type' => $data['type']),
				array('templateId' => $this->values['templateId'], 'cloneId' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_pagesManageCorrect(){
		/*
			Изменение данной конкретной страницы шаблона
		*/

		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$data = $this->Core->getTemplatePageData($tData['type'], $tData['folder'], $this->values['id'], array('name', 'body', 'type'));

		$this->pathFunc = 'templates&type='.$tData['type'];
		$this->funcName = '{Call:Lang:core:core:korrektirova:'.Library::serialize(array($data['name'])).'}';
		$this->pathPoint = array('pagesManage&id='.$this->values['templateId'] => '{Call:Lang:core:core:stranitsysha:'.Library::serialize(array($tData['name'])).'}');

		if($data['type'] == 'blocks'){
			$this->setContent(
				$this->getFormText(
					$this->addFormBlock(
						$this->newForm(
							'pagesManageBlocksCorrectNew',
							'pagesManageBlocksCorrectNew',
							array('caption' => '{Call:Lang:core:core:dobavitblokv}')
						),
						'new_tmpl_block'
					),
					array(),
					array('templateId' => $this->values['templateId'], 'tmplPgId' => $this->values['id'])
				)
			);

			$arr = array();
			$j = 0;

			foreach($this->Core->getTemplatePage($data['url'], $tData['folder'], $tData['type']) as $type => $templates){
				foreach($templates as $name => $entries){
					foreach($entries as $i => $e){
						$e['params']['type'] = $type;
						$e['params']['name'] = $name;
						$e['params']['id'] = $j;

						$e['params']['descript'] = $this->Core->getTemplatePageBlockNameByTmpl($data['url'], $name, $type, isset($e['params']['template']) ? $e['params']['template'] : '', $tData['folder'], $tData['type']);
						$arr[$j] = $e['params'];
						$j ++;
					}
				}
			}

			$this->setContent(
				$this->getListText(
					$this->newList(
						'template_page_blocks',
						array(
							'arr' => $arr,
							'form_actions' => array(
								'delete' => '{Call:Lang:core:core:udalit}'
							),
							'actions' => array(
								'descript' => 'pagesManageBlocksCorrect2&templateId='.$this->values['templateId'].'&tmplPgId='.$this->values['id']
							),
							'action' => 'pagesManageBlocksCorrectActions&templateId='.$this->values['templateId'].'&tmplPgId='.$this->values['id']
						),
						array(
							'caption' => '{Call:Lang:core:core:blokidlia:'.Library::serialize(array($data['name'])).'}'
						)
					)
				)
			);
		}
		else{
			$this->setContent(
				$this->getFormText(
					$this->addFormBlock(
						$this->newForm(
							'tmpl_page',
							'pagesManageCorrect2',
							array('caption' => $this->funcName)
						),
						'tmpl_page'
					),
					array('body' => $data['body']),
					array('templateId' => $this->values['templateId'], 'id' => $this->values['id']),
					'big'
				)
			);
		}
	}

	protected function func_pagesManageCorrect2(){
		/*
			Сохраняет новый шаблон
		*/

		if(!$this->check()) return false;

		if($this->Core->getParam('templateSource') == 'folder'){
			$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
			$return = $this->Core->ftpSave($this->Core->getTemplatePath($tData['type'], $tData['folder']).$this->values['id'], $this->values['body']);
		}
		elseif($this->Core->getParam('templateSource') == 'db'){
			$return = $this->DB->Upd(array('template_pages', array('body' => $this->values['body']), "`id`='".db_main::Quot($this->values['id'])."'"));
		}
		else throw new AVA_Exception('{Call:Lang:core:core:neopredeleno1}');

		if($return !== false) $this->refresh('pagesManage&id='.$this->values['templateId']);
		else $this->back('pagesManage&id='.$this->values['templateId']);
		return $return;
	}

	protected function func_pagesManageActions(){
		/*
			Удаляет страницы внутри шаболна
		*/

		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$XML = $this->Core->readTemplateXML($tData['type'], $tData['folder']);

		if($this->Core->getParam('templateSource') == 'folder'){
			if($this->values['action'] == 'delete'){
				foreach($this->values['entry'] as $i => $e){
					$this->Core->ftpRm($this->Core->getTemplatePath($tData['type'], $tData['folder']).$i);
					foreach($XML['descript']['pages'] as $i1 => $e1){
						if($e1['url'] == $i){
							unset($XML['descript']['pages'][$i1]);
						}
					}
				}

				$return = $this->Core->ftpSave($this->Core->getTemplatePath($tData['type'], $tData['folder']).'/descript.xml', XML::getXML($XML));
			}
		}
		elseif($this->Core->getParam('templateSource') == 'db'){
			$return = $this->typeActions('template_pages', 'pagesManage');
		}
		else throw new AVA_Exception('{Call:Lang:core:core:neopredeleno1}');

		$this->refresh('pagesManage&id='.$this->values['templateId']);
		return $return;
	}


	/********************************************************************************************************************************************************************

																Блоки шаблонов страницы

	*********************************************************************************************************************************************************************/

	protected function func_pagesManageBlocksCorrectNew(){
		/*
			Добавляет блок в списко
		*/

		if(!$this->check()) return false;

		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$data = $this->Core->getTemplatePageData($tData['type'], $tData['folder'], $this->values['tmplPgId'], array('url'));
		$XML = array('template' => array());
		$attr = array();

		foreach($this->Core->getTemplatePage($data['url'], $tData['folder'], $tData['type']) as $type => $templates){
			foreach($templates as $name => $entries){
				foreach($entries as $i => $e){
					$e['params']['descript'] = $this->Core->getTemplatePageBlockNameByTmpl($data['url'], $name, $type, isset($e['params']['template']) ? $e['params']['template'] : '', $tData['folder'], $tData['type']);
					$e['params']['type'] = $type;
					$e['params']['name'] = $name;

					$XML['template']['item'][] = $e['content'];
					$attr['template']['item'][]['@attr'] = $e['params'];
				}
			}
		}

		$params = $this->fieldValues(array('descript', 'name', 'template', 'type'));

		if(!isset($this->values['modify'])){
			$XML['template']['item'][] = $this->values['body'];
			$attr['template']['item'][]['@attr'] = $params;
		}
		else{
			$XML['template']['item'][$this->values['modify']] = $this->values['body'];
			$attr['template']['item'][$this->values['modify']]['@attr'] = $params;
		}

		$XML = XML::getXML($XML, false, $attr, false);
		if($this->Core->getParam('templateSource') == 'folder') $return = $this->Core->ftpSave($this->Core->getTemplatePath($tData['type'], $tData['folder']).$data['url'], $XML);
		elseif($this->Core->getParam('templateSource') == 'db') $return = $this->DB->Upd(array('template_pages', array('body' => $XML), "`id`='".db_main::Quot($this->values['tmplPgId'])."'"));

		if($return !== false) $this->refresh('pagesManageCorrect&templateId='.$this->values['templateId'].'&id='.$this->values['tmplPgId']);
		else $this->back('pagesManageCorrect&templateId='.$this->values['templateId'].'&id='.$this->values['tmplPgId']);
		return $return;
	}

	protected function func_pagesManageBlocksCorrect2(){
		/*
			Правка блока
		*/

		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$data = $this->Core->getTemplatePageData($tData['type'], $tData['folder'], $this->values['tmplPgId'], array('url'));
		$values = array();

		foreach($this->Core->getTemplatePage($data['url'], $tData['folder'], $tData['type']) as $type => $templates){
			foreach($templates as $name => $entries){
				foreach($entries as $i => $e){
					$e['params']['body'] = $e['content'];
					$e['params']['descript'] = $this->Core->getTemplatePageBlockNameByTmpl($data['url'], $name, $type, isset($e['params']['template']) ? $e['params']['template'] : '', $tData['folder'], $tData['type']);
					$e['params']['type'] = $type;
					$e['params']['name'] = $name;
					$values[] = $e['params'];
				}
			}
		}

		$this->pathFunc = 'templates&type='.$tData['type'];
		$this->funcName = '{Call:Lang:core:core:izmenitblok:'.Library::serialize(array($values[$this->values['id']]['descript'])).'}';
		$this->pathPoint = array(
			'pagesManageCorrect&templateId='.$this->values['templateId'].'&id='.$this->values['tmplPgId'] => '{Call:Lang:core:core:korrektirova:'.Library::serialize(array($data['name'])).'}',
			'pagesManage&id='.$this->values['templateId'] => '{Call:Lang:core:core:stranitsysha:'.Library::serialize(array($tData['name'])).'}',
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'pagesManageBlocksCorrectNew',
						'pagesManageBlocksCorrectNew',
						array('caption' => $this->funcName)
					),
					'new_tmpl_block',
					array('modify' => 1)
				),
				$values[$this->values['id']],
				array('templateId' => $this->values['templateId'], 'tmplPgId' => $this->values['tmplPgId'], 'modify' => $this->values['id']),
				'big'
			)
		);
	}

	protected function func_pagesManageBlocksCorrectActions(){
		/*
			Удаление блоков
		*/

		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($this->values['templateId'])."'"));
		$data = $this->Core->getTemplatePageData($tData['type'], $tData['folder'], $this->values['tmplPgId'], array('url'));
		$XML = array('template' => array());
		$attr = array();

		foreach($this->Core->getTemplatePage($data['url'], $tData['folder'], $tData['type']) as $type => $templates){
			foreach($templates as $name => $entries){
				foreach($entries as $i => $e){
					$e['params']['descript'] = $this->Core->getTemplatePageBlockNameByTmpl($data['url'], $name, $type, isset($e['params']['template']) ? $e['params']['template'] : '', $tData['folder'], $tData['type']);
					$e['params']['type'] = $type;
					$e['params']['name'] = $name;

					$XML['template']['item'][] = $e['content'];
					$attr['template']['item'][]['@attr'] = $e['params'];
				}
			}
		}

		if($this->values['action'] == 'delete'){
			foreach($this->values['entry'] as $i => $e){
				unset($XML['template']['item'][$i], $attr['template']['item'][$i]);
			}
		}

		$XML = XML::getXML($XML, false, $attr, false);
		if($this->Core->getParam('templateSource') == 'folder') $return = $this->Core->ftpSave($this->Core->getTemplatePath($tData['type'], $tData['folder']).$data['url'], $XML);
		elseif($this->Core->getParam('templateSource') == 'db') $return = $this->DB->Upd(array('template_pages', array('body' => $XML), "`id`='".db_main::Quot($this->values['tmplPgId'])."'"));

		$this->refresh('pagesManageCorrect&templateId='.$this->values['templateId'].'&id='.$this->values['tmplPgId']);
		return $return;
	}


	/********************************************************************************************************************************************************************

																			Виджеты

	*********************************************************************************************************************************************************************/

	protected function func_widgets(){
		/*
			Виджеты
		*/

		$id = isset($this->values['templateId']) ? $this->values['templateId'] : $this->values['id'];
		$tData = $this->DB->rowFetch(array('templates', array('folder', 'type', 'name'), "`id`='".db_main::Quot($id)."'"));
		$fields = $this->fieldValues(array('name', 'text', 'body', 'template', 'show', 'sort'));
		$fields['template'] = $tData['folder'];

		$return = $this->typicalMain(
			array(
				'name' => 'widgets',
				'func' => 'widgets&id='.$id,
				'table' => 'template_blocks',
				'caption' => 'Добавить визуальный блок',
				'listParams' => array(
					'req' => array('template_blocks', '*', "`template`='{$tData['folder']}'", "`sort`"),
					'actions' => array('text' => $this->func.'&type_action=modify&templateId='.$id),
					'searchForm' => array(
						'searchFields' => array(
							'text' => 'Имя',
							'name' => 'Идентификатор',
							'body' => 'Содержимое блока',
							'show' => ''
						),
						'orderFields' => array('text' => 'имени', 'name' => 'идентификатору'),
						'searchParams' => array(
							'action' => $this->path.'?mod='.$this->mod.'&func='.$this->func.'&id='.$id
						)
					)
				),
				'fields' => $fields
			)
		);

		$this->pathFunc = 'templates&type=main';
		$this->funcName = 'Визуальные блоки шаблона "'.$tData['name'].'"';
		return $return;
	}


	/********************************************************************************************************************************************************************

																				Шаблоны писем

	*********************************************************************************************************************************************************************/

	protected function func_mailTemplates(){
		/*
			Шаблоны писем
		*/

		$modules = Library::array_merge($GLOBALS['Core']->getModules(), array('main' => 'Main', 'core' => 'Core'));

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'mailTemplateModify2',
						'mailTemplateModify2',
						array('caption' => '{Call:Lang:core:core:dobavitshabl}')
					),
					'mail_template',
					array(
						'modules' => $modules
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'mail_templates_list',
					array(
						'req' => array( 'mail_templates', array('id', 'mod', 'name', 'text', 'subj', 'sender', 'sender_eml', 'system'), '', "`sort`" ),
						'actions' => array(
							'text' => 'mailTemplateModify'
						),
						'form_actions' => array(
							'delete' => '{Call:Lang:core:core:udalit}'
						),
						'action' => 'mailTemplateActions',
						'searchForm' => array(
							'searchFields' => array(
								'mod' => 'Модуль',
								'text' => 'Имя',
								'name' => 'Идентификатор',
								'format' => 'Формат',
								'subj' => 'Тема',
								'body' => 'Текст шаблона',
								'sender' => 'Отправитель',
								'sender_eml' => 'E-mail отправителя',
							),
							'orderFields' => array(
								'text' => 'имени',
								'name' => 'идентификатору',
								'subj' => 'теме',
								'body' => 'тексту шаблона',
								'sender' => 'отправителю',
								'sender_eml' => 'e-mail отправителя',
							),
							'searchMatrix' => array(
								'mod' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => 'Любой'), $modules)
								),
								'format' => array(
									'type' => 'select',
									'additional' => array('' => 'Любой', 'text/html' => 'HTML', 'text/plain' => '{Call:Lang:core:core:obychnyjteks}', 'multipart/related' => '{Call:Lang:core:core:priattachenn}')
								)
							)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:shablonypise}'
					)
				)
			)
		);
	}

	protected function func_mailTemplateModify(){
		/*
			Изменение шаблона письма
		*/

		$id = db_main::Quot($this->values['id']);
		$values = $this->DB->rowFetch(array('mail_templates', '*', "`id`='$id'"));
		$this->pathFunc = 'mailTemplates';
		$this->funcName = '{Call:Lang:core:core:shablonpisma:'.Library::serialize(array($values['text'])).'}';

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'mail_template',
						'mailTemplateModify2',
						array('caption' => $this->funcName)
					),
					'mail_template',
					array(
						'modify' => $id,
						'modules' => Library::array_merge($GLOBALS['Core']->getModules(), array('main' => 'Main', 'core' => 'Core'))
					)
				),
				$values,
				array('modify' => $id),
				'big'
			)
		);
	}

	protected function func_mailTemplateModify2(){
		/*
			Изменение шаблона письма
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->isUniq('mail_templates', array('name' => '{Call:Lang:core:core:takojidentif}', 'text' => '{Call:Lang:core:core:takoeimiauzh}'), $id);

		$id = $this->typeIns(
			'mail_templates',
			$this->fieldValues(
				array(
					'text',
					'body',
					'name',
					'mod',
					'format',
					'subj',
					'sender_eml',
					'sender',
					'notify_success_body',
					'notify_success',
					'notify_success_subj',
					'notify_fail_body',
					'notify_fail',
					'notify_fail_subj',
					'notify_eml',
					'notify_sender_eml',
					'notify_sender'
				)
			),
			'mailTemplates'
		);

		if(empty($this->values['modify'])) $this->redirect('mailTemplateModify&id='.$id);
		return $id;
	}

	protected function func_mailTemplateActions(){
		/*
			Удаление шаблонов письма
		*/

		$this->typeActions('mail_templates', 'mailTemplates', array(), " AND !`system`");
	}



	/********************************************************************************************************************************************************************

																	Отправка писем

	*********************************************************************************************************************************************************************/

	protected function func_mails(){
		/*
			Список всех отправленных писем
		*/

		$this->setContent(
			$this->getListText(
				$this->newList(
					'mails_list',
					array(
						'req' => array('mails', array('id', 'date', 'in_work', 'senddate', 'attempts', 'status', 'mod', 'func', 'format', 'eml', 'subj', 'body', 'sender_eml', 'sender'), '', "`date` DESC"),
						'form_actions' => array(
							'markSend' => '{Call:Lang:core:core:pometitotpra}',
							'markNotSend' => '{Call:Lang:core:core:pometitneotp}',
							'markDel' => '{Call:Lang:core:core:udalitizoche}',
							'resetQueue' => '{Call:Lang:core:core:sbrositschet}',
							'delete' => '{Call:Lang:core:core:udalit}'
						),
						'action' => 'mailsActions',
						'actions' => array(
							'data' => 'mailData',
							'resend' => 'resendMail'
						),
						'searchForm' => array(
							'searchFields' => array(
								'date' => '{Call:Lang:core:core:vnesenovoche}',
								'in_work' => '{Call:Lang:core:core:nachataotpra}',
								'senddate' => '{Call:Lang:core:core:otpravleno}',
								'eml' => '{Call:Lang:core:core:emailpolucha}',
								'subj' => '{Call:Lang:core:core:tema}',
								'body' => '{Call:Lang:core:core:soderzhanie}',
								'attempts' => '{Call:Lang:core:core:chislopopyto1}',
								'status' => '{Call:Lang:core:core:status}',
								'mod' => '{Call:Lang:core:core:otpravivshij}',
								'func' => '{Call:Lang:core:core:otpravivshai}',
								'format' => '{Call:Lang:core:core:format}',
								'sender_eml' => '{Call:Lang:core:core:emailotpravi2}',
								'sender' => '{Call:Lang:core:core:otpravitel}',
								'extra' => '{Call:Lang:core:core:shapkapisma}',
							),
							'searchMatrix' => array(
								'in_work' => array('type' => 'calendar'),
								'senddate' => array('type' => 'calendar'),
								'attempts' => array('type' => 'gap'),
								'status' => array(
									'type' => 'select',
									'additional' => array('' => '{Call:Lang:core:core:vse}', '0' => '{Call:Lang:core:core:vocheredi}', '1' => '{Call:Lang:core:core:otpravleno}', '2' => '{Call:Lang:core:core:neotpravleno}')
								),
								'mod' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), $this->Core->getModules())
								),
								'format' => array(
									'type' => 'select',
									'additional' => array('' => '{Call:Lang:core:core:vse}', 'text/plain' => 'text/plain', 'text/html' => 'text/html', 'multipart/form-data' => 'multipart/form-data')
								),
							),
							'orderFields' => array(
								'date' => '{Call:Lang:core:core:vneseniiu}',
								'in_work' => '{Call:Lang:core:core:nachaluotpra}',
								'senddate' => '{Call:Lang:core:core:okonchaniiuo}',
								'attempts' => '{Call:Lang:core:core:chislupopyto}',
								'subj' => '{Call:Lang:core:core:teme}',
								'eml' => '{Call:Lang:core:core:emailpolucha1}',
								'sender_eml' => '{Call:Lang:core:core:emailotpravi4}'
							)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:spisokpisem}'
					)
				),
				'big'
			)
		);
	}

	protected function func_mailData(){
		/*
			Просмотр письма
		*/

		$this->typeModify(
			array(
				'mails',
				array('format', 'eml', 'subj', 'body', 'sender_eml', 'sender', 'extra', 'notify_success', 'notify_fail', 'notify_eml', 'notify_sender_eml', 'notify_sender'),
				"`id`='".db_main::Quot($this->values['id'])."'"
			),
			'mails',
			'mailData2',
			array('caption' => '{Call:Lang:core:core:parametrypis}')
		);
	}

	protected function func_mailData2(){
		/*
			Смена параметров письма
		*/

		return $this->typeIns('mails', $this->fieldValues(array('format', 'eml', 'subj', 'body', 'sender_eml', 'sender', 'extra', 'notify_success', 'notify_fail', 'notify_eml', 'notify_sender_eml', 'notify_sender')), 'mails');
	}

	protected function func_resendMail(){
		/*
			Переотправка e-mail
		*/

		$return = mail::sendWithQueue($this->values['id']);
		$this->refresh('mails');
		return $return;
	}

	protected function func_mailsActions(){
		/*
			Переотправка e-mail
		*/

		if(empty($this->values['entry'])){
			$this->back('mails');
			return false;
		}

		$filter = $this->getEntriesWhere();

		switch($this->values['action']){
			case 'delete':
				$return = $this->DB->Del(array('mails', $filter));
				break;

			case 'markSend':
				$return = $this->DB->Upd(array('mails', array('status' => 1), $filter));
				break;

			case 'markNotSend':
				$return = $this->DB->Upd(array('mails', array('status' => 0), $filter));
				break;

			case 'markDel':
				$return = $this->DB->Upd(array('mails', array('status' => 2), $filter));
				break;

			case 'resetQueue':
				$return = $this->DB->Upd(array('mails', array('attempts' => 0), $filter));
				break;
		}

		$this->refresh('mails');
		return $return;
	}

	protected function func_mailsSend(){
		/*
			Вывод формы отправки письма
		*/

		$this->setContent($this->sendMailForm());
	}

	protected function func_mailsSend2(){
		/*
			Рассылка писем
		*/

		if(!empty($this->values['users'])) $emails = $this->Core->DB->columnFetch(array('users', '*', 'eml', "`show`=1".($this->values['users'] == 'all' ? "" : ' AND '.$this->getEntriesWhere($this->values['users']))));
		else $emails = array($this->values['eml'] => array());

		foreach($emails as $i => $e){
			$this->mail(
				$i,
				$this->fieldValues(array('subj', 'body', 'format', 'sender_eml', 'sender', 'extra', 'notify_success', 'notify_fail', 'notify_eml', 'notify_sender', 'notify_sender_eml')),
				$e
			);
		}

		$this->refresh('mails', 'Письма помещены в очередь для отправки');
	}

	private function sendMailForm($users = 'all', $values = array()){
		/*
			Форма рассылки писем
			users - all=все, array=по списку, false=ввести e-mail на месте
		*/

		return $this->getFormText(
			$this->addFormBlock(
				$this->newForm(
					'mailsSend2',
					'mailsSend2',
					array('caption' => 'Отправить письмо')
				),
				'mails',
				array('users' => $users)
			),
			$values,
			array(),
			'big'
		);
	}


	/********************************************************************************************************************************************************************

																	Управление URL (SEF)

	*********************************************************************************************************************************************************************/

	protected function func_url(){
		/*
			Управление SEF-URL
			Вводится новый URL и предыдущий, при этом предыдущим может быть как строка GET-запроса, так и URL приголный для разложения
		*/

		$fields = array();
		if(!empty($this->values['site'])){
			$fields = array(
				'url' => $this->values['url'],
				'rewrited' => $this->values['rewrited'],
				'site' => $this->values['site']
			);

			$id = empty($this->values['modify']) ? '' : $this->values['modify'];
			$this->isUniq('urls', array('rewrited' => '{Call:Lang:core:core:takojurluzhe1}'), $id);
		}

		$this->typicalMain(
			array(
				'name' => 'urls',
				'func' => 'url',
				'caption' => '{Call:Lang:core:core:dobaviturl}',
				'listParams' => array(
					'req' => array('urls', '*'),
					'form_actions' => array(
						'delete' => '{Call:Lang:core:core:udalit}'
					),
					'actions' => array(
						'params' => 'url&type_action=modify'
					),
					'searchForm' => array(
						'searchFields' => array(
							'url' => '{Call:Lang:core:core:urliliegocha}',
							'rewrited' => '{Call:Lang:core:core:perepisyvaem}'
						),
						'orderFields' => array('url' => 'URL', 'rewrited' => '{Call:Lang:core:core:perepisyvaem1}')
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:vsepravilaza}'
				),
				'fields' => $fields
			)
		);

		$this->funcName = '{Call:Lang:core:core:upravlenieur}';
	}

	protected function func_urlGenRights(){
		/*
			Правила генерации URL
				- Правило отмены имени модуля и функции. Может быть только одно, предполагает что вообще все запросы тправляются к данному модулю и функции
				- Правило замены имени модуля и функции. Может быть использована некоторая закономерность, которая преобразует URL, например некоторое имя до
					или после искомого URL
				- Правило исключения имен переменных в запросе. Т.е. переменные располагаются некоторым образом и их имена не отображаются в URL
				- Правило включения неиспользуемых комбинаций в URL. Т.е. правило по которому в строку включается некоторый текст, который просто денонсируется в процессе
					разбора URL
				- Правило свободной обработки URL. Т.е. URL передается какому-то исполняемому коду, который его обрабатывает и возвращает результат
		*/

		$modules = $this->Core->getModules();

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'urlGenRightsNew',
						'urlGenRightsNew',
						array('caption' => 'Добавить правило')
					),
					'url_rights',
					array(
						'modules' => $modules
					)
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'url_rights_list',
					array(
						'req' => array('url_gen_rights', '*'),
						'form_actions' => array(
							'suspend' => 'Отключить',
							'unsuspend' => 'Включить',
							'delete' => 'Удалить'
						),
						'actions' => array(
							'text' => 'urlGenRightsData'
						),
						'action' => 'urlGenRightsActions',
						'searchForm' => array(
							'searchFields' => array(
								'text' => 'Имя',
								'name' => 'Идентификатор',
								'type' => 'Тип',
								'mod' => 'Модуль',
								'func' => 'Функция',
								'show' => ''
							),
							'searchMatrix' => array(
								'type' => array(
									'type' => 'select',
									'additional' => array()
								),
								'mod' => array(
									'type' => 'select',
									'additional' => Library::array_merge(array('' => 'Все'), $modules)
								),
							),
							'orderFields' => array(
								'text' => 'имени',
								'name' => 'идентификатору'
							)
						)
					),
					array(
						'caption' => 'Список правил'
					)
				)
			)
		);
	}

	protected function func_urlGenRightsNew(){
		/*
			Новое правило
		*/

		$id = empty($this->values['modify']) ? '' : $this->values['modify'];
		$this->isUniq('url_gen_rights', array('text' => '{Call:Lang:core:core:takoeimiauzh}', 'name' => '{Call:Lang:core:core:takojidentif}'), $id);
		if($this->DB->cellFetch(array('url_gen_rights', 'name', "`mod`='{$this->values['mod']}' AND `func`='{$this->values['func']}' AND `type`='{$this->values['type']}' AND `id`!='$id'"))){
			$this->setError('type', 'Правило этого типа уже существует для этого модуля и функции');
		}

		$ins = $this->fieldValues(array('name', 'text', 'type', 'mod', 'func', 'show'));
		$ins['vars'] = $this->fieldValues(array('replaceRight', 'dropVarsList', 'dropVarsDlm', 'dropVarsDlm2', 'dropVarsLastDlm', 'dropVarsEmpty', 'unuseTextPre', 'unuseTextPost', 'evalGetUrl', 'evalGetVars'));
		$ins['vars']['dropVarsList'] = regExp::Split("\n", $ins['vars']['dropVarsList']);

		foreach($ins['vars']['dropVarsList'] as $i => $e) $ins['vars']['dropVarsList'][$i] = trim($e);
		return $this->typeIns('url_gen_rights', $ins, 'urlGenRights');
	}

	protected function func_urlGenRightsData(){
		/*
			Изменеие параметров правила
		*/

		$this->pathFunc = 'urlGenRights';

		$params['values'] = $this->DB->rowFetch(array('url_gen_rights', '*', "`id`='{$this->values['id']}'"));
		$params['values'] = Library::array_merge(Library::unserialize($params['values']['vars']), $params['values']);
		$params['values']['dropVarsList'] = implode("\n", $params['values']['dropVarsList']);

		$params['caption'] = 'Правило "'.$params['values']['text'].'"';
		$params['formData'] = array('modules' => $this->Core->getModules());
		return $this->typeModify(false, 'url_rights', 'urlGenRightsNew', $params);
	}

	protected function func_urlGenRightsActions(){
		/*
			Массовые действия над правилами
		*/

		return $this->typeActions('url_gen_rights', 'urlGenRights');
	}


	/********************************************************************************************************************************************************************

																Дополнительные базы данных

	*********************************************************************************************************************************************************************/

	protected function func_database(){
		/*
			Новая база данных для установки туда модулей и пакетов
			При установке модуля зависящего от других может быть установлено требование что он должен ставиться в ту же БД что и те от которых он зависит
			Для всех таблиц модуля прописуется в качестве префикса URL-наименование этого модуля.
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'new_database',
						'databaseAdd',
						array(
							'caption' => '{Call:Lang:core:core:dobavitbazud}',
							'comment' => '{Call:Lang:core:core:dobavlennaia}'
						)
					),
					'database',
					array('dbDrivers' => $this->Core->getDBDrivers())
				)
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'databases',
					array(
						'req' => array('databases', '*', '', "`name`"),
						'actions' => array(
							'name' => 'databasesModify',
							'del' => 'databasesDel'
						),
						'searchForm' => array(
							'searchFields' => array(
								'name' => '{Call:Lang:core:core:imiabazy}',
								'host' => '{Call:Lang:core:core:khost}',
								'user' => '{Call:Lang:core:core:polzovatel}',
								'prefix' => '{Call:Lang:core:core:prefiks1}'
							),
							'orderFields' => array(
								'host' => '{Call:Lang:core:core:khostu}',
								'user' => '{Call:Lang:core:core:polzovateliu}',
								'name' => '{Call:Lang:core:core:imenibazy}',
								'prefix' => '{Call:Lang:core:core:prefiksu}'
							)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:dopolnitelny5}'
					)
				)
			)
		);
	}

	protected function func_databaseAdd(){
		/*
			Добавляет запись о БД
		*/

		if(!Library::callClass(
			'db_'.$this->values['driver'],
			'checkConnect',
			array(
				$this->values['host'],
				$this->values['user'],
				$this->values['pwd'],
				$this->values['name']
			),
			true
		)){
			$this->setError('name', '{Call:Lang:core:core:neudalosusta2}');
		}
		if(!$this->check()) return false;

		$fields = $this->fieldValues(array('ident', 'name', 'host', 'user', 'prefix', 'driver'));
		$fields['pwd'] = Library::crypt($this->values['pwd']);
		$fields['vars'] = call_user_func(array('db_'.$fields['driver'], 'getConnectParams'), $this, $this->values);
		return $this->typeIns('databases', $fields, 'database');
	}

	protected function func_databasesModify(){
		/*
			Модификация записи о БД
		*/

		$values = $this->DB->rowFetch(array('databases', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		$values['pwd'] = Library::Decrypt($values['pwd']);
		$values = Library::array_merge($values, Library::unserialize($values['vars']));

		$this->pathFunc = 'database';
		$this->funcName = '{Call:Lang:core:core:izmenitparam1:'.Library::serialize(array($values['name'])).'}';

		$this->typeModify(
			false,
			'database',
			'databaseAdd',
			array(
				'params' => array('caption' => $this->funcName),
				'values' => $values,
				'formData' => array(
					'dbDrivers' => $this->Core->getDBDrivers()
				)
			)
		);
	}

	protected function func_databasesDel(){
		/*
			Удаляет запись о БД
		*/

		$id = $this->values['id'];
		$this->DB->Del(array('databases', "`id`='".db_main::Quot($id)."'"));
		$this->refresh('database');
		return true;
	}



	/********************************************************************************************************************************************************************

																	Управление встроенным кроном

	*********************************************************************************************************************************************************************/

	protected function func_cron(){
		/*
			Назначение заданий

			Крон вызывается как CGI-скрипт, либо http-запросом (если CGI-исполнение отключено) из index.php каждый раз при запуске этого файла
			Крон проверяет в БД все регулярные запросы которые должны быть исполнены к настоящему моменту, но пока не исполнены, выставляет в БД для них новый срок,
			и одновременно выставляет в таблицу одноразовых заданий запрос на выполнение.

			После этого запускается другая часть крона которая считывает все текущие необработанные запросы и выполняет их

			Запросы могут быть типа:
				mail - отправка письма
				http - http-запрос к удаленному серверу
				eval - Eval исполнение куска кода
				shell - Команда запускаемая в оболочке shell
				callMod - Обращение к определенной функции определенного модуля с определенными параметрами
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'cron',
						'cronAdd',
						array( 'caption' => '{Call:Lang:core:core:dobavitzadan}' )
					),
					'cron'
				),
				array(),
				array(),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'cron_list',
					array(
						'req' => array('cron', '*'),
						'form_actions' => array(
							'delete' => '{Call:Lang:core:core:udalit}',
							'suspend' => 'Приостановить',
							'unsuspend' => 'Восстановить'
						),
						'actions' => array(
							'modify' => 'cronModify'
						),
						'action' => 'cronActions'
					),
					array(
						'caption' => '{Call:Lang:core:core:vsezadaniiak}'
					)
				),
				'big'
			)
		);
	}

	protected function func_cronAdd(){
		/*
			Добавляет команду Cron
		*/

		if(!empty($this->values['eval'])) eval($this->values['command']);
		$fields = $this->fieldValues(array('month', 'day', 'week', 'hour', 'minute', 'tick', 'name', 'limit', 'command', 'comment'));
		if(empty($this->values['modify'])) $fields['module'] = 'main';

		if(empty($this->values['modify'])) $this->isUniq('cron', array('name' => '{Call:Lang:core:core:takojidentif}'));
		if($this->values['limit']) $fields['limit'] = $this->values['limit'];
		return $this->typeIns('cron', $fields, 'cron');
	}

	protected function func_cronModify(){
		$values = $this->DB->rowFetch(array('cron', '*', "`id`='".db_main::Quot($this->values['id'])."'"));
		if($values['limit']) $values['limit'] = Dates::date("d.m.Y H:i:s", $values['limit']);
		else $values['limit'] = '';
		return $this->typeModify('', 'cron', 'cronAdd', array('caption' => '{Call:Lang:core:core:izmeneniekro}', 'values' => $values, 'tmplName' => 'big'));
	}

	protected function func_cronActions(){
		$this->typeActions('cron', 'cron');
	}


	/********************************************************************************************************************************************************************

																			Результаты исполнения

	*********************************************************************************************************************************************************************/

	protected function func_tasks(){
		/*
			Результаты исполнения задач крон
		*/

		$this->setContent(
			$this->getListText(
				$this->newList(
					'cron_tasks',
					array(
						'req' => array( 'tasks', '*', '', "`added` DESC" ),
						'form_actions' => array(
							'delete' => '{Call:Lang:core:core:udalit}'
						),
						'action' => 'taskActions',
						'searchForm' => array(
							'searchFields' => array(
								'added' => '{Call:Lang:core:core:vremiavnesen}',
								'runned' => '{Call:Lang:core:core:startvypolne}',
								'execute' => '{Call:Lang:core:core:zaversheniev}',
								'status' => '{Call:Lang:core:core:status}',
								'result' => '{Call:Lang:core:core:rezultat}',
								'result_text' => '{Call:Lang:core:core:otvetnoesoob}',
								'comment' => '{Call:Lang:core:core:kommentarij}'
							),
							'orderFields' => array(
								'status' => '{Call:Lang:core:core:statusu}',
								'result' => '{Call:Lang:core:core:rezultatu}',
								'added' => '{Call:Lang:core:core:vremenivnese}',
								'runned' => '{Call:Lang:core:core:startu}',
								'execute' => '{Call:Lang:core:core:zaversheniiu}'
							),
							'searchMatrix' => array(
								'added' => array('type' => 'calendar'),
								'runned' => array('type' => 'calendar'),
								'execute' => array('type' => 'calendar'),
								'status' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:core:core:liuboj}',
										'0' => '{Call:Lang:core:core:nevypolnialo}',
										'1' => '{Call:Lang:core:core:vypolneno}',
										'2' => '{Call:Lang:core:core:ispolniaetsi}',
										'3' => '{Call:Lang:core:core:ostanovlenop}'
									)
								),
								'result' => array(
									'type' => 'select',
									'additional' => array(
										'' => '{Call:Lang:core:core:liuboj}',
										'1' => 'TRUE',
										'0' => 'FALSE'
									)
								)
							)
						)
					),
					array(
						'caption' => '{Call:Lang:core:core:spisokzadach}'
					)
				),
				'big'
			)
		);
	}

	protected function func_taskActions(){
		return $this->typeActions('tasks', 'tasks');
	}


	/********************************************************************************************************************************************************************

																			Миграция

	*********************************************************************************************************************************************************************/

	protected function func_migration(){
		/*
			Мигратор из других биллингов и с разных панелей управления
			На этом шаге выбирается из какой системы мигрировать
		*/

		$this->setMigrationForm();
	}

	protected function func_migration2(){
		/*
			На этом шаге выбирается управление передается комплекту функций мигратора
		*/

		if($return = $this->{$this->values['from'].$this->values['step'].'Migration'}()){
			$this->back('migration', $this->getErrorsList().$this->getPrintMessages(), '', '{Call:Lang:core:core:migratsiiaza}');
		}
		return $return;
	}

	private function setMigrationForm($step = ''){
		$hiddens = $this->values;
		$hiddens['step'] = $step;

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'migration2',
						'migration2',
						array(
							'caption' => '{Call:Lang:core:core:migratsiiaak}'
						)
					),
					'migration'.(isset($this->values['from']) ? $this->values['from'] : '').$step
				),
				array(),
				$hiddens,
				'big'
			)
		);
	}

	public function ava2Migration(){
		if(!$this->check()) return false;
		$this->setMigrationForm('1');
	}

	public function ava21Migration(){
		if(!file_exists($this->values['path'])) $this->setError('path', '{Call:Lang:core:core:nevernoukaza}');
		if(!file_exists($this->values['path'].'inc/sql.php')) $this->setError('path', '{Call:Lang:core:core:nenajdenfajl4:'.Library::serialize(array($this->values['path'])).'}');
		if(!file_exists($this->values['path'].'inc/settings.php')) $this->setError('path', '{Call:Lang:core:core:nenajdenfajl5:'.Library::serialize(array($this->values['path'])).'}');

		if(!$this->check()) return false;
		$this->setMigrationForm('2');
	}

	public function ava22Migration(){
		/*
			Проводит перенос всех пользователей, аккаунтов, платежей, тарифов и пр. поебени в БД AVA V.3

			Биллинг
			 + 0. Перенести настройки валют и способов оплаты
			 + 1. Переносятся все сервера (servers)
			 + 1. Переносятся все группы тарифов
			 + 2. Переносятся все тарифы (rates_*)
			3. Настройки цен на составляющие ТП
			4. Перенести скидки
			5. Перенести диллерские уровни
			 + 6. Переносятся все аккаунты (agreements)
			 + 7. Переносятся все сведения о заказахъ (* (additionaldedic напр.), started_domains, started_users)
			 + 8. Переносятся все счета (orders)
			 + 9. Переносятся все сведения о платежахъ (payments)
			10. Переносятся все сведения о отправленных письмах (mails)
			11. Переносятся все сведения о сменах ТП(change_rate)
			12. Переносятся все остальное (ban_wmid, osmp_allow_ip, osmp_payment_transactions)
			13. Перенести настройки в принципе


			Контент
			1. Новости (news)
			2. Страницы (pages)
			3. Камменты к ТП(rate_descriptions)


				Партнерка
					banners
					click_count
					partner_payments
					partner_stat
					partners
					ref_count
					show_count
					sites


				Сообщения
					message_subjects
					messages


			I. Перенос тех сведений которые относятся к ядру
				1. Происходит выбор всех пользователей из существующей новой и старой базы. Те, которых нету в новой базе, вносятся в нее

			II. Перенос биллинга
				1. На предыдущем этапе должно быть определено какую услугу в какую переносить
				2.
		*/

		if(!$this->check()) return false;
		$this->Core->setFlag('tmplLock');
		$this->Core->setFlag('refreshed');

		define('TRID', time());
		$_SERVER['SCRIPT_FILENAME'] = _W.'index.php';

		@require($this->values['path'].'inc/settings.php');
		@require($this->values['path'].'inc/sql.php');
		@require_once(_W.'modules/billing/extensions/old/func_other.php');

		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		set_time_limit(3600);

		$oldDb = new db_mysql($sql_host, $sql_uname['admin'], encrypt_data($sql_pwd['admin']), $GLOBALS["AVA_DB_PARAMS"]);
		$oldDb->setDB($sql_name, '');
		$t = time();

		switch($sql_charset){
			case 'cp1251': $this->oldCharset = 'windows-1251'; break;
			case 'utf8': $this->oldCharset = 'utf-8'; break;
			default: $this->oldCharset = $sql_charset; break;
		}


		//Аккаунты пользователей
		$issetUsers = $this->DB->columnFetch(array('users', '*', 'login'));
		$userTblFields = $this->DB->getFields('users');
		$insUsers = array();
		$j = 0;

		foreach(($oldUsers = $oldDb->columnFetch(array('agreements', '*', 'agreement_id', "`agreement_id` > 1"))) as $i => $e){
			if(!isset($issetUsers[$e['login']])){
				$e['vars'] = $this->oldUnserialize($e['vars']);
				$insUsers[$j] = array('date' => $e['open_date'], 'login' => $e['login'], 'code' => Library::inventStr(16), 'name' => isset($e['vars']['name']) ? $e['vars']['name'] : '', 'eml' => $e['eml'], 'utc' => 10800, 'show' => 1);

				if(is_array($e['vars'])){
					foreach($e['vars'] as $i1 => $e1){
						if(isset($userTblFields[$i1])) $insUsers[$j][$i1] = $e1;
						else $insUsers[$j]['vars'][$i1] = $e1;
					}
				}

				$j ++;
			}
		}

		if($insUsers) $this->DB->Ins(array('users', $insUsers));


		//Администраторы
		$issetAdmins = $this->DB->columnFetch(array('admins', '*', 'login'));
		foreach($this->readHtpasswdFile() as $e){
			if(!isset($issetAdmins[$e])){
				$ul = $e;
				if(isset($issetUsers[$ul])) $ul = Library::getEmptyHashIndex($issetUsers, $ul, $step = 1, $start = '');
				$id = $this->Core->DB->Ins(array('users', array('date' => $t, 'login' => $ul, 'eml' => $emails['root'], 'name' => $ul, 'code' => Library::inventStr(16), 'utc' => 10800, 'show' => 1)));
				$this->Core->DB->Ins(array('admins', array('user_id' => $id, 'eml' => $emails['root'], 'date' => $t, 'login' => $e, 'show' => 1)));
			}
		}


		//Перенос биллинговой системы
		if($this->values['newBilling']){
			$bObj = $this->Core->callModule($this->values['newBilling']);


			//Настройки валют
			foreach($money_types as $i => $e){
				if(!empty($curList[$i])) $bObj->DB->Upd(array('currency', array('exchange' => $money_kurses[$i]), "`name`='$i'"));
				else $bObj->DB->Ins(array('currency', array('name' => $i, 'text' => $e, 'exchange' => $money_kurses[$i])));
			}


			//Способы оплаты
			$payList = $bObj->DB->columnFetch(array('payments', '*', 'name'));
			foreach($cash_manners as $i => $e){
				$newCm = $this->getNewCashManner($i);
				$vars = array();

				switch($newCm){
					case 'wmr': case 'wmz': case 'wme': case 'wmu': case 'wmb': case 'wmg':
						if(file_exists($this->values['path'].'inc/pay_wm_settings.php')){
							@include($this->values['path'].'inc/pay_wm_settings.php');
							$vars = array('purse' => constant(regExp::upper($newCm)), 'secretKey' => Library::crypt(encrypt_data($secret_key)), 'credit' => 0);
						}
						break;

					case 'ym':
						if(file_exists($this->values['path'].'inc/pay_ym_settings.php')){
							@include($this->values['path'].'inc/pay_ym_settings.php');
							$vars = array('shopId' => $shop_id, 'bankId' => $bankid, 'scid' => 0, 'secretKey' => Library::crypt(encrypt_data($secret_key)), 'mode' => 1);
						}
						break;

					case 'bank': case 'sber':
						$vars = array('orgName' => defined('ORG_NAME') ? ORG_NAME : '', 'orgNameShort' => defined('SHORT_NAME') ? SHORT_NAME : '', 'inn' => defined('INN') ? INN : '', 'kpp' => defined('KPP') ? KPP : '', 'ogrn' => defined('OGRN') ? OGRN : '', 'okved' => defined('OKVED') ? OKVED : '', 'city' => defined('CITY') ? CITY : '', 'address' => defined('ADDR') ? ADDR : '', 'postAddress' => defined('POST_ADDR') ? POST_ADDR : '', 'leaderName' => defined('LEADER') ? LEADER : '', 'leaderTitle' => defined('LEADER_STATUS') ? LEADER_STATUS : '', 'bank' => defined('BANK_NAME') ? BANK_NAME : '', 'bankAddress' => defined('BANK_ADDR') ? BANK_ADDR : '', 'bankAccount' => defined('BANK_ACCOUNT') ? BANK_ACCOUNT : '', 'bankCorrAccount' => defined('BANK_KORR') ? BANK_KORR : '', 'bik' => defined('BANK_BIK') ? BANK_BIK : '', 'nds' => defined('NDS') ? NDS : '');
						break;

					case 'zp':
						if(file_exists($this->values['path'].'inc/pay_zp_settings.php')){
							@include($this->values['path'].'inc/pay_zp_settings.php');
							$vars = array('purse' => $shop_id, 'secretKey' => Library::crypt(encrypt_data($merchant_key)), 'pwd' => Library::crypt(encrypt_data($sign_password)));
						}
						break;

					case 'robox':
						if(file_exists($this->values['path'].'inc/pay_rob_settings.php')){
							@include($this->values['path'].'inc/pay_rob_settings.php');
							$vars = array('mode' => 1, 'login' => Library::crypt(encrypt_data($login)), 'pwd1' => Library::crypt(encrypt_data($pass1)), 'pwd2' => Library::crypt(encrypt_data($pass2)));
						}
						break;
				}

				if(!empty($payList[$newCm])) $bObj->DB->Upd(array('payments', array('currency' => $cash_manners_money[$i], 'vars' => Library::serialize($vars)), "`name`='$newCm'"));
				else $bObj->DB->Ins(array('payments', array('name' => $i, 'text' => $e, 'currency' => $cash_manners_money[$i], 'vars' => Library::serialize($vars), 'show' => 1)));
			}


			//Переносим серванты
			$issetServers = $bObj->DB->columnFetch(array('connections', 'text', 'name'));
			$issetExtensions = $bObj->DB->columnFetch(array('service_extensions_connect', 'name', 'mod'));
			$conIns = array();
			$j = 0;

			foreach(($servers = $oldDb->columnFetch(array('servers', '*', 'server_id'))) as $e){
				if(empty($issetServers[$e['cp_name'].$e['server_id']])){
					if(!isset($issetExtensions[$e['cp_name']])) continue;
					$e['cp_login'] = encrypt_data($e['cp_login']);
					$e['cp_pwd'] = encrypt_data($e['cp_pwd']);
					$e['vars'] = array();

					switch($e['cp_name']){
						case 'cp': $e['vars']['pwd_type'] = (regExp::len($e['cp_pwd']) > 32) ? 'hash' : 'pwd'; break;
						case 'directi':
							$e['cp_login'] = explode('|', $e['cp_login']);
							$e['vars']['parent_id'] = isset($e['cp_login']['1']) ? $e['cp_login']['1'] : '';
							$e['cp_login'] = isset($e['cp_login']['0']) ? $e['cp_login']['0'] : '';
							break;
					}

					$conIns[$j] = array('extension' => $e['cp_name'], 'name' => $e['cp_name'].$e['server_id'], 'text' => '{Call:Lang:core:core:server:'.Library::serialize(array($e['server_id'], $e['cp_name'], $e['cp_host'])).'}', 'host' => $e['cp_host'], 'login_host' => $e['login_host'], 'login' => $e['cp_login'], 'pwd' => Library::Crypt($e['cp_pwd']), 'comment' => $e['comment'], 'vars' => $e['vars']);

					if(empty($issetServers[$e['cp_name'].$e['server_id'].'reseller']) && $oldDb->issetTable('service_reseller') && $oldDb->issetTable('rates_service_reseller') && !empty($available_services[$e['cp_name']])){
						$j ++;
						$conIns[$j] = $conIns[$j - 1];
						$conIns[$j]['extension'] .= 'reseller';
					}

					$j ++;
				}
			}

			if($conIns) $bObj->DB->Ins(array('connections', $conIns));


			//Переносим клиентов
			$issetUsers = $this->DB->columnFetch(array('users', '*', 'login'));
			$issetAdmins = $this->DB->columnFetch(array('admins', 'id', 'user_id'));
			$insClients = array();

			foreach($oldUsers as $e){
				if(empty($issetAdmins[$issetUsers[$e['login']]['id']])){
					$insClients[] = array('user_id' => $issetUsers[$e['login']]['id'], 'date' => $e['open_date'], 'balance' => $e['balance'], 'all_payments' => $e['all_payments']);
				}
			}

			if($insClients) $bObj->DB->Ins(array('clients', $insClients));
			$issetClients = $bObj->DB->columnFetch(array('clients', 'id', 'user_id'));


			//Переносим услуги
			$services = RegExp::charset('WINDOWS-1251', 'UTF-8', $services);
			$pkgsDscIns = array();
			$pkgsGrpsIns = array();

			$ns = array();
			$pkgsIns = array();
			$accsIns = array();

			foreach($services as $i => $e){
				if((!$ns[$i] = $this->values['service_'.$i]) || !$oldDb->issetTable($i) || !$oldDb->issetTable('rates_'.$i)) continue;
				unset($ir_matrix);


				//Создаем услугу
				if($ns[$i] == '@new'){
					$ns[$i] = $i;
					if(!$bObj->DB->cellFetch(array('services', 'id', "`name`='$i' OR `text`='$e'"))){
						$bObj->DB->CT(array('packages_'.$i, array('package_id' => ''), array('uni' => array(array('package_id')))));
						$bObj->DB->CT(array('orders_'.$i, array('service_order_id' => 'INT'), 'extras' => array('uni' => array(array('service_order_id')))));
						$bObj->DB->Ins(array('services', array('name' => $i, 'text' => $e, 'type' => $services_type[$i] == 'one_time' ? 'onetime' : 'prolonged', 'base_term' => $services_type[$i] == 'one_time' ? '' : $base_term[$i], 'test_term' => 'day', 'show' => 1)));
					}
				}


				//Создаем поля описания к услуге
				$issetPkgDsc = $bObj->DB->columnFetch(array('package_descripts', array('vars'), 'name', "`service`='{$ns[$i]}'"));
				if(file_exists($this->values['path'].'matrix/ir_matrix_'.$i.'.php')){
					$ir_matrix = array();
					if(file_exists($this->values['path'].'matrix/ir_matrix_'.$i.'.php')) include($this->values['path'].'matrix/ir_matrix_'.$i.'.php');

					foreach($ir_matrix as $i1 => $e1){
						if(!isset($issetPkgDsc[$i1])){
							$e1 = RegExp::charset('WINDOWS-1251', 'UTF-8', $e1);
							$vars = array();
							$sort = 300;
							$vars['extra']['mpkg_price'] = $e1['price'];

							if($e1['type'] == 'text'){
								$vars['extra']['mpkg_price_unlimit'] = $e1['unlim'];
								$sort = 200;
							}
							elseif($e1['type'] == 'select'){
								$sort = 100;
								$vars['additional'] = array();

								foreach($e1['price'] as $i2 => $e2){
									$vars['matrix']['additional'][$e2] = $e2;
									$vars['extra']['mpkg_price'][$e2] = $i2;
								}
							}

							if(!empty($e1['err'])) $vars['matrix']['warn'] = $e1['err'];
							if(!isset($issetPkgDsc[$i1])) $pkgsDscIns = array('name' => $i1, 'text' => $e1['text'], 'type' => $e1['type'], 'vars' => $vars, 'service' => $ns[$i], 'apkg' => 1, 'mpkg' => 1, 'pkg_list' => 1, 'use_if_no_conformity' => 1, 'use_if_no_panel' => 1, 'show' => 1, 'sort' => $sort);
							else $bObj->DB->Upd(array('package_descripts', array('vars' => Library::array_merge(Library::unserialize($issetPkgDsc[$i1]['vars']), $vars)), "`name`='$i'"));
						}
					}
				}


				//Создаем группы тарифовъ
				$grpStyles = $oldDb->columnFetch(array('rate_descriptions', 'style', 'grp', "`type`='grp_comment' AND `service`='{$ns[$i]}'"));
				$issetGroups = $bObj->DB->columnFetch(array('package_groups', 'name', 'text', "`service`='$i'"));
				if(!empty(${$i.'_groups'})){
					foreach(${$i.'_groups'} as $i1 => $e1){
						if(empty($issetGroups[$i1])) $pkgsGrpsIns[] = array('service' => $ns[$i], 'name' => $i1, 'text' => $e1, 'main' => 1, 'pkg_table_mode' => isset($grpStyles[$i1]) ? $grpStyles[$i1] : 0, 'hide_if_none' => 1, 'compact_if_alike' => 1);
					}
				}


				//Создаем пакеты
				$issetPackages = $bObj->DB->columnFetch(array('order_packages', 'name', 'text', "`service`='{$ns[$i]}'"));
				foreach($oldDb->columnFetch(array('rates_'.$i, '*')) as $e1){
					if(!isset($issetPackages[$e1['rate_id']])){
						if($e1['server_id'] && isset($issetExtensions[$servers[$e1['server_id']]['cp_name']])){
							$sData = $servers[$e1['server_id']];
							$oldSrv = $sData['cp_name'];
							if($ns[$i] == 'service_reseller') $serverId = $sData['cp_name'].$sData['server_id'].'reseller';
							else $serverId = $sData['cp_name'].$sData['server_id'];
						}
						else $serverId = $oldSrv = '';

						$pkgsIns[] = array(
							'service' => $ns[$i],
							'name' => $e1['rate_id'],
							'text' => $e1['rate_name'],
							'server_name' => $i == 'service_domain' ? regExp::lower($e1['rate_id']) : $e1['rate_id'],
							'server' => $serverId,
							'main_group' => $e1['grp'],
							'show' => 1,
							'price' => $e1['rate_price'],
							'price2' => $e1['prolong_price'],
							'prolong_price' => $e1['prolong_price'],
							'install_price' => $e1['create_price'],
							'terms' => empty(${$i.'_term_list'}[$e1['rate_id']]) ? '' : implode(',', ${$i.'_term_list'}[$e1['rate_id']]),
							'prolong_terms' => empty(${$i.'_term_list'}[$e1['rate_id']]) ? '' : implode(',', ${$i.'_term_list'}[$e1['rate_id']]),
							'test' => $e1['test_term'],
							'vars' => Library::serialize(
								array(
									'params' => $this->getNewAvaPkgParams($bObj, $this->oldUnserialize($e1['rate_info']), $i, $ns[$i], $oldSrv, $serverId),
									'rights' => array(
										'new' => $e1['right_create'],
										'prolong' => $e1['right_prolong'],
										'modify' => $e1['right_other_services'],
										'changeGrp' => $e1['right_change'],
										'changeSrv' => $e1['right_change'],
										'changeDn' => $e1['right_change'],
										'changeUp' => $e1['right_change'],
										'pause' => 1,
										'del' => $e1['right_del']
									),
									'notify_rights' => array('notify_settings_type' => 'useMain'),
									'extraDescript' => array()
								)
							)
						);
					}
				}


				//Переносим аккаунты
				$issetAccs = $bObj->DB->columnFetch(array('order_services', 'id', 'ident', "`service`='{$ns[$i]}'"));
				foreach($oldDb->columnFetch(array($i, '*')) as $e1){
					$ident = $i == 'service_domain' ? $e1['rate'] : $e1['cp_login'];

					if(!empty($issetUsers[$oldUsers[$e1['agreement_id']]['login']]) && !empty($issetClients[$issetUsers[$oldUsers[$e1['agreement_id']]['login']]['id']]) && empty($issetAccs[$ident])){
						$e1['rate_info'] = $this->oldUnserialize($e1['rate_info']);

						if($e1['server_id']){
							$sData = $servers[$e1['server_id']];
							$oldSrv = $sData['cp_name'];
							if($ns[$i] == 'service_reseller') $serverId = $sData['cp_name'].$sData['server_id'].'reseller';
							else $serverId = $sData['cp_name'].$sData['server_id'];
						}
						else $serverId = $oldSrv = '';

						$accsIns[] = array(
							'service' => $ns[$i],
							'client_id' => $issetClients[$issetUsers[$oldUsers[$e1['agreement_id']]['login']]['id']],
							'ident' => $ident,
							'package' => $i == 'service_domain' ? regExp::replace("|^[^\.]+\.|", "", $e1['rate'], true) : $e1['rate'],
							'server' => $serverId,
							'date' => $e1['created'],
							'created' => $e1['created'],
							'last_paid' => $e1['last_paid'],
							'paid_to' => $t + ($e1['free_period'] * 86400),
							'price' => $e1['sum4period4rate'],
							'modify_price' => $e1['sum4period4os'],
							'all_payments' => $e1['sum'],
							'step' => $e1['deleted'] ? -1 : ($e1['suspend'] ? 0 : 1),
							'auto_prolong' => '1',
							'auto_prolong_fract' => '1',
							'vars' => $vars
						);
					}
				}
			}


			//Вносим пакеты и пр. что насобирали
			if($pkgsDscIns) $bObj->DB->Ins(array('package_descripts', $pkgsDscIns));
			if($pkgsGrpsIns) $bObj->DB->Ins(array('package_groups', $pkgsGrpsIns));

			if($pkgsIns){
				$bObj->DB->Ins(array('order_packages', $pkgsIns));

				foreach($ns as $i => $e){
					$pkgsIns2 = array();
					foreach($bObj->DB->columnFetch(array('order_packages', 'id', '', "`service`='$i'")) as $i1 => $e1) $pkgsIns2[] = array('package_id' => $e1);
					if($pkgsIns2) $bObj->DB->Ins(array('packages_'.$e, $pkgsIns2));
				}
			}

			if($accsIns) $bObj->DB->Ins(array('order_services', $accsIns));
			foreach($ns as $i => $e){
				$accsIns2 = array();
				foreach($bObj->DB->columnFetch(array('order_services', 'id', '', "`service`='$e'")) as $i1 => $e1) $accsIns2[] = array('service_order_id' => $e1);
				if($accsIns2) $bObj->DB->Ins(array('orders_'.$e, $accsIns2));
			}


			//Переносим счета
			$issetOrders = $bObj->DB->columnFetch(array('orders', 'id', 'date'));
			$ordersIns = array();

			foreach(($oldOrders = $oldDb->columnFetch(array('orders', '*', "", "`agreement_id`>1"))) as $i => $e){
				$oldOrders[$i]['other'] = $e['other'] = $this->oldUnserialize($e['other']);
				$cid = isset($issetClients[$issetUsers[$oldUsers[$e['agreement_id']]['login']]['id']]) ? $issetClients[$issetUsers[$oldUsers[$e['agreement_id']]['login']]['id']] : 0;

				if(!isset($issetOrders[$e['create_date']]) && ($e['agreement_id'] > 1) && $cid){
					$ordersIns[] = array(
						'client_id' => $cid,
						'date' => $e['create_date'],
						'ordered' => $e['create_date'],
						'step' => $e['enrolled'] ? 6 : 4,
						'sum' => isset($e['other']['price_sum']) ? $e['other']['price_sum'] : $e['sum'],
						'discount' => isset($e['other']['price_discount_sum']) ? $e['other']['price_discount_sum'] : 0,
						'total' => isset($e['other']['price_sum4pay']) ? $e['other']['price_sum4pay'] : $e['sum']
					);
				}
			}

			if($ordersIns) $bObj->DB->Ins(array('orders', $ordersIns));
			$transactionsIns = array();
			$orderEntriesIns = array();

			$issetOrders = $bObj->DB->columnFetch(array('orders', 'id', 'date'));
			$issetTransactions = $bObj->DB->columnFetch(array('payment_transactions', 'id', 'date'));
			$issetOrderEntries = $bObj->DB->columnFetch(array('orders', 'id', 'date'));


			//Переносим транзакции и записи счетов
			foreach($oldOrders as $e){
				$cid = isset($issetClients[$issetUsers[$oldUsers[$e['agreement_id']]['login']]['id']]) ? $issetClients[$issetUsers[$oldUsers[$e['agreement_id']]['login']]['id']] : 0;

				if(($e['agreement_id'] > 1) && $cid){
					if(!isset($issetTransactions[$e['create_date']])){
						$transactionsIns[] = array(
							'client_id' => $cid,
							'object_type' => 'orders',
							'object_id' => $issetOrders[$e['create_date']],
							'status' => $e['enrolled'] ? 2 : 0,
							'date' => $e['create_date'],
							'pay' => $e['paid_date'],
							'sum' => $e['sum'],
							'payment' => isset($e['vars']['cash_manner']) ? $this->getNewCashManner($e['vars']['cash_manner']) : ''
						);
					}

					if(!isset($issetOrderEntries[$e['create_date']])){
						$e['vars'] = $this->oldUnserialize($e['vars']);
						$e['other'] = $this->oldUnserialize($e['other']);

						die('Смотри order_entries');
					}
				}
			}

			if($transactionsIns) $bObj->DB->Ins(array('payment_transactions', $transactionsIns));
			if($orderEntriesIns) $bObj->DB->Ins(array('order_entries', $orderEntriesIns));


			//Сведения о движении денежных средств
			$issetPays = $bObj->DB->columnFetch(array('pays', 'id', 'date'));
			$paysIns = array();

			foreach($oldDb->columnFetch(array('payments', '*')) as $e){
				if(isset($oldUsers[$e['agreement_id']])){
					$cid = isset($issetClients[$issetUsers[$oldUsers[$e['agreement_id']]['login']]['id']]) ? $issetClients[$issetUsers[$oldUsers[$e['agreement_id']]['login']]['id']] : 0;
					if(($e['agreement_id'] > 1) && $cid && !isset($issetPays[$e['date']])){
						$paysIns = array('client_id' => $cid, 'date' => $e['date'], 'real_date' => $e['date'], 'sum' => $e['sum_main'], 'foundation' => $e['foundation'], 'foundation_type' => $e['sum_main'] >= 0 ? 'balance' : 'service');
					}
				}
			}
		}


		//Переносим партнерку
		if($this->values['newPartner']){
			$pObj = $this->Core->callModule($this->values['newPartner']);


			//Создаем партнеров
			$issetPartners = $pObj->DB->columnFetch(array('partners', array('user_id', 'id'), 'login'));
			$issetUsers = $this->DB->columnFetch(array('users', '*', 'login'));
			$partnersIns = array();
			$partnersPaysIns = array();

			foreach(($partners = $oldDb->columnFetch(array('partners', '*', "partner_id", "", "`partner_id`"))) as $i => $e){
				if(!isset($issetPartners[$e['login']])){
					$partnersIns[$e['partner_id']] = array('login' => $e['login'], 'refered_by' => $e['refer_id'] ? $partners[$e['refer_id']]['login'] : '', 'date' => $e['open_date'], 'balance' => $e['balance'], 'all_pays' => $e['all_payments'], 'status' => $e['allow']);
					$partnersPaysIns[$e['partner_id']] = array('partner_id' => $e['login'], 'type' => 'admin', 'date' => $t, 'sum' => $e['balance']);

					if($e['agreement_id'] < 2){
						if(!empty($issetUsers[$e['login']])) $e['login'] = $e['login'].'_'.$e['partner_id'];
						$partnersIns[$e['partner_id']]['user_id'] = $this->DB->Ins(array('users', array('date' => $e['open_date'], 'login' => $e['login'], 'pwd' => '', 'code' => Library::inventStr(16), 'name' => $e['login'], 'eml' => $e['eml'], 'utc' => 10800, 'show' => 1)));
					}
					elseif(isset($oldUsers[$e['agreement_id']])){
						$partnersIns[$e['partner_id']]['user_id'] = $issetUsers[$oldUsers[$e['agreement_id']]['login']]['id'];
					}
					else unset($partnersIns[$e['partner_id']], $partnersPaysIns[$e['partner_id']]);
				}
			}

			if($partnersIns) $pObj->DB->Ins(array('partners', $partnersIns));
			if($partnersPaysIns) $pObj->DB->Ins(array('pays', $partnersPaysIns));


			//Вносим сведения о юзерах приведенных по рекомендации партнера
			$issetUsers = $this->DB->columnFetch(array('users', '*', 'login'));
			$usersByPartner = $oldDb->columnFetch(array('agreements', 'refered', 'agreement_id', "`refered`>0"));
			$ubpIns = array();

			foreach($usersByPartner as $i => $e){
				$ubpIns[] = array('user_id' => $issetUsers[$oldUsers[$i]['login']]['id'], 'partner_id' => $partners[$e]['login']);
			}
			if($ubpIns) $pObj->DB->Ins(array('partner_users', $ubpIns));


			//Партнерские сайты
			$sitesIns = array();
			foreach($oldDb->columnFetch(array('sites', '*', 'site_id', "", "`site_id`")) as $i => $e){
				$sitesIns[] = array('name' => $e['name'], 'url' => $e['url'], 'partner_id' => $partners[$e['partner_id']]['login'], 'date' => $e['req_date'], 'status' => $e['allow']);
			}
			if($sitesIns) $pObj->DB->Ins(array('sites', $sitesIns));


			//Партнерские банеры
			$bannersIns = array();
			$issetBanners = $pObj->DB->columnFetch(array('banners', 'text', 'name'));

			foreach($oldDb->columnFetch(array('banners', '*', "banner_id", "", "`banner_id`")) as $i => $e){
				list($text, $code, $content, $type, $cgType) = $this->getNewBannerParams($pObj, $e);
				if(empty($issetBanners[$e['name']])){
					$bannersIns[] = array('type' => $type, 'name' => $e['name'], 'text' => $text, 'link' => $e['link'], 'content' => $content, 'code' => $code, 'code_gen_type' => $cgType, 'show' => 1);
				}
			}
			if($bannersIns) $pObj->DB->Ins(array('banners', $bannersIns));


			//Запросы оплаты партнера
			$paysIns = array();
			$issetPPays = $pObj->DB->columnFetch(array('pay_orders', 'id', 'date'));

			foreach($oldDb->columnFetch(array('partner_payments', '*')) as $e){
				if(empty($issetPPays[$e['query_date']])){
					$paysIns[] = array('partner_id' => $partners[$e['partner_id']]['login'], 'date' => $e['query_date'], 'payed' => $e['pay_date'], 'sum' => $e['sum'], 'payment' => $e['cash_manner'], 'status' => $e['payd'], 'init' => 'u');
				}
			}
			if($paysIns) $pObj->DB->Ins(array('pay_orders', $paysIns));
		}


		//Переносим модуль поддержки
		if($this->values['newSupport']){
			$tObj = $this->Core->callModule($this->values['newSupport']);


			//Темы сообщений (департаменты)
			$depIns = array();
			$issetSubjs = array();

			foreach(($subjs = $oldDb->columnFetch(array('message_subjects', '*'))) as $e){
				$issetSubjs[$e['id']] = regExp::replace("|\W|", "", Library::cyr2translit($e['main_subj']));
				if($e['id'] > 3) $depIns[] = array('text' => $e['main_subj'], 'name' => $issetSubjs[$e['id']], 'show' => 1);
			}
			if($depIns) $tObj->DB->Ins(array('departments', $depIns));


			//Тикеты (ветки сообщений)
			$issetUsers = $this->DB->columnFetch(array('users', '*', 'login'));
			$issetTickets = $tObj->DB->columnFetch(array('tickets', 'id', 'date'));
			$supportId = $tObj->DB->cellFetch(array('supports', 'id'));
			$ticketIns = array();

			foreach(($tickets = $oldDb->columnFetch(array('messages', '*', 'msg_id', '!`parent_id`', '`add_date`'))) as $e){
				if(empty($issetTickets[$e['add_date']])){
					$uid = ($e['agreement_id'] > 1) ? $issetUsers[$oldUsers[$e['agreement_id']]['login']] : 0;

					if($e['main_subj'] == 1 || $e['main_subj'] == 2) $ms = 'support';
					elseif($e['main_subj'] == 3) $ms = 'pay';
					elseif(isset($issetSubjs[$e['main_subj']])) $ms = $issetSubjs[$e['main_subj']];
					else $ms = 'support';

					$ticketIns[] = array('user_id' => $uid, 'eml' => $e['eml'], 'support_id' => $supportId, 'department' => $ms, 'date' => $e['add_date'], 'name' => $e['subj'], 'status' => $this->getMsgStatus($e), 'status_by' => 'user', 'code' => $e['uniq_id'], 'show' => '1');
				}
			}

			if($ticketIns) $tObj->DB->Ins(array('tickets', $ticketIns));


			//Сообщения
			$issetMessages = $tObj->DB->columnFetch(array('messages', 'id', 'date'));
			$issetTickets = $tObj->DB->columnFetch(array('tickets', 'id', 'date'));
			$msgIns = array();

			foreach(($messages = $oldDb->columnFetch(array('messages', '*', 'msg_id', '', '`add_date`'))) as $e){
				if(empty($issetMessages[$e['add_date']])){
					$tId = $e['parent_id'] ? (isset($tickets[$e['parent_id']]) ? $issetTickets[$tickets[$e['parent_id']]['add_date']] : 0) : $issetTickets[$e['add_date']];

					if($tId){
						$uid = ($e['agreement_id'] > 1) ? $issetUsers[$oldUsers[$e['agreement_id']]['login']] : 0;
						$msgIns[] = array('ticket_id' => $tId, 'date' => $e['add_date'], 'author' => $uid, 'author_type' => 'user', 'text' => $e['text']);
						if($e['answer']) $msgIns[] = array('ticket_id' => $tId, 'date' => $e['add_date'], 'author' => $supportId, 'author_type' => 'admin', 'text' => $e['answer']);
					}
				}
			}

			if($msgIns) $tObj->DB->Ins(array('messages', $msgIns));
		}


		//Контент
		if($this->values['newCms']){
			$cmsObj = $this->Core->callModule($this->values['newCms']);

			//Новости
			$newsIns = array();
			foreach($oldDb->columnFetch(array('news', '*', '', '', '`date`')) as $e){
				$newsIns[] = array('name' => 'news'.$e['new_id'], 'text' => $e['head'], 'sort' => $j, 'show' => 1, 'link' => $e['link'], 'notice' => $e['body'], 'date' => $e['date']);
			}
			if($newsIns) $cmsObj->DB->Ins(array('news', $newsIns));


			//Странички
			$issetPages = $cmsObj->DB->columnFetch(array('pages', 'id', 'url'));
			foreach($oldDb->columnFetch(array('pages', '*', '', '', '`level`, `order`')) as $e){
				if($e['name'] && empty($issetPages[$e['identifier']])){
					if(!$e['link'] || $e['link'] == '#') $e['link'] = '';
					elseif(regExp::match('page=content', $e['link']) && regExp::match('ident='.$e['identifier'], $e['link'])){
						$e['link'] = 'index.php?mod='.$this->values['newCms'].'&func=page&id='.$e['identifier'];
					}
					elseif(regExp::Match(_D, $e['link'])) continue;

					if($e['body']){
						$correlate['pages'][$e['identifier']] = $cmsObj->DB->Ins(
							array(
								'pages',
								array('parent' => $e['mother_partition'], 'date' => $t, 'name' => $e['name'], 'url' => $e['identifier'], 'body' => $e['body'], 'vars' => array('title' => $e['title'], 'keywords' => $e['keywords'], 'description' => $e['description']), 'show' => 3)
							)
						);
					}

					$cmsObj->DB->Ins(
						array(
							'menu1',
							array('parent_id' => $cmsObj->DB->cellFetch(array('menu1', 'id', "`name`='{$e['mother_partition']}'")), 'name' => $e['identifier'], 'text' => $e['name'], 'show' => 1, 'link' => $e['link'])
						)
					);
				}
			}

			if(!empty($bObj)){
				foreach($bObj->DB->columnFetch(array('services', 'text', 'name')) as $i => $e){
					if($bObj->DB->cellFetch(array('order_packages', 'id', "`service`='$i'"))){
						$m = $bObj->getMod();

						$this->insertLink(
							array(
								'text' => $e,
								'name' => 'packages_'.$i,
								'mod' => $m,
								'url' => 'index.php?mod='.$m.'&func=packages&service='.$i,
								'usedCmsLevel' => array('mainmenu', 'menu1')
							),
							'packages'
						);

						foreach($bObj->DB->columnFetch(array('package_groups', 'text', 'name', "`service`='$i'")) as $i1 => $e1){
							if($bObj->DB->cellFetch(array('order_packages', 'id', "`service`='$i' AND `main_group`='$i1'"))){
								$this->insertLink(
									array(
										'text' => $e1,
										'name' => 'packages_'.$i.'_'.$i1,
										'mod' => $m,
										'url' => 'index.php?mod='.$m.'&func=packages&service='.$i.'&grp='.$i1,
										'usedCmsLevel' => array('mainmenu', 'menu1')
									),
									'packages_'.$i
								);
							}
						}
					}
				}
			}
		}

		$this->Core->rmFlag('tmplLock');
		$this->Core->rmHeader('Location');
		$this->Core->rmFlag('refreshed');

		return true;
	}

	private function oldUnserialize($str){
		return RegExp::charset($this->oldCharset, $GLOBALS["AVA_DB_PARAMS"]['outCharset'], unserialize(RegExp::charset($GLOBALS["AVA_DB_PARAMS"]['outCharset'], $this->oldCharset, $str)));
	}

	private function readHtpasswdFile(){
		$return = array();
		foreach(explode("\n", Files::read($this->values['path'].'admin/.htpasswd')) as $e){
			$e = explode(':', $e);
			$e['0'] = trim($e['0']);
			if($e['0']) $return[] = $e['0'];
		}

		return $return;
	}

	private function getMsgStatus($params){
		if(!$params['answer_date'] && !$params['looked']) return 'noansw';
		elseif(!$params['answer_date'] && $params['looked']) return 'wait';
		else return 'answ';
	}

	private function getNewBannerParams(gen_partner $pObj, $params){
		$cgType = 'auto';

		switch($params['type']){
			case 'form':
				$cgType = 'manual';
				$code = $params['body'];

			case 'link':
				$type = 'text';
				break;

			case 'banner':
				$type = 'image';
				break;

			default:
				$type = 'text';
				$cgType = 'manual';
				break;
		}

		if($type == 'image') $content = $this->moveOldBanner($params['body']);
		else $content = $params['body'];

		if($cgType == 'auto'){
			$code = $this->Core->readAndReplace(
				$this->Core->getModuleTemplatePath($pObj->getMod()).'bannercode.tmpl',
				$pObj,
				array(
					'content' => $content,
					'code_gen_type' => $cgType,
					'type' => $type,
					'text' => $type == 'text' ? $params['body'] : $params['name'],
					'link' => $params['link']
				)
			);
		}

		return array($params['body'], $code, $content, $type, $cgType);
	}

	private function moveOldBanner($body){
		if(!file_exists($this->values['path'].'images/banners/'.$body)) return false;
		$return = $this->Core->getParam('partnerBannerFolder', $this->values['newPartner']).$body;
		$this->Core->ftpCopy($this->values['path'].'images/banners/'.$body, _W.$return);

		$img = new Image;
		$img->createImage(_W.$return);
		if($img->resizeImageWhichMore($this->Core->getParam('thumbWh'), $this->Core->getParam('thumbHt'))){
			$img->flushImage(_W.$this->Core->getParam('partnerBannerFolder', $this->values['newPartner']).'.thumbs/'.$body, $this->Core->getParam('thumbQuality'));
		}

		return $return;
	}

	private function getNewCashManner($oldCm){
		/*
			Возвращает имя нового способа оплаты
		*/

		switch($oldCm){
			case 'sb': return 'sber';
			case 'b': return 'bank';
			case 'rupay_pk': case 'rupay_post': case 'rupay': return 'rbc';
			case 'rob': return 'robox';
			default: return $oldCm;
		}
	}

	private function getNewAvaPkgParams($bObj, $oldParams, $oldService, $service, $oldCp, $cp){
		/*
			Возвращает параметры нового ТП основываясь на старом ТП
		*/

		if(!is_array($oldParams)) return array();

		if(empty($this->serverParamsConf[$service])){
			foreach($bObj->DB->columnFetch(array('package_descripts', 'vars', 'name', "`service`='{$service}'")) as $i => $e){
				$e = Library::unserialize($e);
				foreach($e['extra'] as $i1 => $e1){
					if(regExp::Match("cp_conformity_", $i1)) $this->serverParamsConf[$service][regExp::replace('cp_conformity_', '', $i1)][$e1] = $i;
				}
			}

			@include(_W.'modules/billing/extensions/old/multiserver.php');
			@include($this->values['path'].'inc/cp_conformity.php');

			foreach($cp_conformity as $i => $e){
				if(is_array($e)){
					foreach($e as $i1 => $e1){
						foreach($e1 as $i2 => $e2){
							$this->serverParamsConfOld[$service][$i][$i1][$e2['index']] = $i2;
						}
					}
				}
			}
		}

		$return = array();
		foreach($oldParams as $i => $e){
			if(!empty($this->serverParamsConfOld[$service][$oldCp][$oldService][$i]) && !empty($this->serverParamsConf[$service][$cp][$this->serverParamsConfOld[$service][$oldCp][$oldService][$i]])){
				$return[$this->serverParamsConf[$service][$cp][$this->serverParamsConfOld[$service][$oldCp][$oldService][$i]]] = $e;
			}
		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																			SQL-запросы к базе данных

	*********************************************************************************************************************************************************************/

	protected function func_db(){
		/*
			Произвольные запросы к БД SQL
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'dbRequest',
						'dbRequest',
						array(
							'caption' => '{Call:Lang:core:core:proizvolnyjz}',
							'extras' => 'target="_blank"'
						)
					),
					'db_request',
					array(
						'db' => Library::array_merge(array('' => 'По умолчанию'), $this->Core->getDatabases())
					)
				),
				array(),
				array(),
				'big'
			)
		);
	}

	protected function func_dbRequest(){
		/*
			Выводит на страницу содержимое произвольного MySQL-запроса
		*/

		$sql = trim($this->values['sql']);
		if(!$sql){
			$this->back('db', '{Call:Lang:core:core:pustojsqlzap}');
			return false;
		}

		$textSql = regExp::html($sql);
		$this->Core->setTempl('empty');
		$this->setMeta($textSql);

		$DB = $this->Core->getDB($this->values['db']);
		$req = $DB->Req($sql);
		$strip = empty($this->values['strip']) ? 0 : $this->values['strip'];

		if(regExp::Match("/^(SELECT|SHOW|DESCRIBE)/iUs", $sql, true)){
			$arr = array();
			while($r = $req->Fetch()){
				$arr[] = array('list' => $r, 'strip' => $strip);
				if(count($arr) == 1) $arr[0]['first'] = true;
			}

			if(!count($arr)){
				$this->setContent('{Call:Lang:core:core:povashemuzap}');
			}
			else{
				$this->setContent(
					$this->getListText(
						$this->newList(
							'db_request',
							array(
								'arr' => $arr
							),
							array(
								'caption' => '{Call:Lang:core:core:zapros:'.Library::serialize(array($textSql)).'}'
							),
							$this->Core->getModuleTemplatePath('core').'db_requests.tmpl'
						)
					)
				);
			}
		}
		else{
			$this->setContent($req->getInfo());
		}
	}


	/********************************************************************************************************************************************************************

																		Загрузка файлов

	*********************************************************************************************************************************************************************/

	protected function func_folders(){
		/*
			Управление галереями загрузки файлов
		*/

		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'new'){
				if(!regExp::match("|/$|", $this->values['path'], true)) $this->values['path'] .= '/';
				if(!file_exists(_W.$this->values['path']) && !$this->Core->ftpMk(_W.$this->values['path'])) $this->setError('path', '{Call:Lang:core:core:neudalossozd:'.Library::serialize(array($this->values['path'])).'}');
				elseif(!is_dir(_W.$this->values['path'])) $this->setError('path', '{Call:Lang:core:core:neiavliaetsi2:'.Library::serialize(array($this->values['path'])).'}');

				if(!file_exists(_W.$this->values['path'].'/.thumbs/')) $this->Core->ftpMk(_W.$this->values['path'].'/.thumbs/');
				if(!empty($this->values['standarts'])){
					foreach($this->values['standarts'] as $i => $e){
						if(!file_exists(_W.$this->values['path'].'/'.$i)) $this->Core->ftpMk(_W.$this->values['path'].'/'.$i);
					}
				}
			}
		}

		$imageStandarts = $this->Core->getImageStandarts();
		$modules = $this->Core->getModules();

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitpapku}',
				'formData' => array(
					'imageStandarts' => $imageStandarts,
					'modules' => $modules
				),
				'modifyData' => array('extract' => array('standarts', 'modules')),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'name' => '{Call:Lang:core:core:nazvanie}',
							'path' => '{Call:Lang:core:core:put}',
							'main_standart' => '{Call:Lang:core:core:osnovnojstan}',
							'standarts' => '{Call:Lang:core:core:dopolnitelny6}',
							'modules' => '{Call:Lang:core:core:modul}',
							'show' => ''
						),
						'orderFields' => array(
							'name' => '{Call:Lang:core:core:nazvanie}',
							'path' => '{Call:Lang:core:core:put}'
						),
						'searchMatrix' => array(
							'main_standart' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), $imageStandarts)
							),
							'standarts' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), Library::concatPrefixArrayKey($imageStandarts, ',', ','))
							),
							'modules' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), $modules)
							),
						),
						'isBe' => array('files' => true)
					),
					'actions' => array(
						'name' => $this->func.'&type_action=modify'
					)
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:vsedostupnye}'
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																Стандарты обработки изображений

	*********************************************************************************************************************************************************************/

	protected function func_image_standarts(){
		/*
			Стандарты обработки изображений
		*/

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitstand}',
				'formData' => array(
					'watermarks' => $this->Core->getWatermarks()
				),
				'modifyData' => array('extract' => array('watermarks')),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:core:core:nazvanie}',
							'name' => '{Call:Lang:core:core:identifikato}',
							'quality' => '{Call:Lang:core:core:kachestvoizo1}',
							'width' => '{Call:Lang:core:core:shirina}',
							'height' => '{Call:Lang:core:core:vysota}',
							'rotate' => 'Угол поворота',
							'resize_style' => '{Call:Lang:core:core:prikonflikte}'
						),
						'orderFields' => array(
							'quality' => '{Call:Lang:core:core:kachestvuizo}',
							'width' => '{Call:Lang:core:core:shirine}',
							'height' => '{Call:Lang:core:core:vysote}',
							'text' => '{Call:Lang:core:core:nazvaniiu}',
							'name' => '{Call:Lang:core:core:identifikato5}',
						),
						'searchMatrix' => array(
							'quality' => array('type' => 'gap'),
							'width' => array('type' => 'gap'),
							'height' => array('type' => 'gap'),
							'rotate' => array('type' => 'gap'),
							'resize_style' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:core:core:vse}',
									'0' => '{Call:Lang:core:core:umenshatprop}',
									'1' => '{Call:Lang:core:core:vyrezatlevyj}',
									'2' => '{Call:Lang:core:core:vyrezatiztse}',
									'3' => '{Call:Lang:core:core:vyrezatlevyj1}',
									'4' => '{Call:Lang:core:core:vyrezatverkh}',
									'5' => '{Call:Lang:core:core:vyrezattsent}',
									'6' => '{Call:Lang:core:core:vyrezatnizts}',
									'7' => '{Call:Lang:core:core:vyrezatpravy}',
									'8' => '{Call:Lang:core:core:vyrezatiztse1}',
									'9' => '{Call:Lang:core:core:vyrezatpravy1}',
								)
							)
						),
						'isBe' => array('' => 1)
					),
					'actions' => array(
						'text' => $this->func.'&type_action=modify'
					)
				),
				'formTemplName' => 'big',
				'listTemplName' => 'big',
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:standartypre}'
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																				Шрифты

	*********************************************************************************************************************************************************************/

	protected function func_fonts(){
		/*
			Стандарты обработки изображений
		*/

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitshrif}',
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'name' => '{Call:Lang:core:core:nazvanie}',
							'file' => '{Call:Lang:core:core:imiafajla}'
						),
						'orderFields' => array(
							'name' => '{Call:Lang:core:core:nazvaniiu}',
							'file' => '{Call:Lang:core:core:imenifajla}'
						)
					),
					'action' => 'fontActions'
				),
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokustano}'
				)
			)
		);
	}

	protected function func_fontActions(){
		/*
			Действия над шрифтами
		*/

		if(empty($this->values['entry'])){
			$this->back($back);
			return false;
		}

		$folder = $this->Core->getParam('fontsFolder');
		$folder = regExp::match("|/$|", $folder, true) ? $folder : $folder.'/';

		foreach($this->DB->columnFetch(array('fonts', 'file', 'id', '('.$this->getEntriesWhere().')')) as $e){
			if(!$this->Core->ftpRm($folder.$e)) $this->setError('', '{Call:Lang:core:core:neudalosudal:'.Library::serialize(array($e)).'}');
		}

		return $this->typeActions('fonts', 'fonts');
	}


	/********************************************************************************************************************************************************************

																		Водяные знаки

	*********************************************************************************************************************************************************************/

	protected function func_watermarks(){
		/*
			Водяные знаки
		*/

		$fonts = $this->Core->getFontsByFile();

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:dobavitvodia}',
				'formData' => array(
					'fonts' => $fonts
				),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:core:core:nazvanie}',
							'name' => '{Call:Lang:core:core:identifikato}',
							'content' => '{Call:Lang:core:core:tekst}',
							'type' => '{Call:Lang:core:core:tip}',
							'hpos' => '{Call:Lang:core:core:polozheniepo}',
							'hcorner' => '{Call:Lang:core:core:cchitatot}',
							'vpos' => '{Call:Lang:core:core:polozheniepo1}',
							'vcorner' => '{Call:Lang:core:core:cchitatot}',
							'transparency' => '{Call:Lang:core:core:prozrachnost}',
							'font' => '{Call:Lang:core:core:shrift}',
							'color' => '{Call:Lang:core:core:tsvet}',
							'corner' => '{Call:Lang:core:core:ugolpovorota}',
							'show' => ''
						),
						'searchMatrix' => array(
							'type' => array(
								'type' => 'select',
								'additional' => array('' => '{Call:Lang:core:core:vse}', 'image' => '{Call:Lang:core:core:izobrazhenii1}', 'text' => '{Call:Lang:core:core:tekst}')
							),
							'hpos' => array('type' => 'gap'),
							'vpos' => array('type' => 'gap'),
							'hcorner' => array('type' => 'select', 'additional' => array('' => '{Call:Lang:core:core:liubogougla}', 'l' => '{Call:Lang:core:core:levogougla}', 'r' => '{Call:Lang:core:core:pravogougla}')),
							'vcorner' => array('type' => 'select', 'additional' => array('' => '{Call:Lang:core:core:liubogougla}', 't' => '{Call:Lang:core:core:verkhnegougl}', 'b' => '{Call:Lang:core:core:nizhnegougla}')),
							'transparency' => array('type' => 'gap'),
							'corner' => array('type' => 'gap'),
							'font' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), $fonts)
							)
						),
						'isBe' => array('hcorner' => 1, 'vcorner' => 1, 'font' => 1, 'type' => 1),
						'orderFields' => array(
							'hpos' => '{Call:Lang:core:core:polozheniiup}',
							'vpos' => '{Call:Lang:core:core:polozheniiup1}',
							'transparency' => '{Call:Lang:core:core:prozrachnost2}',
							'color' => '{Call:Lang:core:core:tsvetu}',
							'corner' => '{Call:Lang:core:core:uglupovorota}',
							'text' => '{Call:Lang:core:core:nazvaniiu}',
							'name' => '{Call:Lang:core:core:identifikato5}',
							'content' => '{Call:Lang:core:core:tekstu}',
						)
					),
					'actions' => array(
						'text' => $this->func.'&type_action=modify'
					)
				),
				'formTemplName' => 'big',
				'listTemplName' => 'big',
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokustano1}'
				)
			)
		);
	}


	/********************************************************************************************************************************************************************

																				CAPTCHA

	*********************************************************************************************************************************************************************/

	protected function func_captcha_standarts(){
		/*
			Стандарты использования CAPTCHA
		*/

		$backgrounds = $this->Core->getCaptchaBackgrounds();
		$fonts = $this->Core->getFontsByFile();
		$bgList = array();

		foreach($backgrounds as $i => $e){
			$bgList[$i] = '<img src="'._D.$this->Core->getParam('captchaFolder').'.thumbs/'.$e.'" class="thumb2" /> '.$e;
		}

		if(!empty($this->values['type_action'])){
			if($this->values['type_action'] == 'new') $this->isUniq('captcha_standarts', array('name' => '{Call:Lang:core:core:takojidentif}', 'text' => '{Call:Lang:core:core:takoenazvani}'), isset($this->values['modify']) ? $this->values['modify'] : false);
		}

		$this->typicalMain(
			array(
				'caption' => '{Call:Lang:core:core:novyjstandar}',
				'formData' => array(
					'backgrounds' => $bgList,
					'fonts' => $fonts
				),
				'modifyData' => array('extract' => array('backgrounds', 'fonts', 'math_actions')),
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'text' => '{Call:Lang:core:core:nazvanie}',
							'name' => '{Call:Lang:core:core:identifikato}',
							'captcha_type' => '{Call:Lang:core:core:tip}',
							'fonts' => '{Call:Lang:core:core:shrifty}',
							'backgrounds' => '{Call:Lang:core:core:fon}',
							'show' => '',
							'len' => '{Call:Lang:core:core:minimumsimvo}',
							'len_to' => '{Call:Lang:core:core:maksimumsimv}',
							'symbols' => '{Call:Lang:core:core:simvoly}',
							'direction' => '{Call:Lang:core:core:napravlenie}',
							'math_nums' => '{Call:Lang:core:core:chislaminimu}',
							'math_nums_to' => '{Call:Lang:core:core:chislamaksim}',
							'math_actions' => 'Допустимые математические действия',
							'math_len' => '{Call:Lang:core:core:minimumopera}',
							'math_len_to' => '{Call:Lang:core:core:maksimumoper}',
							'start_position' => '{Call:Lang:core:core:pozitsiiaper2}',
							'start_position_to' => '{Call:Lang:core:core:pozitsiiaper3}',
							'start_position_vertical' => '{Call:Lang:core:core:pozitsiiaper4}',
							'start_position_vertical_to' => '{Call:Lang:core:core:pozitsiiaper5}',
							'font_size' => '{Call:Lang:core:core:razmershrift1}',
							'font_size_to' => '{Call:Lang:core:core:razmershrift2}',
							'font_blur' => '{Call:Lang:core:core:razmytostmin}',
							'font_blur_to' => '{Call:Lang:core:core:razmytostmax}',
							'angle' => '{Call:Lang:core:core:ugolnaklonam}',
							'angle_to' => '{Call:Lang:core:core:ugolnaklonam1}',
							'letter_offset' => '{Call:Lang:core:core:smeshchenies2}',
							'letter_offset_to' => '{Call:Lang:core:core:smeshchenies3}',
							'letter_vertical_offset' => '{Call:Lang:core:core:smeshchenies4}',
							'letter_vertical_offset_to' => '{Call:Lang:core:core:smeshchenies5}',
							'color' => '{Call:Lang:core:core:tsvetmin}',
							'color_to' => '{Call:Lang:core:core:tsvetmax}',
							'transparent' => '{Call:Lang:core:core:prozrachnost3}',
							'transparent_to' => '{Call:Lang:core:core:prozrachnost4}',
						),
						'searchMatrix' => array(
							'captcha_type' => array(
								'type' => 'select',
								'additional' => array('' => '{Call:Lang:core:core:vse}', 't' => '{Call:Lang:core:core:tekstovaia}', 'm' => '{Call:Lang:core:core:matematiches}')
							),
							'fonts' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), Library::concatPrefixArrayKey($fonts, ',', ','))
							),
							'backgrounds' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), Library::concatPrefixArrayKey($backgrounds, ',', ','))
							),
							'math_actions' => array(
								'type' => 'select',
								'additional' => Library::array_merge(array('' => '{Call:Lang:core:core:vse}'), array(',+,' => 'Сложение', ',-,' => 'Вычитание', ',*,' => 'Умножение', ',/,' => 'Деление'))
							),
							'len' => array('type' => 'gap'),
							'len_to' => array('type' => 'gap'),
							'direction' => array(
								'type' => 'select',
								'additional' => array(
									'' => '{Call:Lang:core:core:vse}',
									'l' => '{Call:Lang:core:core:slevanapravo}',
									'r' => '{Call:Lang:core:core:spravanalevo}',
									't' => '{Call:Lang:core:core:sverkhuvniz}',
									'b' => '{Call:Lang:core:core:snizuvverkh}',
									'c' => '{Call:Lang:core:core:vkrugovuiupo}',
									'd' => '{Call:Lang:core:core:vkrugovuiupr}',
									'a' => '{Call:Lang:core:core:sluchajnymob}',
								)
							),
							'math_nums' => array('type' => 'gap'),
							'math_nums_to' => array('type' => 'gap'),
							'math_len' => array('type' => 'gap'),
							'math_len_to' => array('type' => 'gap'),
							'start_position' => array('type' => 'gap'),
							'start_position_to' => array('type' => 'gap'),
							'start_position_vertical' => array('type' => 'gap'),
							'start_position_vertical_to' => array('type' => 'gap'),
							'font_size' => array('type' => 'gap'),
							'font_size_to' => array('type' => 'gap'),
							'font_blur' => array('type' => 'gap'),
							'font_blur_to' => array('type' => 'gap'),
							'angle' => array('type' => 'gap'),
							'angle_to' => array('type' => 'gap'),
							'letter_offset' => array('type' => 'gap'),
							'letter_offset_to' => array('type' => 'gap'),
							'letter_vertical_offset' => array('type' => 'gap'),
							'letter_vertical_offset_to' => array('type' => 'gap'),
							'transparent' => array('type' => 'gap'),
							'transparent_to' => array('type' => 'gap'),
						),
						'orderFields' => array(
							'text' => '{Call:Lang:core:core:nazvaniiu}',
							'name' => '{Call:Lang:core:core:identifikato5}',
						),
						'isBe' => array('captcha_type', 'direction')
					),
					'actions' => array(
						'text' => $this->func.'&type_action=modify'
					)
				),
				'formTemplName' => 'big',
				'listTemplName' => 'big',
				'listParams2' => array(
					'caption' => '{Call:Lang:core:core:spisokstanda}'
				)
			)
		);
	}

	protected function func_captchaBackgrounds(){
		/*
			Фоны для CAPTCHA
		*/

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'uploadCaptchaBackground',
						'uploadCaptchaBackground',
						array('caption' => '{Call:Lang:core:core:zagruzitfon}')
					),
					'upload_captcha'
				),
				array(),
				array(),
				'big'
			)
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'captcha_backgrounds_list',
					array(
						'arr' => Files::readFolderFileParams(_W.$this->Core->getParam('captchaFolder')),
						'form_actions' => array('delete' => '{Call:Lang:core:core:udalit}'),
						'action' => 'captchaBackgroundsActions'
					),
					array(
						'caption' => '{Call:Lang:core:core:ispolzuemyef1}',
						'folder' => $this->Core->getParam('captchaFolder'),
					)
				),
				'big'
			)
		);
	}

	protected function func_uploadCaptchaBackground(){
		/*
			Загрузить фон CAPTCHA
		*/

		if(!$this->check()) return false;
		$this->refresh('captchaBackgrounds');
		return true;
	}

	protected function func_captchaBackgroundsActions(){
		/*
			Действия над файлами
		*/

		foreach($this->values['entry'] as $i => $e){
			if(!$this->Core->ftpRm(_W.$this->Core->getParam('captchaFolder').$i)) $this->setError('', '{Call:Lang:core:core:neudalosudal:'.Library::serialize(array($i)).'}');
		}

		if($this->errorMessages) $this->back('captchaBackgrounds');
		else $this->refresh('captchaBackgrounds');
	}


	/********************************************************************************************************************************************************************

																				Доступ к API

	*********************************************************************************************************************************************************************/

	protected function func_api(){
		/*
			Управление доступом к API
		*/

		$fields = array();
		if(!empty($this->values['ip'])){
			$fields = array(
				'ip' => $this->values['ip'],
				'type' => $this->values['type']
			);
		}

		$this->typicalMain(
			array(
				'form' => 'admin_access_ip',
				'table' => 'api_access_ip',
				'func' => 'api',
				'req' => array('api_access_ip', '*', " !`user_id` "),
				'caption' => '{Call:Lang:core:core:dobavitipvsp}',
				'listCaption' => '{Call:Lang:core:core:spisokipskot4}',
				'listParams' => array(
					'searchForm' => array(
						'searchFields' => array(
							'ip' => '{Call:Lang:core:core:poipiliegoch}',
							'type' => array('' => '{Call:Lang:core:core:vse}', 'allow' => '{Call:Lang:core:core:razreshennye}', 'disallow' => '{Call:Lang:core:core:zapreshchenn}')
						),
						'orderFields' => array('name' => 'IP')
					)
				),
				'fields' => $fields
			)
		);
	}


	/********************************************************************************************************************************************************************

																				Настройки системы

	*********************************************************************************************************************************************************************/

	protected function func_settings(){
		/*
			Выводим настройки
			Первым идет общий блок настроек единый для всех сайтов.
			Затем под каждый сайт выделяется вкладка, где есть блоки разделенные по модулям, и типам настроек
		*/

		$p = $this->DB->getPrefix();
		$s = $p.'settings';
		$m = $p.'modules';
		$bn = $p.'settings_block_names';
		$st = $p.'sites';

		$dbObj = $this->DB->Req("SELECT s.*, m.text AS mt, m.sort AS ms, bn.text AS bt, bn.sort AS bns, st.name AS sn, st.sort AS srt FROM
			$s AS s LEFT JOIN $m AS m ON m.url=s.module LEFT JOIN $bn AS bn ON bn.name=s.block LEFT JOIN $st AS st ON st.id=s.site
			WHERE s.show ORDER BY m.sort, m.text, bn.sort, s.sort");

		$values = array();
		$blocks = array();
		$blockNames = array();
		$blocksSort = array();

		$m = '';
		$b = '';

		while($r = $dbObj->Fetch()){
			$bName = $r['site'];
			$vName = $r['module'].'_'.$r['site'].'_'.$r['name'];

			if($r['sn']){
				$blockNames[$bName] = $r['sn'];
				$blocksSort[$bName] = $r['srt'];
			}

			if($r['mt'] && ($m != $r['mt'])){
				$m = $r['mt'];
				$blocks[$bName][$r['module'].'_'.$r['site'].'_caption']['text'] = $m;
				$blocks[$bName][$r['module'].'_'.$r['site'].'_caption']['type'] = 'caption';
			}

			if($r['bt']){
				$blocks[$bName][$r['module'].'_'.$r['site'].'_'.$r['block'].'_caption']['text'] = $r['bt'];
				$blocks[$bName][$r['module'].'_'.$r['site'].'_'.$r['block'].'_caption']['type'] = 'caption';
			}

			$values[$vName] = $r['crypt'] ? Library::Decrypt($r['value']) : $r['value'];
			if($r['var_type'] == 'obj') $values[$vName] = Library::unserialize($values[$vName]);
			$vars = Library::unserialize($r['vars']);
			if(!empty($vars['matrix'])) $blocks[$bName][$vName] = $vars['matrix'];

			$blocks[$bName][$vName]['text'] = $r['text'];
			$blocks[$bName][$vName]['type'] = $r['type'];
			if(!empty($vars['eval'])) $blocks[$bName][$vName] = Library::array_merge($blocks[$bName][$vName], eval($vars['eval']));
		}

		asort($blocksSort);
		$fObj = $this->newForm('settingsSet', 'settingsSet', array( 'caption' => '{Call:Lang:core:core:sistemnyenas}'));

		$this->addFormBlock($fObj, $blocks['0'], array(), array(), 'block0');
		$fObj->setParam('caption0', '{Call:Lang:core:core:obshchie}');
		$y = 1;

		foreach($blocksSort as $i => $e){
			$this->addFormBlock($fObj, $blocks[$i], array(), array(), 'block'.$y);
			$fObj->setParam('caption'.$y, $blockNames[$i]);
			$y ++;
		}

		$this->setContent($this->getFormText($fObj, $values, array(), 'multiblock'));
	}

	protected function func_settingsSet(){
		/*
			Устанавливает настройки
		*/

		if($this->values['core_0_adminAccessType'] == 'disallow' &&
			!$this->DB->cellFetch(array('admin_access_ip', 'id', "'{$_SERVER['REMOTE_ADDR']}' REGEXP (`ip`) AND !`admins_groups_id` AND !`admins_id` AND `type`='allow'"))){
			$this->setError('core_adminAccessType', '{Call:Lang:core:core:vynemozheteu1}');
		}

		if(!$this->check()){
			return false;
		}

		$params =& $this->values;
		$settings = $this->DB->columnFetch(array('settings', array('module', 'name', 'crypt', 'block', 'site', 'vars'), 'id', "`show`"));

		foreach($settings as $i => $e){
			$vars = Library::unserialize($e['vars']);
			$value = !isset($this->values[$e['module'].'_'.$e['site'].'_'.$e['name']]) ? '' : $this->values[$e['module'].'_'.$e['site'].'_'.$e['name']];
			if($e['crypt']) $value = Library::crypt($value);
			if(isset($vars['setEval'])) eval($vars['setEval']);
			$this->DB->Upd(array('settings', array('value' => $value), "`id`='{$i}'"));
		}
		$this->refresh('settings');
	}



	/********************************************************************************************************************************************************************

																		Вспомогательные функции

	*********************************************************************************************************************************************************************/

	public function __ava__insertTemplateIntoSource($obj, $source, $tmplFolder = '', $tmplType = ''){
		/*
			Перемещение данных между БД и папкой шаблонов
		*/

		$DB = $this->Core->DB;
		$where = '';
		if($tmplFolder && $tmplType) $where = db_main::q("`folder`=#0 AND `template_type`=#1", array($tmplFolder, $tmplType));

		if($source == 'folder'){
			if(!moduleInterface::checkCopy(TMPL)){
				$obj->setError('core_0_templateSource', '{Call:Lang:core:core:nevozmozhnos1}');
				return false;
			}

			foreach($DB->columnFetch(array('templates', '*', '', $where)) as $r){
				foreach($DB->columnFetch(array('template_pages', '*', '', "`template`='{$r['folder']}' AND `template_type`='{$r['type']}'")) as $pg){
					$file = TMPL.$r['type'].'/'.$r['folder'].'/'.$pg['url'];
					$tmpFile = TMP.Library::inventStr(10);
					Files::write($tmpFile, $pg['body']);
					$GLOBALS['Core']->ftpCopy($tmpFile, $file);
				}
			}
		}
		elseif($source == 'db'){
			foreach($DB->columnFetch(array('templates', '*', '', $where)) as $r){
				foreach($DB->columnFetch(array('template_pages', array('url', 'id'), '', "`template`='{$r['folder']}' AND `template_type`='{$r['type']}'")) as $r){
					$DB->Upd(array('template_pages', array('body' => Files::read(TMPL.$r['type'].'/'.$r['folder'].'/'.$pg['url'])), "`id`='{$pg['id']}'"));
				}
			}
		}
	}



	/********************************************************************************************************************************************************************

																Взаимодействие с сервером производителя

	*********************************************************************************************************************************************************************/

	private function developerServerGetData($params = array()){
		/*
			Подключается к серверу производителя и получает определенную информацию
		*/
	}



	/********************************************************************************************************************************************************************

																	Другие вспомогательные функции

	*********************************************************************************************************************************************************************/

	private function getAdminsGroups(){
		return Library::array_merge(
			array('0' => '{Call:Lang:core:core:neotnositkgr}'),
			$this->DB->columnFetch(array('admins_groups', 'name', 'id'))
		);
	}


	/********************************************************************************************************************************************************************

																				О программе

	*********************************************************************************************************************************************************************/

	protected function func_about(){
		/*
			О программе
		*/

		$v = $this->DB->CellFetch(array('version', 'version', "`name`='core'"));
		if(count(regExp::Split('.', $v)) == 5) $v = $v.' beta';

		$this->setContent('{Call:Lang:core:core:avapanelvers1:'.Library::serialize(array($v)).'}');
		$this->setContent('{Call:Lang:core:core:ustanovlenym}');

		foreach($this->Core->getModules() as $i => $e){
			$p = $this->Core->getModuleParams($i);
			if(count(regExp::Split('.', $p['version'])) == 5) $p['version'] = $p['version'].' beta';
			$this->setContent('{Call:Lang:core:core:versiia:'.Library::serialize(array($e, $p['version'])).'}');
		}

		$this->setContent("</ul></div>");
	}
}

?>