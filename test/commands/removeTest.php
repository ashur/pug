<?php

/*
 * This file is part of Pug
 */

include_once( 'PugTestCase.php' );

use PHPUnit\Framework\TestCase;

class removeTest extends PugTestCase
{
	public function testRemoveEmptyQueryDisplaysUsage()
	{
		$result = $this->executePugCommand( 'remove' );

		$this->assertEquals( 1, $result['exit'] );

		$expectedOutput = <<<OUTPUT
usage: pug rm [all|<group>[/<project>]|<project>] [-y|--yes|--assume-yes]

OPTIONS
    -y, --yes, --assume-yes
        Automatic yes to prompts. Assume "yes" as answer to all prompts and run
        non-interactively.

OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testRemoveAllReturnsNoOutput()
	{
		$this->usePugfile( '.pug-enabled' );
		$result = $this->executePugCommand( 'remove', ['all', '--yes'] );

		$this->assertEquals( 0, $result['exit'] );
		$this->assertEquals( '', $result['output'] );
	}

	public function testRemoveGroupReturnsListing()
	{
		$this->usePugfile( '.pug-groups' );
		$result = $this->executePugCommand( 'remove', ['green', '--yes'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
* red/1
* red/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testRemoveProjectReturnsListing()
	{
		$this->usePugfile( '.pug-groups' );
		$result = $this->executePugCommand( 'remove', ['red/1'] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
* green/1
* red/2
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testRemoveNonExistentTargetReturnsError()
	{
		$this->usePugfile( '.pug-groups' );

		$targetName = microtime( true );
		$result = $this->executePugCommand( 'remove', [$targetName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: No groups or projects match '{$targetName}'.", $result['output'] );
	}
}
