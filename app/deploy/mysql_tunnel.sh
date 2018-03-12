#!/usr/bin/env bash

source settings


while read -r; do
echo '**********************************************'
echo '******** Setting tunnel on 3310 port to mysql on:'
echo "******** $REPLY"
echo '**********************************************'

ssh -i $seckey -L 0.0.0.0:3310:localhost:3306 -t $REPLY < /dev/tty

break
done < servers.txt
