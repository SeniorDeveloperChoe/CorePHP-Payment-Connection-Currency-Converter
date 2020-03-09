<?php
class AddProduct extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		error_reporting(0);
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$maincatName="m.maincatName_".$this->curr_language." AS maincatName";
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";
		$chkFobYes=$chkFobNo=$chkPremiumYes=$chkPremiumNo=$product_id=$dynamicQuestion="";
		$action="add";
		$negotiableYes=$negotiableNo=$hideMaxPricess=$HideToText="";
		$MaxPrice = $deliveryTermsHtml ="";

		$no_product_post=$this->db->pdoQuery("SELECT COUNT(p.id) AS totalPostProducts,u.product_post,u.product_mark_premium FROM `tbl_products` AS p INNER JOIN tbl_users AS u on u.id=p.user_id WHERE user_id=?",array($_SESSION['userId']))->result();
		
		if($no_product_post['totalPostProducts'] == 0){
			$allow_to_post = 'yes';
		}else{
			$allow_to_post=($no_product_post['product_post'] > 0)?"yes":"no";
		}
		$default_charge="";
		if(!empty($this->id)){	
			$allow_to_post="yes";
			$action="edit";
			$getProductData=$this->db->pdoQuery("SELECT p.*,$maincatName,$catName,$subcatName,s.id as SubCatId,$unitName
											FROM tbl_products AS p
											INNER JOIN tbl_product_main_category AS m ON m.id=p.main_cat_id
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
											WHERE p.id = ?
											",$this->id)->result();	
			$default_charge=$getProductData['default_shipping_charge'];


			$product_id=$getProductData['id'];

				if($getProductData['mark_premium']=='y'){
					$chkPremiumYes="checked";
				}else{
					$chkPremiumNo="checked";
				}

				$dynamicQuestion=$this->getQuestions($getProductData['SubCatId'], true);

				if($getProductData['isNegotiable']=="y"){
					$negotiableYes="checked";
				}else{
					$negotiableNo="checked";
					$hideMaxPricess=$HideToText="hide";
				}


				if($getProductData['isNegotiable']=="y"){
					$MaxPrice=$getProductData['max_price'];
				}else{
					$MaxPrice="0";
				}
			}

			if($action=="add"){
				$negotiableYes="checked";
			}

		$mark_premium=$fob="";
		if(empty($this->id)){
			$mark_premium=$fob="checked";
		}

		$markPremiumContent="";
		$remainPre = getRemainingPremiumProducts($_SESSION['userId']);
		if($no_product_post['product_mark_premium']>0){
			$markPremiumContent='<div class="form-group">
			                        <label class="control-label col-sm-3 col-md-3" for="premium">'.MARK_PRODUCT_AS_PREMIUM.'('.$remainPre.' Remains) </label>
			                        <div class="col-sm-7 col-md-9">
			                           <label class="radio-inline"><input type="radio" name="premium" class="premium" value="y" '.$chkPremiumYes.'/>'.YES.'</label>
			                           <label class="radio-inline"><input type="radio" name="premium" class="premium" value="n" '.$chkPremiumNo.' '.$mark_premium.'/>'.NO.'</label>
			                        </div>
			                     </div>';
		}
		$maincat="maincatName_".$this->curr_language." AS maincatName";
		$cat="categoryName_".$this->curr_language." AS catName";
		$subcat="subcatName_".$this->curr_language." AS subCatName";
		$unit="unit_value_".$this->curr_language." AS unitName";
		$mainCategory=$Category=$subCategory=$Country_select="";
		/*Get Main Category*/
			$main_cat_data=$this->db->pdoQuery("
				SELECT mc.id,$maincat FROM tbl_product_main_category as mc
				JOIN tbl_product_category as pc ON( pc.maincatID = mc.id)
				JOIN tbl_product_subcategory as ps ON( ps.catId = pc.id)
				WHERE mc.isActive=? group by mc.id ORDER BY maincatName_".$this->curr_language." ASC" ,array('y'))->results();

			foreach ($main_cat_data as $key => $value) {
				$selected="";
				if (!empty($this->id)) {
					if($getProductData['maincatName']==$value['maincatName'])	{
						$selected="selected";
					}
				}
				$mainCategory.="<option value='".$value['id']."' ".$selected.">".$value['maincatName']."</option>";
			}
		/*End Main Category secation*/
		/*Get Category*/
		if (!empty($this->id)) {
			$cat_data=$this->db->pdoQuery("SELECT id,$cat FROM tbl_product_category WHERE isActive=? ORDER BY categoryName_".$this->curr_language." ASC",array('y'))->results();

			foreach ($cat_data as $key => $value) {
				$selected="";				
					if($getProductData['catName']==$value['catName'])	{
						$selected="selected";					
				}
				$Category.="<option value='".$value['id']."' ".$selected.">".$value['catName']."</option>";
			}
		}
		/*End Category secation*/
		/*Get Sub Category*/
		if (!empty($this->id)) {
			$sub_cat_data=$this->db->pdoQuery("SELECT id,$subcat FROM tbl_product_subcategory WHERE isActive=? ORDER BY subcatName_".$this->curr_language." ASC",array('y'))->results();
			foreach ($sub_cat_data as $key => $value) {
				$selected="";				
					if($getProductData['subCatName']==$value['subCatName'])	{
						$selected="selected";					
				}
				$subCategory.="<option value='".$value['id']."' ".$selected.">".$value['subCatName']."</option>";
			}
		}
		/*End Sub Category secation*/
		$Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName FROM tbl_country WHERE isActive=?",array('y'))->results();
				foreach ($Country_select_all as $key => $value) {
					$selected="";
					$Country_select.="<option value='".$value['CountryId']."' ".$selected.">".$value['countryName']."</option>";
				}
		/*Get Unite Values*/
		$unit_id_edit="";
			$unit_data=$this->db->pdoQuery("SELECT id,$unit FROM tbl_unit_value WHERE isActive=? Order by id",array('y'))->results();
			$unitValues='';
			foreach ($unit_data as $key => $value) {
				$selected="";
				if (!empty($this->id)) {
					if($getProductData['unitName']==$value['unitName'])	{
						$unit_id_edit="<option value='".$value['id']."' selected>".$value['unitName']."</option>";
						$selected="selected";
					}
				}
				$unitValues.="<option value='".$value['id']."' ".$selected.">".$value['unitName']."</option>";
			}
		/*End Unit Value saction*/
		$deliveryTimeArray=getDeliveryTime();
		$deliveryTime="";
		foreach ($deliveryTimeArray as $key => $value) {
			$selected="";
			if (!empty($this->id)) {
				if($getProductData['estimated_delivery_time']==$value)	{
					$selected="selected";
				}
			}
			$deliveryTime.="<option value='".$value."' ".$selected.">".$value."</option>";
		}
		$random=genrateRandom();
		if(!empty($getProductData['shipping_type'])){
			$selected_others = ($getProductData['shipping_type'] =='others') ? 'selected' : '';
			$selected_everywhere = ($getProductData['shipping_type'] =='everywhere') ? 'selected' : '';
			$selected_free = ($getProductData['shipping_type'] =='free') ? 'selected' : '';
		}else{
			$selected_others ='';
			$selected_everywhere ='';
			$selected_free='';
		}

		$payment_methods_li = '<option value="%VALUE%" %SELECTED% >%VALUE%</option>';
		$selected_payment_methods = !empty($getProductData['payment_methods']) ? explode(',',$getProductData['payment_methods']) : [];
		$payment_methods_data=$this->db->pdoQuery("SELECT * FROM tbl_payment_method WHERE isActive=? Order by id",array('y'))->results();
		$paymentethodHtml = '';
		foreach ($payment_methods_data as $key => $value) {
			$selected="";
			if(in_array($value['payment_method'], $selected_payment_methods)){
				$selected="selected";
			}
			$paymentethodHtml .= str_replace(["%VALUE%", "%SELECTED%"], [$value['payment_method'], $selected], $payment_methods_li);
		}
		$leftPanel=$this->getleftPanel();

		$week_selection = !empty($getProductData['week_month']) && $getProductData['week_month'] == 'week' ? 'selected' : '';
		$month_selection = !empty($getProductData['week_month']) && $getProductData['week_month'] == 'month' ? 'selected' : '';

		
		$deliveryTermsData = $this->db->pdoQuery("SELECT * FROM tbl_delivery_terms where isActive = 'y'",[])->results();
		$delivery_terms_li = '<option value="%ID%" %SELECTED% >%VALUE%</option>';
		$selected_delivery_terms = !empty($getProductData['delivery_terms']) ? explode(',',$getProductData['delivery_terms']) : [];

		$deliveryTermsHtml = '';
		foreach ($deliveryTermsData as $key => $dValue) {
			$selected="";
			if(in_array($dValue['id'], $selected_delivery_terms)){
				$selected="selected";
			}
			$deliveryTermsHtml .= str_replace(["%ID%", "%VALUE%", "%SELECTED%"], [$dValue['id'],$dValue['delivery_terms'], $selected], $delivery_terms_li);
		}
		
		$getDefaultLocation = getDefaultLocation();
		$fields = array(
			"%WEEK_SELECTION%"=>$week_selection,
			"%MONTH_SELECTION%"=>$month_selection,
			"%SELECTED_UNIT%"=> isset($getProductData['unitName']) ? $getProductData['unitName'] : "",
			"%ACCEPTED_DELIVERY_TERMS%"=>$deliveryTermsHtml,


			"%LEFT_PANEL%"=>$leftPanel,
			"%DEFAULT_CHARGE%"=>$default_charge,
			"%PRICE_EVERYWHERE%"=>isset($getProductData['shipping_type']) ? ($getProductData['shipping_type'] == 'others' ? '' :$getProductData['shipping_detail']) : '',
			"%ALREDY_ADDED_DATA%"=>isset($getProductData['id']) ? $this->old_added_data($getProductData['id']):'',
			"%SHIPPING_TYPE%"=>!empty($getProductData['shipping_type']) ? $getProductData['shipping_type']: '',
			"%OTHER_CLASS%"=>!empty($getProductData['shipping_type']) ? (($getProductData['shipping_type'] == 'others') ? 'style="display:block"' : 'style="display:none"'): '',
			"%EVERYWHERE_CLASS%"=>!empty($getProductData['shipping_type']) ? (($getProductData['shipping_type'] == 'everywhere') ? 'style="display:block"' : 'style="display:none"'): '',
			"%SELECT_COUNTRY%"=>$Country_select,
			"%MAIN_CATEGORY%"=>$mainCategory,
			"%SELECTED_OTHERS%"=>$selected_others,
			"%SELECTED_EVERYWHERE%"=>$selected_everywhere,
			"%SELECTED_FREE%"=>$selected_free,
			"%PRODUCT_STOCK%"=>isset($getProductData['product_stock']) ? $getProductData['product_stock'] : '',
			"%ADD_TIME_HIDE%"=>isset($getProductData['id']) ? '' : 'hide',
			"%UNIT_ID_EDIT%"=>$unit_id_edit,
			"%CATEGORY%"=>$Category,
			"%SUB_CATEGORY%"=>$subCategory,
			"%UNIT_VALUES%"=>$unitValues,
			"%PRODUCT_TITLE%"=>!empty($getProductData['product_title'])?$getProductData['product_title']:"",
			"%EMBEDED_URL%"=>!empty($getProductData['embeded_url'])?$getProductData['embeded_url']:"",
			"%MIN_PRICE%"=>!empty($getProductData['min_price'])?$getProductData['min_price']:"",
			"%MAX_PRICE%"=>!empty($MaxPrice)?$MaxPrice:"",
			"%ORDER_QUANTITY%"=>!empty($getProductData['order_quantity'])?$getProductData['order_quantity']:"",
			"%PRODUCT_LOCATION%"=>!empty($getProductData['product_location'])?$getProductData['product_location']:$getDefaultLocation,
			"%PRODUCT_TAGS%"=>!empty($getProductData['product_tags'])?$getProductData['product_tags']:"",
			"%SUPPLY_ABILITY%"=>!empty($getProductData['supply_ability'])?$getProductData['supply_ability']:"",
			"%PRODUCT_DESCRIPTION%"=>!empty($getProductData['product_description'])?$getProductData['product_description']:"",
			"%DELIVERY_TIME%"=>$deliveryTime,
			"%TOKEN%"=>$random,
			"%USER_IMAGES%"=>($action!="add")?$this->getAllPics('',$product_id):'',
			"%DYNAMIC_QUESTION%"=>$dynamicQuestion,
			"%ACTION%"=>$action,
			"%MARK_PREMIUM_CONTENT%"=>$markPremiumContent,
			"%NEGOTIABLE_YES%"=>$negotiableYes,
			"%NEGOTIABLE_NO%"=>$negotiableNo,
			"%HIDEMAX_PRICESS%"=>$hideMaxPricess,
			"%HIDE_TO_TEXT%"=>$HideToText,
			"%ALLOW_TO_POST%"=>$allow_to_post,
			"%IS_NEGOTIABLE%"=>!empty($getProductData['isNegotiable'])?$getProductData['isNegotiable']:"",
			
			"%TAGS_SUGG%" => (isset($getProductData) && $getProductData['product_tags']!='') ? $this->tags_selected($getProductData['product_tags']) : $this->tags_sugg(),
			"%PAGE_HEADING%" =>(!empty($this->id)) ? EDIT_PRODUCT : ADD_NEW_PRODUCT,
			"%BTN_TITLE%" =>(!empty($this->id)) ? EDIT_PRODUCT : ADD_PRODUCT,
			"%MARK_PREMIUM%"=>$mark_premium,
			"%FOB%"=>$fob,
			"%P_E_N%" => !empty($this->id) ? "edit" : "add", 
			"%UPDATED_IMAGES%" => !empty($getProductData['product_image']) ? $getProductData['product_image'] : "", 
			"%PAYMENT_METHODS%"=>$paymentethodHtml,
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
        $data = $this->db->pdoQuery("Select shipping_detail,shipping_type from tbl_products where id = ".$pid)->result();
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
		   /*print_r($obj);exit();*/
		foreach ($obj as $k=>$val){	  
		   $Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName 
		   											FROM tbl_country WHERE isActive=?",array('y'))->results();
		foreach ($Country_select_all as $key => $value) 
		{
			$sel="";				
			$sel=($val['country_id']==$value['CountryId']) ? "selected":"";					
			$alredyC.="<option value='".$value['CountryId']."' ".$sel.">".$value['countryName']."</option>";
		}
        $array = array(
            "%SELECT_COUNTRY%" => $alredyC,
            "%SHIPPING_PRICE%" => $val['shipping_price'],
            "%SHIPPING_SEA_FREIGHT%" => $val['shipping_methods'] == 'sea_freight' ? 'selected':'',
            "%SHIPPING_AIR_CARGO%" => $val['shipping_methods'] == 'air_cargo' ? 'selected':'',
            "%SHIPPING_LAND_TRANSPORT%" => $val['shipping_methods'] == 'land_transport' ? 'selected':'',
            "%SHIPPING_EXPRESS%" => $val['shipping_methods'] == 'express' ? 'selected':''
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
		//dd($questions);

			// printr($questions, 1);

			if(!empty($questions)) {
				$questions_block = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_questions_block-sd.skd"))->compile();
				$questions_block_fields = array("%QUESTION%", "%OPTIONS%", "%MEND_SIGN%");

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
						$mendSign ="";
						if ($value['is_mandatory'] == 'y') {
							$mendSign ="*";
						}
						$questions_block_fields_replace = array($value["field_label_".$this->curr_language], $content, $mendSign);
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
						$mendSign ="";
						if ($value['is_mandatory'] == 'y') {
							$mendSign ="*";
						}
						$questions_block_fields_replace = array($value["field_label_".$this->curr_language], $content, $mendSign);
						$return_content .= str_replace($questions_block_fields, $questions_block_fields_replace, $questions_block);
					} else if($value['field_type']=="3") {
						// email
						$check_class = ($value['is_mandatory'] == 'y') ? 'dynamic_email_required' : 'dynamic_email';
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
						//dd($fields_replace);
						$content = str_replace($fields_find, $fields_replace, $email_block);
						$mendSign ="";
						if ($value['is_mandatory'] == 'y') {
							$mendSign ="*";
						}
						$questions_block_fields_replace = array($value["field_label_$this->curr_language"], $content, $mendSign);
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
						$mendSign ="";
						if ($value['is_mandatory'] == 'y') {
							$mendSign ="*";
						}
						$questions_block_fields_replace = array($value["field_label_$this->curr_language"], $content, $mendSign);
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
					"%IMAGE_ID%" => $file_name,
					/*"%IMAGES%" => getImage(DIR_PRODUCT_IMG.$file_name, "153", "148"),*/
					"%IMAGES%" => SITE_PRODUCT_IMG.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		} else {			
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
		}		
		return $final_content;
	}

	public function getSinglePics($lastImgId="0") {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reorder_images-sd.skd"))->compile();

		$getImages = $this->db->select('temp_product_images', array('id', 'file_name'), array('id'=>$lastImgId), 'ORDER BY id ASC')->results();
		if(!empty($getImages)) {
			$ImgNo=0;
			foreach($getImages AS $key => $values) {
				$Id=$values['id'];
				$file_name=$values['file_name'];
			
				$active = '';
				$fields = array(
					"%ID%" => $Id,
					"%IMAGE_ID%" => $file_name,
					"%IMAGES%" => SITE_PRODUCT_IMG.$file_name,
					"%I%" => ($key+1),
					"%ACTIVE%" => $active
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
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

		//echo "<pre>";print_r($data);exit;
		$objPost->product_title=!empty($product_title)?$product_title:'';
		$objPost->default_shipping_charge=!empty($defaultCharge)?$defaultCharge:'';
		$objPost->product_stock=!empty($product_stock)?$product_stock:'';
		//$objPost->shipping_type=!empty($shipping_type)?$shipping_type:'';
		$objPost->week_month=!empty($week_month)?$week_month:'';
		$payment_methods_str = implode(',', $payment_methods);
		$objPost->payment_methods=!empty($payment_methods_str)?$payment_methods_str:'';
		/*if($objPost->shipping_type == 'others'){
			$array_jeson = [];
			$size_array = count($data['country_id']);
			$i=0;
			$new_json_data = $json_data ='';
			for($i=0;$i<$size_array;$i++) {
				$array_jeson[] = ['country_id' => $data['country_id'][$i], 'shipping_price' => $data['shipping_price'][$i],'shipping_methods' => $data['shipping_methods'][$i]];	
			}
			$json_data=json_encode($array_jeson);
			$new_json_data= $json_data;
			$objPost->shipping_detail=!empty($json_data)?$json_data:'';
		}else if($objPost->shipping_type == 'everywhere'){
			$objPost->shipping_detail=!empty($price_everywhere)?$price_everywhere:'';
		}else{
			$objPost->shipping_detail=0;			
		}*/
		/*if(empty($this->id)){*/
			$objPost->product_slug = slug($objPost->product_title);
		/*}*/

		$objPost->main_cat_id=!empty($MainCategory)?$MainCategory:'';
		
		$objPost->cat_id=!empty($Category)?$Category:'';
		
		$objPost->sub_cat_id=!empty($subCategory)?$subCategory:'';
		
		$objPost->embeded_url=!empty($video)?$video:'';

		if(!empty($negotiable) && $negotiable=='y'){
			$objPost->min_price=!empty($minPrice)?$minPrice:'';
			$objPost->max_price=!empty($maxPrice)?$maxPrice:'';
			$objPost->isNegotiable="y";
		}else{
			$objPost->min_price=!empty($minPrice)?$minPrice:'';
			$objPost->max_price='0';
			$objPost->isNegotiable="n";
		}
			

		$objPost->unit_id=!empty($unit)?$unit:'';
		
		$objPost->order_quantity=!empty($quantity)?$quantity:'';
		
		$objPost->product_location=!empty($location)?$location:'';
		$tags_all = implode(',',$tags);
		$objPost->product_tags=!empty($tags_all)?$tags_all:'';/*
		$objPost->product_tags=!empty($tags)?$tags:'';*/
		$delivery_terms_data = "";
		if (!empty($delivery_terms)) {
			$delivery_terms_data = implode(',', $delivery_terms);
		}
		$objPost->delivery_terms = $delivery_terms_data;
		$objPost->product_description=!empty($description)?trim($description):'';
		
		$objPost->supply_ability=!empty($ability)?$ability:'';
		
		$objPost->mark_premium=!empty($premium)?$premium:'n';
		
		$objPost->estimated_delivery_time=!empty($deliveryTime)?$deliveryTime:'';

		$objPost->user_id=$_SESSION['userId'];


		/******Get Product Images section*******/

		$objPost->product_image="";

			$frmToken=!empty($frmToken)?$frmToken:'';

			$getImgs=$this->db->select("temp_product_images",array("file_name"),array("user_id"=>$_SESSION['userId'],"token"=>$frmToken))->results();
			$productImgs="";

			if(!empty($getImgs)){
				$lastImg=end($getImgs);

				foreach ($getImgs as $key => $value) {
					if($value['file_name']==$lastImg['file_name']){
						$productImgs.=$value['file_name'];
					}else{
						$productImgs.=$value['file_name'].",";
					}
				}
				$objPost->product_image=$productImgs;
				$getImgs=$this->db->delete("temp_product_images",array("user_id"=>$_SESSION['userId'],"token"=>$frmToken));
				

			}

			$deleteImgs   = $this->db->pdoQuery("select * From temp_product_images where user_id =".$_SESSION['userId']. " AND token !='$frmToken'")->results();
			
			if(!empty($deleteImgs)){

				foreach ($deleteImgs as $imgData) {
						$productImgs =$imgData['file_name'];
					if (file_exists(DIR_PRODUCT_IMG.$productImgs)) {
						unlink(DIR_PRODUCT_IMG.$productImgs);
					}
					$this->db->delete("temp_product_images",array("id"=>$imgData['id']));
				}

			}

			if(!empty($this->id)){
				$oldImg=$this->db->select("tbl_products",array("product_image"),array("id"=>$this->id['id']))->result();
				if(!empty($oldImg['product_image'])){
					$objPost->product_image=!empty($objPost->product_image)?$oldImg['product_image'].",".$objPost->product_image:$oldImg['product_image'];
				}
			}
			if (!empty($data['updatedImages'])) {
				$updatedImagesArr = explode(',', $data['updatedImages']);

				$updatedImagesArr = array_filter($updatedImagesArr);

				$objPost->product_image = implode(',', $updatedImagesArr);
			}

			
		/******End Get Product Images Section*******/


		 /******Prodcut UNIQUE ID GENERATION Saction******/
        
	        $adUniqueId   = $this->db->pdoQuery('select MAX(id) as pid From tbl_products')->result();
	        $unique_product_id = 'PD000001';
	        $len          = $adUniqueId['pid'] + 1;
	        $clen         = strlen($len);
	        if ($clen > 6) {
	            $diff    = $clen - 6;
	            $suffixd = '';
	            for ($i = 0; $i < $diff; $i++) {
	                $suffixd .= '0';
	            }
	            $unique_product_id .= $suffixd;
	        }
	        $objPost->product_id = substr_replace($unique_product_id, $len, -$clen);

	     /*End Prodcut UNIQUE ID GENERATION Saction*/
	     
		/*Insert Product*/

		$product_response_edit=array();

			if(!empty($objPost->product_title) && !empty($objPost->main_cat_id) && !empty($objPost->cat_id) && !empty($objPost->sub_cat_id) && !empty($objPost->min_price) && !empty($objPost->unit_id) && !empty($objPost->order_quantity) && !empty($objPost->product_location) && !empty($objPost->product_description) && !empty($objPost->supply_ability) && !empty($objPost->product_image)){

				$id="";
				$objPostArray = (array) $objPost;
				if(!empty($this->id)){
					$id=$this->id['id'];

				}else{
					$id = $this->db->insert('tbl_products',$objPostArray)->getLastInsertId(); 
					$get_user_data=$this->db->select("tbl_users","*",array("id"=>$_SESSION['userId']))->result();
					if($get_user_data['mark_premium']>0){
						$update_premium_count=($objPost->mark_premium=="y") ? ",product_mark_premium=product_mark_premium-1":"";
					}

					if($get_user_data['product_post']>0){
						$this->db->pdoQuery('UPDATE tbl_users set product_post=product_post-1 '.$update_premium_count.' WHERE id=?',array($_SESSION['userId']));
					}
					$get_supplier_id=$this->db->pdoQuery("SELECT id FROM tbl_company WHERE user_id = ?",array($_SESSION['userId']))->result();
					$users_subscription = $this->db->pdoQuery("SELECT *
        													FROM tbl_tradealert_subscriber 
        													WHERE sub_cat_id = $objPost->sub_cat_id ")->results();
					foreach ($users_subscription as $key => $value) {
                    	$userDate = $this->db->pdoQuery("SELECT first_name,last_name,id FROM tbl_users WHERE id= ?",array($_SESSION['userId']))->result();
                    	$check_noti = $this->db->pdoQuery("SELECT email,first_name,last_name,trade_alert_noti FROM tbl_users WHERE id= ?",array($value['user_id']))->result();
                    	if($value['user_id'] != $_SESSION['userId']){
                    		if($check_noti['trade_alert_noti'] == 'y'){
	                    		$arrayCont = array("greetings"=>ucfirst($check_noti['first_name'].' '.$check_noti['last_name']), "productDetailLink"=>SITE_URL.'product-detail/'.$objPost->product_slug);
								sendMail($check_noti['email'], 'notify_trade_alert_set_by_me', $arrayCont);	
	                    	}
	                    	$product_title=$objPost->product_title;
	                    	$msg_place_quote =str_replace(
								array("#USERNAME#", "#PRODUCT_TITLE#"),
								array($userDate['first_name'].' '.$userDate['last_name'],$product_title),
								ADD_NEW_PRODUCT_RELETED_ALERT
							);
							$notifyUrl = 'product-detail/'.$objPost->product_slug;
							add_notification($msg_place_quote, $value['user_id'], $notifyUrl);
						}
                    }
				}

				$question = !empty($question) ? $question : [];
				$objQPost = new stdClass();
					foreach ($question as $key => $value) {
						$objQPost->request_id = $id;
						$objQPost->question_id = $key;

						if(is_array($value)) {
							$objQPost->answers = trim(implode('|', $value));	
						} else {
							$objQPost->answers = trim($value);
						}
						array_push($product_response_edit,$objQPost->answers);

						if(!empty($objQPost->request_id) && !empty($objQPost->question_id) && !empty($objQPost->answers)) {

							if(!empty($this->id)){
								$checkQuestionsExist = $this->db->pdoQuery("SELECT * FROM tbl_dynamic_form_fields_response WHERE request_id = ? AND question_id = ? ", array($objQPost->request_id ,$objQPost->question_id))->result();
								if (!empty($checkQuestionsExist)) {
									$this->db->update('tbl_dynamic_form_fields_response', (array)$objQPost,array("request_id"=>$objQPost->request_id,"question_id"=>$objQPost->question_id));
								} else {
									$this->db->insert('tbl_dynamic_form_fields_response', (array)$objQPost);
								}

								
							}else{
								$this->db->insert('tbl_dynamic_form_fields_response', (array)$objQPost);
							}
						}
					}



				if(!empty($this->id)){	

					if(!empty($_SESSION['moderatorId'])){
						$moderatorActivityArray=array("activity"=>"Edit","page"=>"manage_product-sd","moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$id,"entity_action"=>"Edit Product");
        				add_moderator_activity($moderatorActivityArray); 			
					}

					$objPost->dynamic_response = implode(',', $product_response_edit);
					$edit_objPostArray = (array) $objPost;

					$this->db->update('tbl_products',$edit_objPostArray,array("id"=>$this->id['id'])); 
					$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>PRODUCT_UPDATED_SUCCESSFULLY));
					redirectPage(SITE_URL.'manage-product'); 
				}else{		

					if(!empty($_SESSION['moderatorId'])){
						$moderatorActivityArray=array("activity"=>"Add","page"=>"manage_product-sd","moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$id,"entity_action"=>"Add Product");
        				add_moderator_activity($moderatorActivityArray); 			
					}

					$this->db->update('tbl_products',array("dynamic_response"=>implode(',', $product_response_edit)),array("id"=>$id)); 

                	$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>PRODUCT_ADDED_SUCCESSFULLY));
					redirectPage(SITE_URL.'add-shipping?productId='.$id);
				}


			}else{
				$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
				redirectPage(SITE_URL.'add-product');
			}

		/*End Insertion Saction*/
		
	}
}
?>