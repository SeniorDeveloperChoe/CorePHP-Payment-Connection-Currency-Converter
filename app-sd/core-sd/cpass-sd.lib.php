<?php
class cPass {
	function __construct($module = "", $id = 0) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	public function getPageContent() {
		$html = new MainTemplater(DIR_TMPL . "{$this->module}/{$this->module}.skd");
		$html = $html->compile();
		return $html;
	}
	public function cPassSubmit($data){
		extract($data);
		if ($new_password == $cur_password) {
			$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> MSG_SAME_AS_OLD_PASWD));
			
		} else {
			$count = $this->db->count("tbl_users", array("id" => $this->sessUserId, "password" => md5($cur_password)));
			if ($count == 0) {
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> MSG_PLS_MAKE_SURE_PASWD));
				
			} else {
				if ($new_password == $conf_password) {
					$cnt = $this->db->pdoQuery("update tbl_users set password=? where id=?", array(md5($new_password), $this->sessUserId))->affectedRows();
					if ($cnt > 0) {
						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=> MSG_PASWD_CHANGED_SUCCESSFULLY));
					}
				} else {
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> MSG_CNFM_PASS_DOES_NOT_MATCH));
				}
			}
		}
	}
}

?>
