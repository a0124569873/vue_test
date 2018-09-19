#! /bin/sh

function throw_error() {
    [ z$1 == z ] || echo $1
    exit 1
}

BUILD_START=$(date +%s)

echo "Start Build By: `git log -1 --pretty=format:%cn`"

ROOT=$PWD

cd $WWW_ROOT_DIR
git reset --hard || throw_error
git clean -xdf || throw_error
git pull || throw_error
cd $ROOT

# 1. check and build

cd view
if [ ! -d node_modules ]; then
    echo "node_modules extra start ...."
    tar -xf $NODE_MODULES_PATH  || throw_error
    echo "node_modules extra finish"
fi

npm run build || throw_error "npm run build error!"

cd $ROOT
cd api
#thinkphp runtime dir
if [ ! -d runtime ]; then
    echo "create runtime dir ..."
    mkdir runtime
    chmod -R 777 runtime
    echo "runtime dir created"
fi
#thinkphp think-captcha vendor extra
if [ ! -d thinkphp ]; then
    echo "thinkphp extra start ...."
    tar -xf $TP_FM_PATH || throw_error
    tar -xf $TP_VENDOR_PATH || throw_error
    echo "thinkphp extra finish"
fi

cd $ROOT
# 2.info
USER=$(git log --pretty=format:"%cn"|head -1)
MESSAGE=$(git log --pretty=format:"%s"|head -1)
URL=$(git remote -v |head -1)

# 3. work

rm -rf $WWW_ROOT_DIR/www_root.zip
mv api www_root
rm -rf .gitignore LICENSE.txt Readme.txt .travis.yml
zip -rq $WWW_ROOT_DIR/www_root.zip www_root || throw_error

# 4. commit
cd $WWW_ROOT_DIR
git add . || throw_error
git commit -m "Auto Build By `git log -1 --pretty=format:%cn`:
 $USER : $MESSAGE，
 project_url:$URL,
 共耗时："$(($(date +%s) - ${BUILD_START}))" 秒
 " || throw_error
git push || throw_error

echo "Build Finish!"