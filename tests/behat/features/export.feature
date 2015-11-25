@api
Feature:
  If I have a module that has a features_master component
  Then all the items of that component are exported to code

  @wip
  Scenario: A new feature module with
    Given all features are reverted
    When I export all features_master components to "new_feature" feature
    Given I enable "new_feature"
    Then the "new_feature" feature exports should match "features_master_test" feature
