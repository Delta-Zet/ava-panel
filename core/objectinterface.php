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



class objectInterface extends avaStd{

	private $__flags = array();

	public final function setObjFlag($name, $value = true){
		$this->__flags[$name] = $value;
	}

	public final function getObjFlag($name){
		return isset($this->__flags[$name]) ? $this->__flags[$name] : '';
	}

	public final function rmObjFlag($name){
		unset($this->__flags[$name]);
	}

	public function innerObjParams($obj){		foreach($obj as $i => $e){			$this[$i] = $e;		}	}

	public final function clearObj(){		/*
			Убивает все объекты существующие в данном
		*/

		foreach($this as $i => $e){			if(is_object($e)) $this->$i = null;
			elseif(is_array($e)) $this->clearObjArray($this->$i);		}	}

	private function clearObjArray(&$arr){		foreach($arr as $i => $e){			if(is_object($e)) $arr[$i] = null;
			elseif(is_array($e)) $this->clearObjArray($arr[$i]);
		}	}

	public final function __reserved(){		if($this instanceof moduleinterface){			$this->refresh('', '{Call:Lang:core:core:ehtotfunktsi}');		}
		else{			throw new AVA_Reserve_Extension('{Call:Lang:core:core:ehtotfunktsi}');		}	}

	public final function __call($func, $args){
		/*
			Подменяет любой функционал
		*/

		if(empty($GLOBALS['Core']) || empty($GLOBALS['Core']->DB)) return $this->__callAVAfunc($func, $args);
		return $GLOBALS['Core']->__callNoExistFunc($this, $func, $args);
	}

	public final function __callAVAfunc($func, $args){
		if(!method_exists($this, '__ava__'.$func)) throw new AVA_Exception('{Call:Lang:core:core:popytkaobras:'.Library::serialize(array($func, get_class($this))).'}');
		else return call_user_func_array(array($this, '__ava__'.$func), $args);
	}

	public function runPlugins($point, $type = 'point', $mod = false, $func = false){
		/*
			Запускает установленные в системе плагины, которые должны отработать в определенной точке
		*/

		$return = array();
		foreach($GLOBALS['Core']->getPlugins($type, $GLOBALS['Core']->getCurrentPluginService(), $point, $mod, $func) as $i => $e){			$return[$i] = $this->evalPlugin($e);		}

		switch(count($return)){
			case 0: return 'noAnyPlugins';
			case 1: return $return[Library::firstKey($return)];
			default: return $return;
		}
	}

	public function runPluginsList($list){		$return = array();		foreach($list as $e){			$return = $this->evalPlugin($e);		}

		return $return;	}

	public function evalPlugin($name){
		/*
			Осуществляет вызов плагина
		*/

		$plugParams = $GLOBALS['Core']->getPlugin($name);
		return empty($plugParams['code']) ? false : eval($plugParams['code']);
	}
}

?>