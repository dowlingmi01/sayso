<?php

/**
* Class for various breadcrumb functions.
*
* Note that as our handling of breadcrumbs is substantially different to
* Zend normality, we don't have any connection or inheritance from
* Zend_Breadcrumb.
*
* @author Peter Connolly
*/
class Breadcrumb {

	/**
	* The current breadcrumb
	*
	* @var String
	*/
	private static $_breadcrumb = array();

	/**
	* Separator symbol
	*
	* @var string
	*/
	private static $_separator = " &rsaquo; ";

	/**
	* Boolean - Should the last link in a breadcrumb be a link (true) or text (false)
	*
	* @var mixed
	*/
	private static $_linkLast = false;

	/**
	* Add an item to the breadcrumb array
	*
	* @param mixed $label
	* @param mixed $link
	* @return string of the current breadcrumb
	*/
	public static function add($label,$link) {
		// Remove any underlines from $label, then uppercase the words
		$label = ucwords(str_ireplace('_',' ',$label));

		if ((array_key_exists($label,$_SESSION['breadcrumbnamespace'])) && ($_SESSION['breadcrumbnamespace'][$label]==$link)) {
			// We have a duplicate item in the array. We need to discard everything after this item
			$bPruned = false;
			$prunedArray = array();
			foreach ($_SESSION['breadcrumbnamespace'] as $oldlabel=>$oldlink) {
				if (($oldlabel == $label) && ($oldlink == $link)) {
					$prunedArray[$oldlabel] = $oldlink;
					$bPruned = true;
				} else {
					if (!$bPruned) {
						$prunedArray[$oldlabel] = $oldlink;
					}
				}
			}

			$_SESSION['breadcrumbnamespace'] = $prunedArray;

			$myarray = $_SESSION['breadcrumbnamespace'];

		} else {
			// No duplicate items. Just append this new item
			$_SESSION['breadcrumbnamespace'][$label] = $link;
			$myarray = $_SESSION['breadcrumbnamespace'];
		}
		return self::getTrail();
	}

	/**
	* Remove an item from the breadcrumb array if it's already there, and remove subsequent items
	*
	* @param mixed $label
	* @param mixed $link
	*/
	private static function prune($label,$link) {
		// If the $label and $link already exist in the breadcrumb, trim the breadcrumb to that point
		if (array_search($label,self::$_breadcrumb)!==false) {
			// The label is in the array

		}
	}

	/**
	* Generates the breadcrumb trail
	*
	*/
	public static function getTrail()
	{
		$breadcrumbNamespace = $_SESSION['breadcrumbnamespace'];
		$trail = "";

		$lenBreadcrumb = count($breadcrumbNamespace);
		$ctr = 0;

		foreach ($breadcrumbNamespace as $label=>$link) {

			$ctr++;
			if (($ctr ==$lenBreadcrumb) && (self::_getLinkLast())) {
				$trail .= sprintf("%s",ucwords($label));
			} else {
				$trail .= sprintf("<a href='%s'>%s</a>",$link,ucwords($label));
				if ($ctr!=$lenBreadcrumb) {
					$trail .= self::_getSeparator();
				}
			}
		}

		return $trail;
	}

	/**
	* Starts the Breadcrumb trail again from scratch
	*
	*/
	public static function resetBreadcrumbTrail()
	{
		unset($_SESSION['breadcrumbnamespace']);
		$_SESSION['breadcrumbnamespace'] = array();
		$breadcrumbnamespace = $_SESSION['breadcrumbnamespace'];
		self::add("Home","/");
	}

	/**
	* Initial loader for the Breadcrumb trail
	*
	*/
	public static function startBreadcrumbTrail()
	{
		if (session_id()=="") {
			session_start();
		}

		if (!isset($_SESSION['breadcrumbnamespace'])) {
			$_SESSION['breadcrumbnamespace'] = array();
		}

		$breadcrumbNamespace = $_SESSION['breadcrumbnamespace'];
	}

	/**
	* Setter for the Linklast boolean (determines if the last item in a breadcrumb will be a link or not)
	*
	* @param mixed $linklast
	*/
	public static function setLinkLast($linklast)
	{
		self::$_linkLast = $linklast;
	}

	/**
	* Getter for $_linkLast. If _linkLast is true, the last item in the breadcrumb would be a link
	*
	* @returns not Boolean: true = last item is NOT to be a link. False = last item WILL be a link
	*/
	private static function _getLinkLast()
	{
		return !self::$_linkLast;
	}

	public static function setSeparator($sep)
	{
		self::$_separator = $sep;
	}

	private static function _getSeparator()
	{
		return self::$_separator;
	}


	/**
	* Get the breadcrumb string
	*
	* @return string
	*
	* @author Peter Connolly
	*/
	public static function getBreadcrumb()
	{
		return $this->_breadcrumb;
	}

}
