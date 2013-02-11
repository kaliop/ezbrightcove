<?php
/**
 * File containing the klpbcDeleteCronjobTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpbcDeleteCronjobTest extends klpbcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->queue = new klpBcQueue( new klpBcDiContainereZTiein() );
        $this->queue->clear();
    }

    public function testProcessVideoIsDeleted()
    {
        $this->queueVideo( 71, 1, 123 );

        $cronjob = new klpBcApiDeleteCronjob(
            $this->getDicMockWithApi( 123 )
        );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 71, 1 );

        $this->assertNull( $fetchedVideo, "Expected video to be deleted" );
    }

    public function testProcessVideoNoBrightcoveId()
    {
        $this->queueVideo( 67, 3, null );

        $cronjob = new klpBcApiDeleteCronjob(
            $this->getDicMockWithApi( null )
        );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 71, 1 );

        $this->assertNull( $fetchedVideo, "Expected video to be deleted" );
    }

    public function testProcessVideoDuplicateBrightcoveId()
    {
        $this->queueVideo( 69, 1, 1234 );
        $this->queueVideo( 69, 2, 1234 );

        $cronjob = new klpBcApiDeleteCronjob(
            $this->getDicMockWithApi( 1234 )
        );
        $cronjob->process();

        $fetchedVideo1 = klpBcVideo::fetch( 69, 1 );
        $fetchedVideo2 = klpBcVideo::fetch( 69, 2 );

        $this->assertNull( $fetchedVideo1, "Expected video 1 to be deleted" );
        $this->assertNull( $fetchedVideo2, "Expected video 2 to be deleted" );
    }

    protected function getDicMockWithApi( $brightcoveId )
    {
        $api = $this->getMock( 'klpBcApi' );
        if ( $brightcoveId )
            $api->expects( $this->once() )
                ->method( 'delete' )
                ->with( $this->equalTo( $brightcoveId ) );
        else
            $api->expects( $this->never() )
                ->method( 'delete' );

        return $this->getDicMock( $api, $this->queue );
    }

    protected function getDicMock( $api, $queue )
    {
        $dic = $this->getMock(
            'klpBcDiContainereZTiein', array( 'getBcApi', 'getQueue' )
        );
        $dic->expects( $this->atLeastOnce() )
            ->method( 'getBcApi' )
            ->will( $this->returnValue( $api ) );

        $dic->expects( $this->atLeastOnce() )
            ->method( 'getQueue' )
            ->will( $this->returnValue( $queue ) );

        return $dic;
    }

    protected function queueVideo( $id, $version, $brightcoveId )
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', $id );
        $video->setAttribute( 'version', $version );
        $video->setAttribute( 'state', klpBcVideo::STATE_TO_DELETE );
        if ( $brightcoveId )
            $video->setAttribute( 'brightcove_id', $brightcoveId );
        $video->store();
    }
}
