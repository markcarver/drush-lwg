<?php
/**
 * @file
 * The "lwg" Drush command.
 */

use Drush\Command;

define('DRUSH_LWG_COMMAND_CLASS', 'Drush\\Command\\LWG');
define('DRUSH_LWG_DIR', realpath(__DIR__ . '/..'));
define('DRUSH_LWG_URL', 'https://github.com/markcarver/drush-lwg');

/**
 * Verify that the installed Drush version is supported.
 *
 * @return bool
 *   TRUE if supported, FALSE otherwise.
 */
function _lwg_check_drush_version() {
  // Only execute command if the installed version of Drush is supported.
  $supported_versions = array(7);
  if (!defined('DRUSH_MAJOR_VERSION') || !in_array(DRUSH_MAJOR_VERSION, $supported_versions)) {
    return drush_set_error('DRUSH_VERSION_NOT_SUPPORTED', dt("The \"lwg\" Drush command only supports the following major Drush version(s): !SUPPORTED_VERSIONS\nThe currently installed Drush version is: !DRUSH_VERSION\nFor more details, please visit: !url\n", array(
      '!SUPPORTED_VERSIONS' => implode(', ', $supported_versions),
      '!DRUSH_VERSION' => DRUSH_VERSION,
      '!url' => DRUSH_LWG_URL,
    )));
  }
  return TRUE;
}

/**
 * Retrieve an action class name.
 *
 * @param string $action
 *   Optional. The action to perform.
 *
 * @return string
 *   The class name of the action.
 */
function _lwg_command_action_class($action = NULL) {
  return DRUSH_LWG_COMMAND_CLASS . '\\Action' . ucfirst($action);
}

/**
 * Retrieve an action to perform.
 *
 * @param string $action
 *   Optional. The action to validate.
 * @param string|FALSE $default
 *   The default action to use if $action is not provided.
 *
 * @return string | FALSE
 *   A action to perform or FALSE otherwise.
 */
function _lwg_validate_action($action = NULL, $default = 'list') {
  $action = $action ?: $default;
  if ($action && !in_array(strtolower($action), array('add', 'approve', 'edit', 'help', 'list', 'remove', 'validate'))) {
    drush_set_context('DRUSH_LWG_INVALID_ACTION', dt('Invalid action: "!action".  See `drush lwg help` for information on usage.', array(
      '!action' => $action,
    )));
    $action = FALSE;
  }
  return $action;
}

/**
 * Implements hook_drush_command().
 */
function lwg_drush_command() {
  $info['lwg'] = array(
    'aliases' => array('notice'),
    'allow-additional-options' => TRUE,
    'arguments' => array(
      'action' => 'An action to perform: add, approve, edit, help, list, remove or validate.',
    ),
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    'description' => 'Manage external assets in a project for use with Drupal Licensing Working Group.',
    'scope' => 'system',
    'options' => array(
      'path' => 'The working directory of the project to operate from, defaults to the current working directory if not specified.',
    ),
  );
  return $info;
}

/**
 * Implements drush_COMMAND_help().
 */
function drush_lwg_help() {
  return dt("\nSee `drush lwg help [action]` for additional information on usage.");
}

/**
 * Implements drush_COMMAND_init().
 *
 * @todo remove if/when Drush supports OOP commands.
 */
function drush_lwg_init() {
  if (!_lwg_check_drush_version()) {
    return FALSE;
  }

  $args = func_get_args();
  // Remove the first argument (always the "action" argument).
  if ($action = _lwg_validate_action(array_shift($args))) {
    drush_autoload(__FILE__);
    $class = _lwg_command_action_class($action);
    if (!class_exists($class)) {
      drush_log(dt('Missing class: !class', array('!class' => $class)));
      $class = DRUSH_LWG_COMMAND_CLASS;
    }
    if ($command = drush_get_class($class)) {
      drush_set_context('DRUSH_LWG_COMMAND', $command);
      return call_user_func_array(array($command, 'init'), $args);
    }
  }
}

/**
 * Implements drush_COMMAND_validate().
 *
 * @todo remove if/when Drush supports OOP commands.
 */
function drush_lwg_validate() {
  $args = func_get_args();
  // Remove the first argument (always the "action" argument).
  array_shift($args);
  if ($msg = drush_get_context('DRUSH_LWG_INVALID_ACTION')) {
    return drush_set_error('DRUSH_LWG_INVALID_ACTION', $msg);
  }
  if ($command = drush_get_context('DRUSH_LWG_COMMAND')) {
    return call_user_func_array(array($command, 'validate'), $args);
  }
  return drush_set_error('DRUSH_LWG_INVALID_CLASS_COMMAND');
}

/**
 * Implements drush_COMMAND().
 *
 * @todo remove if/when Drush supports OOP commands.
 */
function drush_lwg() {
  $args = func_get_args();
  // Remove the first argument (always the "action" argument).
  array_shift($args);
  if ($command = drush_get_context('DRUSH_LWG_COMMAND')) {
    return call_user_func_array(array($command, 'execute'), $args);
  }
  return drush_set_error('DRUSH_LWG_INVALID_CLASS_COMMAND');
}
