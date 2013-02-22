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


class Files extends objectInterface{

	private static $baseDir;
	private static $mimeTypes = array(
		'.txt' => 'text/plain',
		'.htm' => 'text/html',
		'.html' => 'text/html',
		'.php' => 'text/html',
		'.css' => 'text/css',
		'.js' => 'application/javascript',
		'.json' => 'application/json',
		'.xml' => 'application/xml',
		'.swf' => 'application/x-shockwave-flash',
		'.flv' => 'video/x-flv',

		// images
		'.png' => 'image/png',
		'.jpg' => 'image/jpeg',
		'.jpeg' => 'image/jpeg',
		'.jpe' => 'image/jpeg',
		'.gif' => 'image/gif',
		'.bmp' => 'image/bmp',
		'.ico' => 'image/vnd.microsoft.icon',
		'.tiff' => 'image/tiff',
		'.tif' => 'image/tiff',
		'.svg' => 'image/svg+xml',
		'.svgz' => 'image/svg+xml',

		// archives
		'.zip' => 'application/zip',
		'.rar' => 'application/x-rar-compressed',
		'.exe' => 'application/x-msdownload',
		'.msi' => 'application/x-msdownload',
		'.cab' => 'application/vnd.ms-cab-compressed',

		// audio/video
		'.mp3' => 'audio/mpeg',
		'.qt' => 'video/quicktime',
		'.mov' => 'video/quicktime',

		// adobe
		'.pdf' => 'application/pdf',
		'.psd' => 'image/vnd.adobe.photoshop',
		'.ai' => 'application/postscript',
		'.eps' => 'application/postscript',
		'.ps' => 'application/postscript',

		// ms office
		'.doc' => 'application/msword',
		'.rtf' => 'application/rtf',
		'.xls' => 'application/vnd.ms-excel',
		'.ppt' => 'application/vnd.ms-powerpoint',

		// open office
		'.odt' => 'application/vnd.oasis.opendocument.text',
		'.ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);


	public function read($file, $len = false){
		/*
			Считывает файл
		*/

		if(!file_exists($file)) throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenfajl1:'.Library::serialize(array($file)).'}');
		elseif(is_dir($file)) throw new AVA_Files_Exception('{Call:Lang:core:core:fajlnelziapr:'.Library::serialize(array($file)).'}');
		elseif(!$fp = fopen($file, 'r')) throw new AVA_Files_Exception('{Call:Lang:core:core:neudaetsiapr:'.Library::serialize(array($file)).'}');

		$return = fread($fp, $len === false ? filesize($file) + 1 : $len);
		fclose($fp);
		return $return;
	}

	public function write($file, $str, $mode = 'wb', $chmod = false){
		/*
			Записывает в файл. Устанавливает ему специфицированный chMod
		*/

		if($chmod === false) $chmod = 0644;
		else $chmod = self::getMode($chmod);

		if(!$fp = fopen($file, $mode)) throw new AVA_Files_Exception('{Call:Lang:core:core:neudalosotkr:'.Library::serialize(array($file, $mode)).'}');
		$return = fwrite($fp, $str);
		fclose($fp);

		if($return) chmod($file, $chmod);
		if($return === false) return false;
		return true;
	}

	public function getExtension($file){
		/*
			Определяет расширение файла
		*/

		regExp::Match("/(\.\w+)$/iU", $file, true, true, $m);
		if(empty($m['1'])) return '';
		return $m['1'];
	}

	public function getFileParams($file){
		/*
			Возвращает размер и дату создания файла
		*/

		$return = lstat($file);
		$return['name'] = basename($file);
		unset($return[0], $return[1], $return[2], $return[3], $return[4], $return[5], $return[6], $return[7], $return[8], $return[9], $return[10], $return[11], $return[12]);
		return $return;
	}

	public function getFileNameWoEx($file){
		/*
			Возвращает имя файла
		 */

		regExp::Match("/^(.*)(\.\w+)$/iU", $file, true, true, $m);
		if(empty($m['1'])) return $file;
		return $m['1'];
	}

