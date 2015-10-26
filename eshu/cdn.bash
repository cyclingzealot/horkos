#!/usr/bin/env bash

START=$(date +%s.%N)

#exit when command fails (use || true when a command can fail)
set -o errexit

#exit when your script tries to use undeclared variables
set -o nounset

#(a.k.a set -x) to trace what gets executed
#set -o xtrace

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
   result=`ps -ef | grep ${oldpid} | grep ${__base}  || true`

   if [ -n "${result}" ]; then
     echo "Script already running! Check $pidfile. Exiting"
     exit 255
   fi
fi

#grab pid of this process and update the pid file with it
pid=`ps -ef | grep ${__base} | grep -v 'vi ' |  head -n1 |  awk ' {print $2;} '`
echo ${pid} > ${pidfile}


#Capture everything to log
mkdir -p ~/log
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
curlTimeout=6
restSecs=60
continueFlag=/tmp/$__base.continue
stopFlag=/tmp/$__base.stop
maxRuns=100

touch $continueFlag

if [[ -f $stopFlag ]]; then
	echo Stop Flag active.  To remove
	echo rm $stopFlag
	exit 1
fi

runs=0
set -x
while [[ -f $continueFlag && $runs -lt $maxRuns && ! -f $stopFlag  ]]; do 
set +x
	echo Run $runs of $maxRuns...

	for lang in e f; do 
		dataDir="$__dir/data/$electionID/$lang/"
		workDir="$dataDir/work/"
		readyDir="$dataDir/ready"
	
		mkdir -p $workDir $readyDir
	
		#This was for the byelection when the riding list was in simple HTML
		#sourceUrl="http://enr.elections.ca/ElectoralDistricts.aspx?lang=$lang"
		#sourceFile="$dataDir/source.html"
		#
		#echo; echo Getting riding list...
		#curl  $sourceUrl > $sourceFile
		#echo; echo Done. ; echo
	
		ridingList="$dataDir/ridingIDsList.txt"
		#grep '<li><a href="ElectoralDistricts.aspx?ed=' $sourceFile | cut -d '=' -f 3 | cut -d '&' -f 1 > $ridingList

		# For federal election, riding list was manually determined
		# Alberta       # British Columbia # Manitoba # New Brunsw   # NFLD          # NWT       #Nova Scotia    # Nunavut  # Ontario      # PEI	   # Quebec       # Saskatchewan  # Yukon
		(seq 1605 1639; seq 1674 1715; seq 1592 1605; seq 1582 1591; seq 1560 1566 ; echo 1641 ; seq 1571 1581 ; echo 1642; seq 2148 2268; seq 1567 1570 ; seq 2070 2147; seq 1660 1673 ; echo 1640 ) | sort > $ridingList
	
		for identifier in `cat $ridingList`; do
			ridingUrl="http://enr.elections.ca/ElectoralDistricts.aspx?ed=$identifier&lang=$lang"
			ridingFile=$workDir/$identifier.html
			readyFile=$readyDir/$identifier.html
	
			startCurl=$(date +%s.%N)
			curl -m $curlTimeout -s $ridingUrl > $ridingFile || true
			string='<th class=" td_no_mobile " scope="col">'
			grep "$string" $ridingFile > /dev/null && mv -v "$ridingFile" "$readyFile" || echo "Could not find $string in $ridingFile"
			endCurl=$(date +%s.%N)
			diffCurl=$(echo "$endCurl - $startCurl" | bc)

			echo; echo 
			echo Data for riding $identifier in language $lang done!
			echo; echo 
	
			echo `date`   Sleeping for $diffCurl + 1 seconds
			sleep $diffCurl
			sleep 1
		done	
	
	done
	
	date 
	echo Resting for $restSecs seconds....
	echo
	echo To interupt until next cron:
	echo rm $continueFlag
	echo
	echo To stop permantly
	echo touch $stopFlag
	echo
	for i in `seq $restSecs -1 0`; do 
		printf "$i... " ; 
		if (( $i % 5 == 0 )) ; then echo ; fi
		sleep 1; 
	done
	echo ; echo

	echo Run $runs of $maxRuns done

	let runs++ || true


done


echo; echo; echo; echo; 



### END SCIPT ##################################################################

END=$(date +%s.%N)
DIFF=$(echo "$END - $START" | bc)
echo Done.  `date` - $DIFF seconds

if [ -f ${pidfile} ]; then
    rm ${pidfile}
fi
