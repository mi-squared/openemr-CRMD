<?php /* Smarty version 2.6.2, created on 2015-02-16 06:45:08
         compiled from /mnt/datadrive/opt/www/vhosts/carolritter.mi-squared.com/emr_carolritter_42/openemr/interface/forms/gyn_physical/templates/general_new.html */ ?>
<!-- GYN_PHYSICAL FORM -->
<html>
    <title>GYN Physical</title>
    <head>
<?php html_header_show();  echo '

 <style type="text/css" title="mystyles" media="all">
<!--
td {
	font-size:12pt;
	font-family:helvetica;
}
li{
	font-size:11pt;
	font-family:helvetica;
	margin-left: 15px;
}
a {
	font-size:11pt;
	font-family:helvetica;
}
.title {
	font-family: sans-serif;
	font-size: 12pt;
	font-weight: bold;
	text-decoration: none;
	color: #000000;
}

.form_text{
	font-family: sans-serif;
	font-size: 9pt;
	text-decoration: none;
	color: #000000;
}

-->
</style>
'; ?>

</head>
<body bgcolor="<?php echo $this->_tpl_vars['STYLE']['BGCOLOR2']; ?>
">
<p><span class="title">GYN Physical</span></p>
<form name="gyn_physical" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
/interface/forms/gyn_physical/save.php"
 onsubmit="return top.restoreSession()">

<TABLE WIDTH=591 BORDER=1 CELLPADDING=7 CELLSPACING=0 STYLE='page-break-before: always'>

	<TR>
		<TD WIDTH=100px>
			<span class='bold'>WNL/ABN</span>
		</TD>
		<TD WIDTH=162px>
			<span class='bold'>SYSTEM</span>
        </TD>
		<TD WIDTH=161px>
			<span class='bold'>SPECIFIC</span>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<span class='bold'>Text Box</span>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_1' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_1_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_1' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_1_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_1' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_1_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'>Constitutional</span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>General Appearance (eg, development, nutrition, body habitus, deformities, attention to grooming)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_1_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_1_textbox(); ?>
</textarea>
			</P>
		</TD>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_12' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_12_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_12' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_12_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_12' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_12_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'>Thyroid</span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination of thyroid (eg, enlargement, tenderness, mass)</span></P>
		</TD>
        <TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_12_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_12_textbox(); ?>
</textarea>
			</P>
		</TD>
	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_24' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_24_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_24' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_24_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_24' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_24_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'>Chest (Breasts)</span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Inspection of breasts (eg, symmetry, nipple discharge)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_24_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_24_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_25' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_25_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_25' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_25_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_25' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_25_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Palpation of breasts and axillae (eg, masses or lumps, tenderness)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_25_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_25_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_26' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_26_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_26' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_26_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_26' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_26_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'>Gastrointestinal (Abdomen)</span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination of abdomen with notation of presence of masses or tenderness</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_26_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_26_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_27' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_27_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_27' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_27_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_27' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_27_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination of liver and spleen</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_27_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_27_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_28' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_28_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_28' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_28_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_28' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_28_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination for presence or absence of hernia</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_28_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_28_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_29' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_29_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_29' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_29_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_29' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_29_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination (when indicated) of anus, perineum and rectum, including sphincter tone, presence of hemorrhoids, rectal masses</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_29_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_29_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_30' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_30_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_30' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_30_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_30' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_30_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Obtain stool sample for occult blood test when indicated</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_30_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_30_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_34' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_34_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_34' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_34_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_34' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_34_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'>Female Genitourinary </span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Pelvic examination (with or without specimen collection for smears and cultures), including</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_34_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_34_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_35' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_35_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_35' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_35_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_35' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_35_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination of external genitalia (eg, general appearance, hair distribution, lesions) and vagina (eg, general appearance, estrogen effect, discharge, lesions, pelvic support, cystocele, rectocele)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_35_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_35_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_36' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_36_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_36' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_36_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_36' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_36_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination of urethra (eg, masses, tenderness, scarring)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_36_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_36_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_37' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_37_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_37' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_37_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_37' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_37_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Examination of bladder (eg, fullness, masses, tenderness)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_37_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_37_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_38' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_38_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_38' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_38_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_38' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_38_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Cervix (eg, general appearance, lesions, discharge)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_38_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_38_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_39' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_39_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_39' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_39_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_39' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_39_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Uterus (eg, size, contour, position, mobility, tenderness, consistency, descent or support)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_39_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_39_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>

	<TR>
		<TD WIDTH=100px>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td><INPUT TYPE=radio NAME='fld_40' VALUE='WNL' '<?php echo $this->_tpl_vars['data']->get_fld_40_wnl(); ?>
'></td><td><span class='text'>WNL</span></td>
			<td><INPUT TYPE=radio NAME='fld_40' VALUE='ABN'  '<?php echo $this->_tpl_vars['data']->get_fld_40_abn(); ?>
'></td><td><span class='text'>ABN</span></td>
			<td><INPUT TYPE=radio NAME='fld_40' VALUE='N/A' '<?php echo $this->_tpl_vars['data']->get_fld_40_na(); ?>
'></td><td><span class='text'>N/A</span></td>
			</tr>
		</table>
		</TD>
		<TD WIDTH=162px>
			<P ALIGN=CENTER><span class='text'><BR/></span>
			</P>
		</TD>
		<TD WIDTH=161px>
			<P><span class='text'>Adnexa/parametria (eg, masses, tenderness, organomegaly, nodularity)</span></P>
		</TD>
		<TD WIDTH=162px VALIGN=TOP>
			<P><textarea name='fld_40_textbox' cols='40' rows='4'><?php echo $this->_tpl_vars['data']->get_fld_40_textbox(); ?>
</textarea>
			</P>
		</TD>
	</TR>
</TABLE>

<table border=0 cellpadding=0 cellspacing=20px>
	<tr>
		<td><input type="submit" name="Submit" value="Save Form"></td>
		<td><a href="<?php echo $this->_tpl_vars['DONT_SAVE_LINK']; ?>
" class="link">[Don't Save]</a></td>
	</tr>
</table>

<input type="hidden" name="id" value="<?php echo $this->_tpl_vars['data']->get_id(); ?>
" />
<input type="hidden" name="activity" value="<?php echo $this->_tpl_vars['data']->get_activity(); ?>
">
<input type="hidden" name="pid" value="<?php echo $this->_tpl_vars['data']->get_pid(); ?>
">
<input type="hidden" name="process" value="true">
</form>
</body>
</html>