<?php 
global $conf;
global $page;
?><h2>Rendered document for <?php echo $page['path']; ?></h2>
<div class="file<?php if (isset($page['html_data'])) { echo " rendered"; } ?>">
<?php
if (isset($page['html_data'])) {
	echo $page['html_data'];
}
else {
?>
<pre>
<?php echo htmlspecialchars($page['data']); ?>
</pre>
<?php
}
?></div>

<h2>Commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $page['project'], 'h' => $page['lastlog']['h'])); ?>"><?php echo htmlentities_wrapper($page['lastlog']['h']); ?></a> by <?php
echo format_author($page['lastlog']['author_name']);
echo ' ['. $page['lastlog']['author_datetime'] .']';
?></h2>
<div class="commitmessage">
<pre>
<?php echo htmlentities_wrapper($page['lastlog']['message_full']); ?>
</pre>
</div>
