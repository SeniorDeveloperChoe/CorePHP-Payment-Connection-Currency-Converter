<?php
class SearchSupplier extends Home {
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
		$verified ='';
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$where = '';
		$where = " WHERE c.isActive = 'y' AND u.user_deleted='n' AND u.isActive='y'";

		
		if(!empty($_SESSION['userId'])){
			$where .= " AND cp.user_id!=".$_SESSION['userId'];
		}


		if(!empty($SearchArray['keyword']) || !empty($_GET['keyword'])){
			if(!empty($SearchArray['keyword'])){
				$keyword = $SearchArray['keyword'];
			} 
			else if(!empty($_GET['keyword'])){
				$keyword = $_GET['keyword'];
			}
			$where .= ' and cp.company_name LIKE "%'.$keyword.'%"';
		}
		if(!empty($SearchArray['cat_id']) || !empty($_GET['cat_id'])){
			if(!empty($SearchArray['cat_id'])){
				$cat_id = $SearchArray['cat_id'];
			} else if(!empty($_GET['cat_id'])){
				$cat_id = $_GET['cat_id'];
			}
		 	$where .= ' and c.id = '.$cat_id.'';
		} if(!empty($SearchArray['verified']) || !empty($_GET['verified'])){
			if(!empty($SearchArray['verified'])){
				$verified = $SearchArray['verified'];
			} else if(!empty($_GET['verified'])){
				$verified = $_GET['verified'];
			}			
			if($verified == 'y'){
		 		$where .= ' and cp.verified = "'.$verified .'"';
			}
		} if(!empty($SearchArray['location']) || !empty($_GET['location'])){
			if(!empty($SearchArray['location'])){
				$location = $SearchArray['location'];
			} else if(!empty($_GET['location'])){
				$location = $_GET['location'];
			}			
			$where .= ' and cp.location LIKE "%'.$location.'%"';
		}
		if(!empty($SearchArray['business_type']) || !empty($_GET['business_type'])){
			if(!empty($SearchArray['business_type'])){
				$business_type = $SearchArray['business_type'];
			} else if(!empty($_GET['business_type'])){
				$business_type = $_GET['business_type'];
			}			
			$where .= ' and cp.business_type_id LIKE "%'.$business_type.'%"';
		}
		if(!empty($SearchArray['revanue']) || !empty($_GET['revanue'])){
			if(!empty($SearchArray['revanue'])){
				$revanue = $SearchArray['revanue'];
			} else if(!empty($_GET['revanue'])){
				$revanue = $_GET['revanue'];
			}
			if($revanue == 1){
				$final_revanue = 'Below US$1 Million';
			}else if($revanue == 2){
				$final_revanue = 'US$1 Million - US$2.5 Million';
			}else if($revanue == 3){
				$final_revanue = 'US$2.5 Million - US$5 Million';
			}else if($revanue == 4){
				$final_revanue = 'US$5 Million - US$10 Million';
			}else if($revanue == 5){
				$final_revanue = 'US$10 Million - US$50 Million';
			}else if($revanue == 6){
				$final_revanue = 'US$50 Million - US$100 Million';
			}else if($revanue == 7){
				$final_revanue = 'Above US$100 Million';
			}		
			$where .= ' and cp.total_annual_revenue ="'.$final_revanue.'"';
		}		
		$GetProductData=$this->db->pdoQuery("SELECT p.product_slug,cp.*,$catName
											FROM tbl_company AS cp
											INNER JOIN tbl_users AS u on u.id = cp.user_id
											INNER JOIN tbl_products AS p on p.user_id = cp.user_id
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id". $where." GROUP BY p.user_id 
											LIMIT $starLimit, $endLimit")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT p.product_slug,cp.*,$catName
											FROM tbl_company AS cp
											INNER JOIN tbl_users AS u on u.id = cp.user_id
											INNER JOIN tbl_products AS p on p.user_id = cp.user_id
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id". $where." GROUP BY p.user_id 
											")->affectedrows();
		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }
		$Supplier_loop_data="";
		if(empty($GetProductData)){
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/no_records-sd.skd"))->compile();
			$Supplier_loop_data = $loop_content;
		}else{
			$getProductImg = $tags ='';
			foreach ($GetProductData as $key => $value) {
				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/supplier_loop_data-sd.skd"))->compile();
				$getProductImg=!empty($value['logo'])?SITE_UPD.'supplier_logo/'.$value['logo']:SITE_UPD."no_prodcut_img.png";
				$chkFavId ='';
				$favCalss="fa fa-heart-o";
				$span_class = '';
				if(!empty($_SESSION['userId'])){					
					$chkFavId=$this->db->select("tbl_favorite_product",array("id"),array("item_id"=>$value['id'],"user_id"=>$_SESSION['userId'],"item_type"=>'s'))->result();
					if(empty($chkFavId)){
						$favCalss="fa fa-heart-o";
					}else{
						$favCalss="fa fa-heart";
						$span_class="liked";
					}
				}
				$responseRates = getCompanyReponseRates($value['id']);

				$productCategory = $this->getAllProductCategory($value['user_id']);
				$loop_fields = array(
									"%COMPANY_NAME%"=>$value['company_name'],
									"%COMPANY_SLUG%"=>$value['company_slug'],
									"%BUSINESS_TYPE%"=>getBusinessType($value['business_type_id']),
									"%CONTACT_TO_SUPPLIER_URL%"=>SITE_URL.'contact-to-supplier/'.$value['product_slug'],
									"%ID%"=>$value['id'],
									"%PRODUCT_ID%"=>base64_encode(base64_encode($value['id'])),
									"%PRO_IMG%"=>$getProductImg,
									"%VERIFIED_CLS%"=>($value['verified'] == 'y') ? '':'hide',				
									"%CHECK_FAV%"=>$favCalss,
									"%SPAN_CLASS%"=>$span_class,
									"%TOTAL_ANNUAL_REVENUE%"=>!empty($value['total_annual_revenue']) ? $value['total_annual_revenue'] : '-',
									"%CATNAME%" => $value['catName'],
									"%RESPONSE_RATE%" => $responseRates['responseRate'],
									"%RESPONSE_TIME%" => $responseRates['responseTime'],
									"%DETAILED_DESCRIPTION%" =>!empty($value['detailed_description']) ? $value['detailed_description'] : '',
									"%LOCATION%" =>$value['location'],
									"%ALL_PRO_CAT%" =>$productCategory,

									);
				$Supplier_loop_data .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
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
		$get_all_businessType=$this->db->pdoQuery("SELECT b.*
											FROM tbl_business_type AS b
											WHERE b.isActive =?
											Order by b.business_type
												",array('y'))->results();
		$all_business_list ='';
		foreach ($get_all_businessType as $key => $value) {
				$checked =(!empty($_GET['business_type'])? (($_GET['business_type'] == $value['id']) ? 'selected' : '') :'');
				$all_business_list .= '<option value="'.$value['id'].'" '.$checked.'>'.$value["business_type_$this->curr_language"].'</option>';
		}
		$cat_id = (!empty($SearchArray['cat_id']) || !empty($_GET['cat_id'])) ? ((!empty($SearchArray['cat_id'])) ? $SearchArray['cat_id']:$_GET['cat_id']) :0;
		
		$keyword_main = '';
		if(!empty($SearchArray['keyword']) || !empty($_GET['keyword'])){
			if(!empty($SearchArray['keyword'])){
				$keyword_main = $SearchArray['keyword'];
			} 
			else if(!empty($_GET['keyword'])){
				$keyword_main = $_GET['keyword'];
			}
		}
		$last_verified = '';
		if(!empty($SearchArray['verified']) || !empty($_GET['verified'])){
			if(!empty($SearchArray['verified'])){
				$last_verified = $SearchArray['verified'];
			} else if(!empty($_GET['verified'])){
				$last_verified = $_GET['verified'];
			}	
		}
		$location_last =''; 
		if(!empty($SearchArray['location']) || !empty($_GET['location'])){
			if(!empty($SearchArray['location'])){
				$location_last = $SearchArray['location'];
			} else if(!empty($_GET['location'])){
				$location_last = $_GET['location'];
			}			
		}
		$selected_1 =$selected_2 =$selected_3 =$selected_4 =$selected_5 =$selected_6 =$selected_7 ='';
		if(!empty($SearchArray['revanue']) || !empty($_GET['revanue'])){
			if(!empty($SearchArray['revanue'])){
				$revanue = $SearchArray['revanue'];
			} else if(!empty($_GET['revanue'])){
				$revanue = $_GET['revanue'];
			}	
			if($revanue == 1){
				$selected_1 ='selected';
			}else if($revanue == 2){
				$selected_2 ='selected';
			}else if($revanue == 3){
				$selected_3 ='selected';
			}else if($revanue == 4){
				$selected_4 ='selected';
			}else if($revanue == 5){
				$selected_5 ='selected';
			}else if($revanue == 6){
				$selected_6 ='selected';
			}else if($revanue == 7){
				$selected_7 ='selected';
			}		
		}
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMITS);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMITS || $totalRow < PAGE_DISPLAY_LIMITS) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));

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
					 "%ALL_CAT_LIST%" => $all_cat_list,
					 "%REV%" => (isset($revanue) ? $revanue : ''),
					 "%BTYPE_ID%" =>(!empty($_GET['business_type'])? $_GET['business_type']: ''),
					 "%VERIFIED_CHECKED%"=>($last_verified == 'y') ? 'checked':'',
					 "%LOCATION_VAL%"=>$location_last,
					 "%PRODUCT_LOOP_DATA%"=>$Supplier_loop_data,
					 "%TOTAL_PAGES%" => $totalPages,
					 "%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMITS,
					 "%CURRENT_PAGE%" => $page,
					 "%HIDECLASS%" => $hide_class,
					 "%KEYWORD%"=>$keyword_main,
					 "%SELECT_BUSINESS_TYPE%" =>$all_business_list,
					 "%SELECTED_1%" => $selected_1,
			 		 "%SELECTED_2%" => $selected_2,
			 		 "%SELECTED_3%" => $selected_3,
			 		 "%SELECTED_4%" => $selected_4,
			 		 "%SELECTED_5%" => $selected_5,
			 		 "%SELECTED_6%" => $selected_6,
			 		 "%SELECTED_7%" => $selected_7
					);
		$html = str_replace(array_keys($fields), array_values($fields), $html);

		$data=array("main_content"=>$html,"products"=>$Supplier_loop_data,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}
	public function getAllProductCategory($user_id = 0) {
		$getProductCategory = $this->db->pdoQuery("SELECT c.categoryName_$this->curr_language FROM tbl_products AS p 
			INNER JOIN tbl_product_category AS c ON p.cat_id = c.id WHERE c.isActive = ? AND p.user_id = ? GROUP BY p.cat_id",array('y',$user_id))->results();
		if (!empty($getProductCategory)) {
			$response = array_column($getProductCategory, 'categoryName_'.$this->curr_language);
			return implode(", ", $response);
		}
		return "-";

	}
}
?>