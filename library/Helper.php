<?php
/*
 * Generic helper functions that transcend modules.
 */

class Helper {

	/**
	 * Get a random string
	 *
	 * Can be used to generate salts, passwords, and other random strings of varving lengths
	 *
	 * @param int $len
	 * @pram bool $specialChars
	 * @return string
	 */
	static public function getRandomToken($len = 32, $lCase = FALSE ,$specialChars = FALSE) {
			$characterList = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			if ($specialChars)
				$characterList .= "!@#$%&*?";
			if ($lCase)
				$characterList .= "abcdefghijklmnopqrstuvwxyz";
			$string = "";
			for ($i=0; $i < $len; $i++) {
				$string .= $characterList{mt_rand(0, (strlen($characterList) - 1))};
			}
			return $string;
	}
}

?>
