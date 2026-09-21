<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_autograder\form;

use local_autograder\local\config\config_repository;

/**
 * The activity settings form, built for real.
 *
 * Checking the pieces of the section in isolation is not enough: this plugin
 * has twice shipped a settings form that threw the moment a teacher opened
 * it, while its unit tests passed. The only way to know the section renders
 * is to build the module's own form the way `course/modedit.php` does and
 * look at what comes out, which is what these do.
 *
 * `$PAGE` can only be set up once per process, so there is one form per test.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\form\autograder_section::add_elements
 */
final class module_form_test extends \advanced_testcase {
    /**
     * Adding a brand new activity shows the section and raises nothing.
     *
     * @param string $modname
     * @dataProvider module_provider
     */
    public function test_the_section_renders_when_adding_an_activity(string $modname): void {
        $this->prepare();

        $course = $this->getDataGenerator()->create_course();
        $html = $this->render_add_form($modname, $course);

        $this->assertStringContainsString('autograder_enabled', $html);
        $this->assert_no_warnings($html);
    }

    /**
     * Editing an existing activity shows the section, tells the teacher what
     * the activity is graded out of, and raises nothing.
     *
     * @param string $modname
     * @dataProvider module_provider
     */
    public function test_the_section_renders_when_editing_an_activity(string $modname): void {
        $this->prepare();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module($modname, $this->module_options($modname, $course));
        $cm = get_coursemodule_from_instance($modname, $module->id, $course->id, false, MUST_EXIST);

        $html = $this->render_edit_form($cm, $course);

        $this->assertStringContainsString('autograder_enabled', $html);
        $this->assertStringContainsString('autograder_grade_point', $html);
        $this->assert_no_warnings($html);
    }

    /**
     * The module types autograder ships enabled for.
     *
     * @return array<string, array{string}>
     */
    public static function module_provider(): array {
        return [
            'assignment' => ['assign'],
            'quiz' => ['quiz'],
            'forum' => ['forum'],
        ];
    }

    /**
     * An activity graded by a rubric renders the section too — this is the
     * case that threw, because the rubric branch reached for a variable that
     * was not in scope.
     */
    public function test_the_section_renders_for_a_rubric_graded_activity(): void {
        $this->prepare();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance((int) $cm->id);

        $generator->get_plugin_generator('core_grading')
            ->create_instance($context, 'mod_assign', 'submissions', 'rubric');
        $generator->get_plugin_generator('gradingform_rubric')
            ->create_instance($context, 'mod_assign', 'submissions', 'Test rubric', 'For autograder', [
                'Argument' => ['Absent' => 0, 'Present' => 5],
            ]);

        $html = $this->render_edit_form($cm, $course);

        $this->assertStringContainsString('autograder_enabled', $html);
        $this->assertStringContainsString('autograder_advanced_notice', $html);
        $this->assert_no_warnings($html);
    }

    /**
     * An activity of a type the site has switched autograder off for carries
     * no section at all.
     */
    public function test_a_disabled_module_type_gets_no_section(): void {
        $this->prepare();

        set_config('enabled_modules', 'quiz', 'local_autograder');

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $html = $this->render_edit_form($cm, $course);

        $this->assertStringNotContainsString('autograder_enabled', $html);
    }

    /**
     * A saved configuration comes back on the form.
     */
    public function test_a_saved_configuration_is_shown_again(): void {
        $this->prepare();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            true,
            'point',
            65.0,
            null,
            2 * DAYSECS,
            2
        );

        $html = $this->render_edit_form($cm, $course);

