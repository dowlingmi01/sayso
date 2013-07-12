<?php
/**
 * Class Email
 *
 * Handles sending emails.
 */

class Email {

	public function send($to, $from, $message, $subject = "Say.So contact request", $messageMeta = NULL)
	{
		try {
			$htmlMessage = $message;

			$textMessage = $message . PHP_EOL;

			if ($messageMeta)
			{
				$htmlMessage .= "<br />";
				$htmlMessage .= "<p>Message meta</p>";
				$htmlMessage .= $messageMeta;

				$textMessage .= "Message meta" . PHP_EOL;
				$textMessage .= $messageMeta . PHP_EOL;
			}

			$mail = new Mailer();
			$mail->setFrom($from)
				->addTo($to)
				->setSubject($subject);
			$mail->setBodyMultilineText($textMessage);
			$mail->setBodyHtml($htmlMessage);
			$mail->send(new Zend_Mail_Transport_Smtp());
		} catch (Exception $e) {
			quickLog($textMessage);
			return false;
		}
	}
}