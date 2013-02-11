<?php
/**
 * File containing the klpBcTypeOptionsUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once( 'autoload.php' );

class klpBcTypeOptionsUnitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->options = new klpBcTypeOptions(
            array( 'maxVideoSize' ), array( 'maxVideoSize' )
        );
    }

    public function testSet()
    {
        $this->options->maxVideoSize = 500;
        $this->assertEquals( 500, $this->options->maxVideoSize,
            "Video length wasn't set properly"
        );
    }

    public function testIsset()
    {
        $this->assertFalse( isset( $this->options->maxVideoSize ),
            "Video length should not be set"
        );

        $this->options->maxVideoSize = 500;

        $this->assertTrue( isset( $this->options->maxVideoSize ),
            "Video length should be set"
        );
    }

    /**
     * @expectedException ezcBasePropertyNotFoundException
     */
    public function testSetUnknownProperty()
    {
        $this->options->sdkfskdjhf = 500;
    }

    /**
     * @expectedException ezcBasePropertyNotFoundException
     */
    public function testGetUnknownProperty()
    {
        $var = $this->options->sdkfskdjhf;
    }

    /**
     * @expectedException ezcBasePropertyNotFoundException
     */
    public function testIssetUnknownProperty()
    {
        isset( $this->options->sdkfskdjhf );
    }

    public function testToJson()
    {
        $this->options->maxVideoSize = 500;
        $serialized = $this->options->toJson();

        $options = new klpBcTypeOptions( array( 'maxVideoSize' ) );
        $options->fromJson( $serialized );

        $this->assertEquals( 500, $options->maxVideoSize, "Options not serialized correctly" );
    }

    public function testToXml()
    {
        $options = new klpBcTypeOptions( array( "testOption1" ) );
        $options->testOption1 = "<test value>";

        $expected = new DOMDocument();
        $expected->loadXml(
            "<root><testOption1>&lt;test value&gt;</testOption1></root>"
        );

        $actual = new DOMDocument();
        $actual->loadXML( '<root/>' );
        $options->toXml( $actual->firstChild );

        $this->assertXmlStringEqualsXmlString(
            $expected->saveXML(), $actual->saveXML()
        );
    }

    public function testFromXml()
    {
        $options = new klpBcTypeOptions(
            array( "testOption2", "optionNotInXml" )
        );

        $dom = new DOMDocument();
        $dom->loadXml(
            "<root><testOption2>test value</testOption2></root>"
        );

        $options->fromXml( $dom->firstChild );
        $this->assertEquals( "test value", $options->testOption2 );
        $this->assertFalse( isset( $options->optionNotInXml ) );
    }
}
