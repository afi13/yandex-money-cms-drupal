<?php

namespace Drupal\yamoney;

/**
 * Contains all events dispatched by Yamoney module.
 */
final class YamoneyEvents {

  /**
   * The event triggered after transaction complete.
   *
   * This event allows modules to react to transaction completion.
   *
   * @Event
   *
   * @see \Drupal\yamoney\YamoneyEvent
   *
   * @var string
   */
  const SUCCESS = 'yamoney.success';

  const FAIL = 'yamoney.fail';

}
