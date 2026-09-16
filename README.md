# Local Autograder #

Grades a student in an assign, forum or quiz activity automatically, some time
after they are due to be graded, with the mark the teacher configured — running
entirely inside Moodle.

Everything the v2 plugin left to an external service (`autograder-service`) now
happens here: detecting who is pending, working out the due date from the
activity's own close date and any user/group exception, choosing which teacher
grades on the site's behalf, and calling Moodle's own grading APIs — including
simple direct grading, scales, and advanced grading (rubrics and marking
guides), simulated exactly as a teacher would grade by hand.

Who grades on the site's behalf is a capability, not a setting:
`local/autograder:gradeonbehalf`, granted to editing teachers by default and
adjusted with an ordinary role override. A site can additionally offer every
user a preference asking never to be chosen — off by default, because taking
teachers out of the rota changes who is graded and when.

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

2026 Didactika.org — Hector Arrechea <hectorlazaroarrechea@gmail.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
