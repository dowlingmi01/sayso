<?php
class Metrics_Event {
	static public function save( $eventName, $data, $userId ) {
		$sql = 'SELECT id FROM metrics_event_type WHERE name = ?';
		$res = Db_Pdo::fetchAll($sql, $eventName);
		if(array_key_exists(0, $res))
			$eventTypeId = $res[0]['id'];
		else {
			$eventTypeId = 1;
			$data['event_name'] = $eventName;
		}
		$sql = 'SELECT * FROM metrics_property';
		$res = Db_Pdo::fetchAll($sql, $eventName);
		$props = array();
		$propsLookup = array();
		$propsUnknown = array();
		$propsValue = array();
		foreach( $res as $prop )
			$props[$prop['name']] = array('id'=>$prop['id'], 'type'=>$prop['type']);
		foreach( $data as $key=>$value )
			if( array_key_exists($key, $props)) {
				$prop = $props[$key];
				switch($prop['type']) {
				case 'lookup':
					$propsLookup[] = array('propertyId'=>$prop['id'], 'propertyLookupValueId'=>Metrics_Event::getLookupValueId($prop['id'], $value));
					break;
				case 'string':
				case 'int':
				case 'double':
					$propsValue[] = array('propertyType' => $prop['type'], 'propertyId'=>$prop['id'], 'value'=>$value );
				}
			} else
				$propsUnknown[] = array('propertyName'=>$key, 'propertyValue' => $value);
				
		$sql = 'INSERT metrics_event ( metrics_event_type_id, user_id ) VALUES ( ?, ? )';
		Db_Pdo::execute($sql, $eventTypeId, $userId );
		$eventId = Db_Pdo::getPdo()->lastInsertId();
		foreach( $propsLookup as $prop ) {
			$sql = 'INSERT metrics_event_property_lookup ( metrics_event_id, metrics_property_id, metrics_property_lookup_value_id ) VALUES ( ?, ?, ? )';
			Db_Pdo::execute($sql, $eventId, $prop['propertyId'], $prop['propertyLookupValueId'] );
		}
		foreach( $propsValue as $prop ) {
			$sql = 'INSERT metrics_event_property_'.$prop['propertyType'].' ( metrics_event_id, metrics_property_id, value ) VALUES ( ?, ?, ? )';
			Db_Pdo::execute($sql, $eventId, $prop['propertyId'], $prop['value'] );
		}
		foreach( $propsUnknown as $prop ) {
			$sql = 'INSERT metrics_event_property_unknown ( metrics_event_id, property_name, value ) VALUES ( ?, ?, ? )';
			Db_Pdo::execute($sql, $eventId, $prop['propertyName'], $prop['propertyValue'] );
		}
	}
	static function getLookupValueId( $propertyId, $value ) {
		$sql = 'SELECT id FROM metrics_property_lookup_value WHERE metrics_property_id = ? AND value = ?';
		$res = Db_Pdo::fetchAll($sql, $propertyId, $value );
		if(array_key_exists(0, $res))
			return($res[0]['id']);
		else {
			$sql = 'INSERT metrics_property_lookup_value ( metrics_property_id, value ) VALUES (?, ?)';
			Db_Pdo::execute($sql, $propertyId, $value );
			return Db_Pdo::getPdo()->lastInsertId();
		}
	}
}