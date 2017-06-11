<?php

/*
 * This file is part of Pug
 */
namespace Pug;

use Huxtable\CLI\Command;
use Huxtable\CLI\Output;
use Huxtable\CLI\FormattedString;

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
	echo PHP_EOL;
	echo " • Upgrading to {$latestRelease['tag_name']}... ";
	echo PHP_EOL . PHP_EOL;

	try
	{
		$pug->upgradeSelf();
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( "Could not upgrade: '{$e->getMessage()}'" );
	}

	/* Display the release description */
	$stringFormatted = new FormattedString();

	$releaseBodyLines = explode( "\r\n", trim( $latestRelease['body'] ) );
	foreach( $releaseBodyLines as $releaseBodyLine )
	{
		$stringFormatted->foregroundColor( 'green' );
		$stringFormatted->setString( "   {$releaseBodyLine}" );

		echo $stringFormatted . PHP_EOL;
	}
	echo PHP_EOL;
});

return $commandUpgrade;
