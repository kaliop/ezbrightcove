<?php
/**
 * File containing the klpBcVideoUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcVideoUnitTest extends PHPUnit_Framework_TestCase
{
    public function testHasOriginalVideo()
    {
        $this->assertTrue( $this->videoMock( true )->hasOriginalVideo(),
            "Video should have original video"
        );

        $this->assertFalse( $this->videoMock( false )->hasOriginalVideo(),
            "Video should not have original video"
        );
    }

    public function testGetStateValue()
    {
        $video = $this->videoMockBuilder()->getMock();

        $this->assertSame(
            klpBcVideo::STATE_DRAFT, $video->getStateValue( "DRAFT"),
            "Expected value of DRAFT to be same as klpBcVideo::STATE_DRAFT"
        );

        $this->assertSame(
            klpBcVideo::STATE_DRAFT, $video->getStateValue( "draft"),
            "Expected value of draft to be same as klpBcVideo::STATE_DRAFT"
        );
    }

    public function testIsInState()
    {
        $video = $this->videoMockBuilder()->getMock();

        $video->setAttribute( 'state', $video->getStateValue( "DRAFT" ) );
        $this->assertTrue( $video->isInState( 'DRAFT' ),
            "Expected video to be in draft state"
        );

        $video->setAttribute( 'state', $video->getStateValue( "FAILED" ) );
        $this->assertTrue( $video->isInState( 'FAILED' ),
            "Expected video to be in failed state"
        );
    }

    public function testIsCompleted()
    {
        $video = $this->videoMockBuilder()->getMock();
        $video->setAttribute( 'state', $video->getStateValue( "COMPLETED" ) );

        $this->assertTrue( $video->isCompleted(), "Expected video to be completed" );
    }

    public function testHasError()
    {
        $video = $this->videoMockBuilder()->getMock();
        $video->setAttribute( 'state', $video->getStateValue( "FAILED" ) );

        $this->assertTrue( $video->hasError(), "Expected video to have error" );
    }

    protected function videoMock( $hasOriginalVideo )
    {
        $video = $this->videoMockBuilder()
                      ->setMethods( array( 'originalVideo' ) )
                      ->getMock();

        if ( $hasOriginalVideo )
        {
            $video->expects( $this->once() )
                  ->method( 'originalVideo' )
                  ->will( $this->returnValue( new stdClass() ) );
        }
        else
        {
            $video->expects( $this->once() )
                  ->method( 'originalVideo' )
                  ->will( $this->returnValue( null ) );
        }

        return $video;
    }

    protected function videoMockBuilder()
    {
        $video = $this->getMockBuilder( 'klpBcVideo' )
                      ->setMethods( array( '__construct' ) )
                      ->disableOriginalConstructor();

        return $video;
    }
}

