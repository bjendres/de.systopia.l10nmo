{*-------------------------------------------------------+
| L10n Profiling Extension                               |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*}


<table class="l10nmo-config">
  <thead>
    <tr>
      <th>{ts domain="de.systopia.l10nmo"}Use{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}File{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Used for Domains{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Used for Locales{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Uploaded{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Order{/ts}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$lines key=line_nr item=line}
    {capture assign="active"}active_{$line_nr}{/capture}
    {capture assign="domain"}domain_{$line_nr}{/capture}
    {capture assign="locale"}locale_{$line_nr}{/capture}
    {capture assign="info"}info_{$line_nr}{/capture}
    {$form.$info.html}
    <tr>
      <td>
        {$form.$active.html}
      </td>
      <td>
        <span title="{$line.description}"><code>{$line.name}</code>{if $line.type == 'p'} {ts domain="de.systopia.l10nmo"}(pack){/ts}{/if}</span>
      </td>
      <td>
        {$form.$domain.html}
      </td>
      <td>
        {$form.$locale.html}
      </td>
      <td>
        {$line.upload_date|crmDate}
      </td>
      <td>
        <a class="crm-weight-arrow crm-hover-button" onclick="l10nx_execute_command('first:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/first.gif" title="{ts domain="de.systopia.l10nmo"}Move to top{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move to top{/ts}" class="order-icon"></a>
        <a class="crm-weight-arrow crm-hover-button" onclick="l10nx_execute_command('up:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/up.gif" title="{ts domain="de.systopia.l10nmo"}Move up one row{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move up one row{/ts}" class="order-icon"></a>
        <a class="crm-weight-arrow crm-hover-button" onclick="l10nx_execute_command('down:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/down.gif" title="{ts domain="de.systopia.l10nmo"}Move down one row{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move down one row{/ts}" class="order-icon"></a>
        <a class="crm-weight-arrow crm-hover-button" onclick="l10nx_execute_command('last:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/last.gif" title="{ts domain="de.systopia.l10nmo"}Move to bottom{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move to bottom{/ts}" class="order-icon"></a>
      </td>
      <td>
        <a class="action-item crm-hover-button l10n-action-delete" onclick="l10nx_execute_command('delete:{$line_nr}');" title="{ts domain="de.systopia.l10nmo"}Delete Translation Files{/ts}">{ts domain="de.systopia.l10nmo"}delete{/ts}</a>
        <a class="action-item crm-hover-button l10n-action-update" onclick="l10nx_execute_command('update:{$line_nr}');" title="{ts domain="de.systopia.l10nmo"}Update Translation Files{/ts}">{ts domain="de.systopia.l10nmo"}update{/ts}</a>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>


<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
<br/>
<div>
  <h3>{ts domain="de.systopia.l10nmo"}About Custom Translation Files{/ts}</h3>
  <p>{ts domain="de.systopia.l10nmo"}Here you can upload custom translation files. We distinguish between individual translation files (MO files), and set of those files already tagged with the language ("packs").{/ts}</p>
  <p>{ts domain="de.systopia.l10nmo"}You can define for each uploaded translation file you have define which domain it is applied to:{/ts}
    <ul>
      <li><code>civicrm</code>: {ts domain="de.systopia.l10nmo"}CiviCRM User Interface. This should already be covered by CiviCRM's l10n pack, but you might want to use this to overwrite some translations.{/ts}</li>
      <li><code>civicrm-option</code>: {ts domain="de.systopia.l10nmo"}Defines the translation of all dropdown values (called OptionValues). These values should be stored in the 'data language' (e.g. English) and can then be translated on the fly for users in other languages. <strong>(experimental!)</strong>{/ts}</li>
      <li><code>civicrm-data</code>: {ts domain="de.systopia.l10nmo"}Offers translations for user generated data, e.g. event descriptions or activity subjects. <strong>(under development, experimental!)</strong>{/ts}</li>
      <li><code>[extensions]</code>: {ts domain="de.systopia.l10nmo"}Each extension defines its own translation domain. Choose this to modify the translation of an extension's user interface. Remark: the extension has to use the correct localisation techniques for this to work.{/ts}</li>
    </ul>
  </p>
  {capture assign=l10nconfig}{crmURL p='civicrm/admin/l10nx' q="reset=1"}{/capture}
  <p>{ts domain="de.systopia.l10nmo" 1=$l10nconfig}Make sure you have <a href="%1">configured the extended localisation</a> accordingly.{/ts}</p>

  <h3>{ts domain="de.systopia.l10nmo"}Where do I get these files?{/ts}</h3>
  <p>{ts domain="de.systopia.l10nmo"}First you would have to get a "template" file (PO file). You can do this for example in one of the following ways:{/ts}
    <ol>
      {capture assign=l10ntemplates}{crmURL p='civicrm/l10nx/templates' q="reset=1"}{/capture}
      <li>{ts domain="de.systopia.l10nmo" 1=$l10ntemplates}<strong>Generate</strong>: Use <a href="%1">this built-in template generator</a>{/ts}</li>
      <li>{ts domain="de.systopia.l10nmo"}<strong>Capture</strong>: If you install the <a href="https://github.com/systopia/de.systopia.l10nprofiler">Profiler extension</a>, you can use that to record the strings used by a specific workflow.{/ts}</li>
      <li>{ts domain="de.systopia.l10nmo"}<strong>From Others</strong>: Obviously, you can also get these files from other users. So far, however, there is not a common pool of such files, so just contact e.g. <code>@bjoern.endres</code> on <a href="https://chat.civicrm.org">Mattermost</a>.{/ts}</li>
    </ol>
  </p>
  <p>{ts domain="de.systopia.l10nmo"}You can then go on and edit these (.PO) files to your liking with a specialised editor like <a href="https://poedit.net">POEdit</a>. The resulting (.MO) files can be uploaded here.{/ts}</p>
</div>


{literal}
  <script type="text/javascript">
    /**
     * This will set the action parameter and then submit the form
     */
    function l10nx_execute_command(action_name) {
      cj("input[name=l10nx_command]").val(action_name);
      cj("input[name=l10nx_command]").closest("form").submit();
    }
  </script>
{/literal}
