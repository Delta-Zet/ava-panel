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



class checkFunctions extends objectInterface {
	public function newpwd($str, $var, $obj){		if(empty($obj->values['modify']) && !$str) $obj->setError('pwd', '{Call:Lang:core:core:vyobiazanyuk}');		elseif(($str || !empty($obj->values['cpwd'])) && $obj->values['pwd'] != $obj->values['cpwd']) $obj->setError('pwd', '{Call:Lang:core:core:parolipodtve}');		elseif(!regExp::pwd($str)) $obj->setError('pwd', '{Call:Lang:core:core:parolsoderzh}');
		elseif(strlen($str) < 7) $obj->setError('pwd', '{Call:Lang:core:core:paroldolzhen}');
		return true;
	}
	public function captcha($str, $var, $obj){		/*
			Проверка капчи
		*/

		if(!captcha::checkCaptcha($obj->values[$var.'_id'], $str)) $obj->setError($var, '{Call:Lang:core:core:nepravilnovv}');
		return true;
	}
}

?>