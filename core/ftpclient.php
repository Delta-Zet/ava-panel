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



class ftpClient extends objectInterface{
	private $fp;
	private $currentFolder;

	private $host;
	private $port;
	private $user;
	private $pwd;

	public function __construct($host, $user = 'anonymous', $pwd = '', $port = 21){		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pwd = $pwd;
	}

	public function connect($pasv = false){
		/*
			Устанавливает ftp-соединение
		*/

		if(!$this->fp = ftp_connect($this->host, $this->port)) return false;
		elseif(!ftp_login($this->fp, $this->user, $this->pwd)) return false;
		if($pasv) $this->pasv(true);
		return $this->fp;
	}

	public function pasv($pasv = true){
		/*
			Устанавливает ftp-соединение
		*/

		ftp_pasv($this->fp, $pasv);
	}

	public function __ava__setFolder($path, $relativeCurrent = false){		/*
			Устанавливает путь
		*/

		if(!$relativeCurrent && $this->currentFolder) ftp_chdir($this->fp, '/');
		if($return = ftp_chdir($this->fp, $path)){			if(!$relativeCurrent) $this->currentFolder = $path;
			else $this->currentFolder .= regExp::Match("|/$|",$path, true) ? $path : '/'.$path;		}

		return $return;	}

	public function __ava__Copy($src, $dst){
		/*
			Копирует файлы или директории с использованием FTP
		*/

		if(is_dir($src)){
			if($dst && $dst != '/') if(!$this->MK($dst)) return false;
			if(!regExp::Match("|/$|", $dst, true, true)) $dst .= '/';

			foreach(Files::readFolder($src) as $e){
				if(is_dir($src.$e)) $e .= '/';
				if(!$this->Copy($src.$e, $dst.$e)) return false;
			}

			return true;
		}
		else{
			return ftp_put($this->fp, $dst, $src, FTP_BINARY);
		}
	}

	public function __ava__Write($dst, $text){
		/*
			Копирует файлы или директории с использованием FTP
		*/

		$tmpFile = TMP.Library::inventStr(8);
		Files::write($tmpFile, $text);
		return $this->Copy($tmpFile, $dst);
	}

	public function __ava__DL($remote, $file = false){
		/*
			Скачивает файл
		*/

		if(!$this->fileExists($remote)) return '';
		$return = ftp_get($this->fp, $file ? $file : $tmpFile = TMP.Library::inventStr(8), $remote, FTP_BINARY);
		if($file) return $return;
		else return Files::read($tmpFile);
	}

	public function __ava__MK($folder){		/*
			Создает папку
		*/

		if($this->fileExists($folder)) return true;
		return ftp_mkdir($this->fp, $folder);	}
	public function __ava__RM($folder){
		/*
			Удаляет папку
		*/

		if(!$this->fileExists($folder)) return true;
		elseif($this->isDir($folder)){
			foreach($this->files($folder) as $e){
				$this->RM($e);
			}
			return ftp_rmdir($this->fp, $folder);
		}
		else return ftp_delete($this->fp, $folder);
	}

	public function __ava__chmod($file, $chmod){
		/*
			Устанавливает права на папку
		*/

		if(!$this->fileExists($file)) return false;
		return ftp_chmod($this->fp, Files::getMode($chmod), $file);
	}

	public function __ava__files($folder = ''){		/*
			Список всех файлов в папке
		*/

		return ftp_nlist($this->fp, $folder);	}

	public function __ava__fileExists($file){
		/*
			Список всех файлов в папке
		*/

		$file = trim($file, '/');
		$fl = $this->files(dirname($file));
		return $fl && (in_array($file, $fl) || in_array('/'.$file, $fl) || in_array(basename($file), $fl));
	}

	public function __ava__isDir($file){
		/*
			Проверяет что это директория
		*/

		if(ftp_size($this->fp, $file) < 0) return true;
		return false;
	}

	public function __destruct(){		if(is_resource($this->fp)) ftp_close($this->fp);	}
}

?>