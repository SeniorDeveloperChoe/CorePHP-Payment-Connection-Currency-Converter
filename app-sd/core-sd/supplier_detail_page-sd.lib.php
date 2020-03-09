<?php
class SupplierDetailPage extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getCompanyDetail=$logo=$time_plan='';
		$getCompanyDetail = $this->db->select('tbl_company', array('*'), array('id'=>$this->id['id']))->result();
		$getSupplierDetail = $this->db->select('tbl_users', array('first_name,last_name','gold_icon_display','plan_purchased_date'), array('id'=>$getCompanyDetail['user_id']))->result();
		$logo=!empty($getCompanyDetail['logo'])?SITE_UPD.'supplier_logo/'.$getCompanyDetail['logo']:SITE_UPD."no_prodcut_img.png";
		$template_type = $header_class_one= $header_class_two=$company_mail=$cno=$hide_no_storefront='';
		if($getCompanyDetail['selected_template'] == 'one'){
			$cno = !empty($getCompanyDetail['contact_no_1']) ? $getCompanyDetail['contact_no_1'] : '';
			$company_mail = !empty($getCompanyDetail['company_mail']) ? $getCompanyDetail['company_mail']:'';
			$template_type = $this->main_supplier_home_one();
			$header_class_one='col-md-4 col-sm-5';
			$header_class_two='col-md-8 col-sm-7';
		} else if($getCompanyDetail['selected_template'] == 'two'){
			$cno = !empty($getCompanyDetail['contact_no_1']) ? $getCompanyDetail['contact_no_1'] : '';
			$company_mail = !empty($getCompanyDetail['company_mail']) ? $getCompanyDetail['company_mail']:'';
			$template_type = $this->main_supplier_home_two();
			$header_class_one='col-md-12';
			$header_class_two='col-md-12 text-center';
		} else if($getCompanyDetail['selected_template'] == 'three'){
			$cno = !empty($getCompanyDetail['contact_no_1']) ? $getCompanyDetail['contact_no_1'] : '';
			$company_mail = !empty($getCompanyDetail['company_mail']) ? $getCompanyDetail['company_mail']:'';
			$template_type = $this->main_supplier_home_three();
			$header_class_one='col-md-4 col-sm-5';
			$header_class_two='col-md-8 col-sm-7';
		} else{
			$hide_no_storefront = 'hide';
			$cno = '-';
			$company_mail = '-';
			$template_type = $this->main_supplier_home_one();
			$header_class_one='col-md-4 col-sm-5';
			$header_class_two='col-md-8 col-sm-7';
		}

		if (empty($_SESSION['userId'])) {
			$hide_no_storefront = 'hide';
		}
		$gold_coin_hide_class='';
		$datestr=$getSupplierDetail['plan_purchased_date'];
		if($datestr == '0000-00-00 00:00:00'){
			$interval_val = '';							
		}else {
			$date1 = new DateTime($datestr); 
		    $date2 = new DateTime("now"); 
		    $interval = $date1->diff($date2); 
		    if($interval->format('%y') == 0){
		    	if($interval->format('%m') == 0){
		    		$time_plan =$interval->format('%d');
		    		$interval_val = DAYS;
		    	} else{
		    		$time_plan =$interval->format('%m'.'.'.'%d');
		    		$interval_val = MONTHS;
		    	}
		    } else{
		    	$time_plan =$interval->format('%y'.'.'.'%m');
		    	$interval_val = YEARS;
		    }
		    
		}
		/*add to favourite*/
			$chkFavId ='';
			$favCalss="fa fa-heart-o";
			$span_class = '';
				if(!empty($_SESSION['userId'])){					
					$chkFavId=$this->db->select("tbl_favorite_product",array("id"),array("item_id"=>$this->id['id'],"user_id"=>$_SESSION['userId'],"item_type"=>'s'))->result();
					if(empty($chkFavId)){
						$favCalss="fa fa-heart-o";
					}else{
						$favCalss="fa fa-heart";
						$span_class="liked";
					}
				}
		$gold_coin_hide_class = ($getSupplierDetail['gold_icon_display'] == 'y') ? '<span class="verified">
                <div class="verified-img">
                    <img src="'.SITE_IMG.'years.png" alt="">
                </div>
                <span>'.$time_plan.' <sup>'.$interval_val.'</sup></span>
            </span>': '';
		$fields = array(
			"%ID%" => $this->id['id'],
			"%MAIN_SUPPLIER_HOME%" => $template_type,
			"%GOLD_COIN_HIDE_CLASS%"=>$gold_coin_hide_class,
			"%HEADER_CLASS_ONE%" => $header_class_one,
			"%HEADER_CLASS_TWO%" => $header_class_two,
			"%SLUG%"=>$getCompanyDetail['company_slug'],
			"%SUPPLIER_NM%"=>$getSupplierDetail['first_name'].' '.$getSupplierDetail['last_name'],
			"%HIDE_REPORT_SUPPLIER%"=> isset($_SESSION['user_id'])?  ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : '<a href="javascript:;" class="report-bug" data-toggle="modal" data-target="#reportBug"><span><i class="icon-action-redo icons"></i></span> '.REPORT_SUPPLIER.'</a>') : '<a href="javascript:;" class="report-bug" data-toggle="modal" data-target="#reportBug"><span><i class="icon-action-redo icons"></i></span> '.REPORT_SUPPLIER.'</a>',
			"%COMPANY_NAME%"=>$getCompanyDetail['company_name'],
			"%COMPANY_MAIL%"=>$company_mail,
			"%CONTACT_NO_1%"=>$cno,
			"%LOGO%"=>$logo,
			"%MAIN_ID%"=>$this->id['id'],
			"%SUPPLIER_ID%"=>$getCompanyDetail['user_id'],
			"%HIDE_NO_STOREFRONT%"=>$hide_no_storefront	,
			"%SPAN_CLASS%" => $span_class,
			"%CHECK_FAV%"=>$favCalss
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function get_cOMPANY_Pics() {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/company_image-sd.skd"))->compile(); 
		$getProductImg=$this->db->select("tbl_company",array("id","company_photos"),array("id"=>$this->id['id']))->result();
		if(!empty($getProductImg['company_photos'])){
			$getImages=explode(',',$getProductImg['company_photos']);
			if(!empty($getImages)) {	
				foreach($getImages AS $key => $values) {
					$active = (empty($key) ? 'checked="checked"' : '');
					$fields = array(
						"%ID%" => $key,
						"%IMAGES%" => SITE_UPD.'supplier_photos/'.$values,
						"%CHECKED%" => ($key == 0) ? 'checked' : ''
					);
					$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
				}
			}
		} else{
				$fields = array(
					"%IMAGES%" => SITE_UPD.'no_prodcut_img.png',
					"%CHECKED%" => 'checked'
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
		}	
		return $final_content;
	}	
	public function main_supplier_home_one() {
			$final_content = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/main_detail_home-sd.skd"))->compile();

			$getCompanyDetail=$logo='';
			$getCompanyDetail = $this->db->select('tbl_company', array('*'), array('id'=>$this->id['id']))->result();
			$count_add_to_showcase = $this->db->select('tbl_products', array('id'), array('addToShowCase'=>'y','user_id'=>$getCompanyDetail['user_id']))->affectedRows();
			$getSupplierDetail = $this->db->select('tbl_users', array('first_name,last_name'), array('id'=>$getCompanyDetail['user_id']))->result();
			$logo=!empty($getCompanyDetail['logo'])?SITE_UPD.'supplier_logo/'.$getCompanyDetail['logo']:SITE_UPD."no_prodcut_img.png";
			$resp_time = '';
				if(!empty($getCompanyDetail['response_time'])) {
					$resp_time = sec2view($getCompanyDetail['response_time']);
				} else {
					$resp_time = "1Min";
				}
			$fields = array(
			"%COMPANY_NAME%"=>$getCompanyDetail['company_name'],
			"%VERIFIED%" => $getCompanyDetail['verified'] == 'y' ? ' <span class="verification">'.VERIFIED_L.' <i class="fa fa-check-circle"></i> </span>' : '',
			"%ALL_COMPANY_IMG%"=>$this->get_cOMPANY_Pics(),
			"%SLUG%"=>$getCompanyDetail['company_slug'],			
			"%SHOWCASE_ONE%"=>$this->showcase_one($getCompanyDetail['user_id'],$getCompanyDetail['id']),
			"%HIDE_SEND_MSG_BTN%"=> isset($_SESSION['user_id'])? ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : '<button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendMsg">'.SEND_MESSAGE.'</button>') : '<button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendMsg">'.SEND_MESSAGE.'</button>',
			"%HIDE_SEND_INQ%"=> isset($_SESSION['user_id'])? ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : ' <button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendInquiry">'.SEND_AN_INQUIRY.'</button>') : ' <button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendInquiry">'.SEND_AN_INQUIRY.'</button>',
			"%BUSINESS_TYPE%"=>getBusinessType($getCompanyDetail['business_type_id']),
			"%LOCATION%"=>$getCompanyDetail['location'],
			"%REGISTERED_YEAR%"=>!empty($getCompanyDetail['registered_year']) ? $getCompanyDetail['registered_year']:'-',
			"%RESPONSE_RATE%"=>(!empty($getCompanyDetail['response_rate']) ? number_format_cust($getCompanyDetail['response_rate'], 2) : 100).'%',
			"%RESPONSE_TIME%"=>$resp_time,
			"%LEGAL_OWNER%"=>!empty($getCompanyDetail['legal_owner']) ? $getCompanyDetail['legal_owner']:'-',
			"%OFFICE_SIZE%"=>!empty($getCompanyDetail['office_size']) ? $getCompanyDetail['office_size']:'-',
			"%CONTACT_PERSON_NAME%"=>!empty($getCompanyDetail['contact_person_name']) ? $getCompanyDetail['contact_person_name']:'-',
			"%WEB_URL%"=>!empty($getCompanyDetail['web_url']) ? $getCompanyDetail['web_url']:'-',
			"%TOTAL_EMPLOYEE%"=>!empty($getCompanyDetail['total_employee']) ? $getCompanyDetail['total_employee']:'-',
			"%MAIN_PRODUCT%"=>!empty($getCompanyDetail['main_product']) ? $getCompanyDetail['main_product']:'-',
			"%COMPANY_ADDRESS%"=>!empty($getCompanyDetail['company_address']) ? $getCompanyDetail['company_address']:'-',
			"%COMPANY_ADVANTAGE%"=>!empty($getCompanyDetail['company_advantage']) ? $getCompanyDetail['company_advantage']:'-',
			"%COUNT_ADD_TO_SHOWCASE_CLASS%"=>($count_add_to_showcase != 0) ? '': 'hide',
                            "%LANGUAGES%"=>$getCompanyDetail['language_spoken']	
			
		);
		$main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
		return $main_content;
		}		
	public function showcase_one($supplier_id,$cmpny_id) {
			$final_content = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/showcase_one-sd.skd"))->compile();
		$unitName="u.unit_value_".$this->curr_language." AS unitName";
			$catName="c.categoryName_".$this->curr_language." AS catName";
			$all_add_to_showcase_product = $this->db->pdoQuery("SELECT p.*,$catName,$unitName
														FROM tbl_products as p
														INNER JOIN tbl_product_category as c on p.cat_id=c.id
														INNER JOIN tbl_unit_value as u on p.unit_id=u.id
														WHERE p.user_id= ".$supplier_id." and p.addToShowCase='y' ORDER BY p.id DESC LIMIT 8 ")->results();
			if(!empty($all_add_to_showcase_product)) {
				foreach ($all_add_to_showcase_product as $key => $value) {
					$profile_image = explode(',', $value["product_image"]);
					$template_type_class = '';
					$getCompanyDetail = $this->db->select('tbl_company', array('selected_template'), array('id'=>$cmpny_id))->result();
					if($getCompanyDetail['selected_template'] == 'one'){
						$template_type_class = 'col-md-3 col-sm-6';
						$tmp_div_class = '';
					} else if($getCompanyDetail['selected_template'] == 'two'){
						$template_type_class = 'col-md-4 col-sm-6';
						$tmp_div_class = '';
					} else if($getCompanyDetail['selected_template'] == 'three'){
						$template_type_class = 'item';
						$tmp_div_class = '';
					} else{
						$template_type_class = '';
					}
					$price = ($value["isNegotiable"] == 'y') ? $value['min_price'].'-'.$value['max_price'].' '.CURRENCY_CODE : $value['min_price'].' '.CURRENCY_CODE;
					$filds = array(
						"%PRO_IMG%" => SITE_PRODUCT_IMG.$profile_image[0],
						"%PRO_TITLE%" => $value["product_title"],
						"%CLS_TYP%" => $template_type_class,
						"%PRO_SLUG%" =>$value["product_slug"],
						"%CATNAME%" =>$value['catName'],
						"%UNITNAME%" =>$value['unitName'],
						"%ORDER_QUANTITY%"=>$value['order_quantity'],
						"%SUPPLY_ABILITY%"=>$value['supply_ability'],
						"%PRICE%"=>$price,
                                            
					);
					$final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
				}
			}					  
			return $final_content;
		}
		public function main_supplier_home_two() {
			$final_content = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/main_detail_home_two-sd.skd"))->compile();
			$getCompanyDetail=$logo='';
			$getCompanyDetail = $this->db->select('tbl_company', array('*'), array('id'=>$this->id['id']))->result();
			$getSupplierDetail = $this->db->select('tbl_users', array('first_name,last_name'), array('id'=>$getCompanyDetail['user_id']))->result();
			$logo=!empty($getCompanyDetail['logo'])?SITE_UPD.'supplier_logo/'.$getCompanyDetail['logo']:SITE_UPD."no_prodcut_img.png";
			$count_add_to_showcase = $this->db->select('tbl_products', array('id'), array('addToShowCase'=>'y','user_id'=>$getCompanyDetail['user_id']))->affectedRows();
			$resp_time = '';
				if(!empty($getCompanyDetail['response_time'])) {
					$resp_time = sec2view($getCompanyDetail['response_time']);
				} else {
					$resp_time = "1Min";
				}
			$fields = array(
			"%COMPANY_NAME%"=>$getCompanyDetail['company_name'],
			"%VERIFIED%" => $getCompanyDetail['verified'] == 'y' ? '<span class="verification">'.VERIFIED_L.' <i class="fa fa-check-circle"></i> </span>' : '',
			"%ALL_COMPANY_IMG%"=>$this->get_cOMPANY_Pics(),
			"%SLUG%"=>$getCompanyDetail['company_slug'],			
			"%SHOWCASE_ONE%"=>$this->showcase_one($getCompanyDetail['user_id'],$getCompanyDetail['id']),
			"%HIDE_SEND_MSG_BTN%"=> isset($_SESSION['user_id'])? ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : '<button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendMsg">'.SEND_MESSAGE.'</button>') : '<button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendMsg">'.SEND_MESSAGE.'</button>',
			"%HIDE_SEND_INQ%"=> isset($_SESSION['user_id'])? ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : ' <button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendInquiry">'.SEND_AN_INQUIRY.'</button>') : ' <button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendInquiry">'.SEND_AN_INQUIRY.'</button>',
			"%BUSINESS_TYPE%"=>getBusinessType($getCompanyDetail['business_type_id']),
			"%LOCATION%"=>$getCompanyDetail['location'],
			"%REGISTERED_YEAR%"=>$getCompanyDetail['registered_year'],
			"%RESPONSE_RATE%"=>(!empty($getCompanyDetail['response_rate']) ? number_format_cust($getCompanyDetail['response_rate'], 2) : 100).'%',
			"%RESPONSE_TIME%"=>$resp_time,
			"%LEGAL_OWNER%"=>$getCompanyDetail['legal_owner'],
			"%OFFICE_SIZE%"=>$getCompanyDetail['office_size'],
			"%CONTACT_PERSON_NAME%"=>$getCompanyDetail['contact_person_name'],
			"%WEB_URL%"=>$getCompanyDetail['web_url'],
			"%TOTAL_EMPLOYEE%"=>$getCompanyDetail['total_employee'],
			"%MAIN_PRODUCT%"=>$getCompanyDetail['main_product'],
			"%COMPANY_ADDRESS%"=>$getCompanyDetail['company_address'],
			"%COMPANY_ADVANTAGE%"=>$getCompanyDetail['company_advantage'],
			"%COUNT_ADD_TO_SHOWCASE_CLASS%"=>($count_add_to_showcase != 0) ? '': 'hide',
			"%LANGUAGES%"=>$getCompanyDetail['language_spoken']		
		);
			$main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
			return $main_content;
		}		
		public function main_supplier_home_three() {
			$final_content = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/main_detail_home_three-sd.skd"))->compile();
			$getCompanyDetail=$logo='';
			$getCompanyDetail = $this->db->select('tbl_company', array('*'), array('id'=>$this->id['id']))->result();
			$getSupplierDetail = $this->db->select('tbl_users', array('first_name,last_name'), array('id'=>$getCompanyDetail['user_id']))->result();
			$count_add_to_showcase = $this->db->select('tbl_products', array('id'), array('addToShowCase'=>'y','user_id'=>$getCompanyDetail['user_id']))->affectedRows();
			$logo=!empty($getCompanyDetail['logo'])?SITE_UPD.'supplier_logo/'.$getCompanyDetail['logo']:SITE_UPD."no_prodcut_img.png";
			$resp_time = '';
				if(!empty($getCompanyDetail['response_time'])) {
					$resp_time = sec2view($getCompanyDetail['response_time']);
				} else {
					$resp_time = "1Min";
				}
			$fields = array(
			"%COMPANY_NAME%"=>$getCompanyDetail['company_name'],
			"%VERIFIED%" => $getCompanyDetail['verified'] == 'y' ? ' <span class="verification">'.VERIFIED_L.' <i class="fa fa-check-circle"></i> </span>' : '',
			"%ALL_COMPANY_IMG%"=>$this->get_cOMPANY_Pics(),
			"%SLUG%"=>$getCompanyDetail['company_slug'],			
			"%SHOWCASE_ONE%"=>$this->showcase_one($getCompanyDetail['user_id'],$getCompanyDetail['id']),
			"%HIDE_SEND_MSG_BTN%"=> isset($_SESSION['user_id'])? ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : '<button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendMsg">'.SEND_MESSAGE.'</button>') : '<button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendMsg">'.SEND_MESSAGE.'</button>',
			"%HIDE_SEND_INQ%"=> isset($_SESSION['user_id'])? ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : ' <button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendInquiry">'.SEND_AN_INQUIRY.'</button>') : ' <button class="btn btn-system btn-block" data-toggle="modal" data-target="#sendInquiry">'.SEND_AN_INQUIRY.'</button>',
			"%BUSINESS_TYPE%"=>getBusinessType($getCompanyDetail['business_type_id']),
			"%LOCATION%"=>$getCompanyDetail['location'],
			"%REGISTERED_YEAR%"=>$getCompanyDetail['registered_year'],
			"%RESPONSE_RATE%"=>(!empty($getCompanyDetail['response_rate']) ? number_format_cust($getCompanyDetail['response_rate'], 2) : 100).'%',
			"%RESPONSE_TIME%"=>$resp_time,
			"%LEGAL_OWNER%"=>$getCompanyDetail['legal_owner'],
			"%OFFICE_SIZE%"=>$getCompanyDetail['office_size'],
			"%CONTACT_PERSON_NAME%"=>$getCompanyDetail['contact_person_name'],
			"%WEB_URL%"=>$getCompanyDetail['web_url'],
			"%TOTAL_EMPLOYEE%"=>$getCompanyDetail['total_employee'],
			"%MAIN_PRODUCT%"=>$getCompanyDetail['main_product'],
			"%COMPANY_ADDRESS%"=>$getCompanyDetail['company_address'],
			"%COMPANY_ADVANTAGE%"=>$getCompanyDetail['company_advantage'],
			"%COUNT_ADD_TO_SHOWCASE_CLASS%"=>($count_add_to_showcase != 0) ? '': 'hide',
                        "%LANGUAGES%"=>$getCompanyDetail['language_spoken']			
		);
		$main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
		return $main_content;
		}
}

?>