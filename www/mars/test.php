<?php

define( 'APPLICATION', 'TEST Application' );
define( 'COPYRIGHT', '&copy; 2015 Juraj Brabec, Hewlett Packard Enterprise Company, L.P.' );

define( 'TIMEPERIOD', 'timeperiod' );
define( 'CUSTOMER', 'customer' );
define( 'MODE', 'mode' );
define( 'MAIL', 'mail' );
define( 'NORMAL', 'normal' );
define( 'SORT', 'sort' );

require_once 'inc\html.php' ;

class mars_page extends html_page {

	public function initialize( ) {
		parent::initialize( );
		$css_file = 'inc/test.css';
		$favicon_file_file = '/favicon.ico';
		$head = $this->head( );
		$head->add( new html_meta( array( CHARSET => 'UTF-8' ) ) );
		$head->add( new html_meta( array( HTTP_EQUIV => 'Content-Type', CONTENT => 'text/html; charset=UTF-8' ) ) );
		$head->add( new html_link( ICON, IMAGEXICON , $favicon_file ) );
		if ( $this->get( MODE ) == MAIL ) {
			$style = $head->add( new html_style( ) );
			$style->add( file_get_contents( sprintf( '%s/%s', __DIR__, $css_file ) ) );
		} else {
			$head->add( new html_link( STYLESHEET, TEXTCSS,  $css_file ) );
			$script = $head->add( new html_script( ) );
			$script->add( 'function hide(element){element.style.display="none"}' );
			$script->add( 'window.onload=function(){document.getElementById("loading").style.display="none"}' );
			$this->html_toolbar( );
		}
		$this->html_logo( );
	}

	public function html_toolbar( ) {
		$div = $this->body( )->add( new html_div( array( ID => 'toolbar' ) ) );
		$div->close( );
	}
	
	public function html_logo( ) {
		$message = $this->get( MESSAGE );
		if ( !empty( $message ) and $this->get( MODE ) != MAIL ) {
			$div = $this->body( )->add( new html_div( array( ID => 'message', ONCLICK => 'hide(this)' ) ) );
			$div->add( new html_text( $message ) );
			$div->implode( );
			$div->close( );
			$this->set( array( MESSAGE => NULL ) );
		}
		$div = $this->body( )->add( new html_div( array( ID => 'header' ) ) );
		$table = $div->add( new html_table( ) );
		$row = $table->add_row( );
		$logo = sprintf( 'data:image/png;base64,%s',
			base64_encode( file_get_contents( sprintf( '%s/logo.png', __DIR__ ) ) ) );
		$row->add( new html_img( $logo, 'Company logo' ) );
		$row->add( $this->get( TITLE ), array( CLASSNAME => 'title' ) );
		$div->close( );
		if ( $this->get( MODE ) != MAIL ) {
			$div = $this->body( )->add( new html_div( array( ID => 'loading', ONCLICK => 'hide(this)' ) ) );
			$div->add( new html_img( 'inc/loading.gif' ) );
			$div->implode( );
			$div->close( );
			$this->flush_buffer( );
		}
	}

	public function html_footer( ) {
		$div = $this->body( )->add( new html_div( array( ID => 'footer' ) ) );
		$div->add( new html_span( APPLICATION, array( CLASSNAME => BOLD ) ) );
		$div->add( new html_text( sprintf( ' | %s on ', COPYRIGHT ) ) );
		$div->add( new html_span( date( 'F j, Y, H:i' ), array( CLASSNAME => BOLD ) ) );
		$div->implode( );
		$div->close( );
	}

	public function finish( $discard = FALSE ) {
		$this->html_footer( );
		parent::finish( $discard );
	}
	
	public function send( ) {
		send( $this->output( ) );
	}
}

