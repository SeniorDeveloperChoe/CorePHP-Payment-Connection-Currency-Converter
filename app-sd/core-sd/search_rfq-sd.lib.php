<?php
class SearchRFQ extends Home {
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
		$mark_urgent = $subCategory = '';
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$sortBy = '';
		$sub_cat_data=$this->db->pdoQuery("SELECT id,$subcatName FROM tbl_product_subcategory as s WHERE isActive=? ORDER BY s.subcatName_".$this->curr_language." ASC",array('y'))->results();
		foreach ($sub_cat_data as $key => $value) {
			$selected="";				
				if(!empty($_GET['filter_by_sub_cat_id']) && $_GET['filter_by_sub_cat_id']==$value['id'])	{
					$selected="selected";
			}
			$subCategory.="<option value='".$value['id']."' ".$selected.">".$value['subCatName']."</option>";
		}

		$where = " WHERE b.isActive='y' and b.payment_status = 'y' AND cp.user_deleted='n' AND cp.isActive='y'";

		if(!empty($_SESSION['userId'])){
			$where .= " AND b.user_id!=".$_SESSION['userId'];
		}

		if(!empty($SearchArray['keyword']) || !empty($_GET['keyword'])){
			if(!empty($SearchArray['keyword'])){
				$keyword = $SearchArray['keyword'];
			} 
			else if(!empty($_GET['keyword'])){
				$keyword = $_GET['keyword'];
			}
			$where .= ' and b.request_title LIKE "%'.$keyword.'%"';
		}
		if(!empty($SearchArray['cat_id']) || !empty($_GET['cat_id'])){
			if(!empty($SearchArray['cat_id'])){
				$cat_id = $SearchArray['cat_id'];
			} else if(!empty($_GET['cat_id'])){
				$cat_id = $_GET['cat_id'];
			}
		 	$where .= ' and c.id = '.$cat_id.'';
		} if(!empty($SearchArray['mark_urgent']) || !empty($_GET['mark_urgent'])){
			if(!empty($SearchArray['mark_urgent'])){
				$mark_urgent = $SearchArray['mark_urgent'];
			} else if(!empty($_GET['mark_urgent'])){
				$mark_urgent = $_GET['mark_urgent'];
			}			
			if($mark_urgent == 'y'){
				$where .= ' and b.urgent_date > NOW() ';
			}
		}

		if(!empty($SearchArray['filter_by_sub_cat_id']) || !empty($_GET['filter_by_sub_cat_id'])){
			if(!empty($SearchArray['filter_by_sub_cat_id'])){
				$filter_by_sub_cat_id = $SearchArray['filter_by_sub_cat_id'];
			} else if(!empty($_GET['filter_by_sub_cat_id'])){
				$filter_by_sub_cat_id = $_GET['filter_by_sub_cat_id'];
			}
		$where .= ' and b.subcat_id = '.$filter_by_sub_cat_id;
		}
		
		//echo $where;exit;
		 if(!empty($SearchArray['required_quantity']) || !empty($_GET['required_quantity'])){
			if(!empty($SearchArray['required_quantity'])){
				$slider_supplier = $SearchArray['required_quantity'];
			} else if(!empty($_GET['required_quantity'])){
				$slider_supplier = $_GET['required_quantity'];
			}
			if (strpos($slider_supplier, '$') !== false) {
				$min_val = explode('$',explode('-',$slider_supplier)[0])[1];
				$max_val = explode('$',explode('-',$slider_supplier)[1])[1];
			}else{
				$min_val = explode('-',$slider_supplier)[0];
				$max_val = explode('-',$slider_supplier)[1];
			}
			$where .= ' and b.required_quantity BETWEEN '.$min_val.' AND '.$max_val.'';	
		}
		
		
		if((empty($SearchArray['last_date_sort']) || empty($_GET['last_date_sort']) ) AND (empty($SearchArray['posted_date_sort']) || empty($_GET['posted_date_sort']))){
			$sortBy =" b.urgent_date DESC ";
		}
		if(!empty($SearchArray['last_date_sort']) || !empty($_GET['last_date_sort'])){
			if(!empty($SearchArray['last_date_sort'])){
				$last_date_sort = $SearchArray['last_date_sort'];
			} else if(!empty($_GET['last_date_sort'])){
				$last_date_sort = $_GET['last_date_sort'];
			}

			$sortBy .= ' b.last_date_of_quote '.$last_date_sort;
		}

