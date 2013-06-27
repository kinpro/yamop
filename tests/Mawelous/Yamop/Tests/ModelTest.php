<?php
namespace Mawelous\Yamop\Tests;

class ModelTest extends BaseTest
{
	
	public function testFill()
	{
		$article = $this->_getArticle();
		$articleData = $this->_getArticleData();
		$commentData = $this->_getCommentData();
		
		$this->assertSame( $articleData[ 'title' ], $article->title );
		$this->assertSame( $articleData[ 'text' ], $article->text);
		$this->assertInstanceOf( '\Model\Note', $article->note);
		
		$comment = current( $article->comments);
		$this->assertInstanceOf( '\Model\Comment', $comment );
		$this->assertSame( $commentData[ 'text' ], $comment->text );
		$this->assertSame( $commentData[ 'date' ]->sec, $comment->date->sec );
	}
	
	public function testGetMapper()
	{
		$this->assertInstanceOf( 'Mawelous\Yamop\Mapper', \Model\Article::getMapper() );
		$this->assertInstanceOf( '\Mapper\AuthorMapper', \Model\Author::getMapper() );
	}
	
	public function testCollectionName()
	{
		$this->assertSame( 'authors', \Model\Author::getCollectionName() );
	}
	
	/**
	 * @expectedException Exception
	 */	
	public function testNoCollectionName()
	{
		\Model\Comment::getCollectionName();
	}
	
	/**
	 * @depends testFill
	 */	
	public function testSave()
	{
		$article = $this->_getArticle();
		$result = $article->save();
		
		$this->assertArrayHasKey( 'ok', $result );
		$this->assertEquals( 1, $result[ 'ok' ] );
		$this->assertObjectHasAttribute( 'id', $article );
		$this->assertObjectHasAttribute( '_id', $article );
		
		$rawArticle = $this->_dbConnection->articles
			->findOne( array( '_id' => $article->_id ) );
		
		$this->assertInternalType( 'array', $rawArticle );

		return $article;
		//$this->_dbConnection->articles->remove( array( '_id' => $this->_articleId  ) );
		
	}
	
	/**
	 * @depends testSave
	 */	
	public function testSaveWithoutStringIds( \Model\Article $article )
	{

		$rawArticle = $this->_dbConnection->articles
			->findOne( array( '_id' => $article->_id  ) );
		
		$this->assertFalse( isset( $rawArticle['id'] ));
		$this->assertFalse( isset( $rawArticle['author']['id'] ));
		$this->assertFalse( isset( $rawArticle['comments'][0]['id'] ));
		
		$this->_dbConnection->articles->remove( array( '_id' => $this->_articleId  ) );
	}
	
	public function testRemove()
	{
		$article = $this->_getArticle();
		$this->_dbConnection->articles->insert( $article );
		
		$article->remove();
		
		$result = $this->_dbConnection->articles->findOne( array( '_id' => $article->_id  ) );

		$this->assertSame( null, $result );
	}
	
	public function testFindById()
	{
		$article = $this->_getArticle();
		$this->_dbConnection->articles->insert( $article );

		$dbArticleByString = \Model\Article::findById( $article->id );
		$dbArticleByMongoId = \Model\Article::findById( $article->_id );

		$this->assertInstanceOf( '\Model\Article', $dbArticleByString );
		$this->assertInstanceOf( '\Model\Article', $dbArticleByMongoId );
		
		$this->_dbConnection->articles->remove( array( '_id' => $this->_articleId  ) );
	}
	
	public function testJoinOne()
	{
		$article = $this->_getArticle();
		$secondArticle = clone $article;
		$thirdArticle = clone $article;

		$author = $this->_getAuthor();
		$this->_dbConnection->authors->insert( $author );	

		$article->joinOne( 'author_id', '\Model\Author', 'author' );
		$this->assertInstanceOf( '\Model\Author', $article->author );
		
		$secondArticle->joinOne( 'author_id', '\Model\Author' );
		$this->assertInstanceOf( '\Model\Author', $secondArticle->author_id );

		$thirdArticle->joinOne( 'author_id', '\Model\Author', 'author', array( 'name' ) );
		$this->assertInstanceOf( '\Model\Author', $thirdArticle->author );
		$this->assertFalse( isset( $thirdArticle->author->email ) );

		$this->_dbConnection->authors->remove( array( '_id' => $this->_authorId  ) );
	}
	
	protected function _getArticle()
	{
		$article = new \Model\Article;
		$article->fill( $this->_getArticleData() );
		return $article;
	}
	
	protected function _getAuthor()
	{
		$author = new \Model\Author;
		$author->fill( $this->_getAuthorData() );
		return $author;
	}	
	
	protected function _getCommentData(){
		return array ( 'date' => new \MongoDate( 12345 ),
			'text' => 'Comment text');
	}
	
	protected function _getAuthorData(){
		return array ( 
				'_id' => $this->_authorId,
				'name' => 'John Doe',
				'email' => 'john@mail.com');
	}	
	
	protected function _getArticleData() 
	{
		return array(
			'_id' => $this->_articleId,
			'author_id'=> $this->_authorId,		 
			'title' => 'Lorem',
			'text' => 'Sample text',
			'note' => array( 'text' => 'Note text'),
			'comments' => array ( $this->_getCommentData() ) );	
	}
}