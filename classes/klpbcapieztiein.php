<?php
/**
 * File containing the klpBcApieZTiein class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Wraps around klpBcApi and initialises it with read+write token from
 * ezbrightcove.ini.
 */
class klpBcApieZTiein extends klpBcApi
{
    /**
     * Creates a new instance of this class
     */
    public function __construct()
    {
        $ini = eZINI::instance( 'ezbrightcove.ini' );
        $readToken = $ini->variable( 'BrightcoveSettings', 'ApiReadToken' );
        $writeToken = $ini->variable( 'BrightcoveSettings', 'ApiWriteToken' );

        parent::__construct( $readToken, $writeToken );
    }
}
