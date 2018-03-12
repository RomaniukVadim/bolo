#!/usr/bin/env bash

source settings

dry="--dry-run"

if [ -z $1 ]
then
    dry=""
fi


while read -r; do
echo '**********************************************'
echo '******** Pulling from:'
echo "******** $REPLY"
echo '**********************************************'
sshcom="ssh -i $seckey"
server="$REPLY:$remote_path"

rsync $dry \
--exclude "public/.git" \
--exclude "public/node_modules/*" \
--exclude "public/vendor/*" \
--exclude "public/storage/cms/*" \
--exclude "public/storage/temp/*" \
--exclude "public/storage/framework/*" \
--exclude "public/storage/debugbar/*" \
--delete -avze "$sshcom" ${server} ./clone/app/

break
done < servers.txt
