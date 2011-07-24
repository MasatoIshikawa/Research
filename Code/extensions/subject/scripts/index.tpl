<h3>科目一覧</h3>

<p><a href="{$ExtensionBaseUrl}/">再表示</a></p>

<table class="hover-on">
<tr>
	<th></th>
	<th></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_id/order/{pick var=$params.order v1=asc v2=desc}/">科目</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_abbr/order/{pick var=$params.order v1=asc v2=desc}/">クラス</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_name/order/{pick var=$params.order v1=asc v2=desc}/">科目名</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_grade/order/{pick var=$params.order v1=asc v2=desc}/">学年</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_class/order/{pick var=$params.order v1=asc v2=desc}/">学科</a></th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_credit/order/{pick var=$params.order v1=asc v2=desc}/"></a>単位</th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_genspe/order/{pick var=$params.order v1=asc v2=desc}/"></a>一般/専門</th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_inout/order/{pick var=$params.order v1=asc v2=desc}/"></a>学内/学外</th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_teacher/order/{pick var=$params.order v1=asc v2=desc}/"></a>担当教員（表示用）</th>
	<th><a href="{$ExtensionBaseUrl}/{$action}/sort/subject_updatetime/order/{pick var=$params.order v1=asc v2=desc}/"></a>更新日時</th>
</tr>
{foreach from=$subjects item=subject}
<tr class="{cycle values=',even'}">
	<td><a href="{$ExtensionBaseUrl}/edit/s/{$subject.subject_id}/"><img src="{$ImgUrl}/img/b_edit.png" alt="編集" /></a></td>
	<td>
		<a href="{$ExtensionBaseUrl}/delete/s/{$subject.subject_id}/" onclick="return confirm('id: {$subject.subject_id}\nを本当に削除してよろしいですか？')">
			<img src="{$ImgUrl}/img/b_drop.png" alt="削除" />
		</a>
	</td>
	<td>{$subject.subject_id}</td>
	<td>{$subject.subject_abbr}</td>
	<td>{$subject.subject_name}</td>
	<td>{$subject.subject_grade}</td>
	<td>{$subject.subject_class}</td>
	<td>{$subject.subject_credit}</td>
	<td>{$columns.subject_genspe.options[$subject.subject_genspe]}</td>
	<td>{$columns.subject_inout.options[$subject.subject_inout]}</td>
	<td>{$subject.subject_teacher}</td>
	<td>{$subject.subject_updatetime}</td>
</tr>
{/foreach}
</table>


<h4><a name="add"></a>新規登録</h4>

<form action="{$ExtensionBaseUrl}/add/" method="post">

{kyomu_add_table columns=$columns name=subject}

<input type="submit" value="登録">
</form>

<!--h4>教員名自動生成</h4>

<form action="{$ExtensionBaseUrl}/teacherName/" method="post">
<input type="submit" value="実行" onclick="return confirm('昨年度と今年度の担当情報を比較し\n変化のある科目について表示用教員名を自動生成します');">
</form-->
