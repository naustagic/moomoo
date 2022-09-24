<?php

namespace RPurinton\Moomoo;

require_once(__DIR__."/ConfigLoader.php");

class SqlClient Extends ConfigLoader
{
	private $sql = null;

	function __construct()
	{
		parent::__construct();
		$this->connect();
	}

	private function connect()
	{
		extract($this->config["sql"]);
		$this->sql = mysqli_connect($host,$user,$pass,$db);
	}

	public function query($query)
	{
		if(!mysqli_ping($this->sql)) $this->connect();
		return mysqli_query($this->sql,$query);
	}

	public function count($result)
	{
		return mysqli_num_rows($result);
	}

        public function assoc($result)
	{
		return mysqli_fetch_assoc($result);
	}

	public function escape($text)
	{
		return mysqli_real_escape_string($this->sql,$text);
	}

	public function single($query)
	{
		if(!mysqli_ping($this->sql)) $this->connect();
		return mysqli_fetch_assoc(mysqli_query($this->sql,$query));
	}
}
