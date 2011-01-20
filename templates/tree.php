<table class="tree">
<thead>
<tr>
	<th class="perm">Permissions</th>
	<th class="name">Name</th>
	<th class="actions">Actions</th>
	<th class="dl">Download</th>
</tr>
</thead>
<tbody>
<?php
foreach ($page['entries'] as $e) {
	if (strlen($page['path']) > 0) {
		$path = $page['path'] .'/'. $e['name'];
	} else {
		$path = $e['name'];
	}
	
	// Create data object
	$item = array('mode' => $e['mode'], 'type' => $e['type'],
	              'name' => $e['name'], 'path' => $path, 'hash' => $e['hash'],
	              'actions' => array(), 'download' => array());
	
	// Call tree item hooks (before we apply the defaults)
	VGPlugin::call_hooks('treeitem_pre', $item);
	
	// Apply defaults
	if ($item['type'] === 'blob') {
		$item['download'][] = "<a href=\"". makelink(array('a' => 'blob', 'p' => $page['project'], 'h' => $item['hash'], 'n' => $item['name'])) ."\">blob</a>";
	} else {
		$item['download'][] = "<a href=\"". makelink(array('a' => 'archive', 'p' => $page['project'], 'h' => $item['hash'], 'hb' => $page['commit_id'], 't' => 'targz', 'n' => $item['name'])) ."\" class=\"tar_link\" title=\"tar/gz\">tar.gz</a>";
		$item['download'][] = "<a href=\"". makelink(array('a' => 'archive', 'p' => $page['project'], 'h' => $item['hash'], 'hb' => $page['commit_id'], 't' => 'zip', 'n' => $item['name'])) ."\" class=\"zip_link\" title=\"zip\">zip</a>";
	}
	
	// Call tree item hooks (after we applied the defaults)
	VGPlugin::call_hooks('treeitem_post', $item);
	
	// Display item
	$tr_class = $tr_class=="odd" ? "even" : "odd";
	
	if ($item['type'] === 'blob') {
		echo "<tr class=\"blob $tr_class\">\n";
		echo "\t<td>$item[mode]</td>\n";
		echo "\t<td><a href=\"". makelink(array('a' => 'viewblob', 'p' => $page['project'], 'h' => $item['hash'], 'hb' => $page['commit_id'], 'f' => $item['path'])) ."\" class=\"item_name\">". htmlspecialchars($item['name']) ."</a></td>\n";
		echo "\t<td>" . implode(' ', $item['actions']) . "</td>\n";
		echo "\t<td>" . implode(' ', $item['download']) . "</td>\n";
	} else {
		echo "<tr class=\"dir $tr_class\">\n";
		echo "\t<td>$item[mode]</td>\n";
		echo "\t<td><a href=\"" .makelink(array('a' => 'tree', 'p' => $page['project'], 'h' => $item['hash'], 'hb' => $page['commit_id'], 'f' => $item['path'])) ."\" class=\"item_name\">". htmlspecialchars($item['name']) ."/</a></td>\n";
		echo "\t<td>" . implode(' ', $item['actions']) . "</td>\n";
		echo "\t<td>" . implode(' ', $item['download']) . "</td>\n";
	}
	echo "</tr>\n";
}
?>
</tbody>
</table>

<p>Download as <a href="<?php echo makelink(array('a' => 'archive', 'p' => $page['project'], 'h' => $page['tree_id'], 'hb' => $page['commit_id'], 't' => 'targz')) ?>" rel="nofollow">tar.gz</a> or <a href="<?php echo makelink(array('a' => 'archive', 'p' => $page['project'], 'h' => $page['tree_id'], 'hb' => $page['commit_id'], 't' => 'zip')) ?>" rel="nofollow">zip</a>. Browse this tree at the <a href="<?php echo makelink(array('a' => 'tree', 'p' => $page['project'], 'hb' => 'HEAD', 'f' => $page['path'])); ?>">HEAD</a>.</p>

