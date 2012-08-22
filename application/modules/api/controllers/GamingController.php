<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';
/**
 * Sayso "mini" Gaming API
 *
 * - user_key is required on most calls since we get the user's
 * 	 gaming id from their user_id
 *
 * @author davidbjames
 *
 */
class Api_GamingController extends Api_GlobalController
{

	public function init()
	{

	}

	public function indexAction()
	{
	}

	// http://local.sayso.com/api/gaming/user-profile/starbar_id/1/user_id/46/user_key/r3nouttk6om52u18ba154mc4j4

	public function getGameAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key'));
		$game = Game_Starbar::getInstance();
		$game->loadGamerProfile();
		return $this->_resultType($game);
	}

	/**
	 * Get RAW user profile from Big Door
	 * - use this for testing only. see next method for standard use
	 *
	 */
	public function userProfileRawAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key'));
		$game = Game_Starbar::getInstance();
		$gamer = $game->getGamer(false /* don't load profile */);
		$client = $game->getHttpClient();
		$client->getEndUser($gamer->getGamingId());
		return $this->_resultType($client->getData(true));
	}

	/**
	 * Get User profile via our own objects
	 *
	 */
	public function userProfileAction () {
		if ($this->gaming_id && $this->starbar_id) {
			$gamer = Gamer::createByGamingId($this->gaming_id);
			$game = Game_Starbar::create($gamer, $this->_request);
			$game->loadGamerProfile();
		} else {
			$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
			$gamer = Gamer::create($this->user_id, $this->starbar_id);
			$game = Game_Starbar::create($gamer, $this->_request);
			$gamer->loadProfile($game->getHttpClient(), $game);
		}
		return $this->_resultType($gamer);
	}

	/**
	 * Get levels THIS IS STILL IN PROGRESS
	 *
	 */
	public function levelsAction () {
		throw new Exception('api/gaming/levels method is hard-coded to Hello Music economy. Fix it to use the HTTP Client from the current Game class');
		$client = new Gaming_BigDoor_HttpClient('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2');
		$client->getNamedLevelCollection(43352);
		$data = $client->getData();
		$levels = new ItemCollection();
		foreach ($data->named_levels as $levelData) {
			$level = new Gaming_BigDoor_Level();
			$level->setId($levelData->id);
			$level->title = $levelData->end_user_title;
			$level->description = $levelData->end_user_description;
			$level->urls = Gaming_BigDoor_Url::buildUrlCollection($levelData->urls);
			$level->timestamp = $levelData->created_timestamp;
			$level->ordinal = $levelData->threshold;
			$levels[] = $level;
		}
		return $this->_resultType($levels);
//		return $this->_resultType($client->getData(true)); // raw data
	}

	public function getGoodAction () {
		$this->_validateRequiredParameters(array('good_id', 'user_key'));
		$game = Game_Starbar::getInstance();
		$client = $game->getHttpClient();

		$client->getNamedGood($this->good_id);
		$data = $client->getData();
		$good = new Gaming_BigDoor_Good();
		$good->build($data);
		$good->accept($game);
		return $this->_resultType($good);
	}

	/**
	 *
	 * @return Gaming_BigDoor_Good
	 */
	public function getGoodFromStoreAction () {
		$this->_validateRequiredParameters(array('good_id', 'user_key'));
		$goods = $this->getGoodsFromStoreAction();
		$good = $goods->getItem($this->good_id);
		if (isNull($good)) {
			throw new Api_Exception(Api_Error::create(Api_Error::GAMING_ERROR, 'Good ID ' . $this->good_id . ' not found in store'));
		}
		return $this->_resultType($good);
	}

	/**
	 *
	 * @return ItemCollection
	 */
	public function getGoodsFromStoreAction () {
		$this->_validateRequiredParameters(array('user_key'));
		$game = Game_Starbar::getInstance();
		$cache = Api_Cache::getInstance('BigDoor_getNamedTransactionGroup_store_' . $game->getEconomy()->getKey(), Api_Cache::LIFETIME_WEEK);
		if ($cache->test()) {
			$data = $cache->load();
		} else {
			$client = $game->getHttpClient();
			$client->setCustomParameters(array(
				'attribute_friendly_id' => 'bdm-product-variant',
				'verbosity' => 9,
				'max_records' => 100
			));
			$client->getNamedTransactionGroup('store');
			$data = $client->getData();
			$cache->save($data);
		}

		$goods = new ItemCollection();
		foreach ($data as $goodData) {
			$good = new Gaming_BigDoor_Good();
			$good->setPrimaryCurrencyId($game->getPurchaseCurrencyId());
			$good->build($goodData);
			$good->accept($game);
			$goods[] = $good;
		}

		return $this->_resultType($goods);
	}

	static public function prepareGoodsForGamer(ItemCollection $goods, Gaming_User $gamer) {
		$tokens = new ItemCollection();
		$purchasedGoods = new ItemCollection();
		$availableGoods = new ItemCollection();
		$soldOutGoods = new ItemCollection();
		$results = new ItemCollection();
		$goods->orderBy('title');
		$goods->orderBy('cost');
		foreach ($goods as $good) {
			if ($good->isForCurrentEnvironment()) {
				if ($good->isToken()) {
					$tokens->addItem($good);
				} elseif ($gamer->getGoods()->hasItem($good->getId())) {
					$purchasedGoods->addItem($good);
				} elseif ($good->inventory_sold < $good->inventory_total) {
					$availableGoods->addItem($good);
				} else {
					$soldOutGoods->addItem($good);
				}
			}
		}
		$tokens->orderBy('id', 'desc'); // reverse the tokens so latest tokens are first
		foreach ($tokens as $good) $results->addItem($good);
		foreach ($availableGoods as $good) $results->addItem($good);
		foreach ($purchasedGoods as $good) $results->addItem($good);
		foreach ($soldOutGoods as $good) $results->addItem($good);

		return $results;
	}

	public function shareAction () {
		$this->_validateRequiredParameters(array('shared_type', 'shared_id', 'social_network', 'user_key'));

		Game_Starbar::getInstance()->share($this->shared_type, $this->social_network, @$this->shared_id);
		return $this->_resultType(true);
	}

	public function checkinAction () {
		$this->_validateRequiredParameters(array('user_key'));
		Game_Starbar::getInstance()->checkin();
		return $this->_resultType(true);
	}

	public function resetAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));
		$newGamer = Gamer::reset($this->user_id, $this->user_key, $this->starbar_id);
		return $this->_resultType($newGamer);
	}

	public function testBigDoorAction () {
		$this->_validateRequiredParameters(array('user_id', 'user_key', 'starbar_id'));

		$gamer = Gamer::create($this->user_id, $this->starbar_id);
		Game_Starbar::create($gamer, $this->_request)->trigger();

		$user = new User();
		$user->loadData($this->user_id);

		return $this->_resultType($user);
	}
}


