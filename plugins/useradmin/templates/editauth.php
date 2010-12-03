<?php
global $page;
?><h1>Edit Authorized Projects</h1>

  <p>Editing authorized projects for user '<?php echo $page['userinfo']['name']; ?>' (ID <?php echo $page['userinfo']['id']; ?>).</p>
  
<?php if ($page['userinfo']['msg'] != '') {
    echo '<p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . $page['userinfo']['msg'] . "</p>\n\n";
} ?>
  <form class="user_settings" method="post" action="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $page['userinfo']['id'])); ?>">
    <fieldset>
<?php 
// Loop over projects and create checkboxes
foreach($page['authinfo'] as $project => $enabled) {
?>
        <label><input type="checkbox" name="<?php echo $project; ?>"<?php if ($enabled) { ?> checked="checked"<?php } ?>><?php echo $project; ?></label><br />
<?php } ?>

      <input type="submit" name="submit_auth" value="Save Authorized Projects" />
    </fieldset>
  </form>
  
  <p>
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist">Back to userlist</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $page['userinfo']['id'])); ?>" title="View user '<?php echo $page['userinfo']['name']; ?>'">View user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $page['userinfo']['id'])); ?>" title="Edit user '<?php echo $page['userinfo']['name']; ?>'">Edit user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['userinfo']['id'])); ?>" title="Delete user '<?php echo $page['userinfo']['name']; ?>'">Delete user</a>
  </p>