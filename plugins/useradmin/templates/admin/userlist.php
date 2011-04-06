<?php
global $page;
?><h1>User Admin</h1>

<?php
if (count($page['useradmin']['errors']) > 0) {
    echo '  <p style="border: 1px solid red; padding: 2px; background: #f77;">' . implode("<br />\n", $page['useradmin']['errors']) . "</p>\n\n";
}
if (count($page['useradmin']['messages']) > 0) {
    echo '  <p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . implode("<br />\n", $page['useradmin']['messages']) . "</p>\n\n";
}
?><table class="userlist">
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
$even = False;
foreach($page['users'] as $user) {
    if ($user->status == 'disabled') {
        $user_class = 'user_disabled';
    } elseif ($user->type == 'admin') {
        $user_class = 'user_admin';
    } else {
        $user_class = 'user';
    }
?>
    <tr class="<?php if ($even) { echo 'even'; } else { echo 'odd'; } ?>">
      <td><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $user->id)); ?>" title="View user '<?php echo $user->name; ?>'" class="<?php echo $user_class; ?>"><?php echo $user->id; ?></a></td>
      <td><?php echo $user->name; ?></td>
      <td><?php echo $user->type; ?></td>
      <td><?php echo $user->status; ?></td>
      <td>
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $user->id)); ?>" title="View user '<?php echo $user->name; ?>'" class="user_view">view</a>
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $user->id)); ?>" title="Edit user '<?php echo $user->name; ?>'" class="user_edit">edit</a>
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $user->id)); ?>" title="Edit authorized projects for user '<?php echo $user->name; ?>'" class="user_auth">auth</a>
          <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $user->id)); ?>" title="Delete user '<?php echo $user->name; ?>'" class="user_delete">delete</a>
      </td>
    </tr>
<?php
    // Reverse even/odd bool
    $even = $even ? False : True;
}
?>
  </tbody>
</table>

<p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'adduser')); ?>" title="Add new user" class="user_add">Add User</a></p>