<?php

function check_input($manufacturer,$git_version,$homepage_num){
	mark("check_input manufacturer:".$manufacturer,"NOTE");
	$build_info=getInfo($manufacturer);
	if(!($build_info)){
		return false;
	}else{
		$useLocalHomePage=$build_info["useLocalHomePage"];
		$local_homepage_key=$build_info["local_homepage_key"];
		$local_homepage_type=$build_info["local_homepage_type"];
		$product_ID=$build_info["product_ID"];
		$landingAs=$build_info["landingAs"];
	}



	$git=git_version($git_version);
	mark("git:".$git,"NOTE");
	if(!($git)){
		return false;
	}
	mark("useLocalHomePage:".$useLocalHomePage,"NOTE");
	if($useLocalHomePage == "yes"){
		$get_local_home_page=getLocalHomepage($local_homepage_key,$local_homepage_type,$homepage_num);
		mark("get_local_home_page:".$get_local_home_page,"NOTE");
		if(!($get_local_home_page)){
			return false;
		}else{
			$homepage_num=$get_local_home_page["homepage_num"];
			mark("homepage_num:".$homepage_num,"NOTE");
		}	
	}else{
		$homepage_num="0000";
	}
	mark("confirm homepage_num:".$homepage_num,"NOTE");
	$build_info["git"]=$git;
	$build_info["homepage_num"]=$homepage_num;
	$build_info["file_cfg_path"]=$get_local_home_page["file_cfg_path"];
	$build_info["file_zip_path"]=$get_local_home_page["file_zip_path"];
	$build_info["file_zip_name"]=$get_local_home_page["file_zip_name"];
	$build_info["file_cfg_name"]=$get_local_home_page["file_cfg_name"];
	mark("confirm build_info:".$build_info,"NOTE");
	return $build_info;


}



