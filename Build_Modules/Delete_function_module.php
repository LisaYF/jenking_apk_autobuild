<?php

function delete_not_empty_dir($delete_not_empty_path,$git_path){
	chdir($git_path);
	mark("delete not empty folders:".$delete_not_empty_path,"NOTE");
	//$delete_path='node_modules/jsi/android/build/*';
	$delete_path=$delete_not_empty_path.'/*';
	$cmd='rm -rf '.$delete_path;
	exec($cmd,$r,$e);
	if($e == 0){
		mark("delete '.$delete_not_empty_path.' success ","NOTE");
	}else{
		mark("delete '.$delete_not_empty_path.' failed ","FAILURE");
		return returnInfo("delete '.$delete_not_empty_path.' failed ","FAILURE");
	}

}

function Delete_file($del_name) {
	mark("Deleting folder:".$del_name,"NOTE");
	if(is_dir($del_name)){
		//	  先删除目录下的文件：
		$dh=opendir($del_name);
		while ($file=readdir($dh)) {
			if($file!="." && $file!="..") {
				$fullpath=$del_name."/".$file;
				if(!is_dir($fullpath)) {
					unlink($fullpath);
				} else {
					Delete_file($fullpath);
				}
			}
		}
		closedir($dh);

		//	删除当前文件夹：
		if(rmdir($del_name)){
			// mark("Success to del dir:".$del_name,"NOTE");
			return true;
		}else {
			mark("Failure to del dir:".$del_name,"NOTE");
			return false;
		}

	}elseif(is_file($del_name)){
		if(unlink($del_name)){
			// mark("Success to del file:".$del_name,"NOTE");
			return true;
		}else {
			mark("Failure to del file:".$del_name,"NOTE");
			return false;
		}

	}else{
		mark("The file to delete does not exists:".$del_name,"NOTE");
		return false;
	}

}
?>
