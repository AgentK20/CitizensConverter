<h2>Conversion Completed!</h2>
<strong>{$converted} NPCs have been converted.</strong><br>
{if $skipped>0}<div class="alert alert-error">
        {$skipped} NPCs ({$skippedString}) were not found in their expected locations, and were skipped (Happens if you've deleted NPCs by hand or in game).
</div>{/if}
The new file can be downloaded <a href="http://citizensnpcs.com/converter/?p=dl&id={$id}">here</a>, or you can wget <a href="{$webLocation}">{$webLocation}</a><br \>
It will be available until the {$date} EST.<br \>Click the link to download it, or wget the file to your server's ./plugins/Citizens folder with the name "saves.yml".