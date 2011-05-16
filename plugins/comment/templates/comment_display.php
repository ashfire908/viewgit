<?php
global $conf;
global $page;
$comment = $page['comment'];
?>
<div class="comment">
	<div class="header">
		<div class="cuser t<?php echo $comment->author->type; ?>">
			<em class="author"><?php echo $comment->author->name; ?></em> commented on commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>" class="commit"><?php echo substr($comment->commit, 0, 7); ?></a>
		</div>
		<div class="info">
			<span class="date"><?php echo $comment->posted->format('F j, Y g:i:s a'); ?></span> <span class="count">#<?php if ($comment->num != null) { echo $comment->num; } else { echo '?'; } ?></span>
		</div>
	</div>
<?php if ($page['comment_opt']['show_actions'] and ($_SESSION['loginid'] == $comment->author->id or auth_user_type() == 'admin')) { ?>
	<ul class="actions">
		<li><a href="<?php echo makelink(array('a' => 'comment', 'm' => 'edit', 'c' => $comment->id)); ?>" title="Edit Comment" class="comment_edit">[edit]</a></li>
		<li><a href="<?php echo makelink(array('a' => 'comment', 'm' => 'delete', 'c' => $comment->id)); ?>" title="Delete Comment" class="comment_delete">[delete]</a></li>
	</ul>
<?php } ?>
	<div class="body">
		<?php echo $comment->render;
    if ($comment->edit->count > 0) { ?>
		
		<p class="edit_msg">Last edited by <span class="t<?php echo $comment->edit->author->type; ?>"><?php echo $comment->edit->author->name; ?></span> on <?php echo $comment->edit->date->format('F j, Y g:i:s a'); ?>, edited <?php echo $comment->edit->count; ?> time<?php if ($comment->edit->count > 1) { ?>s<?php } ?> in total.</p><?php } ?>
	</div>
</div>
