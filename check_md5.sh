#!/bin/bash
#Launcher编译处理加入同版本的校验处理：比较所有文件的md5值（不比较"assets/vendor"和"META-INF"目录里面的内容）
#$1表示BranchName，$2表示Git_submit_number,$3表示$homepage_local_num
cd ./apks/$1/$2/
rm -rf ./check_md5/
same_git_submit_number=`ls *full_signed*.apk|wc -l`
if [ "$same_git_submit_number" = "1" ];then
    echo "this is first time to build"
    echo "md5_same" 
else 
    echo "has $same_git_submit_number same version apk"
    #pri_apk=`ls -l *full_signed*.apk|sed -n '1p'|awk -F ' ' '{print $9}'`
    pri_apk=`ls -l *full_signed*.apk|sed '/$3/'d|sed -n '1p'|awk -F ' ' '{print $9}'`
    
    #latest_apk=`ls -l *full_signed*.apk|sed -n '$p'|awk -F ' ' '{print $9}'`
    latest_apk=`ls -l *full_signed_$3_*.apk|awk -F ' ' '{print $9}'`
    echo "compare 1: $pri_apk 2: $latest_apk"

    mkdir -p ./check_md5/1/ && mkdir -p ./check_md5/2/
    unzip -oq $pri_apk -d ./check_md5/1/ && unzip -oq $latest_apk -d ./check_md5/2/
    rm -rf ./check_md5/1/assets/vendor/ && rm -rf ./check_md5/2/assets/vendor/ && rm -rf ./check_md5/1/META-INF/ && rm -rf ./check_md5/2/META-INF/
    
    echo "1: $pri_apk"
    find ./check_md5/1/ -type f -not \( -name '.*' \) -exec md5sum {} \;|awk -F ' ' '{print $1}'
    
    echo "2: $latest_apk"
    find ./check_md5/2/ -type f -not \( -name '.*' \) -exec md5sum {} \;|awk -F ' ' '{print $1}'
    
    pri_apk_md5=`find ./check_md5/1/ -type f -not \( -name '.*' \) -exec md5sum {} \;|awk -F ' ' '{print $1}'`
    latest_apk_md5=`find ./check_md5/2/ -type f -not \( -name '.*' \) -exec md5sum {} \;|awk -F ' ' '{print $1}'`
    
    if [ "$pri_apk_md5" = "$latest_apk_md5" ];then
        echo "md5_same"     
    else
        echo "md5_different"
    fi
fi