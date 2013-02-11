<?php
/**
 * File containing the klpBcApi class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Class communicating with the Brightcove API
 *
 * Supported operations are:
 * - create(): create and upload a new video
 * - update(): update name and description of existing video
 * - isComplete(): checks if video processing is complete
 * - getStatus(): fetches the current video status
 * - delete(): deletes a video on brightcove
 */
class klpBcApi
{
    /**
     * Brightcove error for when there's too many requests
     *
     * @var int
     */
    const MAX_CONCURRENT_REQUEST_CODE = 213;

    /**
     * Brightcove API read token
     *
     * @var string
     */
    public $readToken;

    /**
     * Brightcove API write token
     *
     * @var string
     */
    public $writeToken;

    /**
     * Brightcove API internal instance
     *
     * @var string
     */
    public $internalApi;

    /**
     * Indicates if brightcove was not able to process our request
     *
     * @var bool
     */
    protected $wasBusy = false;

    /**
     * Error message from last request (if any)
     *
     * @var string
     */
    protected $lastError = null;

    /**
     * Creates a new instance of this class
     *
     * @param string $readToken The Brightcove API read token
     * @param string $readToken The Brightcove API write token
     */
    public function __construct( $readToken = null, $writeToken = null )
    {
        $this->readToken = $readToken;
        $this->writeToken = $writeToken;

        $this->internalApi = new BCMAPI( $readToken, $writeToken );
    }

    /**
     * Fetches all videos paginated by $page and $limit
     *
     * @param int $page Which page to fetch
     * @param int $limit How many videos per page
     * @param object An object containing all API return data and pagination
     *               data and pagination data
     **/
    public function fetchAll( $page = 0, $limit = 20 )
    {
        $videos = $this->runApiMethod( 'find', 'find_all_videos',
            array( 'page_number' => $page, 'page_size' => $limit )
        );

        return $this->createPaginatedResponse( $videos );
    }

    /**
     * Searches videos paginated by $page and $limit
     *
     * @param string $term String to search library of videos for
     * @param int $page Which page to fetch
     * @param int $limit How many videos per page
     * @param object An object containing all API return data and pagination
     *               data and pagination data
     **/
    public function search( $term, $page = 0, $limit = 20 )
    {
        $videos = $this->runApiMethod( 'search', 'video', 
            array( 'any' => $term ),
            array(
                'page_number' => $page,
                'page_size' => $limit
            )
        );

        return $this->createPaginatedResponse( $videos );
    }

    /**
     * Creates and uploads a new video
     *
     * @param string $filePath Path to file
     * @param string $name Name of the video
     * @param string $description Short description of the video
     * @return int Brightcove id
     */
    public function create( $filePath, $name, $description )
    {
        $meta = array(
            'name'=> $name,
            'shortDescription' => $description
        );
        $options = array( 'encode_to' => 'MP4' );

        return $this->runApiMethod( 'createMedia',
            'video', $filePath, $meta, $options
        );
    }

    /**
     * Updates the name and description of existing video
     *
     * @param int $brightcoveId Brightcove video id
     * @param string $name Name of the video
     * @param string $description Short description of the video
     */
    public function update( $brightcoveId, $name, $description )
    {
        $meta = array(
            'id' => $brightcoveId,
            'name' => $name,
            'shortDescription' => $description
        );

        return $this->runApiMethod( 'update', 'video', $meta );
    }

    /**
     * Returns true if video is marked as "COMPLETE" in Brightcove
     *
     * @param int $brightcoveId Brightcove video id
     * @return bool
     */
    public function isComplete( $brightcoveId )
    {
        return $this->getStatus( $brightcoveId ) == 'COMPLETE' ? true : false;
    }

    /**
     * Returns the current status for a video
     *
     * @param int $brightcoveId Brightcove video id
     * @return string Current status
     */
    public function getStatus( $brightcoveId )
    {
        return $this->runApiMethod( 'getStatus', 'video', $brightcoveId );
    }

    /**
     * Deletes a video on brightcove
     *
     * @param int $brightcoveId Brightcove video id
     */
    public function delete( $brightcoveId )
    {
        return $this->internalApi->delete( 'video', $brightcoveId );
    }

    /**
     * Returns true if Brightcove was too busy to process our request
     *
     * @return bool
     */
    public function wasBusy()
    {
        return $this->wasBusy;
    }

    /**
     * Returns true if there was an error with previous request
     *
     * @return bool
     */
    public function hasError()
    {
        return !empty( $this->lastError );
    }

    /**
     * Returns the error message from previous request (if any)
     *
     * @return null|string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Runs a method on the internal api
     *
     * This method takes a variable number or arguments. Any arguments after
     * $method will be passed on to the internal API.
     *
     * @param string $method Name of method to run
     * @return mixed Return value of the api method
     */
    protected function runApiMethod( $method /* ... */ )
    {
        $args = func_get_args();
        array_shift( $args ); // Get rid of first arg which is $method

        try
        {
            $this->wasBusy = false;
            $this->lastError = null;

            return call_user_func_array(
                array( $this->internalApi, $method ), $args
            );
        }
        catch ( BCMAPIException $exception )
        {
            if ( $exception->getCode() == self::MAX_CONCURRENT_REQUEST_CODE )
                $this->wasBusy = true;

            $this->lastError = $exception->getMessage();
        }
    }

    /**
     * Creates paginated response object
     *
     * The returned object has the following properties:
     * object->videos
     * object->page_number
     * object->page_size
     * object->total_count
     *
     * @param array $videos List of videos
     * @return stdObject
     **/
    protected function createPaginatedResponse( $videos )
    {
        $result = new stdClass();
        $result->videos = $videos;
        $result->page_number = $this->internalApi->page_number;
        $result->page_size = $this->internalApi->page_size;
        $result->total_count = $this->internalApi->total_count;

        return $result;
    }
}
