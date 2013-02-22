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


class installModulesCms extends InstallModuleObject implements InstallModuleInterface{
	/*
		В процессе инсталляции в метод checkInstall отправляется объект mod_admin_core (или другой активный)
		Передаваемый объект должен содержать введенные $prefix и dbId.

		Инсталлятор последовательно получает все необходимые для инсталляции данные, после чего вызывает func_clonePackageNext в модуле mod_admin_core вместе с собранными
		данными

		func_clonePackageNext должен последовательно вызывать все инсталлируемые модули
		По завершении он передает все собранные данные в func_clonePackageEnd, который последовательно выполняет вызовы метода Install инсталлятора, а тот в свою очередь
		может собирать необходимые данные, либо воссоздавать их, после чего передавать в func_clonePackageEnd.

		Все обслуживаемые инсталлятором ссылки должны вести в mod_admin_core, для checkInstall - в func_clonePackageNext , для install - в func_clonePackageEnd.
		Вместе с вызовом должен передаваться передаваться параметр current=имяМодуля.
		Когда все данные уже установлены, должен вызываться с параметром end=имяМодуля.
		Если ревызов не производится, инсталлятор может установить end и пр. прямо в объект $obj, а потом завершить работу с true

		После удачного получения конечных данных func_clonePackageEnd осуществляет запись о установке активной копии модуля.
		После получения всех данных производится запись о установленной активной копии пакета (только для того чтобы облегчить комплексное удаление)
	*/


