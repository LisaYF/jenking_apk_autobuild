<?php
use OSS\OssClient;
use OSS\Core\OssException;

spl_autoload_register('classLoader');





function classLoader($class)
{
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
	if (file_exists($file)) {
		require_once $file;
	}
}



function getOssClient(){
	#此处填写自己公司的OSS账号信息
	$accessKeyId = '……';
	$accessKeySecret = '……';
	$endpoint = '……';

	try {
		$ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
	} catch (OssException $e) {
		//printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
		printf($e->getMessage() . "\n");
		return null;
	}
	//printf(__FUNCTION__ . "creating OssClient instance: SUCCESS\n");
	return $ossClient;
}

function uploadFile($ossClient, $bucket, $object, $filePath){

	$options = array();

	try {
		$ossClient->uploadFile($bucket, $object, $filePath, $options);
	} catch (OssException $e) {
		//printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return false;
	}


	//print(__FUNCTION__ . ": OK" . "\n");
	return "http://". $bucket .".oss-cn-qingdao.aliyuncs.com/". $object;
}

function doesObjectExist($ossClient, $bucket, $object)
{
	try {
		$exist = $ossClient->doesObjectExist($bucket, $object);
	} catch (OssException $e) {
		//printf("检查OSS上文件是否存在: FAILED\n");
		printf($e->getMessage() . "\n");
		return false;
	}
	//print("检查OSS上文件是否存在: OK" . "\n");
	return $exist;
}

function getObjectToLocalFile($ossClient, $bucket, $object, $localfile)
{
	$options = array(
			OssClient::OSS_FILE_DOWNLOAD => $localfile,
			);

	try {
		$ossClient->getObject($bucket, $object, $options);
	} catch (OssException $e) {
		//printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return false;
	}
	return true;

}

?>
