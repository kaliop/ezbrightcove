<?php
/**
 * File containing the klpBcBrightcoveServerFunctionseZTiein class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Handles incoming Ajax requests for video inputs.
 * ezjscore is used to handle the requests.
 *
 * This class is just a bridge between ezjscore and the handler that performs
 * the actual work.
 **/
class klpBcBrightcoveServerFunctionseZTiein extends ezjscServerFunctions
{
    /**
     * Fetch brightcove videos request
     *
     * /ezjscore/call/klpbcbrightcoveinputtype::videos::<page to fetch>
     *
     * @param array $args Request arguments array( <page to fetch> )
     * @return object fetch result from Brightcove
     **/
    public static function videos( $args )
    {
        $handler = new klpBcBrightcoveServerFunctions();

        return $handler->videos( 
            $args, klpBcDiContainereZTiein::getInstance()->getBcApi()
        );
    }

    /**
     * Search Brightcove videos
     *
     * /ezjscore/call/klpbcbrightcoveinputtype::search::<search term>::<page to fetch>
     *
     * @param array $args array( string <search term>, int <page to fetch>)
     * @return object fetch result from Brightcove
     **/
    public static function search( $args )
    {
        $handler = new klpBcBrightcoveServerFunctions();

        return $handler->search(
            $args, klpBcDiContainereZTiein::getInstance()->getBcApi()
        );
    }

    /**
     * Gets a list of files from a specified directory
     *
     * /ezjscore/call/klpbcbrightcoveinputtype::files
     *
     * @param array $args
     * @return array List of files and directories
     **/
    public static function files( $args )
    {
        $di = klpBcDiContainereZTiein::getInstance();
        $directory = $di->getServerFileConfig()->rootDirectory;
        $handler = new klpBcBrightcoveServerFunctions();

        return $handler->files( $directory,
            $di->getFileBrowser(), $di->getFileInfoFormatter( $directory )
        );
    }
}
