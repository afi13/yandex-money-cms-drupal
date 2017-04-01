<?php

namespace Drupal\yamoney\Entity;


use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\user\UserInterface;

/**
 * Defines the user role entity class.
 *
 * @ContentEntityType(
 *   id = "yamoney_transaction",
 *   label = @Translation("YAMoney Transaction"),
 *   handlers = {
 *     "access" = "Drupal\user\RoleAccessControlHandler",
 *     "list_builder" = "Drupal\yamoney\TransactionListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   base_table = "yamoney_transaction",
 *   admin_permission = "access yandex money transaction",
 *   static_cache = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "order_id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "delete-form" = "/admin/reports/yamoney_transactions/{yamoney_transaction}/delete",
 *     "collection" = "/admin/reports/yamoney_transactions",
 *   },
 * )
 */
class YAMoneyTransaction extends ContentEntityBase implements TransactionEntityInterface {
  use EntityChangedTrait;

  const STATUS_IN_PROCESS = 'in_process';
  const STATUS_PROCESSED = 'processed';
  const STATUS_PAYED = 'payed';
  const STATUS_FAILED = 'failed';

  /**
   * The transaction ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Transaction User id.
   *
   * @var int
   */
  public $uid;

  /**
   * Transaction amount.
   *
   * @var string
   */
  protected $amount;

  /**
   * Transaction status.
   *
   * @var string
   */
  protected $status;

  /**
   * The transaction user e-mail.
   *
   * @var string
   */
  protected $mail;

  /**
   * Unix timestamp when the transaction was created.
   *
   * @var int
   */
  protected $created;

  /**
   * Order id.
   *
   * @var string
   */
  protected $order_id;

  /**
   * Serialized array of additional transaction information.
   *
   * @var string
   */
  protected $data;

