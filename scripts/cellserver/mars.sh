#!/bin/sh

# MARS 3.0 MAIN SCRIPT
# DON'T MODIFY ANYTHING BELOW THIS LINE -------------------------------------------------------------------------------

usage() {
    echo ""
    echo "MARS 3.0 usage: mars.sh [ -r routine_name | -n | -s | -t ]"
    echo "-r  manual routine start (requires routine name)"
    echo "    Routine names:"
    echo "      libraries"
    echo "      devices"
    echo "      media"
    echo "      specifications"
    echo "      check_bakcups"
    echo "      locked_objects"
    echo "      omnistat"
    echo "-n  triggered from DP Notification"
    echo "-s  used in crontab/scheduler"
    echo "-t  manual installation test"
    echo ""
    exit 1
}

execute() {
	os=`uname`
	if [[ "$os" = "HP-UX" ]]; then
			now=`perl -e 'use POSIX;print strftime "%H:%M:%S",localtime time-(4*60);'`
	else
			now=`date -d'4 min ago' +"%H:%M:%S"`
	fi
	for pid in `ps -ef | grep omnistat | grep -v sh | grep -v grep | awk '{print $2}'`; do
			timestamp=`ps -ef | grep $pid | grep -v sh | grep -v grep | awk '{print $5}'`
			if [[ "$now" > "$timestamp" ]]; then
					kill $pid
			fi
	done
	${_php_home}/php ${_mars_home}/mars.php $1 $2
	[[ -f "${_mars_home}/mars.lock" ]]  || rm -f ${_mars_home}/tmp/*.tmp
}

_timestamp=$(date +"%Y-%m-%d_%H-%M-%S")
_mars_home=${0%/*}
_php_home=`grep ^PHP_HOME ${_mars_home}/config.ini | cut -d= -f2`

[[ -d "${_mars_home}/log" ]]  || mkdir "${_mars_home}/log"
[[ -d "${_mars_home}/queue" ]]  || mkdir "${_mars_home}/queue"
[[ -d "${_mars_home}/tmp" ]]  || mkdir "${_mars_home}/tmp"

_actionlog=${_mars_home}/log/actions.log
_errorlog=${_mars_home}/log/errors.log

case "$1" in
    "")
        usage
        ;;
    -n)
        echo "Notification was queued."
        execute NOTIFICATION
        ;;
    -r)
        echo "Routine '$2' was executed."
        execute ROUTINE $2
        ;;
    -s)
        echo "Scheduler was executed."
        execute SCHEDULER
        ;;
    -t)
        echo "Test was executed."
        execute TEST
        ;;
    *)
        echo "Error: Unknown/incomplete parameter '$1' provided."
        echo "${_timestamp} Unknown/incomplete parameter '$1' provided." >> ${_errorlog}
        usage
        ;;
esac
