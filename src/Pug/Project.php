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
	 * @var boolean
	 */
	protected $usesCocoaPods=false;

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

		// Look for signs of SCM in working directory
		do
		{
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
	 * @param	boolean	$forceDependencyUpdate
	 * @return	void
	 */
	public function update( $forceDependencyUpdate )
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

		// Set up dependency managers
		$cocoaPods = new DependencyManager\CocoaPods( $this->path );
		$composer = new DependencyManager\Composer( $this->path );

		// Update the main repository
		switch( $this->scm )
		{
			case self::SCM_GIT:

				$resultStash = Pug::executeCommand( 'git config pug.update.autostash', false );
				$stashChanges = strtolower( $resultStash['result'] ) == 'true';

				if( $stashChanges )
				{
					echo  ' • Stashing local changes... ';
					$resultStashed = Pug::executeCommand( 'git stash save "pug: automatically stashing changes"' );
					echo PHP_EOL;
				}

				// Build and execute 'pull' command
				$commandPull = 'git pull';

				echo ' • Pulling... ';
				$resultGit = Pug::executeCommand( $commandPull );

				if( $stashChanges && $resultStashed['result'] != 'No local changes to save' )
				{
					echo PHP_EOL;
					echo  ' • Popping stash... ';
					Pug::executeCommand( 'git stash pop' );
				}

				// Submodules
				$modulesFile = new \SplFileInfo( $this->path->getRealPath() . '/.gitmodules' );
				if( $modulesFile->isFile() )
				{
					$resultSubmodules = Pug::executeCommand( 'git config pug.update.submodules', false );
					$updateSubmodules = strtolower( $resultSubmodules['result'] ) != 'false';
	
					if( $updateSubmodules )
					{
						echo PHP_EOL;
						echo ' • Updating submodules... ';
						Pug::executeCommand( 'git submodule update --init --recursive' );
					}
					else
					{
						echo PHP_EOL;
						echo ' • Submodule updates were skipped due to configuration';
						echo PHP_EOL;
					}
				}

				break;

			case self::SCM_SVN:

				echo ' • Updating working copy... ';
				$resultSvn = Pug::executeCommand( 'svn up' );

				break;
		}

		// Update dependencies if necessary
		$cocoaPods->update( $forceDependencyUpdate );
		$composer->update( $forceDependencyUpdate );

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
