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
      <th></th>
      <th>{ts domain="de.systopia.l10nmo"}File{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Domains{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Locales{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Upload{/ts}</th>
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
        <span title="{$line.description}"><code>{$line.name}</code>{if $line.type == 'p'}> {ts domain="de.systopia.l10nmo"}(pack){/ts}{/if}</span>
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
        <a class="crm-weight-arrow" onclick="l10nx_execute_command('first:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/first.gif" title="{ts domain="de.systopia.l10nmo"}Move to top{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move to top{/ts}" class="order-icon"></a>
        <a class="crm-weight-arrow" onclick="l10nx_execute_command('up:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/up.gif" title="{ts domain="de.systopia.l10nmo"}Move up one row{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move up one row{/ts}" class="order-icon"></a>
        <a class="crm-weight-arrow" onclick="l10nx_execute_command('down:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/down.gif" title="{ts domain="de.systopia.l10nmo"}Move down one row{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move down one row{/ts}" class="order-icon"></a>
        <a class="crm-weight-arrow" onclick="l10nx_execute_command('last:{$line_nr}');"><img src="{$config->userFrameworkResourceURL}/i/arrow/last.gif" title="{ts domain="de.systopia.l10nmo"}Move to bottom{/ts}" alt="{ts domain="de.systopia.l10nmo"}Move to bottom{/ts}" class="order-icon"></a>
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


{literal}
  <script type="application/javascript">
    /**
     * This will set the action parameter and then submit the form
     */
    function l10nx_execute_command(action_name) {
      cj("input[name=l10nx_command]").val(action_name);
      cj("input[name=l10nx_command]").closest("form").submit();
    }
  </script>
{/literal}
