<?php
/**
 * File containing the klpBcTestSuite class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcTestSuite extends ezpDatabaseTestSuite
{
    public function __construct()
    {
        $this->sqlFiles = array(
            array( dirname( __FILE__ ) . "/../sql/", 'schema.sql' )
        );

        $ini = eZINI::instance( 'ezbrightcove.ini' );
        // Remember that you might have to update ezbrightcove.ini as well to
        // make changes work outside of tests.
        $vars = array( 'DependencyInjection' =>
                    array( "Settings" =>
                        array( 'typeoptions.class' => 'klpBcTypeOptions' ),
                        array( 'typeclassinputvalidator.class' => 'klpBcTypeClassInputValidator' ),
                        array( 'videoinput.class' => 'klpBcVideoInputeZTiein' ),
                        array( 'video.class' => 'klpBcVideo' ),
                        array( 'videometa.class' => 'klpBcVideoMeta' ),
                        array( 'queue.class' => 'klpBcQueue' ),
                        array( 'bcapi.class' => 'klpBcApieZTiein' ),
                    ),
                array( "BrightcoveSettings" =>
                    array( "ApiReadToken" => "readtoken" ),
                    array( "ApiWriteToken" => "writetoken" ),
                ),
                array( "VideoInputTypes" =>
                    array( "Types" => 
                        array( 'upload' => "klpBcUploadVideoInputType" )
                    )
                )
        );
        $ini->setVariables( $var );

        parent::__construct();
        $this->setName( "Kaliop Brightcove Test Suite" );
        $this->addTestSuite( "klpBcDiContainereZTieinTest" );

        $this->addTestSuite( "klpBcVideoTest" );
        $this->addTestSuite( "klpBcVideoMetaTest" );
        $this->addTestSuite( "klpBcQueueTest" );
        $this->addTestSuite( "klpBcServerFileTest" );

        $this->addTestSuite( "klpBcUploadVideoInputTypeTest" );
        $this->addTestSuite( "klpBcBrightcoveVideoInputTypeTest" );
        $this->addTestSuite( "klpBcTypeTest" );

        $this->addTestSuite( "klpbcCreateCronjobTest" );
        $this->addTestSuite( "klpbcStatusCronjobTest" );
        $this->addTestSuite( "klpbcUpdateCronjobTest" );
        $this->addTestSuite( "klpbcDeleteCronjobTest" );
    }

    public static function suite()
    {
        return new self();
    }
}
