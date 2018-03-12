#!/bin/sh

if [ -z $1 ]
then
    filename=dump.sql
else
	filename=$1
fi

mysql -u root -ppsswd bolo < $filename