	public function isWritable($path){
		/*
			Проверяется возможность записи в определенный файл или директорию
		*/

		if(is_writable($path)) return true;
		if(!file_exists($path)) return is_writable(self::dirname($path));
		return false;
	}

	public function dirname($path){
		return regExp::replace("|/[^/]*$|", '', $path, true).'/';
	}

	public function isReadable($path){
		/*
			Проверяется возможность чтения файла
		*/

		return is_readable($path);
	}

	public function moveUploads($data, $name = false){
		/*
			Перемещает загруженный архив во временную папку
		*/

		$src = $data['tmp_name'];
		$file = $name === false ? basename($data['name']) : $name.self::getExtension($data['name']);
		$dst = TMP.$file;

		if(move_uploaded_file($src, $dst)){
			return $file;
		}
		return false;
	}

	public function mv($src, $dst){
		/*
			Перемещает файл
		*/

		return rename($src, $dst);
	}

	public function cp($src, $dst, $createPath = true){
		/*
			Копирует файл
		*/

		if($createPath && !file_exists($dst2 = self::dirname($dst))) self::mkDir($dst2);
		if(!$return = copy($src, $dst)) throw new AVA_Files_Exception('{Call:Lang:core:core:oshibkakopir:'.Library::serialize(array($src, $dst)).'}');
		return $return;
	}

	public function rm($file){
		/*
			Удаляет файл
		*/

		if(is_dir($file)) return self::rmFolder($file);
		else return unlink($file);
	}

	public function getMode($mode){
		/*
			Выдает какой должен быть chMod
		 */

		$mode = trim($mode);

		if(regExp::Match("/^0/", $mode)) return $mode;
		elseif(regExp::Digit($mode)){
			if($mode < 0) return intval('0'.(-$mode), 8);
			else return intval('0'.$mode, 8);
		}
	}

	public function mkDir($file, $mode = false){
		/*
			Создает папку
		*/

		if($mode === false) $mode = 0755;
		else $mode = self::getMode($mode);

		if(regExp::isAbsPath($file)){
			$file2 = regExp::replace($bd = self::getBasedir(), '', $file);

			if($file2 != $file){
				$path = $bd;
				$file = $file2;
			}
			else $path = '/';
		}
		else $path = '';

		$elms = regExp::Split("/", $file);

		foreach($elms as $e){
			$e = trim($e);
			if(strlen($e) < 1) continue;

			$path .= $e.'/';
			if(!file_exists($path) || !is_dir($path)){
				if(!mkdir($path, $mode)){
					return false;
				}
			}
		}

		return true;
	}

	public function getBasedir(){
		if(!self::$baseDir){
			foreach(regExp::split(':', ini_get('open_basedir')) as $e){
				if($e && regExp::Match($e, _W)){
					self::$baseDir = $e;
					break;
				}
			}

			if(self::$baseDir && !regExp::Match("|/$|", self::$baseDir, true)) self::$baseDir .= '/';
		}

		return self::$baseDir;
	}

	public function cpFolder($src, $dst){
		/*
			Пытается копировать из $src в $dst все содержимое
		*/

		if(!regExp::Match("/\/$/", $src, true)) $src .= '/';
		if(!regExp::Match("/\/$/", $dst, true)) $dst .= '/';

		if(!file_exists($dst)) self::mkDir($dst);
		$files = self::readFolder($src);

		foreach($files as $i => $e){
			$sFile = $src.$e;
			$dFile = $dst.$e;

			if(is_dir($sFile)) self::cpFolder($sFile, $dFile);
			else copy($sFile, $dFile);

			if(!file_exists($dFile)){
				return false;
			}
		}

		return true;
	}

	public function emptyFolder($folder){
		/*
			Проверяет что папка пустая
		*/

		$dp = opendir($folder);

		while(($file = readdir($dp)) !== false){
			if($file == '.' || $file == '..') continue;
			else{
				closedir($dp);
				return false;
			}
		}

		closedir($dp);
		return true;
	}

