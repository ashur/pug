<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI\Command;
use Huxtable\CLI\Output;
use Huxtable\CLI\Format;

/**
 * @command		upgrade
 * @desc		Fetch the newest version of Pug from GitHub
 * @usage		upgrade
 */
$commandUpgrade = new Command( 'upgrade', 'Fetch the newest version of Pug', function()
{
	$pug = new Pug();

	/* Compare local version to latest GitHub release */
	$currentVersion = $pug->getCurrentVersion();

	$latestRelease = $pug->getLatestRelease();
	$latestVersion = substr( $latestRelease['tag_name'], 1 );

	$canUpgrade = version_compare( $currentVersion, $latestVersion, '<' );

	if( !$canUpgrade )
	{
		return "You're up-to-date! v{$currentVersion} is the latest version available.";
	};

	/*
	 * Upgrade
	 */
	echo 'Upgrading... ';

	try
	{
		$pug->upgradeSelf();
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( "Could not upgrade: '{$e->getMessage()}'");
	}

	echo 'done.' . PHP_EOL . PHP_EOL;

	/* Display the release description */
	$stringFormatted = new Format\String();
	$releaseTitle = sprintf( '%s: %s', $latestRelease['tag_name'], $latestRelease['name'] );

	$stringFormatted->setString( $releaseTitle );
	$stringFormatted->foregroundColor( 'yellow' );
	$stringFormatted->bold();

	$releaseBodyLines = explode( "\r\n", $latestRelease['body'] );
	foreach( $releaseBodyLines as $releaseBodyLine )
	{
		echo "   {$releaseBodyLine}" . PHP_EOL;
	}
});

return $commandUpgrade;
