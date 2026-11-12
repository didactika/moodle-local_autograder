---
id: MAP
status: draft
origin: code
sourceOfTruth: code
---

# Code map

| Model element | Kind | Code location | Verified |
| --- | --- | --- | --- |
| Configuration | table | `db/install.xml` (`local_autograder_config`) | yes |
| Decision | table | `db/install.xml` (`local_autograder_decision`) | yes |
| Grade log | table | `db/install.xml` (`local_autograder_grade_log`) | yes |
| Saving the configuration | method | `classes/local/config/config_repository.php:101` | yes |
| Section of the activity form | class | `classes/form/autograder_section.php` | yes |
| Enabled activity types | method | `classes/local/config/eligibility.php:108` | yes |
| Planning a decision | method | `classes/local/decision/decision_planner.php:62` | yes |
| Due date | method | `classes/local/decision/due_date_calculator.php` | yes |
| Decision lifecycle | methods | `classes/local/decision/decision_repository.php:207`, `:413`, `:440` | yes |
| Grading task | method | `classes/task/grade_student.php:56` | yes |
| Choice of the teacher | method | `classes/local/grading/grader_picker.php:56` | yes |
| Writing the grade | method | `classes/local/module/module_adapter.php:173` | yes |
| Event observers | class | `classes/observer.php`, `db/events.php` | yes |
| Capabilities of the plugin | file | `db/access.php` | yes |
| Scheduled tasks | file | `db/tasks.php` | yes |
| Events of the plugin | classes | `classes/event/` | yes |
| Privacy | class | `classes/privacy/provider.php` | yes |
