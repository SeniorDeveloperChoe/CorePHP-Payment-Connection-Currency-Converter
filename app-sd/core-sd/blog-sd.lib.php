<?php
class Blog extends Home {
	function __construct($module = "", $slug = 0, $type = '') {
		foreach ($GLOBALS AS $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->slug = $slug;
		$this->type = $type;
	}
	public function getPageContent() {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/blog-sd.skd"))->compile();		
		$fields = array(
			"%BLOG_POST%" => $this->getBlogPost(),
			"%BLOG_SIDEBAR%" => $this->getBlogSidebar()
		);
		$final_content = str_replace(array_keys($fields), array_values($fields), $main_content);
		return $final_content;
	}
	public function getBlogPost(){	
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/"."blog-post-sd.skd"))->compile();
		$where = "tbp.isActive = ?";
        $wArray = array("y");
		if($this->type == 'category'){
			if($this->slug != '') {
				$where .= " AND tbc.category_slug = ?";
				$wArray[] = $this->slug;
			}
		}
		if($this->type == 'tag'){
			if($this->slug != '') {
				$get_tag = $this->db->pdoQuery("SELECT id FROM tbl_blog_tags WHERE tag_slug = ?",array($this->slug))->result();
				$where .= " AND FIND_IN_SET(?, tbp.tags_id)";
				$wArray[] = $get_tag['id'];
			}
		}
		$get_posts = $this->db->pdoQuery("SELECT tbp.*,tbc.category_name,tbc.category_slug FROM tbl_blog_post as tbp
			INNER JOIN tbl_blog_category as tbc ON (tbc.id = tbp.category_id) WHERE $where ORDER BY id DESC",$wArray)->results();

		if(!empty($get_posts)){
			foreach ($get_posts as $key => $value) {
				$fields = array(
						"%POST_URL%" => SITE_URL."blog/".$value['post_slug'],
						"%CATEGORY_URL%" => SITE_URL.'blog/category/'.$value['category_slug'],
						"%CATEGORY%" => ucwords(filtering($value['category_name'],'output')),
						"%BLOG_IMAGE%" => SITE_UPD.'blog_post/'.$value['post_image'],
						"%BLOG_POSTED_DATE%" => date(DATE_FORMAT,strtotime($value['created_date'])),
						"%TITLE%"=> ucfirst(filtering($value['title'],'output')),
						"%BLOG_DESC%" => filtering(TruncateString($value['post_desc'], 280),'output'),
						"%BLOG_TAGS%" => $this->getPostTags($value['tags_id'])
					);	
				$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
			}
		}else{
			$final_content = '<h4>No any post found</h4>';
		}
		return $final_content;
	}
	
}

?>
