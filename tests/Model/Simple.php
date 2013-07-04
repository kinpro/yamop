<?php 

namespace Model;

class Simple
{
	public $data;
	
	public static $isCollectionNameCalled = false;
	
	public function __construct( $array )
	{
		$this->data = $array;
	}
	
	public static function getCollectionName()
	{
		self::$isCollectionNameCalled = true;
		return 'simple';
	}
	
}
