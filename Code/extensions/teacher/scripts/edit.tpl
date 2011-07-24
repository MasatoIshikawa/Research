{if $teacher}

<h3>教員設定</h3>
<form action="{$ExtensionBaseUrl}/update/" method="post" class="inline">

{kyomu_edit_table columns=$columns source=$teacher name=teacher}

<input type="hidden" name="t" value="{$teacher.teacher_id}">
<input type="submit" value="登録">
</form>

<form action="{$ExtensionBaseUrl}/" method="get" class="inline">
<input type="submit" value="戻る">
</form>

{else}
教員が存在しません
{/if}
