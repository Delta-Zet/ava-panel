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



class pageObjectsInterface extends objectInterface {

	/*
		Общий интерфейс для форм, списков и пр. объектов которые могут появиться на странице
	*/
	public $DB;
	protected $mod;

	protected $name;
	protected $parent;
	protected $templText;
	private $templatesIsRead;

	protected $templFile;
	protected $templName;
	protected $templates = array();
	protected $forceDefTmplRead;

	public function __construct($name, $params, $templFile, $parent, $forceDefTmplRead = true){		/*
			Устанавливает имя, считывает шаблоны
			$parent должен быть нормальным объектом модуля.
		*/

		$this->name = $name;
		$params['name'] = $name;
		$this->forceDefTmplRead = $forceDefTmplRead;

		$this->params = $params;
		$this->parent = $parent;
		$this->templFile = $templFile;
		$this->params['INTERFACE_OBJ'] = $this;
	}

	public function __ava__setDB($DB){		$this->DB = $DB;	}

	public function __ava__setTemplName($templName){
		/*
			Устанавливает имя шаблона всей формы
		*/

		$this->templName = $templName;
		$this->params['template'] = $templName;
	}

	public function __ava__setTemplFile($templFile){		$this->templFile = $templFile;	}

	public function __ava__setParam($var, $value){
		/*
			Устанавливает значение блока в массиве params
		*/

		$this->params[$var] = $value;
	}

	public function __ava__setParamWithReplace($var, $values, $template){
		/*
			Устанавливает значение блока в массиве params
		*/

		$this->params[$var] = $GLOBALS['Core']->replace($this->templates[$template], $this->parent, $values);
	}

	public function __ava__clearParam($var){
		/*
			Устанавливает значение блока в массиве params
		*/

		unset($this->params[$var]);
	}

	public function __ava__getParam($param){		return empty($this->params[$param]) ? '' : $this->params[$param];	}

	public function getName(){		return $this->name;	}

	public function getTemplName(){
		return $this->templName;
	}

	public function getTemplRaw(){
		/*
			Возвращает сырой текст считанного шаблона
		*/

		return $this->templates;
	}

	public function getTemplText(){
		/*
			Возвращает текст сгенерированного объекта
		*/

		return $this->templText;
	}

	public function readTemplates(){		/*
			Считывает заявленные шаблоны
		*/
		if($this->forceDefTmplRead || !regExp::match("/^(\/|\.)/", $this->templFile, true)){
			$this->templates = Library::array_merge($GLOBALS['Core']->getTemplatePage($this->templPref), $this->templates, 2, 0, true);
			if($this->templFile && $this->templFile != $this->templPref){
				$this->templates = Library::array_merge($GLOBALS['Core']->getTemplatePage($this->templFile), $this->templates, 2, 0, true);
			}
		}
		else{
			$this->templates = Library::array_merge($this->templates, $GLOBALS['Core']->getTemplatePage($this->templFile), 2, 0, true);
		}

		$this->templatesIsRead = true;
	}

	public function __ava__getTmplBlock($bName, $params = array(), $bType = 'entry'){		/*
			Возвращает блок шаблона из числа считанныхъ
		*/

		$bParams = array();
		if(!empty($params['template'])) $bParams['template'] = $params['template'];
		return $this->templates[$bType][$bName][$GLOBALS['Core']->getBlockFromCollect($this->templates[$bType][$bName], $bParams)]['content'];
	}

	public function __ava__getText($forceReading = true){
		/*
			Возвращает сгенерированный объект для страницы
		 */

		if(!$this->templatesIsRead || $forceReading) $this->readTemplates();
		$this->addAllBlocks();
		return $this->templText = $GLOBALS['Core']->replace($this->getTmplBlock($this->templPref, $this->params, 'cover'), $this->parent, $this->params);
	}

	public function __ava__setNocalls($values){
		/*
			Устанавливает <nocall> на values
		*/

		if(is_array($values)){			$return = array();
			foreach($values as $i => $e){				$return[$i] = $this->setNocalls($e);			}		}
		elseif($values) $return = '[nocall]'.$values.'[/nocall]';
		else $return = $values;

		return $return;
	}
}

?>