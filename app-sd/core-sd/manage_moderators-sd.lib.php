<?php
class Moderators extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();
		$leftPanel=$this->getleftPanel();

		$searchKeyword=$where="";

		if(!empty($_GET['keywords']) && $_GET['keywords']!="undefined"){
			$searchKeyword=$_GET['keywords'];
			$where = "WHERE (supplier_id = ".$_SESSION['userId'].") AND (first_name LIKE '%".$_GET['keywords']."%' OR email LIKE '%".$_GET['keywords']."%')";
		}
		
		if($search_data == 'all' || empty($search_data)){
			if(empty($where)){
				$where = "WHERE supplier_id = ".$_SESSION['userId'];
			}
		}else{
			$where = "WHERE (supplier_id = ".$_SESSION['userId'].") AND (first_name LIKE '%".$search_data."%' OR email LIKE '%".$search_data."%')";
		}
		
		$getModeratorsData=$this->db->pdoQuery("SELECT * FROM tbl_moderators ".$where." ORDER BY id desc LIMIT $starLimit, $endLimit")->results();

	$SearchResultRows=$this->db->pdoQuery("SELECT * FROM tbl_moderators ".$where." ORDER BY id desc")->affectedrows();
	$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }

		$moderators_loop_data="";

		if(empty($getModeratorsData)){
			$html = (new MainTemplater(DIR_TMPL.$this->module."/no_moderators-sd.skd"))->compile();

		}else{
				foreach ($getModeratorsData as $key => $value) {
				$moderator_loop_content=(new MainTemplater(DIR_TMPL.$this->module."/moderator_loop_data-sd.skd"))->compile();
				$moderator_name=!empty($value['first_name'])?$value['first_name']:"";
				$moderator_email=!empty($value['email'])?$value['email']:"";
				$moderator_created_date=!empty($value['created_date'])?date('dS F Y', strtotime($value['created_date'])):"";
				$moderator_status=(!empty($value['isActive']) && $value['isActive']=="y")?ACTIVATE:DEACTIVE;

				$getPrivilegeModule=$this->db->pdoQuery("SELECT fm.page_nm
														FROM tbl_moderators_permission AS mp
														INNER JOIN tbl_front_modules AS fm ON fm.id=mp.module_id
														WHERE user_id=?
														",array($value['id']))->results();
				$moduleLeave="";
				$PrevilegeModules="";
				foreach ($getPrivilegeModule as $key => $module_value) {
						if($module_value['page_nm']=='supplier_profile-sd'){
							$moduleLeave=COMPANY_PROFILE;

						}else if($module_value['page_nm']=='manage_product-sd'){
							$moduleLeave=MANAGE_PRODUCTS;
		
						}else if($module_value['page_nm']=='membership_plan-sd'){
							$moduleLeave=MEMBERSHIP_PLAN;
		
						}else if($module_value['page_nm']=='manage_quote-sd'){
							$moduleLeave=MY_QUOTES;

						}else if($module_value['page_nm']=='favourite_buying_req-sd'){
							$moduleLeave=FAVORITE_BUYING_REQUESTS;
		
						}else if($module_value['page_nm']=='placed_quotes-sd'){
							$moduleLeave=QUOTE;
		
						}else if($module_value['page_nm']=='supplier_manage_order-sd'){
							$moduleLeave=MY_ORDERS;
		
						}

						$PrevilegeModules.='<label class="label label-default">'.$moduleLeave.'</label>';
				}
				
				$loop_fields = array(	
							"%ID%"=>base64_encode(base64_encode($value['id'])),
							"%MODERATOR_NAME%"=>$moderator_name,
							"%MODERATOR_EMAIL%"=>$moderator_email,
							"%MODERATOR_CREATED_DATE%"=>$moderator_created_date,
							"%MODERATOR_STATUS%"=>$moderator_status,
							"%PREVILEGE_MODULES%"=>$PrevilegeModules,
							"%SLUG%"=>$value['user_slug']
							);
				$moderators_loop_data .= str_replace(array_keys($loop_fields), array_values($loop_fields), $moderator_loop_content);
			}
		}
		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMITS);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMITS || $totalRow < PAGE_DISPLAY_LIMITS) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));


		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%MODERATORS_LOOP_DATA%"=>$moderators_loop_data,
			"%HIDECLASS%" => $hide_class,
			"%TOTAL_PAGES%" => $totalPages,
			"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMIT,
			"%CURRENT_PAGE%" => $page,
			"%SEARCH_KEYWORD%"=>$searchKeyword
		);
		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$moderators_loop_data,"totalPages"=>$totalPages,"total_limit"=>PAGE_DISPLAY_LIMITS,"current_page"=>$page,"hide_class"=>$hide_class);
		return $data;
	}
}
?>