        $this->assertStringContainsString('65', $html);
        $this->assert_no_warnings($html);
    }

    /**
     * Loads what building a module form needs, as an admin.
     */
    private function prepare(): void {
        global $CFG, $PAGE;

        require_once($CFG->dirroot . '/course/moodleform_mod.php');
        require_once($CFG->libdir . '/gradelib.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        // Building a form initialises the theme, and a page whose theme is
        // already up refuses to be given another course. Each of these tests
        // builds a form, so each starts from a page that has not.
        $PAGE->reset_theme_and_output();
        $PAGE->set_url('/course/modedit.php');
    }

    /**
     * What each module type needs to be created graded.
     *
     * @param string $modname
     * @param \stdClass $course
     * @return array
     */
    private function module_options(string $modname, \stdClass $course): array {
        $options = ['course' => $course->id];

        if ($modname === 'forum') {
            // A forum's activity grade is its own setting, separate from the
            // ratings on individual posts.
            $options['grade_forum'] = 100;
        } else {
            $options['grade'] = 100;
        }

        return $options;
    }

    /**
     * Builds and renders the "add activity" form, as course/modedit.php does.
     *
     * @param string $modname
     * @param \stdClass $course
     * @return string
     */
    private function render_add_form(string $modname, \stdClass $course): string {
        global $CFG, $DB, $PAGE, $COURSE;

        $COURSE = $course;
        $PAGE->set_course($course);

        $module = $DB->get_record('modules', ['name' => $modname], '*', MUST_EXIST);
        $data = (object) [
            'section' => 0,
            'visible' => 1,
            'course' => $course->id,
            'module' => $module->id,
            'modulename' => $modname,
            'groupmode' => 0,
            'groupingid' => 0,
            'id' => '',
            'instance' => '',
            'coursemodule' => '',
            'add' => $modname,
            'return' => 0,
            'sr' => 0,
        ];

        require_once($CFG->dirroot . "/mod/{$modname}/mod_form.php");
        $class = "mod_{$modname}_mod_form";

        return $this->render(new $class($data, 0, null, $course));
    }

    /**
     * Builds and renders the "edit activity" form, as course/modedit.php does.
     *
     * @param \stdClass $cm A course module as get_coursemodule_from_* returns it.
     * @param \stdClass $course
     * @return string
     */
    private function render_edit_form(\stdClass $cm, \stdClass $course): string {
        global $CFG, $DB, $PAGE, $COURSE;

        $COURSE = $course;
        $PAGE->set_course($course);
        $PAGE->set_context(\context_module::instance((int) $cm->id));

        $data = $DB->get_record($cm->modname, ['id' => $cm->instance], '*', MUST_EXIST);
        $data->coursemodule = $cm->id;
        $data->section = 0;
        $data->visible = $cm->visible;
        $data->course = $course->id;
        $data->module = $cm->module;
        $data->modulename = $cm->modname;
        $data->instance = $cm->instance;
        $data->groupmode = $cm->groupmode;
        $data->groupingid = $cm->groupingid;
        $data->update = $cm->id;
        $data->return = 0;
        $data->sr = 0;

        require_once($CFG->dirroot . "/mod/{$cm->modname}/mod_form.php");
        $class = "mod_{$cm->modname}_mod_form";

        return $this->render(new $class($data, 0, $cm, $course));
    }

    /**
     * The HTML a built form produces.
     *
     * @param \moodleform_mod $form
     * @return string
     */
    private function render(\moodleform_mod $form): string {
        ob_start();
        $form->display();

        return ob_get_clean();
    }

    /**
     * Fails if PHP complained anywhere in the rendered form.
     *
     * A settings form that throws is not a subtle bug — it stops a teacher
     * creating or editing any activity at all — so anything PHP had to say
     * while building it counts.
     *
     * @param string $html
     */
    private function assert_no_warnings(string $html): void {
        $matches = [];
        preg_match_all(
            '/.{0,80}(Warning|Notice|Undefined variable|Deprecated|Fatal).{0,80}/',
            strip_tags($html),
            $matches
        );

        $this->assertEmpty(
            $matches[0] ?? [],
            'PHP complained while the form was built: ' . implode(' | ', array_unique($matches[0] ?? []))
        );
    }
}
