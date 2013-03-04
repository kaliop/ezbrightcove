<?php
/**
 * File containing the klpBcQueue class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Manages the list of videos that needs to be processed by Brightcove.
 *
 * There's currently only one queue.
 *
 * Example of how to add a new video to the processing queue:
 * <code>
 * $queue = new klpBcQueue();
 * $queue->insert( $video );
 * $queue->size(); # => 1
 * # If video was successfully processed:
 * $queue->move( $video, true );
 * # else
 * $queue->move( $video, false );
 *
 * # Time to delete video
 * $queue->delete( $video );
 * </code>
 */
class klpBcQueue
{
    /**
     * Creates a new instance of this class
     *
     * @param klpBcDiContainer $dic Dependency injection container
     */
    public function __construct( $dic )
    {
        $this->dic = $dic;
    }

    /**
     * Inserts a new video into the queue
     *
     * In order for the video to be added to the queue it must be:
     * - in "DRAFT" status
     * - have an source/original video
     * - require processing (that is the video has an input type that requires
     *    processing)
     *
     * If the video meets all the above criteria but does not require
     * processing it will be moved to the "COMPLETED" state.
     *
     * @param klpBcVideo $video A video object
     * @return klpBcVideo The video same video but with updated state
     */
    public function insert( $video )
    {
        // Set state
        if ( $video->hasOriginalVideo() && $video->isInState( "DRAFT" ) )
        {
            if ( $video->requiresProcessing() )
                $video->setAttribute( 'state', $video->getStateValue( 'TO_PROCESS' ) );
            else
                $video->setAttribute( 'state', $video->getStateValue( 'COMPLETED' ) );

            $video->store();
        }

        // Set need meta update
        $video->setAttribute( 'need_meta_update', 0 );
        if ( $video->hasOriginalVideo() && $video->requiresProcessing() )
        {
            if ( $video->isInState( "COMPLETED" ) )
            {
                $video->setAttribute( 'need_meta_update', 1 );
                $video->store();
            }

        }

        return $video;
    }

    /**
     * Moves a video to the starting point/state (DRAFT)
     *
     * @param klpBcVideo $video A video object
     * @return klpBcVideo The video same video but with updated state
     **/
    public function moveToStart( $video )
    {
        $video->setAttribute( 'state', $video->getStateValue( "DRAFT" ) );
        $video->store();

        return $video;
    }

    /**
     * Moves the video to the next appropriate state
     *
     * If $isError is false then an error state will be set
     * using $message as the error message.
     *
     * @param klpBcVideo $video A video object
     * @param bool $isError
     * @param string $message Any error message
     */
    public function move( $video, $successful, $message = null )
    {
        if ( $successful )
        {
            $this->moveForward( $video );
        }
        elseif ( !$video->needMetaUpdate() )
        {
            $this->moveToFailed( $video, $message );
        }
    }

    /**
     * Empties the queue, use with caution!
     */
    public function clear()
    {
        return $this->runVideoMethod( 'removeAll' );
    }

    /**
     * Deletes a video from the queue.
     *
     * If $version is set to null all versions of the video is deleted.
     *
     * @param klpBcVideo $video A video object
     * @param int $version Video version. Null means delete all versions.
     **/
    public function delete( $video, $version = null )
    {
        $removeCurrentVersion = is_null( $version ) ? false : true;
        return $video->delete( $removeCurrentVersion );
    }

    /**
     * Returns the size of the queue
     *
     * @return int Number of items in the queue
     */
    public function size()
    {
        return $this->runVideoMethod( 'fetchCount' );
    }

    /**
     * Returns a list of videos that is pending processing
     *
     * @return array List of pending processing videos
     */
    public function fetchPendingProcessing()
    {
        return $this->runVideoMethod(
            'fetchByState', array( 'TO_PROCESS' )
        );
    }

    /**
     * Returns a list of videos that is processing
     *
     * @return array List of videos being processed
     */
    public function fetchProcessing()
    {
        return $this->runVideoMethod(
            'fetchByState', array( 'PROCESSING' )
        );
    }

    /**
     * Returns a list of videos that is processing
     *
     * @return array List of videos being processed
     */
    public function fetchPendingMetaUpdate()
    {
        return $this->runVideoMethod( 'fetchPendingMetaUpdate' );
    }

    /**
     * Returns a list of videos that pending deletion
     *
     * @return array List of videos pending deletion
     */
    public function fetchPendingDeletion()
    {
        return $this->runVideoMethod(
            'fetchByState', array( 'TO_DELETE' )
        );
    }

    /**
     * Moves the state of a video forward if possible
     *
     * @param klpBcVideo $video A video object
     * @return bool True when state changed, false if it didn't
     */
    protected function moveForward( $video )
    {
        $state = (int) $video->attribute( 'state' );
        $newState = $state;

        if ( $state === $video->getStateValue( "TO_PROCESS" ) )
            $newState = $video->getStateValue( "PROCESSING" );

        if ( $state === $video->getStateValue( "PROCESSING" ) )
            $newState = $video->getStateValue( "COMPLETED" );

        if ( $state === $video->getStateValue( "TO_DELETE" ) )
        {
            $video->remove();
            return true;
        }

        if ( $state !== $newState || $video->needMetaUpdate() )
        {
            $video->setAttribute( 'need_meta_update', 0 );
            $video->setAttribute( 'state', $newState );
            $video->store();
        }

        return ( $state !== $newState );
    }

    /**
     * Changes the state of a video to "Failed"
     *
     * @param klpBcVideo $video A video object
     * @param string $message Error message
     */
    protected function moveToFailed( $video, $message = null )
    {
        if ( $message )
            $video->setAttribute( 'error_log', $message );

        $video->setAttribute( 'state', $video->getStateValue( "FAILED" ) );
        $video->store();
    }

    /**
     * Runs an arbitrary method on the video.class defined in the DI container
     *
     * @param string $method Name of the method to run
     * @param array $arguments Optionally any arguments for the method
     * @return mixed Return value of the method call
     */
    protected function runVideoMethod( $method, $arguments = null )
    {
        $call = array( $this->dic->{'video.class'}, $method );

        if ( $arguments )
            return call_user_func_array( $call, $arguments );
        else
            return call_user_func( $call );
    }
}
