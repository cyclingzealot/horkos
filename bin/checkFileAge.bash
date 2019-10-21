#!/usr/bin/env bash

START=$(date +%s.%N)

#exit when command fails (use || true when a command can fail)
#set -o errexit

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
configFile="$HOME/.horkos/config"

#Ensure only one copy is running
#pidfile=$HOME/.${__base}.pid
#if [ -f ${pidfile} ]; then
#   #verify if the process is actually still running under this pid
#   oldpid=`cat ${pidfile}`
#   result=`ps -ef | grep ${oldpid} | grep ${__base} || true`  
#
#   if [ -n "${result}" ]; then
#     echo "Script already running! Exiting"
#     exit 255
#   fi
#fi
#
##grab pid of this process and update the pid file with it
#pid=`ps -ef | grep ${__base} | grep -v 'vi ' | head -n1 |  awk ' {print $2;} '`
#echo ${pid} > ${pidfile}


#Capture everything to log
log=~/log/$__base-${ts}.log
exec >  >(tee -a $log)
exec 2> >(tee -a $log >&2)
touch $log
chmod 600 $log


#Check that the config file exists
if [[ ! -f "$configFile" ]] ; then
        echo "I need a file at $configFile with the recipient of notifications, one line, no carriage return"
        exit 1
fi


echo Begin `date`  .....

### BEGIN SCRIPT ###############################################################

error=1
thresholdMinutes=30
notifyEmail=`cat $configFile`
fileList=/tmp/eshuOldFiles.txt
emailFlag=/tmp/${__base}.alertflag
alertThreshold=900 # 15 minutes
scanTarget=$__dir/../eshu/data/

set -x
find $scanTarget -mmin +$thresholdMinutes -type f > $fileList
set +x
findExitCode=$?
count=`wc -l $fileList | cut -d ' ' -f 1 `

if [[ ! -z "$count" && $findExitCode -eq "0" && "$count" -eq "0" ]]; then
	error=0
fi

if [[ "$error" -eq "1" ]]; then 
	msgFile=/tmp/eshuAlertMessage.txt

	echo "There are files older than $thresholdMinutes in horkos/eshu/data on `hostname` or I was not able to get a count:" > $msgFile
	cat $fileList >> $msgFile
	~/bin/age.bash $scanTarget >> $msgFile
	echo  >> $msgFile
	echo "Find exit code was $findExitCode" >> $msgFile
	echo "Running on `hostname`" >> $msgFile

	if [[ ! -f  $emailFlag || `expr $(date +%s) - $(date +%s -r $emailFlag)` -gt "$alertThreshold" ]]; then
		cat $msgFile | mail -s "ALERT: horkos: Old data - not refreshing?" $notifyEmail

		touch $emailFlag
	else 
		echo "We got an error, but the email was already sent.  Threshold is $alertThreshold"
		cat $msgFile
	fi
	rm $msgFile
else 
	echo "Find on $scanTarget ran OK.  No errors.  No old files above $thresholdMinutes"
	~/bin/age.bash $scanTarget 
fi

rm $fileList


### END SCIPT ##################################################################

END=$(date +%s.%N)
DIFF=$(echo "$END - $START" | bc)
echo Done.  `date` - $DIFF seconds

#if [ -f ${pidfile} ]; then
#    rm ${pidfile}
#fi
