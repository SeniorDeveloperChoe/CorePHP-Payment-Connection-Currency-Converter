<?php
class SupplierProfile extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$selected_1 = $selected_2 = $selected_3=$selected_4=$selected_5=$fewer_then_5_selected=$select_5_to_10_people_selected='';
		$selected_11_to_50_people_selected=$selected_51_to_100_people_selected=$selected_101_to_200_people_selected=$selected_201_300_people_selected=$selected_301_500_people_selected=$selected_501_to_1000_people_selected=$above_1000_selected=$business_type_id_view =$hide_image =$action =$total_annual_revanue_1 = $total_annual_revanue_2 = $total_annual_revanue_3 = $total_annual_revanue_4 = $total_annual_revanue_5 = $total_annual_revanue_6 = $total_annual_revanue_7 = $export_per_1=$export_per_2=$export_per_3=$export_per_4=$export_per_5=$export_per_6=$export_per_7=$export_per_8=$export_per_9=$export_per_10=$exw_checked=$no_emp_trade_1=$no_emp_trade_2=$no_emp_trade_3=$no_emp_trade_4=$no_emp_trade_5=$no_emp_trade_6=$no_emp_trade_7=$fob_checked=$cif_checked=$cfr_checked=$usd_ckecked = $eur_ckecked = $jpy_ckecked = $cad_ckecked = $aud_ckecked = $khd_ckecked = $gbp_ckecked = $cny_ckecked = $chf_ckecked = $inr_ckecked = $Arabic_checked =$Chinese_checked =$English_checked =$French_checked =$German_checked =$Hindi_checked =$Italian_checked =$Japanese_checked =$Korean_checked =$Portuguese_checked =$Spanish_checked =$Russian_checked = '';

		$leftPanel=$this->getleftPanel();
		$comapny_detail =$this->db->pdoQuery("SELECT * FROM tbl_company WHERE user_id = ? ",array($this->sessUserId))->result();
		
		// ming lau -> get bank_detail
		$bank_detail = $this->db->pdoQuery("SELECT * FROM tbl_bank")->results();
		$bank_name = "<option value='' disabled>Select Bank Name</option>";
		foreach ($bank_detail as $key => $value) {
			$bank_name .= "<option id='".$value['id']."' value='".$value['bank_code']."'>".$value['bank_name']."</option>";
		}
		// print_r($bank_name);
		// exit();

		if(empty($comapny_detail['hash'])){
				$hash = generateRandString();
				$comapny_detail =$this->db->pdoQuery("UPDATE tbl_company SET hash = ? WHERE user_id = ? ",array($hash,$this->sessUserId))->result();			
				$comapny_detail['hash']=$hash;
		}

		$businessType="bt.business_type_".$this->curr_language." AS businessType";
		$business_types='';
		$business_type_id=$this->db->pdoQuery("SELECT bt.*,$businessType FROM tbl_business_type AS bt WHERE bt.isActive = 'y'")->results();
		$market_per = '';
		$market_per=$this->db->pdoQuery("SELECT * FROM tbl_market_and_distribution WHERE user_id =". $this->sessUserId)->result();

		$lastBusinessType=end($business_type_id);
			foreach ($business_type_id as $key => $value) {
				$lastlbl="";
				if($value['businessType']==$lastBusinessType['businessType']){
					$lastlbl="last_lable";
				}
				$checked=$all_business_type ="";	
				$all_business_type =!empty($comapny_detail['business_type_id']) ? explode(',', $comapny_detail['business_type_id']): '';
				if(!empty($all_business_type)){
					foreach ($all_business_type as $all_key => $val) {
						if($val==$value['id']){
							$checked="checked";	
							$business_type_id_view .=$value['business_type']; 
						}
					}	
				}
				$business_types .='<label class="checkbox-inline '.$lastlbl.'">
									<input class="business_type_id" type="checkbox" name="business_type_id[]" id="business_type_id" value="'.$value['id'].'" '.$checked.'>
									'.$value['business_type'].'
								</label>';
			}
			$main_product_data = !empty($comapny_detail['main_product']) ? explode(',', $comapny_detail['main_product']) : "";
			if(!empty($comapny_detail['office_size']) && $comapny_detail['office_size'] == 'below 100 square meters'){
				$selected_1 = 'selected';
			} else if(!empty($comapny_detail['office_size']) && $comapny_detail['office_size'] == '101 - 500 square meters'){
				$selected_2 = 'selected';
			} else if(!empty($comapny_detail['office_size']) && $comapny_detail['office_size'] == '501 - 1000 square meters'){
				$selected_3 = 'selected';
			} else if(!empty($comapny_detail['office_size']) && $comapny_detail['office_size'] == '1001 -2000 square meters'){
				$selected_4 = 'selected';
			} else if(!empty($comapny_detail['office_size']) && $comapny_detail['office_size'] == 'above 2000 square meters'){
				$selected_5 = 'selected';
			} else{
				$selected_1 = '';
				$selected_2 = '';
				$selected_3 = '';
				$selected_4 = '';
				$selected_5 = '';
			}
			if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == 'Fewer than 5 People'){
				$fewer_then_5_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '5 - 10 People'){
				$select_5_to_10_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '11 - 50 People'){
				$selected_11_to_50_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '51 - 100 People'){
				$selected_51_to_100_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '101 - 200 People'){
				$selected_101_to_200_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '201 - 300 People'){
				$selected_201_300_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '301 - 500 People'){
				$selected_301_500_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == '501 - 1000 People'){
				$selected_501_to_1000_people_selected = 'selected';
			} else if(!empty($comapny_detail['total_employee']) && $comapny_detail['total_employee'] == 'Above 1000 People'){
				$above_1000_selected = 'selected';
			} else{
				$fewer_then_5_selected = '';
				$select_5_to_10_people_selected = '';
				$selected_11_to_50_people_selected = '';
				$selected_51_to_100_people_selected = '';
				$selected_101_to_200_people_selected = '';
				$selected_201_300_people_selected = '';
				$selected_301_500_people_selected = '';
				$selected_501_to_1000_people_selected = '';
				$above_1000_selected = '';
			}
		
			$alredy_have_company =$this->db->pdoQuery("SELECT company_certificates,id,logo FROM tbl_company WHERE user_id = ? ",array($this->sessUserId))->result();
        		if(empty($alredy_have_company) && empty($alredy_have_company['logo']) && $alredy_have_company['logo'] == ''){
					$hide_image = 'hide';
					$action = 'add';      			
        		}
        		else{
        			$hide_image = '';
        			$action = 'edit';
        		}
        	if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'Below US$1 Million'){
				$total_annual_revanue_1 = 'selected';
        	} else if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'US$1 Million - US$2.5 Million'){
				$total_annual_revanue_2 = 'selected';
        	} else if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'US$2.5 Million - US$5 Million'){
				$total_annual_revanue_3 = 'selected';
        	}else if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'US$5 Million - US$10 Million'){
				$total_annual_revanue_4 = 'selected';
        	}else if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'US$10 Million - US$50 Million'){
				$total_annual_revanue_5 = 'selected';
        	}else if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'US$50 Million - US$100 Million'){
				$total_annual_revanue_6 = 'selected';
        	}else if(!empty($comapny_detail['total_annual_revenue']) && $comapny_detail['total_annual_revenue'] == 'Above US$100 Million'){
				$total_annual_revanue_7 = 'selected';
        	} else{
        		$total_annual_revanue_1 = '';
				$total_annual_revanue_2 = '';
				$total_annual_revanue_3 = '';
				$total_annual_revanue_4 = '';
				$total_annual_revanue_5 = '';
				$total_annual_revanue_6 = '';
				$total_annual_revanue_7 = '';
        	}
        	if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == '0 People'){
				$no_emp_trade_1 = 'selected';
        	} else if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == '1-2 People'){
				$no_emp_trade_2 = 'selected';
        	} else if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == '3-5 People'){
				$no_emp_trade_3 = 'selected';
        	}else if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == '6-10 People'){
				$no_emp_trade_4 = 'selected';
        	}else if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == '11-20 People'){
				$no_emp_trade_5 = 'selected';
        	}else if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == '21-50 People'){
				$no_emp_trade_6 = 'selected';
        	}else if(!empty($comapny_detail['no_employee_trade']) && $comapny_detail['no_employee_trade'] == 'Above 50 People'){
				$no_emp_trade_7 = 'selected';
        	} else{
        		$no_emp_trade_1 = '';
				$no_emp_trade_2 = '';
				$no_emp_trade_3 = '';
				$no_emp_trade_4 = '';
				$no_emp_trade_5 = '';
				$no_emp_trade_6 = '';
				$no_emp_trade_7 = '';
        	}
        	if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '1% - 10%'){
				$export_per_1 = 'selected';
        	} else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '11% - 20%'){
				$export_per_2 = 'selected';
        	} else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '21% - 30%'){
				$export_per_3 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '31% - 40%'){
				$export_per_4 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '41% - 50%'){
				$export_per_5 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '51% - 60%'){
				$export_per_6 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '61% - 70%'){
				$export_per_7 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '71% - 80%'){
				$export_per_8 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '81% - 90%'){
				$export_per_9 = 'selected';
        	}else if(!empty($comapny_detail['export_percentage']) && $comapny_detail['export_percentage'] == '91% - 100%'){
				$export_per_10 = 'selected';
        	} else{
        		$export_per_1 = '';
				$export_per_2 = '';
				$export_per_3 = '';
				$export_per_4 = '';
				$export_per_5 = '';
				$export_per_6 = '';
				$export_per_7 = '';
				$export_per_8 = '';
				$export_per_9 = '';
				$export_per_10 = '';
        	}
        	$accepted_payment_curr = !empty($comapny_detail['accepted_payment_currency']) ? explode(',', $comapny_detail['accepted_payment_currency']) : "";
        	if(!empty($accepted_payment_curr)){
	        	foreach ($accepted_payment_curr as $key => $value_currency) {
		        	if($value_currency == 'USD'){
		    			$usd_ckecked = 'checked';        		
		        	} else if($value_currency == 'EUR'){
						$eur_ckecked = 'checked';        		
		        	} else if($value_currency == 'JPY'){
						$jpy_ckecked = 'checked';
		        	} else if($value_currency == 'CAD'){
						$cad_ckecked = 'checked';
		        	} else if($value_currency == 'AUD'){
						$aud_ckecked = 'checked';
		        	} else if($value_currency == 'KHD'){
						$khd_ckecked = 'checked';
		        	} else if($value_currency == 'GBP'){
						$gbp_ckecked = 'checked';
		        	} else if($value_currency == 'CNY'){
						$cny_ckecked = 'checked';
		        	} else if($value_currency == 'CHF'){
						$chf_ckecked = 'checked';
		        	} else if($value_currency == 'INR'){
						$inr_ckecked = 'checked';
		        	} else{
		        		$usd_ckecked = '';
						$eur_ckecked = '';
						$jpy_ckecked = '';
						$cad_ckecked = '';
						$aud_ckecked = '';
						$khd_ckecked = '';
						$gbp_ckecked = '';
						$cny_ckecked = '';
						$chf_ckecked = '';
						$inr_ckecked = '';
		        	}
	        	}
        	}
        	$all_accepted_item = !empty($comapny_detail['accepted_delivery_items']) ? explode(',', $comapny_detail['accepted_delivery_items']) : "";
        	if(!empty($all_accepted_item)){
	        	foreach ($all_accepted_item as $key => $value) {
		        	if($value == 'FOB'){
		    			$fob_checked = 'checked';        		
		        	} else if($value == 'CFR'){
						$cfr_checked = 'checked';        		
		        	} else if($value == 'CIF'){
						$cif_checked = 'checked';
		        	} else if($value == 'CIF'){
						$exw_checked = 'checked';
		        	} else{
		        		$usd_ckecked = '';
						$eur_ckecked = '';
						$jpy_ckecked = '';
						$cad_ckecked = '';
						$aud_ckecked = '';
						$khd_ckecked = '';
						$gbp_ckecked = '';
						$cny_ckecked = '';
						$chf_ckecked = '';
						$inr_ckecked = '';
		        	}
	        	}
        	}
        	$language_spoken_all = !empty($comapny_detail['language_spoken']) ? explode(',', $comapny_detail['language_spoken']) : "";

        $total_per = checkCompleteProfile($_SESSION['userId']);

        $progress_style = 'style="width:'.$total_per.'%"';

        $edit_profileBtn=$edit_companyIntoBtn=$edit_companyCertiBtn=$edit_ExportCapabilityBtn="";
        if(!empty($_SESSION['moderatorId'])){
        	if(checkModeratorAction($this->module,"edit")){
        		$edit_profileBtn='<a href="#" class="btn btn-system" id="edPro">'.EDIT_PROFILE.'</a>';
        		$edit_companyIntoBtn='<button class="btn btn-system" id="EditCompany">'.EDIT_DETAILS.'</button>';
        		$edit_companyCertiBtn='<button class="btn btn-system editCertificates">'.EDIT_DETAILS.'</button>';
        		$edit_ExportCapabilityBtn='<button class="btn btn-system" id="editExportDetails">'.EDIT_DETAILS.'</button>';
        	}else{
        		$edit_profileBtn=$edit_companyIntoBtn=$edit_companyCertiBtn=$edit_ExportCapabilityBtn="";
        	}
        }else{
        	$edit_profileBtn='<a href="#" class="btn btn-system" id="edPro">'.EDIT_PROFILE.'</a>';
    		$edit_companyIntoBtn='<button class="btn btn-system" id="EditCompany">'.EDIT_DETAILS.'</button>';
    		$edit_companyCertiBtn='<button class="btn btn-system editCertificates" >'.EDIT_DETAILS.'</button>';
    		$edit_ExportCapabilityBtn='<button class="btn btn-system" id="editExportDetails">'.EDIT_DETAILS.'</button>';
        }

        $user_detail =$this->db->pdoQuery("SELECT store_front FROM tbl_users WHERE id = ? ",array($this->sessUserId))->result();
        $current_plan_name=$this->db->pdoQuery("SELECT u.id,m.plan_name FROM tbl_users AS u 
        									INNER JOIN tbl_membership_plan AS m ON m.id=u.plan_id 
        									WHERE u.id=?",array($_SESSION['userId']))->result();
        $websiteWithSecure = $website = $selectedHttps = $selectedHttp = '';

		if (!empty($comapny_detail['web_url'])) {
			$websiteArr = explode('://', $comapny_detail['web_url']);
			if (isset($websiteArr[1])) {
				$website = $websiteArr[1];
				if ($websiteArr[0] == 'http') {
					$selectedHttp = 'selected';
				}else{
					$selectedHttps = 'selected';
				}
			}else {
				$website = $user_detail['website'];
			}
			$websiteWithSecure = $websiteArr[0].'://'.$website;
		}
		
		$paymentCurrency = $this->getPaymentCurrency($accepted_payment_curr);
		$spokenLanguage = $this->getSpokenLanguage($language_spoken_all);


		$deliveryTermsData = $this->db->pdoQuery("SELECT * FROM tbl_delivery_terms where isActive = 'y'",[])->results();
		$delivery_terms_li = '<option value="%ID%" %SELECTED% >%VALUE%</option>';
		$selected_delivery_terms = !empty($comapny_detail['accepted_delivery_items']) ? explode(',',$comapny_detail['accepted_delivery_items']) : [];

		$deliveryTermsHtml = '';
		$accepted_delivery_items_display = [];
		foreach ($deliveryTermsData as $key => $dValue) {
			$selected="";
			if(in_array($dValue['id'], $selected_delivery_terms)){
				$selected="selected";
				$accepted_delivery_items_display[] = $dValue['delivery_terms'];
			}
			$deliveryTermsHtml .= str_replace(["%ID%", "%VALUE%", "%SELECTED%"], [$dValue['id'],$dValue['delivery_terms'], $selected], $delivery_terms_li);
		}
		
		$mainProductDiv = '<div class="col-sm-9">
			<input type="text" name="main_product[]" id="main_product" class="form-control" value="%MAIN_PRODUCT%">
			<br>
		</div>';
		$accepted_delivery_items_display = implode(",", $accepted_delivery_items_display);
		$mainProductContent = '';
		if (!empty($main_product_data)) {
			foreach ($main_product_data as $key => $mProduct) {
				$mainProField = ["%MAIN_PRODUCT%" => $mProduct];
				$mainProductContent.=str_replace(array_keys($mainProField), array_values($mainProField), $mainProductDiv);
			}
		}
		$storeSelectedOne = $storeSelectedTwo = $storeSelectedThree = '';
		if (!empty($comapny_detail['selected_template']) && $comapny_detail['selected_template'] == 'one') {
			$storeSelectedOne = 'cp-check1';
		} else if (!empty($comapny_detail['selected_template']) && $comapny_detail['selected_template'] == 'two') {
			$storeSelectedTwo = 'cp-check1';
		} else if (!empty($comapny_detail['selected_template']) && $comapny_detail['selected_template'] == 'three') {
			$storeSelectedThree = 'cp-check1';
		}
        $fields = array(
        	"%EMAIL_VERIFIED_CLASS_LBL%" => (!empty($comapny_detail['email_verified']) &&$comapny_detail['email_verified'] == 'y') ? '': 'hide',
        	"%EMAIL_VERIFIED_CLASS_BTN%" => (!empty($comapny_detail['email_verified']) &&$comapny_detail['email_verified'] == 'y') ? 'hide': '',
        	"%NO_VERIFIED_CLASS_LBL%" => (!empty($comapny_detail['no_verified']) && $comapny_detail['no_verified'] == 'y') ? '': 'hide',
        	"%NO_VERIFIED_CLASS_BTN%" => (!empty($comapny_detail['no_verified']) && $comapny_detail['no_verified'] == 'y') ? 'hide': '',
        	"%HASH%" => !empty($comapny_detail['hash']) ? $comapny_detail['hash'] : "",
        	"%EMAIL%" => !empty($comapny_detail['company_mail']) ? $comapny_detail['company_mail'] : "",
        	"%TOTAL_PER%" => $progress_style,
        	"%TOTAL_PER_VAL%" => $total_per.'%',
    		"%CERTY_ACTION%"=>(empty($alredy_have_company['company_certificates'])) ? 'add':'edit',
			"%TOKEN%"=>genrateRandom(),
			"%COMPANY_ID%"=>!empty($comapny_detail['id'])?$comapny_detail['id']:'',
			"%LEFT_PANEL%"=>$leftPanel,
			"%BUSINESSTYPE%"=>$business_types,
			"%LOGO%"=>!empty($comapny_detail['logo']) ? SITE_UPD.'supplier_logo/'.$comapny_detail['logo'] : SITE_UPD.'no_user_image.png',
			"%OLD_LOGO%"=>!empty($comapny_detail['logo']) ? SITE_UPD.'supplier_logo/'.$comapny_detail['logo'] : SITE_UPD.'no_user_image.png',
			"%COMPANY_NAME%"=>!empty($comapny_detail['company_name']) ? $comapny_detail['company_name'] : '',
			"%COMPANY_MAIL%"=>!empty($comapny_detail['company_mail']) ? $comapny_detail['company_mail'] : '',
			"%CONTACT_NO_1%"=>!empty($comapny_detail['contact_no_1']) ? $comapny_detail['contact_no_1'] : '',
			"%CONTACT_NO_2%"=>!empty($comapny_detail['contact_no_2']) ? $comapny_detail['contact_no_2'] : '',
			"%COMPANY_ADDRESS%"=>!empty($comapny_detail['company_address']) ? $comapny_detail['company_address'] : '',
			"%COMPANY_ADDRESS_2%"=>!empty($comapny_detail['company_address_2']) ? $comapny_detail['company_address_2'] : '',
			"%ZIPCODE%"=>!empty($comapny_detail['zipcode']) ? $comapny_detail['zipcode'] : '',
			"%LOCATION%"=>!empty($comapny_detail['location']) ? $comapny_detail['location'] : '',
			"%MAIN_PRODUCT_DATA%"=>$mainProductContent,
			"%WEB_URL%"=> $website,
			"%WEB_URL_SECURE%"=> $websiteWithSecure,

			"%selectedHttp%"=> $selectedHttp,
			"%selectedHttps%"=> $selectedHttps ,
			"%LEGAL_OWNER%"=>!empty($comapny_detail['legal_owner']) ? $comapny_detail['legal_owner'] : '',
			"%SELECTED_1%"=>$selected_1,
			"%SELECTED_2%"=>$selected_2,
			"%SELECTED_3%"=>$selected_3,
			"%SELECTED_4%"=>$selected_4,
			"%SELECTED_5%"=>$selected_5,
			"%FEWER_THEN_5_SELECTED%"=>$fewer_then_5_selected,
			"%5_TO_10_PEOPLE_SELECTED%"=>$select_5_to_10_people_selected,
			"%11_TO_50_PEOPLE_SELECTED%"=>$selected_11_to_50_people_selected,
			"%51_TO_100_PEOPLE_SELECTED%"=>$selected_51_to_100_people_selected,
			"%101_TO_200_PEOPLE_SELECTED%"=>$selected_101_to_200_people_selected,
			"%201_300_PEOPLE_SELECTED%"=>$selected_201_300_people_selected,
			"%301_500_PEOPLE_SELECTED%"=>$selected_301_500_people_selected,
			"%501_TO_1000_PEOPLE_SELECTED%"=>$selected_501_to_1000_people_selected,
			"%ABOVE_1000_SELECTED%" =>$above_1000_selected,
			"%SELECTED_YEAR%"=>!empty($comapny_detail['registered_year']) ? $comapny_detail['registered_year'] : '',
			"%CONTACT_NAME%"=>!empty($comapny_detail['contact_person_name']) ? $comapny_detail['contact_person_name'] : '',
			"%COMPANY_ADVANTAGE%"=>!empty($comapny_detail['company_advantage']) ? $comapny_detail['company_advantage'] : '',
			"%BANK_NAME%"=>!empty($comapny_detail['bank_name']) ? $comapny_detail['bank_name'] : '',
			"%BANK_ACCOUNT_NUMBER%"=>!empty($comapny_detail['bank_account_number']) ? $comapny_detail['bank_account_number'] : '',
			"%MAIN_PRO%"=>!empty($comapny_detail['main_product']) ? $comapny_detail['main_product'] : '',
			"%OFFICE_SIZE%"=>!empty($comapny_detail['office_size']) ? $comapny_detail['office_size'] : '',
			"%TOTAL_EMPLOYEE%"=>!empty($comapny_detail['total_employee']) ? $comapny_detail['total_employee'] : '',
			"%BUSINESSTYPEID%"=>$business_type_id_view,
			"%HIDE_IMAGE%"=>$hide_image,
			"%ACTION%"=>$action,
			"%DETAILED_DESCRIPTION%"=>!empty($comapny_detail['detailed_description']) ? $comapny_detail['detailed_description'] : '',
			"%COMPANY_PHOTO%"=>!empty($alredy_have_company['id']) ? $this->getAllPics('',$alredy_have_company['id']) : '',
			"%COMPANY_IMAGE_ALREADY_ADDED%"=>!empty($alredy_have_company['id']) ? $this->alredy_added_pics($alredy_have_company['id']) : '<div class="col-md-12"><span class="no-image"><i class="fa fa-exclamation-circle"></i> '.MSG_NO_RECORDS_FOUND.' </span></div>',
			"%COMPANY_ALL_CERTIFICATION%"=>!empty($alredy_have_company['id']) ? $this->all_certificate($alredy_have_company['id']) : '<div class="col-md-12"><span class="no-image"><i class="fa fa-exclamation-circle"></i> '.MSG_NO_RECORDS_FOUND.' </span></div>',
			"%CERTY_PHOTOS%"=>!empty($alredy_have_company['id']) ? $this->getAllcerty('',$alredy_have_company['id']) : $this->getAllcerty('',0),
			"%TOTAL_ANNUAL_REVANUE%" =>!empty($comapny_detail['total_annual_revenue']) ? $comapny_detail['total_annual_revenue'] : '',
			"%TOTAL_ANNUAL_REVANUE_1%" =>$total_annual_revanue_1,
			"%TOTAL_ANNUAL_REVANUE_2%" =>$total_annual_revanue_2,
			"%TOTAL_ANNUAL_REVANUE_3%" =>$total_annual_revanue_3,
			"%TOTAL_ANNUAL_REVANUE_4%" =>$total_annual_revanue_4,
			"%TOTAL_ANNUAL_REVANUE_5%" =>$total_annual_revanue_5,
			"%TOTAL_ANNUAL_REVANUE_6%" =>$total_annual_revanue_6,
			"%TOTAL_ANNUAL_REVANUE_7%" =>$total_annual_revanue_7,
			"%EXPORT_PER_1%" => $export_per_1,
			"%EXPORT_PER_2%" => $export_per_2,
			"%EXPORT_PER_3%" => $export_per_3,
			"%EXPORT_PER_4%" => $export_per_4,
			"%EXPORT_PER_5%" => $export_per_5,
			"%EXPORT_PER_6%" => $export_per_6,
			"%EXPORT_PER_7%" => $export_per_7,
			"%EXPORT_PER_8%" => $export_per_8,
			"%EXPORT_PER_9%" => $export_per_9,
			"%EXPORT_PER_10%" => $export_per_10,
			"%EXPORT_PERCENTAGE%" =>!empty($comapny_detail['export_percentage']) ? $comapny_detail['export_percentage'] : '',
			"%MARKRT_NAMES_1%" => $market_per['North_America'],
			"%MARKRT_NAMES_2%" => $market_per['South_America'],
			"%MARKRT_NAMES_3%" => $market_per['Eastern_Europe'],
			"%MARKRT_NAMES_4%" => $market_per['Southeast_Asia'],
			"%MARKRT_NAMES_5%" => $market_per['Africa'],
			"%MARKRT_NAMES_6%" => $market_per['Oceania'],
			"%MARKRT_NAMES_7%" => $market_per['Mid_East'],
			"%MARKRT_NAMES_8%" => $market_per['Eastern_Asia'],
			"%MARKRT_NAMES_9%" => $market_per['Western_Europe'],
			"%MARKRT_NAMES_10%" => $market_per['Central_America'],
			"%MARKRT_NAMES_11%" => $market_per['Northern_Europe'],
			"%MARKRT_NAMES_12%" => $market_per['Southern_Europe'],
			"%MARKRT_NAMES_13%" => $market_per['South_Asia'],
			"%MARKRT_NAMES_14%" => $market_per['Domestic_Market'],
			"%TOTAL_PROGRESS_STYLE%" => ($market_per['total'] == '0') ? 'style="width:0%"' : 'style="width:'.$market_per['total'].'%"',
			"%TOTAL_PROGRESS%" => ($market_per['total'] == '0') ? '<p class="total_final">0%</p>' : '<p class="total_final">'.$market_per['total'].'%</p>',
			"%FINAL_TOTAL%" =>$market_per['total'],
			"%STARTED_EXPLORING%" =>!empty($comapny_detail['started_exploring']) ? $comapny_detail['started_exploring'] : '',
			"%NO_EMP_TRADE_1%" => $no_emp_trade_1,
			"%NO_EMP_TRADE_2%" => $no_emp_trade_2,
			"%NO_EMP_TRADE_3%" => $no_emp_trade_3,
			"%NO_EMP_TRADE_4%" => $no_emp_trade_4,
			"%NO_EMP_TRADE_5%" => $no_emp_trade_5,
			"%NO_EMP_TRADE_6%" => $no_emp_trade_6,
			"%NO_EMP_TRADE_7%" => $no_emp_trade_7,
			"%FOB_CHECKED%" => $fob_checked,
			"%CFR_CHECKED%" => $cfr_checked,
			"%CIF_CHECKED%" => $cif_checked,
			"%EXW_CHECKED%" => $exw_checked,
			"%USD_CKECKED%" => $usd_ckecked,
			"%EUR_CKECKED%" => $eur_ckecked,
			"%JPY_CKECKED%" => $jpy_ckecked,
			"%CAD_CKECKED%" => $cad_ckecked,
			"%AUD_CKECKED%" => $aud_ckecked,
			"%KHD_CKECKED%" => $khd_ckecked,
			"%GBP_CKECKED%" => $gbp_ckecked,
			"%CNY_CKECKED%" => $cny_ckecked,
			"%CHF_CKECKED%" => $chf_ckecked,
			"%INR_CKECKED%" => $inr_ckecked,
			"%NO_EMPLOYEE_TRADE%" => !empty($comapny_detail['no_employee_trade']) ? $comapny_detail['no_employee_trade'] : '',
			"%ACCEPTED_DELIVERY_ITEMS%" => $accepted_delivery_items_display,
			"%PAY_CURRENCY%" => !empty($comapny_detail['accepted_payment_currency']) ? $comapny_detail['accepted_payment_currency'] : '',
			"%LANGUAGE_SPOKEN%" => !empty($comapny_detail['language_spoken']) ? $comapny_detail['language_spoken'] : '',
			"%CONTACTNO_VERIFICATION_CODE%" => !empty($comapny_detail['contactno_verification_code']) ? $comapny_detail['contactno_verification_code']: '',
			"%EDIT_PROFILEBTN%"=>$edit_profileBtn,
			"%EDIT_COMPANYINTOBTN%"=>$edit_companyIntoBtn,
			"%EDIT_COMPANYCERTIBTN%"=>$edit_companyCertiBtn,
			"%EDIT_EXPORTCAPABILITYBTN%"=>$edit_ExportCapabilityBtn,
			"%CMPNY_SLUG%"=>!empty($comapny_detail['company_slug']) ? $comapny_detail['company_slug'] : '',
			"%STOREFRONT_AVAILABLE%"=>($user_detail['store_front'] == 'n')?'':'<li><a data-toggle="pill" href="#menu4">Storefront</a></li>',
			"%CURRENT_PLAN_NAME%"=>$current_plan_name['plan_name'],
			"%PAYMENT_CURRENCY%" => $paymentCurrency,
			"%SPOKEN_LANGUAGE%" => $spokenLanguage,
			"%ACCEPTED_DELIVERY_TERMS%"=>$deliveryTermsHtml,
			"%STORE_SELECTED_ONE%"=>$storeSelectedOne,
			"%STORE_SELECTED_TWO%"=>$storeSelectedTwo,
			"%STORE_SELECTED_THREE%"=>$storeSelectedThree,
			"%BANK_NAME_LIST%"=>$bank_name
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);

		return $html;
	}

	public function alredy_added_pics($old_id="") {
		$final_content = $main_content ='';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/already_add_image-sd.skd"))->compile();
		$comapny_detail =$this->db->pdoQuery("SELECT company_photos FROM tbl_company WHERE user_id = ? AND id= ?",array($this->sessUserId,$old_id))->result();
		$all_company_image = explode(',',$comapny_detail['company_photos']);
		foreach ($all_company_image as $key => $value) {
			if($value == '') {
				$value = 'no_img.png';
			}else{
				$value = $value;
			}
			$fields = array(
						"%IMAGE_SRC%" => SITE_UPD.'supplier_photos/'.$value
					);
					$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
		}
		return $final_content;
	}
	public function all_certificate($old_id="") {
		$final_content = $main_content ='';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/company_certificate-sd.skd"))->compile();
		$comapny_detail =$this->db->pdoQuery("SELECT cc.* FROM tbl_company_certificates as cc 
		JOIN tbl_company as c ON(c.id=cc.company_id) WHERE c.user_id = ? AND c.id= ?",array($this->sessUserId,$old_id))->results();
		foreach ($comapny_detail as $key => $certy) {
			if(empty($certy['image']) ) {
				$value = 'no_img.png';
			}else{
				$value = $certy['image'];
			}
			$fields = array(
						"%COMPANY_CERTY%" => SITE_UPD.'supplier_certificate/'.$value,
						"%CERTY_TITLE%" => !empty($certy['title']) ? $certy['title'] : ""
					);
					$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
		}
		return $final_content;
	}
	
	public function getAllCertiPics($token="",$productId="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images_certy-sd.skd"))->compile();

		if(!empty($token)){
			$getImages = $this->db->select('temp_product_images', array('id', 'file_name', 'title'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{
			$getProductImg=$this->db->select("tbl_company",array("id","company_certificates"),array("id"=>$id_comapany))->result();
			if(!empty($getProductImg['company_certificates'])){
				$getImages=explode(',',$getProductImg['company_certificates']);
			}
		}
		if(!empty($getImages)) {
			$ImgNo = 0;
			foreach($getImages AS $key => $values) {
				
				if(!empty($id_comapany)){			
					$Id="pro-".$id_comapany."-".$ImgNo++;
					$file_name=$values;
				}else{
					$Id=$values['id'];
					$file_name=$values['file_name'];
				}			
				$active = (empty($key) ? 'active' : '');
				$fields = array(
					"%ID%" => $Id,
					"%IMG_NAME%" => $file_name,
					"%IMAGES%" => SITE_UPD.'supplier_certificate/'.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active,
					"%CERTY_TITLE%" => !empty($values['title']) ? $values['title'] : ""

				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}
	public function getAllPics($token="",$productId="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();

		if(!empty($token)){
			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{

			$getProductImg=$this->db->select("tbl_company",array("id","company_photos"),array("id"=>$productId))->result();
			if(!empty($getProductImg['company_photos'])){
				$getImages=explode(',',$getProductImg['company_photos']);
			}
		}

		if(!empty($getImages)) {			
			$ImgNo = 0;
			foreach($getImages AS $key => $values) {
				
				if(!empty($productId)){			
					$Id="pro-".$productId."-".$ImgNo++;
					$file_name=$values;
				}else{
					$Id=$values['id'];
					$file_name=$values['file_name'];
				}			
				$active = (empty($key) ? 'active' : '');
				$fields = array(
					"%ID%" => $Id,
					"%IMG_NAME%" => $file_name,
					"%IMAGES%" => SITE_UPD.'supplier_photos/'.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active,
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}

	public function getAllcerty($token="",$id_comapany="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images_certy-sd.skd"))->compile();
		$comapny_detail =$this->db->pdoQuery("SELECT cc.* FROM tbl_company_certificates as cc 
		JOIN tbl_company as c ON(c.id=cc.company_id) WHERE c.user_id = ? AND c.id= ?",array($this->sessUserId,$id_comapany))->results();

		if(!empty($comapny_detail)) {			
			$ImgNo = 0;
			foreach($comapny_detail AS $key => $values) {
				
				if(!empty($id_comapany)){
					$Id="pro-".$values['id'];
					$file_name=$values['image'];
				}
				$active = (empty($key) ? 'active' : '');
				$fields = array(
					"%ID%" => $Id,
					"%IMG_NAME%" => $file_name,
					"%IMAGES%" => SITE_UPD.'supplier_certificate/'.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active,
					"%CERTY_TITLE%" => !empty($values['title']) ? $values['title'] : ""
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}

	public function submit_company_basic($data) {
		extract($data);
		$hash = '';
        $hash = generateRandString();
		$business_type_id = implode(',',$business_type_id);
		$main_product_all = implode(',',array_filter($main_product));
		$objPost = new stdClass();
		$objPost->company_name=!empty($company_name)?$company_name:'';
		$objPost->company_mail=!empty($company_mail)?$company_mail:'';
		$objPost->contact_no_1=!empty($contact_no_1)?$contact_no_1:'';
		$objPost->contact_no_2=!empty($contact_no_2)?$contact_no_2:'';
		$objPost->company_address=!empty($company_address)?$company_address:'';
		$objPost->company_address_2=!empty($company_address_2)?$company_address_2:'';
		$objPost->total_employee=!empty($total_employee)?$total_employee:'';
		$objPost->web_url=!empty($web_url)? $http.$web_url:'';
		$objPost->legal_owner=!empty($legal_owner)?$legal_owner:'';
		$objPost->office_size=!empty($office_size)?$office_size:'';
		$objPost->registered_year=!empty($registered_year)?$registered_year:'';
		$objPost->contact_person_name=!empty($contact_person_name)?$contact_person_name:'';

		$objPost->bank_name=!empty($bank_name)?$bank_name:'';

		// ming lau -> add bank_code
		$bank_code_id = "";
		if(!empty($bank_code)) {
			$bank_detail = $this->db->pdoQuery("SELECT id FROM tbl_bank WHERE bank_code = ? ",array($bank_code))->result();
			$bank_code_id = $bank_detail['id'];
		}

		$objPost->bank_code_id=!empty($bank_code_id)?$bank_code_id:''; 
		$objPost->bank_account_number=!empty($bank_account_number)?$bank_account_number:'';

		$objPost->company_advantage=!empty($company_advantage)?$company_advantage:'';
		$objPost->business_type_id=!empty($business_type_id)?$business_type_id:'';
		$objPost->main_product=!empty($main_product_all)?$main_product_all:'';
		$objPost->zipcode=!empty($zipcode)?$zipcode:'';
		$objPost->location=!empty($location)?$location:'';
		$objPost->user_id=$this->sessUserId;
		$objPost->created_date=date('Y-m-d H:i:s');
		$objPost->company_slug = slug($objPost->company_name);

		if(isset($_FILES['logo']) && $_FILES['logo']['name']!="")
        {           
                $imageName = $_FILES['logo']['name'];
                $tmp_name = $_FILES['logo']['tmp_name'];
                $imageType = $_FILES['logo']['type'];
                $imageSize = $_FILES['logo']['size'];
                $upload_dir = DIR_UPD.'supplier_logo/';
                $ext = '.'.strtoupper(getExt($imageName));
                $newName = rand().time();
                $th_arr = array();
                $th_arr[0] = array('width' => '120', 'height' => '90');                        
                $th_arr[1] = array('width' => '360', 'height' => '240'); 
				$isUploadImage = move_uploaded_file($tmp_name, DIR_UPD.'supplier_logo/'.$newName.$ext);   
                if($isUploadImage) {
                    $objPost->logo = $newName.$ext;                
                }
                if (file_exists(DIR_UPD.'supplier_logo/'.$old_logo)) {
					unlink(DIR_UPD.'supplier_logo/'.$old_logo);
				}
        }
        $userEmail = getTableValue('tbl_users', 'email', array('id'=>$this->sessUserId));
        if (trim($objPost->company_mail) == $userEmail) {
        	$objPost->email_verified = 'y';
        }else{
        	$objPost->email_verified = 'n';
        }
        if(!empty($objPost->company_name) && !empty($objPost->company_mail) && !empty($objPost->contact_no_1) && !empty($objPost->company_address) && !empty($objPost->total_employee) && !empty($objPost->web_url) && !empty($objPost->legal_owner) && !empty($objPost->office_size)  && !empty($objPost->registered_year)  && !empty($objPost->contact_person_name) && !empty($objPost->bank_name) && !empty($objPost->bank_account_number) && !empty($objPost->location) && !empty($objPost->zipcode) && !empty($objPost->company_advantage) && !empty($objPost->business_type_id) && !empty($objPost->main_product) && !empty($this->sessUserId)){ 
        		$alredy_have_company =$this->db->pdoQuery("SELECT id FROM tbl_company WHERE user_id = ? ",array($this->sessUserId))->affectedRows();
        		if($alredy_have_company == 0){
        			$objPostArray_add = (array) $objPost;
					$objPost->hash=$hash;
					$id = $this->db->insert('tbl_company',$objPostArray_add)->getLastInsertId(); 
					if(!empty($_SESSION['moderatorId'])){
						$moderatorActivityArray=array("activity"=>"Add","page"=>$this->module,"moderator_id"=>$_SESSION['	moderatorId'],"entity_id"=>$id,"entity_action"=>"Add Basic Company Details");
        				add_moderator_activity($moderatorActivityArray); 			
					}
        		}
        		else{
        			$objPostArray_edit = (array) $objPost;
        			if(!empty($_SESSION['moderatorId'])){
	        			$moderatorActivityArray=array("activity"=>"Edit","page"=>$this->module,"moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$this->sessUserId,"entity_action"=>"Edit Basic Company Details");
	        			add_moderator_activity($moderatorActivityArray);
	        		}
        			$CId = $this->db->update('tbl_company', $objPostArray_edit,array('user_id'=>$this->sessUserId));
        		}
        		$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>PROFILE_UPDATED_SUCCESSFULLY));
				redirectPage(SITE_URL.'supplier-profile?menu=menu1');

        }else{
        	$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
			RedirectPage(SITE_URL.'supplier-profile');
        }		
	}
	public function submit_comapny_detail_desc($data){
		extract($data);
		$objPost = new stdClass();
		$objPost->user_id=$_SESSION['userId'];
		$objPost->detailed_description=!empty($detailed_description)?$detailed_description:"";
		$objPost->updated_date=date('Y-m-d H:i:s');
		$objPost->company_photos="";

			$frmToken=!empty($frmToken)?$frmToken:'';

			$getImgs=$this->db->select("temp_product_images",array("file_name","actual_file_name"),array("user_id"=>$_SESSION['userId'],"token"=>$frmToken))->results();
			$db_name_attachment="";

			if(!empty($getImgs)){
				$lastImg=end($getImgs);

				foreach ($getImgs as $key => $value) {
					if($value['file_name']==$lastImg['file_name']){
						$db_name_attachment.=$value['file_name'];
					}else{
						$db_name_attachment.=$value['file_name'].",";
					}
				}
				$objPost->company_photos=$db_name_attachment;
				
				$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmToken));
			}
			$alredy_have_company =$this->db->pdoQuery("SELECT id,logo FROM tbl_company WHERE user_id = ? ",array($this->sessUserId))->affectedRows();
			if($alredy_have_company != 0){	
				$oldImg=$this->db->select("tbl_company",array("company_photos"),array("id"=>$comapny_id))->result();
				if(!empty($oldImg['company_photos'])){
					$objPost->company_photos=!empty($objPost->company_photos)?$oldImg['company_photos'].",".$objPost->company_photos:$oldImg['company_photos'];
				}
			}
			$objPostArray = (array) $objPost;
			if(!empty($_SESSION['moderatorId'])){
	        	$moderatorActivityArray=array("activity"=>"Edit","page"=>$this->module,"moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$this->sessUserId,"entity_action"=>"Edit Company Introduction");
	        	add_moderator_activity($moderatorActivityArray);
	        }
			$CId = $this->db->update('tbl_company', $objPostArray,array('user_id'=>$this->sessUserId));
			$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>COMPANY_INFOR_UPDATE_SUCCESS));
			redirectPage(SITE_URL.'supplier-profile?menu=menu2');
	}
	public function submit_export_capability($data) {
		extract($data);
		$accepted_delivery_items_ad = implode(',',$accepted_delivery_items);
		$accepted_payment_currency_ad = implode(',',$accepted_payment_currency);
		$language_spoken_ad = implode(',',$language_spoken);

		$objPost = new stdClass();
		$objPost->accepted_delivery_items=!empty($accepted_delivery_items_ad)?$accepted_delivery_items_ad:'';
		$objPost->accepted_payment_currency=!empty($accepted_payment_currency_ad)?$accepted_payment_currency_ad:'';
		$objPost->language_spoken=!empty($language_spoken_ad)?$language_spoken_ad:'';
		$objPost->total_annual_revenue=!empty($total_annual_revenue)?$total_annual_revenue:'';
		$objPost->export_percentage=!empty($export_percentage)?$export_percentage:'';
		$objPost->no_employee_trade=!empty($no_employee_trade)?$no_employee_trade:'';
		$objPost->started_exploring=!empty($started_exploring)?$started_exploring:'';
		$alredy_have_markets =$this->db->pdoQuery("SELECT id FROM tbl_market_and_distribution WHERE user_id = ?",array($this->sessUserId))->affectedRows();
		$total = $market_names[0]+$market_names[1]+$market_names[2]+$market_names[3]+$market_names[4]+$market_names[5]+$market_names[6]+$market_names[7]+$market_names[8]+$market_names[9]+$market_names[10]+$market_names[11]+$market_names[12]+$market_names[13];
		if($alredy_have_markets == 0){
			$id_market_name = $this->db->insert('tbl_market_and_distribution',array("total"=>$total,"user_id"=>$this->sessUserId,"North_America"=>$market_names[0],"South_America"=>$market_names[1],"Eastern_Europe"=>$market_names[2],"Southeast_Asia"=>$market_names[3],"Africa"=>$market_names[4],"Oceania"=>$market_names[5],"Mid_East"=>$market_names[6],"Eastern_Asia"=>$market_names[7],"Western_Europe"=>$market_names[8],"Central_America"=>$market_names[9],"Northern_Europe"=>$market_names[10],"Southern_Europe"=>$market_names[11],"South_Asia"=>$market_names[12],"Domestic_Market"=>$market_names[13]))->getLastInsertId();     
				$objPost->market_names=!empty($id_market_name)?$id_market_name:'';
		}else{
			$this->db->update('tbl_market_and_distribution',array("total"=>$total,"North_America"=>$market_names[0],"South_America"=>$market_names[1],"Eastern_Europe"=>$market_names[2],"Southeast_Asia"=>$market_names[3],"Africa"=>$market_names[4],"Oceania"=>$market_names[5],"Mid_East"=>$market_names[6],"Eastern_Asia"=>$market_names[7],"Western_Europe"=>$market_names[8],"Central_America"=>$market_names[9],"Northern_Europe"=>$market_names[10],"Southern_Europe"=>$market_names[11],"South_Asia"=>$market_names[12],"Domestic_Market"=>$market_names[13]),array('user_id'=>$this->sessUserId)); 
		}
        if(!empty($objPost->accepted_delivery_items) && !empty($objPost->accepted_payment_currency) && !empty($objPost->language_spoken) && !empty($this->sessUserId) && !empty($objPost->total_annual_revenue)&& !empty($objPost->export_percentage)&& !empty($started_exploring) && !empty($objPost->no_employee_trade)){ 
        		$alredy_have_company =$this->db->pdoQuery("SELECT id FROM tbl_company WHERE user_id = ? AND id = ?",array($this->sessUserId,$comapny_id))->affectedRows();
        		if($alredy_have_company == 0){
        			$objPostArray = (array) $objPost;
					$id = $this->db->insert('tbl_company',$objPostArray)->getLastInsertId();  
					if(!empty($_SESSION['moderatorId'])){
						$moderatorActivityArray=array("activity"=>"Add","page"=>$this->module,"moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$id,"entity_action"=>"Add Export Capability");
	        			add_moderator_activity($moderatorActivityArray);   
	        		}  			
        		}
        		else{
        			if(!empty($_SESSION['moderatorId'])){
	        			$moderatorActivityArray=array("activity"=>"Edit","page"=>$this->module,"moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$this->sessUserId,"entity_action"=>"Edit Export Capability");
	        			add_moderator_activity($moderatorActivityArray);
	        		}
        			$CId = $this->db->update('tbl_company', (array)$objPost,array('user_id'=>$this->sessUserId,'id'=>$comapny_id));
        		}
        		$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>EXPORT_CAPABILITY_SUCCESS));

        		if ($_SESSION["user_type"] != 1) {

                    if (ifFreePlan($_SESSION["userId"])) {
                    	$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>UPGRADE_FREE_MEMBERSHIP_PLAN));
                        redirectPage(SITE_URL.'membership-plan');
                    }
                }
				RedirectPage(SITE_URL.'supplier-profile?menu=menu3');
        }else{
        	$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
			RedirectPage(SITE_URL.'supplier-profile');
        }		
	}

	public function getPaymentCurrency($accepted_payment_curr) {


		$paymentCurrencyLi = '<label class="checkbox-inline %LAST_CLASS%">
								<input type="checkbox" class="add_vall_curr" name="accepted_payment_currency[]" id="accepted_payment_currency" value="%CURRENCY%" %CURR_CKECKED%>
								%CURRENCY%
							</label>';
		$paymentCurrency =$this->db->pdoQuery("SELECT * FROM tbl_currency WHERE isActive = ? ",array('y'))->results();
		$accepted_payment_curr = !empty($accepted_payment_curr) ? $accepted_payment_curr : [];
		$currencyContent = '';
		if (!empty($paymentCurrency)) {
			$totalCurr = count($paymentCurrency);
			foreach ($paymentCurrency as $key => $currencyData) {
				$selectedCur = "";
				if(in_array($currencyData['currency'], $accepted_payment_curr)) {
					$selectedCur = "checked";
				}
				$addLastClass = ( $totalCurr == $key + 1)? 'add_vall_curr_last' : "";

				$fields = [
					"%CURR_CKECKED%" => $selectedCur,
					"%CURRENCY%" => $currencyData['currency'],
					"%LAST_CLASS%" => $addLastClass,
				];


				$currencyContent .= str_replace(array_keys($fields), array_values($fields), $paymentCurrencyLi);

			}
		}
		return $currencyContent;
	}
	public function getSpokenLanguage($language_spoken_all) {


		$spokenLi = '<label class="checkbox-inline %LAST_CLASS%">
								<input type="checkbox" class="add_vall" name="language_spoken[]" id="language_spoken" value="%LANGUAGE%" %CHECKED_LANGUAGE%>
								%LANGUAGE%
							</label>';

		$spokenLanguageData =$this->db->pdoQuery("SELECT * FROM tbl_spoken_language WHERE isActive = ? ORDER BY language ",array('y'))->results();
		$language_spoken_all = !empty($language_spoken_all) ? $language_spoken_all : [];
		$spokenContent = '';
		$totalLang = count($spokenLanguageData);

		if (!empty($spokenLanguageData)) {
			foreach ($spokenLanguageData as $key => $spokenData) {
				$selectedCur = "";
				if(in_array($spokenData['language'], $language_spoken_all)) {
					$selectedCur = "checked";
				}
				$addLastClass = ( $totalLang == $key + 1)? 'add_vall_last' : "";

				$fields = [
					"%CHECKED_LANGUAGE%" => $selectedCur,
					"%LANGUAGE%" => $spokenData['language'],
					"%LAST_CLASS%" => $addLastClass,

				];

				$spokenContent .= str_replace(array_keys($fields), array_values($fields), $spokenLi);

			}
		}
		return $spokenContent;
	}
}
?>