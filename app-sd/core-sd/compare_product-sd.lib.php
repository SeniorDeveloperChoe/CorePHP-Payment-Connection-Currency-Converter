<?php
class CompareProduct extends Home {
	function __construct($module = "", $id = 0, $result="") {
		$this->module = $module;
		$this->id = $id;
		$this->result = $result;
		parent::__construct();
	}

	public function getPageContent() {
		$html = (new MainTemplater(DIR_TMPL . "$this->module/$this->module.skd"))->compile();


		$getTotalComapreProduct=$this->db->pdoQuery("SELECT count(id) AS totalCompareProduct FROM tbl_product_compare WHERE ipAddress = ?",array(get_ip_address()))->result();

		$catName="ca.categoryName_".$this->curr_language." AS catName";
		$subcatName="s.subcatName_".$this->curr_language." AS subCatName";
		$unitName="un.unit_value_".$this->curr_language." AS unitName";

		$getTotalComapreProductData=$this->db->pdoQuery("SELECT c.*,p.product_slug,p.product_image,p.product_title,p.fob,p.order_quantity,p.min_price,p.max_price,p.supply_ability,p.product_location,p.product_tags,p.mark_premium,p.id AS productId,co.company_name,co.business_type_id,co.registration_year,co.registered_year,co.location AS CompanyLocation,co.created_date AS CompanyAddedDate,co.total_annual_revenue,co.response_time,co.response_rate,$catName,$subcatName,$unitName
														FROM tbl_product_compare AS c
														INNER JOIN tbl_products AS p ON p.id=c.product_id
														INNER JOIN tbl_product_category AS ca ON ca.id=p.cat_id 
														INNER JOIN tbl_product_subcategory AS s ON s.id=p.sub_cat_id 
														INNER JOIN tbl_unit_value AS un ON un.id=p.unit_id
														LEFT JOIN tbl_company AS co ON co.user_id=p.user_id
														WHERE c.ipAddress = ?",array(get_ip_address()))->results();

		$compare_head_loop_data=$compareProducts_data=$tags_html="";
		$fob_html=$min_order_quantity_html=$getTotalVisits_html=$getTotalInquiry_html=$getPrice_html=$supplyAbility_html=$productLocation_html=$premiumLabel_html=$tags_html="";
		$companyName=$businessTypes=$establish_year=$comapny_location=$totalRevenue=$responseRate=$registrationDate="";
		foreach ($getTotalComapreProductData as $key => $value) {

					$ProductImgs=explode(',',$value['product_image']);
					$getProductImg=!empty($ProductImgs)?SITE_PRODUCT_IMG.$ProductImgs[0]:SITE_UPD.'no_prodcut_img.png';

				/*-----Get Compare Product Header Data-----*/

					$compare_head_loop_html = (new MainTemplater(DIR_TMPL . "$this->module/compare_head_loop-sd.skd"))->compile();
					$compare_head_loop_fileds=array(
						"%COMAPREPRODUCT_IMG%"=>$getProductImg,
						"%COMPARE_ID%"=>base64_encode(base64_encode($value['id'])),
						"%PRODCUT_NO%"=>"compareItem".$key
					);
					$compare_head_loop_html=str_replace(array_keys($compare_head_loop_fileds), array_values($compare_head_loop_fileds), $compare_head_loop_html);

					$compare_head_loop_data.=$compare_head_loop_html;

				/*-----End Get Compare Product Header Data Saction-----*/



				/*-----Get Compare Products Data-----*/

					$compareProducts_loop_html = (new MainTemplater(DIR_TMPL . "$this->module/compareProducts-sd.skd"))->compile();

					$compareProducts_loop_fields=array(
						"%COMAPREPRODUCT_IMG%"=>$getProductImg,
						"%PRODUCT_TITLE%"=>$value['product_title'],
						"%PRODUCT_SLUG%"=>$value['product_slug'],
						"%CAT_NAME%"=>$value['catName'],
						"%SUBCAT_NAME%"=>$value['subCatName'],
						"%PRODCUT_NO%"=>"compareItem".$key,
						"%COMPARE_ID%"=>base64_encode(base64_encode($value['id']))
					);
					$compareProducts_loop_html=str_replace(array_keys($compareProducts_loop_fields), array_values($compareProducts_loop_fields), $compareProducts_loop_html);

					$compareProducts_data.=$compareProducts_loop_html;

				/*-----End Get Compare Products Data Saction*/


				/*-----Get Product Features-----*/

				$getTotalVisits=$getTotalInquiry="";
				if(!empty($value['productId'])){
					$getTotalVisits=$this->db->pdoQuery("SELECT count(id) AS TotalVisits FROM tbl_product_views WHERE product_id = ?",array($value['productId']))->result();
					$getTotalInquiry=$this->db->pdoQuery("SELECT count(id) AS TotalInquiry FROM tbl_messages WHERE msgIs='i' AND product_id=?",array($value['productId']))->result();	

					$getTotalVisits=($getTotalVisits['TotalVisits']==0)?0:$getTotalVisits['TotalVisits'];
					$getTotalInquiry=($getTotalInquiry['TotalInquiry']==0)?0:$getTotalInquiry['TotalInquiry'];
				}


				$fob=($value['fob']=='y')? Y:N;
				$min_order_quantity=!empty($value['order_quantity'])?$value['order_quantity']:"-";
				$minPrice=!empty($value['min_price'])?$value['min_price']:"-";
				$maxPrice=!empty($value['max_price'])?$value['max_price']:"-";
				$supplyAbility=!empty($value['supply_ability'])?$value['supply_ability']:"-";
				$productLocation=!empty($value['product_location'])?$value['product_location']:"-";
				$product_tags=!empty($value['product_tags'])?$value['product_tags']:"-";
				$premiumLabel=($value['mark_premium']=='y')? Y:N;

				$fob_html.='<td class="compareItem'.$key.' hideall">'.$fob.'</td>';
				$min_order_quantity_html.='<td class="compareItem'.$key.' hideall">'.$min_order_quantity.'</td>';
				$getTotalVisits_html.=' <td class="compareItem'.$key.' hideall">'.$getTotalVisits.'</td>';
				$getTotalInquiry_html.=' <td class="compareItem'.$key.' hideall">'.$getTotalInquiry.'</td>';
				$getPrice_html.='<td class="compareItem'.$key.' hideall">'.$minPrice.'-'.$maxPrice.CURRENCY_CODE.'/'.$value['unitName'].'</td>';
				$supplyAbility_html.='<td class="compareItem'.$key.' hideall">'.$supplyAbility.'</td>';
				$productLocation_html.='<td class="compareItem'.$key.' hideall">'.$productLocation.'</td>';
				$premiumLabel_html.='<td class="compareItem'.$key.' hideall">'.$premiumLabel.'</td>';
				
				if(!empty($product_tags)){
				$tag="";
				$tag=explode(',',$product_tags);
				$tags_html.='<td class="compareItem'.$key.' hideall">';
				foreach ($tag as $tag_key => $tagValue) {
						if (!empty($tagValue)) {
							$tags_html.='<label class="label label-default">'.$tagValue.'</label>';
						}
				}
				$tags_html.='</td>';
				$companyName.='<td class="compareItem'.$key.' hideall">
                                    <div class="media">
                                        <div class="pull-left">
                                            <span class="verify"><i class="fa fa-check-circle"></i></span>
                                        </div>
                                        <div class="media-body">
                                            <a title="" class="company-name">
                                            '.$value['company_name'].'
                                            </a>
                                        </div>
                                    </div>
                                </td>';



                  if(empty($value['response_rate'])){
                  	$response_Rate="-";
                  }else{
                  	$response_Rate=$value['response_rate']."%";
                  }
                $businessType=getBusinessType($value['business_type_id']);
                $businessTypes.='<td class="compareItem'.$key.' hideall">'.$businessType.'</td>';
                $establish_year.='<td class="compareItem'.$key.' hideall">'.$value['registered_year'].'</td>';
                $comapny_location.='<td class="compareItem'.$key.' hideall">'.$value['CompanyLocation'].'</td>';
                $totalRevenue.='<td class="compareItem'.$key.' hideall">'.$value['total_annual_revenue'].'</td>';
                $responseRate.='<td class="compareItem'.$key.' hideall">'.$response_Rate.'</td>';
                $registrationDate.='<td class="compareItem'.$key.' hideall">'.date('dS F Y', strtotime($value['CompanyAddedDate'])).'</td>';

			}

			/*-----End Get Product Features Saction-----*/




		}
		$final_total_cmp_product =!empty($getTotalComapreProduct['totalCompareProduct'])?$getTotalComapreProduct['totalCompareProduct']:"0";

		$fields=array(
					"%HIDE_NO_PRODCUT_IN_COMPARE%"=> $final_total_cmp_product == 0 ? 'hide' : '',
					"%SHOW_NO_PRODCUT_IN_COMPARE%"=> $final_total_cmp_product == 0 ? '' : 'hide',
					"%TOTAL_COMPARE_PRODUCT%"=>!empty($getTotalComapreProduct['totalCompareProduct'])?$getTotalComapreProduct['totalCompareProduct']:"0",
					"%COMPAREBOX_CHECKED%"=>($getTotalComapreProduct['totalCompareProduct']>=1)?"checked":"",
					"%COMPARE_HEAD_LOOP_DATA%"=>$compare_head_loop_data,
					"%COMPAREPRODUCTS_DATA%"=>$compareProducts_data,
					"%FOB_HTML%"=>$fob_html,
					"%MIN_ORDER_QUANTITY_HTML%"=>$min_order_quantity_html,
					"%GETTOTALVISITS_HTML%"=>$getTotalVisits_html,
					"%GETTOTALINQUIRY_HTML%"=>$getTotalInquiry_html,
					"%GETPRICE_HTML%"=>$getPrice_html,
					"%SUPPLYABILITY_HTML%"=>$supplyAbility_html,
					"%PRODUCTLOCATION_HTML%"=>$productLocation_html,
					"%PREMIUMLABEL_HTML%"=>$premiumLabel_html,
					"%TAGS_HTML%"=>$tags_html,
					"%COMPANYNAME%"=>$companyName,
					"%BUSINESSTYPES%"=>$businessTypes,
					"%ESTABLISH_YEAR%"=>$establish_year,
					"%COMAPNY_LOCATION%"=>$comapny_location,
					"%TOTALREVENUE%"=>$totalRevenue,
					"%RESPONSERATE%"=>$responseRate,
					"%REGISTRATIONDATE%"=>$registrationDate
			);


		$html=str_replace(array_keys($fields), array_values($fields), $html);

		return $html;
	}
}
?>