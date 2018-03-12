#!/usr/bin/env bash

source settings

while read -r; do
echo '**********************************************'
echo '******** Creating dump:'
echo "******** $REPLY"
echo '**********************************************'

ssh -i $seckey -T $REPLY <<EOT
cd $remote_path/db
bash create_dump.sh

EOT

break
done < servers.txt