  /**
   * YAMoneyTransaction constructor.
   * @param array $values
   * @param string $entity_type
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->status = YAMoneyTransaction::STATUS_IN_PROCESS;
    $this->created = REQUEST_TIME;
    $this->data = [];
  }

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAmount($amount) {
    $this->amount = $amount;
  }

  /**
   * {@inheritdoc}
   */
  public function setData($data) {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderID($order_id) {
    $this->order_id = $order_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setMail($mail) {
    $this->mail = $mail;
  }

  /**
   * {@inheritdoc}
   */
  public function getMail() {
    return $this->mail;
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderID() {
    return $this->order_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    return $this->amount;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Transaction entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Contact entity.'))
      ->setReadOnly(TRUE);

    // amount
    $fields['amount'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Amount'))
      ->setDescription(t('The Amount of the Transaction entity.'))
      ->setReadOnly(TRUE);

    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The Mail of the Transaction entity.'))
      ->setReadOnly(TRUE);

    $fields['order_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Order ID'))
      ->setDescription(t('The Order ID of the Transaction entity.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setDescription(t('The Order ID of the Transaction entity.'))
      ->setReadOnly(TRUE);

    $fields['data'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Data'))
      ->setDescription(t('The Data of the Transaction entity.'))
      ->setReadOnly(TRUE);

    // Owner field of the contact.
    // Entity reference field, holds the reference to the user object.
    // The view shows the user name field of the user.
    // The form presents a auto complete field for the user name.
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User Name'))
      ->setDescription(t('The Name of the associated user.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }

  /**
   * Checks data value access.
   *
   * @param string $operation
   *   The operation to be performed.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return bool|\Drupal\Core\Access\AccessResultInterface
   *   The access result. Returns a boolean if $return_as_object is FALSE (this
   *   is the default) and otherwise an AccessResultInterface object.
   *   When a boolean is returned, the result of AccessInterface::isAllowed() is
   *   returned, i.e. TRUE means access is explicitly allowed, FALSE means
   *   access is either explicitly forbidden or "no opinion".
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // TODO: Implement access() method.
  }

  /**
   * The cache contexts associated with this object.
   *
   * These identify a specific variation/representation of the object.
   *
   * Cache contexts are tokens: placeholders that are converted to cache keys by
   * the @cache_contexts_manager service. The replacement value depends on the
   * request context (the current URL, language, and so on). They're converted
   * before storing an object in cache.
   *
   * @return string[]
   *   An array of cache context tokens, used to generate a cache ID.
   *
   * @see \Drupal\Core\Cache\Context\CacheContextsManager::convertTokensToKeys()
   */
  public function getCacheContexts() {
    // TODO: Implement getCacheContexts() method.
  }

  /**
   * The cache tags associated with this object.
   *
   * When this object is modified, these cache tags will be invalidated.
   *
   * @return string[]
   *   A set of cache tags.
   */
  public function getCacheTags() {
    // TODO: Implement getCacheTags() method.
  }

  /**
   * The maximum age for which this object may be cached.
   *
   * @return int
   *   The maximum time in seconds that this object may be cached.
   */
  public function getCacheMaxAge() {
    // TODO: Implement getCacheMaxAge() method.
  }

  /**
   * Determines if the current translation of the entity has unsaved changes.
   *
   * If the entity is translatable only translatable fields will be checked for
   * changes.
   *
   * @return bool
   *   TRUE if the current translation of the entity has changes.
   */
  public function hasTranslationChanges() {
    // TODO: Implement hasTranslationChanges() method.
  }

  /**
   * Marks the current revision translation as affected.
   *
   * @param bool|null $affected
   *   The flag value. A NULL value can be specified to reset the current value
   *   and make sure a new value will be computed by the system.
   *
   * @return $this
   */
  public function setRevisionTranslationAffected($affected) {
    // TODO: Implement setRevisionTranslationAffected() method.
  }

  /**
   * Checks whether the current translation is affected by the current revision.
   *
   * @return bool
   *   TRUE if the entity object is affected by the current revision, FALSE
   *   otherwise.
   */
  public function isRevisionTranslationAffected() {
    // TODO: Implement isRevisionTranslationAffected() method.
  }

  /**
   * Gets the entity UUID (Universally Unique Identifier).
   *
   * The UUID is guaranteed to be unique and can be used to identify an entity
   * across multiple systems.
   *
   * @return string|null
   *   The UUID of the entity, or NULL if the entity does not have one.
   */
  public function uuid() {
    // TODO: Implement uuid() method.
  }

  /**
   * Gets the identifier.
   *
   * @return string|int|null
   *   The entity identifier, or NULL if the object does not yet have an
   *   identifier.
   */
  public function id() {
    // TODO: Implement id() method.
  }

  /**
   * Gets the language of the entity.
   *
   * @return \Drupal\Core\Language\LanguageInterface
   *   The language object.
   */
  public function language() {
    // TODO: Implement language() method.
  }

  /**
   * Determines whether the entity is new.
   *
   * Usually an entity is new if no ID exists for it yet. However, entities may
   * be enforced to be new with existing IDs too.
   *
   * @return bool
   *   TRUE if the entity is new, or FALSE if the entity has already been saved.
   *
   * @see \Drupal\Core\Entity\EntityInterface::enforceIsNew()
   */
  public function isNew() {
    // TODO: Implement isNew() method.
  }

  /**
   * Enforces an entity to be new.
   *
   * Allows migrations to create entities with pre-defined IDs by forcing the
   * entity to be new before saving.
   *
   * @param bool $value
   *   (optional) Whether the entity should be forced to be new. Defaults to
   *   TRUE.
   *
   * @return $this
   *
   * @see \Drupal\Core\Entity\EntityInterface::isNew()
   */
  public function enforceIsNew($value = TRUE) {
    // TODO: Implement enforceIsNew() method.
  }

  /**
   * Gets the ID of the type of the entity.
   *
   * @return string
   *   The entity type ID.
   */
  public function getEntityTypeId() {
    // TODO: Implement getEntityTypeId() method.
  }

  /**
   * Gets the bundle of the entity.
   *
   * @return string
   *   The bundle of the entity. Defaults to the entity type ID if the entity
   *   type does not make use of different bundles.
   */
  public function bundle() {
    // TODO: Implement bundle() method.
  }

  /**
   * Gets the label of the entity.
   *
   * @return string|null
   *   The label of the entity, or NULL if there is no label defined.
   */
  public function label() {
    // TODO: Implement label() method.
  }

  /**
   * Gets the URL object for the entity.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   *
   * @deprecated in Drupal 8.0.0, intended to be removed in Drupal 9.0.0
   *   Use \Drupal\Core\Entity\EntityInterface::toUrl() instead.
   *
   * @see \Drupal\Core\Entity\EntityInterface::toUrl
   */
  public function urlInfo($rel = 'canonical', array $options = array()) {
    // TODO: Implement urlInfo() method.
  }

  /**
   * Gets the URL object for the entity.
   *
   * The entity must have an id already. Content entities usually get their IDs
   * by saving them.
   *
   * URI templates might be set in the links array in an annotation, for
   * example:
   * @code
   * links = {
   *   "canonical" = "/node/{node}",
   *   "edit-form" = "/node/{node}/edit",
   *   "version-history" = "/node/{node}/revisions"
   * }
   * @endcode
   * or specified in a callback function set like:
   * @code
   * uri_callback = "comment_uri",
   * @endcode
   * If the path is not set in the links array, the uri_callback function is
   * used for setting the path. If this does not exist and the link relationship
   * type is canonical, the path is set using the default template:
   * entity/entityType/id.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\Exception\UndefinedLinkTemplateException
   */
  public function toUrl($rel = 'canonical', array $options = array()) {
    // TODO: Implement toUrl() method.
  }

  /**
   * Gets the public URL for this entity.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return string
   *   The URL for this entity.
   *
   * @deprecated in Drupal 8.0.0, intended to be removed in Drupal 9.0.0
   *   Please use toUrl() instead.
   *
   * @see \Drupal\Core\Entity\EntityInterface::toUrl
   */
  public function url($rel = 'canonical', $options = array()) {
    // TODO: Implement url() method.
  }

  /**
   * Deprecated way of generating a link to the entity. See toLink().
   *
   * @param string|null $text
   *   (optional) The link text for the anchor tag as a translated string.
   *   If NULL, it will use the entity's label. Defaults to NULL.
   * @param string $rel
   *   (optional) The link relationship type. Defaults to 'canonical'.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return string
   *   An HTML string containing a link to the entity.
   *
   * @deprecated in Drupal 8.0.0, intended to be removed in Drupal 9.0.0
   *   Please use toLink() instead.
   *
   * @see \Drupal\Core\Entity\EntityInterface::toLink
   */
  public function link($text = NULL, $rel = 'canonical', array $options = []) {
    // TODO: Implement link() method.
  }

  /**
   * Generates the HTML for a link to this entity.
   *
   * @param string|null $text
   *   (optional) The link text for the anchor tag as a translated string.
   *   If NULL, it will use the entity's label. Defaults to NULL.
   * @param string $rel
   *   (optional) The link relationship type. Defaults to 'canonical'.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return \Drupal\Core\Link
   *   A Link to the entity.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\Exception\UndefinedLinkTemplateException
   */
  public function toLink($text = NULL, $rel = 'canonical', array $options = []) {
    // TODO: Implement toLink() method.
  }

  /**
   * Indicates if a link template exists for a given key.
   *
   * @param string $key
   *   The link type.
   *
   * @return bool
   *   TRUE if the link template exists, FALSE otherwise.
   */
  public function hasLinkTemplate($key) {
    // TODO: Implement hasLinkTemplate() method.
  }

  /**
   * Gets a list of URI relationships supported by this entity.
   *
   * @return string[]
   *   An array of link relationships supported by this entity.
   */
  public function uriRelationships() {
    // TODO: Implement uriRelationships() method.
  }

  /**
   * Loads an entity.
   *
   * @param mixed $id
   *   The id of the entity to load.
   *
   * @return static
   *   The entity object or NULL if there is no entity with the given ID.
   */
  public static function load($id) {
    // TODO: Implement load() method.
  }

  /**
   * Loads one or more entities.
   *
   * @param array $ids
   *   An array of entity IDs, or NULL to load all entities.
   *
   * @return static[]
   *   An array of entity objects indexed by their IDs.
   */
  public static function loadMultiple(array $ids = NULL) {
    // TODO: Implement loadMultiple() method.
  }

  /**
   * Constructs a new entity object, without permanently saving it.
   *
   * @param array $values
   *   (optional) An array of values to set, keyed by property name. If the
   *   entity type has bundles, the bundle key has to be specified.
   *
   * @return static
   *   The entity object.
   */
  public static function create(array $values = array()) {
    // TODO: Implement create() method.
  }

  /**
   * Saves an entity permanently.
   *
   * When saving existing entities, the entity is assumed to be complete,
   * partial updates of entities are not supported.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  public function save() {
    // TODO: Implement save() method.
  }

  /**
   * Deletes an entity permanently.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  public function delete() {
    // TODO: Implement delete() method.
  }

  /**
   * Acts on an entity before the presave hook is invoked.
   *
   * Used before the entity is saved and before invoking the presave hook. Note
   * that in case of translatable content entities this callback is only fired
   * on their current translation. It is up to the developer to iterate
   * over all translations if needed. This is different from its counterpart in
   * the Field API, FieldItemListInterface::preSave(), which is fired on all
   * field translations automatically.
   * @todo Adjust existing implementations and the documentation according to
   *   https://www.drupal.org/node/2577609 to have a consistent API.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   *
   * @see \Drupal\Core\Field\FieldItemListInterface::preSave()
   *
   * @throws \Exception
   *   When there is a problem that should prevent saving the entity.
   */
  public function preSave(EntityStorageInterface $storage) {
    // TODO: Implement preSave() method.
  }

  /**
   * Acts on a saved entity before the insert or update hook is invoked.
   *
   * Used after the entity is saved, but before invoking the insert or update
   * hook. Note that in case of translatable content entities this callback is
   * only fired on their current translation. It is up to the developer to
   * iterate over all translations if needed.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param bool $update
   *   TRUE if the entity has been updated, or FALSE if it has been inserted.
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    // TODO: Implement postSave() method.
  }

  /**
   * Acts on a created entity before hooks are invoked.
   *
   * Used after the entity is created, but before saving the entity and before
   * any of the presave hooks are invoked.
   *
   * See the @link entity_crud Entity CRUD topic @endlink for more information.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   *
   * @see \Drupal\Core\Entity\EntityInterface::create()
   */
  public function postCreate(EntityStorageInterface $storage) {
    // TODO: Implement postCreate() method.
  }

  /**
   * Acts on entities before they are deleted and before hooks are invoked.
   *
   * Used before the entities are deleted and before invoking the delete hook.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   An array of entities.
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    // TODO: Implement preDelete() method.
  }

  /**
   * Acts on deleted entities before the delete hook is invoked.
   *
   * Used after the entities are deleted but before invoking the delete hook.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   An array of entities.
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    // TODO: Implement postDelete() method.
  }

  /**
   * Acts on loaded entities.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   An array of entities.
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    // TODO: Implement postLoad() method.
  }

  /**
   * Creates a duplicate of the entity.
   *
   * @return static
   *   A clone of $this with all identifiers unset, so saving it inserts a new
   *   entity into the storage system.
   */
  public function createDuplicate() {
    // TODO: Implement createDuplicate() method.
  }

  /**
   * Gets the entity type definition.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity type definition.
   */
  public function getEntityType() {
    // TODO: Implement getEntityType() method.
  }

  /**
   * Gets a list of entities referenced by this entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entities.
   */
  public function referencedEntities() {
    // TODO: Implement referencedEntities() method.
  }

  /**
   * Gets the original ID.
   *
   * @return int|string|null
   *   The original ID, or NULL if no ID was set or for entity types that do not
   *   support renames.
   */
  public function getOriginalId() {
    // TODO: Implement getOriginalId() method.
  }

  /**
   * Returns the cache tags that should be used to invalidate caches.
   *
   * This will not return additional cache tags added through addCacheTags().
   *
   * @return string[]
   *   Set of cache tags.
   *
   * @see \Drupal\Core\Cache\RefinableCacheableDependencyInterface::addCacheTags()
   * @see \Drupal\Core\Cache\CacheableDependencyInterface::getCacheTags()
   */
  public function getCacheTagsToInvalidate() {
    // TODO: Implement getCacheTagsToInvalidate() method.
  }

  /**
   * Sets the original ID.
   *
   * @param int|string|null $id
   *   The new ID to set as original ID. If the entity supports renames, setting
   *   NULL will prevent an update from being considered a rename.
   *
   * @return $this
   */
  public function setOriginalId($id) {
    // TODO: Implement setOriginalId() method.
  }

  /**
   * Gets a typed data object for this entity object.
   *
   * The returned typed data object wraps this entity and allows dealing with
   * entities based on the generic typed data API.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface
   *   The typed data object for this entity.
   *
   * @see \Drupal\Core\TypedData\TypedDataInterface
   */
  public function getTypedData() {
    // TODO: Implement getTypedData() method.
  }

  /**
   * Gets the key that is used to store configuration dependencies.
   *
   * @return string
   *   The key to be used in configuration dependencies when storing
   *   dependencies on entities of this type.
   *
   * @see \Drupal\Core\Entity\EntityTypeInterface::getConfigDependencyKey()
   */
  public function getConfigDependencyKey() {
    // TODO: Implement getConfigDependencyKey() method.
  }

  /**
   * Gets the configuration dependency name.
   *
   * Configuration entities can depend on content and configuration entities.
   * They store an array of content and config dependency names in their
   * "dependencies" key.
   *
   * @return string
   *   The configuration dependency name.
   *
   * @see \Drupal\Core\Config\Entity\ConfigDependencyManager
   */
  public function getConfigDependencyName() {
    // TODO: Implement getConfigDependencyName() method.
  }

  /**
   * Gets the configuration target identifier for the entity.
   *
   * Used to supply the correct format for storing a reference targeting this
   * entity in configuration.
   *
   * @return string
   *   The configuration target identifier.
   */
  public function getConfigTarget() {
    // TODO: Implement getConfigTarget() method.
  }

  /**
   * Provides field definitions for a specific bundle.
   *
   * This function can return definitions both for bundle fields (fields that
   * are not defined in $base_field_definitions, and therefore might not exist
   * on some bundles) as well as bundle-specific overrides of base fields
   * (fields that are defined in $base_field_definitions, and therefore exist
   * for all bundles). However, bundle-specific base field overrides can also
   * be provided by 'base_field_override' configuration entities, and that is
   * the recommended approach except in cases where an entity type needs to
   * provide a bundle-specific base field override that is decoupled from
   * configuration. Note that for most entity types, the bundles themselves are
   * derived from configuration (e.g., 'node' bundles are managed via
   * 'node_type' configuration entities), so decoupling bundle-specific base
   * field overrides from configuration only makes sense for entity types that
   * also decouple their bundles from configuration. In cases where both this
   * function returns a bundle-specific override of a base field and a
   * 'base_field_override' configuration entity exists, the latter takes
   * precedence.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition. Useful when a single class is used for multiple,
   *   possibly dynamic entity types.
   * @param string $bundle
   *   The bundle.
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $base_field_definitions
   *   The list of base field definitions.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of bundle field definitions, keyed by field name.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldDefinitions()
   * @see \Drupal\Core\Entity\FieldableEntityInterface::baseFieldDefinitions()
   *
   * @todo WARNING: This method will be changed in
   *   https://www.drupal.org/node/2346347.
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    // TODO: Implement bundleFieldDefinitions() method.
  }

  /**
   * Determines whether the entity has a field with the given name.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return bool
   *   TRUE if the entity has a field with the given name. FALSE otherwise.
   */
  public function hasField($field_name) {
    // TODO: Implement hasField() method.
  }

  /**
   * Gets the definition of a contained field.
   *
   * @param string $name
   *   The name of the field.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|null
   *   The definition of the field or null if the field does not exist.
   */
  public function getFieldDefinition($name) {
    // TODO: Implement getFieldDefinition() method.
  }

  /**
   * Gets an array of field definitions of all contained fields.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of field definitions, keyed by field name.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldDefinitions()
   */
  public function getFieldDefinitions() {
    // TODO: Implement getFieldDefinitions() method.
  }

  /**
   * Gets an array of all field values.
   *
   * Gets an array of plain field values, including only non-computed values.
   * Note that the structure varies by entity type and bundle.
   *
   * @return array
   *   An array of field values, keyed by field name.
   */
  public function toArray() {
    // TODO: Implement toArray() method.
  }

  /**
   * Gets a field item list.
   *
   * @param string $field_name
   *   The name of the field to get; e.g., 'title' or 'name'.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The field item list, containing the field items.
   *
   * @throws \InvalidArgumentException
   *   If an invalid field name is given.
   */
  public function get($field_name) {
    // TODO: Implement get() method.
  }

  /**
   * Sets a field value.
   *
   * @param string $field_name
   *   The name of the field to set; e.g., 'title' or 'name'.
   * @param mixed $value
   *   The value to set, or NULL to unset the field.
   * @param bool $notify
   *   (optional) Whether to notify the entity of the change. Defaults to
   *   TRUE. If the update stems from the entity, set it to FALSE to avoid
   *   being notified again.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If the specified field does not exist.
   */
  public function set($field_name, $value, $notify = TRUE) {
    // TODO: Implement set() method.
  }

  /**
   * Gets an array of all field item lists.
   *
   * @param bool $include_computed
   *   If set to TRUE, computed fields are included. Defaults to TRUE.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface[]
   *   An array of field item lists implementing, keyed by field name.
   */
  public function getFields($include_computed = TRUE) {
    // TODO: Implement getFields() method.
  }

  /**
   * Gets an array of field item lists for translatable fields.
   *
   * @param bool $include_computed
   *   If set to TRUE, computed fields are included. Defaults to TRUE.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface[]
   *   An array of field item lists implementing, keyed by field name.
   */
  public function getTranslatableFields($include_computed = TRUE) {
    // TODO: Implement getTranslatableFields() method.
  }

  /**
   * Reacts to changes to a field.
   *
   * Note that this is invoked after any changes have been applied.
   *
   * @param string $field_name
   *   The name of the field which is changed.
   *
   * @throws \InvalidArgumentException
   *   When trying to assign a value to the language field that matches an
   *   existing translation.
   * @throws \LogicException
   *   When trying to change:
   *   - The language of a translation.
   *   - The value of the flag identifying the default translation object.
   */
  public function onChange($field_name) {
    // TODO: Implement onChange() method.
  }

  /**
   * Validates the currently set values.
   *
   * @return \Drupal\Core\Entity\EntityConstraintViolationListInterface
   *   A list of constraint violations. If the list is empty, validation
   *   succeeded.
   */
  public function validate() {
    // TODO: Implement validate() method.
  }

  /**
   * Checks whether entity validation is required before saving the entity.
   *
   * @return bool
   *   TRUE if validation is required, FALSE if not.
   */
  public function isValidationRequired() {
    // TODO: Implement isValidationRequired() method.
  }

  /**
   * Sets whether entity validation is required before saving the entity.
   *
   * @param bool $required
   *   TRUE if validation is required, FALSE otherwise.
   *
   * @return $this
   */
  public function setValidationRequired($required) {
    // TODO: Implement setValidationRequired() method.
  }

  /**
   * Adds cache contexts.
   *
   * @param string[] $cache_contexts
   *   The cache contexts to be added.
   *
   * @return $this
   */
  public function addCacheContexts(array $cache_contexts) {
    // TODO: Implement addCacheContexts() method.
  }

  /**
   * Adds cache tags.
   *
   * @param string[] $cache_tags
   *   The cache tags to be added.
   *
   * @return $this
   */
  public function addCacheTags(array $cache_tags) {
    // TODO: Implement addCacheTags() method.
  }

  /**
   * Merges the maximum age (in seconds) with the existing maximum age.
   *
   * The max age will be set to the given value if it is lower than the existing
   * value.
   *
   * @param int $max_age
   *   The max age to associate.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   Thrown if a non-integer value is supplied.
   */
  public function mergeCacheMaxAge($max_age) {
    // TODO: Implement mergeCacheMaxAge() method.
  }

  /**
   * Adds a dependency on an object: merges its cacheability metadata.
   *
   * @param \Drupal\Core\Cache\CacheableDependencyInterface|object $other_object
   *   The dependency. If the object implements CacheableDependencyInterface,
   *   then its cacheability metadata will be used. Otherwise, the passed in
   *   object must be assumed to be uncacheable, so max-age 0 is set.
   *
   * @return $this
   *
   * @see \Drupal\Core\Cache\CacheableMetadata::createFromObject()
   */
  public function addCacheableDependency($other_object) {
    // TODO: Implement addCacheableDependency() method.
  }

  /**
   * Determines whether a new revision should be created on save.
   *
   * @return bool
   *   TRUE if a new revision should be created.
   *
   * @see \Drupal\Core\Entity\EntityInterface::setNewRevision()
   */
  public function isNewRevision() {
    // TODO: Implement isNewRevision() method.
  }

  /**
   * Enforces an entity to be saved as a new revision.
   *
   * @param bool $value
   *   (optional) Whether a new revision should be saved.
   *
   * @throws \LogicException
   *   Thrown if the entity does not support revisions.
   *
   * @see \Drupal\Core\Entity\EntityInterface::isNewRevision()
   */
  public function setNewRevision($value = TRUE) {
    // TODO: Implement setNewRevision() method.
  }

  /**
   * Gets the revision identifier of the entity.
   *
   * @return
   *   The revision identifier of the entity, or NULL if the entity does not
   *   have a revision identifier.
   */
  public function getRevisionId() {
    // TODO: Implement getRevisionId() method.
  }

  /**
   * Checks if this entity is the default revision.
   *
   * @param bool $new_value
   *   (optional) A Boolean to (re)set the isDefaultRevision flag.
   *
   * @return bool
   *   TRUE if the entity is the default revision, FALSE otherwise. If
   *   $new_value was passed, the previous value is returned.
   */
  public function isDefaultRevision($new_value = NULL) {
    // TODO: Implement isDefaultRevision() method.
  }

  /**
   * Acts on a revision before it gets saved.
   *
   * @param EntityStorageInterface $storage
   *   The entity storage object.
   * @param \stdClass $record
   *   The revision object.
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    // TODO: Implement preSaveRevision() method.
  }

  /**
   * Sets the transaction status.
   *
   * @param bool $status
   *   Transaction status.
   *
   * @return mixed
   */
  public function setStatus($status) {
    // TODO: Implement setStatus() method.
  }

  /**
   * Get User ID.
   *
   * @return mixed
   */
  public function getUid() {
    // TODO: Implement getUid() method.
  }

  /**
   * Checks whether the translation is the default one.
   *
   * @return bool
   *   TRUE if the translation is the default one, FALSE otherwise.
   */
  public function isDefaultTranslation() {
    // TODO: Implement isDefaultTranslation() method.
  }

  /**
   * Checks whether the translation is new.
   *
   * @return bool
   *   TRUE if the translation is new, FALSE otherwise.
   */
  public function isNewTranslation() {
    // TODO: Implement isNewTranslation() method.
  }

  /**
   * Returns the languages the data is translated to.
   *
   * @param bool $include_default
   *   (optional) Whether the default language should be included. Defaults to
   *   TRUE.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]
   *   An associative array of language objects, keyed by language codes.
   */
  public function getTranslationLanguages($include_default = TRUE) {
    // TODO: Implement getTranslationLanguages() method.
  }

  /**
   * Gets a translation of the data.
   *
   * The returned translation has to be of the same type than this typed data
   * object.
   *
   * @param $langcode
   *   The language code of the translation to get or
   *   LanguageInterface::LANGCODE_DEFAULT
   *   to get the data in default language.
   *
   * @return $this
   *   A typed data object for the translated data.
   *
   * @throws \InvalidArgumentException
   *   If an invalid or non-existing translation language is specified.
   */
  public function getTranslation($langcode) {
    // TODO: Implement getTranslation() method.
  }

  /**
   * Returns the translatable object referring to the original language.
   *
   * @return $this
   *   The translation object referring to the original language.
   */
  public function getUntranslated() {
    // TODO: Implement getUntranslated() method.
  }

  /**
   * Returns TRUE there is a translation for the given language code.
   *
   * @param string $langcode
   *   The language code identifying the translation.
   *
   * @return bool
   *   TRUE if the translation exists, FALSE otherwise.
   */
  public function hasTranslation($langcode) {
    // TODO: Implement hasTranslation() method.
  }

  /**
   * Adds a new translation to the translatable object.
   *
   * @param string $langcode
   *   The language code identifying the translation.
   * @param array $values
   *   (optional) An array of initial values to be assigned to the translatable
   *   fields. Defaults to none.
   *
   * @return $this
   *
   * @throws \InvalidArgumentException
   *   If an invalid or existing translation language is specified.
   */
  public function addTranslation($langcode, array $values = array()) {
    // TODO: Implement addTranslation() method.
  }

  /**
   * Removes the translation identified by the given language code.
   *
   * @param string $langcode
   *   The language code identifying the translation to be removed.
   */
  public function removeTranslation($langcode) {
    // TODO: Implement removeTranslation() method.
  }

  /**
   * Returns the translation support status.
   *
   * @return bool
   *   TRUE if the object has translation support enabled.
   */
  public function isTranslatable() {
    // TODO: Implement isTranslatable() method.
  }
}
