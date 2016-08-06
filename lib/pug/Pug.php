<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI;
use Huxtable\Core\File;
use Huxtable\Core\HTTP;

define( 'PUG_CONFIG', getenv('HOME') . DIRECTORY_SEPARATOR . '.pug' );

class Pug
{
	/**
	 * @var array
	 */
	protected $projects=[];

	/**
	 * @return	void
	 */
	public function __construct()
	{
		$fileConfig = new File\File( PUG_CONFIG );

		/*
		 * Make sure the config file is ready to go
		 */
		if( !$fileConfig->exists() )
		{
			$fileConfig->create();
		}

		if( !$fileConfig->isReadable() )
		{
			throw new \Exception( 'Can\'t read from ' . PUG_CONFIG, 1 );
		}
		if( !$fileConfig->isWritable() )
		{
			throw new \Exception( 'Can\'t write to ' . PUG_CONFIG, 1 );
		}

		$json = json_decode( $fileConfig->getContents(), true );

		/*
		 * Load projects
		 */
		if( isset( $json['projects'] ) )
		{
			foreach( $json['projects'] as $projectInfo )
			{
				$enabled = isset( $projectInfo['enabled'] ) ? $projectInfo['enabled'] : true;
				$updated = isset( $projectInfo['updated'] ) ? $projectInfo['updated'] : null;
				$dirProject = new File\Directory( $projectInfo['path'] );

				$project = new Project( $projectInfo['name'], $dirProject, $enabled, $updated );

				$this->projects[] = $project;
			}
		}

		$this->sortProjects();
	}

	/**
	 * @param	Project	$project
	 */
	public function addProject(Project $project)
	{
		foreach($this->projects as $current)
		{
			if($project->getName() == $current->getName())
			{
				throw new \Exception("Project '{$project->getName()}' already exists. See 'pug show'.", 1);
			}
		}

		if( $project->getSCM() == Project::SCM_ERR )
		{
			throw new \Exception( "Source control not found in '{$project->getPath()}'." );
		}

		$this->projects[] = $project;
		$this->write();
	}

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function disableProject( $name )
	{
		$count = count($this->projects);
		$disabled = 0;

		for( $i=0; $i<$count; $i++ )
		{
			if( $this->projects[$i]->getName() == $name )
			{
				$this->projects[$i]->disable();
				$disabled++;
			}
		}

		if( $disabled == 0 )
		{
			throw new CLI\Command\CommandInvokedException("Project '{$name}' not found.", 1);
		}

		$this->write();
	}

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function enableProject( $name )
	{
		$count = count($this->projects);
		$enabled = 0;

		for( $i=0; $i<$count; $i++ )
		{
			if( $this->projects[$i]->getName() == $name )
			{
				$this->projects[$i]->enable();
				$enabled++;
			}
		}

		if($enabled == 0)
		{
			throw new CLI\Command\CommandInvokedException("Project '{$name}' not found.", 1);
		}

		$this->write();
	}

	/**
	 * Execute a command, generate friendly output and return the result
	 *
	 * @param	string		$command
	 * @param	boolean		$echo
	 * @return	boolean
	 */
	static public function executeCommand( $command, $echo=true )
	{
		$command = $command . ' 2>&1';	// force output to be where we need it
		$result = exec( $command, $outputCommand, $exitCode );
		$output = [];

		if( count( $outputCommand ) == 0 )
		{
			$output[] = 'done.';
		}
		else
		{
			$output[] = '';
			$color = $exitCode == 0 ? 'green' : 'red';

			foreach( $outputCommand as $line )
			{
				$formattedLine = new CLI\Format\String( "   > {$line}" );
				$formattedLine->foregroundColor( $color );

				if( strlen( $line ) > 0 )
				{
					$output[] = $formattedLine;
				}
			}
		}

		if( $echo )
		{
			foreach( $output as $line )
			{
				echo $line . PHP_EOL;
			}
		}

		return [
			'output' => $outputCommand,
			'result' => $result,
			'exitCode' => $exitCode
		];
	}

	/**
	 * @return	string
	 */
	public function getCurrentVersion()
	{
		$config = include( dirname( __DIR__ ) . '/config.php' );
		return $config['version'];
	}

	/**
	 * Return array of all enabled projects
	 *
	 * @return	array
	 */
	public function getEnabledProjects()
	{
		$enabled = [];

		foreach( $this->projects as $project )
		{
			if( $project->isEnabled() )
			{
				$enabled[] = $project;
			}
		}

		return $enabled;
	}

