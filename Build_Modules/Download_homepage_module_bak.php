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
