<?php

/*
 * MARS 3.0 POOLS AND MEDIA PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'MEDIA_OMNIMM_TIMEOUT', 30 );
define( 'MEDIA_ERROR_UNKNOWN_OMNIMM_LIST_POOLS_ITEM', 'Error: Unknown "omnimm list_pools" item "%s=%s".' );
define( 'MEDIA_POOLS_LOG', 'POOLS: %s inserted, %s updated, %s removed.' );
define( 'MEDIA_ERROR_UNKNOWN_OMNIMM_LIST_POOL_ITEM', 'Error: Unknown "omnimm list_pool %s" item "%s=%s".' );
define( 'MEDIA_ERROR_READING', 'Error reading media for pool "%s": %s' );
define( 'MEDIA_LOG', 'MEDIA: %s inserted, %s updated, %s removed.' );

if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) {
	define( 'MEDIA_OMNIMM_LIST_POOLS_CMD', '"%s\omnimm.exe" -list_pools -detail' );
	define( 'MEDIA_OMNIMM_LIST_POOL_CMD', '"%s\omnimm.exe" -list_pool "%s" -detail' );
} else {
	define( 'MEDIA_OMNIMM_LIST_POOLS_CMD', '"%s/omnimm" -list_pools -detail' );
	define( 'MEDIA_OMNIMM_LIST_POOL_CMD', '"%s/omnimm" -list_pool "%s" -detail' );
}
	
function read_media( $application, $action = true ) {
	if ( $action ) {
		$name = 'OMNIMM-POOLS';
		$file = $application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
		if ( !file_exists( $file ) ) {
			$command = sprintf( MEDIA_OMNIMM_LIST_POOLS_CMD, $application->config[ 'OMNI_BIN' ] );
			$items = array( 'NAME' => 'ROUTINE', 'DATA' => 'MEDIA' );
			$application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
			return false;
		}
		$worker = $application->workers->decode( file_get_contents( $file ) );
		$lines = $worker->output;
		unset( $worker );
		unlink( $file );
	} else {
		$lines = $application->cache_command( sprintf( MEDIA_OMNIMM_LIST_POOLS_CMD, $application->config[ 'OMNI_BIN' ] ), MEDIA_OMNIMM_TIMEOUT );
	}
	$application->database->start_transaction();
	array_push( $lines, '' );
	$i = $u = 0;
	foreach ( $lines as $line ) {
		$key = strtoupper( trim( substr( $line, 0, strpos( $line, ':' ) ) ) );
		$value = trim( substr( strstr( $line, ':' ), 1 ) );
		switch ( $key ) {
			case 'POOL NAME':
				$name = $value;
				unset( $description );
				break;
			case 'POOL DESCRIPTION':
				$description = $value;
				break;
			case 'MEDIA TYPE':
				$type = $value;
				break;
			case 'POLICY':
				$policy = $value;
				break;
			case 'BLOCKS USED [MB]':
				$mbused = $value;
				break;
			case 'BLOCKS TOTAL [MB]':
				$mbtotal = $value;
				break;
			case 'ALTOGETHER MEDIA':
				$media = $value;
				break;
			case 'POOR MEDIA':
				$poor = $value;
				break;
			case 'FAIR MEDIA':
				$fair = $value;
				break;
			case 'MEDIUM AGE LIMIT':
				$agelimit = $value;
				break;
			case 'MAXIMUM OVERWRITES':
				$maxoverwrites = $value;
				break;
			case 'MAGAZINE SUPPORT':
				$magazinesupport = $value;
				break;
			case 'FREE POOL SUPPORT':
				$freepoolsupport = $value;
				break;
			case '':
				$sql = '';
				if ( isset( $name ) and isset( $description ) ) {
					$sql = "insert into dataprotector_pools (name,description,type,policy,mbused,mbtotal," .
						 "media,poor,fair,agelimit,maxoverwrites,magazinesupport,freepoolsupport,updated_by,updated_on) " .
						 "values ('%name','%description','%type','%policy',%mbused,%mbtotal," .
						 "%media,%poor,%fair,'%agelimit',%maxoverwrites,'%magazinesupport','%freepoolsupport','%updated_by','%updated_on') " .
						 "on duplicate key update name='%name',description='%description',type='%type',policy='%policy'," .
						 "mbused=%mbused,mbtotal=%mbtotal,media=%media,poor=%poor,fair=%fair,agelimit='%agelimit',maxoverwrites=%maxoverwrites," .
						 "magazinesupport='%magazinesupport',freepoolsupport='%freepoolsupport',updated_on='%updated_on',valid_until=null;";
					$values = array( 
						'name' => substr( $name, 0, 32 ), 
						'description' => $application->database->escape_string( $description ), 
						'type' => $type, 
						'policy' => $policy, 
						'mbused' => $mbused, 
						'mbtotal' => $mbtotal, 
						'media' => $media, 
						'poor' => $poor, 
						'fair' => $fair, 
						'agelimit' => $agelimit, 
						'maxoverwrites' => $maxoverwrites, 
						'magazinesupport' => $magazinesupport, 
						'freepoolsupport' => $freepoolsupport, 
						'updated_by' => $application->cellserver, 
						'updated_on' => $application->start_time );
					unset( $mediumid );
				}
				if ( $sql == '' ) break;
				$result = $application->database->execute_query( $sql, $values );
				( $result == 1 ) && $i++;
				( $result == 2 ) && $u++;
				if ( $action ) {
					$pool = $name;
					$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'OMNIMM-POOL-%s', $pool ) );
					$file = $application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
					$command = sprintf( MEDIA_OMNIMM_LIST_POOL_CMD, $application->config[ 'OMNI_BIN' ], $pool );
					$items = array( 'NAME' => 'ROUTINE', 'DATA' => 'POOLS' );
					$application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
					$name = $pool;
				}
				break;
			default:
				$application->log_error( sprintf( MEDIA_ERROR_UNKNOWN_OMNIMM_LIST_POOLS_ITEM, $key, $value ) );
		}
	}
	$sql = "update dataprotector_pools set valid_until=updated_on where id in (select id from (" .
		 "select id from dataprotector_pools where updated_by='%updated_by' and updated_on<'%updated_on' " .
		 "and valid_until is null) d order by id);";
	$values = array( 
		'updated_by' => $application->cellserver, 
		'updated_on' => $application->start_time );
	$d = $application->database->execute_query( $sql, $values );
	$message = sprintf( MEDIA_POOLS_LOG, $i, $u, $d );
	$application->log_action( $message );
	if ( $action ) {
		queue_action( $application, 'ROUTINE', 'POOLS' );
	} else {
		read_pools( $application, $action );
	}
	$application->database->commit();
	return true;
}
	
function read_pools( $application, $action = true ) {
	$sql = "select distinct name from dataprotector_pools where valid_until is null order by name;";
	$application->database->execute_query( $sql );
	$pools = $application->database->rows;
	if ( $action ) {
		foreach ( $pools as $pool ) {
			$pool = $pool[ 'name' ];
			$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'OMNIMM-POOL-%s', $pool ) );
			$file = $application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
			if ( !file_exists( $file ) ) {
				$result = false;
				$command = sprintf( MEDIA_OMNIMM_LIST_POOL_CMD, $application->config[ 'OMNI_BIN' ], $pool );
				$items = array( 'NAME' => 'ROUTINE', 'DATA' => 'POOLS' );
				$application->workers->add( array( 'CMD' => $command, 'NAME' => $name, 'ITEMS' => $items ), true );
				return false;
			}
		}
	}
	$application->database->start_transaction();
	$i = $u = 0;
	foreach ( $pools as $pool ) {
		try {
			$pool = $pool[ 'name' ];
			if ( $action ) {
				$name = preg_replace('/[^a-zA-Z0-9-_\.]/','', sprintf( 'OMNIMM-POOL-%s', $pool ) );
				$file = $application->root . DIRECTORY_SEPARATOR . 'tmp'.  DIRECTORY_SEPARATOR . $name . '.tmp';
				$worker = $application->workers->decode( file_get_contents( $file ) );
				$lines = $worker->output;
				unset( $worker );
				unlink( $file );
			} else {
				$lines = $application->cache_command( 
					sprintf( MEDIA_OMNIMM_LIST_POOL_CMD, $application->config[ 'OMNI_BIN' ], $pool ), MEDIA_OMNIMM_TIMEOUT );
			}
			array_push( $lines, '' );
			foreach ( $lines as $line ) {
				$key = strtoupper( trim( substr( $line, 0, strpos( $line, ':' ) ) ) );
				$value = trim( substr( strstr( $line, ':' ), 1 ) );
				switch ( $key ) {
					case 'MEDIUM IDENTIFIER':
						$mediumid = $value;
						unset( $label );
						break;
					case 'MEDIUM LABEL':
						$label = $value;
						break;
					case 'LOCATION':
						$location = $value;
						break;
					case 'MEDIUM OWNER':
						$owner = $value;
						break;
					case 'STATUS':
						$status = $value;
						break;
					case 'BLOCKS USED  [KB]':
						$kbused = $value;
						break;
					case 'BLOCKS TOTAL [KB]':
						$kbtotal = $value;
						break;
					case 'USABLE SPACE [KB]':
						$kbusable = $value;
						break;
					case 'NUMBER OF WRITES':
						$writes = $value;
						break;
					case 'NUMBER OF OVERWRITES':
						$overwrites = $value;
						break;
					case 'NUMBER OF ERRORS':
						$errors = $value;
						break;
					case 'MEDIUM INITIALIZED':
						$initialized = $value;
						break;
					case 'LAST WRITE':
						$lastwrite = $value;
						break;
					case 'LAST ACCESS':
						$lastaccess = $value;
						break;
					case 'LAST OVERWRITE':
						$lastoverwrite = $value;
						break;
					case 'PROTECTED':
						$protected = $value;
						break;
					case 'WRITE-PROTECTED':
						$wp = $value;
						break;
					case '':
						$sql = '';
						if ( isset( $mediumid ) and isset( $label ) ) {
							$sql = "insert into dataprotector_media (pool,mediumid,label,location,owner,status,kbused,kbtotal,kbusable," .
								 "writes,overwrites,errors,initialized,lastwrite,lastaccess,lastoverwrite," .
								 "protected,wp,updated_by,updated_on) values ('%pool','%mediumid','%label','%location','%owner'," .
								 "'%status',%kbused,%kbtotal,%kbusable,%writes,%overwrites,%errors," .
								 "'%initialized',nullif('%lastwrite',''),nullif('%lastaccess','')," .
								 "nullif('%lastoverwrite',''),nullif('%protected',''),'%wp','%updated_by','%updated_on') " .
								 "on duplicate key update pool='%pool',label='%label',location='%location'," .
								 "owner='%owner',status='%status',kbused=%kbused,kbtotal=%kbtotal," .
								 "kbusable=%kbusable,writes=%writes,overwrites=%overwrites,errors=%errors," .
								 "initialized='%initialized',lastwrite=nullif('%lastwrite','')," .
								 "lastaccess=nullif('%lastaccess',''),lastoverwrite=nullif('%lastoverwrite','')," .
								 "protected=nullif('%protected',''),wp='%wp',updated_on='%updated_on',valid_until=null;";
							$values = array( 
								'pool' => substr( $pool, 0, 32 ), 
								'mediumid' => $mediumid, 
								'label' => $label, 
								'location' => $location, 
								'owner' => $owner, 
								'status' => $status, 
								'kbused' => $kbused, 
								'kbtotal' => $kbtotal, 
								'kbusable' => $kbusable, 
								'writes' => $writes, 
								'overwrites' => $overwrites, 
								'errors' => $errors, 
								'initialized' => $application->parse_date( $initialized ), 
								'lastwrite' => $application->parse_date( $lastwrite ), 
								'lastaccess' => $application->parse_date( $lastaccess ), 
								'lastoverwrite' => $application->parse_date( $lastoverwrite ), 
								'protected' => $application->parse_date( $protected ), 
								'wp' => $wp, 
								'updated_by' => $application->cellserver, 
								'updated_on' => $application->start_time );
							unset( $mediumid );
						}
						if ( $sql == '' ) break;
						$result = $application->database->execute_query( $sql, $values );
						( $result == 1 ) && $i++;
						( $result == 2 ) && $u++;
						break;
					default:
						$application->log_error( sprintf( MEDIA_ERROR_UNKNOWN_OMNIMM_LIST_POOL_ITEM, $pool, $key, $value ) );
				}
			}
		}
		catch ( exception $e ) {
			$application->log_error( sprintf( MEDIA_ERROR_READING, $pool, $e->getmessage( ) ) );
		}
	}
	$sql = "update dataprotector_media set valid_until=updated_on where id in (select id from (" .
		 "select id from dataprotector_media where updated_by='%updated_by' and updated_on<'%updated_on' " .
		 "and valid_until is null) d order by id);";
	$values = array( 
		'updated_by' => $application->cellserver, 
		'updated_on' => $application->start_time );
	$d = $application->database->execute_query( $sql, $values );
	$sql = "update dataprotector_pools p left join( " .
		 "select updated_by,pool,round(sum(kbused)/1024,1) as mbused,round(sum(kbtotal)/1024,1) as mbtotal " .
		 "from dataprotector_media where valid_until is null group by updated_by,pool) m on (m.updated_by=p.updated_by and m.pool=p.name) " .
		 "set p.mbused=ifnull(m.mbused,0),p.mbtotal=ifnull(m.mbtotal,0) where p.valid_until is null;";
	$application->database->execute_query( $sql, $values );
	$application->database->commit();
	$message = sprintf( MEDIA_LOG, $i, $u, $d );
	$application->log_action( $message );
	return true;
}