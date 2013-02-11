<?php
/**
 * File containing the klpBcBrightcoveVideoInputTypeUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcBrightcoveVideoInputTypeUnitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->input = new klpBcBrightcoveVideoInputType( 'test_input' );
    }

    public function testIsInputDataValid()
    {
        $this->assertFalse(
            $this->input->isInputDataValid( array(), true, false ),
            "Empty input should not be valid when input is required"
        );

        $this->assertTrue(
            $this->input->isInputDataValid( array(), false, false ),
            "Empty input should be valid when input is not required"
        );

        $this->assertFalse(
            $this->input->isInputDataValid( array(), true, true ),
            "Empty input should not be valid when is required and existing content=true"
        );

        $data = array( 'brightcove_id' => "" );
        $this->assertFalse(
            $this->input->isInputDataValid( $data, true, false ),
            "No brightcove id in input should not be valid when input is required"
        );

        $data = array( 'brightcove_id' => "" );
        $this->assertTrue(
            $this->input->isInputDataValid( $data, false, false ),
            "No brightcove id in input should be valid when input is not required"
        );

        $data = array( 'brightcove_id' => "123192873" );
        $this->assertTrue(
            $this->input->isInputDataValid( $data, false, false ),
            "Input with brightcove id should be valid"
        );
    }

    public function testRequiresProcessing()
    {
        $this->assertFalse( $this->input->requiresProcessing() );
    }

    public function testCanDownload()
    {
        $this->assertFalse( $this->input->canDownload() );
    }
}
