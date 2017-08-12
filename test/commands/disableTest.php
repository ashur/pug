<?php

/*
 * This file is part of Pug
 */

include_once( 'PugTestCase.php' );

use PHPUnit\Framework\TestCase;

class disableTest extends PugTestCase
{
	public function testDisableAll()
	{
		$this->usePugfile( '.pug-enabled' );
		$result = $this->executePugCommand( 'disable', ['all'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
  pug/1
  pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testDisableEmptyQueryDisplaysUsage()
	{
		$result = $this->executePugCommand( 'disable' );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( 'usage: pug disable [all|<group>|<project>]', $result['output'] );
	}

	public function testDisableExistingGroup()
	{
		$this->usePugfile( '.pug-enabled' );
		$result = $this->executePugCommand( 'disable', ['pug'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
  pug/1
  pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testDisableExistingSingleProject()
	{
		$this->usePugfile( '.pug-enabled' );
		$result = $this->executePugCommand( 'disable', ['pug/1'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
  pug/1
* pug/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testDisableNonExistentTargetReturnsError()
	{
		$targetName = microtime( true );
		$result = $this->executePugCommand( 'disable', [$targetName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: No groups or projects match '{$targetName}'.", $result['output'] );
	}
}
