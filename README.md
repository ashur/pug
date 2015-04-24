<h3 align="center">
  <img src="assets/pug.png" alt="pug" />
</h3>

Quickly update local projects and their dependencies with a single command. Currently supports Subversion and Git, and [CocoaPods](http://cocoapods.org/) and [Composer](https://getcomposer.org).


## Installation

```bash
$ git clone --recursive https://github.com/ashur/pug.git
```

You'll probably want to add the resulting folder to your environment `$PATH`. For example, if you're running **bash**, you'd add the following to `~/.bashrc`:

```bash
export PATH=$PATH:/path/to/pug/folder
```

This lets you run `pug` commands from anywhere on the command line, not just from inside the Pug repository folder.

### Requirements

Pug requires PHP 5.4 or greater

## Usage

Pug can fetch updates for projects that live at arbitrary paths. Some thrilling examples:

```bash
$ pug update ../plank
$ pug update ~/Developer/plank
```

Update the project at your current working directory:

```bash
$ pug update
$ pug update ./
```

Admittedly, the convenience here is small for simple projects, but Pug gives you a tiny leg up as things get more complicated. A single command is all you need to update Git repositories _and_ their submodules _and_ dependencies managed by tools like CocoaPods.

### Tracking

If you juggle multiple projects that need to stay up-to-date, Pug really starts to shine with tracking:

```bash
$ pug track ~/Developer/plank
total 2
Feb 18 08:46 plank -> /Users/ashur/Developer/plank
Feb 18 08:35 pug -> /Users/ashur/Developer/pug
```

Need to get up to speed across multiple projects every morning? It's a breeze, Louise:

```bash
$ pug update all
```

Finished a project and want to tidy up a bit? Untrack, Jack:

```bash
pug untrack plank
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

## Under the hood

Okay so but what is Pug _actually_ doing when it updates? In order of operations:

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

### Composer

## pug help

Command-specific help is available on the command line:

```bash
$ pug help
usage: pug <command> [<args>]

Commands are:
   help       Display help information about pug
   list       List all tracked projects
   track      Track a project at <path>
   untrack    Stop tracking the project <name>.
   update     Fetch project updates

See 'pug help <command>' to read about a specific command
```
