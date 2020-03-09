<?php
class ProductDetail extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		if(!empty($_SESSION['moderatorId'])){
			$moderatorActivityArray=array("activity"=>"View","page"=>"manage_product-sd","moderator_id"=>$_SESSION['moderatorId'],"entity_id"=>$this->id,"entity_action"=>"View Product");
			add_moderator_activity($moderatorActivityArray); 			
		}
		$hide_buttons="";
		if(!empty($_SESSION['user_type']) && $_SESSION['user_type']=="2"){
			$hide_buttons="hide";
		}

		$maincatName="m.maincatName_".$this->curr_language." AS maincatName";
		$catName="c.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";

		$getProductData=$this->db->pdoQuery("SELECT p.*,co.company_slug,c.id As catId,$maincatName,$catName,$subcatName,s.id as SubCatId,$unitName,co.company_name,co.logo,co.location,co.business_type_id,co.verified,co.company_slug, co.id as compId
											FROM tbl_products AS p
											INNER JOIN tbl_product_main_category AS m ON m.id=p.main_cat_id
											INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
											INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
											INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
											LEFT JOIN tbl_company AS co ON co.user_id=p.user_id
											WHERE p.id = ?
											",array($this->id))->result();	


		/*-----Get Comapre Products*/
			$htmlProductComapre = (new MainTemplater(DIR_TMPL . "$this->module/compare_product-sd.skd"))->compile();	
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
			$getComapreProductId=$this->db->pdoQuery("SELECT id FROM tbl_product_compare WHERE ipAddress=? AND product_id=?",array(get_ip_address(),$this->id))->result();	

		/*-----End Get Comapre Products Saction*/


		/*-----Add No. of visits On Product-----*/
			$objPost = new stdClass();
			$objPost->product_id=!empty($this->id)?$this->id:0;
			$objPost->user_id=!empty($_SESSION['userId'])?$_SESSION['userId']:0;
			$objPost->ipAddress=get_ip_address();
			$objPost->created_date=date("Y-m-d H:i:s");

			$chkIpAddress=$this->db->select("tbl_product_views",array("id"),array("ipAddress"=>$objPost->ipAddress))->result();

			if(empty($chkIpAddress)){
				$objPostArray=(array)$objPost;
				$this->db->insert("tbl_product_views",$objPostArray);
			}

		/*-----End Add No. of visits On Product Saction-----*/



		/*-----Get Product Images and Video-----*/

		$getProductImg=explode(',',$getProductData['product_image']);
		$productImageLarge=$productImageSmall="";
		$lastKey=0;
		foreach ($getProductImg as $key => $value) {
			$active="";
			if($key<=0){
				$active="active";
			}
			$ImgPath=!empty($value)?SITE_PRODUCT_IMG.$value:SITE_UPD."no_prodcut_img.png";
			$productImageLarge.='<div role="tabpanel" class="tab-pane '.$active.'" id="prod-'.$key.'">
			                      <img src="'.$ImgPath.'" data-origin="'.$ImgPath.'" alt="">
			                    </div>';
            $productImageSmall.='<li role="presentation" class="'.$active.'" id="item'.$key.'">
			                      <a href="#prod-'.$key.'" aria-controls="prod-'.$key.'" role="tab" data-toggle="tab"><img src="'.$ImgPath.'" alt=""></a>
			                    </li>';
			$lastKey=$key;
		}

		$videoLarge=$videoSmall="";

		if(!empty($getProductData['embeded_url'])){
			$lastKey=$lastKey+1;
			$videoLarge='<div role="tabpanel" class="tab-pane" id="prod-'.$lastKey.'">
                      <iframe src="'.$getProductData['embeded_url'].'" frameborder="0" allow="autoplay; encrypted-media"  allowfullscreen></iframe>
                    </div>';
            $videoSmall='<li role="presentation" id="item'.$lastKey.'">
	                      	<a href="#prod-'.$lastKey.'" aria-controls="prod-'.$lastKey.'" role="tab" data-toggle="tab" title="'.PRODUCT_VIDEO.'">
	                        <span>
	                          <i class="fa fa-play" aria-hidden="true"></i>
	                        </span>
	                      </a>
	                    </li>';
		}

		/*-----End Get Product Images and Video Saction-----*/

		$fob=($getProductData['fob']=='y')?"Yes":"No";

		/*-----Get Tags-----*/
		$tags="";
		if(!empty($getProductData['product_tags'])){
				$tag=explode(',',$getProductData['product_tags']);
				$tags.='<div class="pro-tags">';
				foreach ($tag as $key => $tagValue) {
						if (!empty($tagValue)) {
							$tags.='<label class="label label-default" id="tag'.$key.'">'.$tagValue.' <a href="javascript:;"><span></span></a></label>';
						}
				}
				$tags.='</div>';	
		}
		 /*-----End Get Tag saction-----*/

		
		/*-----Get Business Type-----*/
		$businessIds=explode(',', $getProductData['business_type_id']);
		$lastElement=end($businessIds);
		$businessType="";
			$businessTypee="business_type_".$this->curr_language;
		foreach ($businessIds as $key => $value) {
			$getBusinessType=$this->db->select("tbl_business_type",array($businessTypee),array("id"=>$value))->result();

			if($value!=$lastElement){
				$businessType .= $getBusinessType[$businessTypee].', ';
			}else{
				$businessType .= $getBusinessType[$businessTypee];
			}

		}
		/*-----End Get Business Type Saction-----*/

			$FavSpanClass="";
			$favCalss="";
			$chkFavId="";
		if(!empty($_SESSION['userId'])){
			$chkFavId=$this->db->select("tbl_favorite_product",array("id"),array("item_id"=>$getProductData['id'],"item_type"=>"p","user_id"=>$_SESSION['userId']))->result();
			if(empty($chkFavId)){
				$favCalss="fa fa-heart-o";
			}else{
				$favCalss="fa fa-heart";
				$FavSpanClass="liked";
			}


		}
		
		$verifiedSupplier="";
		if($getProductData['verified']=="y"){
			$verifiedSupplier='<span class="premium-class verified-class ">
                                      Verified
                                    </span>';
		}


		/*-----Get Dynamic Questions-----*/

			$defaultLan = $this->db->pdoQuery("SELECT id FROM tbl_language WHERE default_lan='y'")->result();
	        $field="dff.field_label_".$this->curr_language;
	        $fetchLabel="field_label_".$this->curr_language;
	        $getQuestionAnswer = $this->db->pdoQuery("SELECT dfr.*,".$field." FROM tbl_dynamic_form_fields_response AS dfr
	                                                    INNER JOIN tbl_dynamic_form_fields AS dff ON dff.id=dfr.question_id
	                                                    WHERE dfr.request_id=?",array($getProductData['id']))->results();
	        $QuesAns="";
	        if(!empty($getQuestionAnswer)){
	        	foreach ($getQuestionAnswer as $key => $value) {

	        			$getAnswers=explode('|', $value['answers']);
	        			$answers="";
	        			if(!empty($getAnswers[1])){
	        				$lastAns=end($getAnswers);
	        				foreach ($getAnswers as $key => $values) {
	        					if($values==$lastAns){
	        						$answers.=$values;
	        					}else{
	        						$answers.=$values.',';
	        					}
	        				}
	        			}else{
	        				$answers=$value['answers'];
	        			}


				        $QuesAns.='<div class="media">
				                      <div class="pull-left">
				                        <label>'.$value[''.$fetchLabel.''].' : </label>
				                      </div>
				                      <div class="media-body">
				                        <span>'.$answers.'</span>
				                      </div>
				                    </div>';
	        	}
	        	

	        }

        /*-----End Get Dynamic Questions Section-----*/


        /*-----Get Similar Products-----*/

			$getSimilarProducts=$this->db->pdoQuery("SELECT p.*,$maincatName,$catName,$subcatName,s.id as SubCatId,$unitName
												FROM tbl_products AS p
												INNER JOIN tbl_product_main_category AS m ON m.id=p.main_cat_id
												INNER JOIN tbl_product_category AS c ON c.id=p.cat_id 
												INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
												INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
												WHERE p.cat_id = ? AND p.id!=?
												",array($getProductData['cat_id'],$this->id))->results();	
			$SimilarProducts="";
			foreach ($getSimilarProducts as $key => $SimilarProductsValues) {
				$SimiliarProducthtml = (new MainTemplater(DIR_TMPL . "$this->module/similar_products-sd.skd"))->compile();	
				
				$SimilarProductImage=explode(',', $SimilarProductsValues['product_image']);

				$SimiliarProductfields = array(
					"%SIMILAR_PRODUCT_IMG%"=>!empty($SimilarProductImage[0])?SITE_PRODUCT_IMG.$SimilarProductImage[0]:SITE_UPD."no_prodcut_img.png",
					"%SIMILAR_PRODUCT_TITLE%"=>!empty($SimilarProductsValues['product_title'])?$SimilarProductsValues['product_title']:"",
					"%SIMILAR_CAT_NAME%"=>!empty($SimilarProductsValues['catName'])?$SimilarProductsValues['catName']:"",
					"%SIMILAR_UNIT_NAME%"=>!empty($SimilarProductsValues['unitName'])?$SimilarProductsValues['unitName']:"",
					"%SIMILAR_PRODUCT_PRICE%"=>(!empty($SimilarProductsValues['min_price']) && !empty($SimilarProductsValues['max_price']))? $SimilarProductsValues['min_price'].' - '.$SimilarProductsValues['max_price'].' '.CURRENCY_CODE:"",
					"%SIMILAR_ORDER_QUANTITY%"=>!empty($SimilarProductsValues['order_quantity'])?$SimilarProductsValues['order_quantity']:"",
					"%SIMILAR_PRODUCT_SLUG%"=>!empty($SimilarProductsValues['product_slug'])?$SimilarProductsValues['product_slug']:""
				);

			$SimilarProducts.= str_replace(array_keys($SimiliarProductfields), array_values($SimiliarProductfields), $SimiliarProducthtml);

			}

		/*-----End Get Similar Products Saction-----*/

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



		$TradeAlertButton="";
		if(!empty($_SESSION['userId']) && isset($_SESSION['userId']))	{
			$chkForTradeAlerts=$this->db->select("tbl_tradealert_subscriber",array("id"),array("user_id"=>$_SESSION['userId'],"sub_cat_id"=>$getProductData['SubCatId']))->result();

			if(empty($chkForTradeAlerts)){
					$TradeAlertButton ='<div class="main-content tradealertcontent '.$hide_buttons.'">
	        	    						<button class="btn btn-system btn-block btn-lg TradeAlertSubscribe">'.SUBSCRIBE_TO_TRADE_ALERT.' &nbsp;<i class="fa 	fa-paper-plane"></i></button>	
	        	  						</div>';
				
			}else{
				$TradeAlertButton ='<div class="main-content tradealertcontent '.$hide_buttons.'">
	        	    						<label class="btn btn-system btn-block btn-lg">'.TRADE_ALERT_SUBSCRIBED.' &nbsp;<i class="fa 	fa-paper-plane"></i></label>	
	        	  						</div>';
			}
		}
			$link = $user_link = '';
			$link = SITE_URL."product-detail/".$getProductData['product_slug'];
			$getProductImg=explode(',',$getProductData['product_image']);
			$user_link=!empty($getProductImg[0])?SITE_APP_UPD.'product-sd/'.$getProductImg[0]:SITE_APP_UPD."no_prodcut_img.png";

			$ProductId=base64_encode(base64_encode($getProductData['id']));

			$startOrderLink=$buyNowOrderLink=$SendInquiryLink=$ContactSupplirLink=$AddToCartLink=$AddToCartBtn=$favLink=$favClass="";
			
			if(isset($_SESSION['userId'])>0){
				$chkAddToCart=$this->db->select("tbl_cart",array("id"),array("user_id"=>$_SESSION['userId'],"product_id"=>$getProductData['id']))->result();
			}
			else{
				$chkAddToCart ='';
			}

			if(empty($_SESSION['userId'])){
				$startOrderLink=SITE_URL."login";
				$buyNowOrderLink=SITE_URL."login";
				$SendInquiryLink=SITE_URL."login";
				$ContactSupplirLink=SITE_URL."login";
				$AddToCartLink=SITE_URL."login";
				$AddToCartBtn="";
				$favLink='href='.SITE_URL.'login';
			}else{
				$favClass="addToFavorite";
				$startOrderLink=SITE_URL."product-start-order/".$getProductData['product_slug'];
				$buyNowOrderLink=SITE_URL."product-buy-now/".$getProductData['product_slug'];
				$SendInquiryLink=SITE_URL."send-inquiry/".$getProductData['product_slug'];
				$ContactSupplirLink=SITE_URL."contact-to-supplier/".$getProductData['product_slug'];
				$AddToCartLink=SITE_URL."cart";
			}

			$BuyNow_or_StartOrder_price=$BuyNow_or_StartOrder_btn="";
			if($getProductData['isNegotiable']=='n'){
				$BuyNow_or_StartOrder_price=$getProductData['min_price'].' '.CURRENCY_CODE;
				$BuyNow_or_StartOrder_btn='<a class="btn btn-system '.$hide_buttons.'" href="'.$buyNowOrderLink.'">'.BUY_NOW.'</a>';
			}else{
				$BuyNow_or_StartOrder_price=$getProductData['min_price'].' - '.$getProductData['max_price'].' '.CURRENCY_CODE;
				$BuyNow_or_StartOrder_btn='<a class="btn btn-system '.$hide_buttons.'" href='.$startOrderLink.'>'.START_ORDER.'</a>';
				$AddToCartBtn="";
			}

			if(empty($chkAddToCart)){
				$AddToCartBtn='<a class="btn btn-system addTocart '.$hide_buttons.'"  id="'.$ProductId.'">'.ADD_TO_CART.'</a>';
			}else{
				$AddToCartBtn='<a class="btn btn-system '.$hide_buttons.'" href="'.$AddToCartLink.'">'.GO_TO_CART.'</a>';
			}

			if($getProductData['isNegotiable']=="y"){
				$AddToCartBtn="";
			}

			//var_dump($_SESSION['user_id']);exit;
			$btnStartOrder=$btnsendInquiry=$btnGoToCart=$btnContactSupplier="";
			if(!empty($_SESSION['userId']) || !empty($_SESSION['temp_userid'])){
					$btnStartOrder=$BuyNow_or_StartOrder_btn;
					$btnsendInquiry='<a class="btn btn-default '.$hide_buttons.'" href="'.$SendInquiryLink.'">'.SEND_AN_INQUIRY.'</a>';
					$btnGoToCart=$AddToCartBtn;
					$btnContactSupplier=' <a class="btn btn-system '.$hide_buttons.'" href="'.$ContactSupplirLink.'">'.CONTACT_SUPPLIER.'</a>';

			}
			if( (!empty($_SESSION['userId']) && !empty($getProductData['user_id']) ) && $getProductData['user_id'] == $_SESSION['userId'] ){
				$btnStartOrder=$btnsendInquiry=$btnGoToCart="";
			}
			if ($getProductData['deactive_by_user'] == 'y') {
				$btnStartOrder=$btnsendInquiry=$btnGoToCart=$btnContactSupplier="";
			}

		$Supplier_detail_page_link=SITE_URL.'supplier-detail-page/'.$getProductData['company_slug'];

		$Country_select=$shipping_methods=$all_country=$shipping_amount='';
		if($getProductData['shipping_type'] != 'others'){
				$Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName FROM tbl_country WHERE isActive=?",array('y'))->results();
					foreach ($Country_select_all as $key => $value) {
						$selected="";
						$all_country.=" <option value='".$value['CountryId']."' ".$selected.">".$value['countryName']."</option>";
						$Country_select="<select class='form-control country_id' name='country_id' id='country_id'>
                                            <option selected='selected' disabled='disabled' value=''>---".SELECT_COUNTRY."---</option>                                          
                                           ".$all_country."
                                            </select>";

					}
					if($getProductData['shipping_type'] == 'everywhere'){
						$shipping_amount = $getProductData['shipping_detail'];
					}else{
						$shipping_amount = '0';
					}
					$shipping_methods = '<select class="form-control shipping_method" name="shipping_method" id="shipping_method">
										  <option value="">---'.SELECT_SHIPPING_METHODS.'---</option>
                                          
                                          <option value="standard">'.LBL_STANDARD.'</option>
                                          <option value="express">'.EXPRESS.'</option>
                                          </select>';
			}else{
				$shipping_methods='<select class="form-control shipping_method" name="shipping_method" id="shipping_method_other">
				<option value="">---'.SELECT_SHIPPING_METHODS.'---</option></select>';
				$all_json_same_country =array();
				$obj = (array)json_decode($getProductData['shipping_detail'],true);
				foreach ($obj as $k=>$val){	  
				   $Country_select_all=$this->db->pdoQuery("SELECT CountryId,countryName 
				   											FROM tbl_country WHERE isActive=?",array('y'))->results();
				foreach ($Country_select_all as $key => $value) 
				{				
					if($val['country_id']==$value['CountryId']){
						if(in_array($val['country_id'],$all_json_same_country)){

						}else{
							$all_country.="<option value='".$value['CountryId']."'>".$value['countryName']."</option>" ;
						}
						$Country_select="<select class='form-control country_id' name='country_id' id='country_id_other'>
                                            <option selected='selected' disabled='disabled'>---".SELECT_COUNTRY."---</option>                                          
                                           ".$all_country."
                                            </select>";
                        array_push($all_json_same_country, $val['country_id']);
					}else{
						$Country_select.='';
					}
				}
				}
			}
		$week_month = '';
		if (!empty($getProductData['week_month'])) {
			$week_month = $getProductData['week_month'] == 'week' ? LBL_WEEK : LBL_MONTH;
		}
		$selected_delivery_terms = '-';
		if (!empty($getProductData['delivery_terms'])) {
			$deliveryTermsData = $this->db->pdoQuery("SELECT * FROM tbl_delivery_terms WHERE id in (".$getProductData['delivery_terms'].") ",[])->results();
			$deliveryTermsData = array_column($deliveryTermsData, 'delivery_terms');


			$selected_delivery_terms = !empty($deliveryTermsData) ? implode(',',$deliveryTermsData) : '';

		}
		$responseRates = getCompanyReponseRates($getProductData['compId']);

		$fields = array(
			"%MARK_PREMIUM%" => $getProductData['mark_premium'] == 'y' ? '<span class="premium-class">
         '.PREMIUM.'</span>' : '',
			"%PRODUCT_ID%"=>$ProductId,
			"%PID%"=>$getProductData['id'],
			"%ALL_COUNTRIES%"=>$Country_select,
			"%SHIPPING_METHODS%"=>$shipping_methods,
			"%SHIPPING_AMOUNT%"=>$shipping_amount,
			"%CATID%"=>$getProductData['catId'],
			"%PRODUCTIMAGE_LARGE%" => $productImageLarge,
			"%PRODUCTIMAGE_SMALL%" => $productImageSmall,
			"%VIDEO_LARGE%" => $videoLarge,
			"%VIDEO_SMALL%" => $videoSmall,
			"%MAIN_CAT_NAME%"=>!empty($getProductData['maincatName'])?$getProductData['maincatName']:"",
			"%CAT_NAME%"=>!empty($getProductData['catName'])?$getProductData['catName']:"",
			"%SUB_CAT_NAME%"=>!empty($getProductData['subCatName'])?$getProductData['subCatName']:"",
			"%PRODUCT_TITLE%"=>!empty($getProductData['product_title'])?$getProductData['product_title']:"",
			"%UNIT_NAME%"=>!empty($getProductData['unitName'])?$getProductData['unitName']:"",
			"%ORDER_QUANTITY%"=>!empty($getProductData['order_quantity'])?$getProductData['order_quantity']:"",
			"%SUPPLY_ABILITY%"=>!empty($getProductData['supply_ability'])?$getProductData['supply_ability']:"",
			"%PRODUCT_LOCATION%"=>!empty($getProductData['product_location'])?$getProductData['product_location']:"",
			"%FOB%"=>$selected_delivery_terms,
			"%TAGS%"=>$tags,
			"%COMPANY_NAME%"=>!empty($getProductData['company_name'])?$getProductData['company_name']:"",
			"%LOCATION%"=>!empty($getProductData['location'])?$getProductData['location']:"",
			"%BUSINESS_TYPE%"=>!empty($businessType)?$businessType:"",
			"%COMPANY_LOGO%"=>!empty($getProductData['logo'])?SITE_UPD.'supplier_logo/'.$getProductData['logo']:SITE_UPD.'supplier_logo/no_comapany.PNG',
			"%PRODUCT_PRICE%"=>(!empty($getProductData['min_price']))? $BuyNow_or_StartOrder_price:"",
			"%FAV_CALSS%"=>$favCalss,
			"%FAV_SPAN_CLASS%"=>$FavSpanClass,
			"%PRODUCT_DESCRIPTION%"=>!empty($getProductData['product_description'])?$getProductData['product_description']:"",
			"%DYNAMIC_QUS_ANS%"=>!empty($QuesAns)?$QuesAns:"",
			"%SIMILAR_PRODUCTS%"=>$SimilarProducts,
			"%VERIFIED_SUPPLIER%"=>!empty($verifiedSupplier)?$verifiedSupplier:"",
			"%SLUG%"=>$this->result,
			"%COMPAR_PRODUCTS%"=>$CompareProducts,
			"%COMPARE_CLASS%"=>empty($CompareProducts)?"hide":"",
			"%CHKECKED_COMAPRE_PRODUCT%"=>!empty($getComapreProductId)?"checked":"",
			"%TOTAL_COMPARE_PRODUCT%"=>!empty($getTotalComapreProducts_ipBased)?$getTotalComapreProducts_ipBased:"",
			"%GETTOTALCOMAPREPRODUCTS_IPBASED%"=>$getTotalComapreProducts_ipBased,
			"%TRADEALERT_BUTTON%"=>$TradeAlertButton,
			"%SUB_CAT_ID%"=>$getProductData['SubCatId'],
			"%COMPAREPAGE_LINK%"=>$comparePageLink,
			"%PRODUCT_SLUG%"=>$getProductData['product_slug'],
			"%START_ORDER_LINK%"=>$startOrderLink,
			"%LINK%"=>$link,
			"%USER_LINK%"=>$user_link,
			"%USERNAME%" => $getProductData['product_title'],
			"%BUYNOW_OR_STARTORDER_BTN%"=>$BuyNow_or_StartOrder_btn,
			"%ADD_TO_CART_BTN%"=>$AddToCartBtn,
			"%FAV_LINK%"=>$favLink,
			"%FAV_CLASS%"=>$favClass,
			"%ADD_TO_CART_LINK%"=>$AddToCartLink,
			"%BTN_START_ORDER%"=>$btnStartOrder,
			"%BTN_GO_TO_CART%"=>$btnGoToCart,
			"%BTN_CONTACT_SUPPLIER%"=>$btnContactSupplier,
			"%BTN_SEND_INQUIRY%"=>$btnsendInquiry,
			"%SUPPLIER_DETAIL_PAGE_LINK%"=>$Supplier_detail_page_link,
			"%COMPANY_SLUG%"=>$getProductData['company_slug'],
			"%SIMILARPRODUCTS_HIDE%"=>!empty($SimilarProducts)?"":"hide",
			"%WEEK_MONTH%" => $week_month,
			"%RESPONSE_RATE%" => $responseRates['responseRate'],
			"%RESPONSE_TIME%" => $responseRates['responseTime'],
			"%DELIVERY_TIME%" => !empty($getProductData['estimated_delivery_time'])?$getProductData['estimated_delivery_time']:"",

		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}


	public function getTotalComapreProduct(){	
			$getTotalComapreProduct=$this->db->pdoQuery("SELECT count(id) AS totalCompareProduct FROM tbl_product_compare WHERE ipAddress = ?",array(get_ip_address()))->result();
			return $getTotalComapreProduct['totalCompareProduct'];
	}
}
?>