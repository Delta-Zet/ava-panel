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



class db_main extends objectInterface{

	/*
		Структура драйвера БД сострои из 3 объектов:
			connect - соединение с БД
			request - запрос к БД
			interface - собственно сам интерфейс запросов
	*/

	public function innerWhere(&$req, $where){		/*
			Расширяет where-запрос внутри $req
		*/

		if(is_array($req)){			if(isset($req['0'])) $req['2'] = empty($req['2']) ? $where : $req['2'].' AND '.$where;			else $req['where'] = empty($req['where']) ? $where : $req['where'].' AND '.$where;		}	}
	public function Quot($str){
		return regExp::Slashes($str);
	}

	public function q($str, $params){		/*
			Отфутболивает ковычки в зопросе
		*/

		$searchs = array();
		$replaces = array();
		if(!is_array($params)) $params = array($params);

		foreach($params as $i => $e){			$searchs[] = '#'.$i;			$replaces[] = "'".self::Quot($e)."'";		}

		return regExp::replace($searchs, $replaces, $str);	}
}

?>