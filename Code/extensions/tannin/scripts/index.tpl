<h3>担任一覧</h3>


<table class="hover-on">
<tr>
	<th></th>
	<th></th>
	<th>クラス</th>
	<th>教員</th>
</tr>
{foreach from=$tannins item=tannin}
<tr class="{cycle values=',even'}">
	<td><a href="{$ExtensionBaseUrl}/edit/t/{$tannin.tannin_id}/"><img src="{$ImgUrl}/img/b_edit.png" alt="編集" /></a></td>
	<td>
		<a href="{$ExtensionBaseUrl}/delete/t/{$tannin.tannin_id}/" onclick="return confirm('{$tannin.class_name}\nを本当に削除してよろしいですか？')">
			<img src="{$ImgUrl}/img/b_drop.png" alt="削除" />
		</a>
	</td>
	<td>{$tannin.class_name}</td>
	<td>{$tannin.teacher_name}</td>
</tr>
{/foreach}
</table>



<h4>新規登録</h4>

<form action="{$ExtensionBaseUrl}/add/" method="post">

<table>
<tr>
<th></th>
<th>値</th>
</tr>
<tr>
<th>クラス</th>
<td>{html_options name=tannin[class_id] options=$classes}</td>
</tr>
<tr>
<th>教員</th>
<td>{html_options name=tannin[teacher_id] options=$teachers}</td>
</tr>
</table>

<input type="hidden" name="tannin[year_id]" value="{$year.year_id}">
<input type="submit" value="登録">
</form>

