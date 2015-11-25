<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct($parameters) {
    // Set the default timezone to NY
    date_default_timezone_set('America/New_York');
  }

  /**
   * @Then component :name is :status
   */
  public function checkComponentStatus($name, $status){
    $return_var = 0;
    $output = array();
    exec("drush pm-info --fields=status --format=table $name", $output, $return_var);
    $real_status = trim($output[1]);
    if($return_var){
      throw new \Exception("Error from running pm-list.");
    }
    else{
      if($real_status !== $status){
        throw new \Exception("Status '$status' expected, but $name is currently '$real_status'");
      }
    }
  }

  /**
   * @When all features are rebuilt
   * @When feature :name is rebuilt
   */
  public function featuresAreRebuilt($name = false)
  {
    $return_var = 0;
    $output = array();
    if ($name == false) {
      exec("drush fua -y --quiet", $output, $return_var);
    }
    else {
      exec("drush fu $name -y --quiet", $output, $return_var);
    }
    if ($return_var) {
      throw new \Exception("Error rebuilding features.");
    }
  }

  /**
   * @When all features are reverted
   * @When feature :name is reverted
   */
  public function featuresAreReverted($name = false){
    if($name == false){
      $return = features_revert();
    }
    else{
      $return = features_revert(array($name));
    }
    if($return){
      throw new \Exception("Error reverting features.");
    }
  }

  /**
   * @Given I export all features_master components to :name feature
   */
  public function exportAllComponents($name){
    $return_var = 0;
    $output = array();
    exec("drush fe $name features_master:modules features_master:permissions features_master:themes --ignore-conflicts --quiet -y", $output, $return_var);
    if($return_var){
      throw new \Exception("Error trying to export to feature.");
    }
  }

  /**
   * @Then the :src feature exports should match :dest feature
   */
  public function filesShouldMatch($src, $dest){

    // Get module's features.features_master.inc default call

    foo();

    $src_call = $src.'_features_master_defaults';
    $dest_call = $dest.'_features_master_defaults';

    $src_exports = $src_call();
    $dest_exports = $dest_call();


    $return_var = array_diff($src_exports, $dest_exports);

    if(!empty($return_var)){
      $string = implode($return_var, ', ');
      throw new \Exception("The exports do not match, for the following: $string");
    }
  }

  /**
   * @Given I enable :name module
   */
  public function enableModule($name){
    try{
      module_enable(array($name));
    }catch(Exception $e){
      sprintf("Error trying to enable $name module");
    }
  }

  /**
   * @Given I disable :name module
   */
  public function disableModule($name){
    try{
      module_disable(array($name));
    }catch(Exception $e){
      sprintf("Error trying to disable $name module");
    }
  }

  /**
   * @Given I enable :name theme
   */
  public function enableTheme($name){
    try{
      theme_enable(array($name));
    }catch(Exception $e) {
      sprintf("Error trying to enable $name theme");
    }
  }

  /**
   * @Given I disable :name theme
   */
  public function disableTheme($name){
    try{
      theme_disable(array($name));
    }catch(Exception $e){
      sprintf("Error trying to disable $name theme");
    }
  }

  /**
   * @Given I remove the :perm permission from the :role role
   */
  public function removePermissionFromRole($perm, $role){
    $role = user_role_load_by_name($role);
    user_role_revoke_permissions($role->rid, array($perm));
  }

  /**
   * @Given I add the :perm permission to the :role role
   */
  public function addPermissionToRole($perm, $role){
    $role = user_role_load_by_name($role);
    user_role_grant_permissions($role->rid, array($perm));
  }

  /**
   * @Then the :role has the :perm permission
   */
  public function roleHasPermission($role, $perm){
    $role = user_role_load_by_name($role);
    $output = user_role_permissions(array($role->rid => $role->rid));
    if(!array_key_exists($perm, $output[$role->rid])){
      throw new \Exception("Role $role->name does not have the $perm permission");
    }
  }

  /**
   * @Then the :role does not have the :perm permission
   */
  public function roleDoesNotHavePermission($role, $perm){
    $role = user_role_load_by_name($role);
    $output = user_role_permissions(array($role->rid => $role->rid));
    if(array_key_exists($perm, $output[$role->rid])){
      throw new \Exception("Role $role->name does have the $perm permission");
    }
  }

}