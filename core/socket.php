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



class Socket extends objectInterface{

	private $fp;
	private $timeout;
	private $starts;

	public function	Connect($host, $port, $timeout = 15, &$errno = false, &$errstr = false){
		$this->timeout = $timeout;
		$this->starts = time();
		if($this->fp = fsockopen($host, $port, $errno, $errstr, $timeout)) return true;
		return false;
	}

	public function __ava__Put($data){		return fputs($this->fp, $data);
	}

	public function __ava__Read($limit = 0){
		$resultLength = 0;
		$result = '';

		if($limit > 0) $result = fread($this->fp, $limit);
		else{
			while(!feof($this->fp)){
				$block = fread($this->fp, 1024);
				if(!$block) break;
				$result .= $block;
				$resultLength += RegExp::bLen($block);

				if(($this->starts + $this->timeout) < time()){
					break;
				}
			}
		}

		return $result;
	}

	public function Close(){
		if(is_resource($this->fp)) fclose($this->fp);
	}

	public function Status(){
		if(is_resource($this->fp)) return true;
		return false;
	}

	public function __destruct(){		$this->Close();	}
}

?>