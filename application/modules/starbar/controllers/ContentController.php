<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_ContentController extends Api_GlobalController
{
	// Render with JsonP by default
	protected $_usingJsonPRenderer = true;

	public function preDispatch()
	{
		$request = $this->getRequest();
		/*
		* bundle_of_joy is the variable sent to and from SurveyGizmo.
		* It is a 'manually' serialized list of variables and values, where ^|^ seperates variables
		* from each other, and ^-^ seperates variable names from variable values. Example:
		* GET version: ?user_id=1&user_key=123&auth_key=abc
		* bundle_of_joy version: ?bundle_of_joy=user_id^-^1^|^user_key^-^123^|^auth_key^-^abc
		*/
        if ($request->getParam('bundle_of_joy')) {
            foreach (explode('^|^', $request->getParam('bundle_of_joy')) as $keyValue) {
                $parts = explode('^-^', $keyValue);
                $request->setParam($parts[0], $parts[1]);
            }
        }
        
	    if (!($this->user_id && $this->user_key)) {
	    	echo "No user id or key.";
	    	exit;
		}
	}   

    public function postDispatch()
    {
    	if ($this->_usingJsonPRenderer) {
	        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
	        $this->render();
	        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
		} else {
            $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
        	$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
		}
}

    public function aboutSaysoAction ()
    {

    }

    // Embed a single SG poll. Expects "survey_id" passed via URL (GET)
    public function embedPollAction ()
    {
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();

		$surveyId = (int) $request->getParam('survey_id');

		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			$this->view->assign('survey', $survey);

			$bundleOfJoy = $this->_getBundleOfJoy($surveyId);
			$this->view->assign('bundle_of_joy', $bundleOfJoy);
		}
    }

    // Embed a single SG survey. Expects "survey_id" passed via URL (GET)
    public function embedSurveyAction ()
    {
		$request = $this->getRequest();

		$surveyId = (int) $request->getParam('survey_id');

		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			$this->view->assign('survey', $survey);

			$bundleOfJoy = $this->_getBundleOfJoy($surveyId);
			$this->view->assign('bundle_of_joy', $bundleOfJoy);
		}
	}

    public function surveyUnavailableAction ()
    {
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;
	}

    public function surveyDisqualifyAction ()
    {
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();
		$surveyId = $request->getParam('survey_id');
		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			$this->view->assign('survey', $survey);
		}
	}

    public function surveyCompleteAction ()
    {
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();
		$surveyId = $request->getParam('survey_id');
		if ($surveyId) {
			$survey = new Survey();
			$survey->loadData($surveyId);

			$this->view->assign('survey', $survey);
		}
	}

    // Fetches polls for the current user for display
    public function pollsAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForStarbarAndUser(1, $this->user_id, 'poll', 'new');
		$completeSurveys->loadSurveysForStarbarAndUser(1, $this->user_id, 'poll', 'complete');
		$archiveSurveys->loadSurveysForStarbarAndUser(1, $this->user_id, 'poll', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);

		$this->view->assign('count_new_surveys', sizeof($newSurveys));
		$this->view->assign('count_complete_surveys', sizeof($completeSurveys));
		$this->view->assign('count_archive_surveys', sizeof($archiveSurveys));

		$this->view->assign('user_id', $this->user_id);
		$this->view->assign('user_key', $this->user_key);
	}

    // Fetches surveys for the current user for display
    public function surveysAction ()
    {
		$newSurveys = new SurveyCollection();
		$completeSurveys = new SurveyCollection();
		$archiveSurveys = new SurveyCollection();

		$newSurveys->loadSurveysForStarbarAndUser(1, $this->user_id, 'survey', 'new');
		$completeSurveys->loadSurveysForStarbarAndUser(1, $this->user_id, 'survey', 'complete');
		$archiveSurveys->loadSurveysForStarbarAndUser(1, $this->user_id, 'survey', 'archive');

		$this->view->assign('new_surveys', $newSurveys);
		$this->view->assign('complete_surveys', $completeSurveys);
		$this->view->assign('archive_surveys', $archiveSurveys);

		$this->view->assign('count_new_surveys', sizeof($newSurveys));
		$this->view->assign('count_complete_surveys', sizeof($completeSurveys));
		$this->view->assign('count_archive_surveys', sizeof($archiveSurveys));
	}

    public function onboardingAction ()
    {

		}

    public function promosAction ()
    {

    }
		
		public function rewardsAction ()
    {

		}

    public function userProfileAction ()
    {
		$user = new User();
		$user->loadData($this->user_id);
		$this->view->assign('user', $user);

		$this->view->assign('user_key', $this->user_key);

		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

		$twitterSocial = new User_Social();
		$twitterSocial->loadByUserIdAndProvider($user->id, 'twitter');
		$this->view->assign('twitter_social', $twitterSocial);

		$userEmail = new User_Email();
		$userEmail->loadData($user->primary_email_id);
		$this->view->assign('user_email', $userEmail);
	}

    public function userLevelAction ()
    {

    }

    public function facebookConnectAction ()
    {
    	// this page is fetched in a popup, not ajax
    	$this->_usingJsonPRenderer = false;
        
        $config = Api_Registry::getConfig();
		$request = $this->getRequest();

		$facebook = new Facebook(array(
			'appId'  => $config->facebook->app_id,
			'secret' => $config->facebook->secret
		));

		$user = $facebook->getUser();

		if ($user) {
			try {
				$user_profile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			}
		}

		$callbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-connect?user_id=".$this->user_id."&user_key=".$this->user_key;
		
		if ($user) {
			if ($this->user_id === Api_UserSession::getInstance($this->user_key)->getId()) {
    			$userSocial = new User_Social();
    			$userSocial->user_id = $request->getParam('user_id');
    			$userSocial->provider = "facebook";
    			$userSocial->identifier = $user;
    			if (isset($user_profile['username']))
    				$userSocial->username = $user_profile['username'];
    			$userSocial->save();
			}
		} else {
			$this->_redirect($facebook->getLoginUrl());
		}
	}

    public function twitterConnectRedirectAction ()
    {
        $config = Api_Registry::getConfig();

        $success = false;
        
        try {
			/* Build TwitterOAuth object with client credentials. */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret);

			$callbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/twitter-connect-result?user_id=".$this->user_id."&user_key=".$this->user_key;
			
			/* Get temporary credentials and set the callback URL. */
			$twitterRequestToken = $connection->getRequestToken($callbackUrl);

			/* Save temporary credentials to session. */
			$_SESSION['oauth_token'] = $twitterRequestToken['oauth_token'];
			$_SESSION['oauth_token_secret'] = $twitterRequestToken['oauth_token_secret'];

			if ($twitterRequestToken['oauth_callback_confirmed'] == 'true') $success = true;
		} catch (Exception $e) {}

		if ($success) {
			$this->_redirect("http://api.twitter.com/oauth/authorize?oauth_token=".$twitterRequestToken['oauth_token']);
		}
	}

    public function twitterConnectResultAction ()
    {
    	// this page is fetched in a popup, not ajax
    	$this->_usingJsonPRenderer = false;

        $config = Api_Registry::getConfig();
		$request = $this->getRequest();

		/* If the oauth_token is old redirect to the connect page. */
		if ($request->getParam('oauth_verifier') && $_SESSION['oauth_token'] !== $request->getParam('oauth_token')) {
			$this->_redirect('/starbar/hellomusic/twitter-connect-redirect');
		}

        $success = false;
        
        try {
			/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

			/* Request access tokens from twitter */
			$accessToken = $connection->getAccessToken($request->getParam('oauth_verifier'));

			if ($this->user_id === Api_UserSession::getInstance($this->user_key)->getId()) {
				$userSocial = new User_Social();
				$userSocial->user_id = $request->getParam('user_id');
				$userSocial->provider = "twitter";
				$userSocial->identifier = $accessToken['user_id'];
				$userSocial->username = $accessToken['screen_name'];
				$userSocial->save();
			}

			$success = true;
		} catch (Exception $e) {}

		$this->view->assign('success', $success);
	}

    private function _getBundleOfJoy ($surveyId)
    {
    	$bundleOfJoy = "";
    	$sep = "^|^"; // seperator between variables
    	$eq = "^-^"; // seperator between variable name and value
    	// e.g. user_id^-^1^|^user_key^-^123
    	
    	$bundleOfJoy .= "user_id" . $eq . $this->user_id;
    	$bundleOfJoy .= $sep;
    	$bundleOfJoy .= "user_key" . $eq . $this->user_key;
    	$bundleOfJoy .= $sep;
    	$bundleOfJoy .= "auth_key" . $eq . $this->auth_key;
    	$bundleOfJoy .= $sep;
    	$bundleOfJoy .= "survey_id" . $eq . $surveyId;
    	
    	return $bundleOfJoy;
		}
		
		
    public function onboardAction ()
    {

    }
		
}
