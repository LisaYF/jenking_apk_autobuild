<?php
//include_once "./DB_module.php";
//echo getInfo("dhakhdaks");


function getInfo($manufacturer){

	$Product_table="customer_homepage";
	$TVInfo_table="qcast_tvconfig_info";
	$SpecialInfo_table="qcast_build_info";

	$ProductInfo_key="alias";
	$TVInfo_key="manufacturer";
	$SpecialInfo_key="productName";

	$ProductInfo=Get_Build_Info($Product_table,$ProductInfo_key,$manufacturer);
	$TVInfo=Get_Build_Info($TVInfo_table,$TVInfo_key,$manufacturer);
	$SpecialInfo=Get_Build_Info($SpecialInfo_table,$SpecialInfo_key,$manufacturer);

	if(!($ProductInfo) || !($TVInfo) || !($SpecialInfo)){
		return false;
	}else{

		$packagename=$ProductInfo['packagename'];
		$umengkey=$ProductInfo['umengkey'];
		$appname=$ProductInfo['appname'];
		$product_name=$ProductInfo['product_name'];
		$product_ID=$ProductInfo['code'];
		$app_type=$ProductInfo['type'];
		$video_type=$ProductInfo['video_type'];//0=非视频;1=视频;2=非单品

		$landingAs=$TVInfo['landingAs'];

		//是否本地化主页，如果是，给出本地化主页信息
		// $useLocalHomePage=$TVInfo['useLocalHomePage'];
		$homePage=$TVInfo['homePage'];
		// if($useLocalHomePage == 'yes'){
		// 	$homepage_array=explode('/',$homePage);
		// 	$local_homepage_key=$homepage_array[4];
		// 	$local_homepage_type=$homepage_array[3];
		// }else{
		// 	$local_homepage_key='no';
		// 	$local_homepage_type='no';
		// }
		
		//新增homepageDiff:homepage_rn_diff/ccf_launcher_test
		$homepageDiff=$TVInfo['homepageDiff'];
		mark("*******************homepageDiff:".$homepageDiff,"NOTE");
		//判断homepage_rn_diff是否为空
		if(!empty($homepageDiff)){
			mark("homepageDiff not null","NOTE");
			$homepageDiff_array=explode('/',$homepageDiff);
			mark("*******************homepageDiff_array:".$homepageDiff_array,"NOTE");
			
			// $local_homepage_type=$homepageDiff_array[0];
			$local_homepage_type="homepage_v8_diff";
			mark("*******************local_homepage_type:".$local_homepage_type,"NOTE");
			
			$local_homepage_key=$homepageDiff_array[1];
			mark("*******************local_homepage_key:".$local_homepage_key,"NOTE");
		}else{
			mark("homepageDiff null","NOTE");
			$homepage_array=explode('/',$homePage);
			$local_homepage_key=$homepage_array[4];
			$local_homepage_type=$homepage_array[3];
		}

		//目前不用判断了，都设成yes
		$useLocalHomePage = 'yes';

		//启动图、icon图
		$icon_img=$SpecialInfo['icon'] == "default"?$landingAs:$SpecialInfo['icon'];
		$start_img=$SpecialInfo['startimg'] == "default"?$landingAs:$SpecialInfo['startimg'];

		if($product_ID > 999 and $product_ID < 1100){
			$qcode_code_version='1000';
			$public_alias="RNpub";
		}elseif($product_ID > 1099 and $product_ID < 1200){
			$qcode_code_version='1199';
			$public_alias="RNtest";
		}elseif($product_ID > 1199 and $product_ID < 1210){
			$qcode_code_version="1200";
			$public_alias="RNLauncher";
		}elseif($product_ID > 1219 and $product_ID < 1231){
			$qcode_code_version="1220";
			$public_alias="V8Launcher";
		}elseif($product_ID > 1209 and $product_ID < 1300){
			$qcode_code_version="1210";
			$public_alias="ccflauncher1210";
		}elseif($product_ID > 1390 and $product_ID < 1399){
			$qcode_code_version="1395";
			$public_alias="V8LauncherTest";
		}elseif($product_ID > 1299 and $product_ID < 1400){
			$qcode_code_version="1399";
			$public_alias="TestLauncher";
		}
		
		if($qcode_code_version == '1000'){
			$qcodecore="qcodecore.apk";
			$libresources="libresources.pak.59.so";
			$libwebview="libwebview.59.so";
		}else{
			$qcodecore="qcodecore-".$qcode_code_version.".apk";
			$libresources="libresources.pak.59-".$qcode_code_version.".so";
			$libwebview="libwebview.59-".$qcode_code_version.".so";
		}

		$BuildInfo_list=array(
			"product_name"=>$product_name,
			"product_ID"=>$product_ID,
			"manufacturer"=>$manufacturer,
			"packagename"=>$packagename,
			"umengkey"=>$umengkey,
			"appname"=>$appname,
			"app_type"=>$app_type,
			"video_type"=>$video_type,
			"landingAs"=>$landingAs,
			"useLocalHomePage"=>$useLocalHomePage,
			"local_homepage_key"=>$local_homepage_key,
			"local_homepage_type"=>$local_homepage_type,
			"icon_img"=>$icon_img,
			"start_img"=>$start_img,
			"qcode_code_version" => $qcode_code_version,
			"qcodecore" => $qcodecore,
			"libresources"=> $libresources,
			"libwebview"=> $libwebview,
			"public_alias" => $public_alias,
			// "homepageDiff" => $homepageDiff,
			// "homepage_rn_diff" => $homepage_rn_diff,
		);

		return $BuildInfo_list;
	}
}


function Get_Build_Info($table,$key1,$valeu1){
	$db = new MySql();
	$sql = "SELECT * FROM ".$table." WHERE ".$key1." = '".$valeu1."'";
//	echo $sql.PHP_EOL;
	$n = $db->select($sql, $result);
	if($n == 1){
		$config = $result[0];
		return $config;
	}else{
		return false;
	}
	$db->destroy();
	$db = null;

}



?>
