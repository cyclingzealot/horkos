#!/bin/bash


#../bin/resetPerms.bash `whoami`

if [ ! -d "../vendor" ]; then 
    cd ..
    composer install
    cd -
fi


echo > logs/test.log ; 
echo > logs/dev.log ; 
echo > logs/prod.log ; 

rm -rf cache/* 2> /dev/null

clear 

length=`tput cols`
yes '#' |  head -n $length | tr -d "\n" | xargs echo

phpunit ../src/Jlam/HorkosBundle/Tests/TallyHolderTest.php
sleep 5
phpunit $1

/usr/bin/notify-send -t 8000 "$0 done"
