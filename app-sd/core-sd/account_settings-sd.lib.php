<?php
class AccountSettings extends Home {
	function __construct($module = "", $preflang) {
		$this->module = $module;
		$this->preflang = $preflang;
		parent ::__construct();
	}

	public function getPageContent(){
		$main_content = '';
		$temp_name = ($this->module."/".$this->module.".skd");
	    $main_content = (new MainTemplater(DIR_TMPL.$temp_name))->compile();

	    $user_info = $this->db->select('tbl_users', array('pref_email_lang','paypal_id'), array('id'=>$this->sessUserId))->result();
	    $getLangName = getTableValue("tbl_language", "languageName", array("id"=>$user_info["pref_email_lang"]));

		$getnotifications = $this->db->pdoQuery("SELECT * FROM tbl_users WHERE id=$this->sessUserId")->result();
		$pro_notify_1 = '';
		$order_request_noti = '';
		$buying_request_noti = '';
		$trade_alert_noti = '';
		$order_accept_reject_noti = '';
		$request_changed_noti = '';
		$order_dispute_noti = '';

		$pro_notify_1 = $getnotifications['browser_noti'] == 'y' ? 'checked' : '';
		$order_request_noti = $getnotifications['order_request_noti'] == 'y' ? 'checked' : '';
		$buying_request_noti = $getnotifications['buying_request_noti'] == 'y' ? 'checked' : '';
		$trade_alert_noti = $getnotifications['trade_alert_noti'] == 'y' ? 'checked' : '';
		$order_accept_reject_noti = $getnotifications['order_accept_reject_noti'] == 'y' ? 'checked' : '';
		$request_changed_noti = $getnotifications['request_changed_noti'] == 'y' ? 'checked' : '';
		$order_dispute_noti = $getnotifications['order_dispute_noti'] == 'y' ? 'checked' : '';

		$getUserEmail = getTableValue("tbl_users", "email", array("id"=>$this->sessUserId));
		$getUserSubStatus = getTableValue("tbl_subscribers", "status", array("email"=>$getUserEmail));

		$langNote = (!empty($user_info['pref_email_lang']) || ($user_info['pref_email_lang'] != 0) ? GET_SELECTED_PREF_LANG." ".$getLangName."" : '');
		
		$fields = array(
			"%LEFT_PANEL%" => $this->getleftPanel(),
			"%PRO_NOTIFY_1%" => $pro_notify_1,
			"%PAYPAL_ID%" => !empty($user_info['paypal_id']) ? $user_info['paypal_id'] : '',
			"%NEWSLETTER_STATUS%" => (($getUserSubStatus=="a") ? "checked='checked'" : ""),
			"%ORDER_REQUEST_NOTI%" => $order_request_noti,
			"%BUYING_REQUEST_NOTI%" => $buying_request_noti,
			"%TRADE_ALERT_NOTI%" => $trade_alert_noti,
			"%ORDER_ACCEPT_REJECT_NOTI%" => $order_accept_reject_noti,
			"%REQUEST_CHANGED_NOTI%" => $request_changed_noti,
			"%ORDER_DISPUTE_NOTI%" => $order_dispute_noti
		);

		$main_content = str_replace(array_keys($fields),array_values($fields),$main_content);
		return $main_content;
	}
	
	public function getLanguages() {
		$languages_options = '';
		$languages = $this->db->select('tbl_language', array('*'), array('status'=>'a'))->results();
		if(!empty($languages)) {
			foreach ($languages as $key => $value) {
				$select = (($this->preflang==$value['id']) ? "selected='selected'" : '');
				$languages_options .= '<option value="'.$value['id'].'" '.$select.'>'.$value['languageName'].'</option>';
			}
		}
		return $languages_options;
	}
}
?>