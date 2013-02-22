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



class APIinterface extends objectInterface{

	/*
		Интерфейс для объектов API каждого модуля
	*/
	public $API;
	public $values;

	protected $type;
	protected $mod;
	protected $func;

	public function __construct($API, $type, $mod, $func, $params){		$this->API = $API;
		$this->type = $type;
		$this->mod = $mod;
		$this->func = $func;
		$this->values = $params;
	}

	public function callFunc(){		if(!method_exists($this, $this->func)){			$this->API->setError(11, '{Call:Lang:core:core:nesushchestv:'.Library::serialize(array($this->func)).'}');
			return false;		}

		$f = $this->func;
		return $this->$f();	}
}

?>