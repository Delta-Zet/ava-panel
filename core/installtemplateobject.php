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



class InstallTemplateObject extends InstallObject{
	public $folder;
	public $name;

	public final function __construct($DB, $obj, $name, $folder, $params = array()){		$this->DB = $DB;
		$this->obj = $obj;
		$this->folder = $folder;
		$this->name = $name;
		$this->params = $params;
	}

	public function __ava__setAllDefaults($type, $params){
		/*
			Устанавливает значения по умолчанию
		*/

		if(method_exists($this, 'getTemplateBlocks')) $this->setTemplateBlocks($this->getTemplateBlocks($params), $type);
	}

	public function __ava__setTemplateBlocks($blocks, $type){		$j = $this->DB->cellFetch(array('template_blocks', 'sort', "", "`sort` DESC")) + 1;
		foreach($blocks as $i => $e){
			$e['sort'] = $j;			$e['show'] = 1;
			$e['name'] = $i;

			$e['template'] = $this->folder;
			$e['text'] = $this->paramReplaces($e['text']);
			$this->DB->$type(array('template_blocks', $e));

			$j ++;		}	}

	public function __ava__getTemplatePagesByType($type){		$return = array();
		if(isset($this->params['pages']) && is_array($this->params['pages'])){
			foreach($this->params['pages'] as $e){				if($type == $e['type']) $return[] = $e['url'];			}
		}

		return $return;	}
}

?>