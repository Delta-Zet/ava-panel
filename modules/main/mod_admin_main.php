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


class mod_admin_main extends gen_main{

	protected function func_main(){
		/*
			Главная страница раздела администратора
			Может быть установлено:
				- Вывод всех основных блоков верхнего уровня (через subblocks)
				- Вывод той страницы которую админ назначил как главную
		*/

		if(!empty($this->User->adminParams['main_page'])){
			$this->redirect2($this->User->adminParams['main_page']);
			return true;
		}

		$this->values['blockId'] = 0;
		$this->func_subblocks();
	}


	/*******************************************************************************************************************************************************************

																			Аутентификация

	********************************************************************************************************************************************************************/


	protected function func_authAdmin(){
		/*
			Аутентификация. Данные об аутентифицированном пользователе хранятся в сессии. Однако если пользователь указал "запомнить", ему устанавливаются специальные
			cookie сроком до 2037г., которые содержат логин и хеш пароля, при выходе удаляется запись об аутентифицированном пользователе и cookie, указывающие на то
			что пользователь запомнен.
		 */

		$this->Core->setTempl('auth_page');
		$this->setMeta('{Call:Lang:core:core:vkhod}');

		if(empty($this->values['go_auth'])){
			$this->setContent(
				$this->getFormText(
					$this->addFormBlock(
						$this->newForm(
							'authAdmin',
							'authAdmin'
						),
						'type_auth'
					),
					array(),
					array('go_auth' => 1, 'redirect' => $this->Core->getBackUrl()),
					'auth_admin'
				)
			);

			return;
		}

		if(!$this->User->authAdmin($this)){
			$this->check();
			return false;
		}

		$this->setAdminStat('login', '{Call:Lang:core:core:vkhodvadmink}', '', 0, array(), '', $this->User->getAdminId());
		$this->redirect2(empty($this->values['redirect']) ? $this->path : $this->values['redirect'], '{Call:Lang:core:core:autentifikat1}');
		return true;
	}

	protected function func_logout(){
		/*
			Очищает сессию админа и кукисы запоминания
		*/

		$id = $this->User->getAdminId();
		$this->User->logout();

		$this->setAdminStat('logout', '{Call:Lang:core:core:vykhodizadmi}', '', 0, array(), '', $id);
		$redirect = empty($this->values['redirect']) ? $this->path : $this->values['redirect'];
		$this->redirect2($redirect, '{Call:Lang:core:core:vashiautenti}');

		return true;
	}


	/***************************************************************************************************************************************************************

														Меню админа и другие функции вывода служебной информации

	****************************************************************************************************************************************************************/

	protected function func_subblocks(){
		/*
			Вывод списка всех подблоков относящихся к данному пункту меню
		*/

		$id = db_main::Quot($this->values['blockId']);
		$siteId = $this->Core->adminSite->getSiteId();
		$entries = array();

		$t1 = $this->DB->getPrefix().'admin_menu';
		$t2 = $this->DB->getPrefix().'modules';

		$dbObj = $this->DB->Req("SELECT t1.*
			FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t2.url=t1.pkg
			WHERE t1.show AND t1.parent_id='$id' AND (t2.sites REGEXP (',{$siteId},') OR t1.pkg='main' OR t1.pkg='core')
			ORDER BY t1.sort"
		);

		while($r = $dbObj->Fetch()){
			if($this->authorityByUrl($r)) $entries[$r['id']] = $r;
		}

		$this->setContent(
			$this->getListText(
				$this->newList(
					'admin_subblocks',
					array(
						'arr' => $entries
					),
					$this->DB->rowFetch(array('admin_menu', '*', "`id`='$id'")),
					'admin_menu'
				),
				'admin_subblocks'
			)
		);
	}

	protected function func_buttons(){
		/*
			Выводит служебные кнопки админа
		*/

		$admin = $this->Core->userIsAdmin();
		$buttons = $this->DB->columnFetch(array('admin_buttons', array('name', 'target'), 'url', "admin='$admin'", '`sort`'));

		foreach($buttons as $i => $e){
			$e['url'] = $i;
			$this->setContent($this->Core->readBlockAndReplace('admin_menu', 'admin_buttons', $this, $e), 'buttons');
		}
	}

