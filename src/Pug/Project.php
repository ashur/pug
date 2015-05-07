<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use \Huxtable\Output;

class Project implements \JsonSerializable
{
	/**
	 * @var boolean
	 */
	protected $enabled;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var \SplFileInfo
	 */
	protected $path;

	/**
	 * @var int
	 */
	protected $updated;

	/**
	 * @param	string	$name		Name of project
	 * @param	string	$path		Path to project directory
	 * @param	boolean	$enabled	Enabled status
	 * @param	string	$updated	UNIX timestamp of last update
	 */
	public function __construct($name, $path, $enabled=true, $updated=null)
	{
		$this->name = $name;
		$this->path = new \SplFileInfo($path);
		$this->enabled = $enabled;
		$this->updated = $updated;
	}

	/**
	 * @return	void
	 */
	public function disable()
	{
		$this->enabled = false;
	}

	/**
	 * @return	void
	 */
	public function enable()
	{
		$this->enabled = true;
	}

	/**
	 * Execute a command, generate friendly output and return the result
	 * 
	 * @param	string	$command
	 * @return	boolean
	 */
	public function executeCommand( $command )
	{
		$command = $command . ' 2>&1';	// force output to be where we need it

		$result = exec( $command, $output, $exitCode );

		if( count( $output ) == 0 )
		{
			echo 'done.' . PHP_EOL;
		}
		else
		{
			echo PHP_EOL;
			$color = $exitCode == 0 ? 'green' : 'red';

			foreach( $output as $line )
			{
				if( strlen( $line ) > 0 )
				{
					echo Output::colorize( '   > ' . $line, $color ) . PHP_EOL;
				}
			}
		}

		return [
			'result' => $result,
			'exitCode' => $exitCode
		];
	}

	/**
	 * @return	boolean
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @return	\SplFileInfo
	 */
	public function getFileInfo()
	{
		return $this->path;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return	string
	 */
	public function getPath()
	{
		return $this->path->getPathname();
	}

	/**
	 * @return	string
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @return	boolean
	 */
	public function update()
	{
		if( !$this->getFileInfo()->isDir() )
		{
			throw new InvalidDirectoryException( "Project root '{$this->getPath()}' is not a valid directory" );
		}
		if( !$this->getFileInfo()->isReadable() )
		{
			throw new InvalidDirectoryException( "Project root '{$this->getPath()}' isn't readable" );
		}

		// --
		// SCM
		// --
		$gitFile = new \SplFileInfo( $this->path->getRealPath() . '/.git' );
		$svnFile = new \SplFileInfo( $this->path->getRealPath() . '/.svn' );

		$scmFound = $gitFile->isDir() || $svnFile->isDir();
		if( !$scmFound )
		{
			// @todo	Improve detection by traversing up the path (in case the working directory is a descendent of a directory that is under SCM)
			throw new MissingSourceControlException( "Source control not found in '{$this->path->getPathname()}'" );
		}

		echo "Updating '{$this->getName()}'... ";
		echo PHP_EOL . PHP_EOL;

		chdir($this->path->getPathname());

		// --
		// Git
		// --
		if( $gitFile->isDir() )
		{
			echo ' • Pulling... ';
			$resultGit = $this->executeCommand( 'git pull' );

			$modulesFile = new \SplFileInfo( $this->path->getRealPath() . '/.gitmodules' );
			if( $modulesFile->isFile() )
			{
				echo ' • Updating submodules... ';
				$this->executeCommand( 'git submodule update --init --recursive' );
			}
		}

		// --
		// Subversion
		// --
		if( $svnFile->isDir() )
		{
			echo ' • Updating working copy... ';
			$resultSvn = $this->executeCommand( 'svn up' );
		}

		// --
		// CocoaPods
		// --
		$podFile = new \SplFileInfo($this->path->getRealPath() . '/Podfile');

		if($podFile->isFile())
		{
			echo ' • Updating CocoaPods... ';
			$this->executeCommand( 'pod install' );
		}

		// --
		// Composer
		// --
		$composerFile = new \SplFileInfo($this->path->getRealPath() . '/composer.json');

		if($composerFile->isFile())
		{
			echo ' • Updating Composer... ';
			$this->executeCommand( 'composer update' );
		}

		echo PHP_EOL;

		$this->updated = time();
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		return [
			'name' => $this->getName(),
			'path' => $this->getPath(),
			'enabled' => $this->enabled,
			'updated' => $this->updated
		];
	}
}

?>
