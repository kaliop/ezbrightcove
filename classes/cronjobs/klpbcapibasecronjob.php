<?php
/**
 * File containing the klpBcApiBaseCronjob class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Base class for all Brightcove API related cronjobs
 *
 * This base class provides common functionality for api cronjob such as
 * hook for fetching videos and hook for processing individual videos.
 **/
abstract class klpBcApiBaseCronjob
{
    /**
     * Creates a new instance of this class
     *
     * @param klpDiContainer $dic Dependency injection container
     */
    public function __construct( $dic )
    {
        $this->dic = $dic;
    }

    /**
     * Kicks of the process of sending videos to brightcove for processing
     */
    public function process()
    {
        $api = $this->dic->getBcApi();
        $queue = $this->dic->getQueue();
        $videos = $this->fetchVideos();

        foreach( $videos as $video )
            $this->processVideo( $api, $queue, $video );
    }

    /**
     * Sends an individual video for processing
     *
     * @param klpBcVideo $video
     */
    abstract protected function processVideo( $api, $queue, $video );

    /**
     * Fetches all videos ready for processing
     */
    abstract protected function fetchVideos();

    /**
     * Clears the cache for the video's attribute's object.
     *
     * @param klpBcVideo $video
     **/
    protected function clearCache( $video )
    {
        $attribute = eZContentObjectAttribute::fetch(
            $video->attribute( 'contentobject_attribute_id' ),
            $video->attribute( 'version' )
        );

        if ( $attribute )
        {
            eZContentCacheManager::clearContentCacheIfNeeded(
                $attribute->attribute( 'contentobject_id' )
            );
        }
    }
}
