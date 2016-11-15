<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI\Format;
use Huxtable\Core\File;

class Project implements \JsonSerializable
{
	const NAMESPACE_DELIMITER = '/';

	const SCM_GIT = 1;
	const SCM_SVN = 2;	// left for historical purposes
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
	 * @var	string
	 */
	protected $namespace;

	/**
	 * @var Huxtable\Core\File\Directory
	 */
	protected $source;

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
	 * @param	string							$name		Name of project
	 * @param	Huxtable\Core\File\Directory	$source		Path to project directory
	 * @param	boolean							$enabled	Enabled status
	 * @param	string							$updated	UNIX timestamp of last update
	 */
	public function __construct( $name, File\Directory $source, $enabled=true, $updated=null )
	{
		if( $source->getRealPath() != false )
		{
			$this->source = new File\Directory( $source->getRealPath() );	// expand relative paths
		}
		else
		{
			$this->source = $source;
		}

		/*
		 * Parse for namespace
		 */
		if( substr_count( $name, self::NAMESPACE_DELIMITER ) > 0 )
		{
			$namePieces = explode( self::NAMESPACE_DELIMITER, $name );
			$this->namespace = self::getNormalizedNamespaceString( $namePieces[0] );
		}

		$this->name = $name;

		$this->enabled = $enabled;
		$this->updated = $updated;
	}

