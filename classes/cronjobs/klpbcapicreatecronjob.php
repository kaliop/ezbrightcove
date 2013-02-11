<?php
/**
 * File containing the klpBcApiCreateCronjob class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Cronjob that sends any videos pending processing to Brightcove for
 * processing.
 */
class klpBcApiCreateCronjob extends klpBcApiBaseCronjob
{
    /**
     * Sends an individual video for processing
     *
     * @param klpBcApi $api
     * @param klpBcQueue $queue
     * @param klpBcVideo $video
     */
    protected function processVideo( $api, $queue, $video )
    {
        $hasCreatedVideo = false;
        $brightcoveId = false;
        $videos = $video->fetchVersions();

        foreach( $videos as $video )
        {
            if ( $hasCreatedVideo === false )
            {
                $meta = $this->dic->getVideoMeta();
                $brightcoveId = $api->create(
                    $video->getFileUrl(),
                    $meta->getName( $video ),
                    $meta->getDescription( $video )
                );

                $hasCreatedVideo = true;
                $this->clearCache( $video );
            }

            if ( $brightcoveId )
                $video->setAttribute( 'brightcove_id', $brightcoveId );

            if ( !$api->wasBusy() )
                $queue->move( $video, !$api->hasError(), $api->getLastError() );
        }
    }

    /**
     * Fetches all videos ready for processing
     *
     * @return array List of pending videos
     */
    protected function fetchVideos()
    {
        return $this->dic->getQueue()->fetchPendingProcessing();
    }
}
