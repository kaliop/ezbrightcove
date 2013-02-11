<?php
/**
 * File containing the klpBcApiUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcApiUnitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->api = new klpBcApi( "readtoken", "writetoken" );
    }

    public function testFetchAll()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'find' )
              ->with(
                  $this->equalTo( 'find_all_videos' ),
                  $this->equalTo( array(
                      'page_number' => 1,
                      'page_size' => 20
                  ) )
              );
        $this->api->internalApi = $bcApi;

        $this->api->fetchAll( 1, 20 );
    }

    public function testSearch()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'search' )
              ->with(
                  $this->equalTo( 'video' ),
                  $this->equalTo( array(
                      'any' => 'search text',
                  ) ),
                  $this->equalTo( array(
                      'page_number' => 1,
                      'page_size' => 20
                  ) )
              );
        $this->api->internalApi = $bcApi;

        $this->api->search( 'search text', 1, 20 );
    }

    public function testCreate()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'createMedia' )
              ->with(
                  // "api request type" param
                  $this->equalTo( 'video' ),
                  // file path param
                  $this->equalTo( "/tmp/fakefile.mp4" ),
                  // "meta" param
                  $this->equalTo( array(
                      'name' => "Video name",
                      'shortDescription' => "A short description",
                  ) ),
                  // "options" param
                  $this->equalTo( array( 'encode_to' => 'MP4' ) )
              )
              ->will( $this->returnValue( 123 ) );

        $this->api->internalApi = $bcApi;

        $brightcoveId = $this->api->create(
            "/tmp/fakefile.mp4", "Video name", "A short description"
        );

        $this->assertEquals( 123, $brightcoveId,
            "Expected to get a brightcove video id in return"
        );
    }

    public function testUpdate()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'update' )
              ->with(
                  $this->equalTo( 'video' ),
                  $this->equalTo( array(
                      'id' => 123,
                      'name' => 'Video name',
                      'shortDescription' => 'Video description'
                  ))
              );
        $this->api->internalApi = $bcApi;

        $this->api->update(
            123, "Video name", "Video description",
            "Expected video to be complete"
        );
    }

    public function testWasBusy()
    {
        $internalApi = $this->getInternalApiMockThatThrowsException(
            new BCMAPIException( new BCMAPI(), klpBcApi::MAX_CONCURRENT_REQUEST_CODE )
        );
        $this->api->internalApi = $internalApi;

        $brightcoveId = $this->api->create(
            "/tmp/fakefile.mp4", "Video name", "A short description"
        );

        $this->assertTrue( $this->api->wasBusy(), "Expected API to have been busy" );
    }

    public function testHasError()
    {
        $internalApi = $this->getInternalApiMockThatThrowsException(
            new BCMAPIException( new BCMAPI(), 1 )
        );

        $this->api->internalApi = $internalApi;

        $brightcoveId = $this->api->create(
            "/tmp/fakefile.mp4", "Video name", "A short description"
        );

        $this->assertTrue( $this->api->hasError(), "Expected API to have an an error" );
    }

    public function testLastError()
    {
        $internalApi = $this->getInternalApiMockThatThrowsException(
            new BCMAPIException( new BCMAPI(), 1 )
        );

        $this->api->internalApi = $internalApi;

        $brightcoveId = $this->api->create(
            "/tmp/fakefile.mp4", "Video name", "A short description"
        );

        $this->assertGreaterThan( 3, strlen( $this->api->getLastError() ),
            "Expected an error message"
        );
    }

    public function testIsCompleteTrue()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'getStatus' )
              ->with( $this->equalTo( 'video' ), $this->equalTo( 123 ) )
              ->will( $this->returnValue( 'COMPLETE' ) );
        $this->api->internalApi = $bcApi;

        $this->assertTrue( $this->api->isComplete( 123 ),
            "Expected video to be complete"
        );
    }

    public function testIsCompleteFalse()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'getStatus' )
              ->with( $this->equalTo( 'video' ), $this->equalTo( 123 ) )
              ->will( $this->returnValue( 'PROCESSING' ) );
        $this->api->internalApi = $bcApi;

        $this->assertFalse( $this->api->isComplete( 123 ),
            "Expected video to not be complete"
        );
    }

    public function testGetStatus()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'getStatus' )
              ->with( $this->equalTo( 'video' ), $this->equalTo( 123 ) )
              ->will( $this->returnValue( 'COMPLETE' ) );
        $this->api->internalApi = $bcApi;

        $this->api->getStatus( 123 );
    }

    public function testDelete()
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'delete' )
              ->with( $this->equalTo( 'video' ), $this->equalTo( 123 ) );
        $this->api->internalApi = $bcApi;

        $this->api->delete( 123 );
    }

    protected function getInternalApiMockThatThrowsException( $exception )
    {
        $bcApi = $this->getMock( 'BCMAPI' );
        $bcApi->expects( $this->once() )
              ->method( 'createMedia' )
              ->will( $this->throwException( $exception ) );

        return $bcApi;
    }
}
