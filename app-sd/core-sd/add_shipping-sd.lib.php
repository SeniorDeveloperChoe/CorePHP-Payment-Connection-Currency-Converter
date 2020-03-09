<?php
class AddProduct extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}


	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$maincatName="m.maincatName_".$this->curr_language." AS maincatName";
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";
		$chkFobYes=$chkFobNo=$chkPremiumYes=$chkPremiumNo=$product_id=$dynamicQuestion="";
		$action="add";
		$negotiableYes=$negotiableNo=$hideMaxPricess=$HideToText="";
		$MaxPrice="";
		$productId = !empty($_GET['productId']) ? $_GET['productId'] : 0;
		
		$no_product_post=$this->db->pdoQuery("SELECT COUNT(p.id) AS totalPostProducts,u.product_post,u.product_mark_premium FROM `tbl_products` AS p INNER JOIN tbl_users AS u on u.id=p.user_id WHERE user_id=?",array($_SESSION['userId']))->result();
		

		if(!empty($this->id)){
			$action="edit";
			$getProductData=$this->db->pdoQuery("SELECT p.*,p.id AS productId,s.id AS shippingId,s.*
											FROM tbl_shipping_management AS s
											INNER JOIN tbl_products AS p ON p.id=s.product_id
											WHERE s.id =?",array($this->id))->result();	

				$product_id=$getProductData['shippingId'];

			} else {
				$action="edit";
				$getProductData=$this->db->pdoQuery("SELECT p.*,p.id AS productId,s.id AS shippingId,s.*
				FROM tbl_shipping_management AS s
				RIGHT JOIN tbl_products AS p ON p.id=s.product_id
				WHERE p.id =?",array($productId))->result();
			}

		$unit="unit_value_".$this->curr_language." AS unitName";
		$mainCategory=$Category=$subCategory=$Country_select="";
		
		$Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName FROM tbl_country WHERE isActive=?",array('y'))->results();
				foreach ($Country_select_all as $key => $value) {
					$selected="";
					$Country_select.="<option value='".$value['CountryId']."' ".$selected.">".$value['countryName']."</option>";
				}
		/*Get Unite Values*/
		$unitValues = $defaultUnit = "";

		if(!empty($productId) || !empty($this->id)){
			$unit_data=$this->db->pdoQuery("SELECT id,$unit FROM tbl_unit_value WHERE isActive=? AND id=? Order by id",array('y',$getProductData['unit_id']))->result();
			$unitValues="<option value='".$unit_data['id']."' selected>".$unit_data['unitName']."</option>";
			$defaultUnit = $unit_data['unitName'];
		}
		/*End Unit Value saction*/
		
		$random=genrateRandom();
		if(!empty($getProductData['shipping_type'])){
			$selected_others = ($getProductData['shipping_type'] =='others') ? 'selected' : '';
			$selected_free = ($getProductData['shipping_type'] =='free') ? 'selected' : '';
		}else{
			$selected_others ='';
			$selected_free='';
		}
	

		$get_product_name=$this->db->pdoQuery("SELECT product_title,id FROM tbl_products WHERE user_id=?",array($_SESSION['userId']))->results();
		$product_names="";
		foreach ($get_product_name as $key => $value) {
			$selected="";

			if(!empty($getProductData['productId']) && $getProductData['productId']== $value['id']){
				$selected="selected";
			}
				$product_names.='<option value="'.$value['id'].'" '.$selected.'>'.$value['product_title'].'</option>';
		}
		$add_product_link="";
		if(empty($product_names)){
			$add_product_link="<span>Please <a class='theme-color' href='".SITE_URL."add-product'>add product</a> in order to add shipping.</span>";
		}
		$leftPanel=$this->getleftPanel();
		$fields = array(
			"%PRODUCT_NAMES%"=>$product_names,
			"%ADD_PRODUCT_LINK%"=>$add_product_link,
			"%PRICE_EVERYWHERE%"=>isset($getProductData['shipping_type']) ? ($getProductData['shipping_type'] == 'others' ? '' :$getProductData['shipping_detail']) : '',
			"%ALREDY_ADDED_DATA%"=>!empty($this->id) ? $this->old_added_data($getProductData['id']):'',
			"%SHIPPING_TYPE%"=>!empty($getProductData['shipping_type']) ? $getProductData['shipping_type']: '',
			"%OTHER_CLASS%"=>!empty($getProductData['shipping_type']) ? (($getProductData['shipping_type'] == 'others') ? 'style="display:block"' : 'style="display:none"'): '',
			"%EVERYWHERE_CLASS%"=>!empty($getProductData['shipping_type']) ? (($getProductData['shipping_type'] == 'everywhere') ? 'style="display:block"' : 'style="display:none"'): '',
			"%SELECT_COUNTRY%"=>$Country_select,
			"%SELECTED_OTHERS%"=>$selected_others,
			//"%SELECTED_EVERYWHERE%"=>$selected_everywhere,
			"%SELECTED_FREE%"=>$selected_free,
			"%ADD_TIME_HIDE%"=>isset($getProductData['id']) ? '' : 'hide',
			"%UNIT_VALUES%"=>$unitValues,
			"%MIN_RANGE%"=>!empty($getProductData['min_range'])?$getProductData['min_range']:'',
			"%MAX_RANGE%"=>!empty($getProductData['max_range'])?$getProductData['max_range']:'',
			"%TOKEN%"=>$random,
			"%ACTION%"=>$action,
			"%PAGE_HEADING%" =>(!empty($this->id)) ? EDIT_SHIPPING : ADD_SHIPPING,
			"%BTN_TITLE%" =>(!empty($this->id)) ? EDIT_SHIPPING : ADD_SHIPPING,
			"%LEFT_PANEL%"=>$leftPanel,
	        "%DEFAULT_UNIT%" => $defaultUnit,

		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	
    public function tags_selected($list)
  	{	

  		$products = $this->db->select('tbl_products',array('id','product_tags'),array('isActive'=>'y','user_id'=> $this->sessUserId))->results();
		$tag_name =$single_tags='';
		$total_tags=array();
		$added_list=explode(',', $list);
		foreach ($products as $key => $value) {
			$single_tags = explode(',',$value['product_tags']);
			foreach ($single_tags as $keyss => $val_tag) {
				if($val_tag != '' && !in_array($val_tag, $total_tags)){
					$selected = "";
					foreach ($added_list as $avail_keyss => $avail_val_tag) {
						if($avail_val_tag==$val_tag){
							$selected = 'selected';
						}
					}

					$tag_name .= "<option value='".$val_tag."' ".$selected.">".$val_tag."</option>";
				}
				array_push($total_tags,$val_tag);
			}
		}
		return $tag_name;
    }
 	public function tags_sugg()
    {
        $products = $this->db->select('tbl_products',array('id','product_tags'),array('isActive'=>'y','user_id'=> $this->sessUserId))->results();
		$tag_name =$single_tags= '';
		$total_tags=array();
		foreach ($products as $key => $value) {
			$single_tags = explode(',',$value['product_tags']);
			foreach ($single_tags as $keyss => $val_tag) {
				if($val_tag != '' && !in_array($val_tag, $total_tags)){
					$tag_name .= "<option value='".$val_tag."'>".$val_tag."</option>";
				}
				array_push($total_tags,$val_tag);
			}
		}
		return $tag_name;
    }
	 public function old_added_data($pid = '')
    {

        $data = $this->db->pdoQuery("Select shipping_detail,shipping_type from tbl_shipping_management where id = ".$pid)->result();
        $loop_data = '';
        $main_content = new MainTemplater(DIR_TMPL . $this->module . "/shipping_detail_loop-sd.skd");
        $main_content = $main_content->compile();
		$shipping_methods =$CountryId = $shipping_price = $alredyC = '';
		$obj =array();
		$all_id_json = array();		
		if($data['shipping_type']!="others"){
			return $loop_data;
		}
		
		$obj = (array)json_decode($data['shipping_detail'],true);
		$Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName 
			FROM tbl_country WHERE isActive=?",array('y'))->results();
		foreach ($obj as $k=>$val){	  
			foreach ($Country_select_all as $key => $value) 
			{
				$sel="";				
				$sel=($val['country_id']==$value['CountryId']) ? "selected":"";					
				$alredyC.="<option value='".$value['CountryId']."' ".$sel.">".$value['countryName']."</option>";
			}
	        $array = array(
	            "%SELECT_COUNTRY%" => $alredyC,
	            "%SHIPPING_PRICE%" => $val['shipping_price'],
	            "%SHIPPING_DAYS%" => $val['shipping_days'],

	            "%SHIPPING_STANDARD%" => $val['shipping_methods'] == 'standard' ? 'selected':'',
	            "%SHIPPING_EXPRESS%" => $val['shipping_methods'] == 'express' ? 'selected':'',
	            );
	        $loop_data .= str_replace(array_keys($array), array_replace($array), $main_content);
		}
		return $loop_data;
    }
	public function getQuestions($subcategories, $get_answers=false) {
		$return_content = '';
		$form_ids = $this->db->pdoQuery("SELECT id FROM `tbl_dynamic_form` WHERE subcatId = ?", array($subcategories))->result();

		if(!empty($form_ids)) {
			if($get_answers) {
				$questions = $this->db->pdoQuery("
					SELECT ff.*, ffa.answers
					FROM tbl_dynamic_form_fields AS ff
					LEFT JOIN tbl_dynamic_form_fields_response AS ffa ON(ffa.question_id = ff.id AND ffa.request_id = ?)
					WHERE ff.formId = ?
					ORDER BY sequence ASC
				", array($this->id['id'], $form_ids['id']))->results();
			} else {
				$questions = $this->db->pdoQuery("SELECT * FROM tbl_dynamic_form_fields WHERE formId = ? ORDER BY sequence ASC", array($form_ids['id']))->results();
			}


			if(!empty($questions)) {
				$questions_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_questions_block-sd.skd"))->compile();
				$questions_block_fields = array("%QUESTION%", "%OPTIONS%");

				$fields_find = array("%ID%", "%NAME%", "%VALUE%", "%LABEL%", "%OPTIONS%", "%CHECKED%", "%ERR_CLASS%", "%PLACEHOLDER%");

				$textbox_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_textbox_block-sd.skd"))->compile();
				$textarea_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_textarea_block-sd.skd"))->compile();
				$email_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_email_block-sd.skd"))->compile();
				$select_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_selectbox_block-sd.skd"))->compile();
				$checkbox_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_checkbox_block-sd.skd"))->compile();
				$radiobutton_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_radiobox_block-sd.skd"))->compile();


				foreach ($questions as $key => $value) {
					if($value['field_type']=="1") {
						// textbox
						$check_class = ($value['is_mandatory'] == 'y') ? 'dynamic_text_required' : 'dynamic_text';

						$fields_replace = array(
							$value['field_name'].'_'.$value['id'],
							"question[".$value['id']."]",
							(($get_answers) ? $value['answers'] : $value["field_value_".$this->curr_language]),
							'',
							'',
							'',
							$check_class,
							$value["default_value_".$this->curr_language]
						);
						$content = str_replace($fields_find, $fields_replace, $textbox_block);
						$questions_block_fields_replace = array($value["field_label_".$this->curr_language], $content);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					} else if($value['field_type']=="2") {
						// textarea
						$check_class = ($value['is_mandatory'] == 'y') ? 'dynamic_textarea_required' : 'dynamic_textarea';
						$fields_replace = array(
							$value['field_name'].'_'.$value['id'],
							"question[".$value['id']."]",
							(($get_answers) ? $value['answers'] : $value["field_value_".$this->curr_language]),
							'',
							'',
							'',
							$check_class,
							$value["default_value_".$this->curr_language]
						);
						$content = str_replace($fields_find, $fields_replace, $textarea_block);
						$questions_block_fields_replace = array($value["field_label_".$this->curr_language], $content);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					} else if($value['field_type']=="3") {
						// email
						$check_class = ($value['is_mandatory'] == 'y') ? 'dynamic_email_required' : 'dynamic_email';
						$fields_replace = array(
							$value['field_name'].'_'.$value['id'],
							"question[".$value['id']."]",
							(($get_answers) ? $value['answers'] : ""),
							'',
							'',
							'',
							$check_class,
							$value["default_value_".$this->curr_language]
						);
						$content = str_replace($fields_find, $fields_replace, $email_block);
						$questions_block_fields_replace = array($value["field_label_$this->curr_language"], $content);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					} else if($value['field_type']=="4") {
						// dropdowns
						$dropdown_options = '';
						$options = explode('|', $value["field_value_".$this->curr_language]);
						$default_value = $value["default_value_".$this->curr_language];
						$default_value = ((substr_count($default_value, '|') > 0) ? explode('|', $value["default_value_".$this->curr_language])[0] : "");
						$answers = (($get_answers) ? explode('|', $value['answers']) : $default_value);
						$check_class = ($value['is_mandatory'] == 'y') ? 'dynamic_select_required' : 'dynamic_select';

						if(!$get_answers && !empty($value["default_option_".$this->curr_language])) {
							$dropdown_options = '<option value="">'.$value["default_option_".$this->curr_language].'</option>';
						}

						foreach ($options as $options_key => $options_value) {
							$selected = (is_array($answers) ? ((in_array($options_value, $answers)) ? 'selected="selected"' : '') : ((trim($options_value)==trim($answers)) ? 'selected="selected"' : ''));
							$dropdown_options .= '<option value="'.$options_value.'" '.$selected.'>'.$options_value.'</option>';
						}
						$fields_replace = array($value['field_name'].'_'.$value['id'], "question[".$value['id']."]", '', '', $dropdown_options, '', $check_class);
						$content = str_replace($fields_find, $fields_replace, $select_block);
						$questions_block_fields_replace = array($value["field_label_$this->curr_language"], $content);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					} else if($value['field_type']=="5") {
						// checkbox
						$checkbox_content = '';
						$options = explode('|', $value["field_value_".$this->curr_language]);
						$default_value = $value["default_value_".$this->curr_language];
						$default_value = ((substr_count($default_value, '|') > 0) ? explode('|', $value["default_value_".$this->curr_language])[0] : $default_value);
						$answers = (($get_answers) ? explode('|', $value['answers']) : $default_value);
						$check_class = ($value['is_mandatory'] == 'y') ? "dynamic_check_required" : "dynamic_check";

						foreach ($options as $options_key => $options_value) {
							$selected = (is_array($answers) ? ((in_array($options_value, $answers)) ? 'checked="checked"' : '') : ((trim($options_value)==trim($answers)) ? 'checked="checked"' : ''));
							$fields_replace = array($value['field_name'].'_'.$value['id'], "question[".$value['id']."][]", $options_value, $options_value, '', $selected, $check_class);
							$checkbox_content .= str_replace($fields_find, $fields_replace, $checkbox_block);
						}
						$questions_block_fields_replace = array($value["field_label_$this->curr_language"], $checkbox_content);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					} else if($value['field_type']=="6") {
						// radio
						$radio_content = '';
						$options = explode('|', $value["field_value_".$this->curr_language]);
						$default_value = $value["default_value_".$this->curr_language];
						$default_value = ((substr_count($default_value, '|') > 0) ? explode('|', $value["default_value_".$this->curr_language])[0] : $default_value);
						$answers = (($get_answers) ? explode('|', $value['answers']) : $default_value);
						$check_class = ($value['is_mandatory'] == 'y') ? 'dynamic_radio_required' : 'dynamic_radio';

						foreach ($options as $options_key => $options_value) {
							$selected = (is_array($answers) ? ((in_array($options_value, $answers)) ? 'checked="checked"' : '') : ((trim($options_value)==trim($answers)) ? 'checked="checked"' : ''));

							$fields_replace = array($value['field_name'].'_'.$value['id'], "question[".$value['id']."][]", $options_value, $options_value, '', $selected, $check_class);
							$radio_content .= str_replace($fields_find, $fields_replace, $radiobutton_block);
						}
						$questions_block_fields_replace = array($value["field_label_$this->curr_language"], $radio_content);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					}
				}
			}
		}
		return $return_content;
	}


	public function getAllPics($token="",$productId="") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();

		if(!empty($token)){

			$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('user_id'=>$this->sessUserId,'token'=>$token), 'ORDER BY id ASC')->results();
		}else{
			$getProductImg=$this->db->select("tbl_products",array("id","product_image"),array("id"=>$this->id['id']))->result();
			if(!empty($getProductImg['product_image'])){
				$getImages=explode(',',$getProductImg['product_image']);
			}
		}

		if(!empty($getImages)) {			
			$ImgNo=0;
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
					"%IMAGES%" => getImage(DIR_PRODUCT_IMG.$file_name, "153", "148"),
					"%I%" => ($key+1),
					"%ACTIVE%" => $active
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
		}		
		return $final_content;
	}


	public function chk_eligible_to_add_prodcut() {	
	
		$result = getTablevalue('tbl_users', 'address_line1', array('id'=>$_SESSION['userId']));
		$chk_sup_pro=$this->db->select("tbl_company","*",array("user_id"=>$_SESSION['userId']))->result();
		
		$redirect_msg=$redirect_url="";

		if(empty($result)){
			$redirect_msg=FILL_USER_PROFILE_FIRST;
			$redirect_url=SITE_URL."user-profile";
		}elseif(empty($chk_sup_pro['company_mail']) || empty($chk_sup_pro['company_address'])){
			$redirect_url=SITE_URL."supplier-profile";
			$redirect_msg=FILL_BASIC_COMAPNY_DETAIL_FIRST;
		}elseif(empty($chk_sup_pro['detailed_description'])){
			$redirect_url=SITE_URL."supplier-profile";
			$redirect_msg=FILL_COMPANY_INTRODUCTION_FIRST;
		}elseif(empty($chk_sup_pro['market_names']) || empty($chk_sup_pro['export_percentage'])){
			$redirect_url=SITE_URL."supplier-profile";
			$redirect_msg=FILL_EXPORT_CAPABILITY_FIRST;
		}
		if(!empty($redirect_url)){
			$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>$redirect_msg));
			redirectPage($redirect_url);			
		}

	}



	public function addProduct($data) {
		extract($data);
		$objPost = new stdClass();	
		$productId = !empty($_GET['productId'])?  $_GET['productId'] : 0;

		$objPost->shipping_type=!empty($shipping_type)?$shipping_type:'';
		$objPost->min_range=!empty($minPrice)?$minPrice:'';
		$objPost->max_range=!empty($maxPrice)?$maxPrice:'';
		$objPost->unit=!empty($unit)?$unit:'';
		$objPost->product_id=!empty($products)?$products:'';
		$objPost->created_date=date('Y-m-d H:i:s');
		
		if($shipping_type == 'others'){
			$array_jeson = [];
			$size_array = count($data['country_id']);
			$i=0;
			$new_json_data = $json_data ='';
			for($i=0;$i<$size_array;$i++) {
				$array_jeson[] = ['country_id' => $data['country_id'][$i], 'shipping_price' => $data['shipping_price'][$i],'shipping_methods' => $data['shipping_methods'][$i]
					,'shipping_days' => $data['shipping_days'][$i]
				];
			}
			$json_data=json_encode($array_jeson);
			
			$new_json_data= $json_data;
			$objPost->shipping_detail=!empty($json_data)?$json_data:'';
		}else if($shipping_type == 'everywhere'){
			$objPost->shipping_detail=!empty($price_everywhere)?$price_everywhere:'';
		}else{
			$objPost->shipping_detail=0;			
		}


		/*Insert Product*/

			if(!empty($objPost->unit)){
				$objPostArray = (array) $objPost;
				if(empty($this->id)){
					$this->db->insert('tbl_shipping_management',$objPostArray); 
					$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>YOU_ARE_SUCCESSFULLY_ADD_SHIPPING));
				}else{
					$this->db->update('tbl_shipping_management',$objPostArray,array("id"=>$this->id)); 
					$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>YOU_ARE_SUCCESSFULLY_UPDATE_SHIPPING));
				}
				redirectPage(SITE_URL.'manage-shipping');
			}else{
				$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
				redirectPage(SITE_URL.'manage-shipping');
			}

		/*End Insertion Saction*/
		
	}
}
?>