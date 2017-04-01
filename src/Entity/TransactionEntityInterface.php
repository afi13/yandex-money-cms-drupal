<?php

namespace Drupal\yamoney\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

interface TransactionEntityInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Sets the transaction amount.
   *
   * @param string $amount
   *   Transaction amount.
   */
  public function setAmount($amount);

  /**
   * Sets the transaction status.
   *
   * @param bool $status
   *   Transaction status.
   *
   * @return mixed
   */
  public function setStatus($status);

  /**
   * Sets the transaction data.
   *
   * @param $data
   *   Array of transaction data.
   *
   * @return mixed
   */
  public function setData($data);

  /**
   * Sets the transaction Order ID.
   *
   * @param $order_id
   *   Order ID.
   *
   * @return mixed
   */
  public function setOrderID($order_id);

  /**
   * Sets the transaction mail.
   *
   * @param $mail
   *   Mail.
   *
   * @return mixed
   */
  public function setMail($mail);

  /**
   * Get the transaction mail.
   *
   * @return mixed
   */
  public function getMail();

  /**
   * Get the transaction data.
   *
   * @return mixed
   */
  public function getData();

  /**
   * Get the transaction status.
   *
   * @return mixed
   */
  public function getStatus();

  /**
   * Get the transaction order ID.
   *
   * @return mixed
   */
  public function getOrderID();

  /**
   * Get the transaction amount.
   *
   * @return mixed
   */
  public function getAmount();

  /**
   * Get User ID.
   *
   * @return mixed
   */
  public function getUid();

}
