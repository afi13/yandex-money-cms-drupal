<?php

namespace Drupal\yamoney\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Access check for yamoney routes.
 */
class YamoneyServerIpAccessCheck implements AccessInterface {
  /**
   * Checks access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\user\UserInterface $user
   *   The user whose tracker page is being accessed.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, UserInterface $user) {
    $ip = \Drupal::request()->getClientIp();

    $config = \Drupal::config('yamoney.settings');
    $allowed_ips = $config->get('yamoney_ip');

    $allowed_ips = explode("\n", $allowed_ips);
    foreach ($allowed_ips as $allowed_ip) {
      $allowed_ip = trim($allowed_ip);
      if (empty($allowed_ip)) {
        continue;
      }
      if ($allowed_ip === '0.0.0.0' || $ip === $allowed_ip) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }
}
