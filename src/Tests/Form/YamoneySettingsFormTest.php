<?php

/**
 * @file
 * Contains \Drupal\yamoney\Tests\Form\YamoneySettingsFormTest.
 */

namespace Drupal\yamoney\Tests\Form;

use Drupal\simpletest\WebTestBase;

/**
 * Tests yamoney settings form.
 *
 * @group yamoney
 */
class YamoneySettingsFormTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['yamoney'];

  /**
   * A user that has permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $web_user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->web_user = $this->drupalCreateUser(['access yamoney settings']);
    $this->drupalLogin($this->web_user);
  }

  /**
   * Testing Yamoney settings form.
   *
   * @throws \Exception
   *   Exceptions if tests failed.
   */
  public function testFormPage() {
    $this->drupalGet('/admin/config/system/yamoney');
    $this->assertText(t('Base options'));
    $this->assertText(t('Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу <a href="https://money.yandex.ru/doc.xml?id=527132">https://money.yandex.ru/doc.xml?id=527132</a> (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.'));
    $this->assertText(t('The list of IP addresses which has access to payment callbacks. One per line.<br/>0.0.0.0 means allow from all.'));
    $this->assertText('Allowed IPs for callbacks');
    $this->assertText('Payment mode');
    $this->assertText('Enabled payment methods');
    $this->assertText('Default payment method');
    $this->assertText('Yandex shop settings');
    $this->assertText('Select if you have a shop in Yandex.Money');
    $this->assertText('You shop ID. If you have any shops in you Yandex account.');
    $this->assertText('Shop SCID');
    $this->assertText('Shop security key');
    $this->assertText('Yandex purse settings');
    $this->assertText('Your Yandex.Money purse number.');
    $this->assertText('Your Yandex.Money pay comment.');
    $this->assertText('Text for success and fail payment pages');
    $this->assertText('Text for success page');
    $this->assertText('Text for fail page');
  }

}
