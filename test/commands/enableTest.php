<?php

/*
 * This file is part of Pug
 */

include_once( 'PugTestCase.php' );

use PHPUnit\Framework\TestCase;

class enableTest extends PugTestCase
{
	public function testEnableAll()
	{
		$this->usePugfile( '.pug-disabled' );
		$result = $this->executePugCommand( 'enable', ['all'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
* pug/1
* pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testEnableEmptyQueryDisplaysUsage()
	{
		$result = $this->executePugCommand( 'enable' );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( 'usage: pug enable [all|<group>|<project>]', $result['output'] );
	}

	public function testEnableExistingGroup()
	{
		$this->usePugfile( '.pug-disabled' );
		$result = $this->executePugCommand( 'enable', ['pug'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
* pug/1
* pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testEnableExistingSingleProject()
	{
		$this->usePugfile( '.pug-disabled' );
		$result = $this->executePugCommand( 'enable', ['pug/1'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
* pug/1
  pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testEnableNonExistentTargetReturnsError()
	{
		$targetName = microtime( true );
		$result = $this->executePugCommand( 'enable', [$targetName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: No groups or projects match '{$targetName}'.", $result['output'] );
	}
}
