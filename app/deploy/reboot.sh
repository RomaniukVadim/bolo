#!/usr/bin/env bash

source settings

while read -r; do
echo '**********************************************'
echo '******** Rebooting:'
echo "******** $REPLY"
echo '**********************************************'

ssh -i $seckey -T $REPLY <<EOT
sudo -i
reboot

EOT

done < servers.txt
