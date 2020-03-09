<?php
class favouriteProduct extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$hide_class = "";
		$searchKeyword="";
		$FavProductLoopData=$loop_tags="";
		$where = "WHERE fp.item_type = 'p' AND fp.user_id = ".$_SESSION['userId'];
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="sc.subcatName_".$this->curr_language." AS subcatName";
		$unit_name="u.unit_value_".$this->curr_language." AS unit_value";
		$getFavouriteData=$this->db->pdoQuery("SELECT fp.*,p.product_tags,p.mark_premium,p.product_title,p.fob,p.product_slug,$catName,$subcatName,$unit_name,p.product_image,p.product_location,p.user_id,comp.business_type_id , comp.company_name,comp.logo,p.isNegotiable,p.min_price,p.max_price,p.order_quantity,p.delivery_terms , comp.id as compId
											FROM tbl_favorite_product AS fp
											INNER JOIN tbl_products AS p ON p.id = fp.item_id
											INNER JOIN tbl_product_category AS c ON c.id = p.cat_id
											INNER JOIN tbl_product_subcategory AS sc ON sc.id = p.sub_cat_id
											INNER JOIN tbl_unit_value AS u ON u.id = p.unit_id
											INNER JOIN tbl_company AS comp ON comp.user_id = p.user_id
											".$where."
											ORDER BY id desc LIMIT $starLimit, $endLimit
											")->results();
		//echo "<pre>";print_r($getFavouriteData);exit;
		$SearchResultRows=$this->db->pdoQuery("SELECT fp.*,p.product_tags,p.mark_premium,p.product_title,p.fob,p.product_slug,$catName,$subcatName,$unit_name,p.product_image,p.product_location,p.user_id,comp.company_name,comp.logo,p.isNegotiable,p.min_price,p.max_price,p.order_quantity
											FROM tbl_favorite_product AS fp
											INNER JOIN tbl_products AS p ON p.id = fp.item_id
											INNER JOIN tbl_product_category AS c ON c.id = p.cat_id
											INNER JOIN tbl_product_subcategory AS sc ON sc.id = p.sub_cat_id
											INNER JOIN tbl_unit_value AS u ON u.id = p.unit_id
											INNER JOIN tbl_company AS comp ON comp.user_id = p.user_id
											".$where."
											")->affectedrows();

		if($SearchResultRows == 0 ){
            $hide_class = '';
            $hide_no_record = '';
            $hide_more_record = 'hide';
        }
        else{
            $hide_class = "hidden";
            $hide_no_record = 'hide';
            $hide_more_record = '';
        }

		foreach ($getFavouriteData as $key => $value) {
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/quote_loop_data-sd.skd"))->compile();
			$product_image=explode(',',$value['product_image']);
			$imgFound="";
			foreach ($product_image as $img_key => $img_value) {
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$img_value, PATHINFO_EXTENSION); 
					if(empty($imgFound)){
						if ($ext == 'png' || $ext == 'PNG' || $ext == 'JPG' || $ext == 'jpg' || $ext == 'JPEG' || $ext == 'jpeg') {
							$product_image=!empty($img_value)?SITE_UPD.'product-sd/'.$img_value:SITE_UPD."no_prodcut_img.png"; 
							$imgFound="Yes";
						}else{
							$product_image = SITE_UPD."no_prodcut_img.png"; 
						}
					}
			}
			$mark_premium = '';
			$mark_premium = ($value['mark_premium'] == 'y') ? '<span class="premium-class ">Premium</span>' : '';
			$category=!empty($value['catName'])?$value['catName']:"";
			$subcategory=!empty($value['subcatName'])?$value['subcatName']:"";
			$UNIT_NM=!empty($value['unit_value'])?$value['unit_value']:"";
			$product_title=!empty($value['product_title'])?$value['product_title']:"";
			$product_location=!empty($value['product_location'])?$value['product_location']:"";
			$fav_pro_id=base64_encode(base64_encode($value['id']));
			$clogo=!empty($value['logo'])?SITE_UPD.'supplier_logo/'.$value['logo']:SITE_UPD.'no_user_image.png';
			$company_name=!empty($value['company_name'])?$value['company_name']:"";
			$selected_delivery_terms = '-';
			if (!empty($value['delivery_terms'])) {
				$deliveryTermsData = $this->db->pdoQuery("SELECT * FROM tbl_delivery_terms WHERE id in (".$value['delivery_terms'].") ",[])->results();
				$deliveryTermsData = array_column($deliveryTermsData, 'delivery_terms');
				$selected_delivery_terms = !empty($deliveryTermsData) ? implode(',',$deliveryTermsData) : '';
			}
			$price = ($value['isNegotiable'] == 'y') ? $value['min_price'].'-'.$value['max_price'] : $value['min_price'];
			$total_tags = '';
			$total_tags = explode(',',$value['product_tags']);
			foreach ($total_tags as $keys => $val) {
				$val= trim($val);
				if(empty($val)){
					continue;
				}
				$loop_content_tag = (new MainTemplater(DIR_TMPL.$this->module."/tag_loop_data-sd.skd"))->compile();
				$tags_loop_fields=array(
						"%TAGS%"=>$val
					);
				$loop_tags .= str_replace(array_keys($tags_loop_fields), array_values($tags_loop_fields), $loop_content_tag);
			}
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
			$responseRates = getCompanyReponseRates($value['compId']);
			$Quote_loop_fields=array(
					"%ATTECHMENTS_IMG%"=>$product_image,
					"%LOOP_TAGS%"=>$loop_tags,
					"%MIN_ORDER%"=>$value['order_quantity'],
					"%FOB%"=>$selected_delivery_terms,
					"%CATEGORY%"=>$category,
					"%SUBCATEGORY%"=>$subcategory,
					"%UNIT_NM%"=>$UNIT_NM,
					"%PRODUCT_TITLE%"=>$product_title,
					"%PRODUCT_LOCATION%"=>$product_location,
					"%PRICE%"=>$price.' '.CURRENCY_CODE,
					"%CLOGO%"=>$clogo,
					"%COMPANY_NAME%"=>$company_name,
					"%FAV_PRO_ID%"=>$fav_pro_id,
					"%PREMIUM%" =>$mark_premium,
					"%PRODUCT_SLUG%" => $value['product_slug'],
					"%BUSINESS_TYPE%"=>!empty($businessType)?$businessType:"",
					"%RESPONSE_RATE%" => $responseRates['responseRate'],
					"%RESPONSE_TIME%" => $responseRates['responseTime'],
				);

			$FavProductLoopData .= str_replace(array_keys($Quote_loop_fields), array_values($Quote_loop_fields), $loop_content);
		}
		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));

		$fields = array(
			"%HIDE_NO_RECORD%" => $hide_no_record,
			"%HIDE_MORE_RECORD%" => $hide_more_record,
			"%LEFT_PANEL%"=>$leftPanel,
			"%LOOP_FAV_PRODUCT%"=>$FavProductLoopData,
			"%HIDECLASS%" => $hide_class,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%SEARCH_KEYWORD%"=>$searchKeyword
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$FavProductLoopData,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}

}
?>