	protected function func_menu(){
		/*
			Генерация админского меню
		*/

		$menu = array();
		$path = array();
		$result = '';

		$t1 = $this->DB->getPrefix().'admin_menu';
		$t2 = $this->DB->getPrefix().'modules';
		$siteId = $this->Core->adminSite->getSiteId();

		$mmo = $this->Core->getMainModObj();
		$mmMod = $mmo->getMod();
		$lastUrl = '';

		if(!empty($mmo->funcName)){
			$path[] = array('url' => _D.ADMIN_FOLDER.'/index.php?mod='.$mmo->getMod().'&func='.$mmo->getFunc().'&'.Library::array2url(Library::array_merge($this->Core->getGPCArr('g'), $this->Core->getGPCArr('p'))), 'text' => $mmo->funcName, 'entry_active' => true);
		}

		foreach($mmo->pathPoint as $i => $e){
			$path[] = array('url' => _D.ADMIN_FOLDER.'?mod='.$mmMod.'&func='.$i, 'text' => $e);
		}

		$mObj = $this->DB->Req("SELECT t1.* FROM $t1 AS t1 LEFT JOIN $t2 AS t2 ON t1.pkg=t2.url
			WHERE t1.show AND (t2.sites REGEXP (',{$siteId},') OR t1.pkg='main' OR t1.pkg='core') ORDER BY t1.parent_id DESC, sort");

		while($r = $mObj->Fetch()){
			if($this->User->isAuthority($r['pkg'])){
				if($mmo->pathFunc && $r['url'] == '?mod='.$mmMod.'&func='.$mmo->pathFunc) $current = $r['id'];
				if(!$this->authorityByUrl($r)) continue;

				if(!empty($r['url2']['query']) && $r['url2']['query'] == $this->Core->getGPCVar('s', 'QUERY_STRING')){
					$r['entry_active'] = true;
					$path[] = array('url' => $r['url'], 'text' => $r['text'], 'entry_active' => true);
					$current = $r['parent_id'];
				}

				if(!empty($current) && $current == $r['id']){
					$path[] = array('url' => $r['url'], 'text' => $r['text']);
					$current = $r['parent_id'];
				}

				$menu[$r['id']] = $r;
			}
		}

		ksort($menu);
		$this->setContent(
			$this->getListText(
				$this->newList(
					'admin_menu',
					array(
						'arr' => $menu,
						'multilevelReq' => array('parent' => 'parent_id', 'id' => 'id')
					),
					array(),
					'admin_menu'
				),
				'admin_menu'
			),
			'menu'
		);

		$this->setContent(
			$this->getListText(
				$this->newList(
					'path_point',
					array('arr' => array_reverse($path)),
					array(),
					'admin_menu'
				),
				'path_point'
			),
			'path'
		);
	}

	private function authorityByUrl(&$r){
		if(!$r['url']) $r['url'] = _D.ADMIN_FOLDER.'/index.php?mod=main&func=subblocks&blockId='.$r['id'];
		elseif(regExp::match("/^\?/", $r['url'], true)) $r['url'] = _D.ADMIN_FOLDER.'/index.php'.$r['url'];

		$r['url2'] = parse_url($r['url']);
		$parsedQuery = Library::parseStr($r['url2']['query']);
		return !(!empty($parsedQuery['func']) && !$this->User->isAuthority($parsedQuery['mod'], $parsedQuery['func']));
	}


	/*******************************************************************************************************************************************************************

																			Предпочтения

	********************************************************************************************************************************************************************/

	protected function func_prefs(){
		/*
			Предпочтения для админа

			1. Главная страница
			2. Шаблон админки
		*/

		$id = $this->User->getAdminId();
		$values = $this->DB->rowFetch(array('admins', array('data', 'login', 'eml', 'user_id'), "`id`='$id'"));
		$values = Library::array_merge(Library::unserialize($values['data']), $values);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'prefsSet',
						'prefsSet',
						array('caption' => '{Call:Lang:core:core:nastrojkiadm1}')
					),
					array(
						'admin_data',
						$this->getUserRegFormMatrix('in_admin', '', $v2)
					),
					array('templates' => $this->Core->getAllTemplates('admin', true, true))
				),
				Library::array_merge($v2, $values),
				array(),
				'big'
			)
		);

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'newPwd',
						'newPwd',
						array(
							'caption' => '{Call:Lang:core:core:smenitparola}'
						)
					),
					'type_newpwd'
				),
				array(),
				array(),
				'big'
			)
		);
	}

	protected function func_prefsSet(){
		/*
			Предпочтения админки
		*/

		if(!$this->check()) return false;
		$data = $this->fieldValues(array('main_page', 'template'));
		foreach($this->getUserRegFormMatrix('in_admin') as $i => $e) $data[$i] = isset($this->values[$i]) ? $this->values[$i] : '';

		$this->DB->Upd(array('admins', array('data' => $data, 'eml' => $this->values['eml']), "`id`='".$this->User->getAdminId()."'"));
		$this->Core->reauthUserSession($this->User->getUserId());
		$this->refresh('prefs');

		return true;
	}

	protected function func_newPwd(){
		if(!$this->check()) return false;

		$id = $this->User->getAdminId();
		$p = $this->DB->getPrefix();
		$r = $this->DB->rowFetch("SELECT t1.login, t2.code FROM {$p}admins AS t1 LEFT JOIN {$p}users AS t2 ON t1.user_id=t2.id WHERE t1.id='$id'");

		$this->DB->Upd(array('admins', array('pwd' => Library::getPassHash($r['login'], $this->values['pwd'], $r['code'])), "`id`='$id'"));
		$this->refresh('prefs');
		return true;
	}


	/***************************************************************************************************************************************************************

																	Персональные настройки администратора

	****************************************************************************************************************************************************************/

	protected function func_admin_buttons(){
		/*
			Устанавливает служебные кнопки администратора (блок кнопок вверху)
		*/

		$admin = $this->values['admin'] = $this->Core->userIsAdmin();
		$this->typicalMain(
			array(
				'form' => 'admin_buttons',
				'req' => array('admin_buttons', '*', "admin='$admin'", "`sort`"),
				'caption' => '{Call:Lang:core:core:dobavitknopk}',
				'modifyCaption' => '{Call:Lang:core:core:izmeneniekno}',
				'fields' => $this->fieldValues(array('name', 'url', 'sort', 'admin', 'target')),
				'listCaption' => '{Call:Lang:core:core:vashisluzheb}',
				'listParams' => array(
					'searchForm' => array(
						'orderFields' => array(
							'name' => '{Call:Lang:core:core:imeni}',
							'url' => 'URL',
						),
						'searchFields' => array(
							'name' => '{Call:Lang:core:core:imia}',
							'url' => 'URL',
							'target' => '{Call:Lang:core:core:otkrytv}'
						),
						'searchMatrix' => array(
							'target' => array(
								'additional' => array(
									'' => '{Call:Lang:core:core:liubye}',
									'_top' => '{Call:Lang:core:core:tomzheokne}',
									'_blank' => '{Call:Lang:core:core:novomokne}'
								),
								'type' => 'select'
							)
						)
					),
					'actions' => array(
						'name' => 'admin_buttons&type_action=modify'
					)
				)
			)
		);
	}



	/***************************************************************************************************************************************************************

																		Статистика администраторов

	****************************************************************************************************************************************************************/

	protected function func_myStat(){
		/*
			Статистика заходов администратора в его раздел. Отображаются:
			1. Заходы админа в раздел администратора, выходы и выполнение администратором каких-либо действий
			2. Подсчет посещенных администратором страниц
			3. Время захода и выхода, продолжительность работы
			4. Иные сведения в зависимости от настроек тех или иных модулей
		*/

		$this->showStat($this->Core->userIsAdmin());
	}
}

?>