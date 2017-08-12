<?php

/*
 * This file is part of Pug
 */

include_once( 'PugTestCase.php' );

use PHPUnit\Framework\TestCase;

class renameTest extends PugTestCase
{
	public function testRenameEmptyQueryDisplaysUsage()
	{
		$result = $this->executePugCommand( 'rename' );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( 'usage: pug rename <old> <new>', $result['output'] );
	}

	public function testRenameNonExistentProjectReturnsError()
	{
		$this->usePugfile( '.pug-new' );

		$oldProjectName = microtime( true );
		$newProjectName = microtime( true );

		$result = $this->executePugCommand( 'rename', [$oldProjectName, $newProjectName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: No project matches '{$oldProjectName}'.", $result['output'] );
	}

	public function testRenameProjectToExistingProjectNameReturnsError()
	{
		$this->usePugfile( '.pug-disabled' );

		$oldProjectName = 'pug/2';
		$newProjectName = 'pug/1';

		$result = $this->executePugCommand( 'rename', [$oldProjectName, $newProjectName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: A project named '{$newProjectName}' already exists.", $result['output'] );
	}

	public function testRenameProjectReturnsListing()
	{
		$this->usePugfile( '.pug-disabled' );

		$oldProjectName = 'pug/2';
		$newProjectName = 'pug/3';

		$result = $this->executePugCommand( 'rename', [$oldProjectName, $newProjectName] );

		$this->assertEquals( 0, $result['exit'] );

		$expectedOutput = <<<OUTPUT
  pug/1
  pug/3
OUTPUT;

		$this->assertEquals( $expectedOutput, $result['output'] );
	}

	public function testRenameProjectToExistingGroupNameReturnsError()
	{
		$this->markTestIncomplete();

		$this->usePugfile( '.pug-disabled' );

		$oldProjectName = 'pug/2';
		$newProjectName = 'pug';

		$result = $this->executePugCommand( 'rename', [$oldProjectName, $newProjectName] );

		$this->assertEquals( 1, $result['exit'] );
		$this->assertEquals( "pug: A group named '{$newProjectName}' already exists.", $result['output'] );
	}
}
