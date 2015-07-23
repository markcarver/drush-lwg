<?php
/**
 * @file
 * Definition of Drush\Command\CommandInterface.
 */

namespace Drush\Command;

/**
 * Interface CommandInterface
 * @package Drush\Command
 * @todo Remove if or when this ever becomes a native interface in Drush.
 */
interface CommandInterface {

  /**
   * Registers information about the command.
   *
   * @return array
   */
  public function info();

  /**
   * Execute the command, after validation.
   *
   * @param mixed ...
   *   Any passed arguments of the command.
   *
   * @return void | BOOL
   */
  public function execute();

  /**
   * Initiates the command, prior to validation.
   *
   * @param mixed ...
   *   Any passed arguments of the command.
   *
   * @return void | BOOL
   */
  public function init();

  /**
   * Validates the command before proceeding to execute it.
   *
   * @param mixed ...
   *   Any passed arguments of the command.
   *
   * @return void | BOOL
   */
  public function validate();
}
