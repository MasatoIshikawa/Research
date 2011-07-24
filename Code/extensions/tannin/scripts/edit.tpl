{if $tannin}

<h3>担任設定</h3>
<form action="{$ExtensionBaseUrl}/update/" method="post" class="inline">

<table>
<tr>
	<th></th>
	<th>現在の値</th>
	<th>変更</th>
</tr>
<tr>
<th>クラス</th>
<td>{$tannin.class_name}</td>
<td>{html_options name=tannin[class_id] options=$classes selected=$tannin.class_id}</td>
</tr>
<tr>
<th>教員</th>
<td>{$tannin.teacher_name}</td>
<td>{html_options name=tannin[teacher_id] options=$teachers selected=$tannin.teacher_id}</td>
</tr>
</table>

<input type="hidden" name="t" value="{$tannin.tannin_id}">
<input type="submit" value="登録">
</form>

<form action="{$ExtensionBaseUrl}/" method="get" class="inline">
<input type="submit" value="戻る">
</form>

{else}
担任設定が存在しません
{/if}
