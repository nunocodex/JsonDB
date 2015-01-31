<?php

class JsonTable
{
    /**
     * @var string
     */
    protected $json_file = '';

    /**
     * @var
     */
    protected $file_handle;

    /**
     * @var array
     */
    protected $file_data = [];

    /**
     * @param $json_file
     * @param bool $create
     * @throws Exception
     */
    public function __construct($json_file, $create = false)
    {
        if (!file_exists($json_file) and false === $create) {
            throw new Exception("JsonTable Error: Table not found: {$json_file}.");
        }

        if (true === $create) {
            $this->createTable($json_file, $create);
        }

        $this->json_file = $json_file;

        $this->file_data = json_decode(file_get_contents($this->json_file), true);

        $this->lockFile();
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->save();
        fclose($this->file_hHandle);
    }

    /**
     * Lock the json file.
     *
     * @throws Exception
     */
    protected function lockFile()
    {
        $handle = fopen($this->json_file, "w");

        if (flock($handle, LOCK_EX)) {
            $this->file_handle = $handle;
        } else {
            throw new Exception("JsonTable Error: Can't set file-lock.");
        }
    }

    /**
     * Save the json file.
     *
     * @throws Exception
     */
    protected function save()
    {
        if (!fwrite($this->file_handle, json_encode($this->file_data))) {
            throw new Exception("JsonTable Error: Can't write data to: {$this->json_file}.");
        }
    }

    /**
     * Return the content from json file.
     *
     * @return array|mixed
     */
    public function selectAll()
    {
        return $this->file_data;
    }

    /**
     * Return the selected key.
     *
     * @param $key
     * @param int $val
     * @return array
     */
    public function select($key, $val = 0)
    {
        $result = [];

        if (is_array($key)) {
            $result = $this->select($key[1], $key[2]);
        } else {
            $data = $this->file_data;

            foreach ($data as $_key => $_val) {
                if (isset($data[$_key][$key])) {
                    if ($data[$_key][$key] == $val) {
                        $result[] = $data[$_key];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Update the json file.
     *
     * @param array $data
     * @return array
     */
    public function updateAll($data = [])
    {
        if (isset($data[0]) and substr_compare($data[0], $this->json_file, 0)) {
            $data = $data[1];
        }

        return $this->file_data = [$data];
    }

    public function update($key, $val = 0, $new_data = []) {
        $result = false;
        if (is_array($key)) {
            // Define local vars.
            $arr_table = $key[1];
            $arr_key = $key[2];
            $arr_value = $key[3];

            $result = $this->update($arr_table, $arr_key, $arr_value);
        } else {
            $data = $this->file_data;

            foreach($data as $_key => $_val) {
                if (isset($data[$_key][$key])) {
                    if ($data[$_key][$key] == $val) {
                        $data[$_key] = $new_data;
                        $result = true;
                        break;
                    }
                }
            }

            if ($result) {
                $this->file_data = $data;
            }
        }

        return $result;
    }

    /**
     * Insert data into json file.
     *
     * @param array $data
     */
    public function insert($data = [])
    {
        if (isset($data[0]) and substr_compare($data[0], $this->json_file, 0)) {
            $data = $data[1];
        }

        $this->file_data[] = $data;
    }

    /**
     * Delete all data from json file.
     */
    public function deleteAll()
    {
        $this->file_data = [];
    }

    /**
     * Delete key from json file.
     *
     * @param $key
     * @param int $val
     * @return int
     */
    public function delete($key, $val = 0)
    {
        $result = 0;

        if (is_array($key)) {
            $result = $this->delete($key[1], $key[2]);
        } else {
            $data = $this->file_data;

            foreach($data as $_key => $_val) {
                if (isset($data[$_key][$key])) {
                    if ($data[$_key][$key] == $val) {
                        unset($data[$_key]);
                        $result++;
                    }
                }
            }

            if ($result) {
                sort($data);
                $this->file_data = $data;
            }
        }

        return $result;
    }

    /**
     * Create table.
     *
     * @param $table_path
     * @return bool
     * @throws Exception
     */
    public function createTable($table_path)
    {
        if (is_array($table_path)) {
            $table_path = $table_path[0];
        }

        if (file_exists($table_path)) {
            throw new Exception("Table already exists: {$table_path}.");
        }

        if (!fclose(fopen($table_path, 'a'))) {
            throw new Exception("New table couldn't be created: {$table_path}.");
        }
    }

}
