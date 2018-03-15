<?php

/*
 * MARS 3.0 COPYLISTS PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'COPYLISTS_COPYLIST_PATTERN', 
	"(?P<comment>^#.*?\n)|(?P<key>^\s*?[A-Z]+)|(?:{(?P<items>(?:(?>[^{}]+)|(?R))*?)})|(?P<option>-\w+)|(?P<value>\S+)" );
define( 'COPYLISTS_SCHEDULE_PATTERN', "(?P<option>\"?-\w+)|(?P<value>\S+)" );
define( 'COPYLISTS_SAMEASDL_STRING', 'Same as datalist' );
define( 'COPYLISTS_TYPE', serialize( array( 
	'OPTIONS' ) ) );
define( 'COPYLISTS_COPYLISTS', 'copylists/scheduled' );
define( 'COPYLISTS_SCHEDULES', 'copylists/scheduled/schedules' );
define( 'COPYLISTS_ERROR', 'Error in copy list "%s": %s' );
define( 'COPYLISTS_ERROR_NOT_EXISTS', 'Missing copy list for schedule file "%s".' );
define( 'COPYLISTS_ERROR_INVALID', 'Copy list "%s" is not valid.' );
define( 'COPYLISTS_ERROR_MISSING_SCHEDULE', 'Schedule file "%s" does not exist.' );
define( 'COPYLISTS_ERROR_READING', 'Error reading "%s": %s' );
define( 'COPYLISTS_ERROR_WRITE_COPYLIST', 'Cannot write to copy list file "%s".' );
define( 'COPYLISTS_ERROR_WRITE_SCHEDULE', 'Cannot write to schedule file "%s".' );
define( 'COPYLISTS_ERROR_WRITING', 'Error writing "%s": %s' );
define( 'COPYLISTS_LOG', 'COPYLISTS: %s inserted, %s updated, %s removed.' );

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
} else {
}

class mars_copylist {
	var $application;
	var $name;
	var $copylist_file;
	var $schedule_file;
	var $copylist_modified;
	var $schedule_modified;
	var $copylist;
	var $parsed_copylist;
	var $schedule;
	var $parsed_schedule;
	var $devices;
	var $datalists;
	var $protection;
	var $periodicity;
	var $nextexecution;

	function mars_copylist( $application, $name, $copylist = '', $schedule = '' ) {
		$this->application = $application;
		$this->name = $name;
		$this->copylist_file = '';
		$this->schedule_file = '';
		$this->copylist_modified = '';
		$this->schedule_modified = '';
		$this->copylist = $copylist;
		$this->schedule = $schedule;
		$this->parsed_copylist = '';
		$this->parsed_schedule = '';
		$this->devices = array();
		$this->datalists = array();
		$this->dl_group = '';
		$this->protection = COPYLISTS_SAMEASDL_STRING;
		$this->periodicity = -1;
		$this->nextexecution = array();
	}

	function read_file() {
		try {
			$this->copylist_file = $this->application->config[ 'OMNI_SERVER' ] . DIRECTORY_SEPARATOR 
				. COPYLISTS_COPYLISTS . DIRECTORY_SEPARATOR . $this->name;
			$this->copylist_file = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->copylist_file );
			$this->schedule_file = $this->application->config[ 'OMNI_SERVER' ] . DIRECTORY_SEPARATOR 
				. COPYLISTS_SCHEDULES . DIRECTORY_SEPARATOR . $this->name;
			$this->schedule_file = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->schedule_file );
			if ( !file_exists( $this->copylist_file ) ) {throw new exception( 
					sprintf( COPYLISTS_ERROR_NOT_EXISTS, $this->schedule_file ) );}
			$this->copylist_modified = date( $this->application->config[ 'TIME_FORMAT' ], filemtime( $this->copylist_file ) );
			$this->copylist = $this->application->read_file( $this->copylist_file );
			if ( !in_array( trim( substr( $this->copylist, 0, 8 ) ), unserialize( COPYLISTS_TYPE ) ) ) {throw new exception( 
				sprintf( COPYLISTS_ERROR_INVALID, $this->copylist_file ) );}
			$this->parse_copylist( );
			if ( file_exists( $this->schedule_file ) ) {
				$this->schedule_modified = date( $this->application->config[ 'TIME_FORMAT' ], filemtime( $this->schedule_file ) );
				$this->schedule = $this->application->read_file( $this->schedule_file );
				$this->parse_schedule( );
				$this->periodicity = -1;
				$this->nextexecution = $this->get_nextexecution( );
				if ( $this->nextexecution ) {
					$nextexecution = $this->get_nextexecution( sprintf( "%s +1 day", $this->nextexecution[ 0 ][ 'date' ] ) );
					$this->periodicity = 
						( strtotime( $nextexecution[ 0 ][ 'date' ] ) - strtotime( $this->nextexecution[ 0 ][ 'date' ] ) ) / ( 24 * 60 * 60 );
				}
			} else {
				throw new exception( sprintf( COPYLISTS_ERROR_MISSING_SCHEDULE, $this->schedule_file ) );
			}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( COPYLISTS_ERROR, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function write_file() {
		try {
			$this->copylist_file = $this->application->config[ 'OMNI_SERVER' ] 
				. DIRECTORY_SEPARATOR . COPYLISTS_COPYLISTS . DIRECTORY_SEPARATOR . $this->name;
			$this->copylist_file = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->copylist_file );
			$this->schedule_file = $this->application->config[ 'OMNI_SERVER' ] 
				. DIRECTORY_SEPARATOR . COPYLISTS_SCHEDULES . DIRECTORY_SEPARATOR . $this->name;
			$this->schedule_file = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->schedule_file );
			if ( !file_put_contents( $this->copylist_file, $this->copylist ) ) {throw new exception( 
					sprintf( COPYLISTS_ERROR_WRITE_COPYLIST, $this->copylist_file ) );}
			if ( !file_put_contents( $this->schedule_file, $this->schedule ) ) {throw new exception( 
					sprintf( COPYLIST_ERROR_WRITE_SCHEDULE, $this->schedule_file ) );}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( COPYLISTS_ERROR_WRITING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function read_database() {
		try {
			$this->copylist_file = '';
			$this->schedule_file = '';
			$this->copylist_modified = '';
			$this->schedule_modified = '';
			$sql = "select * from dataprotector_copylists where cellserver='%cellserver' and name='%name' limit 1;";
			$values = array( 
				'cellserver' => $this->application->cellserver, 
				'name' => $this->name );
			if ( $this->application->database->execute_query( $sql, $values ) == 0 ) {throw new exception( 
					sprintf( COPYLISTS_ERROR_NOT_IN_DB, $this->name ) );}
			$this->copylist = $this->application->database->rows[ 0 ][ 'copylist' ];
			$this->schedule = $this->application->database->rows[ 0 ][ 'schedule' ];
			$this->parse_copylist( );
			$this->periodicity = -1;
			$this->nextexecution = $this->get_nextexecution( );
			if ( $this->nextexecution ) {
				$nextexecution = $this->get_nextexecution( sprintf( "%s +1 day", $this->nextexecution[ 0 ][ 'date' ] ) );
				$this->periodicity = ( strtotime( $nextexecution[ 0 ][ 'date' ] ) - strtotime( $this->nextexecution[ 0 ][ 'date' ] ) ) /
					 ( 24 * 60 * 60 );
			}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( COPYLISTS_ERROR_READING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return true;
	}

	function write_database() {
		$result = false;
		try {
			$sql = "select name from config_customers where '%name' regexp specification and valid_until is null limit 1;";
			$values = array(
					'name' => $this->name );
			if ( $this->application->database->execute_query( $sql, $values ) == 1 ) {
				$customer = $this->application->database->rows[ 0 ][ 'name' ];
			} else {
				$customer = '';
			}
			$nextexecution = '';
			if ( $this->nextexecution ) {
				$nextexecution = date( $this->application->config[ 'TIME_FORMAT' ], 
					strtotime( sprintf( '%s %s', $this->nextexecution[ 0 ][ 'date' ], $this->nextexecution[ 0 ][ 'time' ] ) ) );
			}
			$sql = "insert into dataprotector_copylists " .
				 "(cellserver,name,customer,copylist_modified,copylist," .
				 "schedule_modified,schedule,devices,datalists,dl_group,nextexecution,periodicity,protection) " .
				 "values('%cellserver','%name',nullif('%customer',''),nullif('%copylist_modified',''),'%copylist'," .
				 "nullif('%schedule_modified',''),nullif('%schedule',''),'%devices','%datalists','%dl_group'," .
				 "nullif('%nextexecution',''),'%periodicity','%protection') " .
				 "on duplicate key update customer=nullif('%customer',''),copylist_modified=nullif('%copylist_modified','')," . 
				 "copylist='%copylist',schedule_modified=nullif('%schedule_modified',''),schedule=nullif('%schedule',''), " .
				 "devices='%devices',datalists='%datalists',dl_group='%dl_group',nextexecution=nullif('%nextexecution','')," .
				 "periodicity='%periodicity',protection='%protection',updated_on='%updated_on',valid_until=null;";
			$values = array( 
				'cellserver' => $this->application->cellserver, 
				'name' => $this->name, 
				'customer' => $customer,
				'copylist_modified' => $this->copylist_modified, 
				'copylist' => $this->application->database->escape_string( $this->copylist ), 
				'schedule_modified' => $this->schedule_modified, 
				'schedule' => $this->application->database->escape_string( $this->schedule ), 
				'devices' => implode( PHP_EOL, $this->devices ), 
				'datalists' => implode( PHP_EOL, $this->datalists ),
				'dl_group' => implode ( PHP_EOL, $this->dl_group ),
				'nextexecution' => $nextexecution, 
				'periodicity' => $this->periodicity, 
				'protection' => $this->protection, 
				'updated_on' => $this->application->start_time );
			$result = $this->application->database->execute_query( $sql, $values );
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( COPYLISTS_ERROR_WRITING, $this->name, $e->getmessage( ) ) );
			return false;
		}
		return $result;
	}

	function parse( $text ) {
		$result = array();
		$match = array();
		$tokens = preg_match_all( '/' . COPYLISTS_COPYLIST_PATTERN . '/m', $text, $match );
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
					'comment', 
					'key', 
					'option', 
					'value', 
					'items' ) as $j ) {
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
					( $option == 'protect' ) && $this->protection = $value;
					break;
				case 'items':
					if ( ( $key != '' ) and !is_array( $result[ $key ] ) ) {
						$result[ $key ] = ( $result[ $key ] == '' ? array() : array( 
							'name' => $result[ $key ] ) );
						$result[ $key ][ 'items' ] = $this->parse( $action2 );
					} else {
						$result[ 'items' ] = $this->parse( $action2 );
					}
					break;
			}
			$i++;
		}
		return $result;
	}

	function parse_copylist() {
		if ( is_array( $this->parsed_copylist ) ) return $this->parsed_copylist;
		$copylist = $this->parse( $this->copylist );
		
		$keys = array_keys( $copylist );
		foreach ( $keys as $key => $value ) {
			$keys[ $key ] = str_replace( '-S', '', $value );
		}
		$this->parsed_copylist = array_combine( $keys, $copylist );
		foreach ( $this->parsed_copylist[ 'FILTER' ] as $filter ) {
			isset( $filter[ 'name' ] ) && ( $filter[ 'name' ] == 'datalist' ) && $this->datalists = implode( $filter[ 'items' ] );
			isset( $filter[ 'name' ] ) && ( $filter[ 'name' ] == 'dl_group' ) && $this->dl_group = implode( $filter[ 'items' ] );
		}
		$this->datalists = str_replace( '" "', ',', $this->datalists );
		$this->datalists = str_replace( '"', '', $this->datalists );
		$this->datalists = explode( ',', $this->datalists );
		$this->dl_group = str_replace( '" "', ',', $this->dl_group );
		$this->dl_group = str_replace( '"', '', $this->dl_group );
		$this->dl_group = explode( ',', $this->dl_group );
		foreach ( $this->parsed_copylist[ 'DEVICE' ] as $device ) {
			isset( $device[ 'name' ] ) && $this->devices[ ] = str_replace( '"', '', $device[ 'name' ] );
		}
		$this->devices = array_unique( $this->devices );
		return $this->parsed_copylist;
	}

	function parse_schedule() {
		if ( is_array( $this->parsed_schedule ) ) return $this->parsed_schedule;
		$schedule = array();
		$match = array();
		$tokens = preg_match_all( '/' . COPYLISTS_SCHEDULE_PATTERN . '/m', $this->schedule, $match );
		$i = 0;
		$option = '';
		$value = '';
		$item = array();
		while ( $i < $tokens ) {
			foreach ( array( 
				'option', 
				'value' ) as $j ) {
				( $match[ $j ][ $i ] != '' ) && $action1 = $j;
			}
			$action2 = trim( $match[ $action1 ][ $i ] );
			switch ( $action1 ) {
				case 'option':
					$option = substr( $action2, 1 );
					$value = '';
					$item[ $option ] = $value;
					break;
				case 'value':
					$value = trim( $value . ' ' . $action2 );
					$item[ $option ] = $value;
					if ( $option == 'at' ) {
						$schedule[ ] = $item;
						$item = array();
					}
					break;
			}
			$i++;
		}
		$this->parsed_schedule = $schedule;
		return $this->parsed_schedule;
	}

	function is_scheduled( $date = 'today' ) {
		try {
			$result = false;
			$schedule = $this->parse_schedule( );
			$date = date( 'Y-m-d', strtotime( $date ) );
			list ( $yday, $weekday, $day, $month, $year ) = explode( ' ', date( 'z D j M Y', strtotime( $date ) ) );
			foreach ( $schedule as $item ) {
				if ( isset( $item[ 'disabled' ] ) ) {
					$result = false;
					break;
				}
				if ( isset( $item[ 'every' ] ) and isset( $item[ '4day' ] ) and in_array( $weekday, explode( ' ', $item[ '4day' ] ) ) ) {
					$firstday = date( 'z', strtotime( sprintf( '%s Jan %s', $item[ '4day' ], $year ) ) );
					if ( ( ( $yday - $firstday ) % 28 ) == 0 ) {
						list ( $mode, $value ) = each( $item );
						$result[ ] = array(
								'mode' => trim( $mode . ' ' . $value ),
								'date' => $date,
								'time' => $item[ 'at' ] );
					}
				}
				if ( isset( $item[ 'starting' ] ) and isset( $item[ 'every' ] ) and isset( $item[ 'day' ] ) and
						( isset( $item[ '2month' ] ) or isset( $item[ '3month' ] ) or isset( $item[ '4month' ] )
								or isset( $item[ '6month' ] ) or isset( $item[ '12month' ] ) ) ) {
									$emonth = 1;
									isset( $item[ '2month' ] )  && $emonth = 2;
									isset( $item[ '3month' ] )  && $emonth = 3;
									isset( $item[ '4month' ] ) && $emonth = 4;
									isset( $item[ '6month' ] )  && $emonth = 6;
									isset( $item[ '12month' ] ) && $emonth = 12;
									$months = array( );
									list( $sday, $smonth,$syear ) = explode( ' ', $item[ 'starting' ] );
									for ( $m = 0; $m < 12; $m++) {
										( $smonth + $m - 1 ) % $emonth == 0 &&
										$months[ ] = date('M', strtotime( sprintf( '+%d months', $m ), 0 ) );
									}
								} else {
									unset( $months );
								}
								if ( isset( $item[ 'every' ] ) and isset( $item[ 'day' ] ) and ( ( $item[ 'day' ] == '' ) or ( $item[ 'day' ] == $day )
										or in_array( $day, explode( ' ', $item[ 'day' ] ) ) or in_array( $weekday, explode( ' ', $item[ 'day' ] ) ) )
										and ( !isset( $item[ 'month' ] ) or ( $item[ 'month' ] == $month ) or( in_array( $month, explode( ' ', $item[ 'month' ] ) ) ) )
										and ( !isset( $months ) or in_array( $month,  $months ) ) ) {
											list ( $mode, $value ) = each( $item );
											$result[ ] = array(
													'mode' => trim( $mode . ' ' . $value ),
													'date' => $date,
													'time' => $item[ 'at' ] );
										}
										if ( isset( $item[ 'only' ] ) and $item[ 'only' ] == $year and $item[ 'day' ] == $day and $item[ 'month' ] == $month ) {
											list ( $mode, $value ) = each( $item );
											$result[ ] = array(
													'mode' => trim( $mode . ' ' . $value ),
													'date' => $date,
													'time' => $item[ 'at' ] );
										}
										if ( isset( $item[ 'exclude' ] ) and ( !isset( $item[ 'year' ] ) or $item[ 'year' ] == $year )
												and	( isset( $item[ 'month' ] ) and $item[ 'month' ] == $month )
												and ( isset( $item[ 'day' ] ) and $item[ 'day' ] == $day ) ) {
													$result = false;
												}
												if ( isset( $item[ 'exclude' ] ) and !isset( $item[ 'year' ] ) and !isset( $item[ 'month' ] ) and ( isset( $item[ 'day' ] )
														and ( in_array( $day, explode( ' ', $item[ 'day' ] ) ) ) or in_array( $weekday, explode( ' ', $item[ 'day' ] ) ) ) ) {
															$result = false;
														}
														if ( isset( $item[ 'exclude' ] ) and !isset( $item[ 'year' ] ) and !isset( $item[ 'month' ] )
																and isset( $item[ '4day' ] ) and in_array( $weekday, explode( ' ', $item[ '4day' ] ) ) ) {
																	$firstday = date( 'z', strtotime( sprintf( '%s Jan %s', $item[ '4day' ], $year ) ) );
																	if ( ( ( $yday - $firstday ) % 28 ) == 0 ) {
																		$result = false;
																	}
																}
			}
		}
	catch ( exception $e ) {
		$this->application->log_error( $e->getmessage( ) );
	}
		return $result;
	}

	function get_nextexecution( $date = 'today' ) {
		$i = 1;
		$result = $this->is_scheduled( $date );
		while ( $i < 365 and !$result ) {
			$result = $this->is_scheduled( sprintf( '%s +%d day', $date, $i ) );
			$i++;
		}
		return $result;
	}
}

function read_copylists( $application ) {
	$lines = array();
	$files = array_merge( glob( $application->config[ 'OMNI_SERVER' ] . DIRECTORY_SEPARATOR . COPYLISTS_COPYLISTS . DIRECTORY_SEPARATOR .'*' ), 
		glob( $application->config[ 'OMNI_SERVER' ] . DIRECTORY_SEPARATOR . COPYLISTS_SCHEDULES . DIRECTORY_SEPARATOR . '*' ) );
	foreach ( $files as $file ) {
		if ( is_file( $file ) ) $lines[ ] = basename( $file );
	}
	$i = $u = 0;
	foreach ( $lines as $line ) {
		$copylist = new mars_copylist( $application, $line );
		if ( $copylist->read_file( ) ) {
			$result = $copylist->write_database( );
			( $result == 1 ) && $i++;
			( $result == 2 ) && $u++;
		}
	}
	$sql = "update dataprotector_copylists set valid_until=updated_on where id in (select id from (" .
		 "select id from dataprotector_copylists where cellserver='%cellserver' " .
		 "and updated_on<'%updated_on' and valid_until is null) d order by id);";
	$values = array( 
		'cellserver' => $application->cellserver, 
		'updated_on' => $application->start_time );
	$d = $application->database->execute_query( $sql, $values );
	$message = sprintf( COPYLISTS_LOG, $i, $u, $d );
	$application->log_action( $message );
	return true;
}
