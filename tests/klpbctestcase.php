<?php
/**
 * File containing the klpBcTestCase class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcTestCase extends ezpDatabaseTestCase
{
    public function __construct()
    {
        $this->sqlFiles = array(
            array( dirname( __FILE__ ) . "/../sql/", 'schema.sql' ) 
        );

        parent::__construct();
    }

    public function setUp()
    {
        parent::setUp();
        $this->dic = klpBcDiContainereZTiein::getInstance( true );
    }
}

