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



class whois extends objectInterface {
	public $DB;
	public $serverParams = array();
	private $domain;		//Домен в оригинале
	private $domainWOZone;

	private $domainZone;	//Доменная зона (в оригинале)
	private $IDNdomain;		//Домен в IDN
	private $result;
	private $resultStatus;	//Результат. 0 - свободен, 1 - занят, 2 - whois недоступен, 3 - недопустимый домен

	public function __construct($domain, $DB){		$this->domain = db_main::Quot($domain);
		$this->DB = $DB;
		$this->domainZone = gen_bill_domains::getDomainZone($this->domain, $this->domainWOZone);

		$this->IDNdomain = $this->getIDNDomain($this->domain);
		$this->serverParams = $this->DB->rowFetch(array('whois_servers', '*', "`zone`='{$this->domainZone}'"));
		$this->serverParams['port'] = empty($this->serverParams['port']) ? 43 : $this->serverParams['port'];
	}

	public function __ava__getZone(){		return $this->domainZone;	}

	public function getIDNDomain($domain){		/*
			Возвращает домен в IDN
		*/

		$GLOBALS['Core']->loadExtension('bill_domains', 'idna');
		$punyObj = new idna();
		$return = array();

		foreach(regExp::split('.', $domain) as $e){			$return[] = $punyObj->encode($e);		}

		return implode('.', $return);
	}

	public function __ava__send(){		/*
			Отправляет запрос к Whois-серверу
		*/

		if(!$this->domainIsAvailable()){			$this->resultStatus = 3;
		}
		elseif(empty($this->serverParams['host'])){
			$this->resultStatus = 2;
		}
		else{
			$socket = new Socket;
			$socket->Connect($this->serverParams['host'], $this->serverParams['port']);
			$socket->Put($this->IDNdomain."\015\012");
			$this->result = $socket->Read();

			if(!$this->result) $this->resultStatus = 2;
			else $this->resultStatus = $this->check($this->result);
		}

		return $this->result;	}

	public function __ava__domainIsAvailable(){		/*
			Проверяет что такой домен вообще реален
		*/

		if(!$this->domainZone || !regExp::Match("|^[A-zА-яЁё0-9][A-zА-яЁё0-9\-]*$|", $this->domainWOZone, true)) return false;
		elseif(regExp::Match("/[А-яЁё]/", $this->domainZone, true) && regExp::Match("/[A-z]/", $this->domain, true)) return false;
		elseif(
			!regExp::Match("/[А-яЁё]/", $this->domainZone, true) &&
			($this->domainZone != 'su') &&
			($this->domainZone != 'net') &&
			($this->domainZone != 'com') &&
			($this->domainZone != 'name') &&
			($this->domainZone != 'tel') &&
			($this->domainZone != 'tv') &&
			($this->domainZone != 'ws') &&
			($this->domainZone != 'cc') &&
			regExp::Match("/[А-яЁё]/", $this->domainWOZone, true)
		) return false;
		elseif($this->domainZone != 'su' && regExp::bLen($this->domainWOZone) < 3) return false;
		elseif(regExp::Match("/[А-яЁё]/", $this->domainWOZone, true) && regExp::Match("/[A-z]/", $this->domainWOZone, true)) return false;
		elseif(regExp::Match("/[^А-яЁёA-z0-9\-\.]/", $this->domain, true)) return false;

		return true;
	}

	public function __ava__getResult(){		/*
			Возвращает результат Whois-проверки
		*/

		return $this->result;	}

	public function __ava__getResultStatus(){		return $this->resultStatus;	}

	public function __ava__check($text){		/*
			Проверяет домен на занятость в соответствии с текущими правилами
			Возвращает true если домен не существует и false в ином случае
		*/

		if($text === false) $text = $this->result;
		$isMatched = regExp::Match($this->serverParams['pattern'], $text, (bool)regExp::Match("|^[\|/#]|", $this->serverParams['pattern'], true));
		if(($isMatched && !$this->serverParams['inverse']) || (!$isMatched && $this->serverParams['inverse'])) return 0;		else return 1;	}}


?>