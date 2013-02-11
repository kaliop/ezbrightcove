<?php
/**
 * File containing the klpbcCreateCronjobTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpbcCreateCronjobTest extends klpbcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->queue = new klpBcQueue( new klpBcDiContainereZTiein() );
        $this->queue->clear();
    }

    public function testProcessVideoSuccess()
    {
        $this->queueVideo( 89, 2 );

        $cronjob = new klpBcApiCreateCronjob( $this->getDicMockWithApi( 123, false, null ) );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 89, 2 );

        $this->assertEquals(
            klpBcVideo::STATE_PROCESSING, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to have changed to STATE_PROCESSING"
        );
    }

    public function testProcessVideoFailure()
    {
        $this->queueVideo( 13, 4 );

        $cronjob = new klpBcApiCreateCronjob( $this->getDicMockWithApi(
            null, true, "My error message"
        ) );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 13, 4 );

        $this->assertEquals(
            klpBcVideo::STATE_FAILED, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to have changed to STATE_FAILED"
        );

        $this->assertEquals(
            "My error message", $fetchedVideo->attribute( 'error_log' ),
            "Expected error log to match"
        );
    }

    public function testProcessVideoBcIsBusy()
    {
        $this->queueVideo( 42, 30 );

        $api = $this->getMock( 'klpBcApi' );
        $api->expects( $this->once() )
            ->method( 'wasBusy' )
            ->will( $this->returnValue( true ) );

        $cronjob = new klpBcApiCreateCronjob(
            $this->getDicMock( $api, $this->queue )
        );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 42, 30 );

        $this->assertEquals(
            klpBcVideo::STATE_TO_PROCESS, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to not change has Brigthcove was busy"
        );
    }

    public function testProcessVideoMultipleVersions()
    {
        $this->queueVideo( 21, 1 );
        $this->queueVideo( 21, 2 );

        $cronjob = new klpBcApiCreateCronjob( $this->getDicMockWithApi( 123, false, null ) );
        $cronjob->process();

        $fetchedVideo1 = klpBcVideo::fetch( 21, 1 );
        $fetchedVideo2 = klpBcVideo::fetch( 21, 2 );

        $this->assertEquals(
            klpBcVideo::STATE_PROCESSING, (int) $fetchedVideo1->attribute( 'state' ),
            "Expected state to have changed to STATE_PROCESSING for video 1"
        );

        $this->assertEquals(
            klpBcVideo::STATE_PROCESSING, (int) $fetchedVideo1->attribute( 'state' ),
            "Expected state to have changed to STATE_PROCESSING for video 2"
        );

        $this->assertEquals(
            123, (int) $fetchedVideo1->attribute( 'brightcove_id' ),
            "Expected video 1 to have a brightcove id"
        );

        $this->assertEquals(
            123, (int) $fetchedVideo2->attribute( 'brightcove_id' ),
            "Expected video 2 to have a brightcove id"
        );
    }

    protected function getDicMockWithApi( $brightcoveId, $hasError, $lastError )
    {
        $api = $this->getMock( 'klpBcApi' );
        $api->expects( $this->once() )
            ->method( 'create' )
            ->with(
                $this->stringContains( 'original/video/myvideo.mp4' ),
                $this->equalTo( 'My video name' ),
                $this->equalTo( 'My video description' )
            )
            ->will( $this->returnValue( $brightcoveId ) );
        $api->expects( $this->atLeastOnce() )
            ->method( 'hasError' )
            ->will( $this->returnValue( $hasError ) );
        $api->expects( $this->atLeastOnce() )
            ->method( 'getLastError' )
            ->will( $this->returnValue( $lastError ) );

        return $this->getDicMock( $api, $this->queue );
    }

    protected function getDicMock( $api, $queue )
    {
        $dic = $this->getMock(
            'klpBcDiContainereZTiein', array(
                'getBcApi', 'getQueue', 'getVideoMeta'
            )
        );
        $dic->expects( $this->atLeastOnce() )
            ->method( 'getBcApi' )
            ->will( $this->returnValue( $api ) );

        $dic->expects( $this->atLeastOnce() )
            ->method( 'getQueue' )
            ->will( $this->returnValue( $queue ) );

        $dic->expects( $this->atLeastOnce() )
            ->method( 'getVideoMeta' )
            ->will( $this->returnValue( $this->getMetaMock() ) );

        return $dic;
    }

    protected function getMetaMock()
    {
        $meta = $this->getMock( 'klpBcVideoMeta' );
        $meta->expects( $this->once() )
             ->method( 'getName' )
             ->will( $this->returnValue( 'My video name' ) );
        $meta->expects( $this->once() )
             ->method( 'getDescription' )
             ->will( $this->returnValue( 'My video description' ) );

        return $meta;
    }

    protected function queueVideo( $id, $version )
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', $id );
        $video->setAttribute( 'version', $version );
        $video->setAttribute( 'input_type_identifier', 'upload' );
        $video->store();

        $file = eZBinaryFile::create( $id, $version );
        $file->setAttribute( 'mime_type', 'video/mp4' );
        $file->setAttribute( 'filename', 'myvideo.mp4' );
        $file->store();

        $this->queue->insert( $video );
    }
}

