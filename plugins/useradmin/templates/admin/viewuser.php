<?php
global $page;
?><h1>View User</h1>

<?php
if (count($page['useradmin']['errors']) > 0) {
    echo '  <p style="border: 1px solid red; padding: 2px; background: #f77;">' . implode("<br />\n", $page['useradmin']['errors']) . "</p>\n\n";
}
if (count($page['useradmin']['messages']) > 0) {
    echo '  <p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . implode("<br />\n", $page['useradmin']['messages']) . "</p>\n\n";
}
?>  <dl class="viewuser">
    <dt>Id</dt>
      <dd><?php echo $page['user']->id; ?></dd>

    <dt>User name</dt>
      <dd><?php echo $page['user']->name; ?></dd>

    <dt>Type</dt>
      <dd><?php echo $page['user']->type; ?></dd>

    <dt>Status</dt>
      <?php 
switch ($page['user']->status) {
    case 'active':
        ?><dd class="active_user"><?php
        break;
    case 'disabled':
        ?><dd class="disabled_user"><?php
        break;
    default:
        ?><dd><?php 
        break;
} echo $page['user']->status; ?></dd>

    <dt>Comment</dt>
      <dd><?php echo $page['user']->comment; ?></dd>
    
    <br style="clear: left;" />
  </dl>
  
  <h2>Authorized Projects</h2>
    <ul>
<?php foreach($page['user']->projects as $project) { ?>
      <li><a href="<?php echo makelink(array('a' => 'summary', 'p' => $project)); ?>"><?php echo $project; ?></a></li>
<?php }?>
    </ul>

  <p>
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist" class="userlist">Back to userlist</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $page['user']->id)); ?>" title="Edit user '<?php echo $page['user']->name; ?>'" class="user_edit">Edit user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $page['user']->id)); ?>" title="Edit authorized projects for user '<?php echo $page['user']->name; ?>'" class="user_auth">Edit authorized projects</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['user']->id)); ?>" title="Delete user '<?php echo $page['user']->name; ?>'" class="user_delete">Delete user</a>
  </p>
