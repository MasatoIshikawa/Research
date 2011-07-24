{if $subject}

<h3>科目設定</h3>
<form action="{$ExtensionBaseUrl}/update/" method="post" class="inline">

{kyomu_edit_table columns=$columns source=$subject name=subject}

<input type="hidden" name="s" value="{$subject.subject_id}">
<input type="submit" value="登録">
</form>

<form action="{$ExtensionBaseUrl}/" method="get" class="inline">
<input type="submit" value="戻る">
</form>

{else}
科目が存在しません
{/if}

