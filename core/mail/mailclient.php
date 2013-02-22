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



class mailClient extends mailInterface{
	public function send(){
		$return = mail(implode(',', $this->receivers), $this->subj, $this->body, $h = $this->prepareHeaders());

		if($return && SHOW_MAIL_DEBUG_DATA > 0){			$GLOBALS['Core']->setDebugAction('{Call:Lang:core:core:otpravlenopi:'.Library::serialize(array($this->subj)).'} '."<br/><br/>".$h."\r\n\r\n".$this->body);		}
		elseif(SHOW_MAIL_DEBUG_DATA > 0){
			$GLOBALS['Core']->setDebugAction('{Call:Lang:core:core:pismoneotpra:'.Library::serialize(array($this->subj)).'} '."<br/><br/>".$h."\r\n\r\n".$this->body);
		}

		return $return;	}
}

?>