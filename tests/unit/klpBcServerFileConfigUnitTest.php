<?php
/**
 * File containing the klpBcServerFileConfigUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcServerFileConfigUnitTest extends PHPUnit_Framework_TestCase
{

    public function testRootDirectory()
    {
        $ini = $this->getMock( 'stdClass', array( 'variable' ) );
        $ini->expects( $this->once() )
            ->method( 'variable' )
            ->will( $this->returnValue( "/tmp/root" ) );

        $config = new klpBcServerFileConfig( $ini );

        $this->assertSame( "/tmp/root", $config->rootDirectory );
    }

    public function testIsEnabled()
    {
        $ini = $this->getMock( 'stdClass', array( 'variable' ) );
        $ini->expects( $this->once() )
            ->method( 'variable' )
            ->will( $this->returnValue( null ) );

        $config = new klpBcServerFileConfig( $ini );

        $this->assertFalse( $config->isEnabled );
    }
}
