
<?php

function Get_Build_Version($git_ci_num, $apktype = "RN_build"){
	global $BranchName;
	$db = new MySql();
	$sql = "SELECT * FROM `qcast_apkdownload_manage` where apktype = '".$apktype."' and branchname = '".$BranchName."' order by id desc limit 1";
	$n = $db->select($sql, $result);
	mark("n:".$n,"NOTE");
	if($n == 0){
		$git_version=0;
		$one_more="zero";
	}else{
		$git_version=$result[0]['git_version'];
		$one_more=$result[0]['comment'];
	}
	mark("git_version:".$git_version,"NOTE");
	$spe_branch = array('master','release_build');
	if(!(in_array($BranchName,$spe_branch)) and ($git_version > $git_ci_num)){
			$one_more = 'try_again';
	}

	if(($git_version < $git_ci_num)||!(strpos($one_more, 'try_again')===false)){
		$date_insert=array(
			"apktype"=>$apktype,
			"git_version"=>$git_ci_num,
		);
		$id=$db->insert('qcast_apkdownload_manage',$date_insert);
		
		$vercode=$id;
		$version_1st_num=1;
		$version_2nd_num=intval(floor($id/289))-1;
		$version_3rd_num=$id%289;
		$version=$version_1st_num.'.'.$version_2nd_num.'.'.$version_3rd_num;
		$update_sql="update `qcast_apkdownload_manage` set vercode='".$vercode."', version='".$version."', branchname='".$BranchName."' where id=".$id;
		mark("SQL:".$update_sql,"NOTE");
		$db->update($update_sql);
		return array(
			"version"=>$version,
			"vercode"=>$vercode,
		);
	}else{
		$old_sql = "SELECT * FROM `qcast_apkdownload_manage` where git_version = '".$git_ci_num."' and branchname = '".$BranchName."' order by id desc limit 1";
		//echo $old_sql.PHP_EOL;
		mark("git_ci_num:".$git_ci_num,"NOTE");
		mark("BranchName:".$BranchName,"NOTE");
		$n = $db->select($old_sql,$old_result);
		mark("n:".$n,"NOTE");
		mark("old_sql:".$old_sql,"NOTE");
		mark("old_result:".$old_result,"NOTE");
		if($n==1){
			return array(
				"version"=>$old_result[0]['version'],
				"vercode"=>$old_result[0]['vercode'],
			);
		}else{
			mark("Failed to obtain version information:this version".$git_ci_num."Not supported web_build","FAILURE");
			return false;
		}
	}
	$db->destroy();
	$db = null;
}


?>

