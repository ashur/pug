![pug](http://pug.sh.s3.amazonaws.com/pug.png)

Quickly update local projects and their dependencies with a single command. Pug currently supports Subversion and Git, and [CocoaPods](https://cocoapods.org/) and [Composer](https://getcomposer.org).


## Setup

> See [Installation](INSTALL.md) for details on getting started with Pug

### Configuration

If a timezone isn't set in php.ini, Pug defaults to `UTC`. To override either, open [config.php](https://github.com/ashur/pug/blob/master/config.php) and specify a [supported timezone](http://php.net/manual/en/timezones.php).

## Usage

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

Admittedly, the convenience here is small for simple projects, but Pug gives you a tiny leg up as things get more complicated. A single command is all you need to update Git repositories _and_ their submodules _and_ dependencies managed by tools like Composer.


## Tracking

If you juggle multiple projects that need to stay up-to-date, Pug really starts to shine with tracking:

```bash
$ pug track ~/Developer/plank
total 2
Feb 11  2014 plank -> ~/Developer/plank
Apr 27 21:37 pug -> ~/Developer/pug
```

Updating all your projects at once is a breeze:

```bash
$ pug update all
```

Finished a project and want to tidy up a bit?

```bash
$ pug untrack plank
```


## Under the hood

Okay so but what is Pug doing when it updates? In order of operations:

### SCM

If a project is using Git, `pug update` runs two commands:

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
   help       Display help information about pug
   list       List all tracked projects
   track      Track a project at <path>
   untrack    Stop tracking the project <name>.
   update     Fetch project updates

See 'pug help <command>' to read about a specific command
```

### Shortcuts

Pug has short command aliases for listing tracked apps:

```
$ pug list|ls
```

and for updating:

```
$ pug update|up
```
