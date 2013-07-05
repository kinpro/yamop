<?php
namespace Mawelous\Yamop\Tests;

use Mawelous\Yamop\Model;
use Mawelous\Yamop\Mapper;
use \Mockery as m;

class MapperTest extends BaseTest
{
	
	public function tearDown()
	{
		m::close();
		parent::tearDown();
	}
	
	public function testFetchObject()
	{
		$data = $this->_getSimpleData();
		$mapper = new Mapper( '\Model\Simple' );
		$result = $mapper->fetchObject( $data );
		
		$this->assertInstanceOf( '\Model\Simple', $result );
		$this->assertSame( $data, $result->data );
		
	}
	
	public function testFindReturnsMapper()
	{
		$data = $this->_saveSimpleData();
		
		$mapper = new Mapper( '\Model\Simple' );
		$return = $mapper->find( $data );
		
		$this->assertInstanceOf( '\Mawelous\Yamop\Mapper', $return );
		$this->assertTrue( \Model\Simple::$isCollectionNameCalled );
		
	}
	
	public function testFindSetsCursor()
	{
		$data = $this->_saveSimpleData();
	
		$mapper = new Mapper( '\Model\Simple' );
	
		$emptyCursor = $mapper->getCursor();
		
		$result = $mapper->find( $data );
		$firstCursor = $mapper->getCursor();
		
		$mapper->find( array( 'not_existing' => 'nothing' ) );
		$secondCursor = $mapper->getCursor();

		$this->assertTrue( \Model\Simple::$isCollectionNameCalled );
		$this->assertSame( null, $emptyCursor );
		$this->assertInstanceOf( '\MongoCursor', $firstCursor );
		$this->assertEquals( 1, $firstCursor->count() );
		$this->assertInstanceOf( '\MongoCursor', $secondCursor );
		$this->assertEquals( 0, $secondCursor->count() );
	
	}	
	
	public function testFindOneAsObjectNoSettings()
	{
		$data = $this->_saveSimpleData();
		
		$mapper = new Mapper( '\Model\Simple' );
		
		$object = $mapper->findOne( $data );
		
		$this->assertInstanceOf( '\Model\Simple', $object );
		$this->assertTrue( isset( $object->data['_id'] ) );
		$this->assertInstanceOf( '\MongoId', $object->data['_id'] );
		$this->assertTrue( isset( $object->data['test'] ) );		
	}
	
	public function testFindOneAsObjectAfterSettings()
	{
		$data = $this->_saveSimpleData();	

		$mapperOne = new Mapper( '\Model\Simple',  Mapper::FETCH_OBJECT );
		$objectOne = $mapperOne->findOne( $data );
		
		$this->assertInstanceOf( '\Model\Simple', $objectOne );
		
		$mapperTwo = new Mapper( '\Model\Simple' );
		$mapperTwo->setFetchType( Mapper::FETCH_OBJECT );
		$objectTwo = $mapperTwo->findOne( $data );
		
		$this->assertInstanceOf( '\Model\Simple', $objectTwo );
		
	}
	
	public function testFindOneAsArray()
	{
		$data = $this->_saveSimpleData();
	
		$mapperOne = new Mapper( '\Model\Simple',  Mapper::FETCH_ARRAY );
		$result = $mapperOne->findOne( $data );
	
		$this->assertInternalType( 'array', $result );
		$this->assertTrue( isset( $result['_id'] ) );
		$this->assertInstanceOf( '\MongoId', $result['_id'] );
		$this->assertTrue( isset( $result['test'] ) );
	
		$mapperTwo = new Mapper( '\Model\Simple' );
		$mapperTwo->setFetchType( Mapper::FETCH_ARRAY );
		$result = $mapperTwo->findOne( $data );
	
		$this->assertInternalType( 'array', $result );
		$this->assertTrue( isset( $result['_id'] ) );
		$this->assertInstanceOf( '\MongoId', $result['_id'] );
		$this->assertTrue( isset( $result['test'] ) );	
	
	}	
	
	public function testFindOneAsJson()
	{
		$data = $this->_saveSimpleData();
	
		$mapperOne = new Mapper( '\Model\Simple',  Mapper::FETCH_JSON );
		$result = $mapperOne->findOne( $data );
	
		$this->assertInternalType( 'string', $result );
		$decoded = json_decode( $result );
		$this->assertInstanceOf( 'stdClass', $decoded );
		$this->assertTrue( isset( $decoded->_id ) );
		$this->assertInstanceOf( 'stdClass', $decoded->_id );
		$this->assertTrue( isset( $decoded->test ) );
	
		$mapperTwo = new Mapper( '\Model\Simple' );
		$mapperTwo->setFetchType( Mapper::FETCH_JSON );
		$result = $mapperTwo->findOne( $data );
		
		$this->assertInternalType( 'string', $result );
		$decoded = json_decode( $result );
		$this->assertInstanceOf( 'stdClass', $decoded );
		$this->assertTrue( isset( $decoded->_id ) );
		$this->assertInstanceOf( 'stdClass', $decoded->_id );
		$this->assertTrue( isset( $decoded->test ) );		
	
	}	
	
