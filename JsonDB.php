<?php

/**
 * Based on Straussn's JSON-Databaseclass.
 *
 * Handle JSON-Files like a very, very simple DB. Useful for little ajax applications.
 * Last change: 05-06-2012
 * Version: 1.0b
 * by Manuel Strauss, Web: http://straussn.eu, E-Mail: StrZlee@gmx.net, Skype: StrZlee
 */

/*
	Example:
		$db = new JsonDB( "./path_to_my_jsonfiles/" );
		$result = $db -> select( "json_file_name_without_extension", "search-key", "search-value" );

			Example JSON-File:
				[
					{"ID": "0", "Name": "Hans Wurst", "Age": "12"},
					{"ID": "1", "Name": "Karl Stoascheissa", "Age": "15"},
					{"ID": "2", "Name": "Poidl Peidlbecka", "Age": "14"}
				]

		Method Overview:

			new JsonDB(".(path_to_my_jsonfiles/");
			JsonDB -> createTable("hello_world_table");
			JsonDB -> select ( "table", "key", "value" ) - Selects multible lines which contains the key/value and returns it as array
			JsonDB -> selectAll ( "table" )  - Returns the entire file as array
			JsonDB -> update ( "table", "key", "value", ARRAY ) - Replaces the line which corresponds to the key/value with the array-data
			JsonDB -> updateAll ( "table", ARRAY ) - Replaces the entire file with the array-data
			JsonDB -> insert ( "table", ARRAY , $create = FALSE) - Appends a row, returns true on success. if $create is TRUE, we will create the table if it doesn't already exist.
			JsonDB -> delete ( "table", "key", "value" ) - Deletes all lines which corresponds to the key/value, returns number of deleted lines
			JsonDB -> deleteAll ( "table" ) - Deletes the whole data, returns "true" on success
			new JsonTable("./data/test.json", $create = FALSE) - If $create is TRUE, creates table if it doesn't exist.
*/

class JsonDB
{
    /**
     * @var string
     */
    protected $path = './';

    /**
     * @var string
     */
    protected $file_ext = '.json';

    /**
     * @var array
     */
    protected $tables = [];

    /**
     * Construct the object and set the base path for databases.
     *
     * @param $path
     * @throws Exception
     */
    public function __construct($path)
    {
        if (!is_dir($path)) {
            throw new Exception("JsonDB Error: Database not found.");
        }

        $this->path = rtrim($path, '/');
    }

    /**
     * Get table instance if exists or create new one.
     *
     * @param $table
     * @param $create
     * @return mixed
     */
    protected function getTableInstance($table, $create)
    {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = new JsonTable($this->path . $table, $create);
        }

        return $this->tables[$table];
    }

    /**
     * Magic call for JsonTable class.
     *
     * @param $op
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function __call($op, $args)
    {
        if ($args and method_exists('JsonTable', $op)) {
            $table = $args[0] . $this->file_ext;
            $create = false;

            if ($op == 'createTable') {
                return $this->getTableInstance($table, true);
            } elseif ($op == 'insert' and isset($args[2]) and $args[2] === true) {
                $create = true;
            }

            return $this->getTableInstance($table, $create)->$op($args);
        } else {
            throw new Exception("JsonDB Error: Unknown method or wrong arguments.");
        }
    }

    /**
     * Set extension for json file.
     *
     * @param $file_ext
     * @return $this
     */
    public function setExtension($file_ext)
    {
        $this->file_ext = $file_ext;
        return $this;
    }

}
?>
