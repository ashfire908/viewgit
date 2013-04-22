<?php
global $page;
$comment = $page['comment'];
?>

<p><a href="<?php echo makelink(array('a' => 'comment', 'm' => 'edit', 'c' => $comment->id)); ?>">Edit comment</a> - <a href="<?php echo makelink(array('a' => 'comment', 'm' => 'delete', 'c' => $comment->id)); ?>">Delete comment</a> - <a href="<?php echo makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit)); ?>">View commit <?php echo substr($comment->commit, 0, 7); ?></a></p>