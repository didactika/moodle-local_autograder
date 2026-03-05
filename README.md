# Local Autograder #

Autograder is a Moodle plugin designed to automatically grade assignments and forums based on the time elapsed and the grade specified by the instructor.

The autograding process takes place after the activity’s submission deadline has passed, provided that autograder is enabled for the activity. Once the activity has ended, the configured autograding time is counted.

This plugin stores the data entered through its configuration form and interacts with an external service that uses RabbitMQ to handle the autograding logic.

By automating the grading process according to predefined rules, this plugin reduces the instructor’s administrative workload while ensuring consistent and timely grading.


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/autograder

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2025 Eduardo Cubias <eduardo.cubias@ct.uneatlantico.es>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
