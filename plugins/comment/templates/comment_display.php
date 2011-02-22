<?php
global $conf;
global $page;
$comment = $page['comment'];
?>
<div class="comment">
	<div class="header">
		<div class="cuser t<?php echo $comment->user->type; ?>">
			<em class="author"><?php echo $comment->user->name; ?></em> commented on commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>" class="commit"><?php echo substr($comment->commit, 0, 7); ?></a>
		</div>
		<div class="info">
			<span class="date"><?php echo $comment->posted->format('F j, Y g:i:s a'); ?></span> <span class="count">#<?php if ($comment->num != null) { echo $comment->num; } else { echo '?'; } ?></span>
		</div>
	</div>
<?php if ($page['comment_opt']['show_actions'] and ($_SESSION['loginid'] == $comment->user->id or auth_user_type() == 'admin')) { ?>
	<ul class="actions">
		<li><a href="<?php echo makelink(array('a' => 'comment', 'm' => 'edit', 'c' => $comment->id)); ?>" title="Edit Comment" class="comment_edit">[edit]</a></li>
		<li><a href="<?php echo makelink(array('a' => 'comment', 'm' => 'delete', 'c' => $comment->id)); ?>" title="Delete Comment" class="comment_delete">[delete]</a></li>
	</ul>
<?php } ?>
	<div class="body">
		<?php echo $comment->render;
    if ($comment->edited > $comment->posted) { ?>
		
		<p class="last_edit">Last edited: <?php echo $comment->edited->format('F j, Y g:i:s a'); ?></p><?php } ?>
	</div>
</div>
