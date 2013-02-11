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


$api = new klpBcApieZTiein();
$result = $api->fetchAll(1, 2);

var_dump( $result );

$script->shutdown();
