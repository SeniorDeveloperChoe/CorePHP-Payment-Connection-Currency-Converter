<?php
class MembershipPlan extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}
	
	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();

		$leftPanel=$this->getleftPanel();
				  $getMembershipPlanData = $this->db->pdoQuery('SELECT 
				  			res.id,res.plan_name,res.sequence,res.plan_price_per_month,res.priority_ranking,res.product_post,   res.product_mark_premium,res.place_quote,res.no_place_quote,res.product_showcase,res.gold_icon_display,res.sub_account,res.store_front,res.isactive,res.created_date,res.plan_id,res.is_expired,res.plan_purchased_date
					FROM (
						SELECT mp.id,mp.plan_name,mp.sequence,mp.plan_price_per_month,mp.priority_ranking,mp.product_post,mp.product_mark_premium,mp.place_quote,mp.no_place_quote,mp.product_showcase,mp.gold_icon_display,mp.sub_account,mp.store_front,mp.isactive,mp.created_date,u.plan_id,u.is_expired,u.plan_purchased_date FROM tbl_users as u JOIN tbl_membership_plan as mp ON(u.plan_id = mp.id) WHERE u.id='.$_SESSION["userId"].'
    				UNION ALL     				
						SELECT m.id,m.plan_name,m.sequence,m.plan_price_per_month,m.priority_ranking,m.product_post,m.product_mark_premium,m.place_quote,m.no_place_quote,m.product_showcase,m.gold_icon_display,m.sub_account,m.store_front,m.isactive,m.created_date,NULL as plan_purchased_date,NULL as plan_id,NULL as is_expired FROM tbl_membership_plan as m WHERE m.isactive="y"
    						) as res
    				GROUP BY res.id ORDER BY res.sequence')->results();
		$plan_loop_data='';
		$plan_name_content=$plan_price_per_month_content=$priority_ranking_content=$product_post_content=$product_mark_premium_content=$place_quote_content=$no_place_quote_content=$product_showcase_content=$gold_icon_display_content=$sub_account_content=$store_front_content=$plan_btn_id_content=$select_plan_duration=$plan_subsribe_date="";

		$getCurrentPlanId=$this->db->pdoQuery("SELECT plan_id,is_expired FROM tbl_users WHERE isActive=? ANd id=?",array("y",$_SESSION['userId']))->result();
		foreach ($getMembershipPlanData as $key => $value) {
			$priority_extension="";
			if(!empty($value['priority_ranking']) && $value['priority_ranking']=='1'){
				$priority_extension="st";
			}elseif(!empty($value['priority_ranking']) && $value['priority_ranking']=='2'){
				$priority_extension="nd";
			}elseif(!empty($value['priority_ranking']) && $value['priority_ranking']=='3'){
				$priority_extension="rd";
			}else{
				$priority_extension="th";
			}


			$plan_name=!empty($value['plan_name'])?$value['plan_name']:"-";
			$plan_price_per_month=!empty($value['plan_price_per_month'] || $value['plan_price_per_month']=="0")?$value['plan_price_per_month']:"-";
			$priority_ranking=!empty($value['priority_ranking'])?$value['priority_ranking']:"-";
			$product_post=!empty($value['product_post'])?$value['product_post']:"-";
			$product_mark_premium=!empty($value['product_mark_premium'])?$value['product_mark_premium']:"-";
			$place_quote=($value['place_quote']=="y")?"Y":"N";
			$no_place_quote=!empty($value['no_place_quote'])?$value['no_place_quote']:"-";
			$product_showcase=!empty($value['product_showcase'])?$value['product_showcase']:"-";
			$gold_icon_display=($value['gold_icon_display']=="y")?"Y":"N";
			$sub_account=($value['sub_account']=="y")?"Y":"N";
			$store_front=($value['store_front']=="y")?"Y":"N";
			$planDate=!empty($value['plan_purchased_date']) && $value['plan_purchased_date'] != '0000-00-00 00:00:00' ? $value['plan_purchased_date']:"--";
			
			$plan_date=$planDate!= '--' ? date('dS F Y', strtotime($planDate)) : '--';
			$plan_btn_id=base64_encode(base64_encode($value['id']));

			$yearly_price=$plan_price_per_month*12;

			$currentPackage_class="";
			if($getCurrentPlanId['plan_id']==$value['id']){
				$currentPackage_class="chosen-pkg";
			}

			$plan_name_content.='<th class="'.$value['sequence'].' '.$currentPackage_class.'">
                                    '.$plan_name.'
                                </th>';
            $plan_price_per_month_content.='<td class="plan1 price-month">
                                                 '.$plan_price_per_month.' '.CURRENCY_CODE.' 
                                            </td>';
            $priority_ranking_content.='<td class="plan1">
                                            '.$priority_ranking.'<sup>'.$priority_extension.'</sup>
                                        </td>';

            $product_post_content.=' <td class="plan1">
                                        '.$product_post.'
                                    </td>';

            $product_mark_premium_content.='<td class="plan1">
                                                '.$product_mark_premium.'
                                            </td>';
            $place_quote_content.='<td class="plan1">
	                                    '.$place_quote.'
	                                </td>';
            $no_place_quote_content.='<td class="plan1">
                                        '.$no_place_quote.'
                                    </td>';

            $product_showcase_content.='<td class="plan1">
                                        '.$product_showcase.'
                                    </td>';

            $gold_icon_display_content.='<td class="plan1">
                                        '.$gold_icon_display.'
                                    </td>';

            $sub_account_content.='<td class="plan1">
                                        '.$sub_account.'
                                    </td>';

            $store_front_content.='<td class="plan1">
                                        '.$store_front.'
                                    </td>';

            $plan_subsribe_date.='<td class="plan1">
                                        '.$plan_date.'
                                    </td>';
            if($plan_price_per_month=="0" || empty($plan_price_per_month)){
            	$select_plan_duration.='<td class="plan1">
                                       
                                    </td>';
            } else {
	            $select_plan_duration.='<td class="plan1">
	                                        <select class="form-control membership'.$key.'">
	                                            <option value="m">1 month - '.$plan_price_per_month.' '.CURRENCY_CODE.'</option>
	                                            <option value="y">1 year -  '.$yearly_price.' '.CURRENCY_CODE.'</option>
	                                        </select>
	                                    </td>';
            }
             $free_plan="";
            if($plan_price_per_month=="0" || empty($plan_price_per_month)){
            	$free_plan="freeplan";
            }

            if(!empty($_SESSION['moderatorId'])){
            	if(checkModeratorAction($this->module,"edit")){
            		if($free_plan=="freeplan" && $value['is_expired']!="y")
            		{
            			$plan_btn_id_content .='';
            		}else{
        				$plan_btn_id_content .='<td class="plan1">
                                    <a id="'.$plan_btn_id.'" data-id='.$key.' class="btn btn-system btn-block '.$free_plan.' upgradeNow">'.UPGRADE_NOW.'</a>
                                </td>';
            		}
            	
            	}else{
            		$plan_btn_id_content .= "";
            	}
            }else{
            	
            	if($free_plan=="freeplan" && $value['is_expired']!="y"){
            		$plan_btn_id_content .='';
            	}else{
            		$plan_btn_id_content .='<td class="plan1">            							
                                        <a id="'.$plan_btn_id.'" data-id='.$key.' class="btn btn-system btn-block '.$free_plan.'  upgradeNow">'.UPGRADE_NOW.'</a>
                                    </td>';
            	}
            	
            }

		}

		$planExpireDate = getTableValue("tbl_users", "plan_duration", array('id' => $_SESSION["userId"]));
		$expirePlan = "";
		if (!empty($planExpireDate) && $planExpireDate != '0000-00-00 00:00:00') {
			$expirePlan = LBL_MEMBERSHIP_PLAN_EXPIRE .' '.date('dS F Y', strtotime($planExpireDate));
		}

		$fields = array(
			"%LEFT_PANEL%"=>$leftPanel,
			"%PLAN_LOOP_DATA%"=>$plan_loop_data,
			"%PLAN_NAME_CONTENT%"=>$plan_name_content,
			"%PLAN_PRICE_PER_MONTH_CONTENT%"=>$plan_price_per_month_content,
			"%PRIORITY_RANKING_CONTENT%"=>$priority_ranking_content,
			"%PRODUCT_POST_CONTENT%"=>$product_post_content,
			"%PRODUCT_MARK_PREMIUM_CONTENT%"=>$product_mark_premium_content,
			"%PLACE_QUOTE_CONTENT%"=>$place_quote_content,
			"%NO_PLACE_QUOTE_CONTENT%"=>$no_place_quote_content,
			"%PRODUCT_SHOWCASE_CONTENT%"=>$product_showcase_content,
			"%GOLD_ICON_DISPLAY_CONTENT%"=>$gold_icon_display_content,
			"%SUB_ACCOUNT_CONTENT%"=>$sub_account_content,
			"%STORE_FRONT_CONTENT%"=>$store_front_content,
			"%PLAN_BTN%"=>$plan_btn_id_content,
			"%PLAN_DATE%"=>$plan_subsribe_date,
			"%SELECT_PLAN_DURATION%"=>$select_plan_duration,
			"%EXPIRE_PLAN%"=>$expirePlan,
		);

		$html = str_replace(array_keys($fields), array_values($fields), $html);
		return $html;
	}

}
?>