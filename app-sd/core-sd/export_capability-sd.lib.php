<?php
class export_capability extends Home {
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
		$market_per = '';
		$market_per=$this->db->pdoQuery("SELECT * FROM tbl_market_and_distribution WHERE user_id =". $getCompanyDetail['user_id'])->result();
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
			"%TOTAL_ANNUAL_REVENUE%"=>!empty($getCompanyDetail['total_annual_revenue']) ?$getCompanyDetail['total_annual_revenue']:'-',
			"%REGISTERED_YEAR%"=>!empty($getCompanyDetail['registered_year'])?$getCompanyDetail['registered_year']:'-',
			"%EXPORT_PERCENTAGE%"=>!empty($getCompanyDetail['export_percentage']) ? $getCompanyDetail['export_percentage']:'-',
			"%NO_EMPLOYEE_TRADE%"=>!empty($getCompanyDetail['no_employee_trade']) ? $getCompanyDetail['no_employee_trade']:'-',
			"%ACCEPTED_DELIVERY_ITEMS%"=>!empty($getCompanyDetail['accepted_delivery_items']) ?$getCompanyDetail['accepted_delivery_items'] : '-',
			"%ACCEPTED_PAYMENT_CURRENCY%"=>!empty($getCompanyDetail['accepted_payment_currency']) ? $getCompanyDetail['accepted_payment_currency']:'-',
			"%MARKRT_NAMES_1%" => !empty($market_per['North_America']) ? $market_per['North_America']:'0',
			"%MARKRT_NAMES_2%" => !empty($market_per['South_America']) ? $market_per['South_America']:'0',
			"%MARKRT_NAMES_3%" => !empty($market_per['Eastern_Europe']) ? $market_per['Eastern_Europe']:'0',
			"%MARKRT_NAMES_4%" => !empty($market_per['Southeast_Asia']) ? $market_per['Southeast_Asia']:'0',
			"%MARKRT_NAMES_5%" => !empty($market_per['Africa']) ? $market_per['Africa']:'0',
			"%MARKRT_NAMES_6%" => !empty($market_per['Oceania']) ? $market_per['Oceania']:'0',
			"%MARKRT_NAMES_7%" => !empty($market_per['Mid_East']) ? $market_per['Mid_East']:'0',
			"%MARKRT_NAMES_8%" => !empty($market_per['Eastern_Asia']) ? $market_per['Eastern_Asia']:'0',
			"%MARKRT_NAMES_9%" => !empty($market_per['Western_Europe']) ? $market_per['Western_Europe']:'0',
			"%MARKRT_NAMES_10%" => !empty($market_per['Central_America']) ? $market_per['Central_America']:'0',
			"%MARKRT_NAMES_11%" => !empty($market_per['Northern_Europe']) ? $market_per['Northern_Europe']:'0',
			"%MARKRT_NAMES_12%" => !empty($market_per['Southern_Europe']) ? $market_per['Southern_Europe']:'0',
			"%MARKRT_NAMES_13%" => !empty($market_per['South_Asia']) ? $market_per['South_Asia']:'0',
			"%MARKRT_NAMES_14%" => !empty($market_per['Domestic_Market']) ? $market_per['Domestic_Market']:'0',
			"%VERIFIED%" => $getCompanyDetail['verified'] == 'y' ? ' <span class="verification">'.VERIFIED_L.' <i class="fa fa-check-circle"></i> </span>' : ''
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
}

?>