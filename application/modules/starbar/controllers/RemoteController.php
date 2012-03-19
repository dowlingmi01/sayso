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
		$this->_validateRequiredParameters(array('starbar_id', 'user_id'));

		$starbar = new Starbar();
		$starbar->loadData($this->starbar_id);
		$this->view->starbar = $starbar;

		$starbarUserMap = new Starbar_UserMap();
		$starbarUserMap->loadDataByUniqueFields(array('user_id' => $this->user_id, 'starbar_id' => $starbar->getId()));

		$starbar->setUserMap($starbarUserMap);

		if ($this->visibility) {
			$starbar->setVisibility($this->visibility);
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
