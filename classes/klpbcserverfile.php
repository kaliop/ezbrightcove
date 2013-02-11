<?php
/**
 * File containing the klpBcServerFile class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Persistent object that stores a path to a file for a content object attribute.
 **/
class klpBcServerFile extends eZPersistentObject
{
    /**
     * Returns the definition array for this persistent object
     *
     * @return array
     */
    static function definition()
    {
        return array(
            'fields' => array(
                'contentobject_attribute_id' => array( 'name' => 'ContentObjectAttributeId',
                                                       'datatype' => 'integer',
                                                       'default' => 0,
                                                       'required' => true ),
                'version' => array( 'name' => 'Version',
                                    'datatype' => 'integer',
                                    'default' => 0,
                                    'required' => true ),
                'filepath' => array( 'name' => 'FilePath',
                                'datatype' => 'string',
                                'default' => '',
                                'required' => true ) ),
            'keys' => array( 'contentobject_attribute_id', 'version' ),
            'class_name' => __CLASS__,
            'sort' => array( 'contentobject_attribute_id' => 'asc' ),
            'name' => 'ezx_klpbc_serverfile'
        );
    }

    /**
     * Fetch ServerFile by content object attribute id and version
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return klpBcServerFile
     */
    public static function fetch( $id, $version )
    {
        $conds = array(
            'contentobject_attribute_id' => $id,
            'version' => $version
        );

        return self::fetchObject( self::definition(), null, $conds );
    }

    /**
     * Deletes server file entries. If version is null it deletes all versions.
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     **/
    public static function delete( $id, $version )
    {
        $conds = array( 'contentobject_attribute_id' => $id );
        if ( !is_null( $version ) )
            $conds['version'] = $version;

        return self::removeObject( self::definition(), $conds );
    }
}
