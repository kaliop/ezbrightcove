<?php
/**
 * File containing the klpBcApiUpdateCronjob class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Cronjob that updates the meta data on Brightcove for a video
 */
class klpBcApiUpdateCronjob extends klpBcApiBaseCronjob
{
    /**
     * List of already updated brightcove video ids
     *
     * @var array
     **/
    protected $updatedBrightcoveIds = array();

    /**
     * Updates meta data for a video
     *
     * @param klpBcApi $api
     * @param klpBcQueue $queue
     * @param klpBcVideo $video
     */
    protected function processVideo( $api, $queue, $video )
    {
        $bId = $video->attribute( 'brightcove_id' );
        if ( !in_array( $bId, $this->updatedBrightcoveIds ) )
        {
            $meta = $this->dic->getVideoMeta();
            $api->update(
                $bId,
                $meta->getName( $video ),
                $meta->getDescription( $video )
            );

            $this->updatedBrightcoveIds[] = $bId;
        }

        if ( !$api->wasBusy() )
            $queue->move( $video, !$api->hasError(), $api->getLastError() );
    }

    /**
     * Fetches all videos ready for processing
     *
     * @return array List of pending videos
     */
    protected function fetchVideos()
    {
        return $this->dic->getQueue()->fetchPendingMetaUpdate();
    }
}
