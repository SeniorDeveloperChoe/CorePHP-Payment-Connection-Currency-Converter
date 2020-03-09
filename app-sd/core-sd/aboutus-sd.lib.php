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
		
		$getAboutUsData=$this->db->pdoQuery("SELECT * FROM tbl_about_us")->result();
		$img_text="text_on_image_".$this->curr_language;
		$desc_text="about_description_".$this->curr_language;
		$fields = array(
			"%TEXT_ON_IMG%"=>$getAboutUsData[''.$img_text.''],
			"%DESC%"=>$getAboutUsData[''.$desc_text.'']
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
}
?>