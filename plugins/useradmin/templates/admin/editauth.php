<?php
global $page;

if ($page['useradmin']['success']) {
    $page['useradmin']['messages'][] = "Authorized projects for '" . $page['user']->name . "' updated.";
}
?><h1>Edit Authorized Projects</h1>

  <p>Editing authorized projects for user '<?php echo $page['user']->name; ?>' (ID <?php echo $page['user']->id; ?>).</p>
  
<?php
if (count($page['useradmin']['errors']) > 0) {
    echo '  <p style="border: 1px solid red; padding: 2px; background: #f77;">' . implode("<br />\n", $page['useradmin']['errors']) . "</p>\n\n";
}
if (count($page['useradmin']['messages']) > 0) {
    echo '  <p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . implode("<br />\n", $page['useradmin']['messages']) . "</p>\n\n";
}
?>
  <form class="user_settings" method="post" action="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $page['user']->id)); ?>">
    <fieldset>
<?php 
// Loop over projects and create checkboxes
foreach($page['useradmin']['auth'] as $project => $enabled) {
?>
        <label><input type="checkbox" name="<?php echo str_replace('_', ' ', $project); ?>"<?php if ($enabled) { ?> checked="checked"<?php } ?>><?php echo $project; ?></label><br />
<?php } ?>

      <input type="submit" name="submit_auth" value="Save Authorized Projects" />
    </fieldset>
  </form>
  
  <p>
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist" class="userlist">Back to userlist</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $page['user']->id)); ?>" title="View user '<?php echo $page['user']->name; ?>'" class="user_view">View user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $page['user']->id)); ?>" title="Edit user '<?php echo $page['user']->name; ?>'" class="user_edit">Edit user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['user']->id)); ?>" title="Delete user '<?php echo $page['user']->name; ?>'" class="user_delete">Delete user</a>
  </p>