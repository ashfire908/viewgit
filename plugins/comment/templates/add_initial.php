<?php 
global $page;
$comment = $page['comment'];
?>
<h1>Add comment for commit <?php echo substr($comment->commit, 0, 7); ?></h1>