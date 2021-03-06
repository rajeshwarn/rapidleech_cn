<?php
function zip() {
	global $list, $options, $PHP_SELF;
?>
<form name="ziplist" method="post" action="<?php echo $PHP_SELF; ?>"><input type="hidden" name="act" value="zip_go" />
	<table cellspacing="5">
		<tr>
			<td align="center"><strong>添加文件至ZIP压缩文件</strong></td>
		</tr>
		<tr>
			<td align="center">
				<table>
					<tr>
						<td>压缩文件名:&nbsp;<input type="text" name="archive" size="25" value=".zip" /></td>
					</tr>
					<tr>
						<td><input type="checkbox" name="no_compression"<?php echo ($options['disable_archive_compression'] ? ' disabled="disabled" checked="checked"' : ''); ?> />&nbsp;仅存储</td>
					</tr>
				</table>
				<table>
					<tr>
						<td><input type="submit" value="添加文件" /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php
	echo "<br />已选择的文件" . (count ( $_GET ["files"] ) > 1 ? "s" : "") . ": ";
	for($i = 0; $i < count ( $_GET ["files"] ); $i ++) {
		$file = $list [($_GET ["files"] [$i])];
		echo "<input type=\"hidden\" name=\"files[]\" value=\"{$_GET[files][$i]}\" />\r\n";
		echo "<b>" . basename ( $file ["name"] ) . "</b>";
		echo ($i == count ( $_GET ["files"] ) - 1) ? "." : ",&nbsp;";
	}
?>
<br /><br />
</form>
<?php
}

function zip_go() {
	global $list, $options;
	$saveTo = realpath ( $options['download_dir'] ) . '/';
	$_POST ["archive"] = (strlen ( trim ( urldecode ( $_POST ["archive"] ) ) ) > 4 && substr ( trim ( urldecode ( $_POST ["archive"] ) ), - 4 ) == ".zip") ? trim ( urldecode ( $_POST ["archive"] ) ) : "archive.zip";
	$_POST ["archive"] = $saveTo.basename($_POST ["archive"]);
	for($i = 0; $i < count ( $_POST ["files"] ); $i ++) {
		$files [] = $list [($_POST ["files"] [$i])];
	}
	foreach ( $files as $file ) {
		$CurrDir = ROOT_DIR;
		$inCurrDir = stristr ( dirname ( $file ["name"] ), $CurrDir ) ? TRUE : FALSE;
		if ($inCurrDir) {
			$add_files [] = substr ( $file ["name"], (strlen ( $CurrDir ) + 1) );
		}
	}
	require_once (CLASS_DIR . "pclzip.php");
	$archive = new PclZip ( $_POST ["archive"] );
	$no_compression = ($options['disable_archive_compression'] || isset($_POST["no_compression"]));
	if (file_exists ( $_POST ["archive"] )) {
		if ($no_compression) { $v_list = $archive->add ( $add_files, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_NO_COMPRESSION); }
		else { $v_list = $archive->add ( $add_files, PCLZIP_OPT_REMOVE_ALL_PATH); }
	} else {
		if ($no_compression) { $v_list = $archive->create ( $add_files, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_NO_COMPRESSION); }
		else { $v_list = $archive->create ( $add_files, PCLZIP_OPT_REMOVE_ALL_PATH); }
	}
	if ($v_list == 0) {
		echo "Error: " . $archive->errorInfo ( true ) . "<br /><br />";
		return;
	} else {
		echo "压缩文件<b>" . $_POST ["archive"] . "</b>创建成功！<br /><br />";
	}
	if (is_file($_POST['archive'])) {
		$time = filemtime($_POST['archive']); while (isset($list[$time])) { $time++; }
		$list[$time] = array("name" => $_POST['archive'], "size" => bytesToKbOrMbOrGb(filesize($_POST['archive'])), "date" => $time);
		if (!updateListInFile($list)) { echo lang(146)."<br /><br />"; }
	}
}
?>