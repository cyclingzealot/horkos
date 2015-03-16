#!/bin/bash


url='cdn2015.localhost/test.html'

httpCode=`curl -o /dev/null --silent --head --write-out '%{http_code}\n' $url`

if [ "$httpCode" -lt 200 -a "$httpCode" -gt 399 ] ; then 
	echo I did not get a status code between 200 and 399 for $url . I got $httpCode
	echo See http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html to configure apache
	exit 1; 
fi


#content=`curl --silent --head --write-out '%{http_code}\n' $url`
content=`curl --silent $url `


needle="This is static html test page within the symfony dir!"

if [[ ! "$content" =~ "$needle" ]]; then
  echo "Can't find \"$needle\" at \"$url\" .  The content was \n\n" $content  "\n\n"
  echo See http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html to configure apache
  exit 1
fi


exit 0
