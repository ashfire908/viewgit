<?php
global $page;
?><h1>Add User</h1>

<?php
if ($page['userinfo']['success'] == true) { ?>
  <p>User '<?php echo $page['userinfo']['name']; ?>' has successfully been created.</p>
  
  <p>
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist">Back to userlist</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $page['userinfo']['id'])); ?>" title="View user '<?php echo $page['userinfo']['name']; ?>'">View user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $page['userinfo']['id'])); ?>" title="Edit user '<?php echo $page['userinfo']['name']; ?>'">Edit user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $page['userinfo']['id'])); ?>" title="Edit user '<?php echo $page['userinfo']['name']; ?>' authorized projects">Edit authorized projects</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['userinfo']['id'])); ?>" title="Delete user '<?php echo $page['userinfo']['name']; ?>'">Delete user</a>
  </p>
<?php } else {
    if ($page['userinfo']['error'] != '') {
        ?><p style="border: 1px solid red; padding: 2px; background: #f77;"><?php echo $page['userinfo']['error']; ?></p>

<?php } ?>
  <form class="user_settings" method="post" action="<?php echo makelink(array('a' => 'admin', 'm' => 'adduser')); ?>">
    <fieldset class="two_column">
      <label for="name">User Name:</label>
        <input type="text" name="name" /><br />
      <label for="password">Password:</label>
        <input type="password" name="password" /><br />
      <label for="password_confirm">Confirm:</label>
        <input type="password" name="password_confirm" /><br />
      <label for="type">User Type:</label>
        <select name="type">
          <option value="standard">Standard</option>
          <option value="admin">Admin</option>
        </select><br />
      <label for="status">Status:</label>
        <select name="status">
          <option value="active">Active</option>
          <option value="disabled">Disabled</option>
        </select><br />
      <label for="comment">Comment:</label>
        <input type="text" name="comment" /><br />
        
      <input type="submit" name="submit_user" value="Add User" />
    </fieldset>
  </form>
  
  <p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist">Back to userlist</a></p>
<?php } ?>