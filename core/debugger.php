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



class Debugger extends ModuleInterface{

	private $start;
	private $steps = array();
	private $intervals = array();
	private $cnt = 0;
	public $params;

	public function __construct(){		$this->start = time() + microtime();
		$this->steps[$this->cnt] = array(
			'time' => $this->start,
			'action' => 'Начало работы'
		);
	}

	public function setAction($action){		$action = $this->stripAction($action);		$this->cnt ++;
		$this->steps[$this->cnt] = array(
			'time' => time() + microtime(),
			'action' => $action
		);

		return $this->cnt;
	}

	public function setInterval($action, $start, $end = false){		$action = $this->stripAction($action);
		if($end === false) $end = $this->setAction($action);
		$this->intervals[] = array(
			'action' => $action,
			'start' => $this->steps[$start]['time'],
			'end' => $this->steps[$end]['time'],
			'length' => $this->steps[$end]['time'] - $this->steps[$start]['time'],
			'startId' => $start,
			'endId' => $end,
		);
	}

	public function __ava__close(){		$end = time() + microtime();
		$this->cnt ++;

		$this->steps[$this->cnt] = array(
			'time' => $end,
			'action' => 'Окончание работы'
		);

		$this->intervals[] = array(
			'action' => 'Общая продолжительность работы',
			'start' => $this->start,
			'end' => $end,
			'length' => $end - $this->start,
			'startId' => 0,
			'endId' => $this->cnt,
		);

		$this->params['steps'] = $this->steps;
		$this->params['intervals'] = $this->intervals;
		return $GLOBALS['Core']->prepareFinalTmplText($GLOBALS['Core']->replace($GLOBALS['Core']->getTemplatePage('debug', false, 'system'), $this, $this->params));	}

	private function stripAction($str){		return '[nocall]'.regExp::html(regExp::substr($GLOBALS['Core']->getPhrase($str), 0, 1024)).'[/nocall]';	}
}

?>