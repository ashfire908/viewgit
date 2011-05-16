<?php
global $page;
$comment = $page['comment'];
?>
<h1>Comment #<?php echo $comment->num; ?> for commit <?php echo substr($comment->commit, 0, 6); ?></h1>
