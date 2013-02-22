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



class avaStd{

	public final function getVar($var){
		if(empty($this->$var)) return '';
		return $this->$var;
	}

	public final function getInd($arr, $ind){
		if(empty($this->$arr[$ind])) return '';
		return $this->$arr[$ind];
	}

	public final function setVar($var, $val){
		$this->$var = $val;
	}

	public final function setInd($arr, $var, $val){
		$this->$arr[$var] = $val;
	}

	public final function clearVar($var){
		if(is_array($this->$var)) $this->$var = array();
		else $this->$var = NULL;
	}

	public static final function get($var){
		return self::$var;
	}

	public static final function getI($arr, $var){
		return self::$arr[$var];
	}

	public static final function set($var, $val){
		return self::$var = $val;
	}

	public static final function setI($arr, $var, $val){
		return self::$arr[$var] = $val;
	}

	public final function getFuncs(){
		return get_class_methods($this);
	}

	public final function append($var, $params){		$this->$var = Library::array_merge($this->$var, $params);
	}
}

?>