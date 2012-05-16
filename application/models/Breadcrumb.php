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
	* All parameters for the current Zend request
	*
	* @var String
	*/
	private $_allParams;

	/**
	* Stores the current entire URL. Sliced and diced to produce the breadcrumb
	*
	* @var mixed
	*/
	private $_url;

	/**
	* The current breadcrumb
	*
	* @var String
	*/
	private $_breadcrumb;

	/**
	* New parameters for this action
	*
	* @var array
	*/
	private $_newParam = array();

	/**
	* Separator symbol
	*
	* @var string
	*/
	private $_separator = " &rsaquo; ";

	/**
	* Boolean - Should the last link in a breadcrumb be a link (true) or text (false)
	*
	* @var mixed
	*/
	private $_linkLast = false;

	/**
	* Constructor - Takes the current URI and builds a breadcrumb
	*
	* @param array $paramlist An array in the following format;
	* * Array
	*	(
	*		[module] => cms
	*		[controller] => admin
	*		[action] => detail
	*		[table] => survey
	*		[id] => 90
	*	)
	*
	*/
	public function __construct($paramlist)
	{
		$this->_allParams = $paramlist;

		$this->_setURL($_SERVER["REQUEST_URI"]);
		$this->setBreadcrumb(); // Set the initial breadcrumb
	}

	/**
	* Create a breadcrumb string from the current URI
	*
	* @returns string the breadcrumb, as a string of links
	*/
	public function setBreadcrumb()
	{

		$bcBase = "/cms/admin";
		$interLink = "";
		$this->_breadcrumb = sprintf("<a href='%s'>Home</a>",$bcBase);
		if (count($this->_getURL()) <= 8) {

			$reversearray = $this->_cleanArray($this->_getURL());

			// Do we have an id
			if ($reversearray[1]=="id") {
				// We have an id number in position 0. We need to build a breadcrumb which accomodates a View option of the table
				$interText = sprintf("View all %s Records",ucwords(str_replace("_"," ",$reversearray[2])));
				$interLink = sprintf("<a href='%s/view/table/%s'>%s</a>",$bcBase,$reversearray[2],$interText);
				$this->_breadcrumb .= sprintf("%s%s",$this->_getSeparator(),$interLink);
			}
			// Do we have an action?
			if (array_key_exists(5,$this->_getURL())) {
				$bcAction = ucfirst($this->_getURL(3));
				$bcTable = ucwords(str_replace("_"," ",$this->_getURL(5)));
				$bcComment = $bcAction." ".$bcTable;
				$bcLink = $bcBase."/".$this->_getURL(3)."/".$this->_getURL(4)."/".$this->_getURL(5);

				// Last item for the breadcrumb doesn't have a link
				$this->_breadcrumb .= sprintf("%s%s",$this->_getSeparator(),$bcComment);
			}
		} else {

			// We need a breadcrumb - we're going deeper!
			//printf("Going deeper. <pre>%s</pre>",print_r($this->_getURL(),true));

			$url = $this->_cleanArray($this->_getURL());



			//printf("Now have  <pre>%s</pre>",print_r($url,true));
			//exit;
			$cumulativeURL = "";
			// In the URL, actions start at item 3, and have five items in a valid action
			for ($cnt = 3; $cnt <= count($this->_getURL())-3; $cnt=$cnt+5) {

				// Links are in batches of 5
				$bcAction = ucfirst($this->_getURL($cnt));
				$bcTable = ucwords(str_replace("_"," ",$this->_getURL($cnt+2)));
				$bcComment = $bcAction." ".$bcTable;
				//printf("<p>The Action at [%s] is [%s]</p>",$cnt,$bcAction);
				if (($cnt + 5 == count($this->_getURL())) && (!$this->_getLinkLast())) {
					// This is the last link. Only display as a link if we need to
					$this->_breadcrumb  .= sprintf("%s%s",$this->_getSeparator(),$bcComment);
				} else {
					// we may not have five links - for an add, we only get four.
					if ($this->_getURL($cnt)) {
						$cumulativeURL .= sprintf("%s/",$this->_getURL($cnt));
					}
					if ($this->_getURL($cnt+1)) {
						$cumulativeURL .= sprintf("%s/",$this->_getURL($cnt+1));
					}
					if ($this->_getURL($cnt+2)) {
						$cumulativeURL .= sprintf("%s/",$this->_getURL($cnt+2));
					}
					if ($this->_getURL($cnt+3)) {
						$cumulativeURL .= sprintf("%s/",$this->_getURL($cnt+3));
					}
					if ($this->_getURL($cnt+4)) {
						$cumulativeURL .= sprintf("%s/",$this->_getURL($cnt+4));
					}
				//	$cumulativeURL .= sprintf("%s/%s/%s/%s/%s/",$this->_getURL($cnt),$this->_getURL($cnt+1),$this->_getURL($cnt+2),$this->_getURL($cnt+3),$this->_getURL($cnt+4));

					$this->_breadcrumb  .= sprintf("%s<a href='%s/%s'>%s</a>",$this->_getSeparator(),$bcBase,$cumulativeURL,$bcComment);

				}
			}
		}
		return $this->_breadcrumb;
	}

	/**
	* Setter for the Linklast boolean (determines if the last item in a breadcrumb will be a link or not)
	*
	* @param mixed $linklast
	*/
	public function setLinkLast($linklast)
	{
		$this->_linkLast = $linklast;
	}

	private function _getLinkLast()
	{
		return $this->_linkLast;
	}

	public function setSeparator($sep)
	{
		$this->_separator = $sep;
	}

	private function _getSeparator()
	{
		return $this->_separator;
	}

	/**
	* Remove excess parameters from a URL array
	*
	* @param mixed $dirtyarray
	* @author Peter Connolly
	*/
	private function _cleanArray($dirtyarray,$reverse=true)
	{
		if ($reverse) {
			if ($dirtyarray[0]==null) {
				unset($dirtyarray[0]);
			}

			$revarray = array_reverse($dirtyarray);
		} else {
			$revarray = $dirtyarray; // The array does not need reversing
		}

		if (array_key_exists(1,$revarray)) {
			if (($revarray[1]=="startlist")||($revarray[1]=="perPagelist")) {
				unset($revarray[0]);
				unset($revarray[1]);
			}
		}

		if (array_key_exists(3,$revarray)) {
			if (($revarray[3]=="startlist")||($revarray[3]=="perPagelist")) {
				unset($revarray[2]);
				unset($revarray[3]);
			}
		}

		// reindex the array - in case we removed any keys in the above lines.
		return array_values($revarray);
	}
	/**
	* Get the breadcrumb string
	*
	* @return string
	*
	* @author Peter Connolly
	*/
	public function getBreadcrumb()
	{
		return $this->_breadcrumb;
	}

	/**
	* Get the URI array (or a specific index thereof)
	*
	* @param mixed $index - If null, entire array is returned.
	*
	* @author Peter Connolly
	*/
	private function _getURL($index=null)
	{
		if ($index!=null) {
			if (array_key_exists($index,$this->_url)) {
				return $this->_url[$index];
			} else {
				return false;
			}
		} else {
			return $this->_url;
		}
	}

	/**
	* Set the URL array
	*
	* Accepts the URI as a string, and converts to an array
	*
	* @param string $URI
	* @author Peter Connolly
	*/
	private function _setURL($URI)
	{
		$this->_url = explode("/",$URI);
	}

	/**
	* Returns an array of parameters telling Zend how to handle a request
	*
	* Array
	*	(
	*		[module] => cms
	*		[controller] => admin
	*		[action] => detail
	*		[table] => survey
	*		[id] => 90
	*	)
	*/
	public function getParameters()
	{
		// We need a breadcrumb - we're going deeper!
			//printf("<h1>Going for parameters</h1><pre>%s</pre>",print_r($this->_getURL(),true));
			//exit;
		$reversearray = array_reverse($this->_getURL());

		if ($reversearray[0]==null) {
			unset($reversearray[0]);
			$reversearray = array_values($reversearray);
		}
		//$reversearray = $this->_cleanArray($reversearray,false);
	//	print_r($reversearray);

		switch ($reversearray[1]) {
			case "id":
				// We have an id number in position 0.
				// We need to show a specific record
				$this->_newParam["module"] = "cms";
				$this->_newParam["controller"]="admin";
				$this->_newParam["action"]=$reversearray[4];
				$this->_newParam["table"]=$reversearray[2];
				$this->_newParam["id"]=$reversearray[0];
			break;
			case "startlist":
			case "perPagelist":
				// We've passed some parameters
				$this->_newParam["module"] = "cms";
				$this->_newParam["controller"]="admin";
				$this->_newParam["action"]=$reversearray[4];
				$this->_newParam["table"]=$reversearray[2];
				$this->_newParam[$reversearray[1]]=$reversearray[0];
				// are we preceded by a startlist or perpagelist (we can get both in a param)
				if (($reversearray[3]=="startlist") || ($reversearray[3]=="perPagelist")) {
					$this->_newParam[$reversearray[3]]=$reversearray[2];
					$this->_newParam["table"]=$reversearray[4];
				}
			break;
			default:
			//Browse all records
				$this->_newParam["module"] = "cms";
				$this->_newParam["controller"]="admin";
				$this->_newParam["action"]=$reversearray[2];
				$this->_newParam["table"]=$reversearray[0];
		}


//printf("<h1>my new parameter</h1><pre>%s</pre>",print_r($this->_newParam,true));
		return $this->_newParam;
	}

}
