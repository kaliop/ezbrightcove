<?php
/**
 * File containing the klpBcQueueUnitTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

require_once 'autoload.php';

class klpBcQueueUnitTest extends PHPUnit_Framework_TestCase
{
    public function testInsert()
    {
        $queue = new klpBcQueue( new klpDiContainer(
            array( "video.class" => 'klpBcVideo' )
        ) );

        $inputs = $this->queueInsertInputs();
        foreach( $inputs as $index => $input )
        {
            $video = $this->videoMock(
                $input['hasOriginal'],
                $input['requiresProcessing'],
                $input['state']
            );

            $video = $queue->insert( $video );

            $message = "({$index}) ";
            $message .= "Expected video with hasOriginal={$input['hasOriginal']}, ";
            $message .= "requiresProcessing={$input['requiresProcessing']}";
            $message .= "and state={$input['state']} ";
            $message .= "to result in state changing to {$input['result']}";

            $this->assertEquals(
                $input['result'], $video->attribute( 'state' ),
                $message
            );

            $message = "({$index}) ";
            $message .= "Expected video with hasOriginal={$input['hasOriginal']}, ";
            $message .= "requiresProcessing={$input['requiresProcessing']} ";
            $message .= "and state={$input['state']} ";
            $message .= "to result in need_meta_update changing to {$input['need_meta_update']}";

            $this->assertEquals(
                (int) $input['need_meta_update'], $video->attribute( 'need_meta_update' ),
                $message
            );
        }
    }

    public function testDelete()
    {
        $queue = new klpBcQueue( new klpDiContainer(
            array( "video.class" => 'klpBcVideo' )
        ) );

        $video = $this->getMock( 'stdClass', array( 'delete' ) );
        $video->expects( $this->any() )
              ->method( 'delete' )
              ->with( $this->equalTo( false ) );

        $queue->delete( $video, null );

        $video = $this->getMock( 'stdClass', array( 'delete' ) );
        $video->expects( $this->any() )
              ->method( 'delete' )
              ->with( $this->equalTo( true ) );
        $queue->delete( $video, 123 );
    }

    protected function videoMock( $hasOriginal, $requiresProcessing, $state )
    {
        $video = $this->getMockBuilder( 'klpBcVideo' )
                       ->setMethods( array( 'hasOriginalVideo',
                                            'requiresProcessing',
                                            'store' ) )
                      ->disableOriginalConstructor()
                      ->getMock();

        $video->expects( $this->any() )
              ->method( 'hasOriginalVideo' )
              ->will( $this->returnValue( $hasOriginal ) );

        $video->expects( $this->any() )
              ->method( 'requiresProcessing' )
              ->will( $this->returnValue( $requiresProcessing ) );

        $video->expects( $this->any() )
              ->method( 'store' );

        $video->setAttribute( 'state', $state );
        $video->setAttribute( 'need_meta_update', 0 );

        return $video;
    }

    protected function queueInsertInputs()
    {
        return array(
            array(
                "hasOriginal" => true,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_DRAFT,
                "result" => klpBcVideo::STATE_TO_PROCESS,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => false,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_DRAFT,
                "result" => klpBcVideo::STATE_DRAFT,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => true,
                "requiresProcessing" => false,
                "state" => klpBcVideo::STATE_DRAFT,
                "result" => klpBcVideo::STATE_COMPLETED,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => false,
                "requiresProcessing" => false,
                "state" => klpBcVideo::STATE_DRAFT,
                "result" => klpBcVideo::STATE_DRAFT,
                "need_meta_update" => false,
            ),

            array(
                "hasOriginal" => true,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_FAILED,
                "result" => klpBcVideo::STATE_FAILED,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => true,
                "requiresProcessing" => false,
                "state" => klpBcVideo::STATE_FAILED,
                "result" => klpBcVideo::STATE_FAILED,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => false,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_FAILED,
                "result" => klpBcVideo::STATE_FAILED,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => false,
                "requiresProcessing" => false,
                "state" => klpBcVideo::STATE_FAILED,
                "result" => klpBcVideo::STATE_FAILED,
                "need_meta_update" => false,
            ),
            // need meta use cases
            array(
                "hasOriginal" => true,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_COMPLETED,
                "result" => klpBcVideo::STATE_COMPLETED,
                "need_meta_update" => true,
            ),
            array(
                "hasOriginal" => true,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_PROCESSING,
                "result" => klpBcVideo::STATE_PROCESSING,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => false,
                "requiresProcessing" => true,
                "state" => klpBcVideo::STATE_PROCESSING,
                "result" => klpBcVideo::STATE_PROCESSING,
                "need_meta_update" => false,
            ),
            array(
                "hasOriginal" => true,
                "requiresProcessing" => false,
                "state" => klpBcVideo::STATE_COMPLETED,
                "result" => klpBcVideo::STATE_COMPLETED,
                "need_meta_update" => false,
            ),
        );
    }
}
