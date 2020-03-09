<?php
class manage_shipping extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}


	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$get_all_data=$this->db->pdoQuery("SELECT un.unit_value,p.default_shipping_charge,p.id AS PID,p.product_slug,p.product_title,p.product_image,p.product_description,s.* FROM tbl_shipping_management AS s INNER JOIN tbl_products AS p ON p.id=s.product_id INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id WHERE p.user_id=".$_SESSION['userId']." GROUP BY s.product_id")->results();
		$all_product_data="";
		$table_data="";
		$load_datatables="";
		if(empty($get_all_data)){
			
			$html = (new MainTemplater(DIR_TMPL.$this->module."/no_shipping-sd.skd"))->compile();

		}else{
			foreach ($get_all_data as $key => $value) {
				if(strlen($value['product_description'])>100){
					$product_descripton=substr($value['product_description'], 0, 100).'...';
				}else{
					$product_descripton=$value['product_description'];
				}
				$productImage = '';
				if (!empty($value['product_image'])) {
					$productImage = $value['product_image'];
					$productImage = explode(',', $productImage);
					$productImage = $productImage[0];
				}
				$table_data.='<div class="col-md-6">
				                    <div class="shipping-box">
				                      <div class="media">
				                        <div class="media-left">
				                        	<a href="'.SITE_URL.'product-detail/'.$value['product_slug'].'">
					                          <div class="ship-img">
					                            <img src="'.SITE_UPD.'product-sd/'.$productImage.'">
					                          </div>
				                          	</a>
				                        </div>
				                        <div class="media-body">
				                          <h5><a href="'.SITE_URL.'product-detail/'.$value['product_slug'].'">'.$value['product_title'].'</a></h5>
				                          <p class="desc">'.$product_descripton.'</p>
				                          <p><label>Default Charge : </label> <span>'.$value['default_shipping_charge'].' '.CURRENCY_CODE.'</span> <a href="javascript:void(0);" id="'.$value['PID'].'" class="theme-color editDeafultCharge"><i class="fa fa-pencil"></i> '.EDIT.'</a></p>
				                        </div>
				                      </div>
				                      <h5 class="theme-color">'.SHIPPING_DETAILS.' </h5>
				                        <table id="example_'.$key.'" class="table table-bordered table-striped">
				                          <thead>
				                            <tr>
				                              <th>'.LBL_MIN.'</th>
				                              <th>'.LBL_MAX.'</th>
				                              <th>'.LBL_UNIT.'</th>
				                              <th>'.SHIPPING_TYPE.'</th>
				                              <th>Action</th>
				                            </tr>
				                          </thead>
				                          <tbody>';

	          	$load_datatables.='var example'.$key.' = $("#example_'.$key.'").DataTable({
								       "pageLength": 3,
								       "aaSorting" : [],
								       "scrollY": "120px",
	        						   "scrollCollapse": true,
	        						   "paging":         false
								    });';

				$get_table_data=$this->db->pdoQuery("SELECT s.* FROM tbl_shipping_management AS s WHERE s.product_id=?",array($value['product_id']))->results();
				$all_product_data="";

				foreach ($get_table_data as $tabl_value) {
					$shippingType="";
					if($tabl_value['shipping_type']=="free"){
						$shippingType="Free";
					}elseif($tabl_value['shipping_type']=="others"){
						$shippingType="Certain";
					}else{
						$shippingType="Everywhere";
					}

					$all_product_data.='<tr>
			                              <td>'.$tabl_value['min_range'].'</td>
			                              <td>'.$tabl_value['max_range'].'</td>
			                              <td>'.$value['unit_value'].'</td>
			                              <td>'.$shippingType.'</td>
			                              <td>
			                                <a href="'.SITE_URL.'edit-shipping/'.$tabl_value['id'].'" class="btn btn-edit" title="Edit">
			                                  <i class="fa fa-pencil"></i>
			                                </a>
			                                <a href="javascript:void(0);" data-tableid="#example_'.$key.'" data-id="'.$tabl_value['id'].'" class="btn btn-cancel deleteRecord" title="Cancel">
			                                  <i class="fa fa-times"></i>
			                                </a>
			                              </td>
			                            </tr>';
				}
				

				$table_data.=$all_product_data.'</tbody>
				               </table>
				                 </div>
				                  </div>';
			}
		}
		$fields=array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%TABLE_DATA%"=>$table_data,
			"%LOAD_DATATABLES%"=>$load_datatables
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
			/*$final_content = "<div class='col-md-12 martop20'><div class='nrf '>".MSG_NO_PHOTOS."</div></div>";*/
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
				$array_jeson[] = ['country_id' => $data['country_id'][$i], 'shipping_price' => $data['shipping_price'][$i],'shipping_methods' => $data['shipping_methods'][$i]];	
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
				$id="";
				$objPostArray = (array) $objPost;
				$id = $this->db->insert('tbl_shipping_management',$objPostArray)->getLastInsertId(); 
				$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>"You are successfully add shipping"));
				redirectPage(SITE_URL.'manage-shipping');
			}else{
				$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUE));
				redirectPage(SITE_URL.'manage-shipping');
			}

		/*End Insertion Saction*/
		
	}
}
?>