	/**
	 * @return	void
	 */
	protected function detectSCM()
	{
		$this->scm = self::SCM_ERR;

		// Look for signs of SCM in working directory
		$dirCurrent = new File\Directory( $this->source->getRealPath() );

		do
		{
			try
			{
				$dirGit = $dirCurrent->childDir( '.git' );

				// Detecting a directory named .git instead of any matching file ensures that
				//   we'll traverse up to and then update the project root instead of a submodule
				if( $dirGit->exists() )
				{
					$this->scm = self::SCM_GIT;
					break;
				}
			}
			catch( \Exception $e )
			{
				// .git exists but it isn't a directory. This probably means we're in a submodule...
			}

			$dirCurrent = $dirCurrent->parent();
		}
		while( $dirCurrent->getPathname() != $dirCurrent->parent()->getPathname() );
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
	 * @param	string	$name
	 * @return	boolean|string
	 */
	public function getConfigValue( $name )
	{
		$commandConfig = Pug::executeCommand( "git config {$name}", false );

		switch( strtolower( $commandConfig['result'] ) )
		{
			case '':
				$value = null;
				break;

			case 'false':
				$value = false;
				break;

			case 'true':
				$value = true;
				break;

			default:
				$value = $commandConfig['result'];
				break;
		}

		return $value;
	}

	/**
	 * @return	\SplFileInfo
	 */
	public function getFileInfo()
	{
		return $this->source;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return	string|null
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @param	string	$namespace
	 * @return	string
	 */
	static public function getNormalizedNamespaceString( $namespace )
	{
		// Strip trailing '/'
		if( substr( $namespace, -1 ) == Project::NAMESPACE_DELIMITER )
		{
			$namespace = substr( $namespace, 0, strlen( $namespace ) - 1 );
		}

		return $namespace;
	}

	/**
	 * @return	string
	 */
	public function getPath()
	{
		return $this->source->getPathname();
	}

	/**
	 * @return	int
	 */
	public function getSCM()
	{
		if( is_null( $this->scm ) )
		{
			$this->detectSCM();
		}

		return $this->scm;
	}

	/**
	 * Take inventory of all submodule states
	 *
	 * @return	array
	 */
	public function getSubmoduleInventory()
	{
		$inventory = [];

		$delimiter = '{PUG_DELIMITER}';
		$commandSubmoduleStatus = "git submodule foreach --quiet --recursive 'echo \$name {$delimiter} \$toplevel/\$path {$delimiter} \$sha1 {$delimiter} `git rev-parse --abbrev-ref HEAD`'";
		$resultSubmoduleStatus = Pug::executeCommand( $commandSubmoduleStatus, false );

		foreach( $resultSubmoduleStatus['output'] as $result )
		{
			$resultPieces = explode( $delimiter, $result );

			$submoduleName   = trim( $resultPieces[0] );
			$dirSubmodule    = new File\Directory( trim( $resultPieces[1] ) );
			$submoduleCommit = trim( $resultPieces[2] );
			$submoduleBranch = trim( $resultPieces[3] );

			$projectSubmodule = new Project( $submoduleName, $dirSubmodule );
			$inventory[$submoduleName] =
			[
				'project'	=> $projectSubmodule,
				'commit'	=> $submoduleCommit,
				'branch'	=> $submoduleBranch,
			];
		}

		return $inventory;
	}
	/**
	 * Returns a timestamp of the project's last update
	 *
	 * @return	string
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function setName( $name )
	{
		$this->name = $name;
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
			throw new InvalidDirectoryException( "Project root '{$this->source}' is not a valid directory." );
		}
		if( !$this->getFileInfo()->isReadable() )
		{
			throw new InvalidDirectoryException( "Project root '{$this->source}' isn't readable." );
		}
		if( $this->scm == self::SCM_ERR )
		{
			throw new MissingSourceControlException( "Source control not found in '{$this->source->getPathname()}'." );
		}

		chdir( $this->source->getPathname() );

		echo "Updating '{$this->getName()}'... " . PHP_EOL . PHP_EOL;

		// Set up dependency managers
		$cocoaPods = new DependencyManager\CocoaPods( $this->source );
		$composer = new DependencyManager\Composer( $this->source );

		// Update the main repository
		$stashChanges = $this->getConfigValue( 'pug.update.stash' ) == true;

		if( $stashChanges )
		{
			echo  ' • Stashing local changes... ';
			$resultStashed = Pug::executeCommand( 'git stash save "pug: automatically stashing changes"' );
			echo PHP_EOL;
		}

		// Before we update anything, take a snapshot of submodule states
		$submoduleInventory = $this->getSubmoduleInventory();

		/*
		 * Get updates
		 */
		 /* Fetch & Rebase */
		if( $this->getConfigValue( 'pug.update.rebase' ) == true )
		{
			echo ' • Fetching... ';
			$resultGitFetch = Pug::executeCommand( 'git fetch' );

			echo PHP_EOL;
			echo ' • Rebasing... ';
			$resultGitRebase = Pug::executeCommand( 'git rebase' );
		}
		/* Pull (Fetch & Merge) */
		else
		{
			echo ' • Pulling... ';
			Pug::executeCommand( 'git pull' );
		}

		/* Pop stash */
		if( $stashChanges && $resultStashed['result'] != 'No local changes to save' )
		{
			echo PHP_EOL;
			echo  ' • Popping stash... ';
			Pug::executeCommand( 'git stash pop' );
		}

		/*
		 * Submodules
		 */
		$this->updateSubmodules( $submoduleInventory );

		/*
		 * Update dependencies if necessary
		 */
		$cocoaPods->update( $forceDependencyUpdate );
		$composer->update( $forceDependencyUpdate );

		echo PHP_EOL;

		$this->updated = time();
	}

	/**
	 * @param	array	$preInventory
	 * @return	void
	 */
	public function updateSubmodules( array $preInventory )
	{
		$modulesFile = $this->source->child( '.gitmodules' );

		if( !$modulesFile->exists() )
		{
			return;
		}

		$updateSubmodules = $this->getConfigValue( 'pug.update.submodules' ) !== false;
		if( !$updateSubmodules )
		{
			echo PHP_EOL;
			echo ' • Submodule updates were skipped due to configuration';
			echo PHP_EOL;

			return;
		}

		echo PHP_EOL;

		/*
		 * Perform the actual update
		 */
		echo ' • Updating submodules... ';
		Pug::executeCommand( 'git submodule update --init --recursive' );

		// Now that we've updated everything, take another snapshot of submodule states
		$postInventory = $this->getSubmoduleInventory();

		/*
		 * Restore submodules to previous states as appropriate
		 */
		foreach( $postInventory as $submoduleName => $postUpdateInfo )
		{
			$projectSubmodule = $postUpdateInfo['project'];
			$pathSubmodule = $projectSubmodule->getPath();

			$stringFormatted = new Format\String();
			$stringFormatted->foregroundColor( 'blue' );

			if( !isset( $preInventory[$submoduleName] ) )
			{
				continue;
			}

			$preUpdateInfo = $preInventory[$submoduleName];

			/*
			 * Submodules that were checked out to a branch before the update
			 */
			if( $preUpdateInfo['branch'] != 'HEAD' )
			{
				/*
				 * We're in a detached state now
				 */
				if( $preUpdateInfo['branch'] != $postUpdateInfo['branch'] )
				{
					chdir( $pathSubmodule );

					// Check the submodule out the the previous branch
					Pug::executeCommand( "git checkout {$preUpdateInfo['branch']}", false );

					$messageCheckout = "   % Submodule path '{$projectSubmodule->getName()}': checked out '{$preUpdateInfo['branch']}'";
					$stringFormatted->setString( $messageCheckout );
					echo $stringFormatted . PHP_EOL;
				}

				// If the pointer has changed, we should pull down submodule changes as well...?
				if( $preUpdateInfo['commit'] != $postUpdateInfo['commit'] )
				{
					chdir( $pathSubmodule );

					$messagePulling = "   % Submodule path '{$projectSubmodule->getName()}': pulling '{$preUpdateInfo['branch']}'... ";
					$stringFormatted->setString( $messagePulling );
					echo $stringFormatted;

					Pug::executeCommand( 'git pull', false );

					$stringFormatted->setString( 'done.' );
					echo $stringFormatted . PHP_EOL;
				}
			}
		}
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		return [
			'name' => $this->getName(),
			'path' => $this->source->getPathname(),
			'enabled' => $this->enabled,
			'updated' => $this->updated
		];
	}
}
