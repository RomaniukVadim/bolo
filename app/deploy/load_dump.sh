#!/usr/bin/env bash

source settings

while read -r; do
echo '**********************************************'
echo '******** Loading dump:'
echo "******** $REPLY"
echo '**********************************************'

ssh -i $seckey -T $REPLY <<EOT
cd $remote_path/db
bash load_dump.sh

EOT

break
done < servers.txt
