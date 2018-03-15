<?php

/*
 * MARS 3.0 NOTIFICATIONS PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */
define( 'NOTIFICATIONS', 
	// DP7 (FILELIBRARYDISKUSAGE, IDBFCNEEDED, IDBSPACELOW, UXMEDIAAGENTWARNING)
	'ALARM,CSASTARTSESSIONFAILED,DEVICEERROR,ENDOFCOPY,ENDOFSESSION,HEALTHCHECKFAILED,DBCORRUPTED,DBPURGENEEDED,DBSPACELOW' .
		 'DBTABLESPACELOW,LICENSEWILLEXPIRE,LICENSEWARNING,MAILSLOTSFULL,MOUNTREQUEST,NOTENOUGHFREEMEDIA,SESSIONERROR' .
		 'STARTOFCOPY,STARTOFSESSION,UNEXPECTEDEVENTS,USERCHECKFAILED' . 
	// DP8
		',DBBACKUPNEEDED' );
define( 'NOTIFICATIONS_QUEUE', 'queue' );
define( 'NOTIFICATIONS_HISTORY', 'history' );
define( 'NOTIFICATIONS_TMP', 'tmp' );
define( 'NOTIFICATIONS_INVALID', 'Invalid notification "%s".' );
define( 'NOTIFICATIONS_UNHANDLED', 'Unhandled notification (see "%s").' );
define( 'NOTIFICATIONS_ERROR', 'Error: %s: %s' );
define( 'NOTIFICATIONS_ALARM_PREFIX', 'AM1' );
define( 'NOTIFICATIONS_ALARM', 'Alarm "%s".' );
define( 'NOTIFICATIONS_CSASTARTSESSIONFAILED_PREFIX', 'SS1' );
define( 'NOTIFICATIONS_CSASTARTSESSIONFAILED', 'CSA Start of session "%s" failed.' );
define( 'NOTIFICATIONS_DEVICEERROR_PREFIX', 'DE1' );
define( 'NOTIFICATIONS_DEVICEERROR', 'Device error on device "%s".' );
define( 'NOTIFICATIONS_ENDOFCOPY', 'Copy session "%s" ended.' );
define( 'NOTIFICATIONS_ENDOFSESSION', 'Session "%s" ended.' );
define( 'NOTIFICATIONS_HEALTHCHECKFAILED_PREFIX', 'HC1' );
define( 'NOTIFICATIONS_HEALTHCHECKFAILED', 'Healthcheck has failed with message "%s".' );
define( 'NOTIFICATIONS_DBCORRUPTED_PREFIX', 'DC1' );
define( 'NOTIFICATIONS_DBCORRUPTED', 'Error message "%s": Database part "%s" is corrupted.' );
define( 'NOTIFICATIONS_DBSPACELOW_PREFIX', 'DS1' );
define( 'NOTIFICATIONS_DBSPACELOW', 'Free space available for Internal Database is low.' );
define( 'NOTIFICATIONS_DBPURGENEEDED_PREFIX', 'DP1' );
define( 'NOTIFICATIONS_DBPURGENEEDED', 'Filename purge should be run for the IDB.' );
define( 'NOTIFICATIONS_DBTABLESPACELOW_PREFIX', 'TS1' );
define( 'NOTIFICATIONS_DBTABLESPACELOW', 'IDB tablespace "%s" reached %s percent.' );
define( 'NOTIFICATIONS_LICENSEWILLEXPIRE_PREFIX', 'LE1' );
define( 'NOTIFICATIONS_LICENSEWILLEXPIRE', 'License will expire  in %s days.' );
define( 'NOTIFICATIONS_LICENSEWARNING_PREFIX', 'LW1' );
define( 'NOTIFICATIONS_LICENSEWARNING', '%s license(s) need to be purchased for category "%s".' );
define( 'NOTIFICATIONS_MAILSLOTSFULL_PREFIX', 'MS1' );
define( 'NOTIFICATIONS_MAILSLOTSFULL', 'Mail slots of device "%s" are full.' );
define( 'NOTIFICATIONS_MOUNTREQUEST_PREFIX', 'MR1' );
define( 'NOTIFICATIONS_MOUNTREQUEST', 'Mount Request from session "%s" on device "%s" for pool "%s".' );
define( 'NOTIFICATIONS_NOTENOUGHFREEMEDIA_PREFIX', 'FM1' );
define( 'NOTIFICATIONS_NOTENOUGHFREEMEDIA', 'Free media in pool "%s" reached %s' );
define( 'NOTIFICATIONS_SESSIONERROR_PREFIX', 'SE1' );
define( 'NOTIFICATIONS_SESSIONERROR', 'Session "%s" ("%s") has %s errors' );
define( 'NOTIFICATIONS_STARTOFCOPY', 'Copy session "%s" started.' );
define( 'NOTIFICATIONS_STARTOFSESSION_PARSING_ERROR', 'Parsing error #%s in "%s" of "%s" ("%s").' );
define( 'NOTIFICATIONS_STARTOFSESSION', 'Session "%s" started.' );
define( 'NOTIFICATIONS_STARTOFSESSION_SKIPPED', 'Processing of session "%s" start skipped.' );
define( 'NOTIFICATIONS_UNEXPECTEDEVENTS_PREFIX', 'UE1' );
define( 'NOTIFICATIONS_UNEXPECTEDEVENTS', 'Error: Unexpected events occured (%s).' );
define( 'NOTIFICATIONS_USERCHECKFAILED_PREFIX', 'UC1' );
define( 'NOTIFICATIONS_USERCHECKFAILED', 'Error %s: "%s".' );
define( 'NOTIFICATIONS_DBBACKUPNEEDED_PREFIX', 'DB1' );
define( 'NOTIFICATIONS_DBBACKUPNEEDED', 'Error: Database backup is needed. Last backup time:%s Number of incremental backups: %s' );

