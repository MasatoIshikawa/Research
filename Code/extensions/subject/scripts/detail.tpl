<h3>科目情報</h3>

<table>
	<tr>
		<th>科目名</th>
		<td>{$subject.subject_name}</td>
	</tr>
	<tr>
		<th>学期</th>
		<td>{$columns.subject_semester.options[$subject.subject_semester]}</td>
	</tr>
	<tr>
		<th>単位数</th>
		<td>{$subject.subject_credit}単位</td>
	</tr>
	

{foreach from=$teachers item=teacher name=teachers}
	<tr>
	{if $smarty.foreach.teachers.first}
		<th rowspan="{$smarty.foreach.teachers.total}">担当教員</th>
	{/if}
		<td><a href="{$TeacherDetailUrl}/t/{$teacher.teacher_id}/">
		{$teacher.teacher_name}</a></td>
	</tr>
{/foreach}

{foreach from=$classes item=class name=classes}
	<tr>
	{if $smarty.foreach.classes.first}
		<th rowspan="{$smarty.foreach.classes.total}">開講クラス</th>
	{/if}
		<td><a href={$ClassDetailUrl}/c/{$class.class_id}/">
		{$class.class_name}</a></td>
	</tr>
{/foreach}

{foreach from=$entriedYears item=entriedYear name=entriedYears}
	<tr>
	{if $smarty.foreach.entriedYears.first}
		<th rowspan="{$smarty.foreach.entriedYears.total}">授業評価</th>
	{/if}
		<td><a href="{$JugyoDisplayUrl}/y/{$entriedYear.year_id}/s/{$subject.subject_id}/c/{$class.class_id}/">
		{$entriedYear.year_name}</a></td>
	</tr>
{/foreach}

</table>
