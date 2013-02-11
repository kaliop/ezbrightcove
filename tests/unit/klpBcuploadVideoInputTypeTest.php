<?php
/**
 * File containing the klpBcUploadVideoInputTypeUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcUploadVideoInputTypeUnitTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        parent::__construct();

        $this->uploadErrors = array(
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION
        );
    }

    public function setUp()
    {
        $this->input = new klpBcUploadVideoInputType( 'test_input' );
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

        $this->assertTrue(
            $this->input->isInputDataValid( array(), true, true ),
            "Empty input should be valid when is required but existing content=true"
        );

        $data = array( 'file' => array( 'error' => 0 ) );
        $this->assertTrue(
            $this->input->isInputDataValid( $data, false, false ),
            "No error with file upload should be valid"
        );

        $data = array( 'file' => array( 'error' => UPLOAD_ERR_NO_FILE ) );
        $this->assertFalse(
            $this->input->isInputDataValid( $data, true, false ),
            "No file and required input should be invalid"
        );

        $data = array( 'file' => array( 'error' => UPLOAD_ERR_NO_FILE ) );
        $this->assertTrue(
            $this->input->isInputDataValid( $data, false, false ),
            "No file and input is not requied should valid"
        );

        foreach( $this->uploadErrors as $error )
        {
            $data = array( 'file' => array( 'error' => $error ) );
            $this->assertFalse(
                $this->input->isInputDataValid( $data, false, false ),
                "Upload with error {$error} should be invalid"
            );
        }
    }

    public function testRequiresProcessing()
    {
        $this->assertTrue( $this->input->requiresProcessing() );
    }

    public function testCanDownload()
    {
        $this->assertTrue( $this->input->canDownload() );
    }
}
