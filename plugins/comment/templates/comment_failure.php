<?php
global $page;
global $conf;

$comment = $page['comment'];
switch ($page['comment_mode']) {
    case 'add': ?>
<h1>Failed to add comment</h1>

<p>Error: Failed to add comment for commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>"><?php echo substr($comment->commit, 0, 7); ?></a>.</p>

<p><a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">Return to commit <?php echo substr($comment->commit, 0, 7); ?></a>
<?php   break;
    case 'edit': ?>
<h1>Failed to edit comment</h1>

<p>Error: Failed to edit comment #<?php echo $comment->num; ?> for commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>"><?php echo substr($comment->commit, 0, 7); ?></a>.</p>

<p><a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">Return to commit <?php echo substr($comment->commit, 0, 7); ?></a>
<?php   break;
    case 'delete': ?>
<h1>Failed to delete comment</h1>

<p>Error: Failed to delete comment #<?php echo $comment->num; ?> for commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>"><?php echo substr($comment->commit, 0, 7); ?></a>.</p>

<p><a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">Return to commit <?php echo substr($comment->commit, 0, 7); ?></a>
<?php   break;
}
