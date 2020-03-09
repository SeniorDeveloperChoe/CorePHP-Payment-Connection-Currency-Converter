<?php
class Content extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$fields = array(
			"%PAGE_TITLE%" => (!empty($this->result["pageTitle_$this->curr_language"]) ? $this->result["pageTitle_$this->curr_language"] : $this->result['']),
			"%CONTENT%" => (!empty($this->result["pageDesc_$this->curr_language"]) ? $this->result["pageDesc_$this->curr_language"] : $this->result['pageDesc'])
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
}
?>