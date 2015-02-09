#!/bin/bash


url='http://cdn2015.localhost/hello/Fair%20Vote%20Canada'

httpCode=`curl -o /dev/null --silent --head --write-out '%{http_code}\n' $url`

if [ "$httpCode" -lt 200 -a "$httpCode" -gt 399 ] ; then 
	echo I did not get a status code between 200 and 399 for $url . I got $httpCode
	echo See http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html to configure apache
	exit 1; 
fi


#content=`curl --silent --head --write-out '%{http_code}\n' $url`
content=`curl --silent $url `


needle="Hello Fair Vote Canada!"

if [[ ! "$content" =~ "$needle" ]]; then
  echo "Can't find \"$needle\" at \"$url\" .  The content was \n\n" $content  "\n\n"
  echo See http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html to configure apache
  exit 1
fi


exit 0
