<?php
global $page;
?><h1>User Admin</h1>

<table class="userlist">
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Type</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
<?php 
foreach($page['userlist'] as $cur_user) {
?>
    <tr>
      <td><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $cur_user['id'])); ?>" title="View user '<?php echo $cur_user['username']; ?>'"><?php echo $cur_user['id']; ?></a></td>
      <td><?php echo $cur_user['username']; ?></td>
      <td><?php echo $cur_user['type']; ?></td>
      <td><?php echo $cur_user['status']; ?></td>
      <td>
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $cur_user['id'])); ?>" title="View user '<?php echo $cur_user['username']; ?>'" class="user_view">view</a> -
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $cur_user['id'])); ?>" title="Edit user '<?php echo $cur_user['username']; ?>'" class="user_edit">edit</a> -
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $cur_user['id'])); ?>" title="Edit user '<?php echo $cur_user['username']; ?>' authorized projects" class="user_auth">auth</a> -
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $cur_user['id'])); ?>" title="Delete user '<?php echo $cur_user['username']; ?>'" class="user_delete">delete</a>
      </td>
    </tr>
<?php
}
?>
  </tbody>
</table>

<p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'adduser')); ?>">Add User</a></p>