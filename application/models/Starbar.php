<?php
/**
 * Class representing a Starbar
 *
 * @author davidbjames
 *
 */
class Starbar extends Record
{
	protected $_tableName = 'starbar';

	protected $_uniqueFields = array('short_name' => '');

	protected $_economy;

	protected $_user;

	protected $_userMap;

	protected $_cssUrl = '';

	protected $_visibility = '';

	protected $_html = '';

	/**
	* Each time the economy field is set, load economy data from the db
	*/
    protected function _filter ($value, $property = '') {
    	if( $property == 'economy_id' && $value) {
			$this->_economy = new Economy();
			$this->_economy->loadData($value);
    	}
        return parent::_filter($value, $property);
    }

	public function init() {
		if (Registry::isRegistered('starbar')) {
			throw new Exception('Starbar already created and registered in Registry as \'starbar\'');
		}
		Registry::set('starbar', $this);
		parent::init();
	}

	public function setEconomy (Economy $economy) {
		$this->_economy = $economy;
	}

	/**
	 * @return Economy
	 */
	public function getEconomy () {
		return $this->_economy;
	}

	public function setUser (User $user) {
		$this->_user = $user;
	}

	/**
	 * @return User
	 */
	public function getUser () {
		return $this->_user;
	}

	/**
	 * @param Starbar_UserMap $userMap
	 */
	public function setUserMap (Starbar_UserMap $userMap) {
		$this->_userMap = $userMap;
	}

	/**
	 * Each Starbar has custom CSS which should
	 * be loaded ahead of injection of the markup.
	 * This is handled in the browser app
	 *
	 * @param string $cssUrl
	 */
	public function setCssUrl ($cssUrl) {
		$this->_cssUrl = $cssUrl;
	}

	/**
	 * The actual markup which gets injected into
	 * the DOM of the containing page
	 *
	 * @param string $html
	 */
	public function setHtml ($html) {
		$this->_html = $html;
	}



	public function setVisibility ($visibility) {
		$this->_visibility = $visibility;
	}

	public function getVisibility () {
		return $this->_visibility;
	}

	public function getCommaDelimitedListOfUsers() {
		if (!$this->id) return;

		$sql = "
			SELECT sum.user_id
			FROM starbar_user_map sum
			WHERE sum.starbar_id = ?
		";

		$arrayOfUserIds = Db_Pdo::fetchColumn($sql, $this->id);
		if (sizeof($arrayOfUserIds)) return implode(',', $arrayOfUserIds);
	}

	/**
	 * Are Adjuster ads enabled for this instance?
	 * @return boolean
	 */
	public function adjusterAdsEnabled () {
		return in_array('adjuster_ads', explode(',', $this->flags));
	}

	public function exportData() {
		$fields = array(
			'short_name',
			'label',
			'description',
			'user_pseudonym',
			'domain',
			'flags'
		);
		return array_intersect_key($this->getData(), array_flip($fields));
	}

	public function exportProperties($parentObject = null) {
		$props = array(
			'_economy' => $this->_economy,
			'_user' => $this->_user,
			'_user_map' => $this->_userMap,
			'_css_url' => $this->_cssUrl,
			'_html' => $this->_html,
		);
		return array_merge(parent::exportProperties($parentObject), $props);
	}

	/**
	* Given a starbar shortname, returns the integer ID of that starbar
	*
	* @author Peter Connolly
	* @param string $starbarShortName
	* @return integer ID of starbar if match is found, 99 if not found
	* @example $starbar_id = Starbar::getIdByShortName('machinima');
	* @see ImportMissionJsonController.php
	*/
	public static function getIdByShortName($starbarShortName) {
		$sql = "select id from starbar where short_name = ?";

		$data = Db_Pdo::fetch($sql, $starbarShortName);

		if ($data) {
			return (int) $data['id'];
		} else {
			return 99;
		}
	}
}

