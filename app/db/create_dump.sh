#!/bin/sh

if [ -z $1 ]
then
    filename=dump.sql
else
	filename=$1
fi


mysqldump -u root -ppsswd bolo > $filename