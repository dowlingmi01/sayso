<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_ContentController extends Api_GlobalController
{
	// Render with JsonP by default
	protected $_usingJsonPRenderer = true;

	public function preDispatch()
	{
	    $this->_validateRequiredParameters(array('user_id', 'user_key', 'auth_key'));
	}   

    public function postDispatch()
    {
    	if ($this->_usingJsonPRenderer) {
	        $this->_enableRenderer(new Api_Plugin_JsonPRenderer());
	        $this->render();
	        return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
		} else {
		    // iframe loaded content, hence the need for all JS dependencies
            $this->view->headScript()->appendFile('/js/starbar/jquery-1.6.1.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery-ui-1.8.16.custom.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jscrollpane.min.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cookie.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.jeip.js');
            $this->view->headScript()->appendFile('/js/starbar/jquery.cycle.lite.js');
        	$this->view->headLink()->appendStylesheet('/css/starbar-generic.css');
        	// For init-remote.phtml
			$this->_assignStarbarToView();
		}
	}

    public function rewardsAction ()
    {
        $game = Game_Starbar::getInstance();
        $client = $game->getHttpClient();
        $client->setCustomParameters(array(
        	'attribute_friendly_id' => 'bdm-product-variant', 
        	'verbosity' => 9,
            'end_record' => 100
        ));
        $client->getNamedTransactionGroup('store');
        $data = $client->getData();
        $goods = new Collection(); 
        foreach ($data as $goodData) {
            $good = new Gaming_BigDoor_Good();
            $good->build($goodData);
            $good->accept($game);
            $goods[] = $good;
        }
//        Debug::exitNicely($goods);
        if ($this->test) {
            // get the raw reward data for dev purposes
            $this->_usingJsonPRenderer = false;
            $this->_enableRenderer(new Api_Plugin_JsonRenderer());
            if ($this->test === 'raw') {
                $result = $client->getData();
                return $this->_resultType(new Object($result));
            } else {
                foreach ($goods as $good) unset($good->object);
                return $this->_resultType($goods);
            }
            
        } else { 
            $this->view->rewards = $goods;
        }
        // http://local.sayso.com/starbar/hellomusic/rewards/user_key/r3nouttk6om52u18ba154mc4j4/user_id/46/auth_key/309e34632c2ca9cd5edaf2388f5fa3db
        
	}
	
    public function aboutSaysoAction ()
    {

    }

    // Embed a single SG poll. Expects "survey_id" passed via URL (GET)
    public function embedPollAction ()
    {
    	$this->_validateRequiredParameters(array('survey_id'));
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		$bundleOfJoy = $this->_getBundleOfJoy($this->survey_id);
		$this->view->assign('bundle_of_joy', $bundleOfJoy);

		// @todo point this to onboarding
		$shareLink = "http://www.say.so/";
		
		$shareText = "Poll time! Just took the '".$survey->title."' poll on Hello Music's Say.So Beat Bar";
		$facebookTitle = $survey->title;
		$facebookDescription = "Like Music? You can get the Beat Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
		$facebookCallbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared_type=poll&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $shareText, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
    }

    // Embed a single SG survey. Expects "survey_id" passed via URL (GET)
    public function embedSurveyAction ()
    {
    	$this->_validateRequiredParameters(array('survey_id'));

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		$bundleOfJoy = $this->_getBundleOfJoy($this->survey_id);
		$this->view->assign('bundle_of_joy', $bundleOfJoy);
	}

    public function surveyUnavailableAction ()
    {
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;
	}

    public function surveyDisqualifyAction ()
    {
    	$this->_replaceBundleOfJoyWithGetVariables();
    	$this->_validateRequiredParameters(array('survey_id'));
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		// @todo point this to onboarding
		$shareLink = "http://www.say.so/";
		// @todo share text to vary based on starbar_id?
		$shareText = "Survey time! Just filled out '".$survey->title."' on Hello Music's Say.So Beat Bar";
		$facebookTitle = $survey->title;
		$facebookDescription = "Like Music? You can get the Beat Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
		$facebookCallbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $shareText, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
	}

    public function surveyCompleteAction ()
    {
    	$this->_replaceBundleOfJoyWithGetVariables();
    	$this->_validateRequiredParameters(array('survey_id'));
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$this->view->assign('survey', $survey);

		// @todo point this to onboarding
		$shareLink = "http://www.say.so/";
		// @todo share text to vary based on starbar_id?
		$shareText = "Survey time! Just filled out '".$survey->title."' on Hello Music's Say.So Beat Bar";
		$facebookTitle = $survey->title;
		$facebookDescription = "Like Music? You can get the Beat Bar from Hello Music, give your opinion, earn points, get FREE gear, as well as exclusive access to deeply discounted music gear.";
		$facebookCallbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-post-result?shared_type=survey&shared_id=".$survey->id."&user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
		$this->_assignShareInfoToView($shareLink, $shareText, $facebookCallbackUrl, $facebookTitle, $facebookDescription);
	}

    public function surveyRedirectAction ()
    {
    	$this->_validateRequiredParameters(array('survey_id'));
    	// this page is fetched via an iframe, not ajax;
    	$this->_usingJsonPRenderer = false;

		$survey = new Survey();
		$survey->loadData($this->survey_id);

		$bundleOfJoy = $this->_getBundleOfJoy($survey->id);
		$this->_redirect("http://www.surveygizmo.com/s3/".$survey->external_id."/".$survey->external_key."?bundle_of_joy=".$bundleOfJoy);
	}

    // Fetches polls for the current user for display
    public function pollsAction ()
    {
		$newPolls = new SurveyCollection();
		$completePolls = new SurveyCollection();
		$archivePolls = new SurveyCollection();

		$newPolls->loadSurveysForStarbarAndUser(1, $this->user_id, 'poll', 'new');
		$completePolls->loadSurveysForStarbarAndUser(1, $this->user_id, 'poll', 'complete');
		$archivePolls->loadSurveysForStarbarAndUser(1, $this->user_id, 'poll', 'archive');

		$this->view->assign('new_polls', $newPolls);
		$this->view->assign('complete_polls', $completePolls);
		$this->view->assign('archive_polls', $archivePolls);

		$this->view->assign('count_new_polls', sizeof($newPolls));
		$this->view->assign('count_complete_polls', sizeof($completePolls));
		$this->view->assign('count_archive_polls', sizeof($archivePolls));
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
		
    public function userProfileAction ()
    {
		$user = new User();
		$user->loadData($this->user_id);
		$this->view->assign('user', $user);

		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

		$twitterSocial = new User_Social();
		$twitterSocial->loadByUserIdAndProvider($user->id, 'twitter');
		$this->view->assign('twitter_social', $twitterSocial);

		$userEmail = new User_Email();
		$userEmail->loadData($user->primary_email_id);
		$this->view->assign('user_email', $userEmail);

		// Assign the counts for surveys and polls
		$this->pollsAction();
		$this->surveysAction();
	}
	
	public function userShareAction()
	{
		
	}

    public function userLevelAction ()
    {

    }

    public function facebookConnectAction ()
    {
    	// this page is fetched in a popup, not ajax
    	$this->_usingJsonPRenderer = false;

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

		$callbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/facebook-connect?user_id=".$this->user_id."&user_key=".$this->user_key;

		if ($fbUser) {
			if ($this->user_key && (int)$this->user_id === (int)Api_UserSession::getInstance($this->user_key)->getId()) {
    			$userSocial = new User_Social();
    			$userSocial->user_id = $this->user_id;
    			$userSocial->provider = "facebook";
    			$userSocial->identifier = $fbUser;
    			$userSocial->save();
                
    			if (isset($fbProfile['username'])) {
    				$user = new User();
    				$user->loadData($this->user_id);
    				if (!$user->username) {
    					$user->username = $fbProfile['username'];
    					$user->save();
					}
				}

    			Game_Starbar::getInstance()->associateSocialNetwork($userSocial);
                
    			// Show user congrats notification
    			$message = new Notification_Message();
    			$message->loadDataByUniqueFields(array('short_name' => 'FB Account Connected'));

    			if ($message->id) {
    				$messageUserMap = new Notification_MessageUserMap();
    				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
				}
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

			$callbackUrl = "http://".BASE_DOMAIN."/starbar/hellomusic/twitter-connect-result?user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key;
			
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
			$this->_redirect("/starbar/hellomusic/twitter-connect-redirect?user_id=".$this->user_id."&user_key=".$this->user_key."&auth_key=".$this->auth_key);
		}

        $success = false;
        
        try {
			/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
			$connection = new TwitterOAuth($config->twitter->consumer_key, $config->twitter->consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

			/* Request access tokens from twitter */
			$accessToken = $connection->getAccessToken($request->getParam('oauth_verifier'));

			if ($this->user_key && (int) $this->user_id === (int) Api_UserSession::getInstance($this->user_key)->getId()) {
				$userSocial = new User_Social();
				$userSocial->user_id = $this->user_id;
				$userSocial->provider = "twitter";
				$userSocial->identifier = $accessToken['user_id'];
				$userSocial->username = $accessToken['screen_name'];
				$userSocial->save();
				
				Game_Starbar::getInstance()->associateSocialNetwork($userSocial);
				
    			// Show user congrats notification
    			$message = new Notification_Message();
    			$message->loadDataByUniqueFields(array('short_name' => 'TW Account Connected'));
    			
    			if ($message->id) {
    				$messageUserMap = new Notification_MessageUserMap();
    				$messageUserMap->updateOrInsertMapForNotificationMessageAndUser($message->id, $this->user_id, false);
				}
			}

			$success = true;
		} catch (Exception $e) {}

		$this->view->assign('success', $success);
	}

    public function onboardAction ()
    {

    }
    
    public function facebookPostResultAction ()
    {
        // @todo re-enable this validation as necessary and remove the if() condition below
        //$this->_validateRequiredParameters(array('post_id', 'shared_type'));
    	// this page is fetched in an iframe, not ajax
    	$this->_usingJsonPRenderer = false;

		$request = $this->getRequest();

        $success = false;

		/* Facebook wall post successful */
		if ($request->getParam('post_id')) {
			$success = true;
			Game_Starbar::getInstance()->share($this->shared_type, @$this->shared_id);
		}

		$this->view->assign('success', $success);
	}

    protected function _getBundleOfJoy ($surveyId)
    {
    	$bundleOfJoy = "";
    	$sep = "^|^"; // seperator between variables
    	$eq = "^-^"; // seperator between variable name and value
    	// e.g. user_id^-^1^|^user_key^-^123

		$request = $this->getRequest();

    	// Include easyXDM variables so they can be used at the end of the survey for iframe communication
    	// Namely, used by survey-complete and survey-disqualify
    	$xdm_c = $request->getParam('xdm_c');
    	$xdm_e = $request->getParam('xdm_e');
    	$xdm_p = $request->getParam('xdm_p');

    	$bundleData = array(
    		'user_id' => $this->user_id,
    		"user_key" => $this->user_key,
    		"auth_key" => $this->auth_key,
    		"survey_id" => $surveyId,
    		"xdm_c" => $xdm_c,
    		"xdm_e" => $xdm_e,
    		"xdm_p" => $xdm_p,
    	);

    	foreach ($bundleData AS $name => $value) {
    		if ($bundleOfJoy) $bundleOfJoy .= $sep;
    		$bundleOfJoy .= $name . $eq . $value;
		}

    	return $bundleOfJoy;
	}

	protected function _replaceBundleOfJoyWithGetVariables()
	{
		$queryString = "";
		$request = $this->getRequest();

        if ($request->getParam('bundle_of_joy')) {
            foreach (explode('^|^', $request->getParam('bundle_of_joy')) as $keyPair) {
                if ($queryString) $queryString .= "&";
                else $queryString = "?";

                $parts = explode('^-^', $keyPair);
                $queryString .= $parts[0] . "=" . $parts[1];
            }
            $currentPage = $_SERVER['REQUEST_URI'];
			if (strpos($currentPage, "?") !== false) $currentPage = reset(explode("?", $currentPage));

			$this->_redirect('http://' . BASE_DOMAIN . $currentPage . $queryString);
        }
	}

	protected function _assignShareInfoToView($shareLink = null, $shareText = null, $facebookCallbackUrl = null, $facebookTitle = null, $facebookDescription = null)
	{
        $config = Api_Registry::getConfig();
		
		$this->view->assign('facebook_app_id', $config->facebook->app_id);
		$this->view->assign('facebook_share_image_url', $config->facebook->share_image_url);
		$this->view->assign('twitter_share_via_user', $config->twitter->share_via_user);
		$this->view->assign('twitter_share_related_users', $config->twitter->share_related_users);
		$this->view->assign('twitter_share_hashtags', $config->twitter->share_hashtags);

        $this->view->assign('share_link', $shareLink);

		$facebookShareCaption = $shareText;
		$twitterShareText = $shareText." -";
		$this->view->assign('facebook_share_caption', $facebookShareCaption);
		$this->view->assign('twitter_share_text', $twitterShareText);

		$this->view->assign('facebook_share_callback_url', $facebookCallbackUrl);
		$this->view->assign('facebook_title', $facebookTitle);
		$this->view->assign('facebook_description', $facebookDescription);
	}
	
	protected function _assignStarbarToView()
	{
		if (Registry::isRegistered('starbar')) {
		    $starbar = Registry::getStarbar();
		} else {
		    $starbar = new Starbar();
			$starbar->loadDataByUniqueFields(array('auth_key' => $this->auth_key));
		}

		$this->view->assign('starbar', $starbar);
	}
}
