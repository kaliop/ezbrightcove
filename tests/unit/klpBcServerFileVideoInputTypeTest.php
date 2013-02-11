<?php
/**
 * File containing the klpBcServerFileVideoInputTypeUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcServerFileVideoInputTypeUnitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->input = new klpBcServerFileVideoInputType( 'test_input', new stdClass() );
    }

    public function testIsInputDataValid()
    {
        $this->assertFalse(
            $this->input->isInputDataValid( array(), true, false ),
            "Empty input should not be valid when input is required"
        );

        $this->assertTrue(
            $this->input->isInputDataValid( array(), false, false ),
            "Empty input should be valid when input is not required"
        );

        $this->assertFalse(
            $this->input->isInputDataValid( array(), true, true ),
            "Empty input should not be valid when is required and existing content=true"
        );

        $data = array( 'serverfile' => "" );
        $this->assertFalse(
            $this->input->isInputDataValid( $data, true, false ),
            "No ServerFile id in input should not be valid when input is required"
        );

        $data = array( 'serverfile' => "" );
        $this->assertTrue(
            $this->input->isInputDataValid( $data, false, false ),
            "No ServerFile id in input should be valid when input is not required"
        );

        $data = array( 'serverfile' => "http://www.example.com" );
        $this->assertTrue(
            $this->input->isInputDataValid( $data, false, false ),
            "Input with ServerFile should be valid"
        );
    }

    public function testLastError()
    {
        $this->assertSame( "", $this->input->lastError() );

        $this->input->isInputDataValid( array(), true, true );

        $this->assertTrue( is_string( $this->input->lastError() ) );
        $this->assertTrue( strlen( $this->input->lastError() ) > 1 );
    }

    public function testFetch()
    {
        $serverfile = $this->getMock( 'stdClass', array( 'fetch' ) );
        $serverfile->expects( $this->once() )
            ->method( 'fetch' )
            ->with( $this->equalTo( 100 ), $this->equalTo( 1 ) );

        $this->input->serverfileInstance = $serverfile;

        $this->input->fetch( 100, 1 );
    }

    public function testInitialize()
    {
        $oldServerFile = $this->getMock( 'stdClass', array( 'store', 'setAttribute' ) );
        $oldServerFile->expects( $this->atLeastOnce() )
               ->method( 'setAttribute' );
        $oldServerFile->expects( $this->once() )
               ->method( 'store' );

        $serverfile = $this->getMock( 'stdClass', array( 'fetch' ) );
        $serverfile->expects( $this->once() )
            ->method( 'fetch' )
            ->will( $this->returnValue( $oldServerFile ) );

        $this->input->serverfileInstance = $serverfile;

        $this->input->initialize( null, null, null );
    }

    public function testStoreReturnsFalseWhenNoData()
    {
        $this->assertFalse( $this->input->store( array(), null, null ) );
    }

    public function testStore()
    {
        $serverfile = $this->getMock( 'stdClass', array( 'setAttribute', 'store' ) );
        $serverfile->expects( $this->atLeastOnce() )
             ->method( 'setAttribute' );
        $serverfile->expects( $this->once() )
            ->method( 'store' );

        $input = $this->getMock(
            'klpBcServerFileVideoInputType', array( 'fetch' ), array( 'test', new stdClass() )
        );
        $input->expects( $this->once() )
              ->method( 'fetch' )
              ->will( $this->returnValue( $serverfile ) );

        $this->assertTrue(
            $input->store( array( 'serverfile' => 'http://www.example.com' ), null, null )
        );
    }

    public function testDeleteOneVersion()
    {
        $serverfile = $this->getMock( 'stdClass', array( 'delete' ) );
        $serverfile->expects( $this->once() )
                   ->method( 'delete' )
                   ->with( $this->equalTo( 123 ), $this->equalTo( 1 ) );

        $this->input->serverfileInstance = $serverfile;

        $this->input->delete( 123, 1 );
    }

    public function testDeleteAll()
    {
        $serverfile = $this->getMock( 'stdClass', array( 'delete' ) );
        $serverfile->expects( $this->once() )
                   ->method( 'delete' )
                   ->with( $this->equalTo( 123 ), $this->equalTo( null ) );

        $this->input->serverfileInstance = $serverfile;

        $this->input->delete( 123, null );
    }

    public function testFromString()
    {
        $input = $this->getMock(
            'klpBcServerFileVideoInputType', array( 'store' ), array( 'test', new stdClass() )
        );
        $input->expects( $this->once() )
              ->method( 'store' );

        $input->fromString( null, null, "http://www.example.com" );
    }

    public function testGetFileUrlNoFileStored()
    {
        $input = $this->getMock(
            'klpBcServerFileVideoInputType', array( 'fetch' ), array( 'test', new stdClass() )
        );
        $input->expects( $this->once() )
              ->method( 'fetch' )
              ->will( $this->returnValue( null ) );

        $this->assertNull( $input->getFileUrl( null, null ) );
    }

    public function testGetFileUrlFileStored()
    {
        $config = $this->getMockBuilder( 'klpBcServerFileConfig' )
                       ->disableOriginalConstructor()
                       ->getMock();
        $config->expects( $this->once() )
               ->method( '__get' )
               ->with( $this->equalTo( 'rootDirectory' ) )
               ->will( $this->returnValue( '/a/' ) );
        $file = $this->getMock( 'stdClass', array( 'attribute' ) );
        $file->expects( $this->once() )
             ->method( 'attribute' )
             ->will( $this->returnValue( '/b/c.mp4' ) );
        $input = $this->getMock(
            'klpBcServerFileVideoInputType', array( 'fetch' ), array( 'test', $config )
        );
        $input->expects( $this->once() )
              ->method( 'fetch' )
              ->will( $this->returnValue( $file ) );

        $this->assertSame( "/a/b/c.mp4", $input->getFileUrl( 1, 2 ) );
    }

    public function testGetFileInfo()
    {
        $this->assertFalse( $this->input->getFileInfo( null, null ) );
    }

    public function testRequiresProcessing()
    {
        $this->assertTrue( $this->input->requiresProcessing() );
    }

    public function testCanDownload()
    {
        $this->assertFalse( $this->input->canDownload() );
    }
}
