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



class InstallModuleObject extends InstallObject{
	public $iObj;		//Инсталлируемый объект
	public $prefix;		//URL-имя инсталируемого объекта

	public function __construct($DB, $obj, $prefix, $params = array()){
		$this->obj = $obj;
		$this->params = $params;

		$this->prefix = $prefix;
		$this->iObj = $this->obj->Core->callModule($prefix);
	}

	public function __ava__createAllTables(){
		if(method_exists($this, 'getTables')) $this->createTables($this->getTables());
	}

	public function __ava__dropAllTables(){
		if(method_exists($this, 'getTables')){
			$tList = array();
			foreach($this->getTables() as $i => $e) $tList[$i] = $i;
			if($tList) $this->dropTables($tList);
	}

	public function __ava__updateAllTables(){
		if(method_exists($this, 'getTables')) $this->updateTables($this->getTables());
	}

	public function __ava__setAllDefaults($params){
		/*
			Устанавливает значения по умолчанию
		*/

		if(method_exists($this, 'getDefaultSettingsCaptions')) $this->setDefaultSettingsCaptions($this->getDefaultSettingsCaptions($params));
		if(method_exists($this, 'getDefaultSettings')) $this->setDefaultSettings($this->getDefaultSettings($params));
		if(method_exists($this, 'getDefaultAdminMenu')) $this->setDefaultAdminMenu($this->getDefaultAdminMenu($params));

		if(method_exists($this, 'getDefaultMailTemplates')) $this->setDefaultMailTemplates($this->getDefaultMailTemplates($params));
		if(method_exists($this, 'getDefaultCronjobs')) $this->setDefaultCronjobs($this->getDefaultCronjobs($params));
		if(method_exists($this, 'getDefaultModuleLinks')) $this->setDefaultModuleLinks($this->getDefaultModuleLinks($params));

		if(method_exists($this, 'getDefaultModulePlugins')) $this->setDefaultModulePlugins($this->getDefaultModulePlugins($params));
		if(method_exists($this, 'getDefaultFonts')) $this->setDefaultFonts($this->getDefaultFonts($params));
		if(method_exists($this, 'getDefaultFolders')) $this->setDefaultFolders($this->getDefaultFolders($params));

		if(method_exists($this, 'getDefaultCaptchas')) $this->setDefaultCaptchas($this->getDefaultCaptchas($params));
		if(method_exists($this, 'getDefaultUserFormTypes')) $this->setDefaultUserFormTypes($this->getDefaultUserFormTypes($params));
		if(method_exists($this, 'getDefaultUserFormFields')) $this->setDefaultUserFormFields($this->getDefaultUserFormFields($params));
	}

	public function __ava__updateAllDefaults($params){
		/*
			Устанавливает значения по умолчанию
		*/

		if(method_exists($this, 'getDefaultSettingsCaptions')) $this->setDefaultSettingsCaptions($this->getDefaultSettingsCaptions($params));
		if(method_exists($this, 'getDefaultSettings')) $this->setDefaultSettings($this->getDefaultSettings($params));
		if(method_exists($this, 'getDefaultAdminMenu')) $this->setDefaultAdminMenu($this->getDefaultAdminMenu($params));

		if(method_exists($this, 'getDefaultMailTemplates')) $this->setDefaultMailTemplates($this->getDefaultMailTemplates($params));
		if(method_exists($this, 'getDefaultCronjobs')) $this->setDefaultCronjobs($this->getDefaultCronjobs($params));
		if(method_exists($this, 'getDefaultModuleLinks')) $this->setDefaultModuleLinks($this->getDefaultModuleLinks($params));

		if(method_exists($this, 'getDefaultModulePlugins')) $this->setDefaultModulePlugins($this->getDefaultModulePlugins($params));
		if(method_exists($this, 'getDefaultFonts')) $this->setDefaultFonts($this->getDefaultFonts($params));
		if(method_exists($this, 'getDefaultFolders')) $this->setDefaultFolders($this->getDefaultFolders($params));

		if(method_exists($this, 'getDefaultCaptchas')) $this->setDefaultCaptchas($this->getDefaultCaptchas($params));
		if(method_exists($this, 'getDefaultUserFormTypes')) $this->setDefaultUserFormTypes($this->getDefaultUserFormTypes($params));
		if(method_exists($this, 'getDefaultUserFormFields')) $this->setDefaultUserFormFields($this->getDefaultUserFormFields($params));
	}

	public function __ava__dropAllDefaults(){
		if(method_exists($this, 'dropDefaultAdminMenu')) $this->dropDefaultAdminMenu();
		if(method_exists($this, 'dropDefaultMailTemplates')) $this->dropDefaultMailTemplates();

		if(method_exists($this, 'dropDefaultCronjobs')) $this->dropDefaultCronjobs();
		if(method_exists($this, 'dropDefaultModuleLinks')) $this->dropDefaultModuleLinks();
		if(method_exists($this, 'dropDefaultModulePlugins')) $this->dropDefaultModulePlugins();
	}

	public function __ava__dropDefaultSettings(){

	public function __ava__dropDefaultAdminMenu(){
	}

	public function __ava__dropDefaultMailTemplates(){

	public function __ava__dropDefaultCronjobs(){

	public function __ava__dropDefaultModuleLinks(){

	public function __ava__dropDefaultPlugins(){

	public function __ava__setDefaultSettingsCaptions($settings, $type = 'Ins'){
			$j ++;
		}

	public function __ava__setDefaultSettings($settings, $type = 'Ins'){
		$j = 0;

		foreach($settings as $site => $e){
			foreach($e as $mod => $e1){

						$e3['name'] = $name;
						$e3['block'] = $block;
						$e3['module'] = $mod;

						$e3['site'] = $site;
						$e3['sort'] = $j;
						$e3['show'] = 1;

						if(empty($e3['var_type'])){
							elseif(!empty($e3['value']) && regExp::float($e3['value'])) $e3['var_type'] = 'flt';
							elseif(!empty($e3['type']) && $e3['type'] == 'checkbox_array') $e3['var_type'] = 'obj';
							else $e3['var_type'] = 'str';
						}

						if(empty($e3['type']) && !empty($e3['vars']['matrix']['additional'])) $e3['type'] = 'select';
						elseif(empty($e3['type'])) $e3['type'] = 'text';
						if(!empty($e3['crypt']) && !empty($e3['value'])) $e3['value'] = Library::Crypt($e3['value']);
						$this->DB->$type(array('settings', $e3, "`name`='$name' AND `module`='$mod' AND `site`='$site'"));

						$j ++;
					}
				}
			}
		}
	}

	public function __ava__setDefaultAdminMenu($menu, $type = 'Ins', $parentId = 0){
			Устанавливает дефолтные значения настроекъ
		*/

		$j = $this->DB->cellFetch(array('admin_menu', 'sort', "", "`sort` DESC")) + 1;

		foreach($menu as $i => $e){
			if(!$e['text']) continue;

			if(!$e['pkg']) throw new AVA_Exception("Не определен модуль для \"{$e['text']}\"");
			$ins = Library::array_merge(array('parent_id' => $parentId, 'sort' => $j, 'show' => 1), $e);
			unset($ins['submenu']);
			$id = $this->DB->$type(array('admin_menu', $ins));
			if(!empty($e['submenu'])){

			$j ++;
		}

	public function __ava__setDefaultMailTemplates($templates, $type = 'Ins'){
		$site = $this->DB->cellFetch(array('sites', 'name', "`default`='1'"));
		if(!$eml) throw new AVA_Exception('{Call:Lang:core:core:nenajdenemai}');

		foreach($templates as $mod => $e){
					Library::array_merge(
						array(
							'mod' => $mod,
							'name' => $name,
							'sender' => '{Call:Lang:core:core:administrats:'.Library::serialize(array($site)).'}',
							'sender_eml' => $eml,
							'notify_eml' => $eml,
							'notify_sender' => '{Call:Lang:core:core:administrats:'.Library::serialize(array($site)).'}',
							'notify_sender_eml' => $eml,
							'system' => 1,
							'notify_fail' => 2,
							'system' => 1,
							'format' => 'text/plain',
							'notify_fail_subj' => '{Call:Lang:core:core:neudachnaiao}',
							'notify_fail_body' => '{Call:Lang:core:core:pismodliaeml}',
						),
						$e1
					)
				);

				$this->DB->$type(array('mail_templates', $e1, "`mod`='$mod' AND `name`='$name'"));
			}
		}

	public function __ava__setDefaultCronjobs($tasks, $type = 'Ins'){
			foreach($e as $name => $e1){
				$e1['name'] = $name;
				$e1['comment'] = $this->paramReplaces($e1['comment']);

				$e1['del_forbid'] = 1;
				$e1['show'] = 1;
				$this->DB->$type(array('cron', $e1, "`name`='$name'"));
			}
		}
	}

	public function __ava__setDefaultModuleLinks($links, $type = 'Ins', $parent = ''){

		foreach($links as $i => $e){
			$ins = Library::array_merge(array('parent' => $parent, 'sort' => $j, 'show' => 1), $e);
			$ins['text'] = $this->paramReplaces($ins['text']);
			unset($ins['submenu'], $ins['usedCmsLevel']);

			if($id = $this->DB->$type(array('module_links', $ins))){
				if(!empty($e['usedCmsLevel']) && ($cmsMod = $this->obj->Core->getTopCMSModule($ins['mod']))){
					foreach($e['usedCmsLevel'] as $e1){
						$cmsObj->insertMenuLinks($ins, $e1, $parent);
					}

				$j ++;
			}

			if(!empty($e['submenu'])) $this->setDefaultModuleLinks($e['submenu'], $type, $e['name']);
		}
	}

	public function __ava__setDefaultModulePlugins($calls, $type = 'Ins'){

		foreach($calls as $i => $e){
			$e['name'] = $this->prefix.'_'.$i;
			$e['text'] = $this->paramReplaces($e['text']);
			$e['type'] = 'widget';

			$e['vars'] = array();
			$e['sort'] = $j;
			$e['show'] = 1;

			$e['version'] = $this->params['version'];
			$id = $this->DB->$type(array('plugins', $e));
			$j ++;

	public function __ava__setDefaultFonts($fonts, $type = 'Ins'){
		foreach($fonts as $i => $e){
			$j ++;
	}

	public function __ava__setDefaultFolders($folders, $type = 'Ins'){
		$j = $this->DB->cellFetch(array('folders', 'sort', "", "`sort` DESC")) + 1;

		foreach($folders as $i => $e){
				array(
					'folders',
					array(
						'name' => $this->paramReplaces($e),
						'path' => $i,
						'modules' => ','.$this->prefix.',',
						'show' => 1,
						'sort' => $j
					),
					"`path`='{$e['path']}'"
				)
			);

			$j ++;
		}
	}

	public function __ava__setDefaultCaptchas($captchas, $type = 'Ins'){

		foreach($captchas as $i => $e){
			$e['text'] = $this->paramReplaces($e['text']);
			$e['sort'] = $j;

			$e['show'] = 1;
			if(empty($e['symbols'])) $e['symbols'] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			$this->DB->$type(array('captcha_standarts', $e));
		}

	public function __ava__setDefaultUserFormTypes($types, $type = 'Ins'){
		foreach($types as $i => $e){
			$this->DB->$type(array('users_form_types', array('name' => $i, 'text' => $e, 'sort' => $j, 'show' => 1), "`name`='{$i}'"));
			$j ++;
		}
	}

	public function __ava__setDefaultUserFormFields($fields, $type = 'Ins'){
		$j = $this->DB->cellFetch(array('user_reg_form', 'sort', "", "`sort` DESC")) + 1;
		$issetFields = $this->DB->columnFetch(array('user_reg_form', 'id', 'name'));

		foreach($fields as $i => $e){
			if(isset($issetFields[$i])) continue;

			if(!isset($e['type'])) $e['type'] = 'text';
			if(!isset($e['form_types'])) $e['form_types'] = $this->DB->columnFetch(array('users_form_types', 'name', ''));

			if(!isset($e['in_reg'])) $e['in_reg'] = 1;
			if(!isset($e['in_account'])) $e['in_account'] = 1;
			if(!isset($e['in_admin'])) $e['in_admin'] = 1;

			$e['name'] = $i;
			$e['sort'] = $j;
			$e['show'] = 1;
			$e['form_types'] = ','.implode(',', $e['form_types']).',';

			if(!isset($e['toUserTable'])){
				$e['insert_field'] = 1;

			$this->obj->formFieldsAdd('user_reg_form', $e, array('in_reg', 'in_account', 'in_admin', 'form_types'), 0, $e['toUserTable']);
			$j ++;
		}
	}

	public function __ava__createTables($tbls){
			Создает таблицы
		*/

		foreach($tbls as $i => $e){
	}

	public function __ava__updateTables($tbls){
		/*
			Создает таблицы
		*/

		foreach($tbls as $i => $e){
			if($this->iObj->DB->issetTable($i)) $this->iObj->DB->UT(array($i, $e[0], (empty($e[1]) ? array() : $e[1])));
			else $this->iObj->DB->CT(array($i, $e[0], (empty($e[1]) ? array() : $e[1])));
		}
	}

	public function __ava__dropTables($tbls){
		/*
			Удаляет таблицы
		*/

		$this->iObj->DB->Drop($tbls);
	}
}

?>