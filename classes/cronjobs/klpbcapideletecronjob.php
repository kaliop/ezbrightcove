<?php
/**
 * File containing the klpBcApiDeleteCronjob class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Cronjob that deletes any videos pending deletion
 */
class klpBcApiDeleteCronjob extends klpBcApiBaseCronjob
{
    /**
     * Holds brightcove ids that we've already deleted from Brightcove
     *
     * @var array
     **/
    protected $deletedBrightcoveIds = array();

    /**
     * Deletes any videos pending deletion
     *
     * Each brightcove id will only be deleted from brightcove once should
     * there be duplicate brightcove ids.
     *
     * @param klpBcApi $api
     * @param klpBcQueue $queue
     * @param klpBcVideo $video
     */
    protected function processVideo( $api, $queue, $video )
    {
        $bId = $video->attribute( 'brightcove_id' );

        if ( !$bId )
            return $queue->move( $video, true );

        if ( in_array( $bId, $this->deletedBrightcoveIds ) )
            return $queue->move( $video, true );

        $api->delete( $bId );

        if ( !$api->wasBusy() )
            $queue->move( $video, !$api->hasError(), $api->getLastError() );

        $this->deletedBrightcoveIds[] = $bId;
    }

    /**
     * Fetches all videos that are pending deletion
     *
     * @return array List of videos
     */
    protected function fetchVideos()
    {
        return $this->dic->getQueue()->fetchPendingDeletion();
    }
}
