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



class arcGz extends objectInterface implements arcInterface{

	private $arc;
	private $params;
	private $extractedFile;
	public function __construct($arc, $params = array()){		$this->arc = $arc;
		$this->params = $params;
	}

	public function read($file){		ob_start();		readgzfile($file);
		$str = ob_get_contents();
		ob_end_clean();

		return $str;
	}

	public function extract($folder = ''){		$str = $this->read($this->arc);

		if(!$folder) $folder = regExp::Replace("/\.gz$/iUs", "", $folder, true);		elseif(is_dir($folder)) $folder .= regExp::Replace("/\.gz$/iUs", "", basename($this->arc), true);

		if(Files::Write($folder, $str)){			$this->extractedFile = $folder;
			return true;		}

		return false;	}
	public function compact($files){}

	public function getExtractedFile(){		return $this->extractedFile;	}
}


?>