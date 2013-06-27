<?php
namespace Mawelous\Yamop\Tests;

use Mawelous\Yamop\Model;
use Mawelous\Yamop\Mapper;
use \Mockery as m;

class MapperTest extends BaseTest
{
	public function testFind()
	{
		$this->_dbConnection->articles->insert( array( 'test' => 'test' ) );
		
		$mock = m::mock( 'alias:Article' );
		$mock->shouldReceive( 'getCollectionName' )->andReturn(  "articles" );
		
		$mapper = new Mapper( 'Article' );
		$return = $mapper->find( array( 'test' => 'test' ) );
		
		$this->assertInstanceOf( '\Mawelous\Yamop\Mapper', $return );
		
	}
}