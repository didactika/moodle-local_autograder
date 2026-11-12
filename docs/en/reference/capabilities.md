---
title: Capabilities
summary: Every capability of the plugin, who has it by default and what it allows.
order: 41
---

| Capability | Context | Default roles | Allows the user to |
|---|---|---|---|
| `local/autograder:configure` | Activity | Editing teacher, Manager | Enable, disable and configure Autograder in an activity |
| `local/autograder:manage` | System | Manager | Choose which activity types can use Autograder |

Autograder does not add a capability to decide who a grade can be attributed to. It uses the grading capability of the activity itself: `mod/assign:grade`, `mod/forum:grade`, `mod/quiz:grade`, or `moodle/grade:edit` for the other activity types.

The capabilities to see what Autograder decided belong to the [Autograder report](https://github.com/didactika/moodle-report_autograder).
