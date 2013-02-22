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



class Arc extends Files {
	public function extract($src, $dst, $params = array(), $forceCreate = true){		/*
			Извлекает содержимое архива в некоторую папку
		 */

		if($forceCreate && !file_exists($dst)) self::mkDir($dst);		if(!$src || !is_file($src)) throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenfajl:'.Library::serialize(array($src)).'}');
		if(!$dst || !is_dir($dst)) throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenadir:'.Library::serialize(array($dst)).'}');

		$ext = self::getExtension($src);

		switch($ext){
			case '.gz':

				$arc = new arcGz($src, $params);
				$arc->extract(TMP);
				$src = $arc->getExtractedFile();
				$ext = self::getExtension($src);

				if($ext != '.tar'){
					if(self::mv($src, $dst.basename($src))){						return $arc;					}
					else return false;
				}

				break;

			case '.rar':
				$arc = new arcRar($src, $params);
				if($arc->extract($dst)) return $arc;
				return false;
		}

		if(!is_dir($dst)) self::mkDir($dst);

		$ext = 'arc'.regExp::Replace('.', "", $ext);
		$arc = new $ext($src, $params);

		if($arc->extract($dst)){
			return $arc;
		}

		return false;
	}
	public function compact($files, $arc, $params = array()){		/*
			Пакует $files в $arc
		*/

		$ext = self::getExtension($arc);
		if($ext != '.tar' && self::getExtension($arc2 = self::getFileNameWoEx($arc)) == '.tar'){			self::compact($files, $arc2, $params);
			$files = $arc2;		}

		switch($ext){			case '.tar':
				chdir($files);
				shell_exec('tar -cpf '.$arc.' *'.(file_exists('.htaccess') ? ' .htaccess' : ''));
				break;
			case '.gz':
				shell_exec('gzip '.$files);
				break;

			case '.bz2':
				shell_exec('bzip2 '.$files);
				break;

			case '.zip':
				chdir($files);
				shell_exec('zip -r -9 '.$arc.' *'.file_exists($files.'.htaccess') ? ' .htaccess' : '');
				break;
		}

		return file_exists($arc);
	}
}

?>