<?php
class CompanyCerificate extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getCompanyDetail=$logo='';
		$getCompanyDetail = $this->db->select('tbl_company', array('*'), array('id'=>$this->id['id']))->result();
		$logo=!empty($getCompanyDetail['logo'])?SITE_UPD.'supplier_logo/'.$getCompanyDetail['logo']:SITE_UPD."no_prodcut_img.png";
		$cno = $main = $hide_none='';
		$header_class_one='col-md-4 col-sm-5';
		$header_class_two='col-md-8 col-sm-7';
		if($getCompanyDetail['selected_template'] == 'none'){
			$cno ='-';
			$main =	'-';
			$hide_none = 'hide';
					
		}else{
			if($getCompanyDetail['selected_template'] == 'two'){
				$header_class_one='col-md-12';
				$header_class_two='col-md-12 text-center';
			}
			$hide_none = '';
			$cno =!empty($getCompanyDetail['contact_no_1']) ? $getCompanyDetail['contact_no_1']: '-';
			$main =	!empty($getCompanyDetail['company_mail']) ? $getCompanyDetail['company_mail']: '-';
		}
		$fields = array(
			"%SLUG%"=>$getCompanyDetail['company_slug'],
			"%HEADER_CLASS_ONE%" => $header_class_one,
			"%HEADER_CLASS_TWO%" => $header_class_two,
			"%HIDE_REPORT_SUPPLIER%"=> isset($_SESSION['user_id'])?  ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : '<a href="javascript:;" class="report-bug" data-toggle="modal" data-target="#reportBug"><span><i class="icon-action-redo icons"></i></span> '.REPORT_SUPPLIER.'</a>') : '<a href="javascript:;" class="report-bug" data-toggle="modal" data-target="#reportBug"><span><i class="icon-action-redo icons"></i></span> '.REPORT_SUPPLIER.'</a>',
			"%COMPANY_NAME%"=>$getCompanyDetail['company_name'],
			"%COMPANY_MAIL%"=>$main,
			"%CONTACT_NO_1%"=>$cno,
			"%HIDE_NONE%"=>$hide_none,
			"%LOGO%"=>$logo,
			"%MAIN_ID%"=>$this->id['id'],
			"%SUPPLIER_ID%"=>$getCompanyDetail['user_id'],
			"%DETAILED_DESCRIPTION%"=>$getCompanyDetail['detailed_description'],
			"%ALL_COMPANY_CERTY%"=>$this->get_cOMPANY_Certificates(),
			"%VERIFIED%" => $getCompanyDetail['verified'] == 'y' ? ' <span class="verification">'.VERIFIED_L.' <i class="fa fa-check-circle"></i> </span>' : ''
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function get_cOMPANY_Certificates() {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/company_image-sd.skd"))->compile(); 
		$getProductCertyImg =$this->db->pdoQuery("SELECT cc.* FROM tbl_company_certificates as cc 
		JOIN tbl_company as c ON(c.id=cc.company_id) WHERE c.id= ?",array($this->id['id']))->results();
		if(!empty($getProductCertyImg)){
			if(!empty($getProductCertyImg)) {	
				foreach($getProductCertyImg AS $key => $values) {
					$fields = array(
						"%IMAGES%" => SITE_UPD.'supplier_certificate/'.$values['image'],
						"%CERTY_TITLE%" => !empty($values['title']) ? $values['title'] : ""

					);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
				}
			}		
		}
		else{
			$fields = array(
						"%IMAGES%" => SITE_UPD.'no_prodcut_img.png',
						"%CERTY_TITLE%" => ''
					);
			$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
		}
		return $final_content;
	}	
}

?>