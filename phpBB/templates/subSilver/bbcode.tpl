// bbcode.tpl -- bbcode replacement templates.

<!-- BEGIN ulist_open -->
<ul>
<!-- END ulist_open -->
<!-- BEGIN ulist_close -->
</ul>
<!-- END ulist_close -->


<!-- BEGIN olist_open -->
<ol type="{LIST_TYPE}">
<!-- END olist_open -->
<!-- BEGIN olist_close -->
</ol>
<!-- END olist_close -->

<!-- BEGIN listitem -->
<li>
<!-- END listitem -->
	
<!-- BEGIN quote_open -->
<table border="0" align="center" width="85%">
	<tr>
		<td>
			<font size="-1">{L_QUOTE}</font>
			<hr /> 
		</td>
	</tr>
	<tr>
		<td>
			<font size="-1">
				<blockquote>
<!-- END quote_open -->
<!-- BEGIN quote_close -->
				</blockquote>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<hr />
		</td>
	</tr>
</table>
<!-- END quote_close -->

<!-- BEGIN code_open -->
<table width="85%" border="0" align="center">
	<tr>
		<td>
			<font size="-1">{L_CODE}</font>
			<hr />
		</td>
	</tr>
	<tr>
		<td>
			<font size="-1">
				<pre>
<!-- END code_open -->				
<!-- BEGIN code_close -->
				</pre>
			</font>
		</td>
	</tr>
	<tr>
		<td>
			<hr />
		</td>
	</tr>
</table>
<!-- END code_close -->

<!-- BEGIN b_open -->
<b>
<!-- END b_open -->
<!-- BEGIN b_close -->
</b>
<!-- END b_close -->

<!-- BEGIN u_open -->
<u>
<!-- END u_open -->
<!-- BEGIN u_close -->
</u>
<!-- END u_close -->

<!-- BEGIN i_open -->
<i>
<!-- END i_open -->
<!-- BEGIN i_close -->
</i>
<!-- END i_close -->

<!-- BEGIN color_open -->
<font color="{COLOR}">
<!-- END color_open -->
<!-- BEGIN color_close -->
</font>
<!-- END color_close -->

<!-- BEGIN size_open -->
<font size="{SIZE}">
<!-- END size_open -->
<!-- BEGIN size_close -->
</font>
<!-- END size_close -->

<!-- BEGIN img -->
<img src="{URL}" border="0"></img>
<!-- END img -->

<!-- BEGIN url -->
<a href="{URL}" target="_blank">{DESCRIPTION}</a>
<!-- END url -->

<!-- BEGIN email -->
<a href="mailto:{EMAIL}">{EMAIL}</A>
<!-- END email -->
