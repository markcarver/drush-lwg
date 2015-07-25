<?php

namespace Drush\Command\LWG;

use Drush\Command\LWG;

/**
 * Class ActionHelp
 * @package Drush\Command\LWG
 */
class ActionHelp extends LWG {

  /**
   * {@inheritdoc}
   */
  public function execute($action = NULL) {
    $args = func_get_args();
    $action = _lwg_validate_action($action, FALSE);
    if (!$action) {
      return drush_do_command_redispatch('help', array('lwg'));
    }

    $class = _lwg_command_action_class($action);
    return call_user_func(array($class, 'help'), $args) ?: '';
  }

}
