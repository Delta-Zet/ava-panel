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


class bbCode extends regExp{
	public static function decode($str){
		/*
			Преобразует текст в bbCode в нормальный html
		*/

		$str = regExp::replace("|\[(/?)([bui])\]|i", '<$1$2>', $str, true);
		$str = regExp::replace("|\[color=(#[0-9a-f]{3,6})\]|i", '<span style="color: $1">', $str, true);
		$str = regExp::replace("|\[/color\]|i", '</span>', $str, true);

		$str = regExp::Replace("|\w+://\S+|", '<a href="$0">$0</a>', $str, true);
		$str = regExp::Replace("|\S+@\S+|", '<a href="mailto:$0">$0</a>', $str, true);
		return $str;
	}
}

?>