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
    {capture assign="domain"}domain_{$line_nr}{/capture}
    {capture assign="locale"}locale_{$line_nr}{/capture}
    <tr>
      <td>
        <span title="{$line.description}"><code>{$line.name}</code> ({if $line.type == 'f'}MO File{else}PACK{/if})</span>
      </td>
      <td>
        {$form.$domain.html}
      </td>
      <td>
        {$form.$locale.html}
      </td>
      <td>
        {ts domain="de.systopia.l10nmo" 1=$line.upload_date 2=$line.created_id}Uploaded on %1 by %2{/ts}
      </td>
      <td>TODO</td>
      <td>TODO</td>
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
   * This function makes sure, that only the selected
   *  specification (pack or file) is shown
   *
   * @param select the select field
   */
  function adjust_type(select) {
    console.log(select);
    let type = cj(select).val();
    if (type == 'p') {
      cj(select).parent().parent().find("span.l10nmo-pack").show();
      cj(select).parent().parent().find("span.l10nmo-file").hide();
    } else {
      cj(select).parent().parent().find("span.l10nmo-pack").hide();
      cj(select).parent().parent().find("span.l10nmo-file").show();
    }
  }

  // run initially
  cj("select.l10nmo-type").each(function() {
    adjust_type(cj(this));
  });

  // add handler
  cj("select.l10nmo-type").change(function(event) {
    adjust_type(event.target);
  });
</script>
{/literal}