		if(!empty($SearchArray['posted_date_sort']) || !empty($_GET['posted_date_sort'])){
			if(!empty($SearchArray['posted_date_sort'])){
				$posted_date_sort = $SearchArray['posted_date_sort'];
			} else if(!empty($_GET['posted_date_sort'])){
				$posted_date_sort = $_GET['posted_date_sort'];
			}
			$sortBy .= ' b.created_date '.$posted_date_sort;
		}
		
		$GetRFQData=$this->db->pdoQuery("SELECT b.*,cp.id AS user_id,CONCAT(cp.first_name,' ',cp.last_name) AS user_name,cp.profile_img,cp.user_location,$catName,$subcatName
											FROM tbl_buying_request AS b
											LEFT JOIN tbl_users AS cp ON cp.id=b.user_id
											INNER JOIN tbl_product_category AS c ON c.id=b.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id". $where." ORDER BY ".$sortBy."
											LIMIT $starLimit, $endLimit")->results();
		$SearchResultRows=$this->db->pdoQuery("SELECT b.*,cp.id AS user_id,CONCAT(cp.first_name,' ',cp.last_name) AS user_name,cp.profile_img,$catName,$subcatName
											FROM tbl_buying_request AS b
											LEFT JOIN tbl_users AS cp ON cp.id=b.user_id
											INNER JOIN tbl_product_category AS c ON c.id=b.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id". $where."
											")->affectedrows();
		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }
		$RFQ_loop_data="";
		if(empty($GetRFQData)){
			$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/no_records-sd.skd"))->compile();
			$RFQ_loop_data = $loop_content;
		}else{
			$getProductImg =$getAttechmentsImg ='';
			//echo "<pre>";print_r($GetRFQData);exit;
			foreach ($GetRFQData as $key => $value) {
				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/RFQ_loop_data-sd.skd"))->compile();
				$getAttechmentsImg=explode(',',$value['images']);
				$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$getAttechmentsImg[0], PATHINFO_EXTENSION); 
				if($ext=="pdf") { 
						$getAttechmentsImg = /*getImage(DIR_UPD.'pdf.png',"153","148");*/SITE_UPD."no_prodcut_img.png"; 
				}
				else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$getAttechmentsImg = /*getImage(DIR_UPD.'doc.png',"153","148");*/ SITE_UPD."no_prodcut_img.png";
				}
				else {

					$getProductImgArr = explode(',', $value['images']);
					$getAttechmentsImg=!empty($getProductImgArr['0'])?SITE_BUYING_REQUEST_IMG.$getProductImgArr['0']:SITE_UPD."no_prodcut_img.png";
				}
				if ($key == 4) {
				
				}
					
				$getProductImg=!empty($value['profile_img'])?SITE_UPD.'users-sd/'.$value['user_id'].'/'.$value['profile_img']:SITE_UPD."no_prodcut_img.png";
				$chkFavId ='';
				$favCalss="fa fa-heart-o";
				$span_class = '';
				if(!empty($_SESSION['userId'])){					
					$chkFavId=$this->db->select("tbl_favorite_product",array("id"),array("item_id"=>$value['id'],"user_id"=>$_SESSION['userId'],"item_type"=>'b'))->result();
					if(empty($chkFavId)){
						$favCalss="fa fa-heart-o";
					}else{
						$favCalss="fa fa-heart";
						$span_class="class='liked'";
					}
				}
				$checkIsUrgentRequest = checkIsUrgentRequest($value['urgent_date']);
				$noQuoteLeft = $value['total_quotes'];

				$loop_fields = array(
									"%RFQ_TITLE%"=>$value['request_title'],
									"%ID%"=>$value['id'],
									"%CHECK_FAV%"=>$favCalss,
									"%SPAN_CLASS%"=>$span_class,
									"%REQUEST_SLUG%" => $value['request_slug'],
									"%RFQ_IMG%" => $getAttechmentsImg,
									"%LOGO%" => $getProductImg,
									"%CATNAME%" => $value['catName'],
									"%SUB_CATNAME%" => $value['subCatName'],
									"%REQ_DESCRIPTION%" => $value['req_description'],
									"%REQUIRED_QUANTITY%" => $value['required_quantity'],
									"%NO_OF_BUYERS%" => $value['no_of_buyers'],
									"%BUYER_LOCATION%" => !empty($value['user_location']) ? $value['user_location'] : "-",
									"%COMPANY_NAME%" => $value['user_name'],
									"%URGENT_CLASS%" => $checkIsUrgentRequest == 'true' ? '' : 'hide',
									"%CREATED_DATE%"=>date('dS F Y', strtotime($value['created_date'])),
									"%LAST_DATE_OF_SUBMISSION%" =>date('dS F Y', strtotime($value['last_date_of_quote'])),
									"%NO_QUOTE_LEFT%" => $noQuoteLeft
									);
				$RFQ_loop_data .= str_replace(array_keys($loop_fields), array_values($loop_fields), $loop_content);
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
		if(!empty($SearchArray['mark_urgent'])){
				$last_mark = $SearchArray['mark_urgent'];
			} else if(!empty($_GET['mark_urgent'])){
				$last_mark = $_GET['mark_urgent'];
			}	
		$min_val_show = $max_val_show = '';
		if(!empty($SearchArray['required_quantity']) || !empty($_GET['required_quantity'])){
			if(!empty($SearchArray['required_quantity'])){
				$slider_supplier = $SearchArray['required_quantity'];
			} else if(!empty($_GET['required_quantity'])){
				$slider_supplier = $_GET['required_quantity'];
			}				
			if (strpos($slider_supplier, '$') !== false) {
				$min_val_show = explode('$',explode('-',$slider_supplier)[0])[1];
				$max_val_show = explode('$',explode('-',$slider_supplier)[1])[1];
			}else{
				$min_val_show = explode('-',$slider_supplier)[0];
				$max_val_show = explode('-',$slider_supplier)[1];
			}
		}
		$total_max_val_show = '';
		$total_max_val_show=$this->db->pdoQuery("SELECT max(required_quantity) AS total FROM tbl_buying_request")->result();
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
					 "%RFQ_LOOP_DATA%"=>$RFQ_loop_data,
					 "%MARK_URGENT%"=>$last_mark == 'y' ? 'checked':'',
					 "%TOTAL_PAGES%" => $totalPages,
					 "%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMITS,
					 "%CURRENT_PAGE%" => $page,
					 "%HIDECLASS%" => $hide_class,
					 "%KEYWORD%"=>$keyword,
					 "%MIN_VAL_SHOW%"=>$min_val_show,
					 "%MAX_VAL_SHOW%"=>$max_val_show,
					 "%TOTAL_VAL_SHOW%"=>$total_max_val_show['total'],
					 "%SUB_CATEGORIES%"=>$subCategory,

					);
		$html = str_replace(array_keys($fields), array_values($fields), $html);

		$data=array("main_content"=>$html,"products"=>$RFQ_loop_data,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}
	public function getTotalComapreProduct(){	
			$getTotalComapreProduct=$this->db->pdoQuery("SELECT count(id) AS totalCompareProduct FROM tbl_product_compare WHERE ipAddress = ?",array(get_ip_address()))->result();
			return $getTotalComapreProduct['totalCompareProduct'];
	}
}
?>