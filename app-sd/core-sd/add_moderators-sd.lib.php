<?php
class Moderators extends Home {
	function __construct($module = "",$id="", $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	public function getPageContent($search_data="all",$starLimit=0, $endLimit=PAGE_DISPLAY_LIMIT, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$getModularDetails=$this->db->pdoQuery("SELECT * FROM tbl_moderators WHERE id=?",array($this->id))->result();
	
		if(empty($this->id)){
			$ModeratorEmail='<div class="form-group">
                              <label class="control-label col-sm-3 col-md-3" for="moderatorEmail">'.MODERATOR_EMAIL_ID.'*</label>
                                <div class="col-sm-7 col-md-9">
                                    <input class="form-control" name="moderatorEmail" id="moderatorEmail" placeholder="'.MODERATOR_EMAIL_ID.'" type="email" />
                                </div>
                          </div>';

		}else{
			$ModeratorEmail="";
		}


		$statusActive=$statusDeactive="";
		if($getModularDetails['isActive']=='y'){
			$statusActive="checked";
		}else{
			$statusDeactive="checked";
		}


		$getModularPermissions=$this->db->pdoQuery("SELECT p.*,m.page_nm FROM tbl_moderators_permission as p
												INNER JOIN tbl_front_modules As m on m.id=p.module_id
												WHERE p.user_id=?",array($this->id))->results();

		$company_profile_add=$company_profile_edit=$company_profile_delete=$company_profile_view=$manage_product_add=$manage_product_edit=$manage_product_delete=$manage_product_view=$manage_quote_add=$manage_quote_edit=$manage_quote_delete=$manage_quote_view=$fav_buying_req_add=$fav_buying_req_edit=$fav_buying_req_delete=$fav_buying_req_view=$member_plan_add=$member_plan_edit=$member_plan_delete=$member_plan_view=$place_quote_add=$place_quote_edit=$place_quote_delete=$place_quote_view=$manage_order_add=$manage_order_edit=$manage_order_delete=$manage_order_view="";

		foreach ($getModularPermissions as $key => $value) {
			
			if($value['page_nm']=="supplier_profile-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $supplier_key => $supplier_value) {
						if($supplier_value=="1"){
							$company_profile_add="checked";
						}elseif($supplier_value=="2"){
							$company_profile_view="checked";
						}elseif($supplier_value=="3"){
							$company_profile_edit="checked";
						}elseif($supplier_value=="4"){
							$company_profile_delete="checked";
						}
					}
			}

			if($value['page_nm']=="manage_product-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $manage_product_key => $manage_product_value) {
						if($manage_product_value=="1"){
							$manage_product_add="checked";
						}elseif($manage_product_value=="2"){
							$manage_product_view="checked";
						}elseif($manage_product_value=="3"){
							$manage_product_edit="checked";
						}elseif($manage_product_value=="4"){
							$manage_product_delete="checked";
						}
					}
			}

			if($value['page_nm']=="manage_quote-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $manage_quote_key => $manage_quote_value) {
						if($manage_quote_value=="1"){
							$manage_quote_add="checked";
						}elseif($manage_quote_value=="2"){
							$manage_quote_view="checked";
						}elseif($manage_quote_value=="3"){
							$manage_quote_edit="checked";
						}elseif($manage_quote_value=="4"){
							$manage_quote_delete="checked";
						}
					}
			}

			if($value['page_nm']=="favourite_buying_req-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $favourite_buying_key => $favourite_buying_value) {
						if($favourite_buying_value=="1"){
							$fav_buying_req_add="checked";
						}elseif($favourite_buying_value=="2"){
							$fav_buying_req_view="checked";
						}elseif($favourite_buying_value=="3"){
							$fav_buying_req_edit="checked";
						}elseif($favourite_buying_value=="4"){
							$fav_buying_req_delete="checked";
						}
					}
			}


