<?php
/**
 * File containing the klpBcVideo class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Class that represents a brightcove Video (persistent object).
 *
 * A video can be in a number of different states depending on where in the
 * process of conversion it is. Some videos might not need processing and only
 * has a limited state (DRAFT, COMPLETED).
 *
 * Valid state flow:
 * - DRAFT -> TO_PROCESS | FAILED
 * - TO_PROCESS -> PROCESSING | FAILED
 * - PROCESSING -> COMPLETED | FAILED
 * - COMPLETED -> TO_DELETE
 * - FAILED -> DRAFT | TO_DELETE
 * - TO_DELETE -> FAILED
 */
class klpBcVideo extends eZPersistentObject
{
    const STATE_DRAFT = 0;
    const STATE_TO_PROCESS  = 1;
    const STATE_PROCESSING = 2;
    const STATE_COMPLETED = 3;
    const STATE_FAILED = 4;
    const STATE_TO_DELETE = 5;

    /**
     * Creates a new instance of this class
     *
     * @param array $row eZPersistentObject row data
     */
    public function __construct( $row )
    {
        if ( !is_array( $row ) )
        {
            // Create a valid row with null values for those cases where
            // __construct is called without a proper row. This prevents warnings
            // when eZPersistentObject tries to access an attribute.
            $def = self::definition();
            $values = array_fill( 0, count( $def['fields'] ), null );
            $row = array_combine( array_keys( $def['fields'] ), $values );
        }

        parent::__construct( $row );

        $this->dic = klpBcDiContainereZTiein::getInstance();
        $this->tr = 'extension/klpbc';
    }

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
                'input_type_identifier' => array( 'name' => 'InputTypeIdentifier',
                                                  'datatype' => 'string',
                                                  'default'  => '',
                                                  'required' => true ),
                'state' => array( 'name' => 'State',
                                  'datatype' => 'integer',
                                  'default' => 0,
                                  'required' => true ),
                'brightcove_id' => array( 'name' => 'BrightcoveId',
                                          'datatype' => 'string',
                                          'default' => '',
                                          'required' => false ),
                'need_meta_update' => array( 'name' => 'NeedMetaUpdate',
                                             'datatype' => 'integer',
                                             'default' => 0,
                                             'required' => true ),
                'error_log' => array( 'name' => 'ErrorLog',
                                      'datatype' => 'string',
                                      'default'  => '',
                                      'required' => false ),
                'created' => array( 'name' => 'Created',
                                    'datatype' => 'integer',
                                    'default' => 0,
                                    'required' => true ),
                'modified' => array( 'name' => 'Modified',
                                      'datatype' => 'integer',
                                      'default' => 0,
                                      'required' => true ), ),
            'function_attributes' => array( 'state_label' => 'getStateLabel',
                                            'is_completed' => 'isCompleted',
                                            'has_error' => 'hasError',
                                            'original_video' => 'originalVideo',
                                            'requires_processing' => 'requiresProcessing',
                                            'latest_video' => 'fetchLatestVideo' ),
            'keys' => array( 'contentobject_attribute_id', 'version' ),
            'class_name' => __CLASS__,
            'sort' => array( 'contentobject_attribute_id' => 'asc' ),
            'name' => 'ezx_klpbc_video'
        );
    }

    /**
     * Fetches the total number of videos
     *
     * @return int Total count
     */
    public static function fetchCount()
    {
        return self::count( self::definition() );
    }

    /**
     * Fetch video by content object attribute id and version
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return klpBcVideo
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
     * Fetches a list of videos by the state
     *
     * @param int|string $state State to filter on
     * @return array List of videos or null
     */
    public static function fetchByState( $state )
    {
        if ( !is_numeric( $state ) )
            $state = self::getStateValue( $state );

        $conds = array( 'state' => $state );
        return self::fetchObjectList( self::definition(), null, $conds );
    }

    /**
     * Fetches a list of videos that need meta data to be updated
     *
     * @return array List of videos or null
     */
    public static function fetchPendingMetaUpdate()
    {
        $conds = array( 'need_meta_update' => 1 );

        return self::fetchObjectList(
            self::definition(), null, $conds, array( 'version' => 'desc' )
        );
    }

    /**
     * Deletes all videos, use with caution!
     */
    public static function removeAll()
    {
        return self::removeObject( self::definition() );
    }

    /**
     * Returns the value for a state
     *
     * @param string $state Name of the state (without STATE_ prefix)
     * @return int The state value
     */
    public static function getStateValue( $state )
    {
        return constant( __CLASS__ . strtoupper( "::STATE_{$state}" ) );
    }

    /**
     * Fetches the latest version of the video that has been completed and has
     * a brightcove id
     *
     * @return klpBcVideo|null
     */
    public function fetchLatestVideo()
    {
        $conds = array(
            'contentobject_attribute_id' => $this->ContentObjectAttributeId,
            'state' => $this->getStateValue( 'COMPLETED' )
        );

        $videos = self::fetchObjectList(
            self::definition(),
            null,
            $conds,
            array( 'version' => 'desc' ),
            array( 'length' => 1 )
        );

        if ( isset( $videos[0] ) )
            return $videos[0];
    }

    /**
     * Stores the video to the database
     *
     * The created timestamp is set if no timestamp is already set
     * The modified timestamp is always 'touched' when using this method.
     *
     */
    public function store( $fieldFilters = null )
    {
        if ( !$this->attribute( 'created' ) )
            $this->setAttribute( 'created', time() );

        $this->setAttribute( 'modified', time() );

        parent::store( $fieldFilters );
    }

    /**
     * Deletes this video
     *
     * Any versions of this video that has a brightcove_id is not deleted but
     * moved to the TO_DELETE state.
     *
     * @param bool $removeCurrentVersion Remove current version or all versions
     **/
    public function delete( $removeCurrentVersion = true )
    {
        $query1 = $this->createDeleteUpdateQuery( $removeCurrentVersion );
        $query2 = $this->createDeleteQuery( $removeCurrentVersion );

        $db = eZDB::instance();
        $db->begin();
        $db->query( $query1 );
        $db->query( $query2 );
        $db->commit();
    }

    /**
     * Returns the file url of the original video
     *
     * @return string Path to local file or url to remote fil
     */
    public function getFileUrl()
    {
        $input = $this->attribute( 'input_type_identifier' );

        if ( $input )
        {
            return $this->dic->getVideoInput()->getInput( $input )->getFileUrl(
                $this->attribute( 'contentobject_attribute_id' ),
                $this->attribute( 'version' )
            );
        }
    }

    /**
     * Fetches all videos that have the same content object attribute id
     *
     * @param bool $sameState Only include videos with same state as $this.
     * @return array List of videos
     **/
    public function fetchVersions( $sameState = true )
    {
        $conds = array(
            'contentobject_attribute_id' => $this->attribute( 'contentobject_attribute_id' ),
        );

        if ( $sameState )
            $conds['state'] = $this->attribute( 'state' );

        return self::fetchObjectList(
            self::definition(), null, $conds, array( 'version' => 'desc' )
        );
    }

    /**
     * Returns the state label for the current state
     *
     * @return string
     */
    public function getStateLabel()
    {
        $labelList = array(
            self::STATE_DRAFT => ezpI18n::tr( $this->tr, 'Draft' ),
            self::STATE_TO_PROCESS => ezpI18n::tr( $this->tr, 'Pending processing' ),
            self::STATE_PROCESSING => ezpI18n::tr( $this->tr, 'Processing' ),
            self::STATE_COMPLETED => ezpI18n::tr( $this->tr, 'Completed' ),
            self::STATE_FAILED  => ezpI18n::tr( $this->tr, 'Failed' ),
            self::STATE_TO_DELETE => ezpI18n::tr( $this->tr, 'Pending deletion' ),
        );

        $unknownLabel = ezpI18n::tr( $this->tr, 'Unknown state' );
        $state = $this->attribute( 'state' );

        if ( $state === null )
            $state = self::STATE_DRAFT;

        return isset( $labelList[$state] ) ? $labelList[$state] : $unknownLabel;
    }

    /**
     * Returns true if video's state is a particular state
     *
     * @param string $state Name of the state (without STATE_ prefix)
     * @return bool
     */
    public function isInState( $state )
    {
        return ( $this->attribute( 'state' ) == $this->getStateValue( $state ) );
    }

    /**
     * Returns true if video is 'completed' (any processing is completed')
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->isInState( "COMPLETED" );
    }

    /**
     * Returns true if there's a error with the video (processing failed)
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->isInState( "FAILED" );
    }

    /**
     * Returns true if the video has dirty meta data
     *
     * @return bool
     **/
    public function needMetaUpdate()
    {
        return (bool) (int) $this->attribute( 'need_meta_update' );
    }

    /**
     * Returns true if this video requires any processing (must be sent off for
     * conversion)
     *
     * @return bool
     */
    public function requiresProcessing()
    {
        $input = $this->attribute( 'input_type_identifier' );

        if ( !$input )
            return false;

        return $this->dic->getVideoInput()
                         ->getInput( $input )
                         ->requiresProcessing();
    }

    /**
     * Returns true if an original video exists
     *
     * @return bool
     */
    public function hasOriginalVideo()
    {
        return (bool) $this->originalVideo();
    }

    /**
     * Returns the original video
     *
     * @return mixed
     */
    public function originalVideo()
    {
        $input = $this->attribute( 'input_type_identifier' );

        if ( $input )
        {
            return $this->dic->getVideoInput()->getInput( $input )->fetch(
                $this->attribute( 'contentobject_attribute_id' ),
                $this->attribute( 'version' )
            );
        }
    }

    /**
     * Creates a query for updating all videos that does have a
     * brightcove_id to TO_DELETE state
     *
     * @param bool $removeCurrentVersion Whether current version or all
     *                                   versions should be marked TO_DELETE
     * @return string Sql query
     * @author Me
     **/
    protected function createDeleteUpdateQuery( $removeCurrentVersion )
    {
        $def = self::definition();

        $state = $this->getStateValue( 'TO_DELETE' );
        $conditionString = $this->getDeleteConditions( $removeCurrentVersion, true );
        $query = "UPDATE {$def['name']} set state = $state WHERE {$conditionString}";

        return $query;
    }

    /**
     * Creates a query for deleting all videos that does not have a
     * brightcove_id.
     *
     * @param bool $removeCurrentVersion Whether current version or all
     *                                   versions should deleted
     * @return string Sql query
     * @author Me
     **/
    protected function createDeleteQuery( $removeCurrentVersion )
    {
        $def = self::definition();

        $conditionString = $this->getDeleteConditions( $removeCurrentVersion, false );
        $query = "DELETE FROM {$def['name']} WHERE {$conditionString}";

        return $query;
    }

    /**
     * Creates the 'delete' condition string for use in sql statements
     *
     * @param bool $withBrightcoveId Whether videos with or without brightcove
     *                               ids should be matched
     * @param bool $removeCurrentVersion Whether current version or all
     *                                   versions should be matched
     * @return string Sql condition (for use in 'where' clause)
     **/
    protected function getDeleteConditions( $removeCurrentVersion, $withBrightcoveId )
    {
        $conditions = array();
        $conditions[] = "contentobject_attribute_id = {$this->ContentObjectAttributeId}";
        if ( $removeCurrentVersion )
            $conditions[] = "version = " . $this->Version;

        if ( $withBrightcoveId )
            $conditions[] = 'brightcove_id != ""';
        else
            $conditions[] = 'brightcove_id = ""';

        return implode( " AND ", array_values( $conditions ) );
    }
}
