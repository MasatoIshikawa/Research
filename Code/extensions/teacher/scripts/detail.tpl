<h3>教員情報</h3>

<table>
	<tr>
		<th>学科</th>
		<td>{$teacher.course_name}</td>
	</tr>
		<tr>
		<th>氏名</th>
		<td>{$teacher.teacher_name}</td>
	</tr>
{foreach from=$subjects item=subject name=subjects}
	<tr>
	{if $smarty.foreach.subjects.first}
		<th rowspan="{$smarty.foreach.subjects.total}">担当科目</th>
	{/if}
		<td><a href="{$SubjectDetailUrl}/s/{$subject.subject_id}/">
		[{$subject.subject_abbr}] {$subject.subject_name}</a></td>
	</tr>
{/foreach}


</table>
