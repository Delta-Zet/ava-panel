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


class XML extends objectInterface{
	/*
		Разбор XML-документов

		Разбирает весь XML-ввод. Возвращает его в виде массива, где имя тега становится именем элемента массива, а значение - содержанием, при этом значение разбирается
		по тем же правилам. Если несколько дочерних элементов имеют одинаковое имя они объединяются в цифровой массив

		<data>
			<t1>
				<t2><t3>someValue</t3></t2>
			</t1>
		</data>
		Будет $return['data']['t1']['t2']['t3'] = 'someValue'

		<data>
			<t1>
				<t2><t3>someValue</t3></t2>
			</t1>
			<t1>
				<t2><t3>OtherSomeValue</t3></t2>
			</t1>
		</data>
		Будет
			$return['data']['t1']['0']['t2']['t3'] = 'someValue'
			$return['data']['t1']['1']['t2']['t3'] = 'OtherSomeValue'

	*/

	/*
		Функции для построения XML
	*/

	public function getXml($xml, $headers = false, $attr = array(), $toStd = true){
		/*
			Строит XML из массива
		*/

		if($toStd) $xml = self::toStandart($xml);
		$return = ($headers === false ? '' : self::getXMLHead($headers)."\n").self::getXmlBlock($xml, $attr);
		if(!empty($headers['encoding'])) $return = regExp::charset('UTF-8', $headers['encoding'], $return);
		return $return;
	}

	public function getFullXML($xml, $attr = array()){
		return self::getXml($xml, array('version' => '1.0', 'encoding' => 'UTF-8'), $attr);
	}

	public function getXmlBlock($xml, $attr, $start = ''){
		/*
			Строит блок XML-данных
		*/

		$return = '';
		foreach($xml as $i => $e){
			if(Library::isHash($e)) $e = "\n".self::getXmlBlock($e, empty($attr[$i]) ? array() : $attr[$i], $start."\t").$start;
			elseif(is_array($e)){
				foreach($e as $i1 => $e1){
					if(Library::isHash($e1)) $e1 = "\n".self::getXmlBlock($e1, empty($attr[$i][$i1]) ? array() : $attr[$i][$i1], $start."\t").$start;
					$return .= $start.'<'.$i.(empty($attr[$i][$i1]['@attr']) ? '' : self::getXMLAttr($attr[$i][$i1]['@attr'])).">".$e1."</".$i.">\n";
				}
				continue;
			}

			if($e === '') $return .= $start.'<'.$i.(empty($attr[$i]['@attr']) ? '' : self::getXMLAttr($attr[$i]['@attr']))."/>\n";
			else $return .= $start.'<'.$i.(empty($attr[$i]['@attr']) ? '' : self::getXMLAttr($attr[$i]['@attr'])).">".$e."</".$i.">\n";
		}

		return $return;
	}

	public function getXMLHead($headers){
		/*
			Строит XML-шапку
		*/

		return '<'.'?xml'.self::getXMLAttr($headers).' ?'.'>';
	}

	public function getXMLAttr($attr){
		/*
			Строит строку XML-аттрибутов
		*/

		if(!is_array($attr)) throw new AVA_XML_Exception('{Call:Lang:core:core:nepravilnyjf}');

		$return = '';
		foreach($attr as $i => $e){
			$return .= ' '.$i.'="'.$e.'"';
		}

		return $return;
	}



	/*
		Функции для разбора XML
	*/

	public function parseXML($xml, &$attr = array(), &$headers = false, $block = ''){
		/*
			Разбирает весь XML-ввод, включая шапко
			Если специфицирован $block, будет возвращено его значение
		*/

		$xml = trim($xml);

		if(regExp::match("|^<\?xml([^>\?]+)\?".">|is", $xml, true, true, $m)){
			$headers = self::parseXMLAttr($m['1']);
			if(!empty($headers['encoding'])) $xml = regExp::charset($headers['encoding'], 'UTF-8', $xml);
			$xml = self::trimXMLHead($xml);
		}

		list($xml, $cData) = self::stripCData($xml);
		$xmlp = xml_parser_create('UTF-8');
		if(!@xml_parse($xmlp, '<env>'.$xml.'</env>', true)) return false;
		$return = self::addCData(self::parseXMLBlock($xml, $attr), $cData);

		return $block ? $return[$block] : $return;
	}

	public function stripCData($xml){
		$m = regExp::MatchAll("|<\!\[CDATA\[(.+)\]\]>|iUs", $xml);
		$cData = array();

		foreach($m[0] as $i => $e){
			$cData[$i] = $m[1][$i];
			$xml = regExp::Replace($e, "~=~={$i}=~=~", $xml);
		}

		return array($xml, $cData);
	}

	public function addCData($xmlArr, $cData){
		if(!$cData) return $xmlArr;

		$search = $replace = array();
		foreach($cData as $i => $e){
			$search[] = "~=~={$i}=~=~";
			$replace[] = $e;
		}

		return regExp::replace($search, $replace, $xmlArr);
	}