	public function testFindById()
	{
		$mongoId = new \MongoId();
		$stringId = (string)$mongoId;
		$data = $this->_getSimpleData();
		$data[ '_id' ] = $mongoId;
		self::$_dbConnection->simple->insert( $data );
		
		$byString = ( new Mapper( '\Model\Simple') )->findById( $stringId );
		
		$this->assertInstanceOf( '\Model\Simple', $byString );
		
		$byMongoId = ( new Mapper( '\Model\Simple') )->findById( $mongoId );
		
		$this->assertInstanceOf( '\Model\Simple', $byMongoId );
	}
	
	public function testFindAndGetWithoutFetchSet()
	{
		$data = $this->_saveListData();
		$mapper = new Mapper( '\Model\Simple' );
		$result = $mapper->find()->get();
		
		$this->assertInternalType( 'array', $result );
		
		$keys = array_keys( $result );
		
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$this->assertInstanceOf( '\Model\Simple', current( $result ) );
		$this->assertEquals( count( $data ), count( $result ) );
	}
	
	public function testFindAndGetWithFetchObjectSet()
	{
		$data = $this->_saveListData();
		$mapper = new Mapper( '\Model\Simple', Mapper::FETCH_OBJECT );
		$result = $mapper->find()->get();
	
		$this->assertInternalType( 'array', $result );
	
		$keys = array_keys( $result );
	
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$this->assertInstanceOf( '\Model\Simple', current( $result ) );
		$this->assertEquals( count( $data ), count( $result ) );
		
		$mapper = new Mapper( '\Model\Simple' );
		$mapper->setFetchType( Mapper::FETCH_OBJECT );
		$result = $mapper->find()->get();
		
		$this->assertInternalType( 'array', $result );
		
		$keys = array_keys( $result );
		
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$this->assertInstanceOf( '\Model\Simple', current( $result ) );
		$this->assertEquals( count( $data ), count( $result ) );		
	}	
	
	public function testFindAndGetWithFetchArraySet()
	{
		$data = $this->_saveListData();
		$mapper = new Mapper( '\Model\Simple', Mapper::FETCH_ARRAY );
		$result = $mapper->find()->get();
	
		$this->assertInternalType( 'array', $result );
	
		$keys = array_keys( $result );
	
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$current = current( $result );
		$this->assertInternalType( 'array', $current );
		$this->assertEquals( (string)$data[0]['_id'], (string) $current['_id'] );
		$this->assertEquals( count( $data ), count( $result ) );
	
		$mapper = new Mapper( '\Model\Simple' );
		$mapper->setFetchType( Mapper::FETCH_ARRAY );
		$result = $mapper->find()->get();
	
		$this->assertInternalType( 'array', $result );
	
		$keys = array_keys( $result );
	
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$current = current( $result );
		$this->assertInternalType( 'array', $current );
		$this->assertEquals( (string)$data[0]['_id'], (string) $current['_id'] );
		$this->assertEquals( count( $data ), count( $result ) );
		
	}
		
	public function testFindAndGetWithFetchJsonSet()
	{
		$data = $this->_saveListData();
		$mapper = new Mapper( '\Model\Simple', Mapper::FETCH_JSON );
		$result = $mapper->find()->get();
	
		$this->assertInternalType( 'string', $result );
		
		$result = json_decode( $result, true );
		
		$keys = array_keys( $result );
	
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$current = current( $result );
		
		$this->assertInternalType( 'array', $current );
		$this->assertEquals( (string)$data[0]['_id'], $current['_id']['$id'] );
		$this->assertEquals( count( $data ), count( $result ) );
	
		$mapper = new Mapper( '\Model\Simple' );
		$mapper->setFetchType( Mapper::FETCH_JSON );
		$result = $mapper->find()->get();
	
		$this->assertInternalType( 'string', $result );
		
		$result = json_decode( $result, true );
	
		$keys = array_keys( $result );
	
		$this->assertEquals( (string)$data[0]['_id'], $keys[0] );
		$current = current( $result );
		$this->assertInternalType( 'array', $current );
		$this->assertEquals( (string)$data[0]['_id'], (string) $current['_id']['$id'] );
		$this->assertEquals( count( $data ), count( $result ) );
	
	}	
	
	/**
	 * @expectedException Exception
	 */
	public function testSortWithoutFind()
	{
		( new Mapper( 'Model\Simple' ) )->sort();
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testLimitWithoutFind()
	{
		( new Mapper( 'Model\Simple' ) )->limit();
	}

	/**
	 * @expectedException Exception
	 */
	public function testSkipWithoutFind()
	{
		( new Mapper( 'Model\Simple' ) )->skip();
	}	
	
	public function testSort()
	{
		$data = $this->_saveListData();
		$mapper = new Mapper( '\Model\Simple' );
		$sortedAsc = $mapper->find()->sort( array( 'letter' => 1 ) );
		
		
		
	}
	
	protected function _getSimpleData()
	{
		return array( 'test' => 'test' );
	}
	
	protected function _getListData()
	{
		return array(
				array( 'letter' => 'b', '_id' => new \MongoId( '51d57f68b7846c9816000003' ) ),
				array( 'letter' => 'c'),
				array( 'letter' => 'a')
				);
	}
	
	protected function _saveSimpleData()
	{
		self::$_dbConnection->simple->insert( $this->_getSimpleData() );
		return $this->_getSimpleData();
	}
	
	protected function _saveListData()
	{
		self::$_dbConnection->simple->batchInsert( $this->_getListData() );
		return $this->_getListData();		
	}
	
}