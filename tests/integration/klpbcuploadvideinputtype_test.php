<?php
/**
 * File containing the klpBcUploadVideoInputTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcUploadVideoInputTypeTest extends klpBcTestCase
{
    public function testFetch()
    {
        $file = eZBinaryFile::create( 80, 8 );
        $file->store();

        $inputType = new klpBcUploadVideoInputType( 'test_input' );
        $fetchedFile = $inputType->fetch( 80, 8 );

        $this->assertInstanceOf( "eZBinaryFile", $fetchedFile,
            "Fetch should have returned a eZBinaryFile"
        );
    }

    public function testInitialize()
    {
        $file = eZBinaryFile::create( 90, 9 );
        $file->store();

        $inputType = new klpBcUploadVideoInputType( 'test_input' );
        $inputType->initialize( 90, 9, 10 );

        $fetchedFile = eZBinaryFile::fetch( 90, 10 );

        $this->assertInstanceOf( "eZBinaryFile", $fetchedFile,
            "Initialize should have created a new eZBinaryFile"
        );
    }

    public function testFilePath()
    {
        $file = eZBinaryFile::create( 43, 2 );
        $file->setAttribute( 'mime_type', 'video/mp4' );
        $file->setAttribute( 'filename', 'myvideo.mp4' );
        $file->store();

        $inputType = new klpBcUploadVideoInputType( 'test_input' );

        $this->assertStringEndsWith(
            "/storage/original/video/myvideo.mp4", $inputType->getFileUrl( 43, 2 ),
            "Incorrect file path"
        );
    }

    public function testRemoveOneVersion()
    {
        $file = eZBinaryFile::create( 34, 2 );
        $file->setAttribute( 'mime_type', 'video/mp4' );
        $file->setAttribute( 'filename', 'myvideo.mp4' );
        $file->store();

        $inputType = new klpBcUploadVideoInputType( 'test_input' );
        $inputType->delete( 34, 2 );

        $fetchedFile = eZBinaryFile::fetch( 34, 2 );

        $this->assertNull( $fetchedFile, "File should not longer exists" );
    }

    public function testFromString()
    {
        $tmpFileName = tempnam( sys_get_temp_dir(), "testFromString" ) . ".mp4";
        file_put_contents( $tmpFileName, "testfile" );
        $inputType = new klpBcUploadVideoInputType( 'test_input' );

        $result = $inputType->fromString( 431, 1, $tmpFileName );

        @unlink( $tmpFileName );
        $fetchedFile = eZBinaryFile::fetch( 431, 1 );

        $this->assertTrue( $result );

        $this->assertEquals(
            basename( $tmpFileName ),
            $fetchedFile->attribute( 'original_filename' ),
            "Expected original filename to be correct"
        );
    }

    public function testFromStringInvalidPath()
    {
        $inputType = new klpBcUploadVideoInputType( 'test_input' );
        $result = $inputType->fromString( 431, 1, "some invalid filename here" );

        $this->assertFalse( $result );
    }

    public function testGetFileInfo()
    {
        $inputType = new klpBcUploadVideoInputType( 'test_input' );

        $file = eZBinaryFile::create( 471, 1 );
        $file->setAttribute( 'mime_type', 'video/mp4' );
        $file->setAttribute( 'filename', 'somehash.mp4' );
        $file->setAttribute( 'original_filename', 'myvideo.mp4' );
        $file->store();

        $actual = $inputType->getFileInfo( 471, 1 );

        $this->assertEquals( 'video/mp4', $actual['mime_type'],
            "Expected mime type to be same"
        );
        $this->assertEquals( 'somehash.mp4', $actual['filename'],
            "Expected filename to be same"
        );
        $this->assertEquals( 'myvideo.mp4', $actual['original_filename'],
            "Expected original filename to be same"
        );
        $this->assertStringEndsWith(
            "storage/original/video/somehash.mp4", $actual['filepath'],
            "Expected file path to be the same"
        );
    }
}
