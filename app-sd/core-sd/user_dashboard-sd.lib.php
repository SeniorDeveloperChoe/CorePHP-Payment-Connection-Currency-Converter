<?php
class Content extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();
		$manage_quote_count= $accepted_Quote_count=$rejected_quote_count ='';
		$userData=$this->db->pdoQuery("SELECT * FROM tbl_users WHERE id=?",array($_SESSION['userId']))->result();
		$Msg_data=$this->db->pdoQuery("SELECT count(m.id) AS msg_unread, m.sender FROM tbl_messages as m INNER JOIN tbl_users as u ON u.id = m.sender WHERE m.receiver=? AND m.is_read = ?",array($_SESSION['userId'],'n'))->result();

		$accepted_Quote_count=$this->db->pdoQuery("SELECT count(id) AS accepted_Quote_count FROM tbl_quotes WHERE user_id=? AND status = ?",array($_SESSION['userId'],'a'))->result();
		$pending_Quote_count=$this->db->pdoQuery("SELECT count(id) AS pending_Quote_count FROM tbl_quotes WHERE user_id=? AND status = ?",array($_SESSION['userId'],'p'))->result();
		$rejected_quote_count=$this->db->pdoQuery("SELECT count(id) AS rejected_quote_count FROM tbl_quotes WHERE user_id=? AND status = ?",array($_SESSION['userId'],'r'))->result();

		$pending_rfq=$this->db->pdoQuery("SELECT count(t1.id) AS bid
										FROM tbl_buying_request t1
										LEFT JOIN tbl_quotes t2 ON t2.buying_request_id = t1.id
										WHERE t1.user_id=? AND t2.buying_request_id IS NULL",array($_SESSION['userId']))->result();

		$my_orders=$this->db->pdoQuery("SELECT count(id) AS my_orders FROM tbl_manage_orders WHERE buyer_id=?",array($_SESSION['userId']))->result();

		$userImage=!empty($userData['profile_img'])?SITE_UPD.'users-sd/'.$_SESSION['userId'].'/'.$userData['profile_img']:SITE_UPD.'no_user_image.png';
		$userFirstName=!empty($userData['first_name'])?$userData['first_name']:" ";
		$userLastName=!empty($userData['last_name'])?$userData['last_name']:" ";
		$userName=$userFirstName.' '.$userLastName;

		$recently_view_products = $getRecetlyViewBuyingRequest = '';
		if($this->sessUserType == 1 || $this->sessUserType == 3) {
			$recently_view_products = $this->getRecetly_view_product();
		}
		if($this->sessUserType == 2 || $this->sessUserType == 3 ) {
			$getRecetlyViewBuyingRequest = $this->getRecetlyViewBuyingRequest();
		}
		

		if(empty($recently_view_products)){
			$recently_view_products='<div class="no-msg">
                         <img src="'.SITE_IMG.'icons/alert.png" alt="">
                         <h2>'.OPPS.'</h2>
                         <p>'.NO_PRODUCTS_FOUND_IN_YOUR_LIST.'</p>
                        </div>';
		}else{
			$recently_view_products='<ul id="similarPro5" class="owl-carousel owl-theme related-pro">
                          					'.$recently_view_products.'
                        			</ul>';
		}

		if(empty($getRecetlyViewBuyingRequest)){
			$getRecetlyViewBuyingRequest='<div class="no-msg">
                         <img src="'.SITE_IMG.'icons/alert.png" alt="">
                         <h2>'.OPPS.'</h2>
                         <p>'.NO_PRODUCTS_FOUND_IN_YOUR_LIST.'</p>
                        </div>';
		}else{
			$getRecetlyViewBuyingRequest='<ul id="similarPro5" class="owl-carousel owl-theme related-pro">
                          					'.$getRecetlyViewBuyingRequest.'
                        			</ul>';
		}


		$all_data='';
		if($userData['user_type'] == 1 || $userData['user_type'] == 3){
              $all_data .= '<li>
                              <div class="media">
                                 <div class="media-left">
                                    <span><i class="icon-bubbles icons"></i></span>
                                 </div>
                                 <div class="media-body">
                                    <a href="'.SITE_URL.'buying-request" target="" title="'.PENDING_RFQ.'">
                                      <h2>'.$pending_rfq['bid'].'</h2>
                                      <p>'.PENDING_RFQ.'</p>
                                   </a>
                                 </div>
                              </div>
                           </li>
                           <li>
                              <div class="media">
                                 <div class="media-left">
                                    <span><i class="icon-layers icons"></i></span>
                                 </div>
                                 <div class="media-body">
                                    <a href="'.SITE_URL.'buyer-manage-order" target="" title="'.TOTAL_ORDERS.'">
                                      <h2>'.$my_orders['my_orders'].'</h2>
                                      <p>'.TOTAL_ORDERS.'</p>
                                   </a>
                                 </div>
                              </div>
                           </li>    ';
		}	
		
		
		$user_member_tag = '';
		$upgrade_link = '';
		
		if($userData['user_type'] == 2 || $userData['user_type'] == 3){
			$upgrade_link = '<a href="'.SITE_URL.'membership-plan">'.UPGRADE_MEMBERSHIP_LBL.'</a>';
			
			if($userData['plan_id'] != 4 && $userData['plan_id'] != 0 && $userData['is_expired'] == 'n'){
				$user_member_tag = '<div class="badge">'.PAID_MEMBER_LBL.'</div>';
			} else {
				$user_member_tag = '<div class="badge">'.FREE_MEMBER_LBL.'</div>';
			}
			$orderReceived = $this->db->pdoQuery("SELECT count(id) AS totalReceivedOrders FROM tbl_manage_orders WHERE supplier_id = ? ",array($_SESSION['userId']))->result();

           $all_data .= '<li>
                              <div class="media">
                                 <div class="media-left">
                                    <span><i class="icon-speech icons"></i></span>
                                 </div>
                                 <div class="media-body">
                                    <a href="'.SITE_URL.'manage-quote" target="" title="'.ACCEPTED_REQUESTS.'">
                                      <h2>'.$accepted_Quote_count['accepted_Quote_count'].'</h2>
                                      <p>'.ACCEPTED_REQUESTS.'</p>
                                   </a>
                                 </div>
                              </div>
                           </li>
                           <li>
                              <div class="media">
                                 <div class="media-left">
                                    <span><i class="icon-user-unfollow icons"></i></span>
                                 </div>
                                 <div class="media-body">
                                    <a href="'.SITE_URL.'manage-quote" target="" title="'.REJECTED_REQUESTS.'">
                                      <h2>'.$rejected_quote_count['rejected_quote_count'].'</h2>
                                      <p>'.REJECTED_REQUESTS.'</p>
                                   </a>
                                 </div>
                              </div>
                           </li> 
                           <li>
	                        <div class="media">
	                           <div class="media-left">
	                              <span><i class="icon-bubble icons"></i></span>
	                           </div>
	                           <div class="media-body">
	                              <a href="'.SITE_URL.'supplier-manage-order" target="" title="'.ORDER_RECEIVED.'">
	                                 <h2>'.$orderReceived['totalReceivedOrders'].' </h2>
	                                 <p>'.ORDER_RECEIVED.'</p>
	                              </a>
	                           </div>
	                        </div>
	                      </li>
                        ';
		}
		$posted_rfq = '';
		if($userData['user_type'] == 1 || $userData['user_type'] == 3){
			$posted_rfq = '<li>
		      <a href="'.SITE_URL.'buying-request">
		      <span><i class="icon-bubbles icons"></i></span> '.POSTED_RFQ.'
		      </a>
		   </li>';
		}else{
			$posted_rfq = '';
		}
		
		$userType = '';
		if ($userData['user_type'] == 1) {
			$userType = BUYER;
		} else if ($userData['user_type'] == 3) {
			$userType = OPT_BOTH;
		} else {
			$userType = SUPPLIERS;
		}
		
		$fields = array(
			"%UNREAD_MESSAGE_COUNT%"=>$Msg_data['msg_unread'],
			"%USER_MEMBER_UPGRADE_LINK%" => $upgrade_link,
			"%MANAGE_QUOTE_COUNT%"=>!empty($pending_Quote_count['pending_Quote_count'])?$pending_Quote_count['pending_Quote_count']:0,
			"%TYPE_BUYER_RFQ%"=>$posted_rfq,
			"%TYPE_DIIFF_URL%"=>($userData['user_type'] == 1)?'buying-request':'manage-quote',
			"%ALL_DATA%"=>$all_data,
			"%LEFT_PANEL%"=>$leftPanel,
			"%USER_NAME%"=>$userName,
			"%USER_IMAGE%"=>$userImage,
			"%GET_RECETLY_VIEW_PRODUCT%"=>$recently_view_products,
			"%GET_RECETLY_VIEW_BUYING_REQ%"=>$getRecetlyViewBuyingRequest,

			"%ABOUT_ME%"=>!empty($userData['description'])?$userData['description']:"No Description Found.",
			"%MEMBER_SINCE%"=> date("M, Y",strtotime($userData['created_date'])),
			"%USER_MEMBER_TAG%" => $user_member_tag,
			"%USER_TYPE%" => $userType,

		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function getRecetly_view_product() {
		$all_subcat_final_content = $price = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/recently_viewed-sd.skd"))->compile();
		$prodata=$this->db->pdoQuery("SELECT * FROM tbl_recently_view_products WHERE user_id=? AND type= ? GROUP BY product_id ORDER BY created_date DESC LIMIT 10",array($_SESSION['userId'], 'p'))->results();
		foreach ($prodata as $key => $value) {
			$proDetail=$this->db->pdoQuery("SELECT mark_premium,isNegotiable,max_price,min_price,product_image,product_title,product_slug FROM tbl_products WHERE id=?",array($value['product_id']))->result();
			$getProductImg=explode(',',$proDetail['product_image']);
			$getProductImg=!empty($getProductImg[0])?SITE_PRODUCT_IMG.$getProductImg[0]:SITE_UPD."no_prodcut_img.png";
			$price = ($proDetail["isNegotiable"] == 'y') ?$proDetail['min_price'].'-'.$proDetail['max_price'].' '.CURRENCY_CODE : $proDetail['min_price'].' '.CURRENCY_CODE;
			$filds = array(
				"%PRODUCT_NAME%" =>$proDetail['product_title'],
				"%PRODUCT_SLUG%" =>$proDetail['product_slug'],
				"%GETPRODUCTIMG%" =>$getProductImg,
				"%MARK_PREMIUM%" => $proDetail['mark_premium'] == 'y' ? '<label class="label label-success">
     '.PREMIUM.'</label>' : '',
				"%PRICE%" =>$price
			);
			$all_subcat_final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
		}
		return $all_subcat_final_content;
	}

	public function getRecetlyViewBuyingRequest() {
		$all_subcat_final_content = $price = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/recently_viewed_request-sd.skd"))->compile();
		$prodata=$this->db->pdoQuery("SELECT * FROM tbl_recently_view_products WHERE user_id=? AND type= ? GROUP BY product_id ORDER BY created_date DESC LIMIT 10",array($_SESSION['userId'], 'br'))->results();
		foreach ($prodata as $key => $value) {
			$reqDetail=$this->db->pdoQuery("SELECT * FROM tbl_buying_request WHERE id=?",array($value['product_id']))->result();
			$getReqImg=explode(',',$reqDetail['images']);
			$getReqImg=!empty($getReqImg[0])?SITE_BUYING_REQUEST_IMG.$getReqImg[0]:SITE_UPD."no_prodcut_img.png";
			$filds = array(
				"%REQUEST_NAME%" =>$reqDetail['request_title'],
				"%REQUEST_SLUG%" =>$reqDetail['request_slug'],
				"%GETREQUESTIMG%" =>$getReqImg,
			);
			$all_subcat_final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
		}
		return $all_subcat_final_content;
	}
}
?>