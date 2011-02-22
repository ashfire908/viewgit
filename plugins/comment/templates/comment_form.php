<?php
global $page;
global $conf;

$comment = $page['comment'];
switch ($page['comment_mode']) {
    case 'add':
        $form_class = '';
        $form_action = makelink(array('a' => 'comment', 'm' => 'add', 'p' => $comment->project, 'h' => $comment->commit));
        $form_header = 'Add comment to commit';
        $submit_text = 'Add Comment';
        break;
    case 'add_inline':
        $form_class = ' comment_inline';
        $form_action = makelink(array('a' => 'comment', 'm' => 'add', 'p' => $comment->project, 'h' => $comment->commit));
        $form_header = 'Add comment to commit';
        $submit_text = 'Add Comment';
        break;
    case 'edit':
        $form_class = '';
        $form_action = makelink(array('a' => 'comment', 'm' => 'edit', 'c' => $comment->id));
        $form_header = 'Edit comment #' . $comment->num . ' on commit';
        $submit_text = 'Edit Comment';
        break;
    case 'delete':
        $form_class = '_delete';
        $form_action = makelink(array('a' => 'comment', 'm' => 'delete', 'c' => $comment->id));
        $submit_text = 'Delete Comment';
        break;
}
$comment_id = $comment->id;
$comment_num = $comment->num;
$commit = $comment->commit;
$commit_link = makelink(array('a' => 'commit', 'p' => $comment->project, 'h' => $comment->commit));
$text = $comment->text;
?>
<form class="comment<?php echo $form_class; ?>" method="post" action="<?php echo $form_action; ?>">
<?php
switch ($page['comment_mode']) {
    case 'add':
    case 'add_inline':
    case 'edit':?>
	<div class="header">
		<p><?php echo $form_header; ?> <a href="<?php echo $commit_link; ?>"><?php echo $commit; ?></a></p>
	</div>
	<fieldset>
		<textarea name="comment_text"><?php echo $text; ?></textarea>
		<p class="comment_note">Comments are formatted using <a href="http://daringfireball.net/projects/markdown/">Markdown</a></p>

		<div class="comment_submit"><input type="submit" name="comment_preview" value="Preview" /> - <input type="submit" name="comment_submit" value="<?php echo $submit_text; ?>" /></div>
	</fieldset><?php
        break;
    case 'delete':?>
		<p>Do you really want to delete comment #<?php echo $comment_num; ?> for commit <a href="<?php echo $commit_link; ?>"><?php echo $commit; ?></a>?</p>
		
		<p><input type="submit" name="comment_delete" value="<?php echo $submit_text; ?>" /> - <input type="button" onclick="history.back()" value="Cancel" /></p><?php 
        break;
} ?>
</form>
