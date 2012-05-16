<?php
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_InstallController extends Api_GlobalController {
	public function hellomusicAction() {
		$userHasPassword = false;
		
		$externalUser = new External_User();
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields( array('short_name' => $this->client_name));
		$externalUser->starbar_id = $starbar->id;
		$externalUser->uuid = $this->client_uuid;
		$externalUser->uuid_type = $this->client_uuid_type;
		$externalUser->email = $this->client_email;
		$externalUser->loadOrCreate();
		
		if( $externalUser->user_id ) {
			$user = new User();
			$user->loadData( $externalUser->user_id );
			if( $user->password )
				$userHasPassword = true;
		}
		$isPilotUser = false;
		if( $externalUser->client_data ) {
			$clientData = Zend_Json::decode($externalUser->client_data);
			if( $clientData["hm_pilot_user"] ) {
				$isPilotUser = true;
				$this->view->assign('onboarding_type', 'pilot_user');
			}
		}
		if( !$isPilotUser && preg_match('/^s-/', $this->install_origination)  )
			$this->view->assign('onboarding_type', 'share');
		else if( !$isPilotUser )
			$this->view->assign('onboarding_type', 'new_user');
		
		$install = new User_Install();
		$install->external_user_id = $externalUser->id;
		$install->location = $this->location_token;
		$install->token = User_Key::getRandomToken();
		$install->ip_address = $_SERVER['REMOTE_ADDR'];
		$install->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$install->user_agent_supported = ($this->user_agent_supported == 'true');
		$install->origination = $this->install_origination;
		$install->url = $this->install_url;
		$install->referrer = $this->referrer;
		$install->save();
		
		$this->view->assign('token', $install->token);
		$this->view->assign('client_email', $this->client_email);
		$this->view->assign('user_has_password', $userHasPassword);

		$this->render();
		$body = $this->getResponse()->getBody();
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		return $this->_resultType(new Object(array('html' => $body)));
	}
	public function snakkleAction() {
		return $this->emailBasedInstall();
	}
	public function movieAction() {
		return $this->emailBasedInstall();
	}
	public function emailBasedInstall() {
		$install = new User_Install();
		$starbar = new Starbar();
		$starbar->loadDataByUniqueFields( array('short_name' => $this->client_name));
		$install->starbar_id = $starbar->id;
		$install->location = $this->location_token;
		$install->token = User_Key::getRandomToken();
		$install->ip_address = $_SERVER['REMOTE_ADDR'];
		$install->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$install->user_agent_supported = ($this->user_agent_supported == 'true');
		$install->origination = $this->install_origination;
		$install->url = $this->install_url;
		$install->referrer = $this->referrer;
		$install->save();

		if( preg_match('/^s-/', $this->install_origination)  )
			$this->view->assign('onboarding_type', 'share');
		else
			$this->view->assign('onboarding_type', 'new_user');
		
		$this->view->assign('token', $install->token);

		$this->render();
		$body = $this->getResponse()->getBody();
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		return $this->_resultType(new Object(array('html' => $body)));
	}
	public function userPasswordAction() {
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());

		$this->_validateRequiredParameters(array('install_token'));
		$install = new User_Install();
		$install->loadDataByUniqueFields(array('token'=>$this->install_token));
		if( $install->external_user_id ) {
			$externalUser = new External_User();
			$externalUser->loadData($install->external_user_id);
			$user = $externalUser->getUser($this->user_password);
		} else {
			$this->user_email = strtolower($this->user_email);
			$email = new User_Email();
			$email->loadDataByUniqueFields(array('email'=>$this->user_email));
			$user = new User();
			if( $email->user_id ) {
				$user->loadData( $email->user_id );
				if( $user->password )
					$user->validatePassword($this->user_password);
				else {
					$user->setPlainTextPassword($this->user_password);
					$user->save();
				}
			} else {
				$user->setPlainTextPassword($this->user_password);
				$user->setEmail( $email );
				$user->save();
			}
			$install->user_id = $user->id;
		}
		$install->click_ts = new Zend_Db_Expr('now()');
		$install->save();
		$userKey = new User_Key();
		$userKey->user_id = $user->getId();
		$userKey->token = $this->install_token;
		$userKey->origin = User_Key::ORIGIN_INSTALL;
		$userKey->save();
		return $this->_resultType(true);
	}
	public function extensionAction() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if( strpos($user_agent, 'MSIE') ) {
			$prefix = 'ie/';
			$suffix = '-Setup.exe';
		} else if( strpos($user_agent, 'Chrome') ) {
			$prefix = 'chrome/';
			$suffix = '.crx';
		} else if( strpos($user_agent, 'Safari') ) {
			$prefix = 'safari/';
			$suffix = '.safariextz';
		} else if( strpos($user_agent, 'Firefox') ) {
			$prefix = 'firefox/';
			$suffix = '.xpi';
		}
		
		$env = Registry::getPseudoEnvironmentName();
		
		$fileName = ($env === 'PROD' ? 'Say.So' : 'SaySo-' . $env);

		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
		setcookie('user_key', $this->install_token, time()+(86400*365), '/', null, null, true);
		$this->_redirect('/install/'.$prefix.$fileName.$suffix);
	}
	public function postInstallAction() {
		if( $this->user_key ) {
			$install = new User_Install();
			$install->loadDataByUniqueFields(array('token'=>$this->user_key));
			if( $install->id ) {
				$starbar = new Starbar();
				
				if( $install->starbar_id )
					$starbar->loadData($install->starbar_id);
				else {
					$externalUser = new External_User();
					$externalUser->loadData($install->external_user_id);
					$starbar->loadData($externalUser->starbar_id);
				}
				
				$url = Client::getInstance($starbar->short_name)->getPostInstallURL();
			}
		}
		if( ! $url ) {
			$url = "http://say.so/welcome";
		}
		$this->_redirect($url);
	}
}
