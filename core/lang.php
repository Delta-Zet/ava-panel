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



class Lang extends ObjectInterface{
	private $phrases;
	private $defaultLang;
	private $lang = array();
	public function __construct($defaultLang){		$this->defaultLang = $defaultLang;
	}

	public function setLang($lang){		$this->lang = $lang;	}

	public function getPhrase($type, $objName, $name, $params = array()){		/*
			Возвращает фразу
		*/

		$repl1 = array();
		$repl2 = array();

		foreach($params as $i => $e){
			$repl1[$i] = '{'.$i.'}';
			$repl2[$i] = $e;
		}

		if(!isset($this->phrases[$type][$objName])) $this->load($type, $objName);
		if($repl1) return regExp::Replace($repl1, $repl2, $this->phrases[$type][$objName][$name]);
		else return $this->phrases[$type][$objName][$name];
	}

	private function load($type, $objName){
		require(_W.'languages/'.$this->defaultLang.'/'.$type.'/'.$objName.'/lang.php');		$this->phrases[$type][$objName] = $lang;

		if($this->lang && $this->defaultLang != $this->lang){			$f = _W.'languages/'.$this->lang.'/'.$type.'/'.$objName.'/lang.php';			if(file_exists($f)){				require($f);				$this->phrases[$type][$objName] = Library::array_merge($this->phrases[$type][$objName], $lang);
			}
		}
	}}

?>