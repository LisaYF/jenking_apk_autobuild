<?php
error_reporting( E_ALL&~E_NOTICE );
define("script_path",dirname(__FILE__));
define("template_path",script_path.'/template_xml');
define("tmp_path",script_path.'/tmp');
define("modules_path",script_path.'/Build_Modules');
define("All_img_path",script_path.'/Img');

define("ANDROID_HOME",$_SERVER['ANDROID_HOME']);
define("ANDROID_NDK_HOME",$_SERVER['ANDROID_NDK_HOME']);
define("GRADLE_USER_HOME",$_SERVER['GRADLE_USER_HOME']);
define("dx",ANDROID_HOME .'/build-tools/27.0.3/dx');

//根据不同的php版本调用不同的DB模块
if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
	require_once modules_path.'/DB_module_ansible.php';
}else{
	require_once modules_path.'/DB_module.php';
}

//公共模块
require_once modules_path.'/Get_GitCodeVersion_module.php';
require_once modules_path.'/Get_VersionInfo_module.php';
require_once modules_path.'/Get_BuildInfo_module.php';
require_once modules_path.'/BuildDex_module.php';
require_once modules_path.'/ModifyFile_module.php';
require_once modules_path.'/Download_homepage_module.php';
require_once modules_path.'/Delete_function_module.php';
require_once modules_path.'/Print_function_module.php';
require_once modules_path.'/Upload_oss_module.php';
require_once modules_path."/Check_input_module.php";
require_once modules_path."/ZipPackage_module.php";

// define("build_model", "hand");
// var_dump(autobuild("ccflauncher1210","","","release_build_pre"));
//autobuild("TestLauncher","apk_test_rn_tinker","master","","");
//autobuild("DevTvosLauncher","","","master");
$longopt = array(
    'manufacturer:',
    'job_name:',
	'BranchName:',
	'git_version:',
	'homepage_num:'
);
$param = getopt('', $longopt);
print_r($param);
$manufacturer=$param['manufacturer'];
$job_name=$param['job_name'];
$BranchName=$param['BranchName'];
$git_version=$param['git_version'];
$homepage_num=$param['homepage_num'];

//autobuild($argv[1],$argv[2],$argv[3],$argv[5],$argv[4]);
autobuild($manufacturer,$job_name,$BranchName,$git_version,$homepage_num);

function autobuild($manufacturer,$job_name="",$BranchName="",$git_version="",$homepage_num=""){
	// var_dump(func_get_args());
	mark("ANDROID_HOME:".ANDROID_HOME,"NOTE");
	mark("ANDROID_NDK_HOME:".ANDROID_NDK_HOME,"NOTE");
	mark("GRADLE_USER_HOME:".GRADLE_USER_HOME,"NOTE");
	release_build_info($manufacturer,$BranchName,$job_name);
	return release($manufacturer,$git_version,$homepage_num,$BranchName,$job_name);
}

function release_build_info($manufacturer,$BranchName="",$job_name=""){

	if(empty($BranchName)){
		$BranchName = "master";
	}
	
	$git_path = "/home/jenkins/workspace/".$job_name;
	mark('Project Path:'.$git_path,'NOTE');	
    
    $GLOBALS['BranchName']=$BranchName;
	$GLOBALS["git_path"]=$git_path;
	$GLOBALS["vendor_assets_dir"]=$git_path.'/android/app/cast-receiver/src/main/assets/vendor';//本地化主页，tv_config
	$GLOBALS["vendor_res_dir"]=$git_path.'/android/app/cast-receiver-resource/src/main/res/raw';//图片存放路径：启动图
	$GLOBALS["dex_dir"]=$git_path.'/android/tv-service/service-server-interface/src/main/assets';
	$GLOBALS["build_dex_dir"]=$git_path.'/android';
	$GLOBALS["icon_img_path"]=$git_path.'/android/app/cast-receiver/src/main/res';
	$GLOBALS["ServiceSdkGlobal"]=$git_path.'/android/tv-service/service-public/src/main/java/org/chromium/content/browser/service_public/ServiceSdkGlobal.java';
}

