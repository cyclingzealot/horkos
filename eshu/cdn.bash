#!/usr/bin/env bash

START=$(date +%s.%N)

#exit when command fails (use || true when a command can fail)
set -o errexit

#exit when your script tries to use undeclared variables
set -o nounset

#(a.k.a set -x) to trace what gets executed
set -o xtrace

# in scripts to catch mysqldump fails 
set -o pipefail

# Set magic variables for current file & dir
__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__root="$(cd "$(dirname "${__dir}")" && pwd)" # <-- change this
__file="${__dir}/$(basename "${BASH_SOURCE[0]}")"
__base="$(basename ${__file})"
ts=`date +'%Y%m%d-%H%M%S'`

#Set the config file
configFile="$HOME/.binJlam/templateConfig"

#Ensure only one copy is running
pidfile=$HOME/.${__base}.pid
if [ -f ${pidfile} ]; then
   #verify if the process is actually still running under this pid
   oldpid=`cat ${pidfile}`
   result=`ps -ef | grep ${oldpid} | grep ${scriptname}`  

   if [ -n "${result}" ]; then
     echo "Script already running! Exiting"
     exit 255
   fi
fi

#grab pid of this process and update the pid file with it
pid=`ps -ef | grep ${__base} | head -n1 |  awk ' {print $2;} '`
echo ${pid} > ${pidfile}


#Capture everything to log
log=~/log/$__base-${ts}.log
exec >  >(tee -a $log)
exec 2> >(tee -a $log >&2)
touch $log
chmod 600 $log


#Check that the config file exists
#if [[ ! -f "$configFile" ]] ; then
#        echo "I need a file at $configFile with ..."
#        exit 1
#fi


echo Begin `date`  .....

### BEGIN SCRIPT ###############################################################

electionID=`echo $__base | cut -d '.' -f 1`

for lang in e f; do 
	dataDir="$__dir/$electionID/$lang/"
	workDir="$dataDir/work/"
	readyDir="$dataDir/ready"

	mkdir -p $workDir $readyDir

	sourceUrl="http://enr.elections.ca/ElectoralDistricts.aspx?lang=$lang"
	sourceFile="$dataDir/source.html"
	curl $sourceUrl > $sourceFile

	ridingList="$dataDir/ridingIDsList.txt"
	grep '<li><a href="ElectoralDistricts.aspx?ed=' $sourceFile | cut -d '=' -f 3 | cut -d '&' -f 1 > $ridingList

	for identifier in `cat $ridingList`; do
		ridingUrl="http://enr.elections.ca/ElectoralDistricts.aspx?ed=$identifier&lang=$lang"
		ridingFile=$workDir/$identifier.html
		readFile=$readyDir/$identifier.html

		curl -s $ridingUrl > $ridingFile && mv -v $ridingFile $readyFile || true

		sleep 1 
	done	

done





### END SCIPT ##################################################################

END=$(date +%s.%N)
DIFF=$(echo "$END - $START" | bc)
echo Done.  `date` - $DIFF seconds

if [ -f ${pidfile} ]; then
    rm ${pidfile}
fi
