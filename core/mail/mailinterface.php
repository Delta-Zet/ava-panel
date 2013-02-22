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



class mailInterface extends objectInterface{

	private $charset;
	private $contentType;

	protected $receivers = array();
	protected $subj;
	protected $body;

	private $headers = array();
	protected $attaches = array();

	protected $sendStatus = 0;		//Статус отправки - 0 - не отправлено, 1 - отправлено, -1 - неудачная попытка

	public function setCharset($charset){		$this->charset = $charset;
		$this->headers['Content-type'] .= '; charset='.$charset;	}

	public function setSender($senderEml, $sender){
		$this->headers['From'] = '"=?'.$this->charset.'?B?'.base64_encode(regExp::charset('UTF-8', $this->charset, $sender)).'?="'.' <'.$senderEml.'>';
		$this->headers['Reply-to'] = $senderEml;
	}

	public function setContentType($contentType){		$this->contentType = $contentType;
		$this->headers['Content-type'] = $contentType;
	}

	public function addHeaders($headers){		$this->headers = Library::array_merge($this->headers, $headers);	}

	public function addReceiver($eml){		if(is_array($eml)){			foreach($eml as $i => $e){				$this->receivers[$e] = $e;			}		}
		else{			$this->receivers[$eml] = $eml;
		}	}

	public function setMail( $subj, $body ){		/*
			Body становится аттачем в html-формате.
			Подгружаются все аттачи в него.
			Происходит переименование img, флешек и пр. на Content-ID
			Вставляется boundary в header
		*/

		$this->subj = regExp::charset('UTF-8', $this->charset, $subj);
		$this->body = regExp::charset('UTF-8', $this->charset, $body);
	}

	public function setAttach($i, $e){		$this->attaches[$i] = $e;	}

	public function prepareHeaders(){		/*
			Создает из headers строку
		*/

		$h = '';
		foreach($this->headers as $i => $e){			$h .= "{$i}: {$e}\n";		}

		return $h;	}
}

?>