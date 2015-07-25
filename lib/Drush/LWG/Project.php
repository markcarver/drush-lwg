<?php

namespace Drush\LWG;

use Drush\Command\CommandBase;

/**
 * Class Project
 * @package Drush\LWG
 */
class Project {

  protected $info = FALSE;
  protected $name = FALSE;
  protected $path = FALSE;
  protected $valid = FALSE;

  /**
   * @var CommandBase
   */
  protected $command;

  /**
   * Class constructor.
   *
   * @param CommandBase $command
   *   The current instance of the command.
   */
  public function __construct(CommandBase $command) {
    $this->command = $command;

    // Determine if the "path" option has been set.
    $this->path = drush_get_option('path');
    if ($this->path && !file_exists($this->path)) {
      return drush_set_error('DRUSH_LWG_INVALID_PATH', dt("The specified project path does not exist:\n!path", array(
        '!path' => $this->path,
      )));
    }
    // Otherwise use the current working directory as the "path".
    else if (!$this->path) {
      $this->path = drush_cwd();
    }

    // Ensure the path is writable.
    if (!is_writable($this->path)) {
      return drush_set_error('DRUSH_LWG_PATH_NOT_WRITABLE', dt("The specified project path is not writable:\n!path", array(
        '!path' => $this->path,
      )));
    }

    foreach (drush_scan_directory($this->path, '/\.info(\.yml)?/') as $file) {
      if ($this->info = drush_drupal_parse_info_file($file->filename)) {
        $this->name = $file->name;
        break;
      }
    }

    if (!$this->getInfo('name')) {
      return drush_set_error('DRUSH_LWG_NOT_PROJECT', dt('Project info not found. Please navigate to a valid project directory or specify one with the --path option.'));
    }

    // Indicate that this is a valid project.
    $this->valid = TRUE;
  }

  /**
   * @todo document
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * @todo document
   */
  public function getInfo($property = NULL) {
    $info = is_array($this->info) ? $this->info : array();
    if (isset($property)) {
      return !empty($info) && isset($info[$property]) ? $info[$property] : FALSE;
    }
    return $info;
  }

  public function getName() {
    return $this->name;
  }

  public function getTitle() {
    return $this->getInfo('name') ?: $this->name;
  }

  /**
   * @todo document
   */
  public function isValid() {
    return $this->valid;
  }

  public function projectAdd() {

  }

}
