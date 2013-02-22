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


class installTemplatesMainDefault extends InstallTemplateObject implements InstallTemplateInterface{

	public function Install(){
		$this->setAllDefaults('Ins', $this->obj->values);
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

	public function Update(){
		$this->setAllDefaults('Ins', $this->obj->values);
		return true;
	}

	public function checkUpdate(){
		return true;
	}

	public function getTemplateBlocks($params){
		return array(
			'mainmenu' => array('body' => '{Call:ModuleCall:'.$this->obj->values['union_mod_cms_MainDefault'].':structure:body:name=mainmenu}', 'text' => '{Call:Lang:templates:main/default:glavnoemeniu}'),
			'sidemenu' => array('body' => '{Call:ModuleCall:'.$this->obj->values['union_mod_cms_MainDefault'].':structure:body:name=menu1}', 'text' => '{Call:Lang:templates:main/default:pravoemeniu}'),
			'usermenu' => array('body' => '{Call:ModuleCall:'.$this->obj->values['union_mod_cms_MainDefault'].':structure:body:name=usermenu}', 'text' => '{Call:Lang:templates:main/default:polzovatelsk}'),
			'news' => array('body' => '{Call:ModuleCall:'.$this->obj->values['union_mod_cms_MainDefault'].':structure:body:name=news&sort='.urlencode('`date` DESC').'&limit=5}', 'text' => '{Call:Lang:templates:main/default:novosti}'),
			'domains' => array('body' => '{Call:ModuleCall:'.$this->obj->values['union_mod_bill_domains_MainDefault'].':getDomainsForm:body:service=}', 'text' => '{Call:Lang:templates:main/default:formapoiskad}')
		);
	}

	public function prepareInstall(){
		if(empty($this->obj->values['installMainDefaultStep'])){
			$this->obj->values['installMainDefaultStep'] = 1;

			if(!$cms = $this->obj->Core->DB->columnFetch(array('modules', 'text', 'url', "`name`='cms'"))){
				$this->obj->setError('name', '{Call:Lang:templates:main/default:dliarabotyeh}');
			}

			$js = '';
			$values = array();

			$matrix['capt_MainDefault']['type'] = 'caption';
			$matrix['capt_MainDefault']['text'] = '{Call:Lang:templates:main/default:modulisviaza}';

			$matrix['union_mod_cms_MainDefault']['type'] = 'select';
			$matrix['union_mod_cms_MainDefault']['text'] = '{Call:Lang:templates:main/default:ukazhitemodu}';
			$matrix['union_mod_cms_MainDefault']['warn'] = '{Call:Lang:templates:main/default:neukazanmodu}';
			$matrix['union_mod_cms_MainDefault']['additional'] = $this->obj->Core->DB->columnFetch(array('modules', 'text', 'url', "`name`='cms'"));

			$matrix['union_mod_bill_domains_MainDefault']['type'] = 'select';
			$matrix['union_mod_bill_domains_MainDefault']['text'] = '{Call:Lang:templates:main/default:ukazhitemodu1}';
			$matrix['union_mod_bill_domains_MainDefault']['additional'] = Library::array_merge(
				array('' => '{Call:Lang:templates:main/default:net}'),
				$this->obj->Core->DB->columnFetch(array('modules', 'text', 'url', "`name`='bill_domains'"))
			);

			$this->obj->setContent(
				$this->obj->getFormText(
					$this->obj->addFormBlock(
						$this->obj->newForm(
							'installTemplate',
							'pkgInstallEnd',
							array('caption' => '{Call:Lang:templates:main/default:nastrojkisha}')
						),
						$matrix
					),
					$values,
					$this->obj->values,
					'big'
				)
			);
		}
		elseif($this->obj->values['installMainDefaultStep'] == 1){
			if($this->obj->check()) return true;
		}

		return false;
	}
}


?>