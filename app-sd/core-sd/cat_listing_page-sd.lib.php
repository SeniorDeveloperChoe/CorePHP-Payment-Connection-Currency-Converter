<?php
class similarRfq extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$main_cat_name =$head_cat ='';
		$all_main_cat_mobile = $this->db->pdoQuery("SELECT * FROM tbl_product_main_category WHERE isActive = ?",array('y'))->results();
		if(!empty($all_main_cat_mobile)) {
			foreach ($all_main_cat_mobile as $key => $value) {			
				$active = ($key == 0) ? 'active' : '';
				$active_cls = ($key == 0) ? 'active in' : '';
				$all_cat = $this->db->pdoQuery("SELECT * FROM tbl_product_category WHERE isActive = ? AND maincatID =?",array('y',$value['id']))->results();
					$cat_details ='';
					foreach ($all_cat as $keys => $val_cat_nm) {
						$cat_details .=$this->right_panel($val_cat_nm['id']);
					}
				$head_cat .= '<div id="menu'.$value['id'].'" class="tab-pane fade '.$active_cls.'">
                      <div class="row">'.$cat_details.'</div></div>';
				$main_cat_name .= '<li class="'.$active.'"><a data-toggle="pill" href="#menu'.$value['id'].'">'.$value["maincatName_".$this->curr_language].'</a></li>';
			}
		}
		$fields = array(
			"%MAIN_CAT%"=>$main_cat_name,
			"%RIGHT_PANEL%"=>$head_cat
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}
	public function right_panel($cat_id) {
		$cat_heading = (new MainTemplater(DIR_TMPL . "$this->module/cat_head-sd.skd"))->compile();
		$cat_nm ='';
		$main_cat_name ='';
		$all_cat = $this->db->pdoQuery("SELECT * FROM tbl_product_category WHERE isActive = ? AND id =?",array('y',$cat_id))->results();
		if(!empty($all_cat)) {
			foreach ($all_cat as $key => $value) {		
				$all_subcat = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = 'y' AND catId =".$value['id']." LIMIT 15")->results();
				 $inner_loop_subcat=$inner_subcat_id='';
				 $count_subcat = $this->db->pdoQuery("SELECT * FROM tbl_product_subcategory WHERE isActive = ? AND catId =?",array('y',$value['id']))->affectedRows();
	       		$hide_view_more_subcat = '';
	        	$hide_view_more_subcat = ($count_subcat > 4) ? '<li>
                                            <a href="'.SITE_URL.'search-product/" class="view-more">View More &nbsp;<i class="fa fa-angle-double-right" target=""></i></a>
                                            </li>':'';
				foreach ($all_subcat as $key_subcat => $value_subcat) {	  
					$inner_loop_subcat .= '<li>
	                                         <a href="'.SITE_URL.'search-product/?cat_id='.$value['id'].'&subcat_id='.$value_subcat['id'].'" target="">'.$value_subcat["subcatName_$this->curr_language"].'</a>
	                                      </li>';
					}

				$cat_nm .= '<a href="'.SITE_URL.'search-product/?cat_id='.$value['id'].'" title="'.$value["categoryName_$this->curr_language"].'" target="">'.$value["categoryName_$this->curr_language"].'</a>';
			}
		}
		$fields = array(
			"%CAT_NM%"=>$cat_nm,
			"%SUBCAT_NM%"=>$inner_loop_subcat
		);
		$cat_heading = str_replace(array_keys($fields), array_values($fields), $cat_heading);
		return $cat_heading;
	}

	
}

?>