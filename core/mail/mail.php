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



class Mail extends objectInterface{
	public static function sendWithQueue($id){		/*
			Отправляет письмо извлеченное из очереди. Проставляет отметку об отправке
		*/

		$t = time();
		$p = $GLOBALS['Core']->DB->rowFetch(array('mails', '*', "`id`='$id'"));

		$p['extra'] = empty($p['extra']) ? array('attaches' => array(), 'headers' => array()) : Library::unserialize($p['extra']);
		$p['notify_extra'] = empty($p['notify_extra']) ? array('attaches' => array(), 'headers' => array()) : Library::unserialize($p['notify_extra']);
		$p['notify_extra']['attaches'] = Library::array_merge($p['extra']['attaches'], $p['notify_extra']['attaches']);
		$p['notify_extra']['headers'] = Library::array_merge($p['extra']['headers'], $p['notify_extra']['headers']);

		$fields = array('senddate' => $t, 'attempts' => '`attempts` + 1', '#isExp' => array('attempts' => true));
		$where = "`id`='$id'";

		if(self::send($p['eml'], $p['subj'], $p['body'], $p['sender_eml'], $p['sender'], $p['format'], $p['extra']['attaches'], $p['extra']['attaches'])){			$fields['status'] = 1;			$GLOBALS['Core']->DB->Upd(array('mails', $fields, $where));
			if($p['notify_success']) self::send($p['notify_eml'], $p['notify_success_subj'], $p['notify_success_body'], $p['notify_sender_eml'], $p['notify_sender'], $p['format'], $p['notify_extra']['attaches'], $p['notify_extra']['attaches']);
			return true;		}

		if($p['attempts'] <= $GLOBALS['Core']->getParam('mailAttempts')){			$GLOBALS['Core']->DB->Upd(array('mails', $fields, $where));
			if($p['notify_fail'] == 2) self::send($p['notify_eml'], $p['notify_fail_subj'], $p['notify_fail_body'], $p['notify_sender_eml'], $p['notify_sender'], $p['format'], $p['notify_extra']['attaches'], $p['notify_extra']['attaches']);
		}
		else{			$fields['status'] = 2;
			$GLOBALS['Core']->DB->Upd(array('mails', $fields, $where));
			if($p['notify_fail']) self::send($p['notify_eml'], $p['notify_fail_subj'], $p['notify_fail_body'], $p['notify_sender_eml'], $p['notify_sender'], $p['format'], $p['notify_extra']['attaches'], $p['notify_extra']['attaches']);
		}
	}

	public static function send($to, $subj, $body, $senderEml = '', $sender = '', $format = '', $attaches = array(), $headers = array()){		/*
			Отправляет письма
			to - мыльнитсы получателей, одна или массив
			$sender - отправитель.
			$attaches - аттачи. Массив вида имя в аттаче => полный путь к файлу (либо аттачимый файл как текст)
		*/

		if(!$senderEml) $senderEml = $GLOBALS['Core']->getParam('defaultEml');
		if(!$sender) $sender = $senderEml;
		if(!$format) $format = $GLOBALS['Core']->getParam('mailFormat');

		$mail = self::getMailInterface();
		$mail->setContentType($format);
		$mail->setCharset($GLOBALS['Core']->getParam('mailCharset'));
		$mail->setSender($senderEml, $sender);

		$mail->addHeaders(
			array(
				'MIME-Version' => '1.0',
				'Date' => Dates::strTime(),
				'X-Mailer' => 'AVA Smtp Client'
			)
		);

		$mail->addHeaders($headers);
		$mail->addReceiver($to);
		$mail->setMail( $subj, $body );

		foreach($attaches as $i => $e){			if(file_exists($e)) $e = Files::read($e);
			$mail->setAttach($i, $e);		}
		return $mail->send();
	}

	public static function getMailInterface(){		return new mailClient();	}
}

?>