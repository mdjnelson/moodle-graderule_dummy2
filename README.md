# Dummy Grade Rule plugin

The dummy grade rule plugin implements most of the grade rule API for testing purposes.

## Supported Moodle Versions

This plugin currently supports Moodle:

* 3.7
* 3.8
* 3.9dev

## Moodle Plugin Installation
The following sections outline how to install the Moodle Plugin.

### Command Line Installation
To install the plugin in Moodle via the command line (assumes a Linux based system):

1. Get the code from Gitlab
2. Copy or clone the code into `<moodledir>/grade/rule/dummy`
3. Run the upgrade: `sudo -u www-data php admin/cli/upgrade.php` **Note:** the user may be different to www-data on your system

## User Interface Installation
To install the plugin in Moodle via the Moodle User Interface:

1. Log into your Moodle as an Administrator.
2. Navigate to: *Site administration > Plugins > Install Plugins*
3. Install plugin via zip upload.
