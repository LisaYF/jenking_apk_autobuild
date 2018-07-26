<?php
date_default_timezone_set('PRC');

function addFileToZip($dir, $zip) {
	$handler = opendir($dir); 
	while (($filename = readdir($handler)) !== false) {
		if ($filename != "." and $filename != "..") {
			if (is_dir($dir . "/" . $filename)) {
				addFileToZip($dir . "/" . $filename, $zip);
			} else {
				$zip->addFile($dir . "/" . $filename);
			}   
		}   
	}   
	@closedir($dir);
}
/*function Delete_file($del_name) {
        if(is_dir($del_name)){
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
                if(rmdir($del_name)){
                        return true;
                }else {
                        return false;
                }
        }elseif(is_file($del_name)){
                if(unlink($del_name)){
                        return true;
                }else {
                        return false;
                }
        }else{
                return false;
        }
}
*/
function Delete_File_End($dir,$txt){
  if(is_dir($dir)){
    $files = scandir($dir);
    foreach($files as $filename){
      if($filename!='.' && $filename!='..'){
        if(!is_dir($dir.'/'.$filename)){
          if(preg_match("/.*".$txt."$/",$filename)){
            unlink($dir.'/'.$filename);
          }
        }else{
          Delete_File_End($dir.'/'.$filename,$txt);
        }
      }
    }
  }
}

?>
