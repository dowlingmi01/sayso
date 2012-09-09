<?php

class Survey_QuestionResponse extends Record
{
	protected $_tableName = 'survey_question_response';

	public function beforeSave() {
		$this->setResponseCsvValue();
	}

	public function setResponseCsvValue() {
		switch ($this->data_type) {
			case "choice":
				$this->response_csv = "1";
				break;
			case "integer":
				$this->response_csv = "" . $this->response_integer;
				break;
			case "decimal":
			case "monetary":
				$this->response_csv = "" . $this->response_decimal;
				break;
			case "string":
				$this->response_csv = strtr($this->response_decimal, '"', '\\"');
				break;
		}
	}
}