	public function Install(){
		/*
			Инсталляция пакета
		 */

		/*
		  Основные данные
		  id - идентификатор
		  version_id - идентификатор версии
		  parent_id - родитель
		*/

		$this->createAllTables();
		$this->setDefaultStructures($this->getDefaultStructures($this->obj->values), 'Ins');
		$this->setDefaultBlocks($this->getDefaultBlocks($this->obj->values), 'Ins');
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
		$this->dropAllTables();
		$this->dropAllDefaults();
		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update($oldVersion, $newVersion){
		switch(true){
			case !Library::versionCompare('0.0.1.16', $oldVersion):
				$this->iObj->DB->Drop('form_blocks');
		}

		$v = $this->obj->values;
		$v['sites'] = $this->iObj->Core->getModuleSites($this->prefix);
		$this->updateAllTables();
		$this->updateAllDefaults($v);

		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}

	public function getTables(){

		$return['page_templates'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'vars' => '',
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

		$return['tags'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'pop' => 'INT',
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

		$return['pages'] = array(
			array(
				'id' => '',
				'parent' => 'VARCHAR(64)',
				'version_id' => 'INT',
				'page_template' => '',
				'subpage_template' => '',
				'date' => '',
				'start' => 'INT',
				'stop' => 'INT',
				'name' => '',
				'url' => 'VARCHAR(64)',
				'body' => 'MEDIUMTEXT',
				'vars' => '',
				'tags' => 'TEXT',
				'sort' => '',
				'show' => ''			//Доступ (нет/ 1 - админы/ 2 - юзеры/ 3 - гости/ 4 - забаненные/ 5 - инд. настройки)
			),
			array(
				'uni' => array(
					array('url'),
					array('name')
				)
			)
		);

		$return['versions'] = array(
			array(
				'id' => '',
				'page_id' => 'INT',
				'version_name' => '',
				'version_date' => '',
				'subpage_template' => '',
				'name' => '',
				'body' => 'MEDIUMTEXT',
				'vars' => '',
				'params' => 'TEXT',
				'structure_params' => 'TEXT',
				'show' => ''
			)
		);

		$return['blocks'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => '',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name')
				)
			)
		);

		$return['structures'] = array(
			array(
				'id' => '',
				'name' => '',
				'text' => '',
				'type' => '',
				'module' => '',
				'table' => '',
				'in_page' => 'TINYINT',
				'in_page_up' => 'CHAR(1)',
				'vars' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['structure_blocks'] = array(
			array(
				'id' => '',
				'structures_id' => 'INT',
				'name' => '',
				'text' => '',
				'type' => '',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('structures_id', 'name')
				)
			)
		);

		$return['structure_pages'] = array(
			array(
				'page_id' => 'INT',
				'structure_id' => 'INT',
				'entry_id' => 'INT'
			),
			array(
				'uni' => array(
					array('page_id', 'structure_id'),
					array('structure_id', 'entry_id'),
				)
			)
		);

		$return['forms'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',
				'text' => '',
				'vars' => '',
				'sort' => '',
				'show' => '',
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['form_blocks'] = array(
			array(
				'id' => '',
				'form_id' => 'VARCHAR(64)',
				'name' => '',
				'text' => '',
				'type' => '',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('form_id', 'name'),
				)
			)
		);

		$return['export'] = array(
			array(
				'id' => '',
				'name' => 'VARCHAR(64)',
				'text' => '',
				'url' => '',
				'format' => 'CHAR(1)',		//f - полный, s - сокращенный
				'parent_page' => 'INT',
				'update_interval' => 'INT',
				'last' => 'INT',
				'vars' => '',
				'show' => '',
				'sort' => ''
			),
			array(
				'uni' => array(
					array('name'),
					array('text'),
				)
			)
		);

		$return['exported_urls'] = array(
			array(
				'id' => '',
				'feed_id' => '',
				'url' => '',
				'date' => '',
				'vars' => '',
				'page_id' => 'INT'
			),
			array(
				'uni' => array(
					array('url'),
				)
			)
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
				array(
					'text' => '{Call:Lang:modules:cms:stranitsy}',
					'pkg' => $this->prefix,
					'submenu' => array(
						array('text' => '{Call:Lang:modules:cms:stranitsy}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=pages'),
						array('text' => 'RSS-ленты', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=export'),
						array('text' => 'Шаблоны заполнения', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=pageTemplates'),
					)
				),
				array('text' => 'Теги', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=tags'),
				array('text' => '{Call:Lang:modules:cms:kontentbloki}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=blocks'),
				array('text' => '{Call:Lang:modules:cms:kontentstruk1}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=structure'),
				array('text' => '{Call:Lang:modules:cms:formy}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=forms'),
				array('text' => '{Call:Lang:modules:cms:zagruzkafajl1}', 'pkg' => $this->prefix, 'url' => '?mod='.$this->prefix.'&func=files'),
			)
		);

		return $return;
	}

	public function getDefaultModuleLinks($params){
		$return = array(
			array(
				'text' => '{Call:Lang:modules:cms:lichnyenastr}',
				'name' => 'cabinate',
				'mod' => $this->prefix,
				'url' => 'index.php?mod=main&func=cabinate',
				'usedCmsLevel' => array('usermenu')
			)
		);

		return $return;
	}

	public function getDefaultFolders($params){
		return array('storage/uploads/' => '{Call:Lang:modules:cms:osnovnaiapap}');
	}

	public function getDefaultStructures($params){
		/*
			Дефолтные настройки уровня ядра
		*/

		return array(
			array(
				'mainmenu' => array('text' => '{Call:Lang:modules:cms:glavnoemeniu}', 'in_page' => 1, 'in_page_up' => 1),
				'menu1' => array('text' => '{Call:Lang:modules:cms:pervoedopoln}', 'in_page' => 1, 'in_page_up' => 1),
				'menu2' => '{Call:Lang:modules:cms:vtoroedopoln}',
				'usermenu' => '{Call:Lang:modules:cms:polzovatelsk}',
				'news' => array('text' => '{Call:Lang:modules:cms:novosti}', 'in_page' => 1, 'in_page_up' => 1)
			),
			array(
				'mainmenu' => array(
					'link' => array(
						'text' => '{Call:Lang:modules:cms:ssylka}',
						'in_page_ins_style' => 2,
						'form_as' => 'link'
					)
				),
				'menu1' => array(
					'link' => array(
						'text' => '{Call:Lang:modules:cms:ssylka}',
						'in_page_ins_style' => 2,
						'form_as' => 'link'
					)
				),
				'menu2' => array(
					'link' => array(
						'text' => '{Call:Lang:modules:cms:ssylka}',
						'in_page_ins_style' => 2,
						'form_as' => 'link'
					)
				),
				'usermenu' => array(
					'link' => array(
						'text' => '{Call:Lang:modules:cms:ssylka}',
						'in_page_ins_style' => 2,
						'form_as' => 'link'
					)
				),
				'news' => array(
					'date' => array(
						'text' => '{Call:Lang:modules:cms:data}',
						'type' => 'calendar2',
						'in_page_ins_style' => 2,
						'form_as' => 'date'
					),
					'link' => array(
						'text' => '{Call:Lang:modules:cms:ssylka}',
						'in_page_ins_style' => 2,
						'form_as' => 'link'
					),
					'notice' => array(
						'text' => '{Call:Lang:modules:cms:anons}',
						'type' => 'textarea',
						'in_page_ins_style' => 3
					)
				),
			)
		);
	}

	public function setDefaultStructures($params, $type = 'Ins'){
		/*
			Дефолтные настройки уровня ядра
		*/

		$params = $this->paramReplaces($params);
		$j = $this->iObj->DB->cellFetch(array('structures', 'sort', "", "`sort` DESC")) + 1;
		$templates = $this->iObj->Core->getAllTemplates('main', false, true);

		foreach($params[0] as $i => $e){
			$extra = array();
			foreach($templates as $i1 => $e1){
				$extra['template_'.$i1] = $i;
			}

			$extra['name'] = $i;
			$extra['sort'] = $j;
			$extra['type'] = 'internal';

			$extra['admin_template'] = 'structures_standart';
			$extra['ava_form_transaction_id'] = $this->iObj->callFuncAndGetFormId('structure', 'structureNew');
			$extra['in_page'] = 0;

			if(!is_array($e)) $e = array('text' => $e);
			$this->iObj->values = Library::array_merge($extra, $e);

			if(($id = $this->iObj->callFunc('structureNew')) && !empty($params[1][$i])){
				$this->iObj->getStructureParamsById($id, true);
				$j1 = 1;

				foreach($params[1][$i] as $i1 => $e1){
					if(!is_array($e1)) $e1 = array('text' => $e1);

					$this->iObj->values = Library::array_merge(
						array(
							'structures_id' => $id,
							'name' => $i1,
							'type' => 'text',
							'sort' => $j1,
							'field_action' => 'add',
							'ava_form_transaction_id' => $this->iObj->callFuncAndGetFormId('redactForm', 'redactForm', array('structures_id' => $id)),
							'show' => 1,
							'insert_field' => 1,
							'insert_field_type' => ''
						),
						$e1
					);
					$this->iObj->callFunc('redactForm');
				}

				$j1 ++;
			}

			$j ++;
		}

		return true;
	}

	public function getDefaultBlocks($params){
		return array(
			'title' => '&lt; TITLE &gt;',
			'keywords' => '&lt; KEYWORDS &gt;',
			'description' => '&lt; DESCRIPTION &gt;',
			'caption' => '{Call:Lang:modules:cms:ltzagolovokg}',
		);
	}

	public function setDefaultBlocks($blocks, $type = 'Ins'){
		$j = $this->iObj->DB->cellFetch(array('blocks', 'sort', "", "`sort` DESC")) + 1;

		foreach($blocks as $i => $e){
			if(!is_array($e)) $e = array('text' => $this->paramReplaces($e));

			$this->iObj->values = Library::array_merge(
				array(
					'name' => $i,
					'type' => 'text',
					'sort' => $j,
					'show' => 3,
					'ava_form_transaction_id' => $this->iObj->callFuncAndGetFormId('blocks', 'blocks'),
					'field_action' => 'add'
				),
				$e
			);

			$this->iObj->callFunc('blocks');
			$j ++;
		}
	}

	public function getDefaultCronjobs($params){
		/*
			крон-задачи
		*/

		return array();

		$return[$this->prefix]['rssFeed'] = array(
			'month' => '*',
			'day' => '*',
			'week' => '*',
			'hour' => '*',
			'minute' => '*',
			'tick' => '1',
			'command' => 'return $GLOBALS["Core"]->callModule("'.$this->prefix.'", "rssFeed", array(), 0);'."\n",
			'comment' => 'Экспорт новостей через RSS',
		);

		return $return;
	}

	public function getDefaultModulePlugins($params){
		/*
			крон-задачи
		*/

		return array(
			'subpages' => array(
				'text' => 'Список подстраниц',
				'code' => '$GLOBALS["Core"]->callModule("'.$this->prefix.'", "subpages", $GLOBALS["Core"]->getGPCArr("callData"));'
			)
		);
	}
}


?>