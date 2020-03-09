<?php
	class Home {
		function __construct($module = "") {
			foreach ($GLOBALS as $key => $values) {
				$this->$key = $values;
			}
		}

		public function random_color_part() {
			return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
		}

		public function random_color() {
		    return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
		}

		public function getHeaderContent() {
			$html = (new MainTemplater(DIR_TMPL . "header-sd.skd"))->compile();

			$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$get_path=parse_url($url);
			$exploded_path=explode('/', $get_path['path']);
			$header_search_button_txt="";
			$select_product=$select_supplier=$select_rfq="";
			foreach ($exploded_path as $key => $value) {
				if($value=="search-product"){
					$select_product="selected";
				}elseif($value=="search-supplier"){
					$select_supplier="selected";
				}elseif($value=="search-RFQ"){
					$select_rfq="selected";
				}
			}			

			if(empty($select_product) && empty($select_supplier) && empty($select_rfq)){
				$select_product="selected";
			}


			$temp_name = (!empty($this->sessUserId) ? "header_after_login-sd.skd" : "header_before_login-sd.skd");

			$temp_userid = ($temp_name == "header_before_login-sd.skd") && empty($_SESSION['temp_userid']) ? rand(10,10000) : "";
			if(!empty($temp_userid)){
				$_SESSION['temp_userid'] = $temp_userid;
			}
			$nav = (new MainTemplater(DIR_TMPL . $temp_name))->compile();
			 $count_cart['total_cart'] ='';
			
			/*without login add cart data*/
			if(isset($_SESSION['temp_userid'])){
				$count_cart = $this->db->pdoQuery("SELECT count(id) AS total_cart FROM tbl_cart WHERE tmp_userid = ? AND user_id = 0",array($_SESSION['temp_userid']))->result();
			}

			if(isset($_SESSION['userId']) && $_SESSION['userId'] != 0){
				$count_cart = $this->db->pdoQuery("SELECT count(id) AS total_cart FROM tbl_cart WHERE user_id = ?",array($_SESSION['userId']))->result();
			}
			$user_name=$total_noti=$img_path=$notifications_mesgaes="";
			if(!empty($_SESSION['userId'])){
				$get_user_name=$this->db->pdoQuery("SELECT first_name AS CurrentUser, profile_img FROM tbl_users WHERE id = ?",array($_SESSION['userId']))->result();
				$user_name=$get_user_name['CurrentUser'];

				$get_total_noti=$this->db->pdoQuery("SELECT count(id) AS totalNoti FROM tbl_notification WHERE user_id=? AND is_read=?",array($_SESSION['userId'],'n'))->result();
				$total_noti=$get_total_noti['totalNoti'];
				
				$img_path = SITE_UPD.'no_user_image.png';
				if(!empty($get_user_name['profile_img'])){
					$img_path = SITE_UPD.'users-sd/'.$_SESSION['userId'].'/'.$get_user_name['profile_img'];
				}
				
				$get_total_noti_msg=$this->db->pdoQuery("SELECT * FROM tbl_notification WHERE user_id=? AND is_read=? ORDER BY id DESC LIMIT 5",array($_SESSION['userId'],'n'))->results();
				if(!empty($get_total_noti_msg)){
					foreach ($get_total_noti_msg as $noti_value) {
						$get_color_code=$this->random_color();
						$notifyUrl = !empty($noti_value['notify_url']) ? SITE_URL.$noti_value['notify_url'] : SITE_URL.'notifications';
						$notifications_mesgaes.='<li class="noti-item">
							                      <a href="'.$notifyUrl.'" class="unread" data-id="'.$noti_value['id'].'">
							                        <div class="media">
							                          <div class="media-left">
							                            <div class="noti-img" style="background:#'.$get_color_code.';">
							                              '.$noti_value['message'][0].'
							                            </div>
							                          </div>
							                          <div class="media-body">
							                            <p>'.$noti_value['message'].'</p>
							                            <span class="date">'.date('dS F Y', strtotime($noti_value['created_date'])).'</span>
							                          </div>
							                        </div>
							                      </a>
							                    </li>';
					}
				}else{
					$notifications_mesgaes.='<li class="noti-item">
							                      <a href="javascript:void(0);">
							                        <div class="media">
							                          <div class="media-body">
							                            <p class="text-center theme-color">No Notifications Yet !</p>
							                          </div>
							                        </div>
							                      </a>
							                    </li>';
				}

			}
			$head_search_fiels = array(
				"%SELECT_PRODUCT%"=>$select_product,
				"%SELECT_SUPPLIER%"=>$select_supplier,
				"%SELECT_RFQ%"=>$select_rfq,
				"%CURRENT_USER%"=>$user_name,
				"%TOTAL_NOTI%"=>$total_noti,
				"%IMG_PATH%"=>$img_path,
				"%NOTIFICATIONS_MESGAES%"=>$notifications_mesgaes
			);			

			$nav = str_replace(array_keys($head_search_fiels), array_values($head_search_fiels), $nav);

			$all_hedarer = $this->all_cat_panel();
			$keyword = '';
			$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
			if (strpos($actual_link, 'keyword=') !== false) {
				$parts = parse_url($actual_link);
				parse_str($parts['query'], $query);
				$keyword= $query['keyword'];
			}else{
				$keyword = '';
			}
			$head_lang = array(
				"%HEADER_LANGUAGE%" => $this->getLanguages()
			);
			$nav = str_replace(array_keys($head_lang), array_values($head_lang), $nav);

			$head_fiels = array(
				"%NAV%" => $nav,
				"%KEYWORD%" => $keyword,
				"%COUNT_CART%" => $count_cart['total_cart'],
				"%ALL_CAT_PANEL%" => $all_hedarer['indicator_content'],
				"%DATA_ALL_CAT_PANEL%" => $all_hedarer['slider_content'],
				"%MOBILE_VIEW_CAT_PANEL%" => $this->mobile_cat_panel()
			);

			$html = str_replace(array_keys($head_fiels), array_values($head_fiels), $html);
			return $html;
		}
		
		public function mobile_cat_panel() {
			$get_mobile_view_cat_panel=$get_mobile_view_content = $all_main_cat_mobile ='';
			$get_mobile_view_content = (new MainTemplater(DIR_TMPL."mobile_view_cat_panel-sd.skd"))->compile();
			$all_main_cat_mobile = $this->db->pdoQuery("SELECT mc.*, p.id as p_id FROM tbl_product_main_category as mc 
				JOIN tbl_products as p ON (p.main_cat_id = mc.id)
				WHERE mc.isActive = ? GROUP BY mc.id  LIMIT 10",array('y'))->results();

			if(!empty($all_main_cat_mobile)) {
				foreach ($all_main_cat_mobile as $key => $value) {
					$cat_name = $cat_id ='';
					$all_cat = $this->db->pdoQuery("SELECT pc.* FROM tbl_product_category as pc
					JOIN tbl_products as p ON (p.cat_id = pc.id)
					WHERE pc.isActive = ? AND maincatID =? AND p.main_cat_id =? GROUP BY pc.id order by categoryName_".$this->curr_language."",array('y',$value['id'],$value['id']))->results();
					foreach ($all_cat as $keys => $val_cat_nm) {
						$all_subcat = $this->db->pdoQuery("SELECT psc.* FROM tbl_product_subcategory as psc 
							JOIN tbl_products as p ON (p.sub_cat_id = psc.id)

							WHERE psc.isActive = 'y' AND catId =".$val_cat_nm['id']." GROUP BY psc.id ORDER BY subcatName_".$this->curr_language."")->results();
						//dd($all_subcat);
						$inner_loop_subcat=$inner_subcat_id='';
						foreach ($all_subcat as $key_subcat => $value_subcat) {	  
							$inner_loop_subcat .= '<li>
		                                             <a href="'.SITE_URL.'search-product/?cat_id='.$val_cat_nm['id'].'&subcat_id='.$value_subcat['id'].'" target="">'.$value_subcat["subcatName_$this->curr_language"].'</a>
		                                          </li>';
		                    $inner_subcat_id .= $value_subcat['id'];
 						}
						$cat_name .= ' <li class="panel-group children" id="'.$val_cat_nm['id'].'aa">
								            <div class="panel panel-default child-heading">
								               <div class="panel-heading">
								                  <h6 class="panel-title">
								                     <a data-toggle="collapse" data-parent="#'.$val_cat_nm['id'].'aa" href="#'.$inner_subcat_id.'aaa" title="Mechanical parts &amp; Fabrication">
								                     <span class="menu-title">'.$val_cat_nm["categoryName_$this->curr_language"].'</span>
								                     <span class="fa-icons plus"></span>
								                     </a>
								                  </h6>
								               </div>
								               <div id="'.$inner_subcat_id.'aaa" class="panel-collapse collapse">
								                  <div class="panel-body">
								                     <ul>
								                       '.$inner_loop_subcat.'
								                     </ul>
								                  </div>
								               </div>
								            </div>
								         </li>';
					}	
				$filds = array(
					"%MAIN_CAT_NM%" =>$value["maincatName_$this->curr_language"],
					"%MAIN_CAT_ID%" =>$value['id'],
					"%CAT_NM%" => $cat_name
				);
				$get_mobile_view_cat_panel .= str_replace(array_keys($filds), array_values($filds), $get_mobile_view_content);
				}
			}
			return $get_mobile_view_cat_panel;
		}

		public function all_cat_panel() {
			$indicator_content = $all_cat =$slider_content =$slider_url=$inner_loop='';
			$all_main_cat = $this->db->pdoQuery("SELECT * FROM tbl_product_main_category WHERE isActive = ? LIMIT 10",array('y'))->results();
			foreach ($all_main_cat as $key => $value) {
				$inner_loop= '';
				$totalProduct = $this->db->pdoQuery("SELECT * FROM tbl_products WHERE isActive = ? AND main_cat_id = ?",array('y',$value['id']))->affectedRows();
				if($totalProduct > 0)
				{
					$indicator_content .= '<li class="'.(empty($key) ? 'active' : '').'">
											   <a href="#'.$value['id'].'" data-toggle="tab">'.$value["maincatName_$this->curr_language"].'<span><i class="fa fa-caret-right" aria-hidden="true"></i></span></a>
											</li>';
					
				}
				else{
					$indicator_content .= '';
				}
				$all_cat = $this->db->pdoQuery("SELECT * FROM tbl_product_category WHERE isActive = ? AND maincatID =?",array('y',$value['id']))->results();
				foreach ($all_cat as $keys => $val_cat_nm) {
					$inner_loop_subcat='';
	                $all_subcat = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = 'y' AND catId =".$val_cat_nm['id']." LIMIT 4")->results();

	                $count_subcat = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = ? AND catId =?",array('y',$val_cat_nm['id']))->affectedRows();
	               	$hide_view_more_subcat = '';
	                $hide_view_more_subcat = ($count_subcat > 4) ? '':'hide';
	                foreach ($all_subcat as $key_subcat => $value_subcat) {	  
	                		$inner_loop_subcat .= '<li>
	                                             <a target="" href="'.SITE_URL.'search-product/?cat_id='.$val_cat_nm['id'].'&subcat_id='.$value_subcat['id'].'">'.$value_subcat["subcatName_$this->curr_language"].'</a>
	                                          </li>';

	                }
				$inner_loop .= '<li class="masonry-li">
                               <h4>	
                                  <a target="" href="'.SITE_URL.'search-product/?cat_id='.$val_cat_nm['id'].'">'.$val_cat_nm["categoryName_$this->curr_language"].'</a>
                               </h4>
                               <ul>
                                  '.$inner_loop_subcat.'
                                  <li class="vmore">
                                     <a target="" href="'.SITE_URL.'category-listing" class="'.$hide_view_more_subcat.'">'.VIEW_MORE.' <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                                  </li>
                               </ul>
                            </li>';	                           
				}
				$slider_content .= '<div class="tab-pane '.(empty($key) ? 'active' : '').'" id="'.$value['id'].'">
	                                 <ul class="masonry-ul">
	                                    '.$inner_loop.'
	                                 </ul>
	                              </div>';
			}
			return array('indicator_content' => $indicator_content, 'slider_content' => $slider_content);
		}
		public function getHomeSlider() {
			
			$indicator_content = $slider_content =$slider_url='';
			$sliders = $this->db->select('tbl_slider', array('file_name,slider_url'), array('isActive' => 'y'))->results();
			foreach ($sliders as $key => $value) {
				$indicator_content .= "<li data-target='#bs-carousel' data-slide-to='".$key."' class='".(empty($key) ? "active" : '')."'></li>";

				$slider_content .= "<div class='item ".(empty($key) ? "active" : '')."'>
		    						<div class='slider-img'>
		    						<a href='".$value['slider_url']."'>
		    							<img src='".getImage(DIR_SLIDER.$value['file_name'],SITE_SLIDER.$value['file_name'],$value['file_name'])."'/>
		    						</a>
		    						</div>
		    					</div>";
			}
			return array('indicator_content' => $indicator_content, 'slider_content' => $slider_content);
		}
		public function getPopularSubcatPanel() {
			$get_populat_final_content = $totalProduct ='';
			$get_popular_main_content = (new MainTemplater(DIR_TMPL.$this->module."/popular_subcat-sd.skd"))->compile();

			$Popular_cat_data = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = ? AND isDisplay = ? GROUP BY id",array('y','y'))->results();
			if(!empty($Popular_cat_data)) {
				foreach ($Popular_cat_data as $key => $value) {
					$totalProduct = $this->db->pdoQuery("SELECT * FROM tbl_products WHERE isActive = ? AND sub_cat_id = ?",array('y',$value['id']))->affectedRows();
					// MingLau -> get_popular_product_detail part
					if($totalProduct > 0){
						$filds = array(
							"%SUBCAT_NAME%" =>$value["subcatName_$this->curr_language"],
							"%HIDE_CLSS_VIEW_MORE%" =>($totalProduct > 6) ? '':'hide',
							"%SUBCATT_PRODUCTS%" => $this->getPopularSubcatProducts($value['id'])
						);
						$get_populat_final_content .= str_replace(array_keys($filds), array_values($filds), $get_popular_main_content);
					}
				}
			}
			return $get_populat_final_content;
		}
		public function getCatPanel() {
			$get_cat_panel_final_content = $totalProduct = $SubCatList ='';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/cat_panel-sd.skd"))->compile();

			$Popular_cat_panel = $this->db->pdoQuery("SELECT * FROM tbl_product_category WHERE isActive = ? AND isDisplay = ?",array('y','y'))->results();

			if(!empty($Popular_cat_panel)) {
				foreach ($Popular_cat_panel as $key => $value) {
					$totalProduct = $this->db->pdoQuery("SELECT * FROM tbl_products WHERE isActive = ? AND cat_id = ?",array('y',$value['id']))->affectedRows();
					if($totalProduct > 0){
						$SubCatList = $this->getInnerSubcatList($value['id']);
						$filds = array(
							"%CATNAME%" =>$value["categoryName_$this->curr_language"],
							"%SUBCAT_LIST%" => $SubCatList['indicator_subcat_content'],
							"%SUBCAT_CONTENT%" => $SubCatList['all_subcat_content']
						);
						$get_cat_panel_final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
					}
				}
			}
			return $get_cat_panel_final_content;
		}
		public function getInnerSubcatList($cat_id) {
			$indicator_subcat_content = $all_subcat_content =$slider_url='';
			$subcat_list_all = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE catId = ? AND isActive = ?",array($cat_id,'y'))->results();
			foreach ($subcat_list_all as $key => $value) {
				$totalProduct = $this->db->pdoQuery("SELECT * FROM tbl_products WHERE isActive = ? AND sub_cat_id = ? ",array('y',$value['id']))->affectedRows();
				if($totalProduct > 0){
					$indicator_subcat_content .= " <li class='".(empty($key) ? "active" : '')."'><a data-toggle='pill' href='#".$value['id']."'>".$value["subcatName_$this->curr_language"]."</a></li>";

					$all_subcat_content .= $this->getAllSubcatData($key,$cat_id,$value['id']);
				}
			}
			return array('indicator_subcat_content' => $indicator_subcat_content, 'all_subcat_content' => $all_subcat_content);
		}
		public function getAllSubcatData($key,$cat_id,$sub_cat_id) {
			$all_subcat_final_content = $price = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/all_subcat_data-sd.skd"))->compile();
			$filds = array(
				"%INDICATOR_ID%" =>$sub_cat_id,
				"%ALL_PRO_DATA%" =>$this->innercatProduct($cat_id,$sub_cat_id),
				"%ACTIVE_CLS%" =>empty($key) ? "active" : ''
			);
			$all_subcat_final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
			return $all_subcat_final_content;
		}
		public function innercatProduct($cat_id,$sub_cat_id) {
			$get_populat_subcat_final_content = $price = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/inner_subcat_product-sd.skd"))->compile();

			$all_products = $this->db->pdoQuery("SELECT * FROM tbl_products as p INNER JOIN tbl_users AS u ON u.id=p.user_id WHERE p.isActive = ? AND p.sub_cat_id = ? AND p.cat_id = ? AND u.isActive=?",array('y',$sub_cat_id,$cat_id,'y'))->results();
			if(!empty($all_products)) {
				foreach ($all_products as $key => $value) {
					$profile_image = explode(',', $value["product_image"]);
					$price = ($value["isNegotiable"] == 'y') ? $value['min_price'].'-'.$value['max_price'].' '.CURRENCY_CODE : $value['min_price'].' '.CURRENCY_CODE;
 					$filds = array(
						"%PRODUCT_SLUG%" =>$value["product_slug"],
						"%PRODUCT_TITLE%" =>$value["product_title"],
						"%PRODUCT_IMAGE%" =>SITE_PRODUCT_IMG.$profile_image[0],
						"%PRICE%" => $price
					);
					$get_populat_subcat_final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
				}
			}
			return $get_populat_subcat_final_content;
		}
		public function getPopularSubcatProducts($subcatId) {
			$get_populat_subcat_final_content = $price = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/popular_subcat_product-sd.skd"))->compile();

			$Popular_cat_product_data = $this->db->pdoQuery("SELECT * FROM tbl_products AS p inner join tbl_users as u on u.id=p.user_id WHERE p.isActive = ? AND p.sub_cat_id = ? AND u.isActive=? AND p.add_to_home_page=? LIMIT 20",array('y',$subcatId,'y', 'y'))->results();
			if(!empty($Popular_cat_product_data)) {
				foreach ($Popular_cat_product_data as $key => $value) {
					$profile_image = explode(',', $value["product_image"]);
					$price = ($value["isNegotiable"] == 'y') ? $value['min_price'].'-'.$value['max_price'].' '.CURRENCY_CODE : $value['min_price'].' '.CURRENCY_CODE;
 					$filds = array(
						"%PRODUCT_SLUG%" =>$value["product_slug"],
						"%PRODUCT_TITLE%" =>$value["product_title"],
						"%PRODUCT_IMAGE%" =>SITE_PRODUCT_IMG.$profile_image[0],
						"%PRICE%" => $price
					);
					$get_populat_subcat_final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
				}
			}
			return $get_populat_subcat_final_content;
		}
		public function getPageContent() {
			$main_content = (new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd"))->compile();
			$slider = '';
			$slider = $this->getHomeSlider();
			$total_product = $this->db->pdoQuery("SELECT count(id) AS totalProduct FROM tbl_products WHERE isActive = ? ",array('y'))->result(); 
			$total_supplier = $this->db->pdoQuery("SELECT count(id) AS totalsupplier FROM tbl_company WHERE verified = ?",array('y'))->result(); 
			$total_rfq = $this->db->pdoQuery("SELECT count(id) AS totalrfq FROM tbl_buying_request WHERE isActive = ? ",array('y'))->result();
			$total_buyers = $this->db->pdoQuery("SELECT count(id) AS totalBuyer FROM tbl_users WHERE isActive = ? AND (user_type = ? OR user_type = ?)",array('y','1','3'))->result(); 
			$all_banner = $this->db->pdoQuery("SELECT banner_url,file_name FROM tbl_banner WHERE id in  (1,2) AND banner_type in (1,2)",array())->results();
			$Rfq_banner = !empty($all_banner[1]) ? $all_banner[1] : [];
			$Footer_banner = !empty($all_banner[0]) ? $all_banner[0] : [];
			
			$unit="unit_value_".$this->curr_language." AS unitName";
			$unit_data=$this->db->pdoQuery("SELECT id,$unit FROM tbl_unit_value WHERE isActive=? Order by id",array('y'))->results();
			$unitValues='';
			foreach ($unit_data as $key => $value) {
				$selected="";
				
				$unitValues.="<option value='".$value['id']."'>".$value['unitName']."</option>";
			}
			$total_selected_supplier = $this->db->pdoQuery("SELECT * FROM tbl_users WHERE isActive = ? AND set_on_home_page = ? AND (user_type = ? OR user_type = ?)",array('y','y','2','3'))->affectedRows();
			$all_slider_cat = '';
			$catNm="categoryName_".$this->curr_language." AS catNm";
			$slider_data_cat=$this->db->pdoQuery("SELECT id,$catNm FROM tbl_product_category WHERE isActive=? AND is_display_slider=?",array('y','y'))->results();
			foreach ($slider_data_cat as $key => $val) {
				$totalProduct = $this->db->pdoQuery("SELECT * FROM tbl_products WHERE isActive = ? AND cat_id = ? ",array('y',$val['id']))->affectedRows();
				if($totalProduct > 0){		
				$all_slider_cat.='<li>
							<a href="'.SITE_URL."search-product/?cat_id=".$val['id'].'" title"'.$val['catNm'].'" target="">'.$val['catNm'].'</a>
						</li>';
					}else{
						$all_slider_cat.='';
					}
			}
			$ad_image_one_div = $ad_image_two_div = $ad_image_three_div = $ad_image_four_div = $ad_image_five_div = $ad_image_six_div =$ad_image_seven_div =$ad_image_eight_div =$ad_image_nine_div =$ad_image_ten_div ='';
			$banner_data_one = $this->db->pdoQuery("SELECT * FROM tbl_banner WHERE isActive = ? AND banner_type=?",array('y','popular_one'))->result();
			$ad_image_one_div =!empty ($banner_data_one['file_name']) ? '<div class="ad-banner-section">
				<a href="'.$banner_data_one['banner_url'].'">
					<img src="'.SITE_UPD.'banner-sd/'.$banner_data_one['file_name'].'"/>
				</a>
				<a href="'.SITE_URL.'contact-us" class="hire" title="Hire Ad Space" target="">'.HIRE_AD_SPACE.'</a>
			</div>':'';
			$banner_data_two = $this->db->pdoQuery("SELECT * FROM tbl_banner WHERE isActive = ? AND banner_type=?",array('y','popular_two'))->result();
			$ad_image_two_div =!empty ($banner_data_two['file_name']) ? '<div class="ad-banner-section">
				<a href="'.$banner_data_two['banner_url'].'">
					<img src="'.SITE_UPD.'banner-sd/'.$banner_data_two['file_name'].'"/>
				</a>
				<a href="'.SITE_URL.'contact-us" class="hire" title="Hire Ad Space" target="">'.HIRE_AD_SPACE.'</a>
			</div>':'';
			
			$banner_data_six = $this->db->pdoQuery("SELECT * FROM tbl_banner WHERE isActive = ? AND banner_type=?",array('y','cat_one'))->result();
			$ad_image_six_div =!empty ($banner_data_six['file_name']) ? '<div class="ad-banner-section2 ad-banner-section">
				<a href="'.$banner_data_six['banner_url'].'">
					<img src="'.SITE_UPD.'banner-sd/'.$banner_data_six['file_name'].'"/>
				</a>
				<a href="'.SITE_URL.'contact-us" class="hire" title="Hire Ad Space" target="">'.HIRE_AD_SPACE.'</a>
			</div>':'';			
			$banner_data_seven = $this->db->pdoQuery("SELECT * FROM tbl_banner WHERE isActive = ? AND banner_type=?",array('y','cat_two'))->result();
			$ad_image_seven_div =!empty ($banner_data_seven['file_name']) ? '<div class="ad-banner-section2 ad-banner-section">
				<a href="'.$banner_data_seven['banner_url'].'">
					<img src="'.SITE_UPD.'banner-sd/'.$banner_data_seven['file_name'].'"/>
				</a>
				<a href="'.SITE_URL.'contact-us" class="hire" title="Hire Ad Space" target="">'.HIRE_AD_SPACE.'</a>
			</div>':'';
			
			$topSupplier = $this->topSelected_supplier();
			$topSupplierSection = 'hide';
			if (!empty($topSupplier)) {
				$topSupplierSection = '';
			}

			$fields = array(
				"%AD_IMAGE_ONE_DIV%"=>$ad_image_one_div,
				"%AD_IMAGE_TWO_DIV%"=>$ad_image_two_div,
				"%AD_IMAGE_SIX_DIV%"=>$ad_image_six_div,
				"%AD_IMAGE_SEVEN_DIV%"=>$ad_image_seven_div,
				"%SLIDER_CAT%"=>$all_slider_cat,
				"%VIRE_MORE_HODE_TOP_SELECTED%" => ($total_selected_supplier > 3) ? '':'hide',
				"%SESSUSERID%" => !empty($this->sessUserId) ? $this->sessUserId : 0,
				"%CONTRY_PANEL%" => $this->getContryPanel(),
				"%SLIDER_INDICATORS%" => $slider['indicator_content'],
				"%SLIDER%" => $slider['slider_content'],
				"%POPULAR_SUBCAT_PANEL%" => $this->getPopularSubcatPanel(),
				"%TOTAL_PRODUCT%" => $total_product['totalProduct'],
				"%TOTAL_SUPPLIER%" => $total_supplier['totalsupplier'],
				"%TOTAL_RFQ%" => $total_rfq['totalrfq'],
				"%TOTAL_BUYER%" => $total_buyers['totalBuyer'],
				"%CAT_PANEL%" => $this->getCatPanel(),
				"%TOP_SUPPLIER_SECTION%" => $topSupplierSection,
				"%TOPSELECTED_SUPPLIER%" => $topSupplier,
				"%RFQ_BANNER%"=>SITE_UPD.'banner-sd/'.$Rfq_banner['file_name'],
				"%RFQ_BANNER_URL%"=>$Rfq_banner['banner_url'],
				"%FOOTER_BANNER%"=>SITE_UPD.'banner-sd/'.$Footer_banner['file_name'],
				"%FOOTER_BANNER_URL%"=>$Footer_banner['banner_url'],
				"%UNIT_VALUES%"=>$unitValues
			);
			$main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
			return $main_content;
		}
		public function topSelected_supplier() {
			$get_selected_content = $totalProduct =$total_product_image= $time_plan = $SubCatList =$total_product_image_first=$all_image=$exported_countey ='';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/top_selected_supplier-sd.skd"))->compile();
			$top_selected_supplier = $this->db->pdoQuery("SELECT * FROM tbl_users WHERE isActive = ? AND set_on_home_page = ? AND (user_type = ? OR user_type = ?)",array('y','y','2','3'))->results();
			if(!empty($top_selected_supplier)) {
				foreach ($top_selected_supplier as $key => $value) {
						
						$company_data = $this->db->pdoQuery("SELECT * FROM tbl_company WHERE user_id = ? ",array($value['id']))->result();
						$totalOrders = $this->db->pdoQuery("SELECT count(id) AS totalOrder FROM tbl_manage_orders WHERE supplier_id = ? ",array($value['id']))->result();
						$top_contry = $this->db->pdoQuery("SELECT * FROM tbl_market_and_distribution WHERE user_id = ? ",array($value['id']))->result();
						$exported_countey=array("NA"=>$top_contry['North_America'] ,"SA"=>$top_contry['South_America'] ,"EE"=>$top_contry['Eastern_Europe'] ,"SA"=>$top_contry['Southeast_Asia'] ,"AF"=>$top_contry['Africa'] ,"OC"=>$top_contry['Oceania'] ,"ME"=>$top_contry['Mid_East'] ,"EA"=>$top_contry['Eastern_Asia'] ,"WE"=>$top_contry['Western_Europe'] ,"CA"=>$top_contry['Central_America'] ,"NE"=>$top_contry['Northern_Europe'] ,"SE"=>$top_contry['Southern_Europe'] ,"SA"=>$top_contry['South_Asia'] ,"DM"=>$top_contry['Domestic_Market']);
						arsort($exported_countey);
						$exported_countey = array_slice($exported_countey, 0, 3);	
						$gold_coin_hide_class = '';
						$gold_coin_hide_class = ($value['gold_icon_display'] == 'y') ? '': 'hide';
						$datestr=$value['plan_purchased_date'];
						if($datestr == '0000-00-00 00:00:00'){
							$interval_val = '';
						}else {
							$date1 = new DateTime($datestr); 
						    $date2 = new DateTime("now"); 
						    $interval = $date1->diff($date2); 
						    if($interval->format('%y') == 0){
						    	if($interval->format('%m') == 0){
						    		$time_plan =$interval->format('%d');
						    		$interval_val = DAYS;
						    	} else{
						    		$time_plan =$interval->format('%m'.'.'.'%d');
						    		$interval_val = MONTHS;
						    	}
						    } else{
						    	$time_plan =$interval->format('%y'.'.'.'%m');
						    	$interval_val = YEARS;
						    }
						    
						}
					if(!empty($this->selected_supplier_all_image($value['id']))){
						$filds = array(
							"%COMPANY_NAME%" =>$company_data["company_name"],
							"%COMPANY_SLUG%" =>$company_data["company_slug"],
							"%GOLD_COIN_HIDE_CLASS%" =>$gold_coin_hide_class,
							"%BUSINESS_TYPE%" => getBusinessType($company_data['business_type_id']),
							"%TOTAL_ORDERS%" => $totalOrders['totalOrder'],
							"%VERIFIED_CLASS%" => ($company_data['verified'] == 'y') ? '' : 'hide',
							"%EXPORT_CONTRY%" => implode(',',array_keys($exported_countey)),
							"%TIME_PLAN%" => $time_plan,
							"%HIDE_LINE%" => !empty($this->selected_supplier_all_image($value['id'])) ? '' : 'hide',
							"%PROIMAGE%" => $this->selected_supplier_all_image($value['id']),
							"%INTERVAL%" => $interval_val
						);					
						$get_selected_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
					}else{
						$get_selected_content .='';	
					}
				}
			}
			return $get_selected_content;
		}
		public function selected_supplier_all_image($supplier_id) {
			$final_content = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/supplier_all_pro_image-sd.skd"))->compile();

			$total_product_image = $this->db->pdoQuery("SELECT o.*,p.product_title,p.product_slug,p.product_image, count(o.product_id) as c 
														FROM `tbl_manage_orders` as o 
														INNER JOIN tbl_products as p on p.id=o.product_id 
														WHERE o.supplier_id= ".$supplier_id." and o.product_id!=0 group by o.product_id ORDER BY c DESC, o.id DESC LIMIT 3")->results(); 
			
			if(!empty($total_product_image)) {
				foreach ($total_product_image as $key => $value) {
						$first_image = explode(',', $value['product_image']);
						$filds = array(
							"%PRO_IMG%" => SITE_UPD.'product-sd/'.$first_image[0],
							"%PRO_TITLE%" => $value['product_title'],
							"%PRO_SLUG%" => $value['product_slug']
						);
					$final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
				}
			}					  
			return $final_content;
		}	
		public function getContryPanel() {
			$final_content = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/contry_panel-sd.skd"))->compile();

			$contry_data = $this->db->pdoQuery("SELECT * FROM tbl_manage_country_flag WHERE isActive = ? LIMIT 9",array('y'))->results();
			if(!empty($contry_data)) {
				foreach ($contry_data as $key => $value) {
						$filds = array(
							"%FLAG_IMG%" => getImage(DIR_UPD.'contry_flag-sd/'.$value['country_image'],SITE_UPD.'contry_flag-sd/'.$value['country_image'],$value['country_image']),
							"%FLAG_NM%" => $value["country_name"],
							"%FLAG_LINK%" =>SITE_URL
						);

						$final_content .= str_replace(array_keys($filds), array_values($filds), $main_content);
				}
			}
			return $final_content;
		}		
		public function getFooterContent() {
		    $footer_content_list_types = $footer_content_list_content = $footer_content_list = $footer_script_list_content = $html = $footer_script_list = NULL;

		    $support_content_parsed = $company_content_parsed = $customer_content_parsed = $pros_content_parsed = NULL;
		    $html = (new MainTemplater(DIR_TMPL . "footer-sd.skd"))->compile();

		    $footer_content_links = (new MainTemplater(DIR_TMPL . "footer_click_links-sd.skd"))->compile();
		    $contents = $this->db->select('tbl_content_type', array('*', "type_name_$this->curr_language AS current_content_name"), array('isActive'=>'y'))->result();
			    $company_content = $this->db->select('tbl_content', array('pageTitle', 'page_slug'), array('isActive <>' => 'n'))->results();
			    if(!empty($company_content)) {
			        foreach ($company_content as $key => $value) {
			            $company_content_parsed .= str_replace(
			                array(
			                    "%URL%",
			                    "%PAGE_TITLE%"
			                ),
			                array(
			                    get_link('content',$value['page_slug']),
			                    $value['pageTitle']
			                ),
			                $footer_content_links
			            );
			        }
			    	
			    }
		    
		    $fields = array(
	            "%COMPANY_CONTENT%" => $company_content_parsed,
	            "%REMEMBER_ME_CHECKED%" => ((!empty($_COOKIE['remember']) && $_COOKIE['remember']=="y") ? "checked='checked'" : ""),
				"%LOGIN_EMAIL%" => ((!empty($_COOKIE['uName'])) ? $_COOKIE['uName'] : ""),
				"%LOGIN_PASSWORD%" => ((!empty($_COOKIE['uPass'])) ? base64_decode($_COOKIE['uPass']) : ""),
				"%TERMS_URL%" => get_link('content', getTableValue('tbl_content', 'page_slug', array('id'=>'3'))),
				"%PRIVACY_URL%" => get_link('content', getTableValue('tbl_content', 'page_slug', array('id'=>'4'))),
	        );

		    $html = str_replace(array_keys($fields), array_values($fields), $html);
		    return $html;
		}

		public function getLanguages() {
			$languages_options = '';
			$languages = $this->db->select('tbl_language', array('*'), array('status'=>'a'))->results();
			if(!empty($languages)) {
				foreach ($languages as $key => $value) {
					$languages_options .= '<li>
					                        <a href="'.SITE_URL.'set-language/'.$value['url_constant'].'" id="footer_language" title="'.$value['languageName'].'">
					                        <div class="lang-img">
					                          <img src="'.SITE_LANGUAGE.$value['lang_img'].'">
					                         </div> &nbsp;<span>'.$value['languageName'].'</span>
					                        </a>
					                      </li>';
				}
			}
			return $languages_options;
		}

		public function getleftPanel() {
		    $main_content = '';
		    $temp_name = "left_panel-sd.skd";
	        $main_content = (new MainTemplater(DIR_TMPL.$temp_name))->compile();
	        $supplierProfile=$manageProduct=$manageQuote=$favBuyingRequest=$membership_plan=$supplierMngOrder=$home=$moderatorMng=$userProfile=$buyingRequest=$favProduct=$favSupplier=$tradeAlerts=$notifications=$accountSettings=$paymentHistory="";
	        $totalMessage = $this->db->pdoQuery("SELECT COUNT(id) as newMessage FROM tbl_messages where receiver = $this->sessUserId and is_read='n'")->result();
	        
	        if(!empty($this->modulePermission)){	        		
	        	$fields = array(
	            	"%TEST_CONTENT%" => implode(',', $this->modulePermission),
	            	"%MODERATOR_ID%" => $_SESSION['moderatorId']	            	
		        );
			    $main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
	        }else{
	        	$fields = array(
	            	"%MODERATOR_ID%" => "",
	            	"%NEW_MESSAGES%" => $totalMessage['newMessage']
		        );
			    $main_content = str_replace(array_keys($fields), array_values($fields), $main_content);
	        }

	        return $main_content;
		}

					public function getBlogSidebar() {
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL."blog-sidebar-sd.skd"))->compile();
		$fields = array(
				"%BLOG_TAGS%" => $this->getBlogTags(),
				"%BLOG_CATEGORIES%" => $this->getBlogCategory(),
				"%BLOG_RECENT_POSTS%" => $this->getBlogRecentPosts()
			);
		$final_content = str_replace(array_keys($fields),array_values($fields),$main_content);
		return $final_content;
	}
	public function getBlogTags() {
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL."blog-tags-sd.skd"))->compile();
		$get_blog_tags = $this->db->pdoQuery("SELECT * FROM tbl_blog_tags WHERE isActive =  ?",array('y'))->results();
		foreach ($get_blog_tags as $key => $value) {
			$fields = array(
					"%TAG_URL%" =>'<li><a href="'.SITE_URL.'blog/tag/'.$value['tag_slug'].'">'.ucwords($value['tag_name']).'</a></li>'
				);
			$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
		}
		return $final_content;
	}
	public function getPostTags($blogTag = ''){		
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL."blog-sd/blog-tags-sd.skd"))->compile();
		$tags_id = explode(',', $blogTag);
		foreach ($tags_id as $key => $value) {
			$get_tag = $this->db->pdoQuery("SELECT * FROM tbl_blog_tags WHERE id = ? ",array($value))->result();
			$fields = array(
					"%TAG%" => '<li><a href="'.SITE_URL.'blog/tag/'.$get_tag['tag_slug'].'">'.ucwords($get_tag['tag_name']).'</a></li>'
				);	
			$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
		}
		return $final_content;
	}
	public function getBlogRecentPosts() {
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL."blog-recent-posts-sd.skd"))->compile();
		$get_blog_tags = $this->db->pdoQuery("SELECT * FROM tbl_blog_post WHERE isActive =  ? ORDER BY id DESC LIMIT 4",array('y'))->results();
		foreach ($get_blog_tags as $key => $value) {
			$fields = array(
					"%POST_TITLE%" => ucwords($value['title']),
					"%POST_URL%" => SITE_URL.'blog/'.$value['post_slug']
				);
			$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
		}
		return $final_content;
	}
	public function getBlogCategory() {
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL."blog-category-sd.skd"))->compile();
		$get_blog_tags = $this->db->pdoQuery("SELECT * FROM tbl_blog_category WHERE isActive =  ?",array('y'))->results();
		foreach ($get_blog_tags as $key => $value) {
			$fields = array(
					"%BLOG_CATEGORY%" => ucwords($value['category_name']),
					"%CATEGORY_URL%" => SITE_URL.'blog/category/'.$value['category_slug']
				);
			$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
		}
		return $final_content;
	}

		
	}
?>
