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



class arcRar extends objectInterface implements arcInterface{

	private $arc;
	private $params;
	private $extractedFile;
	public function __construct($arc, $params = array()){
		$this->params = $params;

	public function extract($folder = ''){
		if(function_exists('rar_open')){
			foreach(rar_list($rarFp) as $e){
			    if(!$e->extract($folder)) return false;
			}

			rar_close($rarFp);
		}
		elseif(function_exists('shell_exec')){
			if(regExp::Match('All OK', shell_exec("unrar x".(isset($this->params['pwd']) ? ' -p'.$this->params['pwd'] : '')." {$this->arc} ".$folder))) return true;
		}
		else{

		return true;
	}
	public function compact($files){}

	public function getExtractedFile(){
}


?>