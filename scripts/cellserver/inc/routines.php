<?php

/*
 * MARS 3.0 ROUTINES PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'ROUTINES', 'LIBRARIES,DEVICES,MEDIA,SPECIFICATIONS,OMNI2ESL,CHECK_BACKUPS,LOCKED_OBJECTS,OMNISTAT' );
define( 'ROUTINES_ERROR', 'Error: %s: %s' );
define( 'ROUTINES_ERROR_UNHANDLED', 'Unhandled routine "%s".' );
define( 'ROUTINES_OMNISTAT_TIMEOUT', 15 );
define( 'ROUTINES_CHECK_BACKUPS_TYPE', 'backup' );
define( 'ROUTINES_CHECK_BACKUPS_SESSIONS_ACTIVE', 'progress|mount|queuing' );
define( 'ROUTINES_CHECK_BACKUPS_DAYS', 3 );
define( 'ROUTINES_CHECK_BACKUPS_LOG', 'CHECK BACKUPS: %s missing, %s without status, %s with different status, %s/%s/%s without devices/objects/media details.' );
define( 'ROUTINES_OMNIDBUTIL_TIMEOUT', 15 );
define( 'ROUTINES_ERROR_LOCKED_OBJECTS_UNKNOWN_ITEM', 'Unknown "omnidbutil show_locked_devs" item "%s"="%s".' );
define( 'ROUTINES_LOCKED_OBJECTS_LOG', 'LOCKED OBJECTS: %s inserted, %s updated, %s removed.' );
define( 'ROUTINES_OMNISTAT_DETAILS', 'OMNISTAT_DETAILS' );
define( 'ROUTINES_OMNISTAT_DEVICES_ACTIVE', 'mount|running|reading|writing' );
define( 'ROUTINES_OMNISTAT_OBJECTS_ACTIVE', 'pending|running' );
define( 'ROUTINES_ERROR_OMNISTAT_DETAILS', 'Error reading "omnistat -details" for session "%s" : "%s".' );
define( 'ROUTINES_ERROR_OMNISTAT_UNKNOWN_ITEM', 'Unknown "omnistat" item "%s"="%s".' );
define( 'ROUTINES_OMNISTAT_LOG', 'RUNNING SESSIONS: %s inserted, %s updated, %s removed.' );
define( 'ROUTINES_ERROR_OMNISTAT_DETAILS_EMPTY', 'Empty report.' );
define( 'ROUTINES_ERROR_OMNISTAT_DETAILS_INVALID', 'Invalid report: "%s".' );
define( 'ROUTINES_ERROR_OMNISTAT_DETAILS_UNKNOWN_ITEM', 'Unknown item "%s"="%s".' );

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	define( 'ROUTINES_OMNISTAT_PREV_CMD', '"%s\omnistat.exe" -previous -last %s' );
	define( 'ROUTINES_OMNIDBUTIL_SHOW_LOCKED_DEVS_CMD', '"%s\omnidbutil.exe" -show_locked_devs -all' );
	define( 'ROUTINES_OMNISTAT_DETAIL_CMD', '"%s\omnistat.exe" -detail' );
	define( 'ROUTINES_OMNISTAT_SESSION_DETAIL_CMD', '"%s\omnistat.exe" -session %s -detail' );
} else {
	define( 'ROUTINES_OMNISTAT_PREV_CMD', '"%s/omnistat" -previous -last %s' );
	define( 'ROUTINES_OMNIDBUTIL_SHOW_LOCKED_DEVS_CMD', '"%s/omnidbutil" -show_locked_devs -all' );
	define( 'ROUTINES_OMNISTAT_DETAIL_CMD', '"%s/omnistat" -detail' );
	define( 'ROUTINES_OMNISTAT_SESSION_DETAIL_CMD', '"%s/omnistat" -session %s -detail' );
}

class mars_routine {
	var $application;
	var $name;

	function mars_routine( $application, $name ) {
		$this->application = $application;
		$this->name = strtoupper( $name );
	}

	function execute( $action = true ) {
		try {
			$result = false;
			switch ( strtoupper( $this->name ) ) {
				case 'LIBRARIES':
					$result = $this->libraries( $action );
					break;
				case 'DEVICES':
					$result = $this->devices( $action );
					break;
				case 'MEDIA':
					$result = $this->media( $action );
					break;
				case 'POOLS':
					$result = $this->pools( $action );
					break;
				case 'SPECIFICATIONS':
					$result = $this->specifications( $action ) && $this->copylists( $action );
					break;
				case 'OMNI2ESL':
					$result = $this->omni2esl( $action );
					break;
				case 'CHECK_BACKUPS':
					$result = $this->check_backups( $action );
					break;
				case 'LOCKED_OBJECTS':
					$result = $this->locked_objects( $action );
					break;
				case 'OMNISTAT':
					$result = $this->omnistat( $action );
					break;
				default:
					if ( strpos( $this->name, 'OMNISTAT:' ) === 0 ) {
						list( $name, $sessionid ) = explode( ':', $this->name );
						$result = $this->omnistat_details( $sessionid, $action );
					} else throw new exception( sprintf( ROUTINES_ERROR_UNHANDLED, $this->name ) );
					break;
			}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( ROUTINES_ERROR, $this->name, $e->getmessage( ) ) );
			$result = true;
		}
		return $result;
	}

	function libraries( $action ) {
		$result = true;
		if ( $this->application->mmd_local ) $result = read_libraries( $this->application, $action );
		return $result;
	}

	function devices( $action ) {
		$result = true;
		if ( $this->application->mmd_local ) $result = read_devices( $this->application, $action );
		return $result;
	}

	function media( $action ) {
		$result = true;
		if ( $this->application->mmd_local ) $result = read_media( $this->application, $action );
		return $result;
	}

	function pools( $action ) {
		$result = true;
		if ( $this->application->mmd_local ) $result = read_pools( $this->application, $action );
		return $result;
	}
	
	function specifications( $action ) {
		read_specifications( $this->application );
		return true;
	}

	function copylists( $action ) {
		read_copylists( $this->application );
		return true;
	}
	
	function omni2esl( $action ) {
#		if ( is_dir( $this->application->config[ 'OMNI2ESL' ] ) ) omni2esl( $this->application );
		omni2esl( $this->application );
		return true;
	}

	function check_backups( $action ) {
		if ( $action ) {
			$name = 'OMNISTAT-PREV';
			$command = sprintf( ROUTINES_OMNISTAT_PREV_CMD, $this->application->config[ 'OMNI_BIN' ], ROUTINES_CHECK_BACKUPS_DAYS );
			$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
			$items = array( 'NAME' => 'ROUTINE', 'DATA' => $this->name );
			if ( !file_exists( $file ) ) {
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
				return false;
			}
			$worker = $this->application->workers->decode( file_get_contents( $file ) );
			$lines = $worker->output;
			unset( $worker );
			unlink( $file );
		} else {
			$lines = $this->application->cache_command(
				sprintf( ROUTINES_OMNISTAT_PREV_CMD, $this->application->config[ 'OMNI_BIN' ], ROUTINES_CHECK_BACKUPS_DAYS ),
				ROUTINES_OMNISTAT_TIMEOUT );
		}
		$m = $d = $n = $wd = $wo = $wm = 0;
		foreach ( $lines as $line ) {
			if ( !preg_match( sprintf( '/%s/i', ROUTINES_CHECK_BACKUPS_TYPE ), $line ) ) continue;
			if ( preg_match( sprintf( '/%s/i', ROUTINES_CHECK_BACKUPS_SESSIONS_ACTIVE ), $line ) ) continue;
			list ( $sessionid, $type, $status, $owner, $dummy ) = sscanf( $line, '%s %s %s %s %s' );
			if ( $status == 'In' ) {
				$status = sprintf( '%s %s', $status, $owner );
				$owner = $dummy;
			}
			$sql = "select status from _sessions where cellserver='%cellserver' and sessionid='%sessionid';";
			$values = array( 
				'cellserver' => $this->application->cellserver, 
				'sessionid' => $sessionid );
			$result = $this->application->database->execute_query( $sql, $values );
			if ( $result == 0 ) {
				queue_action( $this->application, 'ENDOFSESSION', $sessionid );
				$m++;
			} else {
				$row = $this->application->database->rows[ 0 ];
				if ( is_null( $row[ 'status' ] ) ) {
					queue_action( $this->application, 'ENDOFSESSION', $sessionid );
					$n++;
				} else {
					if ( str_replace( '/', '', $row[ 'status' ] ) != str_replace( '/', '', $status ) ) {
						queue_action( $this->application, 'ENDOFSESSION', $sessionid );
						$d++;
					}
				}
			}
		}
		$values = array(
				'cellserver' => $this->application->cellserver,
				'sessionid' => $sessionid,
				'last' => ROUTINES_CHECK_BACKUPS_DAYS,
				'skip' => ROUTINES_CHECK_BACKUPS_SESSIONS_ACTIVE );
		$sql = "select s1.sessionid from _sessions s1 where s1.cellserver='%cellserver' " .
				"and s1.starttime>date_sub(now(),interval %last day) " .
				"and status not regexp '%skip' and s1.gbwritten>0 " .
				"and not exists (select id from dataprotector_session_devices s2 where s1.cellserver=s2.cellserver and s1.sessionid=s2.sessionid);";
		$this->application->database->execute_query( $sql, $values );
		foreach ( $this->application->database->rows as $row ) {
			queue_action( $this->application, 'ENDOFSESSIONDEVICES', $row[ 'sessionid' ] );
			$wd++;
		}
		$sql = "select s1.sessionid from _sessions s1 where s1.cellserver='%cellserver' " .
			 "and s1.starttime>date_sub(now(),interval %last day) " .
			 "and status not regexp '%skip' and s1.objects>0 " .
			 "and not exists (select id from dataprotector_session_objects s2 where s1.cellserver=s2.cellserver and s1.sessionid=s2.sessionid);";
		$this->application->database->execute_query( $sql, $values );
		foreach ( $this->application->database->rows as $row ) {
			queue_action( $this->application, 'ENDOFSESSIONOBJECTS', $row[ 'sessionid' ] );
			$wo++;
		}
		$sql = "select s1.sessionid from _sessions s1 where s1.cellserver='%cellserver' " .
			 "and s1.starttime>date_sub(now(),interval %last day) " . 
			 "and status not regexp '%skip' and s1.media>0 " .
			 "and not exists (select id from dataprotector_session_media s2 where s1.cellserver=s2.cellserver and s1.sessionid=s2.sessionid);";
		$this->application->database->execute_query( $sql, $values );
		foreach ( $this->application->database->rows as $row ) {
			queue_action( $this->application, 'ENDOFSESSIONMEDIA', $row[ 'sessionid' ] );
			$wm++;
		}
		$message = sprintf( ROUTINES_CHECK_BACKUPS_LOG, $m, $n, $d, $wd, $wo, $wm );
		( $m + $n + $d + $wd +$wo + $wm > 0 ) && $this->application->log_action( $message );
		return true;
	}

	function locked_objects( $action ) {
		if ( $action ) {
			$name = 'OMNIDBUTIL-LOCKED_DEVS';
			$command = sprintf( ROUTINES_OMNIDBUTIL_SHOW_LOCKED_DEVS_CMD, $this->application->config[ 'OMNI_SBIN' ] );
			$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
			$items = array( 'NAME' => 'ROUTINE', 'DATA' => $this->name );
			if ( !file_exists( $file ) ) {
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
				return false;
			}
			$worker = $this->application->workers->decode( file_get_contents( $file ) );
			$lines = $worker->output;
			unset( $worker );
			unlink( $file );
		} else {
			$lines = $this->application->cache_command(
				sprintf( ROUTINES_OMNIDBUTIL_SHOW_LOCKED_DEVS_CMD, $this->application->config[ 'OMNI_SBIN' ] ), ROUTINES_OMNIDBUTIL_TIMEOUT );
		}
		array_shift( $lines );
		array_shift( $lines );
		array_push( $lines, '' );
		$u = $i = 0;
		$location = $label = '';
		foreach ( $lines as $line ) {
			$key = strtoupper( trim( substr( $line, 0, strpos( $line, ':' ) ) ) );
			$value = trim( substr( strstr( $line, ':' ), 1 ) );
			switch ( $key ) {
				case 'TYPE':
					$type = $value;
					break;
				case 'NAME/ID':
					$name = $value;
					break;
				case 'PID':
					$pid = $value;
					break;
				case 'HOST':
					$host = $value;
					break;
				case 'LOCATION':
					$location = $value;
					break;
				case 'LABEL':
					$label = $value;
					break;
				case '':
					if ( !isset( $name ) or !isset( $host ) ) break;
					if ( $host != $this->application->cellserver ) break;
					$sql = "insert into dataprotector_locked_objects (type,name,pid,host,location,label,updated_on) " .
						 "values ('%type', '%name', '%pid', '%host', '%location', '%label', '%updated_on') on duplicate key update pid='%pid', location='%location', label='%label', updated_on='%updated_on';";
					$values = array( 
						'type' => $type, 
						'name' => $name, 
						'pid' => $pid, 
						'host' => $host, 
						'location' => $location, 
						'label' => $label, 
						'updated_on' => $this->application->start_time );
					$result = $this->application->database->execute_query( $sql, $values );
					( $result == 1 ) && $i++;
					( $result == 2 ) && $u++;
					$location = $label = '';
					break;
				default:
					$this->application->log_error( sprintf( ROUTINES_ERROR_LOCKED_OBJECTS_UNKNOWN_ITEM, $key, $value ) );
			}
		}
		$sql = "delete from dataprotector_locked_objects where host='%host' and updated_on<'%updated_on';";
		$values = array( 
			'host' => $this->application->cellserver, 
			'updated_on' => $this->application->start_time );
		$d = $this->application->database->execute_query( $sql, $values );
		$message = sprintf( ROUTINES_LOCKED_OBJECTS_LOG, $i, $u, $d );
		( $i + $u + $d > 0 ) && $this->application->log_action( $message );
		return true;
	}

	function omnistat( $action ) {
		if ( $action ) {
			$name = 'OMNISTAT';
			$command = sprintf( ROUTINES_OMNISTAT_DETAIL_CMD, $this->application->config[ 'OMNI_BIN' ] );
			$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
			$items = array( 'NAME' => 'ROUTINE', 'DATA' => $this->name );
			if ( !file_exists( $file ) ) {
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
				return false;
			}
			$worker = $this->application->workers->decode( file_get_contents( $file ) );
			$lines = $worker->output;
			unset( $worker );
			unlink( $file );
		} else {
			$lines = $this->application->cache_command( 
				sprintf( ROUTINES_OMNISTAT_DETAIL_CMD, $this->application->config[ 'OMNI_BIN' ] ), ROUTINES_OMNISTAT_TIMEOUT );
		}
		array_push( $lines, '' );
		$u = $i = 0;
		foreach ( $lines as $line ) {
			$key = strtoupper( trim( substr( $line, 0, strpos( $line, ':' ) ) ) );
			$value = trim( substr( strstr( $line, ':' ), 1 ) );
			switch ( $key ) {
				case 'SESSIONID':
					$sessionid = $value;
					break;
				case 'SESSION TYPE':
					$type = $value;
					break;
				case 'SESSION STATUS':
					$status = $value;
					break;
				case 'USER.GROUP@HOST':
					$sessionowner = $value;
					break;
				case 'SESSION STARTED':
					$starttime = $value;
					break;
				case 'BACKUP SPECIFICATION':
					$specification = $value;
					break;
				case '':
					if ( !isset( $sessionid ) ) break;
					$sql = "insert into dataprotector_omnistat " .
						 "(cellserver,sessionid,type,status,sessionowner,starttime,specification,updated_on) " .
						 "values ('%cellserver','%sessionid','%type','%status'," .
						 "'%sessionowner','%starttime','%specification','%updated_on') on duplicate key " . 
						 "update status='%status', updated_on='%updated_on';";
					$values = array( 
						'cellserver' => $this->application->cellserver, 
						'sessionid' => $sessionid, 
						'type' => $type, 
						'status' => $status, 
						'sessionowner' => $sessionowner, 
						'starttime' => $this->application->parse_date( $starttime ), 
						'specification' => $specification, 
						'updated_on' => $this->application->start_time );
					$result = $this->application->database->execute_query( $sql, $values );
					( $result == 1 ) && $i++;
					( $result == 2 ) && $u++;
					if( ( $type != 'Media' ) and 
						( empty( $config[ ROUTINES_OMNISTAT_DETAILS ] ) or stristr( $config[ ROUTINES_OMNISTAT_DETAILS ], $type ) ) ) 
					try {
						$this->omnistat_details( $sessionid, $action );
					}
					catch ( exception $e ) {
						$this->application->log_warning( sprintf( ROUTINES_ERROR_OMNISTAT_DETAILS, $sessionid, $e->getmessage( ) ) );
					}
					$values = array( 
						'cellserver' => $this->application->cellserver, 
						'sessionid' => $sessionid, 
						'skip_devices' => ROUTINES_OMNISTAT_DEVICES_ACTIVE, 
						'skip_objects' => ROUTINES_OMNISTAT_OBJECTS_ACTIVE );
					$sql = "select count(id) as devices,sum(status regexp '%skip_devices') as active_devices," .
						 "sum(ifnull(done,0)) as done from dataprotector_omnistat_devices where cellserver='%cellserver' and sessionid='%sessionid';";
					$this->application->database->execute_query( $sql, $values );
					list ( $devices, $active_devices, $done ) = $this->application->database->rows[ 0 ];
					$sql = "select count(id) as objects,sum(status regexp '%skip_objects') as active_objects," .
						 "sum(ifnull(total_files,0)) as total_files,sum(ifnull(processed_files,0)) as processed_files," .
						 "sum(ifnull(total_size,0)) as total_size,sum(ifnull(processed_size,0)) as processed_size from dataprotector_omnistat_objects where cellserver='%cellserver' and sessionid='%sessionid';";
					$this->application->database->execute_query( $sql, $values );
					list ( $objects, $active_objects, $total_files, $processed_files, $total_size, $processed_size ) = $this->application->database->rows[ 0 ];
					$sql = "update dataprotector_omnistat set devices=nullif('%devices','')," .
						"ad_delta=ifnull(nullif('%active_devices',''),0)-ifnull(active_devices,0)," .
						"active_devices=nullif('%active_devices',''),done_delta=ifnull(nullif('%done',''),0)-ifnull(done,0)," .
						"done=nullif('%done',''),objects=nullif('%objects','')," .
						"ao_delta=ifnull(nullif('%active_objects',''),0)-ifnull(active_objects,0)," .
						"active_objects=nullif('%active_objects',''),total_files=nullif('%total_files','')," .
						"pf_delta=ifnull(nullif('%processed_files',''),0)-ifnull(processed_files,0)," .
						"processed_files=nullif('%processed_files',''),total_size=nullif('%total_size','')," .
						"ps_delta=ifnull(nullif('%processed_size',''),0)-ifnull(processed_size,0),processed_size=nullif('%processed_size','')," .
						"updated_on='%updated_on' where cellserver='%cellserver' and sessionid='%sessionid';";
					$values = array( 
						'devices' => $devices, 
						'active_devices' => $active_devices, 
						'done' => $done, 
						'objects' => $objects, 
						'active_objects' => $active_objects, 
						'total_files' => $total_files, 
						'processed_files' => $processed_files, 
						'total_size' => $total_size, 
						'processed_size' => $processed_size, 
						'cellserver' => $this->application->cellserver, 
						'sessionid' => $sessionid,
						'updated_on' => $this->application->start_time );
					$this->application->database->execute_query( $sql, $values );
					break;
				default:
					$this->application->log_error( sprintf( ROUTINES_ERROR_OMNISTAT_UNKNOWN_ITEM, $key, $value ) );
				}
		}
		$sql = "delete from dataprotector_omnistat where cellserver='%cellserver' and updated_on<'%updated_on';";
		$values = array( 
			'cellserver' => $this->application->cellserver, 
			'updated_on' => $this->application->start_time );
		$d = $this->application->database->execute_query( $sql, $values );
		$message = sprintf( ROUTINES_OMNISTAT_LOG, $i, $u, $d );
		( $i + $u + $d > 0 ) && $this->application->log_action( $message );
		return true;
	}

	function omnistat_details( $sessionid, $action ) {
		if ( $action ) {
			$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'OMNISTAT-%s', $sessionid ) );
			$command = sprintf( ROUTINES_OMNISTAT_SESSION_DETAIL_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid );
			$file = $this->application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
			$items = array( 'NAME' => 'ROUTINE', 'DATA' => sprintf( 'OMNISTAT:%s', $sessionid ) );
			if ( !file_exists( $file ) ) {
				$this->application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true ) &&
				queue_action( $this->application, $items[ 'NAME' ], $items[ 'DATA' ] );
				return false;
			}
			$worker = $this->application->workers->decode( file_get_contents( $file ) );
			$lines = $worker->output;
			empty( $lines[ 0 ] ) && array_shift( $lines );
			unset( $worker );
			unlink( $file );
		} else {
			$lines = $this->application->cache_command( 
				sprintf( ROUTINES_OMNISTAT_SESSION_DETAIL_CMD, $this->application->config[ 'OMNI_BIN' ], $sessionid ), ROUTINES_OMNISTAT_TIMEOUT );
		}
		array_push( $lines, '' );
		if ( strpos( $lines[ 0 ], 'not running' ) ) return true;
		if ( strpos( $lines[ 0 ], 'name' ) === false ) {
			if ( implode( '', $lines ) == '' ) throw new exception( ROUTINES_ERROR_OMNISTAT_DETAILS_EMPTY );
			throw new exception( sprintf( ROUTINES_ERROR_OMNISTAT_DETAILS_INVALID, implode( '', $lines ) ) );
		}
		$total_files = $processed_files = $total_size = '';
		foreach ( $lines as $line ) {
			$key = strtoupper( trim( substr( $line, 0, strpos( $line, ':' ) ) ) );
			$value = trim( substr( strstr( $line, ':' ), 1 ) );
			switch ( $key ) {
				case 'DEVICE NAME':
					$devicename = $value;
					unset( $objectname );
					break;
				case 'HOST':
					$host = $value;
					break;
				case 'STARTED':
					$started = $value;
					break;
				case 'FINISHED':
					$finished = $value;
					break;
				case 'DONE':
					list ( $done ) = sscanf( $value, "%d" );
					break;
				case 'PHYSICAL DEVICE':
					$physical_device = $value;
					break;
				case 'STATUS':
					$status = $value;
					break;
				case 'OBJECT NAME':
					$objectname = $value;
					unset( $devicename );
					break;
				case 'SESSIONID':
					$restore_sessionid = $value;
					break;
				case 'OBJECT TYPE':
				case 'TYPE':
					$type = $value;
					break;
				case 'BACKUP STARTED':
					$backup_started = $value;
					break;
				case 'RESTORE STARTED':
				case 'STARTED':
					$started = $value;
					break;
				case 'FINISHED':
					$finished = $value;
					break;
				case 'LEVEL':
				case 'RUNLEVEL':
					$runlevel = $value;
					break;
				case 'WARNINGS':
					$warnings = $value;
					break;
				case 'ERRORS':
					$errors = $value;
					break;
				case 'TOTAL FILES':
					$total_files = $value;
					break;
				case 'PROCESSED FILES':
					$processed_files = $value;
					break;
				case 'TOTAL SIZE':
					list ( $total_size ) = sscanf( $value, "%d" );
					break;
				case 'PROCESSED SIZE':
					list ( $processed_size ) = sscanf( $value, "%d" );
					break;
				case 'DEVICE':
					$device = $value;
					break;
				case '':
					$sql = '';
					if ( isset( $devicename ) ) {
						$sql = "insert into dataprotector_omnistat_devices (cellserver,sessionid,name,host,started,finished,done," .
							 "physical_device,status,updated_on) values ('%cellserver','%sessionid','%name','%host'," .
							 "nullif('%started',''),nullif('%finished',''),nullif('%done','')," .
							 "nullif('%physical_device',''),nullif('%status',''),'%updated_on') on duplicate key " .
							 "update finished=nullif('%finished',''),done=nullif('%done',''),status=nullif('%status',''),updated_on='%updated_on';";
						$values = array( 
							'cellserver' => $this->application->cellserver, 
							'sessionid' => $sessionid, 
							'name' => $devicename, 
							'host' => $host, 
							'started' => $this->application->parse_date( $started ), 
							'finished' => $this->application->parse_date( $finished ), 
							'done' => $done, 
							'physical_device' => $physical_device, 
							'status' => $status, 
							'updated_on' => $this->application->start_time );
					}
					if ( isset( $objectname ) ) {
						$sql = "insert into dataprotector_omnistat_objects (cellserver,sessionid,name,type,started,finished,runlevel," .
							 "warnings,errors,total_files,processed_files,total_size,processed_size,device,status,updated_on) " .
							 "values ('%cellserver','%sessionid','%name','%type'," .
							 "nullif('%started',''),nullif('%finished',''),nullif('%runlevel','')," .
							 "nullif('%warnings',''),nullif('%errors',''),nullif('%total_files',''),nullif('%processed_files','')," .
							 "nullif('%total_size',''),nullif('%processed_size','')," .
							 "nullif('%device',''),nullif('%status',''),'%updated_on') on duplicate key " .
							 "update finished=nullif('%finished',''),warnings=nullif('%warnings','')," .
							 "errors=nullif('%errors',''),total_files=nullif('%total_files','')," .
							 "processed_files=nullif('%processed_files','')," .
							 "total_size=nullif('%total_size',''),processed_size=nullif('%processed_size','')," .
							 "device=nullif('%device',''),status=nullif('%status',''),updated_on='%updated_on';";
						$values = array( 
							'cellserver' => $this->application->cellserver, 
							'sessionid' => $sessionid, 
							'name' => substr( $this->application->database->escape_string( $objectname ), 0, 200 ), 
							'type' => $type, 
							'started' => $this->application->parse_date( $started ), 
							'finished' => $this->application->parse_date( $finished ), 
							'runlevel' => $runlevel, 
							'warnings' => $warnings, 
							'errors' => $errors, 
							'total_files' => $total_files, 
							'processed_files' => $processed_files, 
							'total_size' => $total_size, 
							'processed_size' => $processed_size, 
							'device' => $device, 
							'status' => $status, 
							'updated_on' => $this->application->start_time );
					}
					$sql != '' && $this->application->database->execute_query( $sql, $values );
					break;
				default:
					$this->application->log_error( sprintf( ROUTINES_ERROR_OMNISTAT_DETAILS_UNKNOWN_ITEM, $sessionid, $key, $value ) );
			}
		}
		return true;
	}
}

function execute_routines( $application ) {
	foreach ( explode( ',', ROUTINES ) as $routine ) {
		list ( $start, $interval ) = array_map( 'trim', 
			explode( '+', sprintf( '%s+1440', $application->config[ sprintf( 'ROUTINE_%s', $routine ) ] ) ) );
		( $start == '' ) && $start = '0:00';
		$passed = round( ( strtotime( $application->start_time ) - strtotime( sprintf( '%s %s', date( 'Y-m-d' ), $start ) ) ) / 60 );
		( ( $passed % $interval ) == 0 ) && queue_action( $application, 'ROUTINE', $routine, 0, $interval );
	}
}
