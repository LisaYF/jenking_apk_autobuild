<?php


function Modify_file($BuildInfo_list,$version_code,$version_name){

	global $git_path,$template_manifest_path;
	//1.1 获取信息编译信息
	$manufacturer=$BuildInfo_list['manufacturer'];
	$packagename=$BuildInfo_list['packagename'];
	$umengkey=$BuildInfo_list['umengkey'];
	$appname=$BuildInfo_list['appname'];
	$product_name=$BuildInfo_list['product_name'];
	$product_ID=$BuildInfo_list['product_ID'];
	$app_type=$BuildInfo_list['app_type'];
	$video_type=$BuildInfo_list['video_type'];//0=非视频;1=视频;2=非单品
	$landingAs=$BuildInfo_list['landingAs'];
	$local_homepage_key=$BuildInfo_list['local_homepage_key'];
	$local_homepage_type=$BuildInfo_list['local_homepage_type'];
	$icon_img=$BuildInfo_list['icon_img'];
	$start_img=$BuildInfo_list['start_img'];


	//1.2 选择AndroidManifest.xml的模板
	//$tem_manifest=template_path.'/AndroidManifest.xml';
	$tem_manifest=$template_manifest_path;


	//2.修改manifest模板，生成一份manifest
	$doc = new DOMDocument("1.0","utf-8");
	$doc->load("$tem_manifest");
	$root=$doc -> documentElement;

	//2.1修改包名、version-name、version-code
	$manifest=$doc->getElementsByTagName("manifest")->item(0);
	foreach($manifest->attributes as $top){
		if('package' == $top->nodeName){
			$top->nodeValue = "$packagename";
		}elseif('android:versionCode' == $top->nodeName){
			$top->nodeValue = "$version_code";
		}elseif('android:versionName' == $top->nodeName){
			$top->nodeValue = "$version_name";
		}
	}

	//2.2修改应用名称:(application->android:label)

/*	$application=$root->getElementsByTagName("application")->item(0);
	foreach($application->attributes as $application_node){
		if('android:label' == $application_node->nodeName){
			$application_node->nodeValue = $product_name;
			echo $product_name.PHP_EOL;
		}
	}
 */


	//2.3修改meta-data中相关信息

	$meta_data=$root->getElementsByTagName("application")->item(0)->getElementsByTagName("meta-data");
	$len=$meta_data->length;
	$i=0;
	$qcast_index=-1;
	$umeng_channel_index=-1;
	$umeng_appkey_index=-1;

	if($len > 0){
		foreach($meta_data as $meta_node){
			foreach($meta_node->attributes as $attrib){
				if('android:name' == $attrib->nodeName && 'QCodeContentId' == $attrib->nodeValue)
				{
					$qcast_index = $i;
				}
				if('android:name' == $attrib->nodeName && 'UMENG_CHANNEL' == $attrib->nodeValue)
				{
					$umeng_channel_index = $i;
				}
				if('android:name' == $attrib->nodeName && 'UMENG_APPKEY' == $attrib->nodeValue)
				{
					$umeng_appkey_index = $i;
				}
			}
			$i+=1;
		}
	}else{
		mark("get metadata data failed","FAILURE");
		return false;
	}

	if(($qcast_index > -1)&&($umeng_appkey_index > -1)&&($umeng_channel_index > -1)){
		foreach($meta_data->item($umeng_appkey_index)->attributes as $um_key){
			if($um_key->nodeName == "android:value"){
				$um_key->nodeValue = $umengkey;
			}
		}

		foreach($meta_data->item($umeng_channel_index)->attributes as $um_channel){
			if($um_channel->nodeName == "android:value"){
				$um_channel->nodeValue = $manufacturer;
			}
		}

		foreach($meta_data->item($qcast_index)->attributes as $qcast_key){
			if($qcast_key->nodeName == "android:value"){
				$qcast_key->nodeValue = $appname;
			}
		}
	}else{
		mark("修改meta-data失败","FAILURE");
		return false;
	}


	//2.4判断是不是Launcher，修改节点,默认模板是apk的manifest模板
	if('launcher' == $landingAs){
		$intent_filter = $root->getElementsByTagName("application")->item(0)->getElementsByTagName("activity")->item(0)->getElementsByTagName("intent-filter");
		$category_parent = $intent_filter->item(0)->getElementsByTagName("category");
		foreach($category_parent as $category_child){
			foreach($category_child->attributes as $node){
				if(!(strpos($node->nodeValue,'LAUNCHER')==false)){
					//删除这个category代表是apk的节点
					$category_child->parentNode->removeChild($category_child);
				}
			}
		}
		//添加Launcher的category节点
		$intent_filter=$doc->getElementsByTagName("intent-filter")->item(0);
		$new_node=$doc->createElement("category");
		$new_node->setAttribute("android:name","android.intent.category.HOME");
		$new_node2=$doc->createElement("category");
		$new_node2->setAttribute("android:name","android.intent.category.DEFAULT");
		$intent_filter->appendChild($new_node);
		$intent_filter->appendChild($new_node2);
	}

	//3. 备份manifest，并生成新的manifest的文件
	$tmp_manifest=tmp_path.'/AndroidManifest_'.$manufacturer.'.xml';

	if(file_exists($tmp_manifest)){
		$ctime_manifest=filectime($tmp_manifest);
		rename($tmp_manifest,tmp_path.'/AndroidManifest_'.$manufacturer.'_'.date("Ymd-Hi",$ctime_manifest).'.xml');
	}
	$doc->save($tmp_manifest);


	//4. 修改应用名称:"../android/qcastcoat/coatapp/src/main/res/values/strings.xml"
	//name="app_name">CoatApp<

	$tem_string=$git_path."/android/qcastcoat/coatapp/src/main/res/values/strings.xml";
	$tmp_string=tmp_path.'/strings_'.$manufacturer.'.xml';

	if(file_exists($tmp_string)){
		$ctime_string=filectime($tmp_string);
		rename($tmp_string,tmp_path.'/strings_'.$manufacturer.'_'.date("Ymd-Hi",$ctime_string).'.xml');
	}

	
	$appname_str=file_get_contents($tem_string);
	$update_str=preg_replace('/name="app_name">(.*)</','name="app_name">'.$product_name."<",$appname_str);

	file_put_contents($tmp_string,$update_str);

	$xml_list=array(
			"file_manifest_path"=>$tmp_manifest,
			"file_string_path"=>$tmp_string,
	);

	if(file_exists($tmp_manifest) && file_exists($tmp_string)){
		mark("Success generates new manifest file and string file","SUCCESS");
		return $xml_list;
	}else{
		mark("Success generates new manifest file or generates string failed","FAILURE");
		return false;
	}


}

?>
