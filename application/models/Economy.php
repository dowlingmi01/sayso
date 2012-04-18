<?php

class Economy extends Record
{
	protected $_tableName = 'economy';

	public function exportData() {
		$fields = array(
			'title',
			'redeemable_currency',
			'experience_currency',
		);
		return array_intersect_key($this->getData(), array_flip($fields));
	}
}
