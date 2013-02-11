<?php
/**
 * File containing the klpBcBrightcoveServerFunctionsUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcBrightcoveServerFunctionsUnitTest extends PHPUnit_Framework_TestCase
{
    public function testVideosCallsApi()
    {
        $api = $this->getMock( 'stdClass', array( 'fetchAll' ) );
        $api->expects( $this->once() )
            ->method( 'fetchAll' );

        $server = new klpBcBrightcoveServerFunctions();
        $result = $server->videos( array(), $api );
    }

    public function testVideosNoPageSet()
    {
        $api = $this->getMock( 'stdClass', array( 'fetchAll' ) );
        $api->expects( $this->once() )
            ->method( 'fetchAll' )
            ->with( $this->equalTo( 0 ) )
            ->will( $this->returnValue( 'some value' ) );

        $server = new klpBcBrightcoveServerFunctions();
        $result = $server->videos( array(), $api );

        $this->assertEquals( 'some value', $result,
            "Expected to receive valid return value"
        );
    }

    public function testVideosPageSet()
    {
        $api = $this->getMock( 'stdClass', array( 'fetchAll' ) );
        $api->expects( $this->once() )
            ->method( 'fetchAll' )
            ->with( $this->equalTo( 12 ) )
            ->will( $this->returnValue( 'some value' ) );

        $server = new klpBcBrightcoveServerFunctions();
        $result = $server->videos( array( 12 ), $api );
    }

    public function testSearchCallsApi()
    {
        $api = $this->getMock( 'stdClass', array( 'search' ) );
        $api->expects( $this->once() )
            ->method( 'search' )
            ->with(
                    $this->equalTo( 'search text' ),
                    $this->equalTo( 3 ),
                    $this->equalTo( 12 )
                  );

        $server = new klpBcBrightcoveServerFunctions();
        $result = $server->search( array( 'search text', '3' ), $api );
    }

    public function testSearchNoPageSet()
    {
        $api = $this->getMock( 'stdClass', array( 'search' ) );
        $api->expects( $this->once() )
            ->method( 'search' )
            ->with(
                    $this->equalTo( 'search text' ),
                    $this->equalTo( 0 ),
                    $this->equalTo( 12 )
                  );

        $server = new klpBcBrightcoveServerFunctions();
        $result = $server->search( array( 'search text' ), $api );
    }

    public function testFiles()
    {
        $formatter = $this->getMock( 'stdClass' );

        $browser = $this->getMock( 'stdClass', array( 'scan' ) );
        $browser->expects( $this->once() )
                ->method( 'scan' )
                ->with(
                    $this->equalTo( '/a/' ),
                    $this->equalTo( $formatter )
                );

        $server = new klpBcBrightcoveServerFunctions();
        $result = $server->files( '/a/', $browser, $formatter );
    }
}
