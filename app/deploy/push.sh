#!/usr/bin/env bash

source settings

dry="--dry-run"

if [ -z $1 ]
then
    dry=""
fi

read -n 1 -p "Proceed push (y/n)? " answer

if echo "$answer" | grep -iq "^y" ;then
	echo ""
else
	echo "Exiting"
	exit
fi

while read -r; do
echo '**********************************************'
echo '******** Pushing to:'
echo "******** $REPLY"
echo '**********************************************'
sshcom="ssh -i $seckey"
server="$REPLY:$remote_path"

rsync $dry \
--rsync-path="sudo rsync" \
--chown=ubuntu:ubuntu \
--chmod=Dug=rwx,Do=rx,Fug=rw,Fo=r  \
--exclude "*.log" \
--exclude "public/.git" \
--exclude "public/node_modules/*" \
--exclude "public/vendor/*" \
--exclude "public/storage/cms/*" \
--exclude "public/storage/temp/*" \
--exclude "public/storage/framework/*" \
--exclude "public/storage/debugbar/*" \
--delete \
-avze "$sshcom" ./clone/app/ ${server}

ssh -i $seckey -T $REPLY <<EOT
echo $remote_path
cd $remote_path/public
composer install
php artisan october:up
php artisan cache:clear
sudo -i
cd $remote_path
chown www-data:www-data *.log
chown -R www-data:www-data public/storage
chown -R www-data:www-data public/themes
service nginx reload

EOT

done < servers.txt
