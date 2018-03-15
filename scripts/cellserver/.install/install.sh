#!/bin/sh

_php_home=/usr/bin

# MARS 3.0 INSTALL SCRIPT
# DON'T MODIFY ANYTHING BELOW THIS LINE -------------------------------------------------------------------------------
_db_server=$1

if [[ -z "${_db_server}" ]]
then
  echo Error: No DB server specified.
  echo Usage: install.sh DB_SERVER_FQDN.
  exit 1
fi

if [[ -e "${_php_home}/php" ]]
then
  echo Installing MARS files...
  ${_php_home}/php install.php ${_db_server}
  [[ -e mars.sh ]] && chmod 755 mars.sh
  echo Done.
else
  echo Error: PHP not found in path "${_php_home}/".
  exit 1
fi
