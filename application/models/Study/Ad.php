<?php

class Study_Ad extends Record
{
	protected $_tableName = 'study_ad';

	public function exportData() {
		$fields = array(
			'type',
			'existing_ad_type',
			'existing_ad_tag',
			'existing_ad_domain',
			'replacement_ad_type',
			'replacement_ad_url',
			'replacement_ad_title',
			'replacement_ad_description',
			'ad_target'
		);
		return array_intersect_key($this->getData(), array_flip($fields));
	}
}
