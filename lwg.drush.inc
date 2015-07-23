<?php
/**
 * @file
 * The "lwg" Drush command.
 */
use Drush\LWG\Command;
drush_autoload(__FILE__);
drush_set_context('DRUSH_LWG', new Command(__DIR__));

/**
 * Implements hook_drush_command().
 */
function lwg_drush_command() {
  return call_user_func_array(array(drush_get_context('DRUSH_LWG'), 'info'), func_get_args());
}

/**
 * Implements drush_COMMAND_init().
 */
function drush_lwg_init() {
  return call_user_func_array(array(drush_get_context('DRUSH_LWG'), 'init'), func_get_args());
}

/**
 * Implements drush_COMMAND_validate().
 */
function drush_lwg_validate() {
  return call_user_func_array(array(drush_get_context('DRUSH_LWG'), 'validate'), func_get_args());
}

/**
 * Implements drush_COMMAND().
 */
function drush_lwg() {
  return call_user_func_array(array(drush_get_context('DRUSH_LWG'), 'execute'), func_get_args());
}
