<?php
class Notifications extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent($starLimit=0, $endLimit=PAGE_DISPLAY_LIMITS, $page=1) {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$leftPanel=$this->getleftPanel();

		$this->db->pdoQuery("UPDATE tbl_notification SET is_read='y' WHERE user_id=?",array($_SESSION['userId']));

		$getNotificationData=$this->db->pdoQuery("SELECT * FROM tbl_notification WHERE user_id=? ORDER BY id DESC LIMIT $starLimit, $endLimit",array($_SESSION['userId']))->results();

		$SearchResultRows=$this->db->pdoQuery("SELECT * FROM tbl_notification WHERE user_id=?",array($_SESSION['userId']))->affectedrows();


		$hide_class = "";
		if($SearchResultRows == 0 ){
            $hide_class = '';
        }
        else{
            $hide_class = "hidden";
        }


		$NotificationLoopData="";

		if(empty($getNotificationData)){
			
			$html = (new MainTemplater(DIR_TMPL.$this->module."/no_notification-sd.skd"))->compile();

		}else{

			foreach ($getNotificationData as $key => $value) {
				
				$notification_loop_html = (new MainTemplater(DIR_TMPL.$this->module."/notification_loop_data-sd.skd"))->compile();

				$notification_loop_fields=array(
							"%MESSAGE%"=>$value['message'],
							"%ID%"=>base64_encode(base64_encode($value['id'])),
							"%NOTIFY_URL%" => SITE_URL.$value['notify_url'],
						);


				$NotificationLoopData .= str_replace(array_keys($notification_loop_fields), array_values($notification_loop_fields), $notification_loop_html);

			}
		}



		$totalRow =$SearchResultRows;

		$getTotalPages = ceil($totalRow / PAGE_DISPLAY_LIMITS);

		$totalPages = (($totalRow ==  PAGE_DISPLAY_LIMITS || $totalRow < PAGE_DISPLAY_LIMITS) ? 0 : ($getTotalPages == 1 ? 2 : $getTotalPages));


		$fields=array(
						"%LEFT_PANEL%"=>$leftPanel,
						"%NOTIFICATIONS%"=>$NotificationLoopData,
						"%TOTAL_PAGES%" => $totalPages,
					 	"%TOTAL_LIMIT%" => PAGE_DISPLAY_LIMITS,
					 	"%CURRENT_PAGE%" => $page,
					 	"%HIDECLASS%" => $hide_class
					);


		$html = str_replace(array_keys($fields), array_values($fields), $html);
		$data=array("main_content"=>$html,"products"=>$NotificationLoopData);
		return $data;
	}

}
?>