<?php
global $page;
global $conf;

$comment = $page['comment'];
switch ($page['comment_mode']) {
    case 'add': ?>
<h1>Added comment</h1>

<p>Added comment for commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>"><?php echo substr($comment->commit, 0, 7); ?></a>.</p>

<p><a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">Return to commit <?php echo substr($comment->commit, 0, 7); ?></a>
<?php   break;
    case 'edit': ?>
<h1>Edited comment</h1>

<p>Edited comment #<?php echo $comment->num; ?> for commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>"><?php echo substr($comment->commit, 0, 7); ?></a>.</p>

<p><a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">Return to commit <?php echo substr($comment->commit, 0, 7); ?></a>
<?php   break;
    case 'delete': ?>
<h1>Deleted comment</h1>

<p>Deleted comment #<?php echo $comment->num; ?> for commit <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>"><?php echo substr($comment->commit, 0, 7); ?></a>.</p>

<p><a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">Return to commit <?php echo substr($comment->commit, 0, 7); ?></a>
<?php   break;
}
