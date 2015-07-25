<?php

namespace Drush\LWG;

use Drush\Command\CommandBase;

/**
 * Class Asset
 * @package Drush\LWG
 */
class Asset extends \ArrayObject {

  /**
   * @var CommandBase
   */
  protected $command;

  protected $required_keys = array(
    'authors',
    'license',
    'licenses',
    'source',
    'title',
    'type',
    'url',
  );

  protected $name;

  /**
   * Class constructor.
   *
   * @param CommandBase $command
   *   The current instance of the command.
   * @param array $input
   *   The input parameter accepts an array.
   * @param string $name
   *   The name of the asset.
   * @param int $flags
   *   Flags to control the behaviour of the ArrayObject object.
   * @param string $iterator_class
   *   Specify the class that will be used for iteration of the ArrayObject
   *   object. ArrayIterator is the default class used.
   */
  public function __construct(CommandBase $command, $name, array $input = array(), $flags = 0, $iterator_class = "ArrayIterator") {
    $this->command = $command;
    $this->name = $name;
    $input = array_merge(array(
      'type' => '@required',
      'title' => '@required',
      'url' => '@required',
      'authors' => array('@required'),
      'source' => '@required',
      'license' => '@required',
      'licenses' => array('@required'),
      'files' => array(),
      'versions' => array(),
    ), $input);
    $this->exchangeArray($input);
  }

  /**
   * {@inheritdoc}
   */
  public function getArrayCopy($objects = FALSE) {
    // Subclass this method so we can convert the asset into a proper array.
    $array = parent::getArrayCopy();
    // Filter out non-required properties.
    foreach ($array as $key => $value) {
      if (in_array($key, $this->required_keys)) {
        continue;
      }
      if (empty($array[$key])) {
        unset($array[$key]);
      }
    }
    return $array;
  }

  public function getName() {
    return $this->name;
  }

  public function table() {
    $data = array();
    foreach ($this->required_keys as $key) {
      $value = $this[$key];
      if (empty($value) || $value === '@required' || (is_array($value) && $value[0] === '@required')) {
        $value = $this->command->ascii(' - MISSING - ', NULL, 'bold', 'red');
        if (is_array($this[$key])) {
          $value = array($value);
        }
      }
      $data[$key] = $value;
    }
    return drush_format_table(drush_key_value_to_array_table($data));
  }

}
