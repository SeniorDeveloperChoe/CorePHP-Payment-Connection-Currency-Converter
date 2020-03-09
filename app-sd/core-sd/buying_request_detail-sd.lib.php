<?php
class BuyingReqDetail extends Home {
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

		$getBuyingReqData=$this->db->pdoQuery("SELECT b.*,c.id AS catId,s.id As subcatId,u.id AS userId,u.user_location,u.profile_img,CONCAT(u.first_name,' ',u.last_name) AS username,u.gender,u.email_veri_status,u.email,$maincatName,$catName,$subcatName,s.id as SubCatId,$unitName
											FROM tbl_buying_request AS b
											left JOIN tbl_product_main_category AS m ON m.id=b.main_cat_id
											left JOIN tbl_product_category AS c ON c.id=b.cat_id 
											left JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id 
											left JOIN tbl_unit_value AS un ON un.id=b.unit_id
											left JOIN tbl_users AS u ON u.id=b.user_id
											WHERE b.id = ?
											",array($this->id))->result();
		$getProductImg=explode(',',$getBuyingReqData['images']);
		$productImageLarge=$productImageSmall=$attechment =$ImgKey ="";
		$lastKey = 0;
		$verify_email = ($getBuyingReqData['email_veri_status'] == 'n') ? '' : '<span class="verify">Confirmed</span>';
		$checkIsUrgentRequest = checkIsUrgentRequest($getBuyingReqData['urgent_date']);
		$mark_urgent = '';
		if ($checkIsUrgentRequest) {
			$mark_urgent = '<span class="premium-class urgent-class ">Urgent</span>';
		}
		
		$gender = ($getBuyingReqData['gender'] == 'f') ? FEMALE : MALE;
		$count=0;

		$userImage = SITE_UPD.'no_user_image.png';
		if(!empty($getBuyingReqData['profile_img'])){
			$userImage = SITE_UPD.'users-sd/'.$getBuyingReqData['userId'].'/'.$getBuyingReqData['profile_img'];
		}
		foreach ($getProductImg as $key => $value) {
			$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$value, PATHINFO_EXTENSION);
				if($ext=="pdf" || $ext=="doc" || $ext=="docx" || $ext =="odt") { 
					$count =1;
					$actual_nm=explode(',',$getBuyingReqData['actual_attechment']);
					if($ext=="doc" || $ext=="docx" || $ext =="odt"){
						$attechment .='<div class="col-md-6 col-sm-6">
	                                        <div class="media">
	                                            <div class="pull-left">
	                                                <a href="'.get_link('download_file', $this->id.'-'.$key.'-'.base64_encode($value)).'">
	                                                    <div class="attachment-img">
	                                                        <img src="'.SITE_UPD.'doc.png" alt="" />
	                                                    </div>
	                                                </a>
	                                            </div>
	                                            <div class="media-body">
	                                                <a href="'.get_link('download_file', $this->id.'-'.$key.'-'.base64_encode($value)).'">
	                                                    <p>'.$actual_nm[$key].'</p>
	                                                </a>
	                                                <span>'.DOC.'</span>
	                                            </div>
	                                        </div>
	                                    </div>';
					} else{
						$attechment .='<div class="col-md-6 col-sm-6">
	                                        <div class="media">
	                                            <div class="pull-left">
	                                                <a href="'.get_link('download_file', $this->id.'-'.$key.'-'.base64_encode($value)).'">
	                                                    <div class="attachment-img">
	                                                        <img src="'.SITE_UPD.'pdf.png" alt="" />
	                                                    </div>
	                                                </a>
	                                            </div>
	                                            <div class="media-body">
	                                                <a href="'.get_link('download_file', $this->id.'-'.$key.'-'.base64_encode($value)).'">
	                                                    <p>'.$actual_nm[$key].'</p>
	                                                </a>
	                                                <span>'.PDF.'</span>
	                                            </div>
	                                        </div>
	                                    </div>';
					}
				}else{
					$active = '';
					$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$value, PATHINFO_EXTENSION);
					if($ext=="pdf" || $ext=="doc" || $ext=="docx" || $ext =="odt") { 
						if($key<=1){
							$active="active";
						}
					}
					else{						
						if($key<=0){
							$active="active";
						}
					}
					$ImgPath=!empty($value)?SITE_BUYING_REQUEST_IMG.$value:SITE_UPD."no_prodcut_img.png";
					$productImageLarge.='<div role="tabpanel" class="tab-pane '.$active.'" id="prod-'.$key.'">
					                      <img src="'.$ImgPath.'" data-origin="'.$ImgPath.'" alt="">
					                    </div>';
		            $productImageSmall.='<li role="presentation" class="'.$active.'" id="item'.$key.'">
					                      <a href="#prod-'.$key.'" aria-controls="prod-'.$key.'" role="tab" data-toggle="tab"><img src="'.$ImgPath.'" alt=""></a>
					                    </li>';

					$lastKey=$key;
					
				}		
				
			}
	        /*-----Get Similar Products-----*/



			$getRecommeneded_rfq=$this->db->pdoQuery("SELECT b.*,u.user_location,$unitName,$maincatName,$catName,$subcatName
												FROM tbl_buying_request AS b
												left JOIN tbl_product_main_category AS m ON m.id=b.main_cat_id
												left JOIN tbl_product_category AS c ON c.id=b.cat_id 
												left JOIN tbl_product_subcategory AS s ON s.id=b.subcat_id 
												left JOIN tbl_unit_value AS un ON un.id=b.unit_id
												left JOIN tbl_users AS u ON u.id=b.user_id
												WHERE b.cat_id = ? AND b.id != ?
												ORDER BY b.mark_urgent ASC
												",array($getBuyingReqData['cat_id'],$this->id))->results();	
			$Recommeneded_rfq="";
			foreach ($getRecommeneded_rfq as $key => $Recommeneded_rfqValues) {
				$SimiliarProducthtml = (new MainTemplater(DIR_TMPL . "$this->module/Recommeneded_rfq-sd.skd"))->compile();	
				$getProductImg2=explode(',',$Recommeneded_rfqValues['images']);
				$item_image = SITE_UPD."no_prodcut_img.png";
				if(!empty($getProductImg2[0])){
					$item_image = SITE_BUYING_REQUEST_IMG.$getProductImg2[0];
				}
			
				$SimiliarProductfields = array(
					'%IMAGE_PATH%' => $item_image,
					'%MAIN_CAT_NAME%' => $Recommeneded_rfqValues['maincatName'],
					'%CAT_NAME%' => $Recommeneded_rfqValues['catName'],
					'%SUB_CAT_NAME%' => $Recommeneded_rfqValues['subCatName'],
					"%REQ_TITLE%"=>!empty($Recommeneded_rfqValues['request_title'])?$Recommeneded_rfqValues['request_title']:"",
					"%SLUG%"=>!empty($Recommeneded_rfqValues['request_slug'])?$Recommeneded_rfqValues['request_slug']:"",
					"%REQ_QUANTITY%"=>!empty($Recommeneded_rfqValues['required_quantity'])?$Recommeneded_rfqValues['required_quantity']:"",
					"%UNIT_NAME%"=>!empty($Recommeneded_rfqValues['unitName'])?$Recommeneded_rfqValues['unitName']:"",
					"%LOCATION%"=>!empty($Recommeneded_rfqValues['user_location'])?$Recommeneded_rfqValues['user_location']:"",
					"%CREATED_DATE%"=>date('dS F Y', strtotime($Recommeneded_rfqValues['created_date']))
				);

			$Recommeneded_rfq.= str_replace(array_keys($SimiliarProductfields), array_values($SimiliarProductfields), $SimiliarProducthtml);

			}
			if(empty($Recommeneded_rfq)){
				$Recommeneded_rfq="<div class='no-result-found'>".NO_RFQS_FOUND."</div>";
			}
			if (isset($_SESSION['userId']) && $_SESSION['userId'] != $getBuyingReqData['user_id']){
				if(isset($_SESSION) && !empty($_SESSION['userId'])){
					$Similar_rfq = (($_SESSION['user_type']) == '1' || ($_SESSION['user_type']) == '3') ? '':'hide';
				}else{
					$Similar_rfq = 'hide';
				}
				if(isset($_SESSION) && !empty($_SESSION['userId'])){
					$place_quote_allow = (($_SESSION['user_type']) == '2' || ($_SESSION['user_type']) == '3') ? '':'hide';
				}else{
					$place_quote_allow = 'hide';
				}
			} else{
				$Similar_rfq ='hide';
				$place_quote_allow ='hide';
			}
			$link = SITE_URL."buying-request-detail/".$getBuyingReqData['request_slug'];
			$getAttechmentsImg=explode(',',$getBuyingReqData['attachmemnt']);
			$ext = pathinfo(DIR_BUYING_REQUEST_IMG.$getAttechmentsImg[0], PATHINFO_EXTENSION); 
			if($ext=="pdf") { 
				$user_link = SITE_APP_UPD."no_prodcut_img.png";
			}
			else if($ext=="doc" || $ext=="docx" || $ext =="odt") { 
				$user_link = SITE_APP_UPD."no_prodcut_img.png";
			}
			else { 
				$user_link = SITE_APP_UPD."buying_request_attechments-sd/".$getAttechmentsImg[0];
			}	
		/*-----End Get Similar Products Saction-----*/

		$place_quote_form="";
		$quotesForm="";
			if(!empty($_SESSION['moderatorId'])){
				if(checkModeratorAction($this->module,"Add")){
					$place_quote_form = (new MainTemplater(DIR_TMPL . "$this->module/place_quote-sd.skd"))->compile();
					$quotes_fields = array(
						"%BUYING_REQUEST_ID%"=>$getBuyingReqData['id'],
						"%USERID%"=>$getBuyingReqData['userId'],					
						"%PLACE_QUOTE_HIDE%"=>$place_quote_allow
					);

				$quotesForm = str_replace(array_keys($quotes_fields), array_values($quotes_fields), $place_quote_form);	

				}else{
					$place_quote_form= "";
				}
			}else{
			$Product_name = '';
		
				$user_sess_id = !empty($_SESSION['userId']) ? $_SESSION['userId'] : 0;
				
				$quotesCount = $this->db->pdoQuery("SELECT * FROM tbl_buying_request WHERE id = ".$this->id)->result();
				$quotes = $this->db->pdoQuery("SELECT * FROM tbl_quotes WHERE buying_request_id = ".$this->id." AND user_id = ".$user_sess_id)->affectedRows();
				
				$userData = $this->db->pdoQuery("SELECT quote_place_on_buying_request FROM tbl_users WHERE id = ".$user_sess_id)->result();
				$Product_name = '';
				if(!empty($userData['quote_place_on_buying_request']) && $userData['quote_place_on_buying_request'] != 0 && $quotesCount['total_quotes'] >0 && $quotes == 0){
						if(!empty($_SESSION['userId'])){
							$place_quote_form = (new MainTemplater(DIR_TMPL . "$this->module/place_quote-sd.skd"))->compile();
							$show_moderator=getTableValue('tbl_users', 'quote_place', array('id'=>$_SESSION['userId']));

							if($show_moderator=="y"){
								if(isset($_SESSION['userId']) && !empty($getBuyingReqData['catId']) && !empty($getBuyingReqData['subcatId'])){
										$pro_nm=$this->db->pdoQuery("SELECT * FROM tbl_products WHERE isActive=? AND cat_id=? AND sub_cat_id=? AND user_id = ?",array('y',$getBuyingReqData['catId'],$getBuyingReqData['subcatId'],$_SESSION['userId']))->results();
										foreach ($pro_nm as $keys => $value_pro) {
											$Product_name.="<option value='".$value_pro['id']."'>".$value_pro['product_title']."</option>";
									}
								}
								$quotes_fields = array(
									"%BUYING_REQUEST_ID%"=>$getBuyingReqData['id'],
									"%USERID%"=>$getBuyingReqData['userId'],
									"%PLACE_QUOTE_HIDE%"=>$place_quote_allow,
									"%ALL_PRODUCTS%"=>$Product_name
								);

								$quotesForm = str_replace(array_keys($quotes_fields), array_values($quotes_fields), $place_quote_form);	
							}
						}
				}else{
					$place_quote_form= "";
				}
			}
		$hideBuyerEmail = "";
		if (empty($_SESSION['userId'])) {
			$hideBuyerEmail = 'hide';
		}
		

		$fields = array(
			"%PRODUCT_ID%"=>base64_encode(base64_encode($getBuyingReqData['id'])),
			"%BUYING_REQUEST_ID%"=>$getBuyingReqData['id'],
			"%SUBCATID%"=>$getBuyingReqData['subcatId'],
			"%CATID%"=>$getBuyingReqData['catId'],
			"%USERID%"=>$getBuyingReqData['userId'],
			"%PRODUCTIMAGE_LARGE%" => $productImageLarge,
			"%PRODUCTIMAGE_SMALL%" => $productImageSmall,
			"%ATTECHMENT%" => $attechment,
			"%MAIN_CAT_NAME%"=>!empty($getBuyingReqData['maincatName'])?$getBuyingReqData['maincatName']:"",
			"%CAT_NAME%"=>!empty($getBuyingReqData['catName'])?$getBuyingReqData['catName']:"",
			"%SUB_CAT_NAME%"=>!empty($getBuyingReqData['subCatName'])?$getBuyingReqData['subCatName']:"",
			"%REQUEST_TITLE%"=>!empty($getBuyingReqData['request_title'])?$getBuyingReqData['request_title']:"",
			"%UNIT_NAME%"=>!empty($getBuyingReqData['unitName'])?$getBuyingReqData['unitName']:"",
			"%LOCATION%"=>!empty($getBuyingReqData['user_location'])?$getBuyingReqData['user_location']:"",
			"%EMAIL%"=>!empty($getBuyingReqData['email'])?$getBuyingReqData['email']:"",
			"%CREATED_DATE%"=>date('dS F Y', strtotime($getBuyingReqData['created_date'])),
			"%REQUIRED_QUANTITY%"=>$getBuyingReqData['required_quantity'],
			"%VERIFY%"=>$verify_email,
			"%GENDER%"=>$gender,
			"%USERNM%"=>$getBuyingReqData['username'],
			"%USERIMAGE%"=>$userImage,
			"%REQ_DESCRIPTION%"=>$getBuyingReqData['req_description'],
			"%RECOMMENDED_RFQ%"=>$Recommeneded_rfq,
			"%MARK_URGENT%"=>$mark_urgent,
			"%SLUG%"=>$getBuyingReqData['request_slug'],
			"%HIDE_ATTCH%"=>($count == 1) ? '':'hide',
			"%SIMILAR_RFQ_HIDE%"=>$Similar_rfq,
			"%PLACE_QUOTE_HIDE%"=>$place_quote_allow,
			"%LINK%"=>$link,
			"%USER_LINK%"=>$user_link,
			"%USERNAME%" => $getBuyingReqData['request_title'],
			"%NO_OF_BUYERS%" => $getBuyingReqData['no_of_buyers'],
			"%PLACE_QUOTE_FORM%"=>$quotesForm,
			"%HIDE_BUYER_EMAIL%"=>$hideBuyerEmail,
		);
		
		$html = str_replace(array_keys($fields), array_values($fields), $html);	
		return $html;
	}
}
?>