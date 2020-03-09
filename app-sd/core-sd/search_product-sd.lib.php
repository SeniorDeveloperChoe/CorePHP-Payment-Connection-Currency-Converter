<?php
class SearchProducts extends Home {
	function __construct($module = "", $id = 0, $result="",$SearchArray= array()) {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
		$this->SearchArray = $SearchArray;
	}
	public function getPageContent($SearchArray=array(),$starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$mark_premium = $subCategory = '';
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";
		$where = " WHERE p.isActive='y' AND p.deactive_by_user='n'  AND u.user_deleted='n' AND u.isActive='y'";

		$sub_cat_data=$this->db->pdoQuery("SELECT id,$subcatName FROM tbl_product_subcategory as s WHERE isActive=? ORDER BY s.subcatName_".$this->curr_language." ASC",array('y'))->results();
		foreach ($sub_cat_data as $key => $value) {
			$selected="";				
				if(!empty($_GET['filter_by_sub_cat_id']) && $_GET['filter_by_sub_cat_id']==$value['id'])	{
					$selected="selected";
			}
			$subCategory.="<option value='".$value['id']."' ".$selected.">".$value['subCatName']."</option>";
		}
		if(!empty($_SESSION['userId'])){
			$where .= " AND p.user_id!=".$_SESSION['userId'];
		}


		if(!empty($SearchArray['field_name_check']) || !empty($_GET['field_name_check'])){
			if(!empty($SearchArray['field_name_check'])){
				$field_name_check = $SearchArray['field_name_check'];
			} 
			else if(!empty($_GET['field_name_check'])){
				$field_name_check = $_GET['field_name_check'];
			}
			foreach ($field_name_check as $keys => $values) {
				$values_all = str_replace("'","\'",trim($values));
				$where .= " and p.dynamic_response LIKE '%".$values_all."%'";
			}
		}
		if(!empty($SearchArray['field_name_radio']) || !empty($_GET['field_name_radio'])){
			if(!empty($SearchArray['field_name_radio'])){
				$field_name_radio = $SearchArray['field_name_radio'];
			} 
			else if(!empty($_GET['field_name_radio'])){
				$field_name_radio = $_GET['field_name_radio'];
			}
			$field_name_radio = str_replace("'","\'",trim($field_name_radio));
			$where .= " and p.dynamic_response LIKE '%".$field_name_radio."%'";
		}
		if(!empty($SearchArray['fnm_text']) || !empty($_GET['fnm_text'])){
			if(!empty($SearchArray['fnm_text'])){
				$fnm_text = $SearchArray['fnm_text'];
			} 
			else if(!empty($_GET['fnm_text'])){
				$fnm_text = $_GET['fnm_text'];
			}
			$fnm_text = str_replace("'","\'",trim($fnm_text));
			$where .= " and p.dynamic_response LIKE '%".$fnm_text."%'";
		}
		if(!empty($SearchArray['field_name_textarea']) || !empty($_GET['field_name_textarea'])){
			if(!empty($SearchArray['field_name_textarea'])){
				$field_name_textarea = $SearchArray['field_name_textarea'];
			} 
			else if(!empty($_GET['field_name_textarea'])){
				$field_name_textarea = $_GET['field_name_textarea'];
			}
			$field_name_textarea = str_replace("'","\'",trim($field_name_textarea));
			$where .= " and p.dynamic_response LIKE '%".$field_name_textarea."%'";
		}
		if(!empty($SearchArray['fnm_select']) || !empty($_GET['fnm_select'])){
			if(!empty($SearchArray['fnm_select'])){
				$fnm_select = $SearchArray['fnm_select'];
			} 
			else if(!empty($_GET['fnm_select'])){
				$fnm_select = $_GET['fnm_select'];
			}
			$fnm_select = str_replace("'","\'",trim($fnm_select));
			$where .= " and p.dynamic_response LIKE '%".$fnm_select."%'";
		}
		if(!empty($SearchArray['keyword']) || !empty($_GET['keyword'])){
			if(!empty($SearchArray['keyword'])){
				$keyword = $SearchArray['keyword'];
			} 
			else if(!empty($_GET['keyword'])){
				$keyword = $_GET['keyword'];
			}
			$keyword = str_replace("'","\'",trim($keyword));
			$where .= " and (p.product_title LIKE '%".$keyword."%' OR p.product_tags LIKE '%".$keyword."%')";
		}
		if(!empty($SearchArray['cat_id']) || !empty($_GET['cat_id'])){
			if(!empty($SearchArray['cat_id'])){
				$cat_id = $SearchArray['cat_id'];
			} else if(!empty($_GET['cat_id'])){
				$cat_id = $_GET['cat_id'];
			}
		 	$where .= " and c.id = ".$cat_id."";
		} if(!empty($SearchArray['subcat_id']) || !empty($_GET['subcat_id'])){
			if(!empty($SearchArray['subcat_id'])){
				$subcat_id = $SearchArray['subcat_id'];
			} else if(!empty($_GET['subcat_id'])){
				$subcat_id = $_GET['subcat_id'];
			}
		 	$where .= " and s.id = ".$subcat_id ."";
		} if(!empty($SearchArray['mark_premium']) || !empty($_GET['mark_premium'])){
			if(!empty($SearchArray['mark_premium'])){
				$mark_prmium = $SearchArray['mark_premium'];
			} else if(!empty($_GET['mark_premium'])){
				$mark_prmium = $_GET['mark_premium'];
			}			
			if($mark_prmium == 'y'){
		 		$where .= " and p.mark_premium = '".$mark_prmium ."'";
			}
		} if(!empty($SearchArray['location']) || !empty($_GET['location'])){
			if(!empty($SearchArray['location'])){
				$location = $SearchArray['location'];
			} else if(!empty($_GET['location'])){
				$location = $_GET['location'];
			}			
			$where .= " and p.product_location LIKE '%".$location."%'";
		}
		if(!empty($SearchArray['price_range_last']) || !empty($_GET['price_range_last'])){
			if(!empty($SearchArray['price_range_last'])){
				$slider_supplier = $SearchArray['price_range_last'];
			} else if(!empty($_GET['price_range_last'])){
				$slider_supplier = $_GET['price_range_last'];
			}
			if (strpos($slider_supplier, '$') !== false) {
				$min_val = explode('$',explode('-',$slider_supplier)[0])[1];
				$max_val = explode('$',explode('-',$slider_supplier)[1])[1];
			}else{
				$min_val = explode('-',$slider_supplier)[0];
				$max_val = explode('-',$slider_supplier)[1];
			}
			$where .= " and p.min_price BETWEEN ".$min_val." AND ".$max_val." and p.max_price <=".$max_val."";	
		}
		if(!empty($SearchArray['tags']) || !empty($_GET['tags'])){
			if(!empty($SearchArray['tags'])){
				$all_tags = $SearchArray['tags'];
			} else if(!empty($_GET['tags'])){
				$all_tags = $_GET['tags'];
			}

			$tags = !is_array($all_tags) ? explode(',',$all_tags) : $all_tags;
			foreach ($tags as $key => $value) {
			 $where .= " and p.product_tags LIKE '%".$value."%'";	 	
			}
		}

		if(!empty($SearchArray['sub_category_id']) || !empty($_GET['sub_category_id'])){
			if(!empty($SearchArray['sub_category_id'])){
				$sub_category_id = $SearchArray['sub_category_id'];
			} else if(!empty($_GET['sub_category_id'])){
				$sub_category_id = $_GET['sub_category_id'];
			}
			$sub_category_id_arr =  !is_array($sub_category_id) ? [$sub_category_id] : $sub_category_id;
			$where .= " AND (";
			$whereSubCat = [];
			foreach ($sub_category_id_arr as $key => $catVal) {
				
				$whereSubCat[]= "p.sub_cat_id = '$catVal'";
			}
			$where .= implode(" OR ", $whereSubCat);
			//print_r($whereSubCat);exit;
			$where .= " ) ";

		}
		
			$order = "ORDER BY u.gold_icon_display ASC";
		if(!empty($SearchArray['price_filter_val']) || !empty($_GET['price_filter_val'])){
			if(!empty($SearchArray['price_filter_val'])){
				$price_filter_val = $SearchArray['price_filter_val'];
			} else if(!empty($_GET['price_filter_val'])){
				$price_filter_val = $_GET['price_filter_val'];
			}
			if($price_filter_val == 'DESC'){
		 	 $order .= ", p.min_price DESC";
			}else{
			 $order .= ", p.min_price ASC";
			}
		}
		if(!empty($SearchArray['min_filter_val']) || !empty($_GET['min_filter_val'])){
			if(!empty($SearchArray['min_filter_val'])){
				$min_filter_val = $SearchArray['min_filter_val'];
			} else if(!empty($_GET['min_filter_val'])){
				$min_filter_val = $_GET['min_filter_val'];
			}
			if($min_filter_val == 'DESC'){				
		 	 $order .= ", p.order_quantity DESC";
			}else{
			 $order .= ", p.order_quantity ASC";
			}
		}
		$GetProductData=$this->db->pdoQuery("SELECT p.*,$catName,$subcatName,$unitName,
											concat(u.first_name,' ',u.last_name) as supplier_name, u.profile_img as supplier_img, co.company_name,co.logo,co.location,co.business_type_id ,co.company_slug
											FROM tbl_products AS p
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
											INNER JOIN tbl_users AS u ON u.id=p.user_id
											INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
											LEFT JOIN tbl_dynamic_form_fields_response AS df ON df.request_id=p.id 
											LEFT JOIN tbl_company AS co ON co.user_id=p.user_id
											INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id". $where." GROUP BY p.id ".$order."
											LIMIT $starLimit, $endLimit")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT p.*,$catName,$subcatName,$unitName
											FROM tbl_products AS p
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id
											INNER JOIN tbl_users AS u ON u.id=p.user_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
											LEFT JOIN tbl_dynamic_form_fields_response AS df ON df.request_id=p.id 
											LEFT JOIN tbl_company AS co ON co.user_id=p.user_id
											INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id". $where." GROUP BY p.id ".$order."
											")->affectedrows();
		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }
		$product_loop_data="";
		if(empty($GetProductData)){
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/no_records-sd.skd"))->compile();
			$product_loop_data = $loop_content;
		}else{
			$getProductImg = $tags ='';
			foreach ($GetProductData as $key => $value) {
				//echo "<pre>";print_r($value);exit;
				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/product_loop_data-sd.skd"))->compile();
				$getProductImg=explode(',',$value['product_image']);
				$getProductImg=!empty($getProductImg[0])?SITE_PRODUCT_IMG.$getProductImg[0]:SITE_UPD."no_prodcut_img.png";
				$tags ='';
				if(!empty($value['product_tags'])){
				$tag=explode(',',$value['product_tags']);
				$tags.='<div class="pro-tags">';
				foreach ($tag as $key => $tagValue) {
						if (!empty($tagValue)) {
							$productSearchUrl = SITE_URL."search-product/?tags=$tagValue";
							$tags.='<label class="label label-default" id="tag'.$key.'"> <a href="'.$productSearchUrl.'">'.$tagValue.'</a></label>';
								}
						}
						$tags.='</div>';	
					}
					$chkFavId ='';
				$favCalss="fa fa-heart-o";
				$span_class = '';
				if(!empty($_SESSION['userId'])){					
					$chkFavId=$this->db->select("tbl_favorite_product",array("id"),array("item_id"=>$value['id'],"user_id"=>$_SESSION['userId'],"item_type"=>'p'))->result();
					if(empty($chkFavId)){
						$favCalss="fa fa-heart-o";
					}else{
						$favCalss="fa fa-heart";
						$span_class="class='liked'";
					}
				}
				$getComapreProductId=$this->db->pdoQuery("SELECT id FROM tbl_product_compare WHERE ipAddress=? AND product_id=?",array(get_ip_address(),$value['id']))->result();	

				$userImage = SITE_UPD.'no_user_image.png';
				if(!empty($value['supplier_img'])){
					$userImage = SITE_UPD.'users-sd/'.$value['user_id'].'/'.$value['supplier_img'];
				}
				$supplier_link = SITE_URL.'supplier-detail-page/'.$value['company_slug'];

				/*-----Get Business Type-----*/
				$businessIds=explode(',', $value['business_type_id']);
				$lastElement=end($businessIds);
				$businessType="";
					$businessTypee="business_type_".$this->curr_language;
				foreach ($businessIds as $key => $busValue) {
					$getBusinessType=$this->db->select("tbl_business_type",array($businessTypee),array("id"=>$busValue))->result();

					if($busValue!=$lastElement){
						$businessType .= $getBusinessType[$businessTypee].', ';
					}else{
						$businessType .= $getBusinessType[$businessTypee];
					}

				}
				/*-----End Get Business Type Saction-----*/
				$week_month = '';
				if (!empty($value['week_month'])) {
					$week_month = $value['week_month'] == 'week' ? LBL_WEEK : LBL_MONTH;
				}
				$selected_delivery_terms = '-';
				if (!empty($value['delivery_terms'])) {
					$deliveryTermsData = $this->db->pdoQuery("SELECT * FROM tbl_delivery_terms WHERE id in (".$value['delivery_terms'].") ",[])->results();
					$deliveryTermsData = array_column($deliveryTermsData, 'delivery_terms');


					$selected_delivery_terms = !empty($deliveryTermsData) ? implode(',',$deliveryTermsData) : '';

				}

				$loop_fields = array(
									"%PRODUCT_TITLE%"=>$value['product_title'],
									"%PRODUCT_SLUG%"=>$value['product_slug'],
									"%ID%"=>$value['id'],
									"%PRODUCT_ID%"=>base64_encode(base64_encode($value['id'])),
									"%PRO_IMG%"=>$getProductImg,
									"%MARK_PREMIUM_CLS%"=>($value['mark_premium'] == 'y') ? '':'hide',				
									"%CHECK_FAV%"=>$favCalss,
									"%SPAN_CLASS%"=>$span_class,
									"%FOB%"=>$selected_delivery_terms,
									"%PRICE_AMOUNT%"=>($value['isNegotiable'] == 'y') ? $value['min_price'].'-'.$value['max_price'].' '.CURRENCY_CODE:$value['min_price'].' '.CURRENCY_CODE,
									"%CATNAME%" => $value['catName'],
									"%ORDER_QUANTITY%" => $value['order_quantity'],
									"%PRODUCT_LOCATION%" => $value['product_location'],
									"%SUBCATNAME%" => $value['subCatName'],
									"%SUPPLY_ABILITY%" => $value['supply_ability'],
									"%MIN_PRICE%" => $value['min_price'],
									"%MAX_PRICE%" => $value['max_price'],
									"%TAGS%" => $tags,
									"%UNIT_NM%" => $value['unitName'],
									"%CHKECKED_COMAPRE_PRODUCT%" =>!empty($getComapreProductId)?"checked":"",
					"%SUPPLIER_NAME%" => $value['company_name'],
					"%SUPPLIER_IMG%" => !empty($value['logo'])?SITE_UPD.'supplier_logo/'.$value['logo']:SITE_UPD.'supplier_logo/no_comapany.PNG',

					"%SUPPLIER_LINK%" => $supplier_link,
					"%SUPPLIER_LOCATION%" => $value['location'],
					"%BUSINESS_TYPE%" => $businessType,
					"%WEEK_MONTH%" => $week_month,
					"%UNIT_NAME%"=>$value['unitName'],

									);
				$product_loop_data .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
				}
		}
		$get_all_category=$this->db->pdoQuery("SELECT c.*
											FROM tbl_product_category AS c
											WHERE c.isActive =?
											Order by c.categoryName
												",array('y'))->results();
		$all_cat_list = $CompareProducts ='';
		foreach ($get_all_category as $key => $value) {
				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/all_cat_list-sd.skd"))->compile();
				$loop_fields = array(
									"%CAT_NAME%"=>$value["categoryName_$this->curr_language"],
									"%SELECTD_CAT_ID%"=>(!empty($_GET['cat_id'])? (($_GET['cat_id'] == $value['id']) ? 'checked' : '') :''),
									"%CAT_ID%"=>$value['id']
									);
				$all_cat_list .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
				}

		$cat_id = (!empty($SearchArray['cat_id']) || !empty($_GET['cat_id'])) ? ((!empty($SearchArray['cat_id'])) ? $SearchArray['cat_id']:$_GET['cat_id']) :0;
		$hide_cls_subcat_heading = (!empty($_GET['cat_id'])) ? '':'hide';
		$get_all_sub_category=$this->db->pdoQuery("SELECT sc.*
											FROM tbl_product_subcategory AS sc
											WHERE sc.isActive =? AND sc.catId = ?
												",array('y',$cat_id))->results();
		$all_sub_cat_list ='';
		foreach ($get_all_sub_category as $key => $value) {
				$loop_content_subcat = (new MainTemplater(DIR_TMPL.$this->module."/all_sub_cat_list-sd.skd"))->compile();
				$loop_fields_subcat = array(
									"%SUBCAT_NM%"=>$value["subcatName_$this->curr_language"],
									"%SUB_CAT_ID%"=>$value['id'],
									"%SELECTD_SUBCAT_ID%"=>(!empty($_GET['subcat_id'])? (($_GET['subcat_id'] == $value['id']) ? 'checked' : '') :'')
									);
				$all_sub_cat_list .= str_replace(array_keys($loop_fields_subcat), array_values($loop_fields_subcat), $loop_content_subcat);
				}
		$subcat_id = (!empty($SearchArray['subcat_id']) || !empty($_GET['subcat_id'])) ? ((!empty($SearchArray['subcat_id'])) ? $SearchArray['subcat_id']:$_GET['subcat_id']) :0;
		$all_dynamic_fields ='';
		$get_all_dynamic_form_val=$this->db->pdoQuery("SELECT df.*
											FROM tbl_dynamic_form_fields AS df
											INNER JOIN tbl_dynamic_form AS d ON(d.id = df.formId)
											WHERE df.add_in_search =? AND d.catId = ? AND subcatId=?",array('y',$cat_id,$subcat_id))->results();
	
		foreach ($get_all_dynamic_form_val as $key => $value) {			
			$field_value_array = explode('|', $value["field_value_$this->curr_language"]);
			$field_val='';
			if($value['field_type'] == "1"){
					$loop_content_dynamic_fields = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_frm_textbox-sd.skd"))->compile();
					if(!empty($SearchArray['fnm_text']) || !empty($_GET['fnm_text'])){
						if(!empty($SearchArray['fnm_text'])){
							$fnm_text = $SearchArray['fnm_text'];
						} 
						else if(!empty($_GET['fnm_text'])){
							$fnm_text = $_GET['fnm_text'];
						}					
					}
					if(!empty($fnm_text)){
						$values_final = $fnm_text;
					}else{
						$values_final = '';
					}
					$field_val .= '<div class="form-group">
								    <input type="text" class="form-control" name="fnm_text" id="fnm_text" placeholder="'.PRODUCT_SEARCH.'" value="'.$values_final.'">
								  </div>';
				}else if($value['field_type'] == "2"){
					$loop_content_dynamic_fields = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_frm_textarea-sd.skd"))->compile();
					if(!empty($SearchArray['field_name_textarea']) || !empty($_GET['field_name_textarea'])){
						if(!empty($SearchArray['field_name_textarea'])){
							$field_name_textarea = $SearchArray['field_name_textarea'];
						} 
						else if(!empty($_GET['field_name_textarea'])){
							$field_name_textarea = $_GET['field_name_textarea'];
						}					
					}
					if(!empty($field_name_textarea)){
						$values_final = $field_name_textarea;
					}else{
						$values_final = '';
					}
					$field_val .= '<div class="form-group">
								    <textarea rows="2" class="form-control field_name_textarea" name="field_name_textarea" id="field_name_textarea" placeholder="'.PRODUCT_SEARCH.'">'.$values_final.'</textarea>
								  </div>';
				} else if($value['field_type'] == "4"){
					$loop_content_dynamic_fields = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_frm_select-sd.skd"))->compile();
					$option_val = $all_question_data='';
					$all_question_data = '';
					$selected_vall = '';
					if(!empty($SearchArray['fnm_select']) || !empty($_GET['fnm_select'])){
						if(!empty($SearchArray['fnm_select'])){
							$fnm_select = $SearchArray['fnm_select'];
						} 
						else if(!empty($_GET['fnm_select'])){
							$fnm_select = $_GET['fnm_select'];
						}					
					}else{
						$fnm_select = '';
					}
					foreach ($field_value_array as $key_field_vall => $value_field_vall) {
						if(!empty($fnm_select)){
							if($fnm_select == $value_field_vall){
								$selected_vall = 'selected';							
							}else{
								$selected_vall = '';
							}
						}else{
							$selected_vall = '';
						}
						$option_val .= '<option value="'.$value_field_vall.'" '.$selected_vall.'>'.$value_field_vall.'</option>';
					}
					$field_val .= '<div class="form-group">
								    <select class="form-control" name="fnm_select" id="fnm_select">
								    <option value="">---'.SELECT_OPTION.'---</option>
								    '.$option_val.'
								    </select>
								  </div>';
				}
			foreach ($field_value_array as $keys => $values) {
				$checkbox = '';
				$selected = '';
				$in_array ='';				
				if($value['field_type'] == "6"){
					$loop_content_dynamic_fields = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_frm_radio-sd.skd"))->compile();
					if(!empty($SearchArray['field_name_radio']) || !empty($_GET['field_name_radio'])){
						if(!empty($SearchArray['field_name_radio'])){
							$field_name_radio = $SearchArray['field_name_radio'];
						} 
						else if(!empty($_GET['field_name_radio'])){
							$field_name_radio = $_GET['field_name_radio'];
						}					
					}
					if(!empty($field_name_radio)){
						if($field_name_radio == $values){
							$selected = 'checked';
						}else{
							$selected = '';
						}
					} else{
						$selected = '';
					}
					$field_val .= '<div class="radio">
								  <label><input type="radio" name="field_name_radio" id="field_name_radio" value="'.$values.'" '.$selected.'>'.$values.'</label>
								</div>';
				}
				else if($value['field_type'] == "5"){
					$loop_content_dynamic_fields = (new MainTemplater(DIR_TMPL.$this->module."/dynamic_frm_checkbox-sd.skd"))->compile();
					if(!empty($SearchArray['field_name_check']) || !empty($_GET['field_name_check'])){
						if(!empty($SearchArray['field_name_check'])){
							$field_name_check = $SearchArray['field_name_check'];
						} 
						else if(!empty($_GET['field_name_check'])){
							$field_name_check = $_GET['field_name_check'];
						}					
					}
					if(!empty($field_name_check)){
						$in_array  = in_array($values, $field_name_check);
						if($in_array){
							$checked = 'checked';
						}else{
							$checked = '';
						}
					} else{
						$checked = '';
					}
					$field_val .= '<div class="checkbox">
								  <label><input type="checkbox" name="field_name_check[]" id="field_name_check" value="'.$values.'" '.$checked.'>'.$values.'</label>
								</div>'; 
				}
				
			}
			$loop_fields_dynmic_fields = array(
								"%FILTER_NM%"=>$value["filter_name_$this->curr_language"],
								"%FORM_FIELD_NM%"=>$value['id'],
								"%FIELD_VALUE%"=>$field_val
								);
			$all_dynamic_fields .= str_replace(array_keys($loop_fields_dynmic_fields), array_values($loop_fields_dynmic_fields), $loop_content_dynamic_fields);
			}

			$htmlProductComapre = (new MainTemplater(DIR_TMPL . $this->module."/compare_product-sd.skd"))->compile();	
			$CompareProducts=$getComapreProductImage=$ComapreProductfields="";
			$getComapreProducts	= $this->db->pdoQuery("SELECT c.id,p.product_title,p.product_image FROM tbl_product_compare AS c
														INNER JOIN tbl_products AS p ON  p.id=c.product_id
														WHERE ipAddress=?",array(get_ip_address()))->results();
			if(!empty($getComapreProducts)){
				foreach ($getComapreProducts as $key => $value) {

						$getComapreProductImage=explode(',', $value['product_image']);
						
						$ComapreProductfields = array(
								"%COMPARE_ID%"=>base64_encode(base64_encode($value['id'])),
								"%COMAPRE_PRODUCT_IMG%"=>!empty($getComapreProductImage[0])?SITE_PRODUCT_IMG.$getComapreProductImage[0]:SITE_UPD."no_prodcut_img.png",
								"%COMAPRE_PRODUCT_TITLE%"=>!empty($value['product_title'])?$value['product_title']:""
							);
						$CompareProducts .= str_replace(array_keys($ComapreProductfields), array_values($ComapreProductfields), $htmlProductComapre);
				}
			}
		$getTotalComapreProducts_ipBased=$this->getTotalComapreProduct();	
		$getTotalComapreProducts_ipBased=!empty($getTotalComapreProducts_ipBased)?$getTotalComapreProducts_ipBased:"";
		$comparePageLink="";

		if($getTotalComapreProducts_ipBased>1){
			$comparePageLink='<div class="compare-extra comparepage">
				              <a id="compareout_of_total" href="'.SITE_URL.'compare-products">
				                '.COMPARE.'('.$getTotalComapreProducts_ipBased.'/4)
				              </a>
				          </div>';
		}
		
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMITS);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMITS || $totalRow < PAGE_DISPLAY_LIMITS) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
		$keyword = '';
		if(!empty($SearchArray['keyword']) || !empty($_GET['keyword'])){
			if(!empty($SearchArray['keyword'])){
				$keyword = $SearchArray['keyword'];
			} 
			else if(!empty($_GET['keyword'])){
				$keyword = $_GET['keyword'];
			}
		}
		$last_mark ='';
		if(!empty($SearchArray['mark_premium'])){
				$last_mark = $SearchArray['mark_premium'];
			} else if(!empty($_GET['mark_premium'])){
				$last_mark = $_GET['mark_premium'];
			}
		$location ='';		
		if(!empty($SearchArray['location']) || !empty($_GET['location'])){
			if(!empty($SearchArray['location'])){
				$location = $SearchArray['location'];
			} else if(!empty($_GET['location'])){
				$location = $_GET['location'];
			}			
		}
		
		$all_tags =$tags_allll = $all_tag_comma_saperate ='';
		
		$get_max_default=$this->db->pdoQuery("SELECT max(max_price) AS finalMax FROM tbl_products WHERE isActive = 'y'")->result();
		$min_val_show = $max_val_show = '';
		if(!empty($SearchArray['price_range_last']) || !empty($_GET['price_range_last'])){
			if(!empty($SearchArray['price_range_last'])){
				$slider_supplier = $SearchArray['price_range_last'];
			} else if(!empty($_GET['price_range_last'])){
				$slider_supplier = $_GET['price_range_last'];
			}				
			if (strpos($slider_supplier, '$') !== false) {
				$min_val_show = explode('$',explode('-',$slider_supplier)[0])[1];
				$max_val_show = explode('$',explode('-',$slider_supplier)[1])[1];
			}else{
				$min_val_show = explode('-',$slider_supplier)[0];
				$max_val_show = explode('-',$slider_supplier)[1];
			}
		}
		$price_filter_val = $min_filter_val ='';
		if(!empty($SearchArray['price_filter_val']) || !empty($_GET['price_filter_val'])){
			if(!empty($SearchArray['price_filter_val'])){
				$price_filter_val = $SearchArray['price_filter_val'];
			} else if(!empty($_GET['price_filter_val'])){
				$price_filter_val = $_GET['price_filter_val'];
			}
		}

		$selected_price_desc = '';
		$selected_price_asc = '';
		$selected_price_desc = ($price_filter_val == 'DESC') ? 'selected' : '';
		$selected_price_asc = ($price_filter_val == 'ASC') ? 'selected' : '';
		if(!empty($SearchArray['min_filter_val']) || !empty($_GET['min_filter_val'])){
			if(!empty($SearchArray['min_filter_val'])){
				$min_filter_val = $SearchArray['min_filter_val'];
			} else if(!empty($_GET['min_filter_val'])){
				$min_filter_val = $_GET['min_filter_val'];
			}
		}
		$selected_min_desc = '';
		$selected_min_asc = '';
		$selected_min_desc = ($min_filter_val == 'DESC') ? 'selected' : '';
		$selected_min_asc = ($min_filter_val == 'ASC') ? 'selected' : '';

		$keyword_final='';
		$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		if (strpos($actual_link, '?') !== false) {
			$keyword_whole = explode('?',$actual_link);
			$keyword_final = end($keyword_whole);
		}else{
			$keyword_final = '';
		}				

		$fields=array("%LEFT_PANEL%"=>$leftPanel,
					 "%QUERY_URL%"=>'?'.$keyword_final,
					 "%HIDE_CLS_SUBACT_HEADER%"=>$hide_cls_subcat_heading,
					 "%ALL_CAT_LIST%" => $all_cat_list,
					 "%ALL_SUB_CAT_LIST%" => $all_sub_cat_list,
					 "%ALL_DYNAMIC_FIELDS%" => !empty($all_dynamic_fields) ? '<div class="ques-ans dynamic_frm">'.$all_dynamic_fields.'</div>' : '',
					 "%MARK_PREMIUM_CHECKED%"=>($last_mark == 'y') ? 'checked':'',
					 "%LOCATION_VAL%"=>$location,
					 "%PRODUCT_LOOP_DATA%"=>$product_loop_data,
					 "%TOTAL_PAGES%" => $totalPages,
					 "%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMITS,
					 "%CURRENT_PAGE%" => $page,
					 "%HIDECLASS%" => $hide_class,
					 "%COMPARE_CLASS%"=>empty($CompareProducts)?"hide":"",
					 "%COMPAR_PRODUCTS%"=>$CompareProducts,
					 "%KEYWORD%"=>$keyword,
					 "%TAGS%"=>$all_tag_comma_saperate,
					 "%MIN_VAL_SHOW%"=>$min_val_show,
					 "%MAX_VAL_SHOW%"=>($max_val_show == 0) ? $get_max_default['finalMax']:$max_val_show,
					 "%SELECTED_PRICE_DESC%" =>$selected_price_desc,
					 "%SELECTED_PRICE_ASC%" =>$selected_price_asc,
					 "%SELECTED_MIN_DESC%" =>$selected_min_desc,
					 "%SELECTED_MIN_ASC%" =>$selected_min_asc,
					 "%ALL_TAG_COMMA_SAPERATE%"=>$all_tag_comma_saperate,
					 "%COMPAREPAGE_LINK%"=>$comparePageLink,
					 "%GETTOTALCOMAPREPRODUCTS_IPBASED%"=>$getTotalComapreProducts_ipBased,
					 "%CHKECKED_COMAPRE_PRODUCT%"=>!empty($getComapreProductId)?"checked":"",
					 "%TOTAL_COMPARE_PRODUCT%"=>!empty($getTotalComapreProducts_ipBased)?$getTotalComapreProducts_ipBased:"",
					 "%PRICE_FILTER_LOAD%"=>isset($price_filter_val) ? $price_filter_val  :'',
					 "%MIN_FILTER_VAL%"=>isset($min_filter_val) ? $min_filter_val  :'',
					 "%SUB_CATEGORIES%"=>$subCategory,

					);
		$html = str_replace(array_keys($fields), array_values($fields), $html);

		$data=array("main_content"=>$html,"products"=>$product_loop_data,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class,'all_sub_cat_list'=>$all_sub_cat_list,'all_dynamic_fields'=>$all_dynamic_fields);
		return $data;
	}
	public function getTotalComapreProduct(){	
			$getTotalComapreProduct=$this->db->pdoQuery("SELECT count(id) AS totalCompareProduct FROM tbl_product_compare WHERE ipAddress = ?",array(get_ip_address()))->result();
			return $getTotalComapreProduct['totalCompareProduct'];
	}
}
?>