<?php
/**
 * File containing the klpbcStatusCronjobTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpbcStatusCronjobTest extends klpbcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->queue = new klpBcQueue( new klpBcDiContainereZTiein() );
        $this->queue->clear();
    }

    public function testProcessVideoIsComplete()
    {
        $this->queueVideo( 84, 1, 123 );

        $cronjob = new klpBcApiStatusCronjob( $this->getDicMockWithApi( 123, true ) );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 84, 1 );

        $this->assertEquals(
            klpBcVideo::STATE_COMPLETED, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to have changed to STATE_COMPLETED"
        );
    }

    public function testProcessVideoIsNotComplete()
    {
        $this->queueVideo( 18, 6, 1234 );

        $cronjob = new klpBcApiStatusCronjob( $this->getDicMockWithApi( 1234, false ) );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 18, 6 );

        $this->assertEquals(
            klpBcVideo::STATE_PROCESSING, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to not have changed"
        );
    }

    protected function getDicMockWithApi( $brightcoveId, $isComplete )
    {
        $api = $this->getMock( 'klpBcApi' );
        $api->expects( $this->once() )
            ->method( 'isComplete' )
            ->with( $this->equalTo( $brightcoveId ) )
            ->will( $this->returnValue( $isComplete ) );

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
        $video->setAttribute( 'brightcove_id', $brightcoveId );
        $video->setAttribute( 'state', klpBcVideo::STATE_PROCESSING );
        $video->store();
    }
}
