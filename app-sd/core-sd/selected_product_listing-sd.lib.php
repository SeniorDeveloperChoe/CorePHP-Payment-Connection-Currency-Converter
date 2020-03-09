<?php
class selected_product_listing extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$main_cat_name ='';
		$all_main_cat_mobile = $this->db->pdoQuery("SELECT * FROM tbl_product_main_category WHERE isActive = ?",array('y'))->results();
		if(!empty($all_main_cat_mobile)) {
				$cat_name = '';
			foreach ($all_main_cat_mobile as $key => $value) {
				$all_cat = $this->db->pdoQuery("SELECT * FROM tbl_product_category WHERE isActive = ? AND maincatID =?",array('y',$value['id']))->results();
				$subcat_loop ='';
					foreach ($all_cat as $keys => $val_cat_nm) {
						$all_subcat = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = 'y' AND catId =".$val_cat_nm['id']." LIMIT 4")->results();
						 $inner_loop_subcat=$inner_subcat_id='';
						 $count_subcat = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = ? AND catId =?",array('y',$val_cat_nm['id']))->affectedRows();
	               	$hide_view_more_subcat = '';
	                $hide_view_more_subcat = ($count_subcat > 4) ? '':'hide';
						foreach ($all_subcat as $key_subcat => $value_subcat) {	  
							$inner_loop_subcat .= '<li>
		                                             <a href="#">'.$value_subcat["subcatName_$this->curr_language"].'</a>
		                                          </li>';
 						}
						$cat_name .= '<div class="col-md-3 col-sm-6 col-xs-6">
                                        <h5>
                                            <a href="#" title="'.$val_cat_nm["categoryName_$this->curr_language"].'">'.$val_cat_nm["categoryName_$this->curr_language"].'</a>
                                        </h5>
                                        <ul>
                                           '.$inner_loop_subcat.'
                                            <li>
                                            <a href="'.SITE_URL.'selected-product-listing" class="view-more '.$hide_view_more_subcat.'">View More &nbsp;<i class="fa fa-angle-double-right"></i></a>
                                            </li>
                                        </ul>
                                    </div>';
					}
				$main_cat_name .= '<li>
                                <a href="#">'.$value["maincatName_$this->curr_language"].'</a>
                            </li>';
			}
		}
		$fields = array(
			"%MAIN_CAT%"=>$main_cat_name,
			"%CAT_NM%"=>$cat_name
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	
}

?>