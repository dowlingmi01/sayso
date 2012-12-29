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
				AND ((start_date < now() AND end_date >= now()) OR (start_date is null))
				ORDER BY FIND_IN_SET(survey_id, '" . $commaDelimitedTrailerIdList . "')
			";

			$data = Db_Pdo::fetchAll($sql);

			if ($data) {
				$this->build($data, new Survey_TrailerInfo());
			}
		}
	}
}
