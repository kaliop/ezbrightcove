<?php
/**
 * File containing the klpBcQueueTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcQueueTest extends klpBcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->queue = new klpBcQueue( new klpBcDiContainereZTiein() );
        $this->queue->clear();
    }

    public function testMoveToStart()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'state', klpBcVideo::STATE_TO_PROCESS );

        $resultVideo = $this->queue->moveToStart( $video );

        $this->assertEquals(
            klpBcVideo::STATE_DRAFT, $resultVideo->attribute( 'state' ),
            "Expected state to be set to DRAFT"
        );
    }

    public function testClear()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 894 );
        $video->setAttribute( 'version', 3 );
        $video->setAttribute( 'state', klpBcVideo::STATE_TO_PROCESS );
        $video->store();

        $this->assertEquals( 1, $this->queue->size(),
            "Queue size must start out with a size of 1"
        );

        $this->queue->clear();
        $this->assertEquals( 0, $this->queue->size(),
            "Queue size must be 0 after clearing"
        );
    }

    public function testFetchPendingProcessing()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 894 );
        $video->setAttribute( 'version', 3 );
        $video->setAttribute( 'state', klpBcVideo::STATE_TO_PROCESS );
        $video->store();

        $videos = $this->queue->fetchPendingProcessing();

        $this->assertEquals( 1, count( $videos ),
            "Expected 1 video to be returned from fetch method"
        );
        $this->assertEquals( 894, $videos[0]->attribute( 'contentobject_attribute_id' ),
            "Expected content object attribute id of first and only video to be 894"
        );
    }

    public function testFetchProcessing()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 898 );
        $video->setAttribute( 'version', 1 );
        $video->setAttribute( 'state', klpBcVideo::STATE_PROCESSING );
        $video->store();

        $videos = $this->queue->fetchProcessing();

        $this->assertEquals( 1, count( $videos ),
            "Expected 1 video to be returned from fetch method"
        );
        $this->assertEquals( 898, $videos[0]->attribute( 'contentobject_attribute_id' ),
            "Expected content object attribute id of first and only video to be 898"
        );
    }

    public function testPendingMetaUpdate()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 890 );
        $video->setAttribute( 'version', 1 );
        $video->setAttribute( 'need_meta_update', 1 );
        $video->store();

        $videos = $this->queue->fetchPendingMetaUpdate();

        $this->assertEquals( 1, count( $videos ),
            "Expected 1 video to be returned from fetch method"
        );
    }

    public function testFetchPendingDeletion()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 905 );
        $video->setAttribute( 'version', 1 );
        $video->setAttribute( 'state', klpBcVideo::STATE_TO_DELETE );
        $video->store();

        $videos = $this->queue->fetchPendingDeletion();

        $this->assertEquals( 1, count( $videos ),
            "Expected 1 video to be returned from fetch method"
        );
        $this->assertEquals( 905, $videos[0]->attribute( 'contentobject_attribute_id' ),
            "Expected content object attribute id of first and only video to be 898"
        );
    }

    public function testMoveFromToProcessingToProcessing()
    {
        $fetchedVideo = $this->moveVideoWithState(
            432, 4, klpBcVideo::STATE_TO_PROCESS, true
        );

        $this->assertEquals(
            klpBcVideo::STATE_PROCESSING, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to have changed to STATE_PROCESSING"
        );
    }

    public function testMoveFromToProcessingToFailure()
    {
        $fetchedVideo = $this->moveVideoWithState(
            432, 4, klpBcVideo::STATE_TO_PROCESS, false
        );

        $this->assertEquals(
            klpBcVideo::STATE_FAILED, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to have changed to STATE_PROCESSING"
        );
    }

    public function testMoveFromProcessingToCompleted()
    {
        $fetchedVideo = $this->moveVideoWithState(
            432, 4, klpBcVideo::STATE_PROCESSING, true
        );

        $this->assertEquals(
            klpBcVideo::STATE_COMPLETED, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to have changed to STATE_COMPLETED"
        );
    }

    public function testMoveFromToDeleteToDeleted()
    {
        $fetchedVideo = $this->moveVideoWithState(
            43, 7, klpBcVideo::STATE_TO_DELETE, true
        );

        $this->assertNull( $fetchedVideo, "Expected video to be deleted" );
    }

    public function testMoveResetsNeedMetaUpdate()
    {
        $fetchedVideo = $this->moveVideoWithState(
            426, 1, klpBcVideo::STATE_COMPLETED, true, 1
        );

        $this->assertEquals(
            klpBcVideo::STATE_COMPLETED, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to not have changed"
        );
        $this->assertEquals(
            0, (int) $fetchedVideo->attribute( 'need_meta_update' ),
            "Expected video to not need a meta update"
        );

        $fetchedVideo = $this->moveVideoWithState(
            429, 1, klpBcVideo::STATE_COMPLETED, false, 1
        );

        $this->assertEquals(
            klpBcVideo::STATE_COMPLETED, (int) $fetchedVideo->attribute( 'state' ),
            "Expected state to not have changed when there's an error"
        );
        $this->assertEquals(
            1, (int) $fetchedVideo->attribute( 'need_meta_update' ),
            "Expected video to still need a meta update"
        );
    }

    protected function moveVideoWithState( $id, $version, $state, $succesful, $needMetaUpdate = 0 )
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', $id );
        $video->setAttribute( 'version', $version );
        $video->setAttribute( 'state', $state );
        $video->setAttribute( 'need_meta_update', $needMetaUpdate );
        $video->store();

        $this->queue->move( $video, $succesful );

        return klpBcVideo::fetch( $id, $version );
    }
}
