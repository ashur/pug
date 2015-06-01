<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use \Huxtable\Output;
use \Huxtable\Command\CommandInvokedException;

define( 'PUG_CONFIG', getenv('HOME').DIRECTORY_SEPARATOR.'.pug' );

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
		$projects = [];
		$fileInfo = new \SplFileInfo(PUG_CONFIG);

		if(!file_exists(PUG_CONFIG))
		{
			touch($fileInfo->getPathname());
		}
		else
		{
			if(!is_readable(PUG_CONFIG))
			{
				throw new CommandInvokedException("Can't read from ~/.pug", 1);
			}
			if(!is_writable(PUG_CONFIG))
			{
				throw new CommandInvokedException("Can't write to ~/.pug", 1);
			}

			$json = json_decode(file_get_contents(PUG_CONFIG), true);

			if(isset($json['projects']))
			{
				foreach($json['projects'] as $project)
				{
					$enabled = isset( $project['enabled'] ) ? $project['enabled'] : true;
					$updated = isset( $project['updated'] ) ? $project['updated'] : null;
					$this->projects[] = new Project($project['name'], $project['path'], $enabled, $updated);
				}
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
				throw new CommandInvokedException("The project '{$project->getName()}' already exists. See 'pug list'", 1);
			}
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
			throw new CommandInvokedException("Project '{$name}' not found", 1);
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
			throw new CommandInvokedException("Project '{$name}' not found", 1);
		}

		$this->write();
	}

	/**
	 * Execute a command, generate friendly output and return the result
	 * 
	 * @param	string	$command
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
				if( strlen( $line ) > 0 )
				{
					$output[] = Output::colorize( "   > " . $line, $color );
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
			'output' => $output,
			'result' => $result,
			'exitCode' => $exitCode
		];
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
		$file = new \SplFileInfo( $name );

		if( $file->isDir() )
		{
			$projectPath = $file->getRealpath();

			// Let's check to see if a tracked project is already registered at this path
			foreach( $this->projects as &$project )
			{
				if( strtolower( $project->getPath() ) == strtolower( $projectPath ) )
				{
					return $project;
				}
			}

			// Definitely no registered project matches, down to the bare file path itself
			return new Project( $file->getRealpath(), $file->getRealPath() );
		}

		// No project or file path matches, time to bail
		throw new CommandInvokedException( "Unknown project or directory '{$name}'", 1 );
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
			throw new CommandInvokedException("Project '{$name}' not found", 1);
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
			throw new CommandInvokedException("Project '{$project->getName()}' not found", 1);
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
	 * @param	string	$target		Target to update
	 */
	public function updateProject( $target )
	{
		if( $target instanceof \Pug\Project )
		{
			$project = $target;
		}
		else
		{
			$project = $this->getProject( $target );
		}

		$project->update();
		$this->write();
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

?>
