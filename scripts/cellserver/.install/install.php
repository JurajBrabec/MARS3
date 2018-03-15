<?php
define( 'USAGE', 'Usage: ./install.sh <MARS DB Server FQDN>' );
define( 'NO_MYSQL', 'Error: MySQL not supported in PHP' );
define( 'NO_MYSQLI', 'Notice: MySQLi not supported in PHP, failing back to depreciated MySQL.' );
define( 'INSTALLING', ' Installing file "%s" : ' );
define( 'ERROR', 'Error' );
define( 'OK', 'OK' );
define( 'FATAL', 'Fatal: An error occurred during MARS installation.' );
define( 'DONE', 'No error occurred during MARS installation.' );

define( 'USERNAME', 'script' );
define( 'PASSWORD', 'omniback' );
define( 'DATABASE', 'mars30' );
define( 'PORT', '3306' );

define( 'CONFIG', 'config.ini');

if( $GLOBALS[ 'argc' ] != 2 ) die( USAGE . PHP_EOL );
if ( !function_exists( 'mysql_connect' ) ) die( NO_MYSQL . PHP_EOL );
$path = dirname( realpath( $GLOBALS[ 'argv' ][ 0 ] ) );
list( $host, $port ) = explode( ':', sprintf( '%s:%s', $GLOBALS[ 'argv' ][ 1 ], PORT ) );
if ( !class_exists( 'mysqli' ) ) {
	echo NO_MYSQLI . PHP_EOL;
	$db = new mysql( sprintf( '%s:%s', $host, $port ), USERNAME, PASSWORD );
	$db->select_db( DATABASE );
} else {
	$db = new mysqli( $host, USERNAME, PASSWORD, DATABASE, $port );
}
$result = 0;
$db->select_db( DATABASE );
$query = $db->query( "select * from config_scripts where valid_until is null;" );
while ( $row = $query->fetch_array( ) ) {
	$row = array_change_key_case( $row, CASE_UPPER );
	$file = $path . DIRECTORY_SEPARATOR . $row[ 'NAME' ];
	echo sprintf( INSTALLING , $file );
	if ( !is_dir( dirname( $file ) ) ) mkdir( dirname( $file ), TRUE ); 
	$code = str_replace( "\r\n", PHP_EOL, $row[ 'CODE' ] );
	if ( @file_put_contents( $file , $code ) ) {
		echo OK . PHP_EOL;
	} else {
		echo ERROR . PHP_EOL;
		$result = 1;
	}
}
if ( !$result ) {
	echo DONE . PHP_EOL;
	@copy( $path . DIRECTORY_SEPARATOR . CONFIG. '.default', $path . DIRECTORY_SEPARATOR . CONFIG );
} else {
	echo FATAL . PHP_EOL;
}
exit( $result );
