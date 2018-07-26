<?php
//include_once "./Print_function_module.php";
function build_dex(){
	global $build_dex_dir;
	chdir($build_dex_dir);
	mark("current directory:".getcwd(),"NOTE");
	$dex_file = "qcast_sdk_core.dex";
	if (file_exists($dex_file)){
		mark("Delete the build's dex file in the previous project","NOTE");
		if (!unlink($dex_file)){
			mark($dex_file."fail to delete","NOTE");
		}else{
			mark($dex_file."have deleted","NOTE");
		}
	}

	$cmd="./gradlew :tv-service:service-server-core:clean";
	mark("clean project","NOTE");
	mark($cmd,"WARNING");
	exec($cmd,$r,$e);
	if($e == 0){
		mark("gradle clean successfully","NOTE");
	}else{
		system($cmd,$va);
		echo $va;
		mark("gradle clean failed","FAILURE");
		return returnInfo("gradle clean failed","FAILURE");
	}


	$cmd = './gradlew :tv-service:service-server-core:assembleRelease';
	mark("compile jar package which dex required","NOTE");
	mark($cmd,"WARNING");
	exec($cmd,$r,$e);
	if($e == 0){
		mark("./gradlew :tv-service:service-server-core:assembleRelease successfully","NOTE");
	}else{
		system($cmd,$va);
		echo $va;
		mark("./gradlew :tv-service:service-server-core:assembleRelease failed","FAILURE");
		return returnInfo("./gradlew :tv-service:service-server-core:assembleRelease failed","FAILURE");
	}

 
	$arr_jars = array(
		'./tv-service/service-server-core/build/intermediates/bundles/release/classes.jar',
		'./common/mtdownloader/build/intermediates/intermediate-jars/release/classes.jar',
		'./common/process-base/build/intermediates/intermediate-jars/release/classes.jar',
		'./tv-service/service-public/build/intermediates/intermediate-jars/release/classes.jar',
		'./common/user-log/build/intermediates/intermediate-jars/release/classes.jar',
	);
	
	$num_jars=0;
	$num_unkonown_jars=0;
	$arr_jars_name = null;

	foreach($arr_jars as $jar){
		$num_jars+=1;
		$arr_jars_name = $arr_jars_name." ".$jar;
		if(!file_exists($jar)){
			mark("can't find jar package:".$jar,"FAILURE");
			$num_unkonown_jars+=1;
		}
	}

	if($num_unkonown_jars == 0){
		mark("jar package finished","SUCCESS");
	}else{
		mark("jar package incomplete","FAILURE");
		return false;
	}


	mark('start compile qcast_sdk_core.dex',"NOTE");
	$cmd = dx.' --dex --output=qcast_sdk_core.dex '.$arr_jars_name ;
	mark($cmd,"WARNING");
	exec($cmd,$r,$e);
	if(!file_exists('qcast_sdk_core.dex')){
		mark("qcast_sdk_core.dex compile failed","FAILURE");
		return false;
	}elseif($e=0){
		mark("success to compile qcast_sdk_core.dex","SUCCESS");
	}

	chdir(script_path);
	mark("current directory:".getcwd(),"NOTE");
}

?>
