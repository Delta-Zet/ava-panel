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



class API_Generate extends objectInterface{

	/*
		Генератор API-запросов
	*/

	private $request;
	private $http;
	private $result;
	private $parsedResult;
	private $errors;

	public function __ava__genAndSend($login, $pwd, $mod, $func, $login2 = '', $pwd2 = '', $params = array()){		return $this->parse($this->send($this->gen($login, $pwd, $mod, $func, $params), $login2, $pwd2));	}

	public function __ava__gen($login, $pwd, $mod, $func, $params = array()){		/*
			Генерирует запрос. Возвращает его. Заодно помещает в переменную requesta
		*/

		$this->request = XML::getXML(
			array(
				'request' => array('version' => $GLOBALS['Core']->getVersion()),
				'auth' => array('login' => $login, 'pwd' => $pwd),
				'params' => Library::array_merge(array('mod' => $mod, 'func' => $func), $params)
			)
		);

		return $this->request;	}

	public function __ava__send($url, $req, $login2 = '', $pwd2 = ''){		$this->http = new httpClient($url, 'POST');
		$this->http->setBody($req);
		if($login2 && $pwd2) $this->http->prepareHeaders(array('Authorization' => 'Basic '.base64_encode($login2.":".$pwd2)));

		$this->http->Send($timeout);
		$this->result = $this->http->getResponseBody();
		return $this->result;	}

	public function __ava__parse($result){		if(XML::isXML($result)){			$this->parsedResult = XML::parse($result);
			if($result['response']['status'] == 'true') return true;
			else{
				if(!empty($result['response']['status']) && $result['response']['status'] == 'false') $this->errors = $result['response']['result']['error'];				else $this->errors = array(array('code' => -1, 'message' => '{Call:Lang:core:core:nekorrektnyj}'));
			}
		}
		else{			$this->errors = array(array('code' => -1, 'message' => '{Call:Lang:core:core:nekorrektnyj}'));		}

		return false;
	}

	public function getRequest(){		return $this->request;	}

	public function getResult(){
		return $this->result;
	}

	public function getParsedResult(){
		return $this->parsedResult;
	}

	public function getErrors(){
		return $this->errors;
	}

	public function getHttpObj(){		return $this->http;	}
}

?>