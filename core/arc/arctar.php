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



class arcTar extends objectInterface implements arcInterface{

	private $arc;
	private $extractedFiles = array();
	private $files;

	private $fp;
	private $len;
	private $position = 0;

	private $types = array (
		0x0 => 'file',
		0x30 => 'file',
		0x31 => 'link',
		0x32 => 'link',
		0x33 => 'file',
		0x34 => 'file',
		0x35 => 'directory',
		0x36 => 'file',
		0x37 => 'file'
	);

	public function __construct($arc, $params = array()){
			throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenfajl1:'.Library::serialize(array($arc)).'}');
		}

		$this->arc = $arc;
	}

	public function extract($folder = ''){
			Распаковывает Tar-архив в специфицированную директорию
		*/

		if(!file_exists($folder)){

		if(!regExp::Match("/\/$/", $folder, true)) $folder .= '/';
		$this->fp = fopen($this->arc, 'r');
		$this->len = filesize($this->arc);

		while(($fileInfo = $this->extractNext()) !== false){

			if($fileInfo['type'] == 'file'){
				Files::touch($saveFile, $fileInfo['time']);
			}
			elseif($fileInfo['type'] == 'directory'){
			}
		}

		fclose($this->fp);
		return true;
	}

	private function extractNext(){
			Извлекает очередной файл из прочитанного архива
		*/

		if($this->position >= $this->len) return false;
		$this->position += 512;

		$file = $fileInfo['name'];
		if(!$file) return false;
		$this->files[] = $file;

		$fileInfo['type'] = isset($this->types[$fileInfo['typeflag']]) ? $this->types[$fileInfo['typeflag']] : '';
		$size = octdec($fileInfo['size']);
		$length = ceil($size / 512) * 512;

		if($length > 0){
			$fileInfo['data'] = substr(fread($this->fp, $length), 0, $size);
			$this->position += $length;
		}
		else $fileInfo['data'] = '';
		return $fileInfo;
	}
	public function compact($files){}

	public function getExtractedFile(){
}

?>