	public function parseXMLBlock($xml, &$attr, $block = false){
		/*
			Возвращает все дочерние блоки XML
		*/

		$return = array();

		while(($xml = trim($xml)) && ($block = self::getFirstBlock($xml))){
			$xml = regExp::subStr($xml, regExp::len($block));
			$l = self::getXMLLayer($block);

			$attr2 = array();
			if(self::isXML($l['text'])) $l['text'] = self::parseXMLBlock($l['text'], $attr2);
			$attr2['@attr'] = $l['attr'];

			if(!isset($return[$l['name']])){
				$return[$l['name']] = $l['text'];
				$attr[$l['name']] = $attr2;
			}
			elseif(is_array($return[$l['name']]) && !Library::isHash($return[$l['name']])){
				$return[$l['name']][] = $l['text'];
				$attr[$l['name']][] = $attr2;
			}
			else{
				$return[$l['name']] = array($return[$l['name']], $l['text']);
				$attr[$l['name']] = array($attr[$l['name']], $attr2);
			}
		}

		return $return;
	}

	public function getFirstBlock($xml){
		/*
			Возвращает первый блок данных XML
		*/

		$xml = trim($xml);
		if(!$xml) return '';																	//Если XML пустой
		elseif(regExp::Match("|^<[^>]+/>|iUs", $xml, true, true, $m)) return $m['0'];			//Если блок вида <name/>

		regExp::Match("/^<([\w\-\.:]+)[\s>]/iUs", $xml, true, true, $m);
		if(empty($m['1'])) throw new AVA_XML_Exception('{Call:Lang:core:core:nekorrektnyj3:'.Library::serialize(array('[nocall]'.regExp::html($xml).'[/nocall]')).'}');
		return self::getXMLRemain($xml, $m['1']);
	}

	private function getXMLRemain($xml, $name){
		/*
			Возвращает остаточный кусок XML вплоть до </$name> включительно
		*/

		$return = '';
		while(!$return || (regExp::matchCount("|<".$name."[\s>]|iUs", $return) != regExp::matchCount("|</".$name."[\s>]|iUs", $return))){
			if(!regExp::Match("|^(\s*<.+</".$name."[\s>][^>]*>?)(.*)$|iUs", $xml, true, true, $m)){
				throw new AVA_XML_Exception('{Call:Lang:core:core:oshibkarazbo:'.Library::serialize(array($name, '[nocall]'.regExp::html(substr($xml, 0, 65535)).'[/nocall]')).'}');
			}

			$return .= $m[1];
			$xml = $m[2];
		}

		return $return;
	}

	public function getXMLLayer($xml){
		/*
			Считывает данные из слоя XML, т.е. из блока обозначенного тегами.
			Возвращает:
				name - пограничные теги
				attributes - данные записанные как атрибуты внутри тега name
				value - все что внутри, в т.ч. и вложенные теги
		*/

		$xml = trim($xml);

		if(regExp::Match("/^<([^\s>]+)([^>]*)\/>$/is", $xml, true, true, $m)){
			$m['3'] = '';
			$m['4'] = $m['1'];
		}
		elseif(!regExp::Match("/^<([^\s>]+)([^>]*)>(.*)<\/([^\s>]+)([^>]*)>$/is", $xml, true, true, $m)){
			throw new AVA_XML_Exception('{Call:Lang:core:core:oshibkarazbo1:'.Library::serialize(array('[nocall]'.regExp::html($xml).'[/nocall]')).'}');
		}

		if($m['1'] != $m['4']){
			throw new AVA_XML_Exception('{Call:Lang:core:core:oshibkarazbo2:'.Library::serialize(array($m['1'], $m['4'], '[nocall]'.regExp::html($xml).'[/nocall]')).'}');
		}

		return array('name' => $m['1'], 'attr' => self::parseXMLAttr($m['2']), 'text' => $m['3']);
	}

	public function trimXMLHead($data){
		/*
			Вырезает шапку XML-данных
		*/

		return regExp::Replace("/^\s*<\?xml([^>]+)\?".">/is", "", $data, true);
	}

	public function parseXMLAttr($str){
		/*
			Разлагает строку на атрибуты
		*/

		$return = array();
		$att1 = regExp::MatchAll('/\s+([\w\-:]+)=["]([^"]+)["]/iUs', $str, false);
		foreach($att1['1'] as $i => $e){
			$return[$e] = $att1['2'][$i];
			$str = regExp::replace($e.'="'.$att1['2'][$i].'"', '', $str);
		}

		$att2 = regExp::MatchAll("/\s+([\w\-:]+)=[']([^']+)[']/iUs", $str, false);
		foreach($att2['1'] as $i => $e){
			$return[$e] = $att2['2'][$i];
			$str = regExp::replace("$e='{$att2['2'][$i]}'", '', $str);
		}

		$att3 = regExp::MatchAll('/\s+([\w\-:]+)\s+/is', $str, false);
		foreach($att3['0'] as $e){
			$return[$e] = '';
		}

		return $return;
	}


	/*
		Служебные
	*/

	public function toStandart($xml){
		if(is_array($xml)){
			foreach($xml as $i => $e){
				$xml[$i] = self::toStandart($e);
			}
		}
		else{
			$xml = regExp::replace(array('<', '>', '&'), array('&lt;', '&gt;', '&amp;'), $xml);
		}

		return $xml;
	}

	public function isXML($xml){
		/*
			Проверяет что $xml содержит теги
		*/

		return regExp::Match("<", $xml);
	}
}

?>