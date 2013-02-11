<?php
/**
 * File containing the klpBcVideoInputUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcVideoInputUnitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->inputs = new klpBcVideoInput();
    }

    public function testRegisterInput()
    {
        $input = (object) array( "testing" );
        $this->inputs->registerInputType( "test_input", $input );

        $this->assertEquals( 1, count( $this->inputs->inputTypes() ) );
    }

    public function testGetInput()
    {
        $input = (object) array( "testing" );
        $this->inputs->registerInputType( "test_input", $input );

        $this->assertEquals( $input, $this->inputs->getInput( "test_input" ) );
    }

    public function testMakeAvailable()
    {
        $input = (object) array( "testing" );
        $this->inputs->registerInputType( "test_input", $input );

        $this->inputs->makeAvailable( "test_input" );

        $this->assertEquals( 1, count( $this->inputs->getAvailable() ) );
    }

    public function testMakeUnavailable()
    {
        $input = (object) array( "testing" );
        $this->inputs->registerInputType( "test_input", $input );
        $this->inputs->registerInputType( "test_input2", $input );

        $this->inputs->makeAvailable( "test_input" );
        $this->inputs->makeUnavailable( "test_input" );

        $this->assertEquals( 0, count( $this->inputs->getAvailable() ) );
    }
}