function release($manufacturer,$git_version="",$homepage_num="",$BranchName="",$job_name){

	global $BranchName,$git_path,$vendor_assets_dir,$vendor_res_dir,$dex_dir,$build_dex_dir,$icon_img_path,$ServiceSdkGlobal,$job_name;
    $output_path=script_path.'/apks/'.$BranchName;
    if (!file_exists($output_path)){
        mkdir ($output_path,0777,true);
    }

	mark("check the input information","NOTE");
	$BuildInfo_list=check_input($manufacturer,$git_version,$homepage_num);
	mark("check finish","NOTE");
	if(!($BuildInfo_list)){
		mark("Wrong input","FAILURE");
		return returnInfo("Wrong input","FAILURE");
	}else{
		mark("input information success","SUCCESS");
	}

	$packagename=$BuildInfo_list['packagename'];
	$product_name=$BuildInfo_list['product_name'];
	$product_ID=$BuildInfo_list['product_ID'];
	// $product_ID="1399";
	$app_type=$BuildInfo_list['app_type'];
	$video_type=$BuildInfo_list['video_type'];//0=非视频;1=视频;2=非单品
	$landingAs=$BuildInfo_list['landingAs'];
	$useLocalHomePage=$BuildInfo_list['useLocalHomePage'];
	$local_homepage_key=$BuildInfo_list['local_homepage_key'];
	$local_homepage_type=$BuildInfo_list['local_homepage_type'];
	$icon_img=$BuildInfo_list['icon_img'];
	$start_img=$BuildInfo_list['start_img'];
	$qcode_code_version=$BuildInfo_list['qcode_code_version'];
	$qcodecore=$BuildInfo_list['qcodecore'];
	$libresources=$BuildInfo_list['libresources'];
	$libwebview=$BuildInfo_list['libwebview'];

	$Git_submit_number=$BuildInfo_list["git"];
	$landingAs=$BuildInfo_list["landingAs"];

	$file_zip_path=$BuildInfo_list['file_zip_path'];
	$file_cfg_path=$BuildInfo_list['file_cfg_path'];
	$file_zip_name=$BuildInfo_list['file_zip_name'];
	$file_cfg_name=$BuildInfo_list['file_cfg_name'];
	$homepage_local_num=$BuildInfo_list['homepage_num'];

	#$Git_submit_number=$Git_submit_number+2000;
	$version_info=Get_Build_Version($Git_submit_number);

	if(empty($version_info)){
		mark("version_info get failed","FAILURE");
		return returnInfo("version_info get failed","FAILURE");
	}else{
		$VersionCode=$version_info['vercode'];
		$VersionName=$version_info['version'];
	}

    empty_mark(1);
	mark("build information list:","FAILURE");
	foreach($BuildInfo_list as $info_key => $info_value){
		note_mark($info_key,$info_value);
	}
	note_mark("vercode",$VersionCode);
	note_mark("version",$VersionName);
	empty_mark(1);

    $tem_arr=explode(".",$VersionName);
	$apkname="QCode_TV_".$manufacturer."_V8_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";
	$apkname_out = "QCode_TV_".$manufacturer."_V8_".$homepage_local_num."_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";
	$apkname_signed="QCode_TV_".$manufacturer."_V8_signed_".$homepage_local_num."_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";
	$apkname_full = "QCode_TV_".$manufacturer."_V8_full_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";
	$apkname_full_out = "QCode_TV_".$manufacturer."_V8_full_".$homepage_local_num."_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";
	$apkname_full_signed = "QCode_TV_".$manufacturer."_V8_full_signed_".$homepage_local_num."_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";

	$apkname_autobuild=$manufacturer ."-". $BuildInfo_list["git"] ."-". $BuildInfo_list["homepage_num"] .".apk";
	$apkname_autobuild_signed=$manufacturer ."-". $BuildInfo_list["git"] ."-". $BuildInfo_list["homepage_num"] ."-signed.apk";

    $output_zip_name = "QCode_TV_".$manufacturer."_V8_".$homepage_local_num."_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".zip";
	$output_zip_name_autobuild = $manufacturer ."-". $BuildInfo_list["git"] ."-". $BuildInfo_list["homepage_num"].".zip";


	$UIServicename="UIService_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";
	$UIServicename_signed="UIService_signed_".$tem_arr[0].".".$tem_arr[1].".".$BuildInfo_list["product_ID"].".".$Git_submit_number.".apk";

	mark("connect OSS client","NOTE");
	$ossClient=getOssClient();
	if(is_null($ossClient)){
		mark("connect OSS failed","FAILURE");
		return returnInfo("connect OSS failed","FAILURE");
	}else{
		mark("connect OSS success","SUCCESS");
	}

	if($landingAs == "launcher"){
		$final_apk=$output_path.'/'.$Git_submit_number.'/'.$apkname_signed;
		$upload_file=$output_path.'/'.$Git_submit_number.'/'.$output_zip_name;
		$oss_object="V8/".$BranchName."/".$Git_submit_number."/".$output_zip_name;
	}else{
		$final_apk = $output_path.'/'.$Git_submit_number.'/'.$apkname_out;
		$upload_file=$final_apk;
		$oss_object="V8/".$BranchName."/".$Git_submit_number."/".$apkname_out;
	} 

	$bucket="填写bucket名称";
	if($bucket == "填写bucket名称-pool"){
		$url="http://填写域名".$oss_object;
	}else{
		$url="http://".$bucket.".oss-cn-qingdao.aliyuncs.com/".$oss_object;
	}
	
	mark("start work","NOTE");
	//创建$Git_submit_number文件夹
	if(!is_dir($output_path."/".$Git_submit_number)){
		mark("create".$Git_submit_number."folder","NOTE");
		mkdir($output_path."/".$Git_submit_number."/",0777,true);
	}else{
		mark($Git_submit_number."folder exist","WARNING");
	}
	
	$exisit=doesObjectExist($ossClient, $bucket, $oss_object);
    if($exisit){
		mark("该渠道包已经编译过，下载地址:".$url,"SUCCESS");
		return returnInfo($url,"SUCCESS",$VersionCode);
    }
    
	$exisit= file_exists($upload_file);
	if($exisit){
		mark("该渠道包已经编译过,未上传到指定位置,重新上传","NOTE");
		if((uploadFile($ossClient, $bucket, $oss_object, $upload_file)) == false){
			mark("上传oss失败","FAILURE");
			return returnInfo("上传oss失败","FAILURE");
		}else{
			mark("上传OSS成功","SUCCESS");
			time_mark($url,"SUCCESS");
			return returnInfo($url,"SUCCESS",$VersionCode);
		}
	}else{

		time_mark("开始编译apk:".$manufacturer ."-". $BuildInfo_list["git"] ."-". $BuildInfo_list["homepage_num"],"NOTE");
    }
	//该渠道已经编译过
	// $exisit=doesObjectExist($ossClient, $bucket, $oss_object);
	// $exisit2= file_exists($upload_file);
	// if($exisit){
	// 	mark("this manufacturer has build，download url:".$url,"SUCCESS");
	// 	mark("copy zip files","NOTE");
	// 	$cmd='\cp \/home\/jenkins\/common\/jenkins_RN_tinker_apkbuild\/apks\/'.$BranchName.'\/'.$Git_submit_number.'\/'.$output_zip_name.' '.'\/home\/jenkins\/workspace\/'.$job_name;
	// 	exec($cmd,$r,$e);
	// 	if($e == 0){
	// 		mark("copy zip files successfully","NOTE");
	// 	}else{
	// 		mark("copy zip files failed","FAILURE");
	// 	}
	// 	return returnInfo($url,"SUCCESS",$VersionCode);
	// }elseif($exisit2){
	// 	mark("this manufacturer has build, but has not upload to right position, upload again","NOTE");
	// 	mark("copy zip files","NOTE");
	// 	$cmd='\cp \/home\/jenkins\/common\/jenkins_RN_tinker_apkbuild\/apks\/'.$BranchName.'\/'.$Git_submit_number.'\/'.$output_zip_name.' '.'\/home\/jenkins\/workspace\/'.$job_name;
	// 	exec($cmd,$r,$e);
	// 	if($e == 0){
	// 		mark("copy zip files successfully","NOTE");
	// 		return returnInfo("copy success","SUCCESS");
	// 	}else{
	// 		mark("copy zip files failed","FAILURE");
	// 		return returnInfo("copy failed","FAILURE");
	// 	}
	// }else{
	// 	time_mark("start build apk:".$manufacturer ."-". $BuildInfo_list["git"] ."-". $BuildInfo_list["homepage_num"],"NOTE");
	// }
	

	//取qcast_sdk_core.dex或编dex
    $dex_object="V8/".$BranchName."/".$Git_submit_number."/qcast_sdk_core.dex";
	if(file_exists($output_path."/".$Git_submit_number."/qcast_sdk_core.dex")){
		mark("qcast_sdk_core.dex exist","WARNING");
	}elseif(doesObjectExist($ossClient, $bucket, $dex_object)){
		mark("OSS has qcast_sdk_core.dex already,start download","NOTE");
		if((getObjectToLocalFile($ossClient, $bucket, $dex_object, $output_path."/".$Git_submit_number."/qcast_sdk_core.dex")) && file_exists($output_path."/".$Git_submit_number."/qcast_sdk_core.dex")){
			mark("qcast_sdk_core.dex download success","SUCCESS");
		}else{
			mark("qcast_sdk_core.dex download failed","FAILURE");
			return returnInfo("qcast_sdk_core.dex downlad failed"."FAILURE");
		}
	}else{
		//$cmd="sed -i 's/SERVICE_VERCODE=\".*\"/SERVICE_VERCODE=\"".$VersionCode."\"/g' ".$ServiceSdkGlobal;
		$cmd="sed -i 's/SERVICE_VERCODE=\".*\"/SERVICE_VERCODE=\"".$Git_submit_number."\"/g' ".$ServiceSdkGlobal;
		exec($cmd,$r,$e);
		$cmd="sed -i 's/SERVICE_VERNAME=\".*\"/SERVICE_VERNAME=\"1\.0\.1000\.".$Git_submit_number."\"/g' ".$ServiceSdkGlobal;
		exec($cmd,$r,$e);
		build_dex();
		copy( $build_dex_dir."/qcast_sdk_core.dex" , $output_path."/".$Git_submit_number."/qcast_sdk_core.dex");
		chdir(script_path);
		mark("upload qcast_sdk_core.dex to OSS","NOTE");
		if(!(uploadFile($ossClient, $bucket, $dex_object, $output_path."/".$Git_submit_number."/qcast_sdk_core.dex"))){
			mark("upload qcast_sdk_core.dex failed","FAILURE");
			return returnInfo("upload qcast_sdk_core.dex failed","FAILURE");
		}else{
			mark("upload qcast_sdk_core.dex success","SUCCESS");
		}
	}
	//删除旧的dex，拷贝新的
	if(file_exists($dex_dir.'/qcast_sdk_core.dex')){
		mark("delete old qcast_sdk_core.dex","NOTE");
		Delete_file($dex_dir.'/qcast_sdk_core.dex');
	}
	mark("copy new qcast_sdk_core.dex","NOTE");
	if(copy($output_path."/".$Git_submit_number."/qcast_sdk_core.dex",$dex_dir.'/qcast_sdk_core.dex')){
		if(file_exists($dex_dir.'/qcast_sdk_core.dex')){
			mark("copy dex success","SUCCESS");
		}else{
			mark("copy qcast_sdk_core.dex file failed","FAILURE");
			return returnInfo("copy qcast_sdk_core.dex file failed","FAILURE");
		}
	}else{
		mark("copy qcast_sdk_core.dex file failed","FAILURE");
		return returnInfo("copy qcast_sdk_core.dex file failed","FAILURE");
	}
	//删除旧的启动图，icon图和tv_config，本地化主页
    empty_mark(2);
	mark("start delete old start-up images, icon images and tv_config、local homepage","NOTE");
	//删除apk
	$delete_path="android/app/cast-receiver/build/outputs/apk";
	delete_not_empty_dir($delete_path,$git_path);

	$delete_path='node_modules/jsi/android/build';
	delete_not_empty_dir($delete_path,$git_path);
	//删除旧的本地化主页，tv_config
	delete_not_empty_dir($vendor_assets_dir,$git_path);
	//删除就的启动图
	// delete_not_empty_dir($vendor_res_dir,$git_path);
	
	//删除旧的icon图
	$icon_img_path_folder='android/app/cast-receiver/src/main/res';
	$delete_file_list=array(
			$vendor_assets_dir,
			$icon_img_path_folder.'/mipmap',
			$icon_img_path_folder.'/mipmap-xhdpi',
			$icon_img_path_folder.'/mipmap-hdpi',
			$icon_img_path_folder.'/mipmap-mdpi',
			$icon_img_path_folder.'/mipmap-ldpi',
			);

    $tmp_num=0;
	foreach($delete_file_list as $dir_name){
		delete_not_empty_dir($dir_name,$git_path);
		if(mkdir($dir_name)){
			$tmp_num+=1;
		};
	}
	if($tmp_num = 5){
		mark("make folder success","SUCCESS");
	}else{
		mark("make folder failed","FAILURE");
		return returnInfo("make folder failed","FAILURE");
	}
	//拷贝启动图
	// mark("copy start-up images:".$start_img,"NOTE");
	// copy(All_img_path.'/boot/'.$start_img.'/licence_side_logo.png',$vendor_res_dir.'/vendor_logo.png');
	// copy(All_img_path.'/boot/'.$start_img.'/loading_frame_1.png',$vendor_res_dir.'/vendor_loading_frame_1.png');
	// copy(All_img_path.'/boot/'.$start_img.'/loading_frame_2.png',$vendor_res_dir.'/vendor_loading_frame_2.png');
	// copy(All_img_path.'/boot/'.$start_img.'/loading_frame_3.png',$vendor_res_dir.'/vendor_loading_frame_3.png');
	// copy(All_img_path.'/boot/'.$start_img.'/startup_image.jpg',$vendor_res_dir.'/vendor_startup_image.jpg');

    // if(file_exists($vendor_res_dir.'/vendor_startup_image.jpg')){
	// 	mark("copy vendor_startup_image.jpg success","SUCCESS");
	// }else{
	// 	mark("copy vendor_startup_image.jpg failed","FAILURE");
	// 	return returnInfo("copy vendor_startup_image.jpg failed","FAILURE");
	// }
	
	//拷贝icon图
	mark("copy icon images:".$icon_img,"NOTE");
	$source_path_list=array(
			All_img_path.'/icon/'.$icon_img.'/default.png',
			All_img_path.'/icon/'.$icon_img.'/xhdpi.png',
			All_img_path.'/icon/'.$icon_img.'/hdpi.png',
			All_img_path.'/icon/'.$icon_img.'/mdpi.png',
			All_img_path.'/icon/'.$icon_img.'/ldpi.png',
			);

	$des_path_list=array(
			$icon_img_path.'/mipmap/ic_launcher.png',
			$icon_img_path.'/mipmap-xhdpi/ic_launcher.png',
			$icon_img_path.'/mipmap-hdpi/ic_launcher.png',
			$icon_img_path.'/mipmap-mdpi/ic_launcher.png',
			$icon_img_path.'/mipmap-ldpi/ic_launcher.png',
			);

	$tmp_num=0;
	for($i=0;$i<5;$i++){
		copy($source_path_list[$i],$des_path_list[$i]);
		if(file_exists($des_path_list[$i])){
			$tmp_num+=1;
		}
	}
	if($tmp_num == 5){
		mark("copy icon images success","SUCCESS");
	}else{
		mark("copy icon images failed","FAILURE");
		return returnInfo("copy icon images failed","FAILURE");
	}

	//拷贝本地化主页
    // if($useLocalHomePage == 'yes'){
	// 	mark("start local homepage","NOTE");
	// 	if(copy($file_zip_path.'/'.$file_zip_name,$vendor_assets_dir.'/'.$file_zip_name) && copy($file_cfg_path,$vendor_assets_dir.'/'.$file_cfg_name)){
	// 		mark("replace local homepage success","SUCCESS");
	// 	}else{
	// 		mark("copy local homepage failed","FAILURE");
	// 		return returnInfo("copy local homepage failed","FAILURE");
	// 	}
	// }else{
	// 	mark("no use local homepage","NOTE");
	// 	$homepage_local_num='00000';
	// }
	if($useLocalHomePage == 'yes'){
		mark("start local homepage","NOTE");
		//拷贝zip包
		mark("start copy zip","NOTE");
		$cmd='\cp'.' '.$file_zip_path.'/*.zip'.' '.$vendor_assets_dir;
		exec($cmd,$r,$e);
		if($e==0){
			mark("copy zip files successfully","NOTE");
		}else{
			mark("copy zip files failed","FAILURE");
			return returnInfo("copy failed","FAILURE");
		}
		//拷贝cfg
		mark("start copy cfg","NOTE");
		if(copy($file_cfg_path,$vendor_assets_dir.'/'.$file_cfg_name)){
			mark("copy cfg success, replace local homepage success","SUCCESS");
		}else{
			mark("copy cfg failed, replace local homepage failed","FAILURE");
			return returnInfo("copy local homepage failed","FAILURE");
		}
	}else{
		mark("do not use local homepage","NOTE");
		$homepage_local_num='00000';
	}

	//拷贝tv_config
	$file_tvconfig_path=getNewJson($manufacturer);
	if(copy($file_tvconfig_path,$vendor_assets_dir.'/qcast_tv_config.json')){
		mark("replace tv_config file success","SUCCESS");
	}else{
		mark("copy tv_config file failed","FAILURE");
		return returnInfo("copy tv_config file failed","FAILURE");
	}

	chdir($git_path);
	
	//开始编译apk,打包
	mark("start release apk, package command as follows:","NOTE");
	if($landingAs == "launcher"){
		mark("firstly package entire Launcher's APK","NOTE");
		$common_cmd="./scripts/autobuild-release-2.x.sh --package-type=android --package-mach=TV --code-revision=HEAD --version-name=".$VersionName." --version-code=".$VersionCode." --market-code=".$product_ID." --market-name=".$manufacturer." --market-code-core=".$qcode_code_version." --output-path=".$output_path." --applicaton-id=".$packagename." --ndk-path=".ANDROID_NDK_HOME." --key-password=qcast@1234 --store-password=qcast@1234";
		$cmd = $common_cmd; 
		mark($cmd,"WARNING");
		exec($cmd,$r,$e);
		mark("e:".$e,"NOTE");
		mark("*******PATH:*********".$output_path.'/'.$Git_submit_number.'/'.$apkname,"NOTE");
		if(($e==0) && (file_exists($output_path.'/'.$Git_submit_number.'/'.$apkname))){
				mark("package finish:(success)","SUCCESS");
				//修改apkname_full的名字
				mark("apkname_full:".$apkname_full,"NOTE");
				$res_rename = rename($output_path.'/'.$Git_submit_number.'/'.$apkname_full,$output_path.'/'.$Git_submit_number.'/'.$apkname_full_out);
				if($res_rename){
						mark("APK change name success","NOTE");
						mark("entire Launcher's APK: ".$apkname_full_out,"FAILURE");
				}else{
						mark("APK change name failed","FAILURE");
						return returnInfo("APK change name failed","FAILURE");
				}
				//修改apkname的名字
				mark("apkname:".$apkname,"NOTE");
				$res_rename2 = rename($output_path.'/'.$Git_submit_number.'/'.$apkname,$output_path.'/'.$Git_submit_number.'/'.$apkname_out);
				if($res_rename2){
					mark("modify file name (add homepage version) success:".$apkname_out,"SUCCESS");
				}else{
					mark("modify filename(add homepage version)failed","FAILURE");
					return returnInfo("modify filename(add homepage version)failed","FAILURE");
				}
		}else{
				mark("package finish:(failure)","FAILURE");
				mark("************************ptint error message**********************","FAILURE");
				system($cmd,$va);
				echo $va;
				return returnInfo("package finish:(failure)","FAILURE");
		}
	}

	//签名
	if($landingAs == "launcher"){
		empty_mark(2);
		mark("application type is launcher，coat nedd system sign a system signature","NOTE");
		$sign_tool_path=script_path."/sys_sign_tools/ccf";

		$cmd="java -jar ".$sign_tool_path."/signapk.jar ".$sign_tool_path."/platform.x509.pem ".$sign_tool_path."/platform.pk8 ".$output_path."/".$Git_submit_number."/".$apkname_full_out." ".$output_path."/".$Git_submit_number."/".$apkname_full_signed;
		mark($cmd,"NOTE");
		exec($cmd,$r,$e);

		$cmd="java -jar ".$sign_tool_path."/signapk.jar ".$sign_tool_path."/platform.x509.pem ".$sign_tool_path."/platform.pk8 ".$output_path."/".$Git_submit_number."/".$apkname_out." ".$output_path."/".$Git_submit_number."/".$apkname_signed;
		mark($cmd,"NOTE");
		exec($cmd,$r,$e);
		// return returnInfo("return***************","FAILURE");

		//签名 UIService_1.3.1220.2208.apk
		// $cmd="java -jar ".$sign_tool_path."/signapk.jar ".$sign_tool_path."/platform.x509.pem ".$sign_tool_path."/platform.pk8 ".$output_path."/".$Git_submit_number."/".$UIServicename." ".$output_path."/".$Git_submit_number."/".$UIServicename_signed;
		// mark($cmd,"NOTE");
		// exec($cmd,$r,$e);


	if(file_exists($output_path."/".$Git_submit_number."/".$apkname_signed) && file_exists($output_path."/".$Git_submit_number."/".$apkname_full_signed)){
			mark("signature success","SUCCESS");
		}else{
			mark("signature failed","FAILURE");
			return returnInfo("signature failed","FAILURE");
		}
		
		//解压full_apk里面的lib文件夹
		mark('unzip lib folder under full_apk','NOTE');
		chdir($output_path."/".$Git_submit_number);
		$cmd2='unzip -o '.$apkname_full_out.' "lib/*" -d '.$output_path."/".$Git_submit_number;
		exec($cmd2,$r2,$e2);
		if($e2 == 0){
			mark("unzip lib folder success","NOTE");
		}else{
			mark("unzip lib folder failed","FAILURE");
			return returnInfo("unzip lib folder  failed","FAILURE");
		}
		chdir(script_path);

		#校验所有文件的md5值
		$cmd='./check_md5.sh '.$BranchName.' '.$Git_submit_number;
		exec($cmd,$r,$e);
		if($e == 0){
			mark("exec check_md5.sh successfully","NOTE");
			echo end($r);
		}else{
			mark("exec check_md5.sh failed","FAILURE");
			echo end($r);
			return returnInfo("exec check_md5.sh  failed","FAILURE");
		}
		$check_md5=end($r);
		if(strcasecmp($check_md5,"md5_same")== 0){
			mark("two apk md5 same","NOTE");
		}else{
			mark("two apk md5 different","NOTE");
			return returnInfo("two apk md5 different","FAILURE");
		}

		//开始打包apk和so文件
		empty_mark(2);
		mark('start package apk and so files','NOTE');
		//UIService_1.3.1220.2208.apk
		$output_zip_path = $output_path.'/'.$Git_submit_number.'/'.$output_zip_name;
		$file_list=array(
			$output_path.'/'.$Git_submit_number.'/'.$apkname_signed,
			// $output_path.'/'.$Git_submit_number.'/'.$UIServicename_signed,
			// $output_path.'/'.$Git_submit_number.'/'.$libresources,
			// $output_path.'/'.$Git_submit_number.'/'.$libwebview
		);

		foreach($file_list as $file){
			if(!file_exists($file)){
				mark('zip package which required file is not complete','FAILURE');
				return returnInfo('zip package which required file is not complete','FAILURE');
			}
		}

		$zip = new ZipArchive();
		if ($zip->open($output_zip_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) === TRUE) {
			$zip->addFile($output_path.'/'.$Git_submit_number.'/'.$apkname_signed,$apkname_signed);
			// $zip->addFile($output_path.'/'.$Git_submit_number.'/'.$UIServicename_signed,$UIServicename_signed);
			//$zip->addFile($output_path.'/'.$Git_submit_number.'/'.$libresources,'libresources.pak.59.so');
			//$zip->addFile($output_path.'/'.$Git_submit_number.'/'.$libwebview,'libwebview.59.so');
			//$zip->addFile($output_path.'/'.$Git_submit_number.'/lib','lib');
			mark('zip lib start','NOTE');
			$path=$output_path.'/'.$Git_submit_number.'/';
			chdir ($path);
			addFileToZip("lib/", $zip);
			mark('zip lib finish','NOTE');
			//$zip->addFile($output_path.'/'.$Git_submit_number.'/'.$qcodecore,'libqcodecore.so');
			$zip->close();
		}else{
			mark('pack zip package failed','FAILURE');
			return returnInfo('pack zip package failed','FAILURE');
		}
		
		if(!file_exists($output_path.'/'.$Git_submit_number.'/'.$output_zip_name)){
			mark('pack zip pakcage failed','FAILURE');
			return returnInfo('pack zip pakcage failed','FAILURE');
		}
		mark('pack ZIP package success','SUCCESS');
		chdir(script_path);
	}
    	
	chdir($git_path);
	if((defined("build_model"))|| ($BranchName == "master")){
		mark("upload tags to branch:".$BranchName,"NOTE");
		$cmd="git push origin ".$BranchName." --tags";
		exec($cmd,$r,$e);
	}
	// return returnInfo("return***************","FAILURE");

	// empty_mark(2);
	// mark("uploading OSS","NOTE");
	// mark("copy zip files","NOTE");
	// $cmd='\cp \/home\/jenkins\/common\/jenkins_RN_tinker_apkbuild\/apks\/'.$BranchName.'\/'.$Git_submit_number.'\/'.$output_zip_name.' '.'\/home\/jenkins\/workspace\/'.$job_name;
	// exec($cmd,$r,$e);
	// if($e == 0){
	// 	mark("copy zip files successfully","NOTE");
	// }else{
	// 	mark("copy zip files failed","FAILURE");
	// 	return returnInfo("copy failed","FAILURE");
	// }
	empty_mark(2);
	mark("正在上传OSS","NOTE");
	if((uploadFile($ossClient, $bucket, $oss_object, $upload_file)) == false){
		mark("上传oss失败","FAILURE");
		time_mark("build通过，上传OSS失败","FAILURE");
		return returnInfo("上传oss失败","FAILURE");
	}else{
		mark("上传OSS成功","SUCCESS");
		time_mark($url,"SUCCESS");
		mark($url,"NOTE");
		// return returnInfo($url,"SUCCESS",$VersionCode);
	}
	// return returnInfo("return***************","FAILURE");

	// #上传full.apk
	mark("upload full apk","NOTE");
	$final_apk2 = $output_path.'/'.$Git_submit_number.'/'.$apkname_full_signed;
	$upload_file2=$final_apk2;
	// $oss_object2="V8/".$BranchName."/".$Git_submit_number."/".$apkname_full_signed;
	$oss_object2="V8/full_apk/".$apkname_full_signed;
	$url2="http://cdn.release.qcast.cn/".$oss_object2;
	if((uploadFile($ossClient, $bucket, $oss_object2, $upload_file2)) == false){
		mark("上传 full apk to oss失败","FAILURE");
		time_mark("build通过，上传full apk to OSS失败","FAILURE");
		return returnInfo("上传full apk to oss失败","FAILURE");
	}else{
		mark("上传 full apk to OSS成功","SUCCESS");
		mark($url2,"SUCCESS");
		// return returnInfo($url2,"SUCCESS");
	}
	// return returnInfo("return***************","FAILURE");

	mark("上传UIservice","NOTE");
	$oss_object_UIService="V8/".$BranchName."/".$Git_submit_number."/".$UIServicename;
	$upload_file_UIService=$output_path.'/'.$Git_submit_number.'/'.$UIServicename;
	$url_UIService="http://cdn.release.qcast.cn/".$oss_object_UIService;
	if((uploadFile($ossClient, $bucket, $oss_object_UIService, $upload_file_UIService)) == false){
		mark("上传 UIService apk to oss失败","FAILURE");
		mark("上传 UIService apk to OSS失败","FAILURE");
		return returnInfo("上传 UIService apk to oss失败","FAILURE");
	}else{
		mark("上传 UIService apk to OSS成功","SUCCESS");
		mark($url_UIService,"SUCCESS");
		return returnInfo($url_UIService,"SUCCESS");
	}

}
function returnInfo($info,$status,$vercode=0){
	$return_info=array(
			"status"=>$status,
			"info"=>$info,
			"vercode"=>$vercode,
			);

	return $return_info;
}
?>