	public function getCTByFile($file){
		/*
			Возвращает Content-type по существующему файлу
		*/

		if(!file_exists($file)) return false;
		if(function_exists('system')) return system("file -bi '$file'");
		return self::getCTByFileName($file);
	}

	public function getCTByFileName($file){
		/*
			Возвращает Content-type по имени файла
		*/

		return self::getCT(self::getExtension($file));
	}

	public function getCT($ex){
		/*
			Возвращает Content-type для этого расширения
		*/

		return self::$mimeTypes[$ex];
	}

	public function getExByCT($type){
		/*
			Возвращает Content-type для этого расширения
		*/

		foreach(self::$mimeTypes as $i => $e){
			if(regExp::lower($e) == regExp::lower($type)) return $i;
		}
		return '';
	}

	public function readFolderRecursive($src){
		/*
			Читает все содержимое папки рекурсивно, возвращает массив всех найденных файлов, в т.ч. в подпапках
		*/

		$return = array();
		foreach(self::readFolder($src) as $e){
			if(is_dir($src.$e)) $return[$e] = self::readFolderRecursive($src.$e);
			else $return[$e] = $e;
		}

		return $return;
	}

	public function readFolderFilesAsPath($src, $ext = array()){
		/*
			Читает все содержимое папки рекурсивно, возвращает массив полных имен найденных файлов
		*/

		$return = array();
		if(!is_array($ext)) $ext = array($ext);

		foreach(self::readFolder($src) as $e){
			if(is_dir($src.$e)) $return = Library::array_merge($return, self::readFolderFilesAsPath($src.$e.'/', $ext));
			elseif(!$ext || in_array(self::getExtension($e), $ext)) $return[$src.$e] = $src.$e;
		}

		return $return;
	}

	public function readFolder($src){
		/*
			Читает все содержимое, возвращает массив всех найденных файлов и папок
		 */

		if(!file_exists($src) || !is_dir($src)) throw new AVA_Files_Exception('{Call:Lang:core:core:neiavliaetsi:'.Library::serialize(array($src)).'}');

		if(!regExp::Match("/\/$/", $src, true)) $src .= '/';
		$return = array();
		$dp = opendir($src);

		while(($file = readdir($dp)) !== false){
			if($file == '.' || $file == '..') continue;
			$return[] = $file;
		}

		closedir($dp);
		return $return;
	}

	public function readFolderFiles($src){
		$return = array();
		foreach(self::readFolder($src) as $i => $e){
			if(is_file($src.$e)) $return[$i] = $e;
		}

		return $return;
	}

	public function readFolderFileParams($src){
		$return = array();
		foreach(self::readFolderFiles($src) as $i => $e){
			$return[$i] = self::getFileParams($src.$e);
			$return[$i]['id'] = $e;
		}

		return $return;
	}

	public function rmFolder($folder){
		/*
			Рекурсивно удаляет папку и все ее содержимое
		*/

		if(!is_dir($folder)) return self::rm($folder);
		elseif(!file_exists($folder)) return true;
		if(!regExp::Match("/\/$/", $folder, true)) $folder .= '/';

		$files = self::readFolder($folder);

		foreach($files as $i => $e){
			$file = $folder.$e;

			if(is_dir($file)) self::rmFolder($file);
			else unlink($file);

			if(file_exists($file)) return false;
		}

		return rmdir($folder);
	}

	public function touch($file, $time){
		if(!$time) $time = time();
		return @touch($file, $time);
	}

	public function getEmptyFolder($folder){
		/*
			Возвращает свободное имя директории
		*/

		$j = 1;
		$fld = $folder;

		while(file_exists($folder)){
			$folder = $fld.$j;
			$j ++;
		}

		return $folder;
	}

	public function getEmptyFile($file){
		/*
			Возвращает свободное имя файла
		*/

		$j = 1;
		$ex = self::getExtension($file);
		$file = self::getFileNameWoEx($file);
		$return = $file.$ex;

		while(file_exists($return)){
			$return = $file.$j.$ex;
			$j ++;
		}

		return $return;
	}
}

?>