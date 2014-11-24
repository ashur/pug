<?php

/*
 * This file is part of Pug
 */

$commands = [];

$commands[] = new Huxtable\Command('list', 'List all tracked projects', function()
{
	$pug = new Pug\Pug();
	listProjects($pug->getProjects());
});

$commands[] = new Huxtable\Command('track', 'Track a project named <name> which lives at <path>', function($name, $path='')
{
	$pug = new Pug\Pug();
	$pug->addProject(new Pug\Project($name, $path));
	listProjects($pug->getProjects());
});

$commands[] = new Huxtable\Command('untrack', 'Stop tracking the project named <name>.', function($name)
{
	$pug = new Pug\Pug();
	$pug->removeProject($name);
	listProjects($pug->getProjects());
});

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
function listProjects(array $projects)
{
	foreach($projects as $project)
	{
		printf(" %-16s%-20s%s\n", $project->getName(), $project->getUpdated(), $project->getPath());
	}
}

?>
