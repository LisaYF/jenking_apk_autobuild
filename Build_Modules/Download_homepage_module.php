<?php
function getLocalHomepage($local_homepage_key,$local_homepage_type,$homepage_num=""){

	$db = new MySql();
	$sql = "SELECT * FROM `qcast_discovery_category` a inner join qcast_discovery_detail b on a.subtype='".$local_homepage_key."' and b.status='Yes' and a.category='".$local_homepage_type."' and a.id=b.categoryid";
	$n = $db->select($sql, $result);
	if($n == 1){
		if($result[0]['url']){
			$tmp = explode("/",$result[0]['url']);
			$num = end($tmp);
			$homepageurl=$result[0]['url'];
			empty_mark(1);

			if(empty($homepage_num)){
				mark("The home page version is not specified and the latest home version is used","WARNING");
			}elseif($homepage_num == $num){
				mark("Specify the home page version as the latest home version","WARNING");
			}else{
				mark("Use the specified home page version","WARNING");
				$old=$num;
				$num = $homepage_num;
				$homepageurl=str_replace($old,$num,$homepageurl);
			}


			mark("The homepage version is:".$num,"FAILURE");
			mark("homepage url:".$homepageurl,"FAILURE");
			empty_mark(1);
		}

		mark("local_homepage_type1:".$local_homepage_type,"NOTE");

		//当编译homepage和homepage_rn的时候
		if($local_homepage_type == "homepage" || $local_homepage_type == "homepage_rn"){
			mark("local_homepage_type2:".$local_homepage_type,"NOTE");
			
			if($local_homepage_type == "homepage"){
				$homepage_name='QCastHome';
			}
			elseif($local_homepage_type == "homepage_rn"){
				$homepage_name='QCodeRnHome';
			}

			
			$cfg_url=$homepageurl.'/'.$homepage_name.'.cfg';
			$zip_url=$homepageurl.'/'.$homepage_name.'_'.$num.'.zip';
	
			$file_localhomepage_path=tmp_path.'/'.$local_homepage_key.'/'.$homepage_name.'_'.$num;
			$file_zip_path=$file_localhomepage_path.'/'.$homepage_name.'_'.$num.'.zip';
			$file_cfg_path=$file_localhomepage_path.'/'.$homepage_name.'.cfg';
	
			if(!(file_exists($file_zip_path)) || !(file_exists($file_cfg_path))){
	
				$cmd = " wget -P $file_localhomepage_path '".$cfg_url."' > /dev/null 2>&1";
				exec($cmd,$r,$e);
				if($e == 0){
					mark("Successful download".$homepage_name.".cfg file","SUCCESS");
				}else{
					mark("download".$homepage_name.".cfg failed","FAILURE");
					return false;
				}
				$cmd = " wget -P $file_localhomepage_path '".$zip_url."' > /dev/null 2>&1";
				exec($cmd,$r,$e);
				if($e == 0){
					mark("Successful download".$homepage_name."_".$num.".zip file","SUCCESS");
				}else{
					mark("download homepage"."$homepage_name"."_".$num.".zip package failed","FAILURE");
					return false;
				}
			}else{
				mark("The localized home page already exists and does not need to download again","WARNING");
			}
	
			if(file_exists($file_zip_path) && file_exists($file_cfg_path)){
				//把$file_zip_path从一个zip包变成一个地址
				$file_zip_path=$file_localhomepage_path;
				mark("this address of homepage".$homepage_name.'_'.$num."Existing in a temporary folder","SUCCESS");
				return array(
					"file_cfg_path"=>$file_cfg_path,
					"file_zip_path"=>$file_zip_path,
					"file_zip_name"=>$homepage_name."_".$num.".zip",
					"file_cfg_name"=>$homepage_name.".cfg",
					"homepage_num"=>$num,
				);
			}else{
				mark("The local home page does not exist for temporary files","FAILURE");
				return false;
			}
		}
		
		//当编译homepage_rn_diff的时候
		if($local_homepage_type == "homepage_v8_diff"){
			mark("local_homepage_type2:".$local_homepage_type,"NOTE");
			
			$homepage_name='QCodeV8Home';
			$cfg_url=$homepageurl.'/'.$homepage_name.'.cfg';
			mark("cfg_url:".$cfg_url,"NOTE");

			$file_localhomepage_path=tmp_path.'/'.$local_homepage_key.'/'.$homepage_name.'_'.$num;
			mark("file_localhomepage_path:".$file_localhomepage_path,"NOTE");
			///home/jenkins/common/test_jenkins_RN_apkbuild/tmp/ccf_launcher_test/QCodeRnHome_10628
			$file_cfg_path=$file_localhomepage_path.'/'.$homepage_name.'.cfg';
			mark("file_cfg_path:".$file_cfg_path,"NOTE");
			///home/jenkins/common/test_jenkins_RN_apkbuild/tmp/ccf_launcher_test/QCodeRnHome_10617/QCodeRnHome.cfg

			if(!file_exists($file_cfg_path)){
				$cmd = " wget -P $file_localhomepage_path '".$cfg_url."' > /dev/null 2>&1";
				exec($cmd,$r,$e);
				if($e == 0){
					mark("Successful download".$homepage_name.".cfg file","SUCCESS");
				}else{
					mark("download ".$homepage_name.".cfg failed","FAILURE");
					return false;
				}
			}else{
				mark("The localized home page already exists and does not need to download again","WARNING");
			}

			//读取cfg文件
			mark("get cfg file","NOTE");
			$cfg_file=file_get_contents($file_cfg_path);
			echo $cfg_file;
			
			//读取json格式数据
			mark("get json info","NOTE");
			$obj=json_decode($cfg_file,true);
			echo  $obj;
			
			foreach ($obj as $key => $val) {
				//获取fullPack
				if($key=="fullPack"){
					mark("key==fullPack","NOTE");
					foreach ($val as $key1 => $val1){
						mark($key1 .'=====>'. $val1,"NOTE");
						$fullPack_zip_name=$homepage_name.'_'.$key1.'_'.$num.'.zip';
						$file_zip_path=$file_localhomepage_path.'/'.$fullPack_zip_name;//QCodeRnHome_ccf_10628.zip
						$zip_url=$homepageurl.'/'.$fullPack_zip_name;
						if(!file_exists($file_zip_path)){
							$cmd = " wget -P $file_localhomepage_path '".$zip_url."' > /dev/null 2>&1";
							exec($cmd,$r,$e);
							if($e == 0){
								mark("Successful download ".$fullPack_zip_name." ZIP file","SUCCESS");

								mark("********check ZIP Md5********","NOTE");
								$md5_val1 = md5_file($file_zip_path);
								mark("********ZIP Md5 md5_val:********".$md5_val1,"NOTE");
								mark("********ZIP Md5 val2:********".$val1,"NOTE");

								if($val1==$md5_val1){
									mark("fullPack: same md5sum","SUCCESS");
								}else{
									mark("fullPack: different md5sum, please upload again","FAILURE");
									return false;
								}

							}else{
								mark("Failed download ".$fullPack_zip_name." ZIP file","FAILURE");
								return false;
							}
						}else{
							mark("The localized home page already exists and does not need to download again","WARNING");
						}
					}
				}
				//获取ver和preVer 
				if($key=="ver"){
					$ver_val=$val;
					mark("ver->val:".$key."-->".$val,"NOTE");
				}
				if($key=="preVer"){
					$preVer_val=$val;
					mark("preVer->val:".$key."-->".$val,"NOTE");
				}
			}
			
			//判断ver和preVer是否相等
			if($ver_val!=$preVer_val){
				mark("ver and preVer not equal","NOTE");
				foreach ($obj as $key => $val) {
				if($key=="diffPack"){
					if(empty($val)){
						mark("diffPack is empty","NOTE");
					}else{
						mark("diffPack not empty","NOTE");
						foreach ($val as $key2 => $val2){
							mark($key2 .'=====>'. $val2,"NOTE");//QCodeRnHome_ccf_10630_10631_diff.zip
							$diffPack_zip_name=$homepage_name.'_'.$key2.'_'.$preVer_val.'_'.$num.'_'.'diff.zip';
							$file_zip_path=$file_localhomepage_path.'/'.$diffPack_zip_name;
							mark("diffPack zip name".$file_zip_path,"NOTE");
							
							$zip_url=$homepageurl.'/'.$diffPack_zip_name;
							if(!file_exists($file_zip_path)){
								$cmd = " wget -P $file_localhomepage_path '".$zip_url."' > /dev/null 2>&1";
								exec($cmd,$r,$e);
								if($e == 0){
									mark("Successful download ".$diffPack_zip_name." ZIP file","SUCCESS");
									
									mark("check ZIP Md5","NOTE");
									$md5_val = md5_file($file_zip_path);
									mark("ZIP Md5 md5_val:".$md5_val,"NOTE");
									mark("ZIP Md5 val2 :".$val2,"NOTE");

									if($val2==$md5_val){
										mark("diffPack: same md5sum","SUCCESS");
									}else{
										mark("diffPack: different md5sum, please upload again","FAILURE");
										return false;
									}
								}else{
									mark("Failed download ".$diffPack_zip_name." ZIP file","FAILURE");
									return false;
								}
							}else{
								mark("The localized home page already exists and does not need to download again","WARNING");
							}
						}
					}
				}
			}
		}
			//把$file_zip_path从一个zip包变成一个地址
			$file_zip_path=$file_localhomepage_path;
			//$file_localhomepage_path:/home/jenkins/common/test_jenkins_RN_apkbuild/tmp/ccf_launcher_test/QCodeRnHome_10628

			if(file_exists($file_cfg_path)){
				mark("this address of homepage ".$homepage_name.'_'.$num." Existing in a temporary folder","SUCCESS");
				return array(
					"file_cfg_path"=>$file_cfg_path,
					"file_zip_path"=>$file_zip_path,
					"file_zip_name"=>$homepage_name."_".$num.".zip",
					"file_cfg_name"=>$homepage_name.".cfg",
					"homepage_num"=>$num,
				);
			}else{
				mark("The local home page does not exist for temporary files","FAILURE");
				return false;
			}			
		}
	}else{
		return false;
	}
	$db->destroy();
	$db = null;
}