class mars_notification {
	var $application;
	var $file;
	var $name;
	var $items;

	function mars_notification( $application, $file ) {
		$this->application = $application;
		$this->file = $file;
		$this->items = array();
		$lines = defined( 'INI_SCANNER_RAW' ) ? parse_ini_file( $this->file, FALSE, INI_SCANNER_RAW ) : parse_ini_file( $this->file, FALSE ) ;
		foreach ( $lines as $key => $value ) {
			$this->items[ strtoupper( $key ) ] = trim( str_replace( "'", '', $value ) );
		}
		if ( !isset( $this->items[ 'NOTIFICATION' ] ) ) {
			$this->items[ 'NOTIFICATION' ] = '';
			$this->application->log_error( sprintf( NOTIFICATIONS_INVALID, basename( $this->file ) ) );
		}
		$this->name = strtoupper( $this->items[ 'NOTIFICATION' ] );
	}

	function execute() {
		try {
			$result = false;
			switch ( $this->name ) {
				case '':
					$result = true;
					break;
				// DP07
				case 'ALARM':
					$result = $this->alarm( );
					break;
				case 'CSASTARTSESSIONFAILED':
					$result = $this->csastartsessionfailed( );
					break;
				case 'DEVICEERROR':
					$result = $this->deviceerror( );
					break;
				case 'ENDOFCOPY':
					$result = $this->endofcopy( );
					break;
				case 'ENDOFSESSION':
					$result = $this->endofsession( );
					break;
				case 'HEALTHCHECKFAILED':
					$result = $this->healthcheckfailed( );
					break;
				case 'DBCORRUPTED':
					$result = $this->dbcorrupted( );
					break;
				case 'DBSPACELOW':
					$result = $this->dbspacelow( );
					break;
					case 'DBPURGENEEDED':
					$result = $this->dbpurgeneeded( );
					break;
				case 'DBTABLESPACELOW':
					$result = $this->dbtablespacelow( );
					break;
				case 'LICENSEWARNING':
					$result = $this->licensewarning( );
					break;
				case 'LICENSEWILLEXPIRE':
					$result = $this->licensewillexpire( );
					break;
				case 'MAILSLOTSFULL':
					$result = $this->mailslotsfull( );
					break;
				case 'MOUNTREQUEST':
					$result = $this->mountrequest( );
					break;
				case 'NOTENOUGHFREEMEDIA':
					$result = $this->notenoughfreemedia( );
					break;
				case 'SESSIONERROR':
					$result = $this->sessionerror( );
					break;
				case 'STARTOFCOPY':
					$result = $this->startofcopy( );
					break;
				case 'STARTOFSESSION':
					$result = $this->startofsession( );
					break;
				case 'UNEXPECTEDEVENTS':
					$result = $this->unexpectedevents( );
					break;
				case 'USERCHECKFAILED':
					$result = $this->usercheckfailed( );
					break;
				// DP08
				case 'DBBACKUPNEEDED':
					$result = $this->dbbackupneeded( );
					break;
				default:
					throw new exception( sprintf( NOTIFICATIONS_UNHANDLED, basename( $this->file ) ) );
					break;
			}
		}
		catch ( exception $e ) {
			$this->application->log_error( sprintf( NOTIFICATIONS_ERROR, $this->name, $e->getmessage( ) ) );
		}
		return $result;
	}

