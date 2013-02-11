<?php
/**
 * File containing the klpBcFileBrowserUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcFileBrowserUnitTest extends PHPUnit_Framework_TestCase
{
    public function testScanWithFiles()
    {
        $this->createFiles( $this->rootDir() );

        $formatter = $this->getFormatter();

        $browser = new klpBcFileBrowser();
        $result = $browser->scan( $this->rootDir(), $formatter );

        $this->removeFiles( $this->rootDir() );

        $this->assertCount( 6, $result );
        
        $this->assertArrayHasKey( 'filename', $result[0] );     
        $this->assertArrayHasKey( 'children', $result[0] );     
        $this->assertCount( 3, $result[0]['children'] );     

        // Assert /c/d/e/{3 files}
        $this->assertArrayHasKey(
            'children', $result[3], "Dir c has children"
        );
        $this->assertArrayHasKey(
            'children', $result[3]['children'][0], "Dir D has children"
        );
        $this->assertArrayHasKey(
            'children', $result[3]['children'][0]['children'][0], "Dir E has children"
        );
        $this->assertCount(
            3, $result[3]['children'][0]['children'][0]['children'],
            "Dir E has 3 children"
        );
    }

    public function testScanRootDirDontExists()
    {
        $formatter = $this->getFormatter();

        $browser = new klpBcFileBrowser();
        $result = $browser->scan( $this->rootDir(), $formatter );

        $this->assertContains( "No such file or directory", $result );
    }

    public function testScanRootDirEmpty()
    {
        mkdir( $this->rootDir(), 0777, true );
        $formatter = $this->getFormatter();

        $browser = new klpBcFileBrowser();
        $result = $browser->scan( $this->rootDir(), $formatter );

        $this->removeFiles( $this->rootDir() );

        $this->assertEmpty( $result );
    }

    public function testScanPermissionDenied()
    {
        mkdir( $this->rootDir(), 0000, true );
        $formatter = $this->getFormatter();

        $browser = new klpBcFileBrowser();
        $result = $browser->scan( $this->rootDir(), $formatter );

        chmod( $this->rootDir(), 0777 );
        $this->removeFiles( $this->rootDir() );

        $this->assertContains( "Permission denied", $result );
    }

    protected function rootDir()
    {
        $rootDir = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . "klpbcfilebrowser";

        return $rootDir;
    }

    protected function createFiles( $rootDir )
    {
        $dirs = array();
        foreach( array( "b", "a", "c/d/e" ) as $dir )
        {
            $dir = $rootDir . DIRECTORY_SEPARATOR . $dir;
            $dirs[] = $dir;
            @mkdir( $dir, 0777, true );
        }

        $files = array( "afile3.mp4", "file2.avi", "file1.txt" );
        foreach( $files as $file )
        {
            $filepath = $rootDir . DIRECTORY_SEPARATOR . $file;
            file_put_contents( $filepath, $file );

            foreach( $dirs as $dir )
            {
                $filepath = $dir . DIRECTORY_SEPARATOR . $file;
                file_put_contents( $filepath, $file );
            }
        }
    }

    protected function removeFiles( $rootDir )
    {
        ezcBaseFile::removeRecursive( $rootDir );
    }

    protected function getFormatter()
    {
        return new klpBcFileInfoFormatter( '/' );
    }
}
