<?php
function endsWith($result,$BranchName)
{
    $length = strlen($BranchName);
    return $length === 0 || 
    (substr($result, -$length) === $BranchName);
}


function git_version($HEAD='HEAD'){
	global $BranchName,$git_path;
	if(is_dir($git_path)){
			chdir($git_path);
	}else{
			mark("Project folder does not exist","FAILURE");
			return false;
	}
	
	// mark("delete workspace's ZIP","NOTE");
	// $cmd1="rm -rf *.zip";
	// exec($cmd1,$r1,$e1);
	
	mark("Pull the remote Repository code","NOTE");
	$cmd="git reset --hard HEAD";
	exec($cmd,$r,$e);


	// $cmd="git checkout master";
	// exec($cmd,$r,$e);

	// $cmd="git pull";
	// exec($cmd,$r,$e);

	$cmd="git checkout ".$BranchName;
	exec($cmd,$r,$e);

	$cmd="git reset --hard origin/".$BranchName;
	exec($cmd,$r,$e2);
	if(!($e2 == 0)){
		mark("Remote Repository code pull failed","FAILURE");
		return false;
	}

	mark("Pull the specified version of the code","NOTE");
	$cmd="git reset --hard ".$HEAD;
	exec($cmd,$r0,$e0);	
	if(!($e0 == 0)){
		mark("the specified version".$HEAD."pull code failed","FAILURE");
		return false;
	}

	mark("check whether this git_version:".$HEAD." is belong to this BranchName:".$BranchName." ?","NOTE");
	$cmd3="git branch --contains ".$HEAD;
	exec($cmd3,$r3,$e3);
	if($e3 == 0){
		mark("This command:".$cmd3." exec success","NOTE");
		$result=$r3[0];
		mark("exec result:".$result,"NOTE");
		mark("whether this result ends with BranchName:".$BranchName,"NOTE");
		$ret = endsWith($result,$BranchName);
		if(!($ret)){
			mark("Not ends with BranchName:".$BranchName.", git_version:".$HEAD." not belong to BranchName:".$BranchName." failed","FAILURE");
			return false;
		}else{
			mark("This result ends with BranchName:".$BranchName.", git_version:".$HEAD." belong to BranchName:".$BranchName,"NOTE");
		}
	}else{
		mark("the command:".$cmd3." exec failed","FAILURE");
		return returnInfo("check this git_version:".$HEAD." is belong to this BranchName:".$BranchName."？，failed","FAILURE");
	}	

	$cmd="git log -1 --pretty=format:'%h' $HEAD";
	exec($cmd,$r1,$e1);
	$head_hash=$r1[0];

	$cmd="git rev-list --count $head_hash";
	exec($cmd,$r2,$e2);
	$head_version=$r2[0];
	chdir(script_path);
	mark("git_version=".$head_version,"NOTE");
	//new
	mark ("git_version+2000","NOTE");
	$head_version=$head_version+2000;
	return $head_version;
}

?>

