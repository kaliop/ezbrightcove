<?php
/**
 * File containing the klpBcBrightcoveServerFunctions class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Handles ajax requests for data related to video input types
 **/
class klpBcBrightcoveServerFunctions
{
    /**
     * Fetches a paginated list of all videos from Brightcove
     *
     * @param array $args array( int <page to fetch> )
     * @param klpBcApi $bcApi Brightcove API instance
     * @return object Fetch result from Brightcove
     **/
    public function videos( $args, $bcApi )
    {
        $page = isset( $args[0] ) ? $args[0] : 0;
        $result = $bcApi->fetchAll( $page, 12 );

        return $result;
    }

    /**
     * Search videos using a search term
     *
     * @param array $args array( string <search term>, int <page to fetch>)
     * @param klpBcApi $bcApi Brightcove API instance
     * @return object List of videos from Brightcove
     **/
    public function search( $args, $bcApi )
    {
        $term = urldecode( $args[0] );
        $page = isset( $args[1] ) ? (int) $args[1] : 0;
        $result = $bcApi->search( $term, $page, 12 );

        return $result;
    }

    /**
     * Get a list of files and directories from a directory
     *
     * @param string $directory Path to directory
     * @param klpBcFileBrowser $browser
     * @param klpBcFileInfoFormatter $formatter
     * @return array List of files and directories
     **/
    public function files( $directory, $browser, $formatter )
    {
        return $browser->scan( $directory, $formatter );
    }
}
