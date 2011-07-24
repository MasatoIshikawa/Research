<h3>教員一覧</h3>

<p><a href="{$ExtensionBaseUrl}/">再表示</a></p>

<table class="hover-on">
<tr>
	<th></th>
	<th></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/teacher_id/order/{pick var=$params.order v1=asc v2=desc}/">教員ID</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/teacher_name/order/{pick var=$params.order v1=asc v2=desc}/">氏名</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/course_id/order/{pick var=$params.order v1=asc v2=desc}/">学科</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/teacher_parttime/order/{pick var=$params.order v1=asc v2=desc}/">非常勤</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/teacher_available/order/{pick var=$params.order v1=asc v2=desc}/">退職</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/teacher_note/order/{pick var=$params.order v1=asc v2=desc}/">特記事項</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/teacher_updatetime/order/{pick var=$params.order v1=asc v2=desc}/">更新日時</a></th>
</tr>
{foreach from=$teachers item=teacher}
<tr class="{cycle values=',even'}">
	<td><a href="{$ExtensionBaseUrl}/edit/t/{$teacher.teacher_id}/"><img src="{$ImgUrl}/img/b_edit.png" alt="編集" /></a></td>
	<td>
		<a href="{$ExtensionBaseUrl}/delete/t/{$teacher.teacher_id}/" onclick="return confirm('id: {$teacher.teacher_id}\nを本当に削除してよろしいですか？')">
			<img src="{$ImgUrl}/img/b_drop.png" alt="削除" />
		</a>
	</td>
	<td>{$teacher.teacher_id}</td>
	<td>{$teacher.teacher_name}</td>
	<td>{$teacher.course_name}</td>
	<td class="center">{if $teacher.teacher_parttime == 1}○{/if}</td>
	<td class="center">{if $teacher.teacher_available == 0}○{/if}</td>
	<td>{$teacher.teacher_note}</td>
	<td>{$teacher.teacher_updatetime}</td>
</tr>
{/foreach}
</table>


<h4><a name="add"></a>新規登録</h4>

<form action="{$ExtensionBaseUrl}/add/" method="post">

{kyomu_add_table columns=$columns name=teacher}

<input type="submit" value="登録">
</form>