			if($value['page_nm']=="membership_plan-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $membership_plan_key => $membership_plan_value) {
						if($membership_plan_value=="1"){
							$place_quote_add="checked";
						}elseif($membership_plan_value=="2"){
							$place_quote_view="checked";
						}elseif($membership_plan_value=="3"){
							$place_quote_edit="checked";
						}elseif($membership_plan_value=="4"){
							$place_quote_delete="checked";
						}
					}
			}


			if($value['page_nm']=="placed_quotes-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $placed_quotes_key => $placed_quotes_value) {
						if($placed_quotes_value=="1"){
							$member_plan_add="checked";
						}elseif($placed_quotes_value=="2"){
							$member_plan_view="checked";
						}elseif($placed_quotes_value=="3"){
							$member_plan_edit="checked";
						}elseif($placed_quotes_value=="4"){
							$member_plan_delete="checked";
						}
					}
			}

			if($value['page_nm']=="supplier_manage_order-sd"){
					$permissions=explode(',', $value['permission']);
					foreach ($permissions as $manage_order_key => $manage_order_value) {
						if($manage_order_value=="1"){
							$manage_order_add="checked";
						}elseif($manage_order_value=="2"){
							$manage_order_view="checked";
						}elseif($manage_order_value=="3"){
							$manage_order_edit="checked";
						}elseif($manage_order_value=="4"){
							$manage_order_delete="checked";
						}
					}
			}
				
		}

		$selectAll="";
		if(!empty($company_profile_add) && !empty($company_profile_edit) && !empty($company_profile_delete) && !empty($company_profile_view) && !empty($manage_product_add) && !empty($manage_product_edit) && !empty($manage_product_delete) && !empty($manage_product_view) && !empty($manage_quote_add) && !empty($manage_quote_edit) && !empty($manage_quote_delete) && !empty($manage_quote_view) && !empty($fav_buying_req_add) && !empty($fav_buying_req_edit) && !empty($fav_buying_req_delete) && !empty($fav_buying_req_view) && !empty($member_plan_add) && !empty($member_plan_edit) && !empty($member_plan_delete) && !empty($member_plan_view) && !empty($place_quote_add) && !empty($place_quote_edit) && !empty($place_quote_delete) && !empty($place_quote_view) && !empty($manage_order_add) && !empty($manage_order_edit) && !empty($manage_order_delete) && !empty($manage_order_view)){
			$selectAll="checked";
		}


		
        $fields = array(
			"%MODERATOR_EMAIL%"=>$ModeratorEmail,
			"%MODERATOR_NAME%"=>!empty($getModularDetails['first_name'])?$getModularDetails['first_name']:"",
			"%STATUS_ACTIVE%"=>$statusActive,
			"%STATUSDE_ACTIVE%"=>$statusDeactive,
			"%COMPANY_PROFILE_ADD%"=>$company_profile_add,
			"%COMPANY_PROFILE_EDIT%"=>$company_profile_edit,
			"%COMPANY_PROFILE_DELETE%"=>$company_profile_delete,
			"%COMPANY_PROFILE_VIEW%"=>$company_profile_view,
			"%MANAGE_PRODUCT_ADD%"=>$manage_product_add,
			"%MANAGE_PRODUCT_EDIT%"=>$manage_product_edit,
			"%MANAGE_PRODUCT_DELETE%"=>$manage_product_delete,
			"%MANAGE_PRODUCT_VIEW%"=>$manage_product_view,
			"%MANAGE_QUOTE_ADD%"=>$manage_quote_add,
			"%MANAGE_QUOTE_EDIT%"=>$manage_quote_edit,
			"%MANAGE_QUOTE_DELETE%"=>$manage_quote_delete,
			"%MANAGE_QUOTE_VIEW%"=>$manage_quote_view,
			"%FAV_BUYING_REQ_ADD%"=>$fav_buying_req_add,
			"%FAV_BUYING_REQ_EDIT%"=>$fav_buying_req_edit,
			"%FAV_BUYING_REQ_DELETE%"=>$fav_buying_req_delete,
			"%FAV_BUYING_REQ_VIEW%"=>$fav_buying_req_view,
			"%MEMBER_PLAN_ADD%"=>$member_plan_add,
			"%MEMBER_PLAN_EDIT%"=>$member_plan_edit,
			"%MEMBER_PLAN_DELETE%"=>$member_plan_delete,
			"%MEMBER_PLAN_VIEW%"=>$member_plan_view,
			"%PLACE_QUOTE_ADD%"=>$place_quote_add,
			"%PLACE_QUOTE_EDIT%"=>$place_quote_edit,
			"%PLACE_QUOTE_DELETE%"=>$place_quote_delete,
			"%PLACE_QUOTE_VIEW%"=>$place_quote_view,
			"%MANAGE_ORDER_ADD%"=>$manage_order_add,
			"%MANAGE_ORDER_EDIT%"=>$manage_order_edit,
			"%MANAGE_ORDER_DELETE%"=>$manage_order_delete,
			"%MANAGE_ORDER_VIEW%"=>$manage_order_view,
			"%SELECT_ALL%"=>$selectAll

		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);


		return $html;
			
	}

	public function addModerator($data,$editId){
		extract($data);

		$objPost = new stdClass();	


		$objPost->first_name=!empty($moderatorName)? $moderatorName : "";
		$objPost->isActive=(!empty($moderatorStatus) && $moderatorStatus=="y")? "y" : "n";
		$objPost->user_slug = slug($objPost->first_name);
		
		if(empty($editId))	{
			$objPost->email =   !empty($moderatorEmail)? $moderatorEmail : "";
			$objPost->created_date = date("Y-m-d H:i:s");
			$objPost->isActive = 'n';
			$objPost->supplier_id = $_SESSION['userId'];
			$objPost->hash = generateRandString();
			$moderator_password = generateRandString(6);
			$objPost->password = md5($moderator_password);
			$bojPostArray = (array)$objPost;
			$LastInsertedModeratorId = $this->db->insert("tbl_moderators",$bojPostArray)->lastInsertId();

			$arrayCont = array("greetings"=>ucfirst($objPost->first_name),"EAMIL"=>$objPost->email,"PASSWORD"=>$moderator_password, "LINK"=>get_link('moderator_activation', base64_encode($objPost->email).'/'.base64_encode($objPost->hash)));
			sendMail($objPost->email, 'moderator_signup', $arrayCont);

		}else{
			$this->db->update("tbl_moderators", (array)$objPost, array('id'=>$editId));
		}
		
		end($action);
		$lastModule=key($action);

		$where="";

		if(empty($action)){
			$this->db->delete("tbl_moderators_permission",array("user_id"=>$editId));
		}else{
			foreach($action as $modules => $permission){
				$objPost1 = new stdClass();
				$permission_id="";				

				if(empty($editId)){
					$objPost1->user_id = $LastInsertedModeratorId;
				}else{
					$objPost1->user_id = $editId;			
				}



				$objPost1->module_id = getTableValue("tbl_front_modules", "id", array("page_nm"=>$modules));
				$objPost1->permission = implode(',', $permission);

				$exist = getTableValue('tbl_moderators_permission', 'id', array('user_id'=>$objPost1->user_id, 'module_id'=>$objPost1->module_id));

				if(!empty($exist)){
					if(empty($where)){
						$where.="WHERE id != '".$exist."' AND user_id = '".$objPost1->user_id."'";
					}else{
						$where.=" AND id != '".$exist."'";
					}
				}
				
				if(!empty($exist)) {
					$this->db->update("tbl_moderators_permission", (array)$objPost1, array('id'=>$exist));
				} else {
					
					$objPost1->created_date = date('Y-m-d H:i:s');
					$permission_id=$this->db->insert("tbl_moderators_permission", (array)$objPost1)->lastInsertId();
				}
				
				if(!empty($permission_id)){
					if(empty($where)){
						$where.="WHERE id != '".$permission_id."'";
					}else{
						$where.=" AND id != '".$permission_id."'";
					}
				}

				if(!empty($editId)){
					if($lastModule==$modules){
						$this->db->exec("DELETE FROM tbl_moderators_permission ".$where."");
					}
				}
				

			}

		}
		if(empty($editId))	{
			$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>MODERATOR_ADDED_SUCCESSFULLY));
			RedirectPage(SITE_URL.'manage-moderators');
		}else{
			$toastr_message = $_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>MODERATOR_UPDATED_SUCCESSFULLY));
			RedirectPage(SITE_URL.'manage-moderators');
		}
	}

}
?>