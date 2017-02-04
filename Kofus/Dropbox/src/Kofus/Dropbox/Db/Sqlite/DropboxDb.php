<?php

namespace Kofus\Dropbox\Db\Sqlite;

use Zend\Uri\UriFactory;
use Kofus\System\Db\Sqlite\File\AbstractFile;
use Zend\Filter\StaticFilter;

class DropboxDb extends AbstractFile
{
	protected function init()
	{
		$this->getDb()->query('CREATE TABLE files (
		    "path" PRIMARY KEY,
		    "name",
		    "modified"
        );')->execute();
		
		$this->getDb()->query('CREATE TABLE folders (
            "path" PRIMARY KEY,
		    "name"
        );')->execute();
	}
	
	public function getLocalFile($path)
	{
	    $records = $this->getDb()->query('
		    SELECT *
		    FROM files
		    WHERE path = '.$this->pl()->quoteValue($path).'
		')->execute();
	    	
	    foreach ($records as $record)
	    	return $record;
	}
	
	public function addLocalFile(array $entry)
	{
	    $record = array(
	        'path' => $entry['path_lower'],
	        'name' => $entry['name'],
	        'modified' => $entry['server_modified']
	    );
	    $this->insert('files', $record);
	}
	
	public function updateLocalFile(array $entry)
	{
		$record = array(
				'path' => $entry['path_lower'],
				'name' => $entry['name'],
				'modified' => $entry['server_modified']
		);
		$this->update('files', $record, 'path = ' . $this->pl()->quoteValue($record['path']));
	}
	
	public function deleteLocalFile(array $entry)
	{
	    $this->getDb()->query('DELETE FROM files WHERE path = ' . $this->pl()->quoteValue($entry['path_lower']))->execute();
	}
	
	public function getLocalFiles()
	{
		$records = $this->getDb()->query('
		    SELECT * FROM files;
		')->execute();
		
		$results = array();
		foreach ($records as $record)
		    $results[] = $record;
		
		return $results;
	}

	
	
	
	
}