function getNewJson($manufacturer){

	$tmp_tvconfig=tmp_path."/qcast_tv_config_".$manufacturer.".json";

		if(file_exists($tmp_tvconfig)){
			$ctime_manifest=filectime($tmp_tvconfig);
			rename($tmp_tvconfig,tmp_path.'/qcast_tv_config_'.$manufacturer.'_'.date("Ymd-Hi",$ctime_manifest).'.json');
		}

	$db = new MySql();
	$sql = "SELECT * FROM qcast_tvconfig_info WHERE manufacturer  = '".$manufacturer."'";
	$n = $db->select($sql, $result);
	if($n == 1){
		$config = $result[0];
		unset($config['id']);
		$config['lastModify'] = strtotime($config['updatetime']);
		unset($config['updatetime']);
		$configStr = json_encode($config);
	}else{
		mark("Failed to get the TV configuration file","FAILURE");
		return false;
	}
	$db->destroy();
	$db = null;
	$configStr  = stripslashes($configStr);
	if($configStr){
		$jsonfile_p = $tmp_tvconfig;
		file_put_contents($jsonfile_p,$configStr);
		if(file_exists($jsonfile_p)){
			mark("Successfully generate the TV configuration file","SUCCESS");
			return $tmp_tvconfig;
		}else{
			mark("Failed to generate the TV configuration file","FAILURE");
			return false;
		}
	}else{
		mark("There is no TV configuration file for this manufacturer","FAILURE");
		return false;
	}

}

?>
