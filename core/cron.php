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



class Cron extends moduleInterface{
	public $Core;
	private $time;
	private $tasks = array();

	public function __construct(){		/*
			Конструктор для крона
			Задания запукаются 2 типов.
				1 - собственно регулярно запускаемые задачи
				2 - задача поставленная на выплнение в очередь прямо сейчас
		*/
		$this->Core = $GLOBALS['Core'];
		$this->time = time();
	}

	public function runCron(){
		/*
			Выбираются все задачи которых нет в очереди на выполнение, находится их ближайший момент выполнения и задача помещается в очередь
		*/

		$this->Core->DB->trStart();
		$cmds = $this->Core->DB->columnFetch(array('cron', '*', 'id', "`show` AND `limit`<=".$this->time." AND ".$this->getEntriesWhere($this->Core->DB->columnFetch(array('tasks', "id", "name", "(`status`=0 OR `status`=1) AND `name`!=''")), 'name', '', '!=', 'AND')));

		foreach($cmds as $i => $e){
			if($this->canRun($e['month'], $e['day'], $e['week'], $e['hour'], $e['minute'], $e['tick'], $e['last_work'])) $this->addTask($e['command'], $e['comment'], $this->time, $e['name']);		}
		$this->Core->DB->trEnd(true);
	}

	public function canRun($m, $d, $w, $h, $i, $a, $last){
		/*
			Проверяет
				- Что текущий месяц, день месяца, день недели и час совпадают с пригодными для запуска
				- Выявляет последнюю ближайшую минуту когда могло произойти выполнение
				- Что последний момент, когда скрипт мог быть выполнен позднее чем last

			При проверки частоты запусков в пределах часа:
				- Получаем минутный список
				- Расширяем его за счет блоков - один из предыдцщего часа, один - из следующего
				- Проводим интерацию. Выявляем что текущая минута >= предыдущей
		*/

		if($a) return true;

		if(!$this->inThisPeriod($cm = Dates::date('n', $this->time), $m)) return false;
		if(!$this->inThisPeriod($cw = Dates::date('w', $this->time), $w)) return false;
		if(!$this->inThisPeriod($cd = Dates::date('j', $this->time), $d)) return false;
		if(!$this->inThisPeriod($ch = Dates::date('G', $this->time), $h)) return false;

		$ci = Dates::date('i', $this->time);
		if(is_array($periods = $this->getPeriods($i))){			array_unshift($periods, $periods[Library::lastKey($periods)] - 60);
			$periods[] = $periods[1] + 60;
			foreach($periods as $i => $e) if($ci < $e){ $ci = $periods[$i - 1]; break; }		}

		if(Dates::mkTime($ch, $ci, 0, $cm, $cd, Dates::date('Y', $this->time)) <= $last) return false;

		return true;
	}

	public function inThisPeriod($cur, $str){		/*
			Проверяет что $cur находится в пределах текущего периода
		*/

		if(($periods = $this->getPeriods($str)) === true) return true;
		return in_array($cur, $periods);
	}

	public function getPeriods($str){
		/*
			Парсит строку. Возвращает список периодов
		*/

		$str = trim($str);
		if($str == '*') return true;

		$values = regExp::Split(',', $str);
		foreach($values as $i => $e){
			if(regExp::Match('-', $e)){
				$e = regExp::Split('-', $e);
				for($y = $e['0']; $y <= $e['1']; $y ++) $values[] = $y;
				unset($values[$i]);
			}
		}

		$values = array_unique($values);
		sort($values);
		return $values;
	}

	public function addTask($cmd, $comment, $add = false, $name = ''){
		/*
			Добавляет задание в очередь текущих заданий
			Может вызываться через ::
		*/

		$GLOBALS['Core']->DB->Ins(array('tasks', array('name' => $name, 'added' => $add ? $add : time(), 'command' => $cmd, 'comment' => $comment)));
	}

	public function runTasks(){
		/*
			Запускает собственно задания стоящие в очереди
		*/

		$this->tasks = $this->Core->DB->columnFetch(array('tasks', '*', 'id', "`status`=0", "`id`", $this->Core->getParam('cronTasksLimit')));
		foreach($this->tasks as $i => $e) if($e['name']) $this->Core->DB->Upd(array('cron', array('last_work' => $this->time), "`name`='{$e['name']}'"));
		foreach($this->tasks as $i => $e) $this->runTask($i);	}

	private function runTask($tId){		/*
			Пережает задание на выполнение
		*/

		if(function_exists('pcntl_fork') && (($pid = pcntl_fork()) != -1)){			if($pid) $this->execute($tId);		}
		else $this->execute($tId);	}

	private function execute($tId){		/*
			Выполняет задание
		*/

		if(empty($this->tasks[$tId])) throw new AVA_Exception('Неопределенное задание');
		$this->Core->DB->Upd(array('tasks', array('status' => 1, 'runned' => time()), "`id`='$tId'"));
		ob_start();

		$fields['result'] = eval($this->tasks[$tId]['command']);
		$fields['result_text'] = ob_get_contents();
		ob_end_clean();

		$fields['execute'] = time();
		$fields['status'] = 2;
		$this->Core->DB->Upd(array('tasks', $fields, "`id`='$tId'"));
	}

	public function dieOldTasks(){
		/*
			"Зависшие" задания завершает по timeout
		*/

		$this->Core->DB->Upd(array('tasks', array('status' => -1), "`runned`<".(time() - $this->Core->getParam('cronTaskQueueTimeout'))." AND `status`=1"));
	}
}

?>