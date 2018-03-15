#!/bin/sh

_reschedule_home=${0%/*}
_php_home=`grep -i php_home ${_reschedule_home}/../config.ini | cut -d= -f2`

${_php_home}/php ${_reschedule_home}/reschedule.php
