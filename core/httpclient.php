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


class httpClient extends objectInterface {

	/*
		Устанавливает соединение по протоколам HTTP и HTTPS. Должна быть поддержка соединения через fsockopen, curl и сокеты
	*/

	private $url = array();
	private $host;
	private $method;
	private $httpVer;
	private $secure = false;

	private $reqHead;
	private $reqBody;
	private $boundary;
	private $request;

	//Ответ
	private $response;
	private $responseCode;
	private $responseStatus;
	private $charset;
	private $cookie = array();
	private $cookieValues = array();
	private $contentType;
	private $responseHead;
	private $responseBody;
	private $responseHeadAsArray = array();
	private $errno;
	private $errstr;

	//Служебные
	private $continued = 0;


	public function __construct($url, $method = 'GET', $httpVer = '1.0'){
		/*
			Принимает и парсит URL, устанавливает другие параметры
		*/

		$this->method = strtoupper($method);
		$this->httpVer = $httpVer;
		if($this->method != 'POST') $this->method = 'GET';
		$this->url($url);
	}

	public function __ava__url($url){
		/*
			Устанавливает URL для отправки запроса
		*/

		$this->url = Library::array_merge($this->url, parse_url($url));
		$this->host = $this->url['host'];

		if(empty($this->url['scheme'])) $this->url['scheme'] = 'http';
		elseif($this->url['scheme'] == 'https'){
			$this->secure = true;
			$this->host = 'ssl://'.$this->host;
		}
		else $this->secure = false;

		if(empty($this->url['path'])) $this->url['path'] = '/';
		if(!empty($this->url['query'])) $this->url['path'] .= '?'.$this->url['query'];

		if(empty($this->url['port'])){
			if($this->secure) $this->url['port'] = 443;
			else $this->url['port'] = 80;
		}
	}

	public function __ava__prepareHeaders($additHeaders = array(), $auths = array(), $cookie = array()){
		/*
			Подготавливает шапку запроса на основании текущих установок
		*/

		$headers = array(
			'Date' => date('r'),
			'Connection' => 'close',
			'Host' => $this->url['host']
		);

		if($this->method == 'POST'){
			if($this->boundary) $headers['Content-type'] = 'multipart/form-data; boundary='.$this->boundary;
			else $headers['Content-type'] = 'application/x-www-form-urlencoded';
			$headers['Content-length'] = regExp::bLen($this->reqBody);
		}

		$n = "\r\n";
		$this->reqHead .= "{$this->method} {$this->url['path']} HTTP/{$this->httpVer}".$n;
		if(is_array($additHeaders)) $headers = Library::array_merge($headers, $additHeaders);

		if($auths){
			switch($auths[0]){
				case 'Basic':
					$headers['Authorization'] = 'Basic '.base64_encode($auths[1].":".$auths[2]);
					break;

				case 'Digest':
					break;

				default:
					throw new AVA_Exception('{Call:Lang:core:core:nekorrektnyj1:'.Library::serialize(array($auths[0])).'}');
			}
		}

		if($cookie){
			foreach($cookie as $i => $e){
				$cookie[$i] = "$i=$e";
			}
			$headers['Cookie'] = trim(implode('; ', $cookie));
		}

		foreach($headers as $i => $e){
			$this->reqHead .= "$i: $e".$n;
		}
		if(!is_array($additHeaders)) $this->reqHead .= $additHeaders;
	}

	public function __ava__Send($timeout = 5, $continue = false){
		/*
			Отправка запрса.
		*/

		if(!$this->reqHead) $this->prepareHeaders();
		$sock = $this->getSocket($timeout);
		$this->put($sock);

		$this->read($sock);
		$this->parseResult();
		$this->close($sock);
		$c = $this->getResponseCode();

		if($continue && ($c == 301 || $c == 302 || $c == 303 || $c == 305 || $c == 307)){
			if($this->continued > 5) return false;
			$this->continued ++;
			$this->url($this->getResponseHeadParam('Location'));
			return $this->Send($timeout, $continue);
		}

		return $c;
	}

	public function __ava__SendAndGetBody($all = false){
		$this->Send();
		return $all ? $this->getResponse() : $this->getResponseBody();
	}

	public function __ava__getSocket($timeout = false){
		/*
			Создает сокета
		*/

		$sock = new Socket();
		$sock->Connect($this->host, $this->url['port'], $timeout, $this->errno, $this->errstr);
		return $sock;
	}

	public function __ava__put($sock){
		/*
			Отправка запроса
		*/

		return $sock->Put($this->getRequest());
	}

	public function __ava__read($sock){
		/*
			Чтение запроса
		*/

		$this->response = $sock->Read();
		return $this->response;
	}

	public function close($sock){
		/*
			Чтение запроса
		*/

		$sock->Close();
	}

	public function __ava__setURL($part, $data){
		$this->url[$part] = $data;
	}

	public function __ava__setSecure($secure){
		$this->secure = $secure;
	}

	public function __ava__setVars($data){
		/*
			Устанавливает переменные в зависимости от используемого в системе метода
		*/

		if($str = Library::deparseStr($data)){
			if($this->method == 'POST'){
				if($this->boundary) $str = $this->getMultipartVars($data);
				$this->reqBody .= $str;
			}
			else{
				if(regExp::Match("?", $this->url['path'])) $this->url['path'] .= '&'.$str;
				else $this->url['path'] .= '?'.$str;
			}
		}
	}

