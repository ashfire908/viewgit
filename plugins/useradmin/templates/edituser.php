<?php
global $page;
?><h1>Edit User</h1>

<?php
if ($page['userinfo']['error'] != '') {
    echo '<p style="border: 1px solid red; padding: 2px; background: #f77;">' . $page['userinfo']['error'] . "</p>\n\n";
}
if ($page['userinfo']['msg'] != '') {
    echo '<p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . $page['userinfo']['msg'] . "</p>\n\n";
}
?>
  <form class="user_settings" method="post" action="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $page['userinfo']['id'])); ?>">
    <fieldset class="two_column">
      <label for="id">User ID:</label>
        <input type="text" name="id" disabled="disabled" value="<?php echo $page['userinfo']['id']; ?>" /><br />
      <label for="name">User Name:</label>
        <input type="text" name="name" value="<?php echo $page['userinfo']['name']; ?>" /><br />
      <label for="password">Password:</label>
        <input type="password" name="password" /><br />
      <label for="password_confirm">Confirm:</label>
        <input type="password" name="password_confirm" /><br />
      <span class="label_inline"></span>
      <label class="field_inline"><input type="checkbox" name="password_keep" value="true" checked="checked" /> Keep old password</label><br />
      <label for="type">User Type:</label>
        <select name="type">
          <option value="standard"<?php if ($page['userinfo']['type'] == 'standard') { echo ' selected="selected"'; }
?>>Standard</option>
          <option value="admin"<?php if ($page['userinfo']['type'] == 'admin') { echo ' selected="selected"'; }
?>>Admin</option>
        </select><br />
      <label for="status">Status:</label>
        <select name="status">
          <option value="active"<?php if ($page['userinfo']['status'] == 'active') { echo ' selected="selected"'; }
?>>Active</option>
          <option value="disabled"<?php if ($page['userinfo']['status'] == 'disabled') { echo ' selected="selected"'; }
?>>Disabled</option>
        </select><br />
      <label for="comment">Comment:</label>
        <input type="text" name="comment" value="<?php echo $page['userinfo']['comment']; ?>" /><br />
        
      <input type="submit" name="submit_user" value="Save User" />
    </fieldset>
  </form>

  <p>
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist" class="userlist">Back to userlist</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $page['userinfo']['id'])); ?>" title="View user '<?php echo $page['userinfo']['name']; ?>'" class="user_view">View user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $page['userinfo']['id'])); ?>" title="Edit user '<?php echo $page['userinfo']['name']; ?>' authorized projects" class="user_auth">Edit authorized projects</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['userinfo']['id'])); ?>" title="Delete user '<?php echo $page['userinfo']['name']; ?>'" class="user_delete">Delete user</a>
  </p>