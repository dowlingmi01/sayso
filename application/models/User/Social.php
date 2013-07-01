<?php


class User_Social extends Record
{
	protected $_tableName = 'user_social';

	/* provider = 'facebook' or 'twitter'
	* returns true if there is a connection, false otherwise
	*/
	public function loadByUserIdAndProvider($userId, $provider) {
		$userSocialFetch = null;

		if ($userId && $provider)
			$userSocialFetch = Db_Pdo::fetch("SELECT * FROM user_social WHERE provider = ? AND user_id = ?", $provider, $userId);

		if ($userSocialFetch) {
			$this->build($userSocialFetch);
			return true;
		}

		return false;
	}

	/**
	 * Static function to connect Facebook to a user acct.
	 *
	 * <p>Hits the Facebook API to get the user id.</p>
	 * <p>Saves the FB id to the user_social table</p>
	 * <p>Atempts to set the say.so first name to the FB first name</p>
	 * <p>Runs the game transaction to credit the user's account for the action</p>
	 * <p>Updates the notifications.</p>
	 *
	 * @param int $userId
	 * @param int $starbarId
	 * @return bool|string|\Exception
	 */
	public static function connectFacebook($userId, $starbarId)
	{
		$config = Api_Registry::getConfig();

		$facebook = new Facebook(array(
			'appId'  => $config->facebook->app_id,
			'secret' => $config->facebook->secret
		));

		$fbUser = $facebook->getUser();

		if ($fbUser) {
			try {
				$fbProfile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$fbUser = null;
			}
		}

		if ($fbUser && $userId) {
			$userSocial = new self;
			$userSocial->user_id = $userId;
			$userSocial->provider = "facebook";
			$userSocial->identifier = $fbUser;
			$userSocial->save();

			/*
			//removed until username edit feature is active
			if (isset($fbProfile['first_name'])) {
				$user = new User();
				$user->loadData($userId);
				if (!$user->username) {
					$user->username = $fbProfile['first_name'];
					$user->save();
				}
			}
			*/

			try {
				Game_Transaction::associateSocialNetwork($userId, $starbarId, $userSocial );
			} catch (Exception $e) {
				return $e;
			}

			// Show user congrats notification
			$message = new Notification_Message();
			$message->loadByShortNameAndStarbarId('FB Account Connected', $starbarId);

			if ($message->id) {
				$messageUserMap = new Notification_MessageUserMap();
				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, FALSE);
			}
		} else
			return $facebook->getLoginUrl();
		return TRUE;
	}

	/**
	 * NEEDS TO BE DEVELOPED
	 */
	public static function connectTwitter($userId, $starbarId, $oauth)
	{
		$config = Api_Registry::getConfig();

		try {
			/* Create TwitterOAuth object with app key/secret and token key/secret from default phase */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret, $oauth->oauth_token, $oauth->oauth_token_secret);

			/* Request access tokens from twitter */
			$accessToken = $connection->getAccessToken();

			if ($userId) {
				$userSocial = new User_Social();
				$userSocial->user_id = $userId;
				$userSocial->provider = "twitter";
				$userSocial->identifier = $accessToken['user_id'];
				$userSocial->username = $accessToken['screen_name'];
				$userSocial->save();

				Game_Transaction::associateSocialNetwork( $userId, $starbarId, $userSocial );

				// Show user congrats notification
				$message = new Notification_Message();
				$message->loadByShortNameAndStarbarId('TW Account Connected', $starbarId);

				if ($message->id) {
					$messageUserMap = new Notification_MessageUserMap();
					$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $userId, false);
				}
			}
			return;
		} catch (Exception $e) {
			return $e;
		}
	}

	public static function getTwiterOauthToken($callbackUrl)
	{
		$config = Api_Registry::getConfig();

		try {
			/* Build TwitterOAuth object with client credentials. */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret);

			$callbackUrl = $callbackUrl;

			/* Get temporary credentials and set the callback URL. */
			$twitterRequestToken = $connection->getRequestToken($callbackUrl);

			/* Save temporary credentials to cache. */
			$oauth['token'] = $twitterRequestToken['oauth_token'];
			$oauth['token_secret'] = $twitterRequestToken['oauth_token_secret'];
			return $oauth;

		} catch (Exception $e) {
			return $e;
		}
	}

}
