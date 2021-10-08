#!/usr/bin/env bash

START=$(date +%s.%N)

arg1=${1:-''}

if [[ $arg1 == '--help' || $arg1 == '-h' || -z "$arg1" ]]; then
    echo "You must now specify the minimum date & time counting starts (when polls close)"
    echo "This reduces the chances for the script to get results for ridings where polls haven't closed"
    echo
    echo "Usage: $0 {\$dateTime}"
    echo
    echo "The dateTime can be anything parsable by bash's date tool"
    echo
    exit 0
fi

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
log=~/log/$__base-${ts}.err.log
#exec >  >(tee -a $log)
#exec 2> >(tee -a $log >&2)
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
electionCountStart=$1


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

	for lang in e ; do
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
        echo > $ridingList;


		# For federal election, riding list was manually determined

        now=$(date +'%s')


        # Montain (Alberta, NWT)
        timezone='Canada/Mountain'
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ]; then
            echo "Including $timezone"

            # Alberta
		    (seq 1605 1639) >> $ridingList;

            # NWT
            echo 1641 >> $ridingList;
        fi


        # Pacific (British Columbia)
        timezone='Canada/Pacific'
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ]; then
            echo "Including $timezone"

            # British Columbia
            (seq 1674 1715) >> $ridingList;
        fi


        # Central (Manitoba , Western Ontario)
        timezone='Canada/Central'
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ]; then
            echo "Including $timezone"

            # Manitoba
            (seq 1592 1605) >> $ridingList;
        fi



        # Atlantic Canada (NB, NS, NFLD)
        timezone='Canada/Atlantic'
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ]; then
            echo "Including $timezone"

	        # New Brunswick
	        (seq 1582 1591) >> $ridingList;

	        # Nova Scotia
	        seq 1571 1581 >> $ridingList;

            # PEI
            seq 1567 1570 >> $ridingList ;

        fi


        # Newfoundland
        timezone='Canada/Newfoundland'
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge  "$startThisTz" ]; then
            echo "Including $timezone"
	        # NFLD
	        seq 1560 1566 >> $ridingList;
        fi


        # Eastern (Ontario, Quebec, Nunavut)
        timezone='Canada/Eastern'
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ]; then
            echo "Including $timezone"

            # Québec
            seq 2070 2147 >> $ridingList;

            # Ontario
            seq 2148 2268 >> $ridingList ;

            # Nunavut
            echo 1642 >> $ridingList;
        fi


        # Saskatchewan
        timezone=Canada/Saskatchewan
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ]; then
            echo "Including $timezone"
            # Saskatchewan
            seq 1660 1673 >> $ridingList;
        fi

        # Yukon
        timezone=Canada/Yukon
        startThisTz=$(TZ="$timezone" date --date "$electionCountStart" +'%s')

        if [ "$now" -ge "$startThisTz" ] ; then
            echo "Including $timezone"
            # Yukon
            echo 1640 >> $ridingList
        fi

        cat $ridingList | sort -n | uniq | sort -R > $ridingList.2
        mv -fv $ridingList.2 $ridingList

        echo
        wc -l $ridingList
        echo
        sleep 3

		for identifier in `cat $ridingList`; do
			ridingUrl="https://enr.elections.ca/ElectoralDistricts.aspx?ed=$identifier&lang=$lang"
			ridingFile=$workDir/$identifier.html
			readyFile=$readyDir/$identifier.html

			startCurl=$(date +%s.%N)

            set -x
			#curl -m $curlTimeout -s $ridingUrl > $ridingFile || true
            wget --connect-timeout $curlTimeout -U 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko/20100101 Firefox/66.0'  -O $ridingFile "$ridingUrl"
            set +x

            chmod o+r $ridingFile

			string='<th class=" td_no_mobile " scope="col">'
			grep "$string" $ridingFile > /dev/null && mv -v "$ridingFile" "$readyFile" || echo "Could not find $string in $ridingFile"
			endCurl=$(date +%s.%N)
			diffCurl=$(echo "$endCurl - $startCurl" | bc)
			waitCurl=$(echo "$diffCurl*2" | bc)

			echo; echo
			echo Data for riding $identifier in language $lang done!
			echo; echo

			echo `date`   Sleeping for $waitCurl + 4 seconds
			sleep $waitCurl
			sleep 4
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
