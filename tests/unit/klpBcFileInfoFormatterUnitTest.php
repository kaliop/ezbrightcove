<?php
/**
 * File containing the klpBcFileInfoFormatterUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcFileInfoFormatterUnitTest extends PHPUnit_Framework_TestCase
{
    public function testGetArray()
    {
        $formatter = new klpBcFileInfoFormatter( '/a' );
        $file = $this->getMock( 'stdClass', array( 
            'getFilename', 'getSize', 'isFile', 'getPathname'
        ));
        $file->expects( $this->atLeastOnce() )
             ->method( 'getFilename' )
             ->will( $this->returnValue( 'a.txt' ) );
        $file->expects( $this->once() )
             ->method( 'getSize' )
             ->will( $this->returnValue( 494 ) );
        $file->expects( $this->once() )
             ->method( 'isFile' )
             ->will( $this->returnValue( false ) );
        $file->expects( $this->atLeastOnce() )
             ->method( 'getPathname' )
             ->will( $this->returnValue( '/a/b/c/a.txt' ) );

        $array = $formatter->getArray( $file );

        $this->assertArrayHasKey( 'filename', $array );
        $this->assertArrayHasKey( 'size', $array );
        $this->assertArrayHasKey( 'isfile', $array );
        $this->assertArrayHasKey( 'path', $array );
        $this->assertArrayHasKey( 'relativepath', $array );

        $this->assertSame( 'a.txt', $array['filename'] );
        $this->assertSame( 494, $array['size'] );
        $this->assertSame( false, $array['isfile'] );
        $this->assertSame( '/a/b/c/a.txt', $array['path'] );
        $this->assertSame( 'b/c/a.txt', $array['relativepath'] );
    }
}
