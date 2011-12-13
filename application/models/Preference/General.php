<?php

class Preference_General extends Record
{
	protected $_tableName = 'preference_general';
	
	protected $_user;
	
	protected $_pollFrequency;
	
	protected $_emailFrequency;
	
	public function setUser (User $user) {
		$this->_user = $user;
		$this->user_id = $user->getId();
	}
	
	public function getUser () {
		if (!$this->_user) {
			$this->_user = new User();
			$this->_user->loadData($this->user_id);
		}
		return $this->_user;
	}
	
	public function setPollFrequency (Lookup_PollFrequency $frequency) {
		$this->_pollFrequency = $frequency;
		$this->poll_frequency_id = $frequency->getId();
	}
	
	public function getPollFrequency () {
		if (!$this->_pollFrequency) {
			$this->_pollFrequency = new Lookup_PollFrequency();
			$this->_pollFrequency->loadData($this->poll_frequency_id);
		}
		return $this->_pollFrequency;
	}
	
	public function setEmailFrequency (Lookup_EmailFrequency $frequency) {
		$this->_emailFrequency = $frequency;
		$this->email_frequency_id = $frequency->getId();
	}
	
	public function getEmailFrequency () {
		if (!$this->_emailFrequency) {
			$this->_emailFrequency = new Lookup_EmailFrequency();
			$this->_emailFrequency->loadData($this->email_frequency_id);
		}
		return $this->_emailFrequency;
	}
}

