<?php
/**
 * File containing the klpBcApiStatusCronjob class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Cronjob that sends any videos pending processing to Brightcove for
 * processing.
 */
class klpBcApiStatusCronjob extends klpBcApiBaseCronjob
{
    /**
     * Checks and updates the status of a video that's been already sent to 
     * Brightcove
     *
     * @param klpBcApi $api
     * @param klpBcQueue $queue
     * @param klpBcVideo $video
     */
    protected function processVideo( $api, $queue, $video )
    {
        $isComplete = $api->isComplete( $video->attribute( 'brightcove_id' ) );

        if ( $isComplete )
        {
            if ( !$api->wasBusy() )
                $queue->move( $video, !$api->hasError(), $api->getLastError() );

            $this->clearCache( $video );
        }
    }

    /**
     * Fetches all videos that have been sent to processing
     *
     * @return array List of videos
     */
    protected function fetchVideos()
    {
        return $this->dic->getQueue()->fetchProcessing();
    }
}