	/**
	 * @return	array
	 */
	public function getLatestRelease()
	{
		$urlRepoReleases = 'https://api.github.com/repos/ashur/pug/releases';

		/* Header */
		$httpRequest = new HTTP\Request( $urlRepoReleases );
		$httpRequest->addHeader( 'User-Agent', 'ashur/pug' );

		/* Perform request */
		$httpResponse = HTTP::get( $httpRequest );
		$responseStatus = $httpResponse->getStatus();

		if( $responseStatus['code'] >= 400 )
		{
			throw new \Exception( "GitHub returned an error: '{$responseStatus['message']}'" );
		}

		$releases = json_decode( $httpResponse->getBody(), true );
		if( json_last_error() != JSON_ERROR_NONE )
		{
			$jsonError = json_last_error_msg();
			throw new \Exception( "Couldn't understand the response from GitHub: '{$jsonError}'" );
		}

		if( !is_array( $releases ) )
		{
			throw new \Exception( "GitHub returned an unexpected response" );
		}

		$latestRelease = array_shift( $releases );
		return $latestRelease;
	}

	/**
	 * @param	string	$name
	 * @return	Project
	 */
	public function getProject( $name )
	{
		foreach( $this->projects as &$project )
		{
			if($project->getName() == $name)
			{
				return $project;
			}
		}

		// No registered project matches, let's try a file path
		$dirProject = new File\Directory( $name );

		if( $dirProject->exists() )
		{
			$projectPath = $dirProject->getRealpath();

			// Let's check to see if a tracked project is already registered at this path
			foreach( $this->projects as &$project )
			{
				if( strtolower( $project->getPath() ) == strtolower( $projectPath ) )
				{
					return $project;
				}
			}

			// Definitely no registered project matches, down to the bare file path itself
			return new Project( $dirProject->getRealpath(), $dirProject );
		}

		// No project or file path matches, time to bail
		throw new \Exception( "Unknown project or directory '{$name}'." );
	}

	/**
	 * @param	boolean	$sortByUpdated
	 * @return	array
	 */
	public function getProjects( $sortByUpdated = false )
	{
		if( $sortByUpdated )
		{
			$this->sortProjects( $sortByUpdated );
		}

		return $this->projects;
	}

	/**
	 * @param	string	$name
	 */
	public function removeProject($name)
	{
		$count   = count($this->projects);
		$removed = 0;

		for($i=0; $i < $count; $i++)
		{
			if($this->projects[$i]->getName() == $name)
			{
				unset($this->projects[$i]);
				$removed++;
			}
		}

		if( $removed == 0 )
		{
			throw new CLI\Command\CommandInvokedException("Project '{$name}' not found.", 1);
		}

		$this->write();
	}

	/**
	 * @param	Project	$project
	 */
	public function setPathForProject(Project $project)
	{
		$updated = 0;
		for($i=0; $i < count($this->projects); $i++)
		{
			if($this->projects[$i]->getName() == $project->getName())
			{
				$this->projects[$i] = $project;
				$updated++;
			}
		}

		if($updated == 0)
		{
			throw new CLI\Command\CommandInvokedException("Project '{$project->getName()}' not found.", 1);
		}

		$this->write();
	}

	/**
	 * @param	boolean	$sortByUpdated
	 */
	protected function sortProjects($sortByUpdated = false)
	{
		$name = [];
		$updated = [];

		// Sort projects by name
		foreach($this->projects as $project)
		{
			$name[] = $project->getName();
			$updated[] = $project->getUpdated();
		}

		if ($sortByUpdated == true)
		{
			array_multisort($updated, SORT_DESC, $name, SORT_ASC, $this->projects);
			return;
		}

		array_multisort($name, SORT_ASC, $this->projects);
	}

	/**
	 * Attempt to update a single project based on its target (registered project name or filepath)
	 *
	 * @param	string	$target					Target to update
	 * @param	boolean	$forceDependencyUpdate
	 */
	public function updateProject( $target, $forceDependencyUpdate=false )
	{
		if( $target instanceof Project )
		{
			$project = $target;
		}
		else
		{
			$project = $this->getProject( $target );
		}

		$project->update( $forceDependencyUpdate );
		$this->write();
	}

	/**
	 * Run 'git pull' and 'git submodule update...' on the local pug repo itself
	 */
	public function upgradeSelf()
	{
		$pathPug = dirname( dirname( __DIR__ ) );
		$dirPug = new File\Directory( $pathPug );

		chdir( $dirPug->getPathname() );

		/* Pull */
		$resultPull = self::executeCommand( 'git pull', false );
		if( $resultPull['exitCode'] != 0 )
		{
			throw new \Exception( array_shift( $resultPull['output'] ) );
		}

		/* Update submodules */
		$resultUpdateSubmodules = self::executeCommand( 'git submodule update --init --recursive', false );
		if( $resultUpdateSubmodules['exitCode'] != 0 )
		{
			throw new \Exception( array_shift( $resultUpdateSubmodules['output'] ) );
		}
	}

	/**
	 */
	protected function write()
	{
		$this->sortProjects();
		$projects = $this->projects;

		$json = json_encode(compact('projects'), JSON_PRETTY_PRINT);

		file_put_contents(PUG_CONFIG, $json);
	}
}
