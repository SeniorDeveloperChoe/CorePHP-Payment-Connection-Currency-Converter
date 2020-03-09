<?php
class POSTDETAILS extends Home {
	function __construct($module = "", $slug = 0) {
		foreach ($GLOBALS AS $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->slug = $slug;
	}
	public function getPageContent() {
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/post-details-sd.skd"))->compile();
		$get_post_detail = $this->db->pdoQuery("SELECT tbp.*,tbc.category_name,tbc.category_slug,tbp.id as postid FROM tbl_blog_post as tbp
		INNER JOIN tbl_blog_category as tbc ON(tbp.category_id = tbc.id) WHERE tbp.post_slug = ? ",array($this->slug))->result();
		$getcomments_count = $this->db->pdoQuery("SELECT id FROM tbl_post_comments WHERE post_id = ? ",array($get_post_detail['postid']))->affectedRows();
		$fields = array(
			"%POST_IMAGE%" => SITE_UPD.'blog_post/'.$get_post_detail['post_image'],
			"%POST_POSTED_DATE%" => date(DATE_FORMAT,strtotime($get_post_detail['created_date'])),
			"%TITLE%"=> ucfirst(filtering($get_post_detail['title'],'output')),
			"%CATEGORY%" => ucwords(filtering($get_post_detail['category_name'],'output')),
			"%CATEGORY_URL%" => SITE_URL."blog/category/".$get_post_detail['category_slug'],
			"%BLOG_DESC%" => filtering($get_post_detail['post_desc'],'output'),
			"%POSTID%" => $get_post_detail['postid'],
			"%USER_ID%" => $this->sessUserId,
			"%MSG_CLASS%" => $this->sessUserId > 0 ? 'hide' : '', 
			"%USER_COMMENTS%" => $this->getUserComments($get_post_detail['postid']),
			"%BLOG_SIDEBAR%" => $this->getBlogSidebar(),
			"%TOTAL_COMMENTS%" => $getcomments_count,
			"%POST_URL%" => SITE_URL."blog-postdetail/".$get_post_detail['post_slug'],
			"%BLOG_TAGS%" => $this->getPostTags($get_post_detail['tags_id'])
		);
		$final_content = str_replace(array_keys($fields), array_values($fields), $main_content);
		return $final_content;
	}
	public function getUserComments($postid = 0){		
		$main_content = $final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/"."user-comments-sd.skd"))->compile();
		$get_comments = $this->db->pdoQuery("SELECT * FROM tbl_post_comments WHERE post_id = ? AND comment_id = ? ORDER BY id DESC ",array($postid,0))->results();
		if(!empty($get_comments)){
			foreach ($get_comments as $key => $value) {
				$get_user = $this->db->pdoQuery("SELECT *,CONCAT(first_name,' ',last_name) AS userName FROM tbl_users WHERE id = ?",array($value['user_id']))->result();
				$profile_img = !empty($get_user['profile_img']) ? $get_user['profile_img'] : 'no_user_image.png';
				if (!empty($get_user['profile_img'])) {
					$profile_img = SITE_UPD.'users-sd/'.$get_user['id'].'/'.$profile_img;
				} else {
					$profile_img = SITE_UPD.'/'.$profile_img;
				}
				$fields = array(
						"%USER_NAME%" => ucwords(filtering($get_user['userName'],'output')),
						"%COMMENT_POST_DATE%" => date(DATE_FORMAT,strtotime($value['created_date'])),
						"%USER_IMG%" => $profile_img,
						"%COMMENT%"=> filtering($value['comment'],'output'),
						"%ID%" => $value['id'],
						"%REPLY_COMMENTS%" => $this->getReplayComment($value['id'])
					);	
				$final_content .= str_replace(array_keys($fields),array_values($fields),$main_content);
			}
		}else{
			$final_content = "<h4>Post has no comments yet</h4>";
		}
		return $final_content;
	}
	public function getReplayComment($commentid = 0){
		$final_content = '';
		$main_content = (new MainTemplater(DIR_TMPL.$this->module."/reply-comments-sd.skd"))->compile();
		$get_replay_comments = $this->db->pdoQuery("SELECT * FROM tbl_post_comments WHERE comment_id = ? ORDER BY id DESC",array($commentid))->results();
		if(!empty($get_replay_comments)){
			foreach($get_replay_comments AS $key => $value) {
				$get_user = $this->db->pdoQuery("SELECT *,CONCAT(first_name,' ',last_name) AS userName FROM tbl_users WHERE id = ?",array($value['user_id']))->result();
				$profile_img = !empty($get_user['profile_img']) ? $get_user['profile_img'] : 'no_user_image.png';
				if (!empty($get_user['profile_img'])) {
					$profile_img = SITE_UPD.'users-sd/'.$get_user['id'].'/'.$profile_img;
				} else {
					$profile_img = SITE_UPD.'/'.$profile_img;
				}
				$fields = array(
					"%USER_NAME%" => ucwords(filtering($get_user['userName'],'output')),
					"%COMMENT_POST_DATE%" => date(DATE_FORMAT,strtotime($value['created_date'])),
					"%USER_IMG%" => $profile_img,
					"%COMMENT%"=> filtering($value['comment'],'output'),
					"%ID%" => $value['id'],
				);
				$final_content .= str_replace(array_keys($fields), array_values($fields), $main_content);
			}
		}
		return $final_content;
	}
	
}

?>
