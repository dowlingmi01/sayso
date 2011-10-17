<?php


class Registry extends Api_Registry
{
	/**
     * @return Starbar | NullObject
     */
    public static function getStarbar () {
        return Api_Registry::_get('starbar');
    }
}

