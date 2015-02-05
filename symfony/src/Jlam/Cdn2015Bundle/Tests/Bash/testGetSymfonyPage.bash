#!/bin/bash


url='cdn2015.localhost/test'

httpCode=`curl -o /dev/null --silent --head --write-out '%{http_code}\n' $url`

if [ "$httpCode" -lt 200 -a "$httpCode" -gt 399 ] ; then 
	echo I did not get a status code between 200 and 399 for $url . I got $httpCode
	exit 1; 
fi


#content=`curl --silent --head --write-out '%{http_code}\n' $url`
content=`curl --silent --head $url `


needle="This is a symfony page"

if [[ ! "$content" =~ "$needle" ]]; then
  echo "Can't find \"$needle\" at \"$url\""
  exit 1
fi


exit 0
