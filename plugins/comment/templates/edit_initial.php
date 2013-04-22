<?php
global $page;
$comment = $page['comment'];
?>
<h1>Edit comment #<?php echo $comment->num; ?> for commit <?php echo substr($comment->commit, 0, 7); ?></h1>