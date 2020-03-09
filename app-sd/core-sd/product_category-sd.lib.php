<?php
class ProductCategory extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$getCompanyDetail=$logo='';
		$getCompanyDetail = $this->db->select('tbl_company', array('*'), array('id'=>$this->id['id']))->result();
		$logo=!empty($getCompanyDetail['logo'])?SITE_UPD.'supplier_logo/'.$getCompanyDetail['logo']:SITE_UPD."no_prodcut_img.png";
		$cno = $main = $hide_none='';
		$header_class_one='col-md-4 col-sm-5';
		$header_class_two='col-md-8 col-sm-7';
		if($getCompanyDetail['selected_template'] == 'none'){
			$cno ='-';
			$main =	'-';
			$hide_none = 'hide';
		}else{
			if($getCompanyDetail['selected_template'] == 'two'){
				$header_class_one='col-md-12';
				$header_class_two='col-md-12 text-center';
			}
			$hide_none = '';
			$cno =!empty($getCompanyDetail['contact_no_1']) ? $getCompanyDetail['contact_no_1']: '-';
			$main =	!empty($getCompanyDetail['company_mail']) ? $getCompanyDetail['company_mail']: '-';
		}
		$cat_list = $this->getCatListWidImage($getCompanyDetail['user_id']);
		$fields = array(
			"%SLUG%"=>$getCompanyDetail['company_slug'],
			"%HEADER_CLASS_ONE%" => $header_class_one,
			"%HEADER_CLASS_TWO%" => $header_class_two,
			"%HIDE_REPORT_SUPPLIER%"=> isset($_SESSION['user_id'])?  ($getCompanyDetail['user_id'] == $_SESSION['userId'] ? '' : '<a href="javascript:;" class="report-bug" data-toggle="modal" data-target="#reportBug"><span><i class="icon-action-redo icons"></i></span> '.REPORT_SUPPLIER.'</a>') : '<a href="javascript:;" class="report-bug" data-toggle="modal" data-target="#reportBug"><span><i class="icon-action-redo icons"></i></span> '.REPORT_SUPPLIER.'</a>',
			"%COMPANY_NAME%"=>$getCompanyDetail['company_name'],
			"%COMPANY_MAIL%"=>$main,
			"%CONTACT_NO_1%"=>$cno,
			"%HIDE_NONE%"=>$hide_none,
			"%LOGO%"=>$logo,
			"%COMPANY_MAIL%"=>$main,
			"%CONTACT_NO_1%"=>$cno,
			"%HIDE_NONE%"=>$hide_none,
			"%INDICATOR_CONTENT%"=>$cat_list['indicator_content'],
			"%ALL_PRO%"=>$cat_list['slider_content'],
			"%VERIFIED%" => $getCompanyDetail['verified'] == 'y' ? ' <span class="verification">'.VERIFIED_L.' <i class="fa fa-check-circle"></i> </span>' : ''
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function getCatListWidImage($user_id) {
			$indicator_content = $slider_content =$slider_url='';
			$sliders = $this->db->pdoQuery("SELECT c.*,p.* FROM tbl_products AS p INNER JOIN tbl_product_category AS c ON p.cat_id = c.id WHERE c.isActive = ? AND p.user_id = ? GROUP BY p.cat_id",array('y',$user_id))->results();
			
			foreach ($sliders as $key => $value) {
				$indicator_content .= "<li class='".(empty($key) ? 'active' : '')."'><a data-toggle='pill' href='#menu".$value["cat_id"]."'>".$value["categoryName_$this->curr_language"]."</a></li>";

				$slider_content .= $this->all_pro_data($key,$value["cat_id"],$user_id,$value["categoryName_$this->curr_language"]);
			}
			return array('indicator_content' => $indicator_content, 'slider_content' => $slider_content);
		}
		public function all_pro_data($key,$cat_id,$user_id,$cat_nm) {
			$allProduct_data = $price = '';
			$main_content = (new MainTemplater(DIR_TMPL.$this->module."/all_product_data-sd.skd"))->compile();
			$filds = array(
				"%CAT_ID%" =>'menu'.$cat_id,
				"%ACTIVE%" =>empty($key) ? 'active' : '',
				"%CAT_NM%" =>$cat_nm,
				"%PRO_SECTION%"=>$this->Detail_product($cat_id,$user_id)
			);
			$allProduct_data .= str_replace(array_keys($filds), array_values($filds), $main_content);
			return $allProduct_data;
		}
		public function Detail_product($cat_id,$user_id) {
			$Detail_product = $price = '';
			$unitName="un.unit_value_".$this->curr_language." AS unitName";
			$all_pro = $this->db->pdoQuery("SELECT c.id,p.*,$unitName FROM tbl_products AS p 
				INNER JOIN tbl_product_category AS c ON p.cat_id = c.id 
				INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
				WHERE c.id = ? AND p.user_id = ?",array($cat_id,$user_id))->results();
			$getComapreProductImage ='';
			foreach ($all_pro as $key => $value) {
				$main_content = (new MainTemplater(DIR_TMPL.$this->module."/detail_product-sd.skd"))->compile();
				$getComapreProductImage=explode(',', $value['product_image']);
				$filds = array(
					"%PRODUCT_TITLE%" =>$value['product_title'],
					"%PRODUCT_SLUG%" =>$value['product_slug'],
					"%ORDER_QUANTITY%"=>$value['order_quantity'].' '.$value['unitName'],
					"%PRICE%" =>$value['isNegotiable'] == 'y' ? $value['min_price'].'-'.$value['max_price'].' '.CURRENCY_CODE: $value['min_price'].' '.CURRENCY_CODE ,
					"%PRODUCT_IMG%" =>SITE_PRODUCT_IMG.$getComapreProductImage[0],
					"%SUPPLY_ABILITY%" =>$value['supply_ability']
				);
			$Detail_product .= str_replace(array_keys($filds), array_values($filds), $main_content);
			}
			return $Detail_product;
		}
	}
?>