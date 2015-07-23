<?php

namespace Drush\LWG;

use Symfony\Component\Yaml\Yaml;

/**
 * Class Notice
 * @package Drush\LWG
 */
class Notice {

  const BASENAME = 'LWG_NOTICE';
  const EXTENSION = 'yml';

  private $data = array();
  private $exists = FALSE;
  private $file = FALSE;

  /**
   * @var Command
   */
  private $command;

  /**
   * Class constructor.
   *
   * @param Command $command
   *   The current instance of the command.
   */
  public function __construct(Command $command) {
    $this->command = $command;
    $project = $command->getProject();
    $filename = $this->getFilename();
    $this->file = $project->getPath() . "/$filename";

    if (!file_exists($this->file)) {
      $prompt = dt("An existing !filename file was not found in @project.\nWould you like to create one?", array(
        '!filename' => $filename,
        '@project' => $project->getInfo('name') ?: 'this project',
      ));
      if (drush_confirm($prompt)) {
        if (!$this->save()) {
          return drush_set_error('DRUSH_LWG_CREATE_FILE', dt("Unable to create the specified project file:\n!file", array(
            '!file' => $this->file,
          )));
        }
      }
      else {
        $this->file = FALSE;
        return $this;
      }
    }
    if (!is_writable($this->file)) {
      return drush_set_error('DRUSH_LWG_FILE_NOT_WRITABLE', dt("The specified project file is not writable:\n!file", array(
        '!file' => $this->file,
      )));
    }
    if (file_exists($this->file)) {
      $this->data = Yaml::parse($this->file);
      // Parser can return NULL or FALSE;
      if (!$this->data) {
        $this->data = array();
      }
      $this->exists = TRUE;
    }
  }

  /**
   * @todo document
   */
  public function getFilename($include_path = FALSE) {
    $filename = self::BASENAME . '.' . self::EXTENSION;
    if ($include_path) {
      $project = $this->command->getProject();
      return $project->getPath() . '/' . $filename;
    }
    return $filename;
  }

  /**
   * @todo document
   */
  public function exists() {
    return $this->exists;
  }

  /**
   * @todo document
   */
  public function save() {
    $content = $this->command->getBanner();
    if (!empty($this->data)) {
      $content .= Yaml::dump($this->data, 4, 2, FALSE, TRUE);
    }
    return @file_put_contents($this->file, $content) !== FALSE;
  }

}
