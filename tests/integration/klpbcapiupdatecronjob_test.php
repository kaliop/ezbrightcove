<?php
/**
 * File containing the klpbcUpdateCronjobTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpbcUpdateCronjobTest extends klpbcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->queue = new klpBcQueue( new klpBcDiContainereZTiein() );
        $this->queue->clear();
    }

    public function testProcessVideoSuccess()
    {
        $this->createVideo( 89, 2, 123 );

        $cronjob = new klpBcApiUpdateCronjob( $this->getDicMockWithApi( 123, false ) );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 89, 2 );

        $this->assertEquals(
            0, (int) $fetchedVideo->attribute( 'need_meta_update' ),
            "Expected video to no longer need meta update"
        );
    }

    public function testProcessVideoFailure()
    {
        $this->createVideo( 91, 5, 123 );

        $cronjob = new klpBcApiUpdateCronjob( $this->getDicMockWithApi( 123, true ) );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 91, 5 );

        $this->assertEquals(
            1, (int) $fetchedVideo->attribute( 'need_meta_update' ),
            "Expected video to still require a meta update"
        );
    }

    public function testProcessVideoBcIsBusy()
    {
        $this->createVideo( 91, 5, 123 );

        $api = $this->getMock( 'klpBcApi' );
        $api->expects( $this->once() )
            ->method( 'wasBusy' )
            ->will( $this->returnValue( true ) );

        $cronjob = new klpBcApiUpdateCronjob(
            $this->getDicMock( $api, $this->queue )
        );
        $cronjob->process();

        $fetchedVideo = klpBcVideo::fetch( 91, 5 );

        $this->assertEquals(
            1, (int) $fetchedVideo->attribute( 'need_meta_update' ),
            "Expected video to still require a meta update"
        );
    }

    public function testProcessVideoMultipleVersions()
    {
        $this->createVideo( 37, 1, 123 );
        $this->createVideo( 37, 2, 123 );

        $cronjob = new klpBcApiUpdateCronjob( $this->getDicMockWithApi( 123, false ) );
        $cronjob->process();

        $fetchedVideo1 = klpBcVideo::fetch( 21, 1 );
        $fetchedVideo2 = klpBcVideo::fetch( 21, 2 );
    }

    protected function getDicMockWithApi( $brightcoveId, $hasError )
    {
        $api = $this->getMock( 'klpBcApi' );
        $api->expects( $this->once() )
            ->method( 'update' )
            ->with(
                $this->equalTo( $brightcoveId ),
                $this->equalTo( 'My video name' ),
                $this->equalTo( 'My video description' )
            );
        $api->expects( $this->atLeastOnce() )
            ->method( 'hasError' )
            ->will( $this->returnValue( $hasError ) );

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

    protected function createVideo( $id, $version, $brightcoveId )
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', $id );
        $video->setAttribute( 'version', $version );
        $video->setAttribute( 'brightcove_id', $brightcoveId );
        $video->setAttribute( 'state', klpBcVideo::STATE_COMPLETED );
        $video->setAttribute( 'need_meta_update', 1 );
        $video->store();

        return $video;
    }
}
