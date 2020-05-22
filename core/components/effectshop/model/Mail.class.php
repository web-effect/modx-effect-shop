<?php


class Mail 
{
	/*
	public static function validate(array $rules, array $data)
	{
		$errors = [];
		foreach ($rules as $field=>$rule) {
			$value = trim($data[$field] ?? '');
			
			if (in_array('email', $rule)) {
				if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$errors[$field] = "Неправильный email-адрес";
				}
			}
			
			if (in_array('required', $rule)) {
				if (empty($value)) {
					$errors[$field] = "Это поле обязательно";
				}
			}
		}
		
		return count($errors) ? [0, $errors] : [1];
	}
	*/


	public static function send($input)
	{
		global $modx;
		$cfg = Params::cfg();
		$errors = [];

		$mails = (gettype($input['to']) == 'array') ? $input['to'] : (explode(',', $input['to']));
		foreach($mails as $k=>$mail) {
			if (!filter_var(trim($mail), FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email {$mail} неверный";
                unset($mails[$k]);
			}
		}
		

		$host = $_SERVER['HTTP_HOST'];
        if (stripos($host, 'beget.tech') !== false) {
            $host = 'site.ru';
        }
		$from = $modx->getOption('emailsender', null, "shop@$host");

		$sitename = $modx->config['site_name'] ?: 'sitename';
        $subject = $input['subject'] ? str_replace('SITENAME', "«{$sitename}»", $input['subject']) : $sitename;


		$pls = [
			'subject' => $subject,
		];
		$pls = array_merge($pls, $input['pls'] ?? []);

		$body = Shop::parseTpl($cfg['order_report_tpl'], $pls);
		

		$modx->getService('mail', 'mail.modPHPMailer');
		$modx->mail->set( modMail::MAIL_BODY, $body );
		$modx->mail->set( modMail::MAIL_FROM, $from );
		$modx->mail->set( modMail::MAIL_SENDER, $from );
		$modx->mail->set( modMail::MAIL_FROM_NAME, $sitename);
		$modx->mail->set( modMail::MAIL_SUBJECT, $subject );
        $modx->mail->setHTML(true);
		foreach($mails as $k=>$mail) {
			$modx->mail->address('to', trim($mail));
		}
		

		if(!empty($input['files'])) {
			if (!empty($input['files_path'])) {
				foreach($input['files'] as $file) {
					$modx->mail->mailer->addAttachment($input['files_path'] . $file);
				}
			}
		} else if(!empty($_FILES)) {
			foreach($_FILES as $file) {
				if ($file['error'] === UPLOAD_ERR_OK) {
					$modx->mail->mailer->addAttachment($file['tmp_name'], $file['name'], 'base64', $file['type']);
				}
			}
		}
		

		if (!$modx->mail->send()) {
            $modx->log(modX::LOG_LEVEL_ERROR,'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
			$errors[] =  $modx->mail->mailer->ErrorInfo;
			return [0, $errors];
		}
		
		$modx->mail->reset();
		return [
            0 => 1,
			'mails' => $mails,
			'errors' => $errors
        ];
	}	


	
}
