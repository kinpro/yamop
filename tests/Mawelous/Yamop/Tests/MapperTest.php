<?php
namespace Mawelous\Yamop\Tests;

use Mawelous\Yamop\Model;
use Mawelous\Yamop\Mapper;
use \Mockery as m;

class MapperTest extends BaseTest
{
	protected $_articleMock;
	
	public function setUp()
	{
		$this->_articleMock = m::mock( 'alias:Article' );
	}
	
	public function tearDown()
	{
		m::close();
	}
	
	public function testFindReturnsMapper()
	{
		self::$_dbConnection->articles->insert( array( 'test' => 'test' ) );
		
		$this->_articleMock->shouldReceive( 'getCollectionName' )->andReturn(  "articles" );
		
		$mapper = new Mapper( 'Article' );
		$return = $mapper->find( array( 'test' => 'test' ) );
		
		$this->assertInstanceOf( '\Mawelous\Yamop\Mapper', $return );
		
		self::$_dbConnection->articles->remove( array( 'test' => 'test' ) );
		
	}
	
	public function testFindSetsCursor()
	{
		self::$_dbConnection->articles->insert( array( 'test' => 'test' ) );
	
		$this->_articleMock->shouldReceive( 'getCollectionName' )->andReturn(  "articles" );
	
		$mapper = new Mapper( 'Article' );
	
		$emptyCursor = $mapper->getCursor();
		
		$mapper->find( array( 'test' => 'test' ) );
		$firstCursor = $mapper->getCursor();
		
		$mapper->find( array( 'not_existing' => 'nothing' ) );
		$secondCursor = $mapper->getCursor();
	
		$this->assertSame( null, $emptyCursor );
		$this->assertInstanceOf( '\MongoCursor', $firstCursor );
		$this->assertEquals( 1, $firstCursor->count() );
		$this->assertInstanceOf( '\MongoCursor', $secondCursor );
		$this->assertEquals( 0, $secondCursor->count() );
	
	}	
}