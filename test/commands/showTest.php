<?php

/*
 * This file is part of Pug
 */

include_once( 'PugTestCase.php' );

use PHPUnit\Framework\TestCase;

class showTest extends PugTestCase
{
	public function testShowReturnsListing()
	{
		$this->usePugfile( '.pug-enabled' );
		$result = $this->executePugCommand( 'show' );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
* pug/1
* pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testShowEmptyPugfileReturnsError()
	{
		$this->usePugfile( '.pug-new' );
		$result = $this->executePugCommand( 'show' );

		$this->assertEquals( 0, $result['exit'] );
		$this->assertEquals( "pug: Not tracking any projects. See 'pug help'", $result['output'] );
	}

	public function testShowUnknownProjectReturnsError()
	{
		$this->usePugfile( '.pug-new' );

		$targetName = microtime( true );
		$result = $this->executePugCommand( 'show', [$targetName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: Unknown project or directory '{$targetName}'.", $result['output'] );
	}

	public function testShowSingleProjectReturnsListing()
	{
		$this->usePugfile( '.pug-enabled' );
		$result = $this->executePugCommand( 'show', ['pug/1'] );

		$this->assertSame( 0, $result['exit'] );
		$this->assertSame( 0, strpos( $result['output'], '* pug/1' ) );
		$this->assertSame( false, strpos( $result['output'], '* pug/2' ) );
	}

	public function testShowGroupReturnsListing()
	{
		$this->markTestIncomplete();

		$this->usePugfile( '.pug-groups' );
		$result = $this->executePugCommand( 'show', ['red'] );

		$this->assertSame( 0, $result['exit'] );
		$this->assertTrue( strpos( $result['output'], '* red/1' ) >= 0 );
		$this->assertTrue( strpos( $result['output'], '* red/2' ) >= 0 );
		$this->assertFalse( strpos( $result['output'], '* green/1' ) >= 0 );
	}
}
