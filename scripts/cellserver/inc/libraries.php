<?php

/*
 * MARS 3.0 LIBRARIES PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'LIBRARIES_OMNIDOWNLOAD_TIMEOUT', 30 );
define( 'LIBRARIES_ERROR_INVALID', 'Library "%s" is invalid.' );
define( 'LIBRARIES_ERROR_READING', 'Error reading "%s": %s' );
define( 'LIBRARIES_ERROR_WRITING', 'Error writing "%s": %s' );
define( 'LIBRARIES_ERROR_NOT_IN_DB', 'Library "%s" not found in database.' );
define( 'LIBRARIES_PATTERN', "^-?(?P<key>\s*?\w+)|(?:{(?P<items>(?:(?>[^{}]+)|(?R))*?)})|(?P<option>-\w+)|(?P<value>\S+)" );
define( 'LIBRARIES_LOG', 'LIBRARIES: %s inserted, %s updated, %s removed.' );

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	define( 'LIBRARIES_OMNIDOWNLOAD_LIST_LIBRARIES_CMD', '"%s\omnidownload.exe" -list_libraries -detail' );
	define( 'LIBRARIES_OMNIDOWNLOAD_LIBRARY_CMD', '"%s\omnidownload.exe" -library "%s"' );
	define( 'LIBRARIES_OMNIUPLOAD_LIBRARY_CMD', '"%s\omniupload.exe" -modify_library "%s"' );
} else {
	define( 'LIBRARIES_OMNIDOWNLOAD_LIST_LIBRARIES_CMD', '"%s/omnidownload" -list_libraries -detail' );
	define( 'LIBRARIES_OMNIDOWNLOAD_LIBRARY_CMD', '"%s/omnidownload" -library "%s"' );
	define( 'LIBRARIES_OMNIUPLOAD_LIBRARY_CMD', '"%s/omniupload" -modify_library "%s"' );
}

class mars_library {
	var $application;
	var $name;
	var $definition;
	var $parsed_definition;

	function mars_library( $application, $name, $definition = '' ) {
		$this->application = $application;
		$this->name = $name;
		$this->definition = $definition;
		$this->parsed_definition = '';
	}

	function omnidownload() {
		try {
			$lines = $this->application->cache_command( 
				sprintf( LIBRARIES_OMNIDOWNLOAD_LIBRARY_CMD, $this->application->config[ 'OMNI_BIN' ], $this->name ), 
				LIBRARIES_OMNIDOWNLOAD_TIMEOUT );
			$this->definition = implode( PHP_EOL, $lines );
			$this->parse( );
			if ( !isset( $this->parsed_definition[ 'NAME' ] ) ) {throw new exception( sprintf( LIBRARIES_ERROR_INVALID, $this->name ) );}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( LIBRARIES_ERROR_READING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function omniupload() {
		try {
			$lines = $this->application->cache_command( 
				sprintf( LIBRARIES_OMNIUPLOAD_LIBRARY_CMD, $this->application->config[ 'OMNI_BIN' ], $this->name ), 
				LIBRARIES_OMNIDOWNLOAD_TIMEOUT );
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( LIBRARIES_ERROR_WRITING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function read_database() {
		try {
			$this->definition = '';
			$sql = "select definition from dataprotector_libraries where cellserver='%cellserver' and name='%name' limit 1;";
			$values = array( 
				'cellserver' => $this->application->cellserver, 
				'name' => $this->name );
			if ( $this->application->database->execute_query( $sql, $values ) == 0 ) {throw new exception( 
					sprintf( LIBRARIES_ERROR_NOT_IN_DB, $this->name ) );}
			$this->definition = $this->application->database->rows[ 0 ][ 'definition' ];
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( LIBRARIES_ERROR_READING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function write_database() {
		$result = false;
		try {
			$library = $this->parse( );
			$sql = "insert into dataprotector_libraries (name,definition,description,host,ioctlserial,control,mgmtconsoleurl,updated_by,updated_on) " .
				 "values ('%name','%definition','%description','%host','%ioctlserial','%control','%mgmtconsoleurl','%updated_by','%updated_on') " .
				 "on duplicate key update definition='%definition',description='%description',host='%host'," .
				 "ioctlserial='%ioctlserial',control='%control',mgmtconsoleurl='%mgmtconsoleurl',updated_on='%updated_on',valid_until=null;";
			$values = array( 
				'name' => $this->name, 
				'definition' => $this->application->database->escape_string( $this->definition ), 
				'description' => $this->application->database->escape_string( $library[ 'DESCRIPTION' ] ), 
				'host' => $library[ 'HOST' ], 
				'ioctlserial' => str_replace( '"', '', $library[ 'IOCTLSERIAL' ] ), 
				'control' => str_replace( '"', '', $library[ 'CONTROL' ] ), 
				'mgmtconsoleurl' => str_replace( '"', '', $library[ 'MGMTCONSOLEURL' ] ), 
				'updated_by' => $this->application->cellserver, 
				'updated_on' => $this->application->start_time );
			$result = $this->application->database->execute_query( $sql, $values );
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( LIBRARIES_ERROR_WRITING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return $result;
	}

	function parse() {
		if ( is_array( $this->parsed_definition ) ) return $this->parsed_definition;
		$result = array();
		$match = array();
		$tokens = preg_match_all( '/' . LIBRARIES_PATTERN . '/m', $this->definition, $match );
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
		$this->parsed_definition = $result;
		return $this->parsed_definition;
	}
}

function read_libraries( $application, $action = true ) {
	if ( $action ) {
		$name = 'OMNIDOWNLOAD-LIBRARIES';
		$command = sprintf( LIBRARIES_OMNIDOWNLOAD_LIST_LIBRARIES_CMD, $application->config[ 'OMNI_BIN' ] );
		$file = $application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		$items = array( 'NAME' => 'ROUTINE', 'DATA' => 'LIBRARIES' );
		if ( !file_exists( $file ) ) {
			$application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
			return false;
		}
		$worker = $application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
	} else {
		$lines = $application->cache_command( sprintf( LIBRARIES_OMNIDOWNLOAD_LIST_LIBRARIES_CMD, $application->config[ 'OMNI_BIN' ] ), 
			LIBRARIES_OMNIDOWNLOAD_TIMEOUT );
	}
	$application->database->start_transaction();
	$i = $u = 0;
	$name = '';
	foreach ( $lines as $line ) {
		if ( preg_match( '/^NAME/', $line ) ) {
			list ( $name ) = sscanf( $line, 'NAME %s' );
			$name = trim( str_replace( '"', '', $name ) );
			$definition = array();
		}
		if ( preg_match( '/^=/', $line ) ) {
			try {
				$library = new mars_library( $application, $name, implode( PHP_EOL, $definition ) );
				if ( $library->parse( ) ) {
					$result = $library->write_database( );
					( $result == 1 ) && $i++;
					( $result == 2 ) && $u++;
				}
			}
			catch ( exception $e ) {
				$this->application->log_error( sprintf( LIBRARIES_ERROR_READING, $this->name, $e->getmessage( ) ) );
			}
			$name = '';
			continue;
		}
		!empty( $name ) && $definition[ ] = $line;
	}
	$sql = "update dataprotector_libraries set valid_until=updated_on where id in (select id from (" .
		 "select id from dataprotector_libraries where updated_by='%updated_by' and updated_on<'%updated_on' " .
		 "and valid_until is null) d order by id);";
	$values = array( 
		'updated_by' => $application->cellserver, 
		'updated_on' => $application->start_time );
	$d = $application->database->execute_query( $sql, $values );
	$application->database->commit();
	$application->log_action( sprintf( LIBRARIES_LOG, $i, $u, $d ) );
	return true;
}
