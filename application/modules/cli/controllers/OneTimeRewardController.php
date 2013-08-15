<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Cli_OneTimeRewardController extends Api_GlobalController
{

	public function init()
	{
		if (PHP_SAPI != 'cli')
		{
			throw new Exception("Unsupported call!");
		}
	}

	/**
	 * All function calls should go here
	 */
	public function runAction()
	{
		try {
			$email = $_SERVER['argv'][2];
			$economy_id = $_SERVER['argv'][3];
			$amount = $_SERVER['argv'][4];
			$comment = $_SERVER['argv'][5];
			if( !($email && $economy_id && $amount && $comment) )
				throw new Exception("Expected Parameters: email economy_id amount comment");
			$userEmail = new User_Email();
			$userEmail->loadDataByUniqueFields(['email'=>$email]);
			if(!$userEmail->user_id)
				throw new Exception("Email not found.");
			$userId = $userEmail->user_id;

			Game_Transaction::run( $userId, $economy_id, 'ADHOC_REDEEMABLEPOINTS', array('custom_amount'=> $amount, 'comment' => $comment) );
			echo "Points awarded.\n";

		} catch( Exception $exception ) {
			echo $exception->__toString();
		}

		exit(0);
	}
}