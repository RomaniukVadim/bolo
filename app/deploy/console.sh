#!/usr/bin/env bash

source settings

while read -r; do
echo '**********************************************'
echo '******** Connecting to:'
echo "******** $REPLY"
echo '**********************************************'
ssh -i $seckey -t $REPLY < /dev/tty
done < servers.txt

