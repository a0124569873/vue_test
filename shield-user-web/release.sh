#!/bin/bash
SOURCE_DIR=${PWD##*/}
TMP_DIR=${SOURCE_DIR}_tmp 
ZIP_FILE=${TMP_DIR}.tar.gz

cd ..

# remove .git
cp -r $SOURCE_DIR $TMP_DIR
rm -rf ${TMP_DIR}/.git
rm -rf ${TMD_DIR}/.gitignore
rm -rf ${TMP_DIR}/release.sh
rm -rf ${TMP_DIR}/view/node_modules
rm -rf ${TMP_DIR}/api/runtime

# env
touch ${TMP_DIR}/api/.env
echo -e 'ES_HOST=123.133.84.201:9200,123.133.84.202:9200,123.133.84.203:9200\nZK_HOST=123.133.84.201:2181,123.133.84.202:2181,123.133.84.202:2181' > ${TMP_DIR}/api/.env

# runtime
if [ ! -d ${TMP_DIR}/api/runtime ];then
    mkdir ${TMP_DIR}/api/runtime
fi

tar -czvf $ZIP_FILE $TMP_DIR

scp -P 22018 $ZIP_FILE root@123.133.84.200:/usr/share/nginx/html/
rm -rf $ZIP_FILE
rm -rf $TMP_DIR

ssh -p 22018 root@123.133.84.200 "
cd /usr/share/nginx/html
tar -xf $ZIP_FILE
rm -rf $ZIP_FILE
chmod -R 777 ${TMP_DIR}/api/runtime
chcon -R -t httpd_sys_rw_content_t ${TMP_DIR}/api/runtime
rm -rf $SOURCE_DIR
mv $TMP_DIR $SOURCE_DIR
"
