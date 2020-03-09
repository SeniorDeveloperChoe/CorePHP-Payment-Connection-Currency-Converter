<?php
class favouriteBuyingReq extends Home {
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
		$hide_no_record = '';
		$hide_more_record = '';
		$searchKeyword="";
		$time_left ='';
		$FavSupplierLoopData=$loop_tags="";
		$where = "WHERE fp.item_type = 's' AND fp.user_id = ".$_SESSION['userId'];
		$businessType="bt.business_type_".$this->curr_language." AS businessType";
		$getFavouriteData=$this->db->pdoQuery("SELECT fp.*,comp.company_slug,comp.company_name,comp.logo,comp.location,comp.verified,comp.business_type_id,comp.user_id AS cUserId, comp.detailed_description, comp.id as compId
											FROM tbl_favorite_product AS fp
											INNER JOIN tbl_company AS comp ON comp.id = fp.item_id
											".$where."
											ORDER BY id desc LIMIT $starLimit, $endLimit
											")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT fp.*,comp.company_slug,comp.company_name,comp.logo,comp.verified,comp.business_type_id,comp.user_id AS cUserId
											FROM tbl_favorite_product AS fp
											INNER JOIN tbl_company AS comp ON comp.id = fp.item_id
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
			$imgFound="";
			$fav_pro_id=base64_encode(base64_encode($value['id']));
			
			$logo=!empty($value['logo'])?SITE_UPD.'supplier_logo/'.$value['logo']:SITE_UPD.'no_comapany.PNG';
			$total_product=$this->db->pdoQuery("SELECT count(id) AS total_product FROM tbl_products WHERE user_id = ? ",array($value['cUserId']))->result();
			$all_business_type="";
			$all_business_type=getBusinessType($value['business_type_id']);

			$chk_Verified_Supplirer="";
			if($value['verified']=='y'){
				$chk_Verified_Supplirer='<span class="premium-class verified-class ">
                            Verified
                          </span>';
			}


			$catName="c.categoryName_".$this->curr_language." AS catName";
			$getSupplierCategories=$this->db->pdoQuery("SELECT  DISTINCT p.cat_id,$catName 
														FROM `tbl_products` AS p
														INNER JOIN tbl_product_category AS c ON c.id=p.cat_id
														WHERE user_id=?",array($value['cUserId']))->results();
			$lastsupCat=end($getSupplierCategories);
			$suCats="";
			foreach ($getSupplierCategories as $cat_key => $cat_value) {
					if($lastsupCat['catName']==$cat_value['catName']){
						$suCats.=$cat_value['catName'];
					}else{
						$suCats.=$cat_value['catName'].',';
					}
			}
			$responseRates = getCompanyReponseRates($value['compId']);

			$Quote_loop_fields=array(
					"%LOGO%"=>$logo,
					"%COMPANY_NAME%"=>$value['company_name'],
					"%COMOPANY_SLUG%"=>$value['company_slug'],
					"%LOCATION%"=>$value['location'],
					"%TOTAL_PRO%"=>$total_product['total_product'],
					"%BUSINESS%"=>$all_business_type,
					"%FAV_PRO_ID%"=>$fav_pro_id,
					"%CHK_VERIFIED_SUPPLIRER%"=>$chk_Verified_Supplirer,
					"%SUCATS%"=>$suCats,
					"%COMP_DESC%" => $value['detailed_description'],
					"%RESPONSE_RATE%" => $responseRates['responseRate'],
					"%RESPONSE_TIME%" => $responseRates['responseTime'],
				);
			$FavSupplierLoopData .= str_replace(array_keys($Quote_loop_fields), array_values($Quote_loop_fields), $loop_content);
		}
		$totalRow =$SearchResultRows;
		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);
		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));
		$fields = array(
			"%HIDE_NO_RECORD%" => $hide_no_record,
			"%HIDE_MORE_RECORD%" => $hide_more_record,
			"%LEFT_PANEL%"=>$leftPanel,
			"%FAV_SUPPLIER%"=>$FavSupplierLoopData,
			"%HIDECLASS%" => $hide_class,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%SEARCH_KEYWORD%"=>$searchKeyword
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$FavSupplierLoopData,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}

}
?>