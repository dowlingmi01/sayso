<?php
/**
 * This controller handles the authentication and delivery of the client-specific Starbar
 *
 * The principle point of entry is the index action which handles pre and post
 * install situations and routes the call to the correct Starbar action
 *
 * Scenarios:
 * - user logs in / installs app / restarts / returns to client site
 * - user logs in / installs app / restarts / returns to any other site
 * - user logs in / logs out / another user logs in
 * - user logs in / deletes cookies / user logs in
 * - user logs in / deletes cookies / another user logs in
 * @author davidbjames
 *
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Starbar_RemoteController extends Api_GlobalController
{
	public function init() {
		if (!$this->_init()) {
			// setup error module to use the API error module
			Zend_Controller_Front::getInstance()
				->getPlugin('Zend_Controller_Plugin_ErrorHandler')
				->setErrorHandlerModule(Api_Bootstrap::$moduleName);
			// make sure errors output via JSONP renderer
			Api_Registry::set('renderer', new Api_Plugin_JsonPRenderer());
		}
	}

	/**
	 * Main Starbar action used for:
	 * - determining which starbar is requested
	 * - routing to the correct starbar
	 * - determing starbar based on origin
	 */
	public function indexAction () {
		$this->_acceptIdParameter('starbar_id');
		$this->_validateRequiredParameters(array('starbar_id', 'user_id', 'user_key', 'auth_key'));

		$starbar = new Starbar();
		$this->view->starbar = $starbar;

		if ($this->starbar_id) {
			$starbar->loadData($this->starbar_id);
		} else {
			$starbar->loadDataByUniqueFields(array('short_name' => $this->short_name));
			$this->starbar_id = $starbar->getId();
		}

		$starbarUserMap = new Starbar_UserMap();
		$starbarUserMap->loadDataByUniqueFields(array('user_id' => $this->user_id, 'starbar_id' => $starbar->getId()));

		$starbar->setUserMap($starbarUserMap);

		if ($this->visibility) {
			$starbar->setVisibility($this->visibility);
		}

		if ($this->client_user_logged_in) {

			if ($starbar->short_name !== $this->client_name) {
				// customer site change!
				// @todo handle this scenario
			}
			if ($starbar->short_name === $this->client_name) {
				// we are on the customer's web site (must be if these params are present)
				// client vars: client_name, client_uuid, client_uuid_type

				$externalUserData = Db_Pdo::fetch('SELECT * FROM external_user WHERE user_id = ?', $this->user_id);
				// so verify that the user id matches the uuid
				// if NOT, then switch users
				if ($externalUserData['uuid'] !== $this->client_uuid) {
					// user change! (on same browser/computer)
					// create/update external user
					$externalUser = new External_User();
					$externalUser->uuid = $this->client_uuid; // unique
					$externalUser->uuid_type = $this->client_uuid_type;
					$externalUser->starbar_id = $starbar->getId(); // unique
					// note: we also treat this as a new "install":
					$externalUser->install_ip_address = $_SERVER['REMOTE_ADDR'];
					$externalUser->install_user_agent = $_SERVER['HTTP_USER_AGENT'];
					$externalUser->install_begin_time = new Zend_Db_Expr('now()');
					$externalUser->save(); // <-- inserts/updates based on uniques

					return $this->_forward(
						'post-install-deliver',
						null,
						null,
						array('external_user' => $externalUser)
					);
				}
			}
		}

		$user = new User();
		$user->loadData($this->user_id);
		$starbar->setUser($user);

		$gamer = Gamer::create($user->getId(), $starbar->getId());

		$game = Game_Starbar::getInstance();
		$game->checkin();
		$this->_request->setParam(Api_AbstractController::GAME, $game);

		return $this->_forward(
			$starbar->short_name,
			null,
			null,
			array('starbar' => $starbar)
		);
	}

	/**
	 * Lady Gaga Starbar
	 * @todo add to starbar table if we decide to use it
	 */
	public function gagaAction ()
	{
		$this->render();
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		return $this->_resultType(new Object(array('html' => $this->getResponse()->getBody())));
	}

	/**
	 * Generic Starbar
	 * @todo add to starbar table
	 */
	public function genericAction () {
	}

	/**
	 * Hello Music "Say.So Music Bar"
	 */
	public function hellomusicAction () {

		// get Starbar passed via index or post-install-deliver
		// and assign it to the view
		$starbar = $this->_getStarbarObject();
		$user = $starbar->getUser();
		$this->view->assign('starbar', $starbar);
		$this->view->assign('user', $user);

		$facebookSocial = new User_Social();
		$facebookSocial->loadByUserIdAndProvider($user->id, 'facebook');
		$this->view->assign('facebook_social', $facebookSocial);

		// render the view manually, we will pass it back in the JSON
		$this->render();

		// setup Hello Music specific data
		$starbar->setCssUrl('//' . BASE_DOMAIN . '/css/starbar-hellomusic.css');
		$starbar->setHtml($this->getResponse()->getBody());

		// return Starbar via JSON-P
		$this->_enableRenderer(new Api_Plugin_JsonPRenderer());
		return $this->_resultType($starbar);
	}

	/**
	 * Make sure Starbar has been determined in index (or post-install-deliver)
	 * @return Starbar
	 */
	private function _getStarbarObject () {
		if ($this->starbar && $this->starbar instanceof Starbar && $this->starbar->hasId()) {
			return $this->starbar;
		} else {
			throw new Exception('Remote starbar actions cannot be accessed directly. Use /starbar/remote with id or short_name.');
		}
	}

}
