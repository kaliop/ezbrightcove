<?php
/**
 * File containing the klpBcVideoTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcVideoTest extends klpBcTestCase
{
    public function testNewAndStore()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 100 );
        $video->setAttribute( 'version', 1 );
        $video->store();

        $fetchedVideo = klpBcVideo::fetch( 100, 1 );

        $this->assertEquals( 100, $fetchedVideo->attribute( 'contentobject_attribute_id' ),
            "Expected content object attribute id = 100"
        );
        $this->assertEquals( 1, $fetchedVideo->attribute( 'version' ),
            "Expected version = 100"
        );
        $this->assertSame( (string) klpBcVideo::STATE_DRAFT, $fetchedVideo->attribute( 'state' ),
            "Expected state to be set to 'draft'"
        );
        $this->assertNotNull( $fetchedVideo->attribute( 'created' ),
            "Expected created date to be set"
        );
        $this->assertNotNull( $fetchedVideo->attribute( 'modified' ),
            "Expected modified date to be set"
        );
    }

    public function testOriginalVideo()
    {
        $binary = eZBinaryFile::create( 59, 30 );
        $binary->store();

        $video = $this->createVideo( 59, 30 );
        $video->setAttribute( 'input_type_identifier', 'upload' );

        $originalVideo = $video->originalVideo();

        $this->assertEquals(
            $binary->attribute( 'id' ), $originalVideo->attribute( 'id' ),
            "Original video was not the same as we expected"
        );
    }

    public function testFetchByState()
    {
        klpBcVideo::removeAll();

        $video = $this->createVideo( 888, 2, klpBcVideo::STATE_FAILED );

        $videos = klpBcVideo::fetchByState( klpBcVideo::STATE_FAILED );
        $this->assertEquals( 1, count( $videos ),
            "Expected 1 video to be returned from fetchByState()"
        );
        $this->assertEquals( 888, $videos[0]->attribute( 'contentobject_attribute_id' ),
            "Expected content object attribute id of first and only video to be 888"
        );
    }

    public function testFetchVersionsInSameState()
    {
        klpBcVideo::removeAll();

        $video1 = $this->createVideo( 342, 1, klpBcVideo::STATE_TO_PROCESS );
        $video2 = $this->createVideo( 342, 2, klpBcVideo::STATE_TO_PROCESS );
        $video3 = $this->createVideo( 342, 3, klpBcVideo::STATE_COMPLETED );

        $videos = $video1->fetchVersions();

        $this->assertEquals( 2, count( $videos ),
            "Expected 2 videos to be returned"
        );
        $this->assertEquals( 2, $videos[0]->attribute( 'version' ),
            "Expected first video to be version 2"
        );
        $this->assertEquals( 1, $videos[1]->attribute( 'version' ),
            "Expected second video to be version 1"
        );
    }

    public function testFetchPendingMetaUpdate()
    {
        klpBcVideo::removeAll();

        $video1 = $this->createVideo( 359, 1, klpBcVideo::STATE_TO_PROCESS, 1 );
        $video2 = $this->createVideo( 359, 2, klpBcVideo::STATE_TO_PROCESS, 0 );
        $video3 = $this->createVideo( 359, 3, klpBcVideo::STATE_COMPLETED, 1 );

        $videos = klpBcVideo::fetchPendingMetaUpdate();

        $this->assertEquals( 2, count( $videos ),
            "Expected 2 videos to be returned"
        );
        $this->assertEquals( 3, $videos[0]->attribute( 'version' ),
            "Expected first video to be version 3"
        );
        $this->assertEquals( 1, $videos[1]->attribute( 'version' ),
            "Expected second video to be version 1"
        );
    }

    public function testFetchLatestVideo()
    {
        klpBcVideo::removeAll();

        $video1 = $this->createVideoWithBid( 319, 1, klpBcVideo::STATE_TO_PROCESS );
        $video2 = $this->createVideoWithBid( 319, 2, klpBcVideo::STATE_COMPLETED );
        $video3 = $this->createVideoWithBid( 319, 3, klpBcVideo::STATE_COMPLETED );
        $video4 = $this->createVideoWithBid( 319, 4, klpBcVideo::STATE_PROCESSING );
        $video5 = $this->createVideoWithBid( 320, 6, klpBcVideo::STATE_COMPLETED );

        $video = $video1->attribute( 'latest_video' );

        $this->assertEquals( 3, $video->attribute( 'version' ),
            "Expected the latest version to be version 3"
        );
    }

    public function testRequiresProcessing()
    {
        $video = new klpBcVideo( false );

        $this->assertFalse( $video->requiresProcessing(),
            "Expected video to not require processing with no input type"
        );

        $video->setAttribute( 'input_type_identifier', 'upload' );

        $this->assertTrue( $video->requiresProcessing(),
            "Expected video to require processing with input type upload"
        );
    }

    public function testDeleteCurrentVersionOnly()
    {
        klpBcVideo::removeAll();

        $video1 = $this->createVideo( 341, 1, klpBcVideo::STATE_COMPLETED );
        $video2 = $this->createVideo( 341, 2 );

        $video1->delete( true );

        $fetchedVideo1 = klpBcVideo::fetch( 341, 1 );
        $this->assertNull( $fetchedVideo1, "Expected video 1 to be deleted" );

        $fetchedVideo2 = klpBcVideo::fetch( 341, 2 );
        $this->assertNotNull( $fetchedVideo2, "Expected video 2 to not be removed" );
    }

    public function testDeleteAllVersions()
    {
        klpBcVideo::removeAll();

        $video1 = $this->createVideo( 342, 1, klpBcVideo::STATE_COMPLETED );
        $video2 = $this->createVideo( 342, 2 );
        $video3 = $this->createVideo( 342, 3 );
        $video3->setAttribute( 'brightcove_id', 123 );
        $video3->store();

        $video1->delete( false );

        $fetchedVideo1 = klpBcVideo::fetch( 342, 1 );
        $this->assertNull( $fetchedVideo1, "Expected video 1 to be deleted" );

        $fetchedVideo2 = klpBcVideo::fetch( 342, 2 );
        $this->assertNull( $fetchedVideo2, "Expected video 2 to be deleted" );

        $fetchedVideo3 = klpBcVideo::fetch( 342, 3 );
        $this->assertTrue( $fetchedVideo3->isInState( "TO_DELETE" ),
            "Expected video 3 to have changed to TO_DELETE"
        );
    }

    protected function createVideo( $id, $version, $state = null, $needMetaUpdate = 0 )
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', $id );
        $video->setAttribute( 'version', $version );

        if ( $state )
            $video->setAttribute( 'state', $state );

        if ( $needMetaUpdate )
            $video->setAttribute( 'need_meta_update', $needMetaUpdate );

        $video->store();

        return $video;
    }

    protected function createVideoWithBid( $id, $version, $state, $bId )
    {
        $video = $this->createVideo( $id, $version, $state );
        $video->setAttribute( 'brightcove_id', $bId );
        $video->store();

        return $video;
    }
}
