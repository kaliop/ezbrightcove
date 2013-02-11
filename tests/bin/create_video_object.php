#!/usr/bin/env php
<?php

require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'debug-message' => '',
                                      'use-session' => true,
                                      'use-modules' => true,
                                      'use-extensions' => true ) );

$script->startup();
$script->setUseDebugOutput( true );


$script->setUseSiteAccess( "ezflow_site_admin" );
$script->initialize();

$tmpFileName = tempnam( sys_get_temp_dir(), "testFromString" ) . ".mp4";
file_put_contents( $tmpFileName, "testfile" );

$string = "upload|{$tmpFileName}";

$video = new ezpObject( 'brightcove', 2 );
$video->name = "CLI test video";
$dataMap = $video->dataMap();
$dataMap['video']->fromString( $string );
$dataMap['video']->store();

$video->publish();

@unlink( $tmpFileName );

$script->shutdown();