	function alarm() {
		if ( isset( $this->application->config[ 'ALARM_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_ALARM, $this->items[ 'ALARMMSG' ] );
			$this->application->create_ticket( $this->application->config[ 'ALARM_PRIORITY' ], NOTIFICATIONS_ALARM_PREFIX, $message );
		}
		return true;
	}

	function csastartsessionfailed() {
		if ( isset( $this->application->config[ 'CSASSFAILED_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_CSASTARTSESSIONFAILED, $this->items[ 'DATALIST' ] );
			$this->application->create_ticket( $this->application->config[ 'CSASSFAILED_PRIORITY' ], 
				NOTIFICATIONS_CSASTARTSESSIONFAILED_PREFIX, $message );
		}
		return true;
	}

	function deviceerror() {
		if ( isset( $this->application->config[ 'DEVICEERROR_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_DEVICEERROR, $this->items[ 'DEVICENAME' ] );
			$this->application->create_ticket( $this->application->config[ 'DEVICEERROR_PRIORITY' ], NOTIFICATIONS_DEVICEERROR_PREFIX, 
				$message );
		}
		return true;
	}

	function endofcopy() {
		$message = sprintf( NOTIFICATIONS_ENDOFCOPY, $this->items[ 'SESSIONID' ] );
		return true;
	}

	function endofsession() {
		if ( isset( $this->items[ 'SESSIONTYPE' ] ) and ( $this->items[ 'SESSIONTYPE' ] == 16 ) ) {return $this->endofcopy( );}
		$message = sprintf( NOTIFICATIONS_ENDOFSESSION, $this->items[ 'SESSIONID' ] );
		queue_action( $this->application, $this->items[ 'NOTIFICATION' ], $this->items[ 'SESSIONID' ] );
		return true;
	}

	function healthcheckfailed() {
		if ( isset( $this->application->config[ 'HEALTHCHECKFAILED_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_HEALTHCHECKFAILED, $this->items[ 'HEALTHCHECKMSG' ] );
			$this->application->create_ticket( $this->application->config[ 'HEALTHCHECKFAILED_PRIORITY' ], 
				NOTIFICATIONS_HEALTHCHECKFAILED_PREFIX, $message );
		}
		return true;
	}

	function dbcorrupted() {
		if ( isset( $this->application->config[ 'DBCORRUPTED_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_DBCORRUPTED, $this->items[ 'DBERRORMESSAGE' ], $this->items[ 'DBPARTCORRUPTED' ] );
			$this->application->create_ticket( $this->application->config[ 'DBCORRUPTED_PRIORITY' ], NOTIFICATIONS_DBCORRUPTED_PREFIX, 
				$message );
		}
		return true;
	}

	function dbspacelow() {
		if ( isset( $this->application->config[ 'DBSPACELOW_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_DBSPACELOW );
			$this->application->create_ticket( $this->application->config[ 'DBSPACELOW_PRIORITY' ], NOTIFICATIONS_DBSPACELOW_PREFIX, 
				$message );
		}
		return true;
	}
	
	function dbpurgeneeded() {
		if ( isset( $this->application->config[ 'DBPURGENEEDED_PRIORITY' ] ) ) {
			$message = NOTIFICATIONS_DBPURGENEEDED;
			$this->application->create_ticket( $this->application->config[ 'DBPURGENEEDED_PRIORITY' ], NOTIFICATIONS_DBPURGENEEDED_PREFIX, 
				$message );
		}
		return true;
	}

	function dbtablespacelow() {
		if ( isset( $this->application->config[ 'TABLESPACELOW_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_DBTABLESPACELOW, $this->items[ 'TBLSPACENAME' ], $this->items[ 'TBLSPACEOCCUPIED' ] );
			$this->application->create_ticket( $this->application->config[ 'TABLESPACELOW_PRIORITY' ], 
				NOTIFICATIONS_DBTABLESPACELOW_PREFIX, $message );
		}
		return true;
	}

	function licensewillexpire() {
		if ( isset( $this->application->config[ 'LICENSEWILLEXPIRE_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_LICENSEWILLEXPIRE, $this->items[ 'LICEXPIREDAYS' ] );
			$this->application->create_ticket( $this->application->config[ 'LICENSEWILLEXPIRE_PRIORITY' ], 
				NOTIFICATIONS_LICENSEWILLEXPIRE_PREFIX, $message );
		}
		return true;
	}

	function licensewarning() {
		if ( isset( $this->application->config[ 'LICENSEWARNING_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_LICENSEWARNING, $this->items[ 'LICQUANTITY' ], $this->items[ 'LICCATEGORY' ] );
			$this->application->create_ticket( $this->application->config[ 'LICENSEWARNING_PRIORITY' ], 
				NOTIFICATIONS_LICENSEWARNING_PREFIX, $message );
		}
		return true;
	}

	function mailslotsfull() {
		if ( isset( $this->application->config[ 'MAILSLOTSFULL_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_MAILSLOTSFULL, $this->items[ 'DEVICENAME' ] );
			$this->application->create_ticket( $this->application->config[ 'MAILSLOTSFULL_PRIORITY' ], NOTIFICATIONS_MAILSLOTSFULL_PREFIX, 
				$message );
		}
		return true;
	}

	function mountrequest() {
		if ( isset( $this->application->config[ 'MOUNTREQUEST_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_MOUNTREQUEST, $this->items[ 'SESSIONID' ], $this->items[ 'DEVICENAME' ], 
				$this->items[ 'MEDIAPOOL' ] );
			$this->application->create_ticket( $this->application->config[ 'MOUNTREQUEST_PRIORITY' ], NOTIFICATIONS_MOUNTREQUEST_PREFIX, 
				$message );
		}
		return true;
	}

	function notenoughfreemedia() {
		if ( ( !$this->application->mmd_local ) ) return true;
		if ( preg_match( sprintf( '/%s/i', $this->application->config[ 'FREEMEDIA_IGNORE_POOL' ] ), $this->items[ 'MEDIAPOOL' ] ) ) return true;
		if ( isset( $this->application->config[ 'FREEMEDIA_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_NOTENOUGHFREEMEDIA, $this->items[ 'MEDIAPOOL' ], $this->items[ 'NUMFREEMEDIA' ] );
			$this->application->create_ticket( $this->application->config[ 'FREEMEDIA_PRIORITY' ], NOTIFICATIONS_NOTENOUGHFREEMEDIA_PREFIX, 
				$message );
		}
		return true;
	}

	function sessionerror() {
		if ( isset( $this->application->config[ 'SESSIONERROR_PRIORITY' ] ) and $this->items[ 'NUMBACKUPERRORS' ] > 0 ) {
			$message = sprintf( NOTIFICATIONS_SESSIONERROR, $this->items[ 'SESSIONID' ], $this->items[ 'DATALIST' ], 
				$this->items[ 'NUMBACKUPERRORS' ] );
			$this->application->create_ticket( $this->application->config[ 'SESSIONERROR_PRIORITY' ], NOTIFICATIONS_SESSIONERROR_PREFIX, 
				$message );
		}
		return true;
	}

	function startofcopy() {
		$message = sprintf( NOTIFICATIONS_STARTOFCOPY, $this->items[ 'SESSIONID' ] );
		return true;
	}

	function startofsession() {
		if ( isset( $this->items[ 'SESSIONTYPE' ] ) and ( $this->items[ 'SESSIONTYPE' ] == 16 ) ) {return $this->startofcopy( );}
		if ( isset( $this->application->config[ 'STARTOFSESSION_SKIP' ] ) ) {
			$message = sprintf( NOTIFICATIONS_STARTOFSESSION_SKIPPED, $this->items[ 'SESSIONID' ] );
			return true;
		}
		if ( isset( $this->items[ 'PARSING_ERROR' ] ) ) {
			$message = sprintf( NOTIFICATIONS_STARTOFSESSION_PARSING_ERROR, $this->items[ 'PARSING_ERROR' ], 
				$this->items[ 'NOTIFICATION' ], $this->items[ 'SESSIONID' ], $this->items[ 'DATALIST' ] );
			throw new exception( $message );
			break;
		}
		$message = sprintf( NOTIFICATIONS_STARTOFSESSION, $this->items[ 'SESSIONID' ] );
		queue_action( $this->application, $this->name, $this->items[ 'SESSIONID' ] );
		return true;
	}

	function unexpectedevents() {
		if ( isset( $this->application->config[ 'UNEXPECTEDEVENTS_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_UNEXPECTEDEVENTS, $this->items[ 'NUMEVENTS' ] );
			$this->application->create_ticket( $this->application->config[ 'UNEXPECTEDEVENTS_PRIORITY' ], 
				NOTIFICATIONS_UNEXPECTEDEVENTS_PREFIX, $message );
		}
		return true;
	}

	function usercheckfailed() {
		if ( isset( $this->application->config[ 'USERCHECKFAILED_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_USERCHECKFAILED, $this->items[ 'COMMANDERCODE' ], $this->items[ 'COMMANDERMSG' ] );
			$this->application->create_ticket( $this->application->config[ 'USERCHECKFAILED_PRIORITY' ], 
				NOTIFICATIONS_USERCHECKFAILED_PREFIX, $message );
		}
		return true;
	}

	function dbbackupneeded() {
		if ( isset( $this->application->config[ 'DBBACKUPNEEDED_PRIORITY' ] ) ) {
			$message = sprintf( NOTIFICATIONS_DBBACKUPNEEDED, $this->items[ 'LASTBCKPTIME' ], $this->items[ 'NUMOFINCRIDBBACKUPS' ] );
			$this->application->create_ticket( $this->application->config[ 'DBBACKUPNEEDED_PRIORITY' ], 
				NOTIFICATIONS_DBBACKUPNEEDED_PREFIX, $message );
		}
		return true;
	}
}

function execute_notifications( $application ) {
	$files = glob( $application->root . DIRECTORY_SEPARATOR . NOTIFICATIONS_QUEUE . DIRECTORY_SEPARATOR . '*' );
	sort( $files );
	$i = 0;
	foreach ( $files as $file ) {
		$notification = new mars_notification( $application, $file );
		if ( $notification->execute( ) ) {
			unlink( $file );
		} else {
			$tmp = str_replace( NOTIFICATIONS_QUEUE, NOTIFICATIONS_TMP, dirname( $file ) )
				. DIRECTORY_SEPARATOR .	substr( basename( $file ), 0, 6 ) . '.' . NOTIFICATIONS_TMP;
			rename( $file, $tmp );
		}
		clearstatcache( );
		$i++;
	}
	return $i;
}
