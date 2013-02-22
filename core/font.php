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


class Font extends Files{
	/*
		Работа со шрифтами
	*/

	private $file;
	private $size;
	private $color;

	private $type;
	private $angle;
	private $transparent;

	public function __construct($size, $color = '000', $file = false, $angle = 0, $transparent = 0){
		$this->size = $size;
		$this->color = $color;
		$this->angle = $angle;
		$this->transparent = $transparent;

		if($file){
			$file = _W.$GLOBALS['Core']->getParam('fontsFolder').$file;
			if(!file_exists($file)) throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenfajl2:'.Library::serialize(array($file)).'}');
			$this->file = $file;
			$this->type = regExp::lower(regExp::replace('.', '', Files::getExtension($file)));
		}
		else{
			$this->type = 'default';
		}
	}

	public function __ava__setFont($file){
		if(!file_exists($file)) throw new AVA_Files_Exception('{Call:Lang:core:core:nenajdenfajl2:'.Library::serialize(array($file)).'}');
		$this->file = $file;
	}

	public function __ava__setSize($size){
		$this->size = $size;
	}

	public function __ava__setColor($color){
		$this->color = $color;
	}

	public function __ava__getFont(){
		return $this->file;
	}

	public function __ava__getSize(){
		return $this->size;
	}

	public function __ava__getColor(){
		return $this->color;
	}

	public function __ava__getAngle(){
		return $this->angle;
	}

	public function __ava__getTransparency(){
		return $this->transparent;
	}

	public function __ava__getType(){
		return $this->type;
	}
}

?>