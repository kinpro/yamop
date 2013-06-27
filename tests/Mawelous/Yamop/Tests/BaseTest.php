<?php
namespace Mawelous\Yamop\Tests;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{

    protected $_server = 'mongodb://127.0.0.1:27017';
    protected $_database = 'yamop_tests';
    protected $_dbConnection;
    
    protected $_articleId;
    protected $_authorId;
	
    public function setUp()
    {
    	$connection = new \MongoClient( $this->_server );
    	$this->_dbConnection = $connection->{$this->_database};
    	\Mawelous\Yamop\Mapper::setDatabase( $this->_dbConnection );
    	
    	$this->_articleId = new \MongoId();
    	$this->_authorId = new \MongoId();
    }

    public function tearDown()
    {
    	$this->_dbConnection->drop();
    }
}