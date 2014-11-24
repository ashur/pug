# Pug

<div style="clear: left;"></div>

Quickly update local projects and their dependencies with a single command. Currently supports Subversion and Git, and [CocoaPods](http://cocoapods.org/) and [Composer](https://getcomposer.org).


## Installation

```bash
$ git clone --recursive https://github.com/ashur/pug.git
```

### Requirements

Pug requires PHP 5.4 or greater

## Usage

Pug can fetch updates for projects that live at arbitrary paths. Some thrilling examples:

```bash
$ pug update .
$ pug update ../plank
$ pug update ~/Developer/plank
```

Admittedly, the convenience here is small for simple projects, but Pug gives you a tiny leg up as things get more complicated. A single command is all you need to update Git repositories _and_ their submodules _and_ dependencies managed by tools like CocoaPods.

### Tracking

If you juggle multiple projects that need to stay up-to-date, Pug really starts to shine with tracking:

```bash
$ pug track ~/Developer/plank
 plank           -                   /Users/ashur/Developer/plank
 pug             Mon Nov 24 08:04    /Users/ashur/Developer/pug
```

Need to get up to speed across multiple projects every morning? It's a breeze, Louise:

```bash
$ pug update all
```

Want to stop tracking a project? We've got that too:

```bash
pug untrack plank
```


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
