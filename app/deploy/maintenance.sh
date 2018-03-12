#!/usr/bin/env bash

source settings

if [ -z $1 ]
then
while read -r; do
echo '**********************************************'
echo '******** Set maintenance:'
echo "******** $REPLY"
echo '**********************************************'

ssh -i $seckey -T $REPLY <<EOT
echo "" > ${remote_path}public/mnt

EOT

done < servers.txt

else
while read -r; do
echo '**********************************************'
echo '******** Remove maintenance:'
echo "******** $REPLY"
echo '**********************************************'

ssh -i $seckey -T $REPLY <<EOT
rm ${remote_path}public/mnt

EOT

done < servers.txt

fi


