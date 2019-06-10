<?php
/*-------------------------------------------------------+
| L10n Profiling Extension                               |
| Copyright (C) 2019 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_L10nmo_ExtensionUtil as E;

/**
 * Upload form for .mo files and language packs
 */
class CRM_L10nmo_Form_Upload extends CRM_Core_Form {


  public function buildQuickForm() {

    // verify folders
    $file_folder = CRM_L10nmo_Form_Configuration::getCustomTranslationFolder(FALSE);
    if (!is_dir($file_folder) || !is_writeable($file_folder)) {
      CRM_Core_Session::setStatus(E::ts("Cannot write to the folder for custom .MO files (%1).", [1 => $file_folder], E::ts("Configuration Error"), 'error'));
    }
    $pack_folder = CRM_L10nmo_Form_Configuration::getCustomTranslationFolder(TRUE);
    if (!is_dir($pack_folder) || !is_writeable($pack_folder)) {
      CRM_Core_Session::setStatus(E::ts("Cannot write to the folder for language packs (%1).", [1 => $pack_folder], E::ts("Configuration Error"), 'error'));
    }

    $this->add(
        'select',
        "type",
        E::ts("Type"),
        ['f' => E::ts('Single File (.MO file)'), 'p' => E::ts('Language Pack (.ZIP file)')],
        TRUE,
        ['class' => 'l10nmo-type']
    );

    $this->add(
        'File',
        'upload_file',
        E::ts('File'),
        'size=30 maxlength=255',
        TRUE);
    //$this->setMaxFileSize();

    $this->add(
        'text',
        'file_name',
        E::ts('File Name'),
        [],
        TRUE);
    $this->registerRule('system_friendly_name', 'callback', 'verifySystemfriendlyName', 'CRM_L10nmo_Form_Upload');
    $this->addRule('file_name', E::ts("No whitespaces or special characters allowed"), 'system_friendly_name');

    $this->add(
        'text',
        'description',
        E::ts('Description'),
        ['class' => 'huge'],
        FALSE);

    $this->addButtons([
      [
          'type'      => 'upload',
          'icon'      => 'fa-plus-circle',
          'name'      => E::ts('Upload'),
          'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    // verify file
    $upload_file = $_FILES['upload_file'];
    if ($values['type'] == 'f') {
      if ($upload_file['type'] != 'application/x-gettext-translation') {
        CRM_Core_Session::setStatus(E::ts("Submitted file '%1' is not a gettext .mo file!", [1 => $upload_file['name']]), E::ts("Wrong File Type"), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=0'));
      }
    } else {
      if ($upload_file['type'] != 'application/zip') {
        CRM_Core_Session::setStatus(E::ts("Submitted file '%1' is not a ZIP file!", [1 => $upload_file['name']]), E::ts("Wrong File Type"), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=0'));
      }
    }


    // create File entity
    if ($values['type'] == 'f') {
      // FILE UPLOAD
      // copy file to the target folder
      $target_folder = CRM_L10nmo_Form_Configuration::getCustomTranslationFolder(FALSE);
      $target_path = $target_folder . DIRECTORY_SEPARATOR . $values['file_name'] . '.mo';
      if (file_exists($target_path)) {
        CRM_Core_Session::setStatus(E::ts("File '%1.mp' already exists!", [1 => $values['file_name']]), E::ts("File Exists"), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=0'));
      }
      copy($upload_file['tmp_name'], $target_path);

      // create file entity
      civicrm_api3('File', 'create', [
          'mime_type'   => 'application/x-gettext-translation',
          'uri'         => 'l10nxmo:' . $values['file_name'] . '.mo',
          'description' => $values['description'],
          'upload_date' => date('YmdHis'),
          'created_id'  => CRM_Core_Session::getLoggedInContactID()
      ]);
      CRM_Core_Session::setStatus(E::ts("File '%1.mo' added!", [1 => $values['file_name']]), E::ts("Success"), 'info');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom', 'reset=1'));


    } else {
      // PACK UPLOAD
      $target_folder = CRM_L10nmo_Form_Configuration::getCustomTranslationFolder(TRUE);
      $target_path = $target_folder . DIRECTORY_SEPARATOR . $values['file_name'];
      if (file_exists($target_path)) {
        CRM_Core_Session::setStatus(E::ts("Directory '%1' already exists!", [1 => $values['file_name']]), E::ts("File Exists"), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=0'));
      }
      mkdir($target_path);

      // unzip files
      $zip = new ZipArchive();
      $file = $zip->open($upload_file['tmp_name']);
      if ($file !== TRUE) {
        CRM_Core_Session::setStatus(E::ts("Unpacking '%1' failed!", [1 => $values['file_name']]), E::ts("Failure"), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=0'));
      }
      $zip->extractTo($target_path);
      $zip->close();

      // verify extracted files
      $language_count = CRM_L10nmo_Form_Configuration::getPackLanguageCount($target_path);
      if (!$language_count) {
        // contains no languages -> delete
        CRM_L10nmo_Form_Configuration::rrmdir($target_path);
        CRM_Core_Session::setStatus(E::ts("ZIP file did not contain any languages!<br/>Be sure that your file content has the following structure:<code><br/>en_US/LC_MESSAGES/civicrm.mo<br/>de_DE/LC_MESSAGES/civicrm.mo<br/>es_MX/LC_MESSAGES/civicrm.mo<br/>...</code>"), E::ts("Failure"), 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=0'));
      }

      // create file
      civicrm_api3('File', 'create', [
          'mime_type'   => 'application/x-gettext-translation',
          'uri'         => 'l10nxpack:' . $values['file_name'],
          'description' => $values['description'],
          'upload_date' => date('YmdHis'),
          'created_id'  => CRM_Core_Session::getLoggedInContactID()
      ]);
      CRM_Core_Session::setStatus(E::ts("Translation pack '%1' added!", [1 => $values['file_name']]), E::ts("Success"), 'info');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom', 'reset=1'));
    }

    parent::postProcess();
  }

  /**
   * Only allow a very restricted character set
   * @param $string the string to check
   */
  public static function verifySystemfriendlyName($string) {
    return preg_match('/^[0-9a-zA-Z_]+$/', $string);
  }
}
