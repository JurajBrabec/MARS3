<?php

/*
 * MARS 3.0 DEVICES PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'DEVICES_OMNIDOWNLOAD_TIMEOUT', 30 );
define( 'DEVICES_OMNIUPLOAD_TIMEOUT', 15 );
define( 'DEVICES_ERROR_READING', 'Error reading "%s": %s' );
define( 'DEVICES_ERROR_WRITING', 'Error writing "%s": %s' );
define( 'DEVICES_ERROR_NOT_IN_DB', 'Device "%s" not found in database.' );
define( 'DEVICES_ERROR_INVALID', 'Device "%s" is invalid.' );
define( 'DEVICES_PATTERN', "^-?(?P<key>\s*?\w+)|(?:{(?P<items>(?:(?>[^{}]+)|(?R))*?)})|(?P<option>-\w+)|(?P<value>\S+)" );
define( 'DEVICES_LOG', 'DEVICES: %s inserted, %s updated, %s removed.' );

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	define( 'DEVICES_OMNIDOWNLOAD_LIST_DEVICES_CMD', '"%s\omnidownload.exe" -list_devices -detail' );
	define( 'DEVICES_OMNIDOWNLOAD_DEVICE_CMD', '"%s\omnidownload.exe" -device "%s"' );
	define( 'DEVICES_OMNIUPLOAD_DEVICE_CMD', '"%s\omniupload.exe" -modify_device "%s"' );
} else {
	define( 'DEVICES_OMNIDOWNLOAD_LIST_DEVICES_CMD', '"%s/omnidownload" -list_devices -detail' );
	define( 'DEVICES_OMNIDOWNLOAD_DEVICE_CMD', '"%s/omnidownload" -device "%s"' );
	define( 'DEVICES_OMNIUPLOAD_DEVICE_CMD', '"%s/omniupload" -modify_device "%s"' );
}

class mars_device {
	var $application;
	var $name;
	var $definition;
	var $parsed_definition;

	function mars_device( $application, $name, $definition = '' ) {
		$this->application = $application;
		$this->name = $name;
		$this->definition = $definition;
		$this->parsed_definition = '';
	}

	function omnidownload() {
		try {
			$lines = $this->application->cache_command( 
				sprintf( DEVICES_OMNIDOWNLOAD_DEVICE_CMD, $this->application->config[ 'OMNI_BIN' ], $this->name ), 
				DEVICES_OMNIDOWNLOAD_TIMEOUT );
			$this->definition = implode( PHP_EOL, $lines );
			$this->parse( );
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( DEVICES_ERROR_READING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function omniupload() {
		try {
			$lines = $this->application->cache_command( 
				sprintf( DEVICES_OMNIUPLOAD_DEVICE_CMD, $this->application->config[ 'OMNI_BIN' ], $this->name ), DEVICES_OMNIUPLOAD_TIMEOUT );
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( DEVICES_ERROR_WRITING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function read_database() {
		try {
			$this->definition = '';
			$sql = "select definition from dataprotector_devices where cellserver='%cellserver' and name='%name' limit 1;";
			$values = array( 
				'cellserver' => $this->application->cellserver, 
				'name' => $this->name );
			if ( $this->application->database->execute_query( $sql, $values ) == 0 ) {throw new exception( 
					sprintf( DEVICES_ERROR_NOT_IN_DB, $this->name ) );}
			$this->definition = $this->application->database->rows[ 0 ][ 'definition' ];
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( DEVICES_ERROR_READING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function write_database() {
		$result = false;
		try {
			$device = $this->parse( );
			$sql = "insert into dataprotector_devices (name,definition,disable,description,host,pool," .
				 "library,blksize,lockname,devserial,updated_by,updated_on) " .
				 "values('%name','%definition',nullif('%disable',''),nullif('%description',''),'%host',nullif('%pool','')," .
				 "'%library',nullif('%blksize',''),'%lockname','%devserial','%updated_by','%updated_on') on duplicate key " .
				 "update definition='%definition',disable=nullif('%disable',''),description=nullif('%description',''),host='%host'," .
				 "pool=nullif('%pool',''),library='%library',blksize=nullif('%blksize','')," .
				 "lockname='%lockname',devserial='%devserial',updated_on='%updated_on',valid_until=null;";
			$values = array( 
				'name' => substr( $this->name, 0, 32 ), 
				'definition' => $this->application->database->escape_string( $this->definition ), 
				'disable' => isset( $device[ 'DISABLE' ] ) ? 'Y' : '', 
				'description' => $this->application->database->escape_string( $device[ 'DESCRIPTION' ] ), 
				'host' => $device[ 'HOST' ], 
				'pool' => str_replace( '"', "", $device[ 'POOL' ] ), 
				'library' => str_replace( '"', "", $device[ 'LIBRARY' ] ), 
				'blksize' => isset( $device[ 'BLKSIZE' ] ) ? $device[ 'BLKSIZE' ] : '', 
				'lockname' => isset( $device[ 'LOCKNAME' ] ) ? str_replace( '"', "", $device[ 'LOCKNAME' ] ) : '', 
				'devserial' => str_replace( '"', "", $device[ 'DEVSERIAL' ] ), 
				'updated_by' => $this->application->cellserver, 
				'updated_on' => $this->application->start_time );
			$result = $this->application->database->execute_query( $sql, $values );
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( DEVICES_ERROR_WRITING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return $result;
	}

	function parse() {
		if ( is_array( $this->parsed_definition ) ) return $this->parsed_definition;
		$result = array();
		$match = array();
		$tokens = preg_match_all( '/' . DEVICES_PATTERN . '/m', $this->definition, $match );
		$i = 0;
		$key = '';
		$option = '';
		$value = '';
		while ( $i <= $tokens ) {
			if ( $i == $tokens ) {
				$action1 = 'key';
				$action2 = '#';
			} else {
				foreach ( array( 
					'key', 
					'option', 
					'value' ) as $j ) {
					( $match[ $j ][ $i ] != '' ) && $action1 = $j;
				}
				$action2 = trim( $match[ $action1 ][ $i ] );
			}
			switch ( $action1 ) {
				case 'key':
					if ( ( $key == $action2 ) or ( isset( $result[ $key . '-S' ] ) ) ) {
						$result[ $key . '-S' ][ ] = $result[ $key ];
						unset( $result[ $key ] );
					}
					$key = strtoupper( $action2 );
					$option = '';
					$value = '';
					$key != '#' && $result[ $key ] = $value;
					break;
				case 'option':
					if ( $option == '' ) {
						if ( $key != '' and !is_array( $result[ $key ] ) ) {
							$result[ $key ] = ( $result[ $key ] == '' ? array() : array( 
								'name' => $result[ $key ] ) );
						}
					}
					$option = substr( $action2, 1 );
					$value = '';
					if ( $key == '' ) {
						$result[ $option ] = $value;
					} else {
						$result[ $key ][ $option ] = $value;
					}
					break;
				case 'value':
					$value = trim( $value . ' ' . $action2 );
					if ( $option == '' ) {
						$result[ $key ] = $value;
					} else {
						if ( $key == '' ) {
							$result[ $option ] = $value;
						} else {
							$result[ $key ][ $option ] = $value;
						}
					}
					break;
			}
			$i++;
		}
		if ( !isset( $result[ 'NAME' ] ) ) {throw new exception( sprintf( DEVICES_ERROR_INVALID, $this->name ) );}
		$this->parsed_definition = $result;
		return $this->parsed_definition;
	}
}

function read_devices( $application, $action = true ) {
	if ( $action ) {
		$name = 'OMNIDOWNLOAD-DEVICES';
		$command = sprintf( DEVICES_OMNIDOWNLOAD_LIST_DEVICES_CMD, $application->config[ 'OMNI_BIN' ] );
		$file = $application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		$items = array( 'NAME' => 'ROUTINE', 'DATA' => 'DEVICES' );
		if ( !file_exists( $file ) ) {
			$application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
			return false;
		}
		$worker = $application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
	} else {
		$lines = $application->cache_command( sprintf( DEVICES_OMNIDOWNLOAD_LIST_DEVICES_CMD, $application->config[ 'OMNI_BIN' ] ), 
			DEVICES_OMNIDOWNLOAD_TIMEOUT );
	}
	$i = $u = 0;
	array_push( $lines, '' );
	$name = '';
	$application->database->start_transaction();
	foreach ( $lines as $line ) {
		if ( preg_match( '/^NAME/', $line ) ) {
			list ( $name ) = sscanf( $line, 'NAME %[^$]s' );
			$name = trim( str_replace( '"', '', $name ) );
			$definition = array();
		}
		if ( preg_match( '/^=/', $line ) ) {
			try {
				$device = new mars_device( $application, $name, implode( PHP_EOL, $definition ) );
				if ( $device->parse( ) ) {
					$result = $device->write_database( );
					( $result == 1 ) && $i++;
					( $result == 2 ) && $u++;
				}
			}
			catch ( exception $e ) {
				$this->application->log_error( sprintf( DEVICES_ERROR_READING, $this->name, $e->getmessage( ) ) );
			}
			$name = '';
			continue;
		}
		!empty( $name ) && $definition[ ] = $line;
	}
	$sql = "update dataprotector_devices set valid_until=updated_on " .
		 "where id in (select id from (select id from dataprotector_devices where updated_by='%updated_by' " .
		 "and updated_on<'%updated_on' and valid_until is null) d order by id);";
	$values = array( 
		'updated_by' => $application->cellserver, 
		'updated_on' => $application->start_time );
	$d = $application->database->execute_query( $sql, $values );
	$application->database->commit();
	$application->log_action( sprintf( DEVICES_LOG, $i, $u, $d ) );
	return true;
}
