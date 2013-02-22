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


function AVACommonException($exc){
	$buffer = ob_get_contents();
	ob_end_clean();
	ob_start();

	echo "<html><head><title>Error {$exc->errorCode}</title></head><body><p>Error {$exc->errorCode} in ".$exc->getFile()." line ".$exc->getLine()."<br/>".emergencyLang($exc->getMessage())."</p>";
	if(Library::constVal('TEST_MODE')) echo "<pre>".strip_tags($exc->getTraceAsString())."</pre>";
	echo "<br/><br/>".$buffer."</body>";

	if(!isset($GLOBALS['Core'])){
		AVAsaveException($exc);
		exit;
	}

	$replaces['msg'] = $exc->getMessage();
	$replaces['trace'] = '[nocall]'.$exc->getTraceAsString().'[/nocall]';
	$replaces['place'] = '{Call:Lang:core:core:mestooshibki:'.Library::serialize(array($exc->getFile(), $exc->getLine())).'}';
	$replaces['title'] = empty($exc->templateTitle) ? strip_tags($replaces['msg']) : $exc->templateTitle;

	$GLOBALS['Core']->runPlugins('exception');
	$tmplName = $GLOBALS['Core']->getTemplateName('system');

	if($exc->errorCode == 10){
		$template = Files::Read(TMPL.'system/'.$tmplName.'/403.tmpl');
		if(!$GLOBALS['Core']->getUserId()) $replaces['formId'] = $GLOBALS['Core']->callModAndGetFormId('main', 'login', 'login');;
		header('HTTP/1.1 403 Forbidden');
	}
	elseif($exc->errorCode == 20){
		$template = Files::Read(TMPL.'system/'.$tmplName.'/404.tmpl');
		header('HTTP/1.1 404 Not Found');
	}
	else{
		$template = Files::Read(TMPL.'system/'.$tmplName.'/error.tmpl');
	}

	$GLOBALS['Core']->setTemplateType('system');
	header('Content-Type: text/html; charset='.$GLOBALS['Core']->getParam('charset'));

	$replaces['type'] = $exc->getExceptionType();
	$replaces['errorCode'] = $exc->errorCode;
	$tmpl = $GLOBALS['Core']->replace($template, $GLOBALS['Core']->loadedMainModObj() ? $GLOBALS['Core']->getMainModObj() : new moduleInterface(false, false, false), $replaces);

	ob_end_clean();
	echo $GLOBALS['Core']->prepareFinalTmplText($tmpl).$buffer;
	AVAsaveException($exc);
}

function AVAsaveException($exc){
	/*
		Сохраняет сообщение в лог
	*/

	if(!empty($GLOBALS["AVA_ERROR_LOG_PARAMS"]['errorLog'])){
		$text1 = gmdate('d.m.Y H:i:s ').$_SERVER['REQUEST_URI'].' '.$exc->getFile().' '.$exc->getLine();
		$text2 = strip_tags(emergencyLang($exc->getMessage()));
		$text3 = str_replace(array("\n", "\r"), " ", substr($text2, 0, 1024));
		$text4 = $text1." ".$text3."\n";
		$text5 = $text1."\n".$text2."\n\n".$exc->getTraceAsString();

		switch($GLOBALS["AVA_ERROR_LOG_PARAMS"]['errorLog']){
			case 'db':
				if(isset($GLOBALS['Core']->DB)){
					return $GLOBALS['Core']->DB->Ins(
						array(
							'exceptions',
							array('date' => time(), 'code' => $exc->errorCode, 'file' => $exc->getFile(), 'line' => $exc->getLine(), 'body' => $text5)
						)
					);
				}
				break;

			case 'file':
				return files::Write($GLOBALS["AVA_ERROR_LOG_PARAMS"]['errorLogPath'].'log.txt', $text4, 'a');

			case 'fileFull':
				return files::Write($GLOBALS["AVA_ERROR_LOG_PARAMS"]['errorLogPath'].'log.txt', $text5."\n\n\n\n", 'a');

			case 'multifiles':
				return files::Write($GLOBALS["AVA_ERROR_LOG_PARAMS"]['errorLogPath'].time().'.'.round(microtime() * 100).'.txt', $text5, 'a');
		}
	}

	return true;
}

function emergencyLang($msg){
	/*
		Экстренная обработка сообщения об ошибке
	*/

	if(!preg_match("|^\{Call:Lang:(.+)\}$|iUs", $msg, $m)) return $msg;
	$m[1] = explode(":", $m[1]);
	$Lang = new Lang(LANGUAGE);
	return $Lang->getPhrase($m[1][0], $m[1][1], $m[1][2], isset($m[1][3]) ? unserialize(base64_decode($m[1][3])) : array());
}

class AVA_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:fatalnaiaosh}';
	public $errorCode = 100;

	function getExceptionType(){
		return 'common';
	}
}

class AVA_DB_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:fatalnaiaosh1}';
	public $errorCode = 60;

	function getExceptionType(){
		return 'DB';
	}
}

class AVA_Templ_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:fatalnaiaosh2}';
	public $errorCode = 50;

	function getExceptionType(){
		return 'template';
	}
}

class AVA_Files_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:fatalnaiaosh3}';
	public $errorCode = 40;

	function getExceptionType(){
		return 'files';
	}
}

class AVA_XML_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:fatalnaiaosh4}';
	public $errorCode = 30;

	function getExceptionType(){
		return 'xml';
	}
}

class AVA_Access_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:fatalnaiaosh5}';
	public $errorCode = 10;

	function getExceptionType(){
		return 'access';
	}
}

class AVA_NotFound_Exception extends Exception {

	public $templateTitle = '{Call:Lang:core:core:stranitsanen}';
	public $errorCode = 20;

	function getExceptionType(){
		return 'notFound';
	}
}

class AVA_Reserve_Extension extends Exception {

	public $templateTitle = '{Call:Lang:core:core:stranitsazar}';
	public $errorCode = 21;

	function getExceptionType(){
		return 'reserved';
	}
}

?>