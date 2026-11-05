<?php

declare(strict_types=1);

namespace Drupal\liquid_timesheets\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\liquid_timesheets\TimesheetAccessControlHandler;
use Drupal\liquid_timesheets\TimesheetListBuilder;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\EntityOwnerInterface;
use Drupal\views\EntityViewsData;

/**
 * A block of time one person spent on a client project.
 */
#[ContentEntityType(
  id: 'timesheet',
  label: new TranslatableMarkup('Timesheet'),
  label_collection: new TranslatableMarkup('Timesheets'),
  label_singular: new TranslatableMarkup('timesheet'),
  label_plural: new TranslatableMarkup('timesheets'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'title',
    'owner' => 'uid',
  ],
  handlers: [
    'list_builder' => TimesheetListBuilder::class,
    'views_data' => EntityViewsData::class,
    'access' => TimesheetAccessControlHandler::class,
    'form' => [
      'default' => ContentEntityForm::class,
      'add' => ContentEntityForm::class,
      'edit' => ContentEntityForm::class,
      'delete' => ContentEntityDeleteForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  links: [
    'canonical' => '/timesheet/{timesheet}',
    'add-form' => '/timesheet/add',
    'edit-form' => '/timesheet/{timesheet}/edit',
    'delete-form' => '/timesheet/{timesheet}/delete',
    'collection' => '/admin/content/timesheets',
  ],
  admin_permission: 'administer timesheets',
  base_table: 'timesheet',
  field_ui_base_route: 'entity.timesheet.settings',
  label_count: [
    'singular' => '@count timesheet',
    'plural' => '@count timesheets',
  ],
)]
class Timesheet extends ContentEntityBase implements EntityOwnerInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['uid']
      ->setLabel(t('Staff'))
      ->setDescription(t('Who did the work.'))
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Generated on save (see preSave()); not on the form.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The client comes from the project (project.client in Views).
    $fields['project'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Project'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'timesheet_project')
      // Edit this view to change which projects can be picked.
      ->setSetting('handler', 'views')
      ->setSetting('handler_settings', [
        'view' => [
          'view_name' => 'timesheet_project_options',
          'display_name' => 'entity_reference_1',
          'arguments' => [],
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -8,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'settings' => ['link' => FALSE],
        'weight' => -8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['work_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date of activity'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE)
      ->setDefaultValue(['default_date_type' => 'now', 'default_date' => 'now'])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -6,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'datetime_default',
        'settings' => ['format_type' => 'html_date'],
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['hours'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Hours'))
      ->setRequired(TRUE)
      ->setSetting('precision', 10)
      ->setSetting('scale', 2)
      ->setSetting('min', 0)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -4,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_decimal',
        'settings' => ['scale' => 2],
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Plain text only: it goes into invoice CSVs, and line breaks must show.
    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setSetting('allowed_formats', ['plain_text'])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Entered into the external accounting/time system.
    $fields['imported'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Imported'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['client_invoiced'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Client invoiced'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 11,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   *
   * Titles follow the Atrium pattern: "TS - Staff - Fri, 2026-02-20 - Client".
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $date = $this->get('work_date')->date?->format('D, Y-m-d') ?? '';
    $client = $this->get('project')->entity?->get('client')->entity?->label() ?? '';
    $staff = $this->getOwner()?->getDisplayName() ?? '';
    $this->set('title', mb_substr("TS - $staff - $date - $client", 0, 255));
  }

}
