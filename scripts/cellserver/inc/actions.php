<?php

/*
 * MARS 3.0 ACTIONS PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'ACTIONS', 'UPDATESCRIPT,REMOVEFILE,ADDCONFIG,MODIFYCONFIG,REMOVECONFIG,ROUTINE,STARTOFCOPY,STARTOFSESSION,ENDOFCOPY,' .
		'ENDOFSESSION,ENDOFSESSIONDEVICES,ENDOFSESSIONOBJECTS,ENDOFSESSIONMEDIA' );
define( 'ACTIONS_ERROR', 'Error: %s "%s": %s' );
define( 'ACTIONS_ERROR_UNHANDLED', 'Unhandled action "%s".' );
define( 'ACTIONS_ERROR_NO_SCRIPT', 'No script matched pattern "%s".' );
define( 'ACTIONS_ERROR_CANNOT_WRITE', 'Cannot write to file "%s".' );
define( 'ACTIONS_SCRIPT_UPDATED', 'Script "%s" was updated successful.' );
define( 'ACTIONS_ERROR_EMPTY_FILENAME', 'Empty file name provided.' );
define( 'ACTIONS_FILE_REMOVED', 'File "%s" was removed successfuly.' );
define( 'ACTIONS_FILE_NOT_REMOVED', 'File "%s" was not removed.' );
define( 'ACTIONS_FILE_NOT_EXISTS', 'File "%s" does not exist.' );
define( 'ACTIONS_ERROR_EMPTY_KEY', 'Empty key provided.' );
define( 'ACTIONS_KEY_NOT_EXISTS', 'Config key "%s" does not exist.' );
define( 'ACTIONS_KEY_ADDED', 'Config key "%s" was set to "%s" and added after key "%s".' );
define( 'ACTIONS_KEY_MODIFIED', 'Config key "%s" was modified from "%s" to "%s".' );
define( 'ACTIONS_KEY_REMOVED', 'Config key "%s" was removed.' );
define( 'ACTIONS_ERROR_EMPTY_ROUTINE', 'Empty routine name provided.' );
define( 'ACTIONS_ERROR_EMPTY_SESSIONID', 'Empty Session ID provided.' );
define( 'ACTIONS_OMNIDB_TIMEOUT', 15 );
define( 'ACTIONS_ERROR_OMNIDB_UNKNOWN_ITEM', 'Unknown "omnidb -rpt %s" item "%s=%s".' );
define( 'ACTIONS_ERROR_OMNIDB_INVALID_OUTPUT', 'Invalid "omnidb rpt" output: "%s"' );
define( 'ACTIONS_SESSION_STARTED', 'Session "%s" started at %s. Specification:"%s" Type:%s Scheduled:%s' );
define( 'ACTIONS_SCHEDULED_SESSION_DELAY_SECONDS', 450 );
define( 'ACTIONS_OMNIRPT_TIMEOUT', 15 );
define( 'ACTIONS_ERROR_OMNIRPT_INVALID_SINGLE_SESSION_OUTPUT', 'Invalid "omnirpt single_session" output: "%s"' );
define( 'ACTIONS_ERROR_OMNIRPT_INVALID_SESSION_DEVICES_OUTPUT', 'Invalid "omnirpt session_devices" output: "%s"' );
define( 'ACTIONS_ERROR_OMNIRPT_DEVICES', 'Error retrieving devices for session "%s: (%s) : %s' );
define( 'ACTIONS_ERROR_OMNIRPT_INVALID_SESSION_OBJECTS_OUTPUT', 'Invalid "omnirpt session_objects" output: "%s"' );
define( 'ACTIONS_ERROR_OMNIRPT_OBJECTS', 'Error retrieving objects for session "%s: (%s) : %s' );
define( 'ACTIONS_ERROR_OMNIRPT_INVALID_SESSION_MEDIA_OUTPUT', 'Invalid "omnirpt session_media" output: "%s"' );
define( 'ACTIONS_ERROR_OMNIRPT_MEDIA', 'Error retrieving media for session "%s: (%s) : %s' );
define( 'ACTIONS_SESSION_RECYCLED', 'Session "%s" (%s) was recycled.' );
define( 'ACTIONS_ERROR_SESSION_RECYCLED', 'Error recycling session "%s" (%s) : %s' );
define( 'ACTIONS_ITO_THRESHOLD_SECONDS', 3600 );
define( 'ACTIONS_ITO_MESSAGE', '%s for %d. time' );
define( 'ACTIONS_ERROR_BACKUPMON', 'Error executing "checkBackupStatus" for session "%s" (%s) : %s' );
define( 'ACTIONS_STARTOFSESSION_SKIPPED', 'Processing of session "%s" start skipped.' );
define( 'ACTIONS_SESSION_LOG', 
	'Session "%-13s" ended at %-5s. Status:%-10s Objects:%-3s Media:%-2s ITO:%-1s BACKUPMON:%-3s Scheduled:%s"' );

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	define( 'ACTIONS_OMNIDB_CMD', '"%s\omnidb.exe" -rpt %s -detail' );
	define( 'ACTIONS_OMNIRPT_SINGLE_SESSION_CMD', '"%s\omnirpt.exe" -report single_session -session %s -level critical -tab' );
	define( 'ACTIONS_OMNIRPT_SESSION_DEVICES_CMD', '"%s\omnirpt.exe" -report session_devices -session %s -tab' );
	define( 'ACTIONS_OMNIRPT_SESSION_OBJECTS_CMD', '"%s\omnirpt.exe" -report session_objects -session %s -tab' );
	define( 'ACTIONS_OMNIRPT_SESSION_MEDIA_CMD', '"%s\omnirpt.exe" -report session_media -session %s -tab' );
	define( 'ACTIONS_OMNIDB_CHANGE_PROTECTION_CMD', '"%s\omnidb.exe" -session %s -change_protection none' );
	define( 'ACTIONS_OMNIDB_CHANGE_CATPROTECTION_CMD', '"%s\omnidb.exe" -session %s -change_catprotection none' );
	define( 'ACTIONS_BACKUPMON_CMD', '"%s\checkBackupStatus.exe" -postexec -nocase' );
} else {
	define( 'ACTIONS_OMNIDB_CMD', '"%s/omnidb" -rpt %s -detail' );
	define( 'ACTIONS_OMNIRPT_SINGLE_SESSION_CMD', '"%s/omnirpt" -report single_session -session %s -level critical -tab' );
	define( 'ACTIONS_OMNIRPT_SESSION_DEVICES_CMD', '"%s/omnirpt" -report session_devices -session %s -tab' );
	define( 'ACTIONS_OMNIRPT_SESSION_OBJECTS_CMD', '"%s/omnirpt" -report session_objects -session %s -tab' );
	define( 'ACTIONS_OMNIRPT_SESSION_MEDIA_CMD', '"%s/omnirpt" -report session_media -session %s -tab' );
	define( 'ACTIONS_OMNIDB_CHANGE_PROTECTION_CMD', '"%s/omnidb" -session %s -change_protection none' );
	define( 'ACTIONS_OMNIDB_CHANGE_CATPROTECTION_CMD', '"%s/omnidb" -session %s -change_catprotection none' );
	define( 'ACTIONS_BACKUPMON_CMD', '"%s/checkBackupStatus" -postexec -nocase' );
}

class mars_action {
	var $application;
	var $items;

	function mars_action( $application, $items ) {
		$this->application = $application;
		$this->items = array_change_key_case( $items, CASE_UPPER );
	}

	function execute() {
		try {
			$result = false;
			switch ( strtoupper( $this->items[ 'NAME' ] ) ) {
				case 'UPDATESCRIPT':
					$result = $this->updatescript( );
					break;
				case 'REMOVEFILE':
					$result = $this->removefile( );
					break;
				case 'ADDCONFIG':
					$result = $this->addconfig( );
					break;
				case 'MODIFYCONFIG':
					$result = $this->modifyconfig( );
					break;
				case 'REMOVECONFIG':
					$result = $this->removeconfig( );
					break;
				case 'ROUTINE':
					$result = $this->routine( );
					break;
				case 'STARTOFCOPY':
					$result = $this->startofcopy( );
					break;
				case 'STARTOFSESSION':
					$result = $this->startofsession( );
					break;
				case 'ENDOFCOPY':
					$result = $this->endofcopy( );
					break;
				case 'ENDOFSESSION':
					$result = $this->endofsession( );
					break;
				case 'ENDOFSESSIONDEVICES':
					$result = $this->endofsessiondevices( );
					break;
				case 'ENDOFSESSIONOBJECTS':
					$result = $this->endofsessionobjects( );
					break;
				case 'ENDOFSESSIONMEDIA':
					$result = $this->endofsessionmedia( );
					break;
				default:
					throw new exception( sprintf( ACTIONS_ERROR_UNHANDLED, $this->items[ 'NAME' ] ) );
					break;
			}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( ACTIONS_ERROR, $this->items[ 'NAME' ], $this->items[ 'DATA' ], $e->getmessage( ) ) );
			$result = true;
		}
		return $result;
	}

	function updatescript() {
		empty( $this->items[ 'DATA' ] ) && $this->items[ 'DATA' ] = '.*';
		$sql = "select * from config_scripts where (id regexp '%data' or name regexp '%data') and valid_until is null ";
		if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
			$sql .= "and name not regexp '\.sh$';";
		} else {
			$sql .= "and name not regexp '\.cmd$';";
		}
		$values = array( 
			'data' => $this->items[ 'DATA' ] );
		$this->application->database->execute_query( $sql, $values );
		if ( $this->application->database->row_count == 0 ) {throw new exception( sprintf( ACTIONS_ERROR_NO_SCRIPT, $this->items[ 'DATA' ] ) );}
		foreach ( $this->application->database->rows as $row ) {
			$row = array_change_key_case( $row, CASE_UPPER );
			$code = str_replace( "\r\n", PHP_EOL, $row[ 'CODE' ] );
			$result = file_put_contents( $this->application->root . DIRECTORY_SEPARATOR . $row[ 'NAME' ], $code );
			if ( !$result ) {throw new exception( sprintf( ACTIONS_ERROR_CANNOT_WRITE ), $row[ 'NAME' ] );}
			$this->application->log_action( sprintf( ACTIONS_SCRIPT_UPDATED, $row[ 'NAME' ] ) );
		}
		return true;
	}

	function removefile() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_FILENAME );}
		$file = $this->application->root . DIRECTORY_SEPARATOR . $this->items[ 'DATA' ];
		$result = true;
		if ( file_exists( $file ) ) {
			$result = unlink( $file );
			$this->application->log_action( sprintf( $result ? ACTIONS_FILE_REMOVED : ACTIONS_FILE_NOT_REMOVED, $file ) );
		} else {
			$this->application->log_error( sprintf( ACTIONS_FILE_NOT_EXISTS, $file ) );
		}
		return $result;
	}

	function writeconfig( $findkey, $value = '', $insertkey = '' ) {
		$filename = $this->application->root . DIRECTORY_SEPARATOR . MARS_CONFIG;
		$config = explode( PHP_EOL, $this->application->read_file( $filename ) );
		$index = -1;
		$remove = 1;
		$replace = array();
		$findkey = strtoupper( $findkey );
		$insertkey = strtoupper( $insertkey );
		foreach ( $config as $item ) {
			( strpos( $item, $findkey ) === 0 ) && $index = array_search( $item, $config );
		}
		if ( $index == -1 ) {throw new exception( sprintf( ACTIONS_KEY_NOT_EXISTS, $findkey ) );}
		if ( $value != '' ) {
			if ( $insertkey == '' ) {
				$replace = array( 
					sprintf( "%-24s=%s", $findkey, trim( $value ) ) );
			} else {
				$remove = 0;
				$index++;
				$replace = array( 
					sprintf( "%-24s=%s", $insertkey, trim( $value ) ) );
			}
		}
		array_splice( $config, $index, $remove, $replace );
		$result = file_put_contents( $filename, implode( PHP_EOL, $config ) );
		if ( !$result ) {throw new exception( sprintf( ACTIONS_ERROR_CANNOT_WRITE, $filename ) );}
	}

	function addconfig() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_KEY );}
		list ( $after, $key, $value ) = explode( ' ', $this->items[ 'DATA' ], 3 );
		$this->writeconfig( $after, $value, $key );
		$this->application->log_action( sprintf( ACTIONS_KEY_ADDED, $key, $value, $after ) );
		return true;
	}

	function modifyconfig() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_KEY );}
		list ( $key, $value ) = explode( ' ', $this->items[ 'DATA' ], 2 );
		$old = empty( $this->application->config[ $key ] ) ? '' : $this->application->config[ $key ];
		$this->writeconfig( $key, $value );
		$this->application->log_action( sprintf( ACTIONS_KEY_MODIFIED, $key, $old, $value ) );
		return true;
	}

	function removeconfig() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_KEY );}
		$key = $this->items[ 'DATA' ];
		$this->writeconfig( $key );
		$this->application->log_action( sprintf( ACTIONS_KEY_REMOVED, $key ) );
		return true;
	}

	function routine() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_ROUTINE );}
		$routine = new mars_routine( $this->application, $this->items[ 'DATA' ] );
		return $routine->execute( );
	}

	function startofcopy() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		return true;
	}

	function was_scheduled_at( $specification, $starttime ) {
		$scheduled = 0;
		$started = $this->application->parse_date( $starttime );
		$s = new mars_specification( $this->application, $specification );
		$s->read_database( );
		$nextexecution = $s->get_nextexecution( date( $started ) );
		$delay = isset( $this->application->config[ 'SCHEDULED_DELAY' ] ) ? $this->application->config[ 'SCHEDULED_DELAY' ] : ACTIONS_SCHEDULED_SESSION_DELAY_SECONDS; 
		if ( $nextexecution ) {
			foreach ( $nextexecution as $n ) {
				$difference = strtotime( $started ) - strtotime( $n[ 'date' ] . ' ' . $n[ 'time' ] );
				$scheduled = ( $difference >= 0 and $difference <= $delay ) ? 1 : $scheduled;
			}
		}
		return $scheduled;
	}

	function startofsession() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		if ( isset( $this->application->config[ 'STARTOFSESSION_SKIP' ] ) ) {
			$message = sprintf( ACTIONS_STARTOFSESSION_SKIPPED, $this->items[ 'SESSIONID' ] );
			return true;
		}
		$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'SoS-%s', $this->items[ 'DATA' ] ) );
		$command = sprintf( ACTIONS_OMNIDB_CMD, $this->application->config[ 'OMNI_BIN' ], $this->items[ 'DATA' ] );
		$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		if ( !file_exists( $file ) ) {
			$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $this->items ) );
			return false;
		}
		$worker = $this->application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
		array_push( $lines, '' );
		foreach ( $lines as $line ) {
			$key = strtoupper( trim( substr( $line, 0, strpos( $line, ':' ) ) ) );
			$value = trim( substr( strstr( $line, ':' ), 1 ) );
			switch ( $key ) {
				case 'SESSIONID':
					$sessionid = $value;
					break;
				case 'BACKUP SPECIFICATION':
					$specification = $value;
					break;
				case 'SESSION TYPE':
					$type = $value;
					break;
				case 'STARTED':
					$started = $value;
					break;
				case 'FINISHED':
					$finished = $value;
					break;
				case 'STATUS':
					$status = $value;
					break;
				case 'NUMBER OF WARNINGS':
					$warnings = $value;
					break;
				case 'NUMBER OF ERRORS':
					$errors = $value;
					break;
				case 'USER':
					$user = $value;
					break;
				case 'GROUP':
					$group = $value;
					break;
				case 'HOST':
					$host = $value;
					break;
				case 'SESSION SIZE':
				case 'SESSION DATA SIZE [KB]':
					$sessionsize = $value;
					break;
				case '':
					break;
				default:
					$this->application->log_error( sprintf( ACTIONS_ERROR_OMNIDB_UNKNOWN_ITEM, $this->items[ 'DATA' ], $key, $value ) );
			}
		}
		if ( !isset( $sessionid ) ) {throw new exception( sprintf( ACTIONS_ERROR_OMNIDB_INVALID_OUTPUT, trim( implode( '', $lines ) ) ) );}
		if ( $type != 'Backup' ) {return true;}
		$scheduled = $this->was_scheduled_at( $specification, $started );
		$this->application->log_action( 
			sprintf( ACTIONS_SESSION_STARTED, $sessionid, date( 'H:i', strtotime( $started ) ), $specification, $type, $scheduled ) );
		return true;
	}

	function endofcopy() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		return true;
	}

	function endofsession() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'EoS-S-%s', $this->items[ 'DATA' ] ) );
		$command = sprintf( ACTIONS_OMNIRPT_SINGLE_SESSION_CMD, $this->application->config[ 'OMNI_BIN' ], $this->items[ 'DATA' ] );
		$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		if ( !file_exists( $file ) ) {
			$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $this->items ) );
			return false;
		}
		$worker = $this->application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
		foreach ( $lines as $line ) {
			if ( empty( $line ) or ( $line[ 0 ] == '#' ) ) continue;
			@list ( $specification, $sessionid, $type, $sessionowner, $status, $mode, $starttime, $starttime_t,
					$endtime, $endtime_t, $queuing, $duration, $gbwritten, $media, $errors, $warnings, $pendingda, $runningda, $failedda, $completedda, $objects, $success ) =
					explode( "\t", rtrim( $line ) );
					break;
		}
		if ( !isset( $sessionid ) ) {
			throw new exception(
				sprintf( ACTIONS_ERROR_OMNIRPT_INVALID_SINGLE_SESSION_OUTPUT, trim( implode( '', $lines ) ) ) );
		}
		if ( $type != 'Backup' ) {
			return true;
		}
		$sessionstatus = 'failed';
		if ( stristr( $status, 'mount' ) ) $sessionstatus = 'in M/R';
		if ( stristr( $status, 'aborted' ) ) $sessionstatus = 'aborted';
		if ( ( $queuing == $duration ) and ( $duration !== "0:00" ) and ( $status !== "Completed" ) and ( $errors = 2 ) ) $sessionstatus = 'timed out';
		if ( stristr( $status, 'progress' ) ) $sessionstatus = 'in progress';
		if ( $success == '100%' ) $sessionstatus = 'success';
		$scheduled = $this->was_scheduled_at( $specification, $this->application->parse_date( $starttime ) );
		$sql = "insert into dataprotector_sessions " .
				"(cellserver,specification,sessionid,scheduled,restartof,type,sessionowner,status,mode," .
				"starttime,starttimet,endtime,endtimet,queuing,duration,gbwritten,media," .
				"errors,warnings,pendingda,runningda,failedda,completedda,objects,success) " .
				"values ('%cellserver','%specification','%sessionid','%scheduled',function_restartof('%cellserver','%sessionid')," .
				"'%type','%sessionowner','%status','%mode','%starttime','%starttimet',nullif('%endtime',''),'%endtimet'," .
				"'%queuing','%duration','%gbwritten','%media','%errors','%warnings','%pendingda'," .
				"'%runningda','%failedda','%completedda','%objects','%success') on duplicate key update " .
				"status='%status',endtime=nullif('%endtime',''),endtimet='%endtimet',duration='%duration',gbwritten='%gbwritten'," .
				"media='%media',errors='%errors',warnings='%warnings',pendingda='%pendingda',runningda='%runningda'," .
				"failedda='%failedda',completedda='%completedda',objects='%objects',success='%success';";
		$values = array(
				'cellserver' => $this->application->cellserver,
				'specification' => $specification,
				'sessionid' => $sessionid,
				'scheduled' => $scheduled,
				'type' => $type,
				'sessionowner' => $sessionowner,
				'status' => $status,
				'mode' => $mode,
				'starttime' => $this->application->parse_date( $starttime ),
				'starttimet' => $starttime_t,
				'endtime' => $this->application->parse_date( $endtime ),
				'endtimet' => $endtime_t,
				'queuing' => $queuing,
				'duration' => $duration,
				'gbwritten' => $this->application->parse_decimal( $gbwritten ),
				'media' => $media,
				'errors' => $errors,
				'warnings' => $warnings,
				'pendingda' => $pendingda,
				'runningda' => $runningda,
				'failedda' => $failedda,
				'completedda' => $completedda,
				'objects' => $objects,
				'success' => $success );
		$this->application->database->execute_query( $sql, $values );
		$sql = $sql = "update dataprotector_specifications set lastsessionid='%sessionid',";
		if ( $success == '100% ' ) {
			$sql .= "lastsuccessfulsessionid='%sessionid',success=success+1,failure=0";
		} else {
			$sql .= "success=0,failure=failure+1";
		}
		$sql .= " where cellserver='%cellserver' and name='%specification';";
		$values = array(
				'cellserver' => $this->application->cellserver,
				'specification' => $specification,
				'sessionid' => $sessionid );
		$this->application->database->execute_query( $sql, $values );
		if ( ( $this->application->config[ 'RECYCLE_FAILURES' ] ) and ( $completedda > 0 ) and ( $success != '100%' ) ) {
			try {
				$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'OMNIDB-CP-%s', $sessionid ) );
				$command = sprintf( ACTIONS_OMNIDB_CHANGE_PROTECTION_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid );
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name ) );
				$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'OMNIDB-CCP-%s', $sessionid ) );
				$command = sprintf( ACTIONS_OMNIDB_CHANGE_CATPROTECTION_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid );
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name ) );
				$this->application->log_action( sprintf( ACTIONS_SESSION_RECYCLED, $sessionid, $sessionstatus ) );
			}
			catch ( exception $e ) {
				$this->application->log_error( sprintf( ACTIONS_ERROR_SESSION_RECYCLED, $sessionid, $specification, $e->getmessage( ) ) );
			}
		}
		$ito = 0;
		if ( ( $success != '100%' ) and 
			( strtotime( $this->application->start_time ) - strtotime( $this->application->parse_date( $endtime ) ) < ACTIONS_ITO_THRESHOLD_SECONDS ) ) {
			$sql = "select s1.failure,r1.ticket_threshold from dataprotector_specifications s1 " .
				"left join config_retentions r1 on ((r1.customer=s1.customer or r1.customer is null) and r1.name=s1.retention) " .
				"where s1.cellserver='%cellserver' and s1.name='%specification' order by r1.customer desc limit 1;";

			$values = array(
				'cellserver' => $this->application->cellserver,
				'specification' => $specification );
			$failures = $threshold = 0;
			if ( $this->application->database->execute_query( $sql, $values ) ) list ( $failures, $threshold ) = $this->application->database->rows[ 0 ];
			if ( $failures >= $threshold ) {
				$message = sprintf( ACTIONS_ITO_MESSAGE, $sessionstatus, $failures );
				$this->application->create_ticket( $this->application->config[ 'ITO_PRIORITY' ], $sessionid, $specification, $message );
				$sessionstatus .= sprintf( " (%d.)", $failures );
				$ito = 1;
			}
		}
		$backupmon = 0;
		if ( $this->application->config[ 'BACKUPMON' ] !== '' ) {
			try {
				$name = 'BACKUPMON';
				$command = sprintf( ACTIONS_BACKUPMON_CMD, $this->application->config[ 'BACKUPMON' ] );
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name ) );
				$backupmon = 1;
			}
			catch ( exception $e ) {
				$this->application->log_error( sprintf( ACTIONS_ERROR_BACKUPMON, $sessionid, $specification, $e->getmessage( ) ) );
			}
		}
		queue_action( $this->application, 'EndOfSessionDevices', $sessionid );
		queue_action( $this->application, 'EndOfSessionObjects', $sessionid );
		queue_action( $this->application, 'EndOfSessionMedia', $sessionid );
		$this->application->log_action(	sprintf( ACTIONS_SESSION_LOG, 
			$sessionid, date( 'H:i', strtotime( $endtime ) ), $sessionstatus, $objects, $media, $ito, $backupmon, $scheduled ) );
		return true;
	}

	function endofsessiondevices() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		$sessionid = $this->items[ 'DATA' ];
		$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'EoS-D-%s', $sessionid ) );
		$command = sprintf( ACTIONS_OMNIRPT_SESSION_DEVICES_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid );
		$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		if ( !file_exists( $file ) ) {
			$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $this->items ) );
			return false;
		}
		$devices = 0;
		$worker = $this->application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
		foreach ( $lines as $line ) {
			if ( empty( $line ) or ( $line[ 0 ] == '#' ) ) continue;
			unset( $device );
			@list ( $device, $starttime, $starttime_t, $endtime, $endtime_t, $duration, $gbwritten, $perf ) = explode( "\t",
					rtrim( $line ) );
			if ( !isset( $device ) or preg_match( '/^Error/' , $line ) ) {
				throw new exception( sprintf( ACTIONS_ERROR_OMNIRPT_INVALID_SESSION_DEVICES_OUTPUT, trim( implode( '', $lines ) ) ) );
			}
			$sql = "insert into dataprotector_session_devices (cellserver,sessionid,device,starttime,starttimet," .
				"endtime,endtimet,duration,gbwritten,performance) values " .
				"('%cellserver','%sessionid','%device','%starttime','%starttimet'," .
				"nullif('%endtime',''),'%endtimet','%duration','%gbwritten','%performance') on duplicate key update " .
				"endtime=nullif('%endtime',''), endtimet='%endtimet', duration='%duration'," .
				"gbwritten='%gbwritten', performance='%performance';";
			$values = array(
				'cellserver' => $this->application->cellserver,
				'sessionid' => $sessionid,
				'device' => substr( $device, 0, 32 ),
				'starttime' => $this->application->parse_date( $starttime ),
				'starttimet' => $starttime_t,
				'endtime' => $this->application->parse_date( $endtime ),
				'endtimet' => $endtime_t,
				'duration' => $duration,
				'gbwritten' => $this->application->parse_decimal( $gbwritten ),
				'performance' => $this->application->parse_decimal( $perf ) );
			$this->application->database->execute_query( $sql, $values );
			$devices++;
		}
		$this->application->log_action(	sprintf( 'Session "%s" used %s devices', $sessionid, $devices ) );
		return true;
	}

	function endofsessionobjects() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		$sessionid = $this->items[ 'DATA' ];
		$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'EoS-O-%s', $sessionid ) );
		$command = sprintf( ACTIONS_OMNIRPT_SESSION_OBJECTS_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid );
		$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		if ( !file_exists( $file ) ) {
			$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $this->items ) );
			return false;
		}
		$objects = 0;
		$worker = $this->application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
		$clients = array();
		$sql = "delete from dataprotector_session_objects where cellserver='%cellserver' and sessionid='%sessionid';";
		$values = array(
				'cellserver' => $this->application->cellserver,
				'sessionid' => $sessionid );
		$this->application->database->execute_query( $sql, $values );
		foreach ( $lines as $line ) {
			if ( empty( $line ) or ( $line[ 0 ] == '#' ) ) continue;
			unset( $object_type );
			if ( preg_match( '/Object Name/', implode( PHP_EOL, $lines ) ) ) {
				@list ( $object_type, $client, $mountpoint, $description, $objectname, $status, $mode, $starttime, $starttime_t,
					$endtime, $endtime_t, $duration, $kbwritten, $files, $perf, $protection, $errors, $warnings, $device ) =
					explode( "\t", rtrim( $line ) );
			} else {
				@list ( $object_type, $client, $mountpoint, $description, $status, $mode, $starttime, $starttime_t,
					$endtime, $endtime_t, $duration, $kbwritten, $files, $perf, $protection, $errors, $warnings, $device ) =
					explode( "\t", rtrim( $line ) );
			}
			if ( !isset( $object_type ) or preg_match( '/^Error/' , $line ) ) {
				throw new exception( sprintf( ACTIONS_ERROR_OMNIRPT_INVALID_SESSION_OBJECTS_OUTPUT, trim( implode( '', $lines ) ) ) );
			}
			$sql = "insert into dataprotector_session_objects (cellserver,sessionid,type,client,mountpoint,description,status,mode," .
				"starttime,starttimet,endtime,endtimet,duration,kbwritten,files,performance,protection,errors,warnings,device) " .
				"values ('%cellserver','%sessionid','%type','%client','%mountpoint','%description','%status'," .
				"'%mode','%starttime','%starttimet',nullif('%endtime',''),'%endtimet','%duration'," .
				"'%kbwritten','%files','%performance',nullif('%protection',''),'%errors','%warnings','%device') " .
				"on duplicate key update " .
				"status='%status', endtime=nullif('%endtime',''), endtimet='%endtimet', duration='%duration', " .
				"kbwritten='%kbwritten', files='%files', performance='%performance', protection=nullif('%protection',''), " .
				"errors='%errors', warnings='%warnings';";
			$values = array(
				'cellserver' => $this->application->cellserver,
				'sessionid' => $sessionid,
				'type' => $object_type,
				'client' => substr( $client, 0, 64 ),
				'mountpoint' => $this->application->database->escape_string( substr( $mountpoint, 0, 127 ) ),
				'description' => $this->application->database->escape_string( substr( $description, 0, 127 ) ),
				'status' => $status,
				'mode' => $mode,
				'starttime' => $this->application->parse_date( $starttime ),
				'starttimet' => $starttime_t,
				'endtime' => $this->application->parse_date( $endtime ),
				'endtimet' => $endtime_t,
				'duration' => $duration,
				'kbwritten' => $this->application->parse_decimal( $kbwritten ),
				'files' => $files,
				'performance' => $this->application->parse_decimal( $perf ),
				'protection' => $this->application->parse_date( $protection ),
				'errors' => $errors,
				'warnings' => $warnings,
				'device' => substr( $device, 0, 32 ) );
			$this->application->database->execute_query( $sql, $values );
			$clients[ ] = array(
					'name' => $client,
					'type' => $object_type );
			$objects++;
		}
		foreach ( super_unique( $clients ) as $client ) {
			$name = $client[ 'name' ];
			$type = $client[ 'type' ];
			$sql = "select ifnull(c1.name, s1.customer) as customer from dataprotector_sessions s " .
					"left join dataprotector_specifications s1 on ( s1.cellserver=s.cellserver and s1.name = s.specification )" .
					"left join config_customers c1 on ('%name' regexp replace(c1.fqdn,'.','\\.')) " .
					"where s.cellserver='%cellserver' and s.sessionid = '%sessionid' limit 1;";
			$values = array(
					'name' => $name,
					'sessionid' => $sessionid,
					'cellserver' => $this->application->cellserver );
			$this->application->database->execute_query( $sql, $values );
			if ( $this->application->database->row_count == 1 ) {
				$customer = $this->application->database->rows[ 0 ][ 'customer' ];
			} else {
				$customer = '';
			}
			$sql = "insert into dataprotector_clients (cellserver,name,type,customer,specifications,mountpoints) values " .
					"('%cellserver','%name','%type',nullif('%customer',''),%specifications,%mountpoints) " .
					"on duplicate key update customer=nullif('%customer','');";
			$values = array(
					'cellserver' => $this->application->cellserver,
					'name' => $name,
					'type' => $type,
					'customer' => $customer,
					'specifications' => 1,
					'mountpoints' => $objects );
			$this->application->database->execute_query( $sql, $values );
		}
		$this->application->log_action(	sprintf( 'Session "%s" had %s objects', $sessionid, $objects ) );
		return true;
	}
		
	function endofsessionmedia() {
		if ( empty( $this->items[ 'DATA' ] ) ) {throw new exception( ACTIONS_ERROR_EMPTY_SESSIONID );}
		$sessionid = $this->items[ 'DATA' ];
		$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'EoS-M-%s', $sessionid ) );
		$command = sprintf( ACTIONS_OMNIRPT_SESSION_MEDIA_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid );
		$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		if ( !file_exists( $file ) ) {
			$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $this->items ) );
			return false;
		}
		$media = 0;
		$worker = $this->application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
		$clients = array();
		$sql = "delete from dataprotector_session_objects where cellserver='%cellserver' and sessionid='%sessionid';";
		$values = array(
				'cellserver' => $this->application->cellserver,
				'sessionid' => $sessionid );
		$this->application->database->execute_query( $sql, $values );
		foreach ( $lines as $line ) {
			if ( empty( $line ) or ( $line[ 0 ] == '#' ) ) continue;
			unset( $mediumid );
			@list ( $mediumid, $label, $location, $pool, $protection, $used, $total, $lastused, $lastused_t ) =
			explode( "\t", rtrim( $line ) );
			if ( !isset( $mediumid ) or preg_match( '/^Error/' , $line ) ) {
				throw new exception( sprintf( ACTIONS_ERROR_OMNIRPT_INVALID_SESSION_MEDIA_OUTPUT, trim( implode( '', $lines ) ) ) );
			}
			$sql = "insert into dataprotector_session_media (cellserver,sessionid,mediumid,label,location,pool," .
				"protection,used,total,lastused,lastusedt) values " .
				"('%cellserver','%sessionid','%mediumid','%label','%location','%pool'," .
				"nullif('%protection',''),'%used','%total','%lastused','%lastusedt') on duplicate key update " .
				"protection=nullif('%protection',''), used='%used', total='%total',lastused='%lastused', lastusedt='%lastusedt';";
			$values = array(
				'cellserver' => $this->application->cellserver,
				'sessionid' => $sessionid,
				'mediumid' => $mediumid,
				'label' => $label,
				'location' => $location,
				'pool' => $pool,
				'protection' => $this->application->parse_date( $protection ),
				'used' => $this->application->parse_decimal( $used ),
				'total' => $this->application->parse_decimal( $total ),
				'lastused' => $this->application->parse_date( $lastused ),
				'lastusedt' => $lastused_t );
			$this->application->database->execute_query( $sql, $values );
			$media++;
		}
		$this->application->log_action(	sprintf( 'Session "%s" used %s media', $sessionid, $media ) );
		return true;
	}
}


function super_unique($array) {
	$result = array_map("unserialize", array_unique(array_map("serialize", $array)));
	foreach ($result as $key => $value) {
		if ( is_array($value) ) {
			$result[$key] = super_unique($value);
		}
	}
	return $result;
}

function execute_actions( $application, $name = '', $data = '' ) {

	$sql = "select if(name regexp 'UPDATE|ENDOFSESSION$',1,if(data regexp 'OMNISTAT|LOCKED',3,2)) as prio, " . 
		"id,timestamp,cellserver,name,data,valid_from,valid_until,executed_on " .
		"from mars_queue where cellserver='%cellserver' and valid_from<='%now' " .
		"and ((valid_until is null) or (valid_until>='%time')) " . 
		"and ((name='%name') or ('%name'='' and executed_on is null)) " . 
		"and ((data='%data') or ('%data'='' and executed_on is null)) " .
		"order by prio,timestamp limit 1;";
	$values = array( 
		'cellserver' => $application->cellserver,
		'time' => $application->start_time, 
		'now' => date( $application->config[ 'TIME_FORMAT' ] ),
		'name' => $name,
		'data' => $data
	);
	$application->database->execute_query( $sql, $values );
	$rows = $application->database->row_count;
	if ( $rows == 1 ) {
		$row = $application->database->rows[ 0 ];
		$id = $row[ 'id' ];
		$sql = "update mars_queue set executed_on='%now' where id='%id';";
		$values = array( 
			'now' => date( $application->config[ 'TIME_FORMAT' ] ),
			'id' => $id );
		$application->database->execute_query( $sql, $values );
		$action = new mars_action( $application, $row );
		if ( $action->execute( ) ) {
			$sql = "delete from mars_queue where id='%id';";
			$values = array( 
				'now' => date( $application->config[ 'TIME_FORMAT' ] ),
				'id' => $id );
			$application->database->execute_query( $sql, $values );
		}
	}
	return $rows;
}

function queue_action( $application, $name, $data, $delay = 0, $valid = 0 ) {
	$valid_from = ( $delay == 0 ) ? $application->start_time : date( $application->config[ 'TIME_FORMAT' ], 
		strtotime( sprintf( '+%d minutes', $delay ), strtotime( $application->start_time ) ) );
	$valid_until = ( $valid == 0 ) ? '' : date( $application->config[ 'TIME_FORMAT' ], 
		strtotime( sprintf( '+%d minutes', $valid ), strtotime( $application->start_time ) ) );
	$sql = "insert into mars_queue (timestamp, cellserver, name, data, valid_from, valid_until ) " .
		 "values ('%timestamp', '%cellserver', '%name', '%data', '%valid_from', nullif( '%valid_until', '' ) ) " .
		 "on duplicate key update valid_from=nullif('%valid_from',''), valid_until=nullif('%valid_until',''), executed_on=null";
	$values = array( 
		'timestamp' => date( $application->config[ 'TIME_FORMAT' ] ), 
		'cellserver' => $application->cellserver, 
		'name' => $name, 
		'data' => $data, 
		'valid_from' => $valid_from, 
		'valid_until' => $valid_until );
	$application->database->execute_query( $sql, $values );
	return true;
}
