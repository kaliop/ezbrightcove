<?php
/**
 * File containing the klpDiContainerUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpDiContainerUnitTest extends PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $params = array( "dummy.class" => __CLASS__ );
        $dic = new klpDiContainer( $params );

        $this->assertEquals( $params, $dic->parameters,
            "Parameters was not set correctly"
        );
    }

    public function testSetParameters()
    {
        $dic = new klpDiContainer( array() );
        $dic->parameters = array( "dummy.class" => __CLASS__ );

        $this->assertEquals( array( "dummy.class" => __CLASS__ ), $dic->parameters,
            "Parameters was not updated correctly"
        );
    }

    public function testUpdateParameters()
    {
        $dic = new klpDiContainer( array( 'dummy.class' => "Some class" ) );
        $dic->{'dummy.class'} = __CLASS__;

        $this->assertEquals( array( "dummy.class" => __CLASS__ ), $dic->parameters,
            "Parameters was not updated correctly"
        );
    }

    public function testUpdateSharedInstances()
    {
        $dic = new klpDiContainer( array( 'dummy.instance' => "Some instance" ) );
        $dic->{'dummy.instance'} = $this;

        $this->assertEquals( $this, $dic->{'dummy.instance'},
            "Shared parameters was not updated correctly"
        );
    }

    public function testGetInstance()
    {
        $dic = new klpDiContainer( array() );
        klpDiContainer::setInstance( $dic );

        $dic2 = klpDiContainer::getInstance();

        $this->assertInstanceOf( 'klpDiContainer', $dic2,
            "Incorrect instance type"
        );

        $this->assertEquals( $dic, $dic2,
            "Both instances should be the same"
        );
    }

    public function testGetTypeOptions()
    {
        $params = array( "typeoptions.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getTypeOptions();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetTypeClassInputValidator()
    {
        $params = array( "typeclassinputvalidator.class" => 'klpBcTypeClassInputValidator' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getTypeClassInputValidator(
            null, null, null, null
        );

        $this->assertInstanceOf( 'klpBcTypeClassInputValidator', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetVideoInput()
    {
        $params = array( "videoinput.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getVideoInput();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetVideo()
    {
        $params = array( "video.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getVideo();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetVideoMeta()
    {
        $params = array( "videometa.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getVideoMeta();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetQueue()
    {
        $params = array( "queue.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getQueue();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetBcApi()
    {
        $params = array( "bcapi.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getBcApi();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetInternalBcApi()
    {
        $params = array( "internalbcapi.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getInternalBcApi();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetFileBrowser()
    {
        $params = array( "filebrowser.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getFileBrowser();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetFileInfoFormatter()
    {
        $params = array( "fileinfoformatter.class" => 'klpBcFileInfoFormatter' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getFileInfoFormatter( null );

        $this->assertInstanceOf( 'klpBcFileInfoFormatter', $instance,
            "Incorrect instance type"
        );
    }

    public function testGetServerFileConfig()
    {
        $params = array( "serverfileconfig.class" => 'stdClass' );
        $dic = new klpDiContainer( $params );

        $instance = $dic->getServerFileConfig();

        $this->assertInstanceOf( 'stdClass', $instance,
            "Incorrect instance type"
        );
    }
}
