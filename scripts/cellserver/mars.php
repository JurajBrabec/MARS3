<?php

/*
 * MARS 3.0 PHP CODE
 * build 3.0.0.0 @ 2014-03-25 10:00
 * * rewritten from scratch
 */

require_once 'inc/application.php';

$mars = new mars_application( $GLOBALS[ 'argv' ] );
$mars->execute( );
