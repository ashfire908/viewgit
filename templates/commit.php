<h1><?php echo htmlentities_wrapper($page['message_firstline']); ?></h1>

<table class="commit">
<tbody>
<tr>
	<td>Author</td>
	<td><?php echo format_author($page['author_name']); ?> &lt;<?php echo htmlentities_wrapper($page['author_mail']); ?>&gt;</td>
</tr>
<tr>
	<td>Author date</td>
	<td><?php echo $page['author_datetime']; ?></td>
</tr>
<tr>
	<td>Author local date</td>
	<td><?php echo $page['author_datetime_local']; ?></td>
</tr>
<tr>
	<td>Committer</td>
	<td><?php echo format_author($page['committer_name']); ?> &lt;<?php echo htmlentities_wrapper($page['committer_mail']); ?>&gt;</td>
</tr>
<tr>
	<td>Committer date</td>
	<td><?php echo $page['committer_datetime']; ?></td>
</tr>
<tr>
	<td>Committer local date</td>
	<td><?php echo $page['committer_datetime_local']; ?></td>
</tr>
<tr>
	<td>Commit</td>
	<td><?php echo $page['commit_id']; ?></td>
</tr>
<tr>
	<td>Tree</td>
	<td><a href="<?php echo makelink(array('a' => 'tree', 'p' => $page['project'], 'h' => $page['tree_id'], 'hb' => $page['commit_id'])); ?>"><?php echo $page['tree_id']; ?></a></td>
</tr>
<?php
foreach ($page['parents'] as $parent) {
	echo "<tr>\n";
	echo "\t<td>Parent</td>\n";
	echo "\t<td><a href=\"". makelink(array('a' => 'commit', 'p' => $page['project'], 'h' => $parent)) ."\">$parent</a></td>\n";
	echo "</tr>\n";
}
?>
</tbody>
</table>

<div class="commitmessage"><pre><?php echo htmlentities_wrapper($page['message_full']); ?></pre></div>

<div class="filelist">
<table>
<thead>
<tr>
	<th>Affected files:</th>
	<th class="actions">Actions:</th>
</tr>
</thead>
<tbody>

<?php
foreach ($page['affected_files'] as $details) {
	$details = array_merge($details, array('link_text' => false,
	                                       'actions' => array()));
	
	// Call commit file hooks (before we apply the defaults)
	VGPlugin::call_hooks('commitfile_pre', $details);
	
	// Apply Defaults
	// Determine if the filename text should be linked
	switch ($details['status']) {
	    case 'A': // Addition
	    case 'C': // Copy
	    case 'M': // Modification
	    case 'R': // Renamed
	    case 'T': // Type Change
	        $details['link_text'] = true;
	        break;
	    case 'D': // Delete
	    case 'U': // Unmerged
	    case 'X': // Unknown
	    default:
	        $details['link_text'] = false;
	        break;
	}
	
	// Call commit file hooks (after we applied the defaults)
	VGPlugin::call_hooks('commitfile_post', $details);
	
	// Display file
	// Determine commit icon class
    if (isset($conf['commit_icons']) and $conf['commit_icons']) {
        switch ($details['status']) {
            case 'A': // Addition
                $icon_class = 'commit_add';
                break;
            case 'C': // Copy
                $icon_class = 'commit_copy';
                break;
            case 'D': // Delete
                $icon_class = 'commit_delete';
                break;
            case 'M': // Modification
                $icon_class = 'commit_edit';
                break;
            case 'R': // Renamed
                $icon_class = 'commit_rename';
                break;
            case 'T': // Type Change
                $icon_class = 'commit_type';
                break;
            case 'U': // Unmerged
                $icon_class = 'commit_unmerge';
                break;
            case 'X': // Unknown
            default:
                $icon_class = 'commit_unknown';
                break;
	    }
    }
    
    // Set link text
    $text = $details['file1'];
    if ($details['status'] == 'C' or $details['status'] == 'R') {
        $text .= " <span class=\"commit_file2\">$details[file2]</span> ($details[score]%)";
    }
    
    // Display item
    echo "<tr>\n<td>";
    if ($details['link_text']) {
        // Link the text
        echo '<a href="' . makelink(array('a' => 'viewblob', 'p' => $page['project'], 'h' => $details['hash'], 'hb' => $page['commit_id'], 'f' => $details['file1'])) . '"';
        if (isset($conf['commit_icons']) and $conf['commit_icons']) {
            echo " class=\"$icon_class\"";
        }
        echo ">$text</a>";
    } else {
        // Don't link the text
        echo '<span';
        if (isset($conf['commit_icons']) and $conf['commit_icons']) {
            echo " class=\"$icon_class\"";
        }
        echo ">$text</span>";
    }
    echo "</td>\n<td>";
    echo implode(' ', $details['actions']);
    echo "</td>\n</tr>";
}
?>

</tbody>
</table>
</div>

<?php
// call plugins that register "commit" hook
if (in_array('commit', array_keys(VGPlugin::$plugin_hooks))) {
	VGPlugin::call_hooks('commit');
}
?>
