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



class eppClient extends objectInterface{
	private $timeout;
	private $result;
	private $resultParsed;
	private $sock;

	public $errors = array();
	private $sockErr;
	private $sockErrNo;

	/*
		Функционал
	*/

	public function __ava__createContact($login, $pwd, $name, $org, $street, $city, $region, $zip, $country, $eml, $phone, $disclose = array('contact:voice' => '', 'contact:email' => '')){		/*
			Создает новый контакт
		*/

		return $this->sendReq(
			array(
				'contact:create' => array(
					'contact:id' => $login,
					'contact:postalInfo' => array(
						'contact:name' => $name,
						'contact:org' => $org,
						'contact:addr' => array(
							'contact:street' => $street,
							'contact:city' => $city,
							'contact:sp' => $region,
							'contact:pc' => $zip,
							'contact:cc' => $country,
						),
					),
					'contact:voice' => $phone,
					'contact:email' => $eml,
					'contact:authInfo' => array(
						'contact:pw' => $pwd,
					),
					'contact:disclose' => $disclose,
				),
			),
			'create',
			array(
				'contact:create' => array(
					'@attr' => array(
						'xmlns:contact' => 'urn:ietf:params:xml:ns:contact-1.0',
						'xsi:schemaLocation' => 'urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd',
					),
					'contact:postalInfo' => array(
						'@attr' => array(
							'type' => 'int'
						),
					),
					'contact:voice' => array(
						'@attr' => array(
							'x' => '1234'
						),
					),
					'contact:disclose' => array(
						'@attr' => array(
							'flag' => '0'
						),
					),
				),
			)
		);
	}

	public function __ava__regDomain($name, $term, $o, $a, $b, $t, $pwd, $ns = array()){
		/*
			Создает новый контакт
		*/

		foreach($ns as $i => $e){			if(!$e) unset($ns[$i]);		}

		return $this->sendReq(
			array(
				'domain:create' => array(
					'domain:name' => $name,
					'domain:period' => $term,
					'domain:ns' => $ns,
					'domain:registrant' => $o,
					'domain:contact' => array($a, $b, $t),
					'domain:authInfo' => array('domain:pw' => $pwd)
				),
			),
			'create',
			array(
				'domain:create' => array(
					'@attr' => array(
						'xmlns:domain' => 'urn:ietf:params:xml:ns:domain-1.0',
						'xsi:schemaLocation' => 'urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd',
					),
					'domain:period' => array('@attr' => array('unit' => 'y')),
					'domain:contact' => array(
						array('@attr' => array('type' => 'admin')),
						array('@attr' => array('type' => 'billing')),
						array('@attr' => array('type' => 'tech')),
					)
				),
			)
		);
	}



	/*
		Служебные
	*/

	public function __ava__Connect($host, $login, $pwd, $port = 700, $secure = true, $timeout = 15){		/*
			Соединяется с сервантом
		*/
		$this->timeout = $timeout;
		if($secure) $host = 'ssl://'.$host;
		$this->sock = new Socket;

		if(!$this->sock->Connect($host, $port, $timeout, $this->sockErr, $this->sockErrNo)){			$this->setError(1);
			return false;		}

		if(!$this->login($login, $pwd)){
			$this->setError(10);			return false;		}
		return $this->sock;
	}

	public function __ava__login($login, $pwd){		/*
			Опровляет интифигационные данные
		*/

		if(!$this->sendReq(array('hello' => ''), '', array(), true)) return false;

		return $this->sendReq(
			array(
				'clID' => $login,
				'pw' => $pwd,
				'options' => array(
					'version' => '1.0',
					'lang' => 'en',
				),
				'svcs' => array(
					'objURI' => array(
						'urn:ietf:params:xml:ns:obj1',
						'urn:ietf:params:xml:ns:obj2',
						'urn:ietf:params:xml:ns:obj3',
					)
				),
			),
			'login',
			array(),
			true
		);	}

	public function __ava__sendReq($data, $command = '', $attr = array(), $isLogin = false){		/*
			Отправляет запрос на сервант
		*/

		if($command){			$data = array('command' => array($command => $data));
			$attr = array('command' => array($command => $attr));
		}

		$data = XML::getXML(
			array('epp' => $data),
			array('version' => '1.0', 'encoding' => 'UTF-8', 'standalone' => 'no'),
			Library::array_merge(array('epp' => array('@attr' => array('xmlns' => 'urn:ietf:params:xml:ns:epp-1.0', 'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation' => 'urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd'))), array('epp' => $attr))
		);

		$this->sock->Put(Library::num2hex(regExp::bLen($data) + 4, 4).$data);
		$result = regExp::win($this->sock->read(Library::hex2num($this->sock->read(4)) - 4));

		if(defined('SHOW_HTTP_REQS') && SHOW_HTTP_REQS > 0) $this->result .= $data."\n\n\n\n\n\n\n\n\n\n\n\n";
		$this->result .= $result."\n\n\n\n\n\n\n\n\n\n\n\n";
		$return = $this->parse($result);

		if($isLogin && !empty($return[0]['epp']['greeting'])) return true;
		elseif(isset($return[1]['epp']['response']['result']['@attr']['code'])){			if($return[1]['epp']['response']['result']['@attr']['code'] >= 1000 && $return[1]['epp']['response']['result']['@attr']['code'] < 2000){				$this->setError(0, $return[1]['epp']['response']['result']['@attr']['code'].':'.$return[0]['epp']['response']['result']['msg']);
				return true;			}
			elseif($return[1]['epp']['response']['result']['@attr']['code'] >= 2000){				$this->setError($return[1]['epp']['response']['result']['@attr']['code'], $return[0]['epp']['response']['result']['msg']);
				return false;
			}
		}

		$this->setError(7);
		return false;
	}

	private function parse($xml){		/*
			Разбирает ответ EPP-сервера
		*/
		if($return = XML::parseXML($xml, $attr)){			$this->resultParsed[] = $return;
			return array($return, $attr);
		}

		$this->setError(7);
		return false;
	}

	private function setError($code, $descript = ''){		/*
			Устанавливает ошибки
		*/

		if(!$descript){
			switch($code){
				case 1: $descript = '{Call:Lang:modules:bill_domains:oshibkasoedi:'.Library::serialize(array($this->sockErrNo, $this->sockErr)).'}'; break;
				case 2: $descript = '{Call:Lang:modules:bill_domains:neopredelenn}'; break;
				case 4: $descript = '{Call:Lang:modules:bill_domains:poukazannomu}'; break;
				case 5: $descript = '{Call:Lang:modules:bill_domains:oshibkaudale}'; break;
				case 6: $descript = '{Call:Lang:modules:bill_domains:nekorrektnyj}'; break;
				case 7: $descript = '{Call:Lang:modules:bill_domains:nekorrektnyj1}'; break;
				case 8: $descript = '{Call:Lang:modules:bill_domains:poterianatra}'; break;
				case 10: $descript = '{Call:Lang:modules:bill_domains:nepravilnyel}'; break;
			}
		}
		$this->errors[] = array(
			'code' => $code,
			'msg' => $descript
		);	}

	public function getErrors(){
		return $this->errors;
	}

	public function getResult(){		return $this->result;	}
}

?>