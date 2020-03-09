<?php
class Quotes extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();

		$searchKeyword="";

		if(!empty($_GET['keywords']) && $_GET['keywords']!="undefined"){
			$searchKeyword=$_GET['keywords'];
			$where = "WHERE q.user_id = ".$_SESSION['userId']." AND b.request_title LIKE '%".$_GET['keywords']."%'";
		}
		if($search_data == 'all' || empty($search_data)){
			if(empty($where)){
				$where = 'WHERE q.user_id = '.$_SESSION['userId'].'';
			}
		}else{
			$where = "WHERE q.user_id = ".$_SESSION['userId']." AND b.request_title LIKE '%".$search_data."%'";
		}


		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="sc.subcatName_".$this->curr_language." AS subcatName";

		$getQuotesData=$this->db->pdoQuery("SELECT q.*,b.request_slug,$catName,$subcatName,CONCAT(u.first_name,' ',u.last_name) AS UserName,u.id AS UID,u.email,u.profile_img,b.request_title AS buying_Request_title,b.images AS images,b.user_id AS BuyerId
											FROM tbl_quotes AS q
											INNER JOIN tbl_buying_request AS b ON b.id = q.buying_request_id
											INNER JOIN tbl_product_category AS c ON c.id = b.cat_id 
											INNER JOIN tbl_product_subcategory AS sc ON sc.id = b.subcat_id 
											INNER JOIN tbl_users AS u ON u.id = b.user_id
											".$where."
											ORDER BY id desc LIMIT $starLimit, $endLimit
											")->results();

		$SearchResultRows=$this->db->pdoQuery("SELECT q.*,b.request_slug,$catName,$subcatName,CONCAT(u.first_name,' ',u.last_name) AS UserName,u.email,u.profile_img,b.request_title AS buying_Request_title,b.images AS images,b.user_id AS BuyerId
											FROM tbl_quotes AS q
											INNER JOIN tbl_buying_request AS b ON b.id = q.buying_request_id
											INNER JOIN tbl_product_category AS c ON c.id = b.cat_id 
											INNER JOIN tbl_product_subcategory AS sc ON sc.id = b.subcat_id 
											INNER JOIN tbl_users AS u ON u.id = b.user_id
											".$where."
											")->affectedrows();


		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }

		$QuotesLoopData="";

		if(empty($getQuotesData)){
			$html = (new MainTemplater(DIR_TMPL.$this->module."/no_quote-sd.skd"))->compile();

		}else{
				foreach ($getQuotesData as $key => $value) {
				$loop_content = (new MainTemplater(DIR_TMPL.$this->module."/quote_loop_data-sd.skd"))->compile();
				$getAttechmentsImg=explode(',',$value['images']);
				$imgFound="";

				$getAttechmentsImg=!empty($getAttechmentsImg['0'])?SITE_BUYING_REQUEST_IMG.$getAttechmentsImg['0']:SITE_UPD."no_prodcut_img.png";

				$category=!empty($value['catName'])?$value['catName']:"";
				$subcategory=!empty($value['subcatName'])?$value['subcatName']:"";
				$requestTitle=!empty($value['buying_Request_title'])?$value['buying_Request_title']:"";
				$request_slug=!empty($value['request_slug'])?$value['request_slug']:"";

				$quentityRequired=!empty($value['quentity'])?$value['quentity']:"";
				$quationAmount=!empty($value['quotation_amount'])?$value['quotation_amount'].' '.CURRENCY_CODE:"";
				$placeDate=!empty($value['created_date'])?date('dS F Y', strtotime($value['created_date'])):"";
				$quoteStatus=$editBtn="";
				$quoteId=base64_encode(base64_encode($value['id']));
				if($value['status']=='a'){
					$quoteStatus=ACCEPTED;
				}else if($value['status']=="r"){
					$quoteStatus=REJECT;
				}else{
					$quoteStatus=PENDING;
					if(!empty($_SESSION['moderatorId'])){
						if(checkModeratorAction($this->module,"edit")){
							$editBtn='<a title="'.EDIT_QUOTE.'" id="'.$quoteId.'" class="editbtnquote" data-toggle="modal" data-target="#editQuotes"><i class="fa fa-pencil"></i></a>';
						}else{
							$editBtn="";
						}
					}else{
						$editBtn='<a title="'.EDIT_QUOTE.'" id="'.$quoteId.'" class="editbtnquote" data-toggle="modal" data-target="#editQuotes"><i class="fa fa-pencil"></i></a>';
					}
					
				}

				$BuyerImg=!empty($value['profile_img'])?SITE_UPD.'users-sd/'.$value['UID'].'/'.$value['profile_img']:SITE_UPD.'no_user_image.png';
				$BuyerName=!empty($value['UserName'])?$value['UserName']:"";
				$BuyerEmail=!empty($value['email'])?$value['email']:"";


				$delete_btn="";
				if(!empty($_SESSION['moderatorId'])){
					if(checkModeratorAction($this->module,"delete")){
						$delete_btn = '<a href="javascript:;" id="'.$quoteId.'" class="removeQuotes" title="'.REMOVE_QUOTE.'"><i class="fa fa-trash"></i></a>';
					}else{
						$delete_btn="";
					}
				}else{
					$delete_btn = '<a href="javascript:;" id="'.$quoteId.'" class="removeQuotes" title="'.REMOVE_QUOTE.'"><i class="fa fa-trash"></i></a>';
				}
				$Quote_loop_fields=array(
						"%ATTECHMENTS_IMG%"=>$getAttechmentsImg,
						"%CATEGORY%"=>$category,
						"%SUBCATEGORY%"=>$subcategory,
						"%REQUEST_TITLE%"=>$requestTitle,
						"%REQUEST_SLUG%"=>$request_slug,
						"%QUENTITY_REQUIRED%"=>$quentityRequired,
						"%QUATION_AMOUNT%"=>$quationAmount,
						"%PLACE_DATE%"=>$placeDate,
						"%QUOTE_STATUS%"=>$quoteStatus,
						"%BUYER_IMG%"=>$BuyerImg,
						"%BUYER_NAME%"=>$BuyerName,
						"%BUYER_EMAIL%"=>$BuyerEmail,
						"%QUOTE_ID%"=>$quoteId,
						"%KEY%"=>$key,
						"%EDIT_BTN%"=>$editBtn,
						"%DELETE_BTN%"=>$delete_btn,
						"%RECEIVER_ID%"=>$value['BuyerId'],
						"%SENDER_ID%"=>$_SESSION['userId'],
					);

				$QuotesLoopData .= str_replace(array_keys($Quote_loop_fields), array_values($Quote_loop_fields), $loop_content);

			}
		}

		


		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMIT);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMIT || $totalRow < PAGE_DISPLAY_LIMIT) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));

		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%QUOTES_LOOP_DATA%"=>$QuotesLoopData,
			"%HIDECLASS%" => $hide_class,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%SEARCH_KEYWORD%"=>$searchKeyword
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$QuotesLoopData,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMIT,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}

}
?>