	private function getMultipartVars($data){
		/*
			Возвращает значение для отправки multipart формы
		*/

		$return = '';
		$n = "\r\n";

		foreach($data as $i => $e){
			$return .= '--'.$this->boundary.$n;
			$return .= 'Content-Disposition: form-data; name="'.$i.'"'.$n.$n;
			$return .= $e.$n;
		}

		return $return;
	}

	public function __ava__setFiles($files){
		/*
			Прикрепляет файлы к POST запросу
		*/

		$add = array();
		if(!is_array($files)) $files = array($files);
		foreach($files as $i => $e) $add[$i] = array('name' => basename($e), 'type' => Files::getCTByFileName($e), 'content' => Files::read($e));
		$this->setFilesContent($files);
	}

	public function __ava__setFilesContent($files){
		/*
			Прикрепляет файлы к POST запросу, получая их уже в прочитанном виде
		*/

		$n = "\r\n";
		if(!$this->boundary){
			$this->boundary = '***---***---***'.Library::inventStr(100).'***---***---***';
			if(!empty($this->reqBody)) $this->reqBody = $this->getMultipartVars(Library::parseStr($this->reqBody));
		}

		foreach($files as $i => $e){
			$this->reqBody .= '--'.$this->boundary.$n;
			$this->reqBody .= 'Content-Disposition: form-data; name="'.$i.'"; filename="'.$e['name'].'"'.$n;
			$this->reqBody .= 'Content-Type: '.$e['type'].$n.$n;
			$this->reqBody .= $e['content'].$n.'--'.$this->boundary.'--'.$n;
		}
	}

	public function __ava__setBody($data){
		if(empty($data)) return;

		$this->method = 'POST';
		if(is_array($data)) $this->setVars($data);
		else $this->reqBody = $data;
	}

	public function __ava__setHead($data){
		/*
			Директивно устанавливает шапку запроса
		*/

		$this->reqHead = $data;
	}

	public function parseResult(){
		/*
			Разбирает ответ
		*/

		$parts = explode("\r\n\r\n", $this->response, 2);
		$this->responseHead = $parts['0'];
		$this->responseBody = isset($parts['1']) ? $parts['1'] : '';

		$heads = regExp::Split("\n", $this->responseHead);
		foreach($heads as $i => $e){
			$e = trim($e);

			if($i == 0){
				$hStatus = regExp::Split(" ", $e, false, 3);
				$this->responseCode = $hStatus['1'];
				$this->responseStatus = $hStatus['2'];
				continue;
			}

			$e = regExp::Split(":", $e, false, 2);
			$e['0'] = trim(regExp::lower(trim($e['0'])));
			$e['1'] = trim($e['1']);

			if(!isset($this->responseHeadAsArray[$e['0']])) $this->responseHeadAsArray[$e['0']] = '';
			$this->responseHeadAsArray[$e['0']] .= trim($e['1']);

			if($e['0'] == 'content-type'){
				$e['0'] = regExp::Split(";", $e['1']);
				$this->contentType = $e['0']['0'];

				foreach($e['0'] as $i1 => $e1){
					if($i1 == 0) continue;
					$e1 = trim($e1);
					$e1 = regExp::Split("=", $e1, false, 2);

					if(regExp::lower($e1['0']) == 'charset'){
						$this->charset = regExp::upper($e1['1']);
					}
				}
			}
			elseif($e['0'] == 'set-cookie'){
				$cList = array();
				foreach(regExp::split(";", $e[1]) as $i1 => $e1){
					$e1 = regExp::split("=", trim($e1), false, 2);
					if($i1 == 0){
						$cList['name'] = $e1[0];
						$cList['value'] = $e1[1];
					}
					else $cList[$e1[0]] = isset($e1[1]) ? $e1[1] : '';
				}

				$this->cookie[$cList['name']] = $cList;
				$this->cookieValues[$cList['name']] = $cList['value'];
			}
		}

		if($this->charset){
			$this->response = regExp::charset($this->charset, 'UTF-8', $this->response);
			$this->responseBody = regExp::charset($this->charset, 'UTF-8', $this->responseBody);
		}
	}

	public function getRequest(){
		return trim($this->reqHead)."\r\n\r\n".$this->reqBody;
	}

	public function getResponse(){
		return $this->response;
	}

	public function getResponseCode(){
		return $this->responseCode;
	}

	public function getResponseStatus(){
		return $this->responseStatus;
	}

	public function getResponseCharset(){
		return $this->charset;
	}

	public function getResponseContentType(){
		return $this->contentType;
	}

	public function getResponseCookies(){
		return $this->cookie;
	}

	public function getResponseCookie($name){
		return $this->cookie[$name];
	}

	public function getResponseCookieValues(){
		return $this->cookieValues;
	}

	public function getResponseCookieValue($name){
		return $this->cookieValues[$name];
	}

	public function getResponseHead(){
		return $this->responseHead;
	}

	public function getResponseBody(){
		return $this->responseBody;
	}

	public function getResponseHeadAsArray(){
		return $this->responseHeadAsArray;
	}

	public function __ava__getResponseHeadParam($param){
		$param = regExp::lower($param);
		return empty($this->responseHeadAsArray[$param]) ? '' : $this->responseHeadAsArray[$param];
	}

	public function getErrno(){
		return $this->errno;
	}

	public function getErrstr(){
		return $this->errstr;
	}
}

?>