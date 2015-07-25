<?php

namespace Drush\LWG;

use Drush\Command\LWG;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Notice
 * @package Drush\LWG
 */
class Notice extends \ArrayObject {

  const BASENAME = 'LWG_NOTICE';
  const EXTENSION = 'yml';

  /**
   * @var Cache
   */
  protected $cache;
  protected $data = array();
  protected $exists = FALSE;
  protected $file = FALSE;

  /**
   * @var LWG
   */
  protected $command;

  /**
   * Class constructor.
   *
   * @param LWG $command
   *   The current instance of the command.
   * @param array|object $input
   *   The input parameter accepts an array.
   * @param int $flags
   *   Flags to control the behaviour of the ArrayObject object.
   * @param string $iterator_class
   *   Specify the class that will be used for iteration of the ArrayObject
   *   object. ArrayIterator is the default class used.
   */
  public function __construct(LWG $command, array $input = array(), $flags = 0, $iterator_class = "ArrayIterator") {
    $this->command = $command;
    $this->exchangeArray($input);
    $project = $command->getProject();
    $filename = $this->getFilename();
    $this->file = $project->getPath() . "/$filename";

    $this->cache = new Cache($project->getName() . ':notice');
    $cache = $this->cache->get();

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
      $array = array();
      // Convert each entry into an Asset object.
      foreach (Yaml::parse($this->file) ?: array() as $name => $data) {
        if (is_array($data)) {
          $array[$name] = new Asset($command, $name, $data);
        }
      }
      if (!$this->cache->exists()) {
        $this->cache->set($array);
      }
      $this->exchangeArray($array);
      $this->exists = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getArrayCopy($objects = FALSE) {
    // Subclass this method so we can convert assets into proper arrays.
    $array = array();
    foreach ($this as $key => $asset) {
      $array[$key] = ($objects ? $asset : ($asset instanceof Asset ? $asset->getArrayCopy() : $asset));
    }
    return $array;
  }

  /**
   * @todo document
   */
  public function getAssets($yaml = FALSE, $objects = FALSE) {
    $array = $this->getArrayCopy($yaml ? FALSE : $objects);
    return $yaml ? Yaml::dump($array, 4, 2) : $array;
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
    $content = $this->command->render('banner.yml');
    if (!empty($this->data)) {
      $content .= $this->getAssets(TRUE);
    }
    return @file_put_contents($this->file, $content) !== FALSE;
  }

}
