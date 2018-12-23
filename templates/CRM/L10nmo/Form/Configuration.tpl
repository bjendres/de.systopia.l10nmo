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
      <th>{ts domain="de.systopia.l10nmo"}Domain{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Type{/ts}</th>
      <th>{ts domain="de.systopia.l10nmo"}Specification{/ts}</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$lines item=line_nr}
    {capture assign="domain"}domain_{$line_nr}{/capture}
    {capture assign="type"}type_{$line_nr}{/capture}
    {capture assign="pack"}pack_{$line_nr}{/capture}
    {capture assign="file"}file_{$line_nr}{/capture}
    {capture assign="locale"}locale_{$line_nr}{/capture}
    <tr>
      <td>
        {$form.$domain.html}
      </td>
      <td>
        {$form.$type.html}
      </td>
      <td>
        <span class="l10nmo-pack">{$form.$pack.html}</span>
        <span class="l10nmo-file">{$form.$locale.html} {$form.$file.html}</span>
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