horkos
=======

For scrapping live results of canadian elections (provincial as well) + wasted votes analysis


"In Greek mythology, Horkos personifies the curse that will be inflicted on any person who swears a false oath".  -- https://en.wikipedia.org/wiki/Horkos

Democracy?  What about this 50% unrepresented votes?


Installation
=======
Some requirements:
* sudo apt-get install apache2 php5-curl php5-cli
* Composer: cd /usr/local/bin;  sudo apt-get install curl && curl -sS https://getcomposer.org/installer | php
* Populate the symphony dir with vendor extensions: 
	cd horkos/symfony; composer.phar install
* Also put the http conf in the right place:
	cd horkos/doc/
	sudo cp horkos2015.conf /etc/apache2/sites-available/
	cd /etc/apache2/sites-enabled/
	sudo ln -s ../sites-available/horkos2015.conf
* Set up permissions as per http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
	# Using ACL on a system that supports chmod +a
	rm -rf app/cache/*
	rm -rf app/logs/*

	HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
	sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
	sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
	
	
	# Using ACL on a system that does not support chmod +a
	HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
	sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
	sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs

