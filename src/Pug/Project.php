<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use \Huxtable\Output;

class Project implements \JsonSerializable
{
	const SCM_GIT = 1;
	const SCM_SVN = 2;
	const SCM_ERR = 3;

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
	protected $scm;

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
		$this->path = new \SplFileInfo( $path );
		$this->enabled = $enabled;
		$this->updated = $updated;
	}

	/**
	 * @return	void
	 */
	protected function detectSCM()
	{
		$this->scm = self::SCM_ERR;

		$cwd = $this->path;

		do
		{
			// Look for signs of SCM in working directory
			$gitFile = new \SplFileInfo( $cwd->getRealPath() . '/.git' );

			// Detecting a directory named .git instead of any matching file ensures that
			//   we'll traverse up to and then update the project root instead of a submodule
			if( $gitFile->isDir() )
			{
				$this->scm = self::SCM_GIT;
				break;
			}
			else
			{
				$svnFile = new \SplFileInfo( $cwd->getRealPath() . '/.svn' );

				if( $svnFile->isDir() )
				{
					$this->scm = self::SCM_SVN;
					break;
				}
			}

			$cwd = $cwd->getPathInfo();
		}
		while( $cwd->getPathname() != $cwd->getPathInfo()->getPathname() );

		// Update project name if necessary
		if( $this->name == $this->path )
		{
			$this->name = $cwd;
		}

		$this->path = $cwd;
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
	protected function executeCommand( $command, $prefix=' >' )
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
					echo Output::colorize( "  {$prefix} " . $line, $color ) . PHP_EOL;
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
	 * Update a project's working copy and its dependencies
	 *
	 * @return	void
	 */
	public function update()
	{
		$this->detectSCM();

		if( !$this->getFileInfo()->isDir() )
		{
			throw new InvalidDirectoryException( "Project root '{$this->getPath()}' is not a valid directory" );
		}
		if( !$this->getFileInfo()->isReadable() )
		{
			throw new InvalidDirectoryException( "Project root '{$this->getPath()}' isn't readable" );
		}
		if( $this->scm == self::SCM_ERR )
		{
			throw new MissingSourceControlException( "Source control not found in '{$this->path->getPathname()}'" );
		}

		chdir( $this->path->getPathname() );

		echo "Updating '{$this->getName()}'... " . PHP_EOL . PHP_EOL;

		switch( $this->scm )
		{
			// --
			// Git
			// --
			case self::SCM_GIT:
			
				echo ' • Pulling... ';
				$resultGit = $this->executeCommand( 'git pull' );
	
				$modulesFile = new \SplFileInfo( $this->path->getRealPath() . '/.gitmodules' );
				if( $modulesFile->isFile() )
				{
					echo ' • Updating submodules... ';
					$this->executeCommand( 'git submodule update --init --recursive' );
				}

				break;

			// --
			// Subversion
			// --
			case self::SCM_SVN:

				echo ' • Updating working copy... ';
				$resultSvn = $this->executeCommand( 'svn up' );

				break;
		}

		// --
		// CocoaPods
		// --
		$podFile = new \SplFileInfo( $this->path->getRealPath() . '/Podfile' );

		if( $podFile->isFile() )
		{
			echo ' • Updating CocoaPods... ';
			$this->executeCommand( 'pod install' );
		}

		// --
		// Composer
		// --
		$composerFile = new \SplFileInfo( $this->path->getRealPath() . '/composer.json' );

		if( $composerFile->isFile() )
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
