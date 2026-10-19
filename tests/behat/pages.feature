@local @local_autograder @javascript
Feature: The pages of Autograder
  In order to set Autograder up
  As an administrator
  I need the activity types and the fallback grader to be settings that work as I use them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | marina   | Marina    | Castro   | marina@example.com   |
    And the following "courses" exist:
      | fullname     | shortname |
      | Statistics 1 | STAT1     |
    And the following "course enrolments" exist:
      | user   | course | role           |
      | marina | STAT1  | editingteacher |
    And I log in as "admin"

  Scenario: An activity type can be switched off and on again
    When I open the autograder page "modules.php"
    And I wait for the autograder page
    Then autograder should be allowed for the "assign" activity type
    And I click on "Disable Autograder for Assignment" "button"
    And I wait "1" seconds
    And autograder should not be allowed for the "assign" activity type
    And I click on "Enable Autograder for Assignment" "button"
    And I wait "1" seconds
    And autograder should be allowed for the "assign" activity type

  Scenario: The fallback grader is searched as the name is typed and saved with the settings
    When I navigate to "Plugins > Local plugins > Autograder" in site administration
    And I click on "Teachers" "link" in the ".nav-tabs" "css_element"
    And I wait for the autograder page
    And I click on "None" "button" in the ".lag-picker" "css_element"
    And I set the field with xpath "//input[contains(@class, 'lag-picker-field')]" to "Marina"
    And I click on "Marina Castro" "button" in the ".lag-picker-panel" "css_element"
    And I press "Save changes"
    Then I should see "Changes saved"
    And I click on "Teachers" "link" in the ".nav-tabs" "css_element"
    And I should see "Marina Castro" in the ".lag-picker" "css_element"
