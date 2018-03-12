#!/bin/sh

sudo iptables -A INPUT -m string --algo bm --string "User-Agent: Mozilla/5.0 Jorgee" -j DROP