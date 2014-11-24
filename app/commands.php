<?php

/*
 * This file is part of Pug
 */

$commands = [];

// Project
$project = new Huxtable\Command('project', 'List, create or delete projects', function()
{
	$pug = new Pug\Pug();
	listProjects($pug->getProjects());
});

$projectAdd = new Huxtable\Command('add', 'Add a project named <name> which lives at <path>.', function($name, $path)
{
	$pug = new Pug\Pug();
	$pug->addProject(new Pug\Project($name, $path));
	listProjects($pug->getProjects());
});

$projectDisable = new Huxtable\Command('disable', 'Disable an existing project named <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->disableProject($name);
	listProjects($pug->getProjects());
});

$projectEnable = new Huxtable\Command('enable', 'Enable a disabled project named <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->enableProject($name);
	listProjects($pug->getProjects());
});

$projectRemove = new Huxtable\Command('remove', 'Remove the project named <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->removeProject($name);
	listProjects($pug->getProjects());
});

$projectSetPath = new Huxtable\Command('set-path', 'Changes the path for the named project.', function($name, $path)
{
	$pug = new Pug\Pug();
	$pug->setPathForProject(new Pug\Project($name, $path));
});

$projectShow = new Huxtable\Command('show', 'Show details for the project named <name>.', function($name)
{
	$pug = new Pug\Pug();
	listProjects(array($pug->getProject($name)), true);
});

$project->addSubcommand($projectAdd);
$project->addSubcommand($projectDisable);
$project->addSubcommand($projectEnable);
$project->addSubcommand($projectRemove);
$project->addSubcommand($projectSetPath);
$project->addSubcommand($projectShow);

$commands[] = $project;

// Update
$update = new Huxtable\Command('update', 'Fetch project updates', function($app)
{
	$pug = new Pug\Pug();
	$pug->update($app);
});

$update->setUsage("update [<app>|all]");

$commands[] = $update;

/**
 * @param	array	$projects
 * @param	boolean	$showPath
 */
function listProjects(array $projects, $showPath=false)
{
	foreach($projects as $project)
	{
		$enabled = $project->isEnabled() === true ? '*' : '-';
		$pattern = $showPath ? " %s %-16s%-20s%s\n" : " %s %-16s%s\n";

		printf($pattern, $enabled, $project->getName(), $project->getUpdated(), $project->getPath());
	}
}

?>
