<?php


class User_PasswordChangeRequest extends Record
{
	protected $_tableName = 'user_password_change_request';

	public function sendToUser(User_Email $email, $starbarId) {
		$this->loadDataByUniqueFields(["user_id" => $email->user_id, "has_been_fulfilled" => null]);
		if (!$this->id) { // if it already exists, it is loaded, so we are done
			$this->verification_code = $this->_getRandomValidationCode(6);
			$this->user_id = $email->user_id;
			$this->save();
		}

		if ($this->id) {
			try {
				$productName = ($starbarId == 4 ? 'Machinima | Recon' : 'Say.So');

				$htmlmessage = "<p>We have received a request to reset your password. Below is the verification code you need to enter to confirm the password change:</p>";
				$htmlmessage .= "<h1>".$this->verification_code."</h1>";
				$htmlmessage .= "<p>Thank you for using ".$productName."</p>";
				$htmlmessage .= "<p>- The ".$productName." Team</p>";

				$message = "We have received a request to reset your password. Below is the verification code you will need to enter to confirm the password change:\n";
				$message .= $this->verification_code."\n\n";
				$message .= "Thank you for using ".$productName."\n";
				$message .= "\n";
				$message .= "- The ".$productName." Team\n";

				$mail = new Mailer();
				$mail->setFrom('support@say.so')
					->addTo($email->email)
					->setSubject($productName . ' - Password Change Request');
				$mail->setBodyMultilineText($message);
				$mail->setBodyHtml($htmlmessage);
				$mail->send(new Zend_Mail_Transport_Smtp());
			} catch (Exception $e) {
				quickLog($message);
				return false;
			}

			return true;
		}

		return false;
	}

	private function _getRandomValidationCode($length) {
		$chars = 'abcdefghijkmnopqrstuvwxyz23456789';

		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $str;
	}
}
