horkos
=======

Horkos WebScrapping live results of canadian elections (provincial as well) + wasted votes analysis


"*In Greek mythology, Horkos personifies the curse that will be inflicted on any person who swears a false oath*".  
-- https://en.wikipedia.org/wiki/Horkos


<br>
Democracy?  What about this 50% unrepresented votes?
<br><br><br><br>



Installation
=======
Some requirements:
* `sudo apt-get install apache2 php5-curl php5-cli php5-gd`
<br><br>

* Composer:<br> 
 `cd /usr/local/bin;  sudo apt-get install curl && curl -sS https://getcomposer.org/installer | php`
 <br><br>
 
 * To install composer on dreamhost, see http://www.geekality.net/2013/02/01/dreamhost-composer/
 <br><br>
 
* Populate the symphony dir with vendor extensions: 
	`cd horkos/symfony; composer.phar install`
    <br><br>
    
* Generate the bootstrap file: `./vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php`<br><br>
 
 

 
 
* Also put the http conf in the right place:<br>
	`cd horkos/doc/ ;`<br>
	`sudo cp horkos2015.conf /etc/apache2/sites-available/ ;`<br>
	`cd /etc/apache2/sites-enabled/ ;`<br>
	`sudo ln -s ../sites-available/horkos2015.conf ;`<br>
    <br>
    
* Set up permissions as per http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
<br><br>
  *   Using ACL on a system that supports *chmod +a*
	  ```
	  rm -rf app/cache/* ;
	  rm -rf app/logs/* ;

      HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
	  sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
	  sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
      ```
	<br>
	* Using ACL on a system that **does not** support *chmod +a*
	
	   ```
       HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
	   sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
	   sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
       ```
<br>
*  If wish to have some static data for devlopement : `cd horkos/eshu/ ; sudo tar -xzf data.tar.gz`<br>
 Or webScrape data from http://enr.elections.ca/ElectoralDistricts.aspx using `horkos/eshu/cdn.bash`
