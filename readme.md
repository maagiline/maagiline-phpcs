# Maagiline PHP coding standard
Use this coding standard in your local development environment, as well as in CI pipelines.

## Set up in PhpStorm
### Set up code sniffing
Code sniffing will instruct your editor to display style issues in your code.

1. In PhpStorm settings, navigate to Languages & Frameworks > PHP > Quality Tools
1. For PHP Codesniffer path, enter: `vendor/bin/phpcs`
1. In PhpStorm settings, navigate to Editor > Inspections > Quality tools > PHP_Codesniffer validation
1. Check this option
1. Set Severity to "Error"
1. In Coding standard, select custom and select `./vendor/maagiline/maagiline-phpcs/ruleset.xml`

![Codesniffer settings in PhpStorm](./docs/codesniffer-settings.png "Codesniffer settings in PhpStorm")

### Set up code beautifier
Some errors can be fixed automatically by `phpcbf`. Wanna go full-on badass? Set up PhpStorm to fix your style issues whenever a file is saved.

#### Set up phpcbf as external tool
First, add phpcbf under "External tools".
1. In PhpStorm settings, navigate to Tools > External Tools
1. Add a new tool (click plus sign in bottom of window)
1. Enter following info
```
Name:               phpcbf
Description:        Fixed phpcs issues automatically
Program:            $ProjectFileDir$/vendor/squizlabs/php_codesniffer/bin/phpcbf
Arguments:          --standard=$ProjectFileDir$/vendor/maagiline/maagiline-phpcs/ruleset.xml $FileDir$/$FileName$
Working directory:  $ProjectFileDir$

Under 'Advanced options', uncheck "Open console for tool output".
```
![phpcbf external tool in PhpStorm](./docs/phpcbf-external-tool.png "phpcbf external tool in PhpStorm")

#### Trigger phpcbf from keyboard shortcut
After clicking "Apply" in the previous menu, do this:
1. In PhpStorm settings, navigate to Keymap
1. Search for "phpcbf"
1. Assign a shortcut. I like to use `cmd+shift+B`

Now, whenever in a file that has errors, tap this to run phpcbf. All errors that can be fixed automatically will be fixed.

#### Trigger phpcbf on file save
(NOT TESTED). If you wish to run phpcbf automatically whenever a file is saved, you can create a File Watcher in PhpStorm settings. The settings should be similar to those outlined in the previous section. Be brave!

## Command line tools
To list errors:
```
./vendor/bin/phpcs --standard=./vendor/maagiline/maagiline-phpcs/ruleset.xml
```

Some errors can be fixed automatically. To fix automatically:
```
./vendor/bin/phpcbf --standard=./vendor/maagiline/maagiline-phpcs/ruleset.xml
```