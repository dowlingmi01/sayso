<?php
/**
 * Static functions for formattin values
 *
 * @author alecksmart
 */
class Data_FormatTools
{
	public static function displayDateToMysql($date)
	{
		$chunks = explode('/', $date);
		$date = new DateTime(sprintf('%s-%s-%s 00:00:00', $chunks[2], $chunks[0], $chunks[1]));
		return $date->format('Y-m-d');
	}

	public static function mysqlDateToDisplay($date)
	{
		$date = new DateTime($date);
		return $date->format('m/d/Y');
	}
}