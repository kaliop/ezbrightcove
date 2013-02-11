<?php
/**
 * File containing the klpBcBrightcoveVideoInputTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcBrightcoveVideoInputTypeTest extends klpBcTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->input = new klpBcBrightcoveVideoInputType( 'test_input' );
    }

    public function testFetch()
    {
        $video = $this->createVideo( 872, 1 );

        $fetchedVideo = $this->input->fetch( 872, 1 );

        $this->assertInstanceOf( "klpbcVideo", $fetchedVideo,
            "Fetch should have returned a klpbcVideo"
        );
    }

    public function testStore()
    {
        $video = $this->createVideo( 231, 1 );

        $data = array( 'brightcove_id' => "123192873" );
        $this->input->store( $data, 231, 1 );

        $fetchedVideo = klpbcVideo::fetch( 231, 1 );

        $this->assertEquals( "123192873", $fetchedVideo->attribute( 'brightcove_id' ),
            "Expected brightcove id to be set and match"
        );
    }

    public function testStoreNotSaved()
    {
        $video = $this->createVideo( 179, 1, "2394823874" );

        $data = array( 'brightcove_id' => "2394823874" );
        $this->assertFalse( $this->input->store( $data, 179, 1 ),
            "Expected input type to not store as value hasn't changed"
        );

        $data = array();
        $this->assertFalse( $this->input->store( $data, 179, 1 ),
            "Expected input type to not store as value is not set"
        );
    }

    public function testStoreSaved()
    {
        $video = $this->createVideo( 149, 1, "2394823874" );
        $data = array( 'brightcove_id' => "" );

        $this->assertTrue( $this->input->store( $data, 149, 1 ),
            "Expected input type to store as value is empty which is allowed"
        );
    }

    public function testDelete()
    {
        $video = $this->createVideo( 493, 1, "12397423" );

        $this->input->delete( 493, 1 );

        $fetchedVideo = klpbcVideo::fetch( 493, 1 );

        $this->assertEquals( "", $fetchedVideo->attribute( 'brightcove_id' ),
            "Expected brightcove id to be empty"
        );
    }

    protected function createVideo( $id, $version, $bId = null )
    {
        $video = new klpbcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', $id );
        $video->setAttribute( 'version', $version );

        if ( $bId )
            $video->setAttribute( 'brightcove_id', $bId );

        $video->store();

        return $video;
    }
}
