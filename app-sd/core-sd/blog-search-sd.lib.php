<?php
class BlogSearch extends Home {
	function __construct($module = "", $keyword = '') {
		foreach ($GLOBALS AS $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->keyword = $keyword;
	}
	public function getPageContent() {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/blog-search-sd.skd"))->compile();
		
		$fields = array(
			"%BLOG_SEARCH%" => $this->getSearchContent($this->keyword),
			"%BLOG_SIDEBAR%" => $this->getBlogSidebar()
		);
		$final_content = str_replace(array_keys($fields), array_values($fields), $main_content);
		return $final_content;
	}
	public function getSearchContent($keyword = ''){		
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/"."blog-search-data-sd.skd"))->compile();
		$where = "tbp.isActive = ?";
        $wArray = array("y");
		if($keyword != ''){
			$where .= " AND tbp.title LIKE ?";
			$wArray[] = "%".$keyword."%";
		}
		$get_posts = $this->db->pdoQuery("SELECT tbp.*,tbc.category_name FROM tbl_blog_post as tbp
			INNER JOIN tbl_blog_category as tbc ON (tbc.id = tbp.category_id) WHERE $where ORDER BY id DESC",$wArray)->results();
		if(!empty($get_posts)){
			foreach ($get_posts as $key => $value) {
				$fields = array(
						"%POST_URL%" => SITE_URL."blog/".$value['post_slug'],
						"%TITLE%"=> ucfirst(filtering($value['title'],'output'))
					);	
				$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
			}
		}else{
			$final_content = '
			<div class="no-msg">
			<img src="'.SITE_IMG.'icons/document.png" alt="" />
			<h3>Opps!</h3>
			<p>No relevant result found for keyword <b>"'.$keyword.'"</b></p>
			</div>

			';
		}
		return $final_content;
	}
}

?>
