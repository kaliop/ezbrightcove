<?php
/**
 * File containing the klpBcVideoInputTypeInterface interface
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

interface klpBcVideoInputTypeInterface
{
    public function isInputDataValid( $data, $isRequired, $hasOriginalData );
    public function lastError();
    public function fetch( $id, $version );
    public function initialize( $id, $currentVersion, $newVersion );
    public function store( $id, $version, $filePath );
    public function delete( $id, $version );
    public function fromString( $data, $id, $version );

    public function getFileUrl( $id, $version );
    public function getFileInfo( $id, $version );

    public function canDownload();
    public function requiresProcessing();
}

