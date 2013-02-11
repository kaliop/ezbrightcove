<?php
/**
 * File containing the klpBcTypeClassInputValidatorUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcTypeClassInputValidatorUnitTest extends PHPUnit_Framework_TestCase
{
    public function testIsValidTrue()
    {
        $http = $this->getMock('eZHTTPTool');
        $http->expects( $this->any() )
            ->method( 'hasPostVariable' )
            ->will( $this->returnValue( true ) );
        $http->expects( $this->any() )
             ->method( 'postVariable' )
             ->will( $this->returnValue( 'some value' ) );
        $validator = $this->validator( $http );
        $options = array( 'some_option' );

        $this->assertTrue( $validator->isValid( $options ), "Options should be valid" );
    }

    public function testIsValidFalseMissingPostVariable()
    {
        $http = $this->getMock('eZHTTPTool');
        $http->expects( $this->any() )
            ->method( 'hasPostVariable' )
            ->will( $this->returnValue( false ) );
        $http->expects( $this->any() )
             ->method( 'postVariable' )
             ->will( $this->returnValue( 'some value' ) );
        $validator = $this->validator( $http );
        $options = array( 'some_option' );

        $this->assertFalse( $validator->isValid( $options ), "Options should be not valid" );
    }

    public function testIsValidFalseNullValue()
    {
        $http = $this->getMock('eZHTTPTool');
        $http->expects( $this->any() )
            ->method( 'hasPostVariable' )
            ->will( $this->returnValue( true ) );
        $http->expects( $this->any() )
             ->method( 'postVariable' )
             ->will( $this->returnValue( null ) );
        $validator = $this->validator( $http );
        $options = array( 'some_option' );

        $this->assertFalse( $validator->isValid( $options ), "Options should be not valid" );
    }

    protected function validator( $http )
    {
        $validator = new klpBcTypeClassInputValidator(
            $http, "base", "klpbc", 12
        );

        return $validator;
    }
}

