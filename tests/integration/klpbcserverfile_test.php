<?php
/**
 * File containing the klpBcServerFileTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcServerFileTest extends klpBcTestCase
{
    public function testNewAndStore()
    {
        $file = new klpBcServerFile( false );
        $file->setAttribute( 'contentobject_attribute_id', 428 );
        $file->setAttribute( 'version', 1 );
        $file->setAttribute( 'filepath', 'http://www.example.com' );
        $file->store();

        $fetchedFile = klpBcServerFile::fetch( 428, 1 );

        $this->assertEquals( 428, $fetchedFile->attribute( 'contentobject_attribute_id' ),
            "Content object attribute id did not match"
        );
        $this->assertEquals( 1, $fetchedFile->attribute( 'version' ),
            "Version did not match"
        );
        $this->assertEquals( "http://www.example.com", $fetchedFile->attribute( 'filepath' ),
            "filepath did not match"
        );
    }

    public function testChangingVersionStoresNewCopy()
    {
        $file = new klpBcServerFile( false );
        $file->setAttribute( 'contentobject_attribute_id', 418 );
        $file->setAttribute( 'version', 1 );
        $file->setAttribute( 'filepath', 'version1' );
        $file->store();

        $file->setAttribute( 'version', 2 );
        $file->setAttribute( 'filepath', 'version2' );
        $file->store();

        $fetchedFile1 = klpBcServerFile::fetch( 418, 1 );
        $fetchedFile2 = klpBcServerFile::fetch( 418, 2 );

        $this->assertEquals( 'version1', $fetchedFile1->attribute( 'filepath' ),
            "File path of version 1 didn't match"
        );

        $this->assertEquals( 'version2', $fetchedFile2->attribute( 'filepath' ),
            "File path of version 2 didn't match"
        );
    }

    public function testDeleteOneVersion()
    {
        $file = new klpBcServerFile( false );
        $file->setAttribute( 'contentobject_attribute_id', 345 );
        $file->setAttribute( 'version', 1 );
        $file->store();

        $file->setAttribute( 'version', 2 );
        $file->store();

        $file2 = new klpBcServerFile( false );
        $file2->setAttribute( 'contentobject_attribute_id', 294 );
        $file2->setAttribute( 'version', 1 );
        $file2->store();

        klpBcServerFile::delete( 345, 1 );

        $fetchedFileV1 = klpBcServerFile::fetch( 345, 1 );
        $fetchedFileV2 = klpBcServerFile::fetch( 345, 2 );
        $fetchedFile2 = klpBcServerFile::fetch( 294, 1 );

        $this->assertNull( $fetchedFileV1, "Expected server file v1 to be deleted" );
        $this->assertNotNull( $fetchedFileV2, "Expected server file v2 to not be deleted" );
        $this->assertNotNull( $fetchedFile2, "Expected second server file to not be deleted" );
    }

    public function testDeleteAllVersions()
    {
        $file = new klpBcServerFile( false );
        $file->setAttribute( 'contentobject_attribute_id', 194 );
        $file->setAttribute( 'version', 1 );
        $file->store();

        $file->setAttribute( 'version', 2 );
        $file->store();

        $file2 = new klpBcServerFile( false );
        $file2->setAttribute( 'contentobject_attribute_id', 294 );
        $file2->setAttribute( 'version', 1 );
        $file2->store();

        klpBcServerFile::delete( 194, null );

        $fetchedFileV1 = klpBcServerFile::fetch( 194, 1 );
        $fetchedFileV2 = klpBcServerFile::fetch( 194, 2 );
        $fetchedFile2 = klpBcServerFile::fetch( 294, 1 );

        $this->assertNull( $fetchedFileV1, "Expected server file v1 to be deleted" );
        $this->assertNull( $fetchedFileV2, "Expected server file v2 to not be deleted" );
        $this->assertNotNull( $fetchedFile2, "Expected second server file to not be deleted" );
    }
}
