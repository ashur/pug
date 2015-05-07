![pug](http://pug.sh.s3.amazonaws.com/pug.png)

Quickly update local projects and their dependencies with a single command. Pug currently supports Subversion and Git, and [CocoaPods](https://cocoapods.org/) and [Composer](https://getcomposer.org).


## Setup

> See [Installation](INSTALL.md) for details on getting started with Pug

### Configuration

If a timezone isn't set in php.ini, Pug defaults to `UTC`. To override either, open [config.php](https://github.com/ashur/pug/blob/master/config.php.dist) and specify a [supported timezone](http://php.net/manual/en/timezones.php).

## Update

Pug can [fetch updates](#underthehood) for projects that live at arbitrary paths. Some thrilling examples:

```bash
$ pug update ../plank
$ pug update ~/Developer/plank
```

Update the project at your current working directory:

```bash
$ pug update
$ pug update ./
```

Admittedly, the convenience here is small for simple projects. As things get more complicated, however, Pug gives you a tiny leg up: a single command is all you need to update Git repositories _and_ their submodules _and_ dependencies managed by tools like Composer.

```bash
$ pug up ~/Developer/access
Updating '/Users/Ashur/Developer/access'... 

 • Pulling... 
   > Already up-to-date.
 • Updating submodules... 
   > Submodule path 'vendor/huxtable': checked out '0cccd17fe78fdd9a778f5025b244eafc68553764'

```

> 💡 **Tip**: Save yourself a few keystrokes with `pug up`

## Tracking

If you juggle multiple projects that need to stay up-to-date, Pug really starts to shine with tracking:

```bash
$ pug add tapas ~/Developer/tapas
* dotfiles
* plank
* tapas
```

Updating a tracked project is easy:

```bash
$ pug up tapas
```

Updating all your projects at once is even easier:

```bash
$ pug up all
Updating 'dotfiles'... 

 • Pulling... 
   > Already up-to-date.
 • Updating submodules... done.

Updating 'plank'... 

 • Pulling... 
   > Already up-to-date.
 • Updating Composer... 
   > Loading composer repositories with package information
   > Updating dependencies (including require-dev)
   > Nothing to install or update
   > Generating autoload files

Updating 'tapas'... 

 • Pulling... 
   > Already up-to-date.

```

### Enable/Disable
Need to focus on a subset of your projects for a while? Disable anything you don't need:

```bash
$ pug disable plank
* dotfiles
  plank
* tapas
```

Pug will hold onto the project definition but skip it when you `update all`:

```bash
$ pug update all
Updating 'dotfiles'... 

 • Pulling... 
   > Already up-to-date.
 • Updating submodules... done.

Updating 'tapas'... 

 • Pulling... 
   > Already up-to-date.

```


## Under the hood

Okay so but what is Pug doing when it updates? Great question. In order of operations:

### SCM

If a project is using Git:

```bash
git pull
git submodule update --init --recursive
```

If it is using Subversion, it runs:

```bash
svn up
```

### Dependencies

If Pug detects CocoaPods, it runs:

```bash
pod install
```

If Pug detects Composer, it runs:

```bash
composer update
```


## pug help

Command-specific help is available on the command line:

```bash
$ pug help
usage: pug [--version] <command> [<args>]

Commands are:
   add        Start tracking a new project
   disable    Exclude project from 'all' updates
   enable     Include project in 'all' updates
   help       Display help information about pug
   rm         Stop tracking a project
   show       Show tracked projects
   update     Fetch project updates

See 'pug help <command>' to read about a specific command
```
