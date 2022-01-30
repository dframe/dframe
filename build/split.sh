#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="v4.1"

function split()
{
    SHA1=`./build/splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote view git@github.com:dframe/view.git
remote token git@github.com:dframe/token.git
remote loader git@github.com:dframe/loader.git
remote router git@github.com:dframe/router.git
remote cron git@github.com:dframe/cron.git
remote asset git@github.com:dframe/asset.git
remote console git@github.com:dframe/console.git
remote config git@github.com:dframe/config.git

split 'src/View' view
split 'src/Token' token
split 'src/Loader' loader
split 'src/Router' router
split 'src/Cron' cron
split 'src/Asset' asset
split 'src/Console' console
split 'src/Config' config
 
 
