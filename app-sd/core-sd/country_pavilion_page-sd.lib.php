<?php
class country_pavilion_page extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent() {
		$fields =$loop_country = $html ='';
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getCountryData=$this->db->pdoQuery("SELECT * FROM tbl_manage_country_flag WHERE isActive = ? ",array('y'))->results();
		foreach ($getCountryData as $keys => $val) {
			$loop_content_tag = (new MainTemplater(DIR_TMPL.$this->module."/loop_data-sd.skd"))->compile();
			$tags_loop_fields=array(
					"%COUNTRY_NAME%"=>$val['country_name'],
					"%COUNTRY_FLAG%"=>getImage(DIR_UPD.'contry_flag-sd/'.$val['country_image'], 64, 64),
				);
			$loop_country .= str_replace(array_keys($tags_loop_fields), array_values($tags_loop_fields), $loop_content_tag);
		}
		
	$fields = array(
			"%MORE_COUNTRY%" => $loop_country
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}

}
?>