@api
Feature:
  If I have a module that has exported code from features_master
  Then reverts will reset to the default for all components of a feature module

  # Pre-condition: Make sure that the features has component X exported and component Y not exported
  #       - Modules
  #           - enabled         = color, overlay
  #           - not installed   = blog, book
  #       - Themes
  #           - enabled         = bartik (default, don't use), seven
  #           - disabled        = garland, stark
  #
  # Post: Ensure that the exported and non-exported components are at the correct state.

  Scenario: All modules are re-enabled or disabled upon revert
    Given I disable "color" module
    And I enable "blog" module
    Then component "color" is disabled
    And component "blog" is enabled
    When all features are reverted
    Then component "color" is enabled
    And component "blog" is disabled

  Scenario: All themes are re-enabled or disabled upon revert
    Given I disable "seven" theme
    And I enable "garland" theme
    Then component "seven" is disabled
    And component "garland" is enabled
    When all features are reverted
    Then component "seven" is enabled
    And component "garland" is disabled

  @debugEach
  Scenario: All permissions are reset to default
    Given I add the "access administration pages" permission to the "authenticated user" role
    And I remove the "administer menu" permission from the "administrator" role
    When all features are reverted
    Then the "authenticated user" does not have the "access administration pages" permission
    And the "administrator" has the "administer menu" permission