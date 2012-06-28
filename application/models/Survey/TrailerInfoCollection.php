<?php

class Survey_TrailerInfoCollection extends RecordCollection
{
	public function getTrailerInfoForTrailers ($trailers) {
		$commaDelimitedTrailerIdList = "";
		foreach ($trailers as $trailer) {
			if ($commaDelimitedTrailerIdList) $commaDelimitedTrailerIdList = $commaDelimitedTrailerIdList . ",";
			$commaDelimitedTrailerIdList = $commaDelimitedTrailerIdList . $trailer->id;
		}
		if ($commaDelimitedTrailerIdList) {
			$sql = "
				SELECT *
				FROM survey_trailer_info
				WHERE survey_id IN (" . $commaDelimitedTrailerIdList . ")
				ORDER BY FIND_IN_SET(survey_id, '" . $commaDelimitedTrailerIdList . "')
			";

			$data = Db_Pdo::fetchAll($sql);

			if ($data) {
				$this->build($data, new Survey_TrailerInfo());
			}
		}
	}
}