function send( $contents = array( ) ) {

	$subject = APPLICATION;
	$to = 'juraj.brabec@hpe.com';
	$cc = '';
	$from = 'mars-reports@hpe.com';
	$report_name = 'test';
	
	$uid = md5( uniqid( time( ) ) );
	$headers = array( );
	$headers[ ] = 'MIME-Version: 1.0';
	$headers[ ] = sprintf( 'Content-Type: multipart/mixed; boundary="%s"', $uid );
	$headers[ ] = sprintf( 'From: %s', $from );
	!empty( $cc ) && $headers[ ] = sprintf( 'Cc: %s', $cc );
	$headers[ ] = sprintf( 'Reply-To: %s', $from );
	$headers[ ] = sprintf( 'Subject: %s', $subject );
	$headers[ ] = sprintf( 'X-Mailer: PHP/%s', phpversion( ) );
		
	$body = array( );
	$body[ ] = 'This is a multi-part message in MIME format.';
	$body[ ] = '';
	$body[ ] = sprintf( '--%s', $uid );
	$logo_file = sprintf( '%s/logo.png', __DIR__ );
	if ( strlen( implode( PHP_EOL, $contents ) ) < 1 * 1024 * 1024 ) {
		$body[ ] = 'Content-type:text/html; charset=utf8';
		$body[ ] = 'Content-Transfer-Encoding: 7bit';
		$body[ ] = '';
		$body[ ] = implode( PHP_EOL, $contents );
		$body[ ] = '';
		$body[ ] = sprintf( '--%s', $uid );
		$body[ ] = 'Content-Type: application/octet-stream; name="logo.png"';
		$body[ ] = 'Content-Transfer-Encoding: base64';
		$body[ ] = 'Content-Disposition: attachment; filename="logo.png"';
		$body[ ] = '';
		$body[ ] = chunk_split( base64_encode( file_get_contents( $logo_file ) ) );
	} else {
		$body[ ] = 'Content-type:text/plain; charset=utf8';
		$body[ ] = 'Content-Transfer-Encoding: 7bit';
		$body[ ] = '';
		$body[ ] = 'Attached is the generated report in HTML format.';
		$body[ ] = sprintf( '(Exceeded allowed size of inline HTML report %s MB)', 1 );
		$body[ ] = '';
	}
	$body[ ] = '';
	$body[ ] = sprintf( '--%s', $uid );
	if ( strlen( implode( PHP_EOL, $contents ) ) < 1 * 1024 * 1024 ) {
		$body[ ] = sprintf( 'Content-Type: application/octet-stream; name="%s.html"', $report_name );
		$body[ ] = 'Content-Transfer-Encoding: base64';
		$body[ ] = sprintf( 'Content-Disposition: attachment; filename="%s.html"', $report_name );
		$body[ ] = '';
		$body[ ] = chunk_split( base64_encode( implode( PHP_EOL, $contents ) ) );
		$body[ ] = '';
	} else {
		$zip_file = sprintf( '%s/%s.zip', sys_get_temp_dir( ), $report_name );
		$zip = new ZipArchive;
		$zip->open( $zip_file, ZipArchive::CREATE );
		$zip->addFromString( sprintf( '%s.html', $report_name ), implode( PHP_EOL, $contents ) );
		$zip->close( );
		$body[ ] = sprintf( 'Content-Type: application/zip; name="%s.zip"', $report_name );
		$body[ ] = 'Content-Transfer-Encoding: base64';
		$body[ ] = sprintf( 'Content-Disposition: attachment; filename="%s.zip"', $report_name );
		$body[ ] = '';
		$body[ ] = chunk_split( base64_encode( file_get_contents( $zip_file ) ) );
		$body[ ] = '';
		unlink( $zip_file );
	}
	$body[ ] = sprintf( '--%s--', $uid );
	$result = mail( $to, $subject, implode( PHP_EOL, $body ), implode( PHP_EOL, $headers ) );
	return $result;	
}

$title = APPLICATION;
$parameters = array( CUSTOMER => '' , TIMEPERIOD => 'D-1::D', MODE => NORMAL );
$page = new mars_page( $title, $parameters );
$page->initialize( );

if ( $page->get( MODE ) != MAIL ) {
	$div = $page->body( )->add( new html_div( ) );
	$action = $page->register_action( array( CUSTOMER => CUSTOMER, MODE => MODE ) );
	$form = $div->add( new html_form( 'form1', 'A simple form' ) );
	$customers = array( 'AIS', 'USGD', 'TEST' );
	$form->add( new html_text( 'Customer:' ) );
	$form->add( new html_select( CUSTOMER, $customers, $page->get( CUSTOMER ) ) );
	$form->add( new html_text( 'Mode:' ) );
	$form->add( new html_input( MODE, $page->get( MODE ) ) );
	$form->add( new html_button( ACTIONS, 'Set values', $action ) );
	$div->close( );
	$page->flush_buffer( );
}

$div = $page->body( )->add( new html_div( array( CLASSNAME => 'shadow' ) ) );
$table = $div->add( new html_table( ) );
$id = 'Customer';
$table->head( )->add( new html_anchor( $id, $page->get_url( array( SORT => $id ) ) ), array( ID => $id ) );
$id = 'Mode';
$table->head( )->add( new html_anchor( $id, $page->get_url( array( SORT => $id ) ) ), array( ID => $id ) );
$sort = $page->get( SORT );
!empty( $sort ) && $table->head()->get_object( $sort )->set( array( CLASSNAME => SORT ) );
$row = $table->add_row( );
$row->add( $page->get( CUSTOMER ) );
$row->add( $page->get( MODE) );
$div->close( );
$page->flush_buffer( );

$div = $page->body( )->add( new html_div( ) );
$ul = $div->add( new html_unorderedlist( ) );
if ( $page->get( MODE ) != MAIL ) {
	$ul->add( new html_listitem( new html_anchor( 'Start over', $page->get_url( NULL ) ) ) );
	$ul->add( new html_listitem( new html_anchor( 'Refresh page', $page->get_url( ) ) ) );
	$ul->add( new html_listitem( new html_anchor( 'Customer AIS', $page->get_url( array( CUSTOMER => 'AIS' ) ) ) ) );
	$ul->add( new html_listitem( new html_anchor( 'Customer TEST, mode HTML', $page->get_url( array( CUSTOMER => 'TEST', MODE => NORMAL ) ) ) ) );
	$ul->add( new html_listitem( new html_anchor( 'Send', $page->get_url( array( MODE => MAIL ) ) ) ) );
} else {
	$ul->add( new html_listitem( new html_anchor( 'Reload', $page->get_url( array( MODE => NORMAL, MESSAGE => 'Page was sent' ) ) ) ) );
}
$div->close( );
$page->flush_buffer( );

$page->finish( );

if ( $page->get( MODE ) == MAIL ) {
	$result = $page->send( );
}

?>
