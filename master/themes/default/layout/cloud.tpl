<body {if INFO.SITEMODE='stand' 'class="sitemode_stand"' endif} {ifdef SESSION.user} onload="onloadPage();" {endif}>
<table id="nav-header" width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td class="logoline" width="1%">{/* <a href="{REF/ROOT}"><img src="{INFO.SKINDIR}/images/logo_red.png" alt="Index" title="Домой" /></a> */}</td>
		<td class="logoline" valign="middle">
			<table align="right" cellpadding="5" cellspacing="0" style="margin-right: 50px;" width="300">
				<tr>
					<td width="1%">{/* <img src="{INFO.SKINDIR}/images/logo-small.png" alt="" /> */}</td>
					<td><span class="contact_info"><b>ООО «Оргтехсервис»</b>{#ifdef TITLE}<br/>{TITLE}{#endif}</span></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="topmenu_t"></td>
	</tr>
	<tr>
		<td colspan="2" class="topmenu" nowrap="nowrap">
		{ifdef SESSION.user}
			<a href="{INFO.PREFIX}admin/"><img alt="*" src="{INFO.SKINDIR}/images/dot.gif" />Главная</a>
			<a href="{INFO.PREFIX}admin/logout.php"><img alt="*" src="{INFO.SKINDIR}/images/dot.gif" />Выход [ {SESSION.user.user_login} ]</a>
		{else}
			<a href="{INFO.PREFIX}admin/login.php"><img alt="*" src="{INFO.SKINDIR}/images/dot.gif" />Вход</a>
		{endif}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="topmenu_b"></td>
	</tr>
</table>

<table width="100%" cellpadding="8" cellspacing="0">
<tr>
{#include "/leftbar2"}
<td valign="top">{#include CONTENT}</td>{/* page_content */}
{#include "/rightbar2"}
</tr>
</table>
{#include "/copyright"}
{/* #include "ga" */}

<div id="alert" onclick="$('#alert').hide();" style="z-index: 999;">&nbsp;</div>
</body>
