<?php
global $page;
?><h1>View User</h1>

  <dl class="viewuser">
    <dt>Id</dt>
      <dd><?php echo $page['userinfo']['id']; ?></dd>

    <dt>User name</dt>
      <dd><?php echo $page['userinfo']['name']; ?></dd>

    <dt>Type</dt>
      <dd><?php echo $page['userinfo']['type']; ?></dd>

    <dt>Status</dt>
      <?php 
switch ($page['userinfo']['status']) {
    case 'active':
        ?><dd class="active_user"><?php
        break;
    case 'disabled':
        ?><dd class="disabled_user"><?php
        break;
    default:
        ?><dd><?php 
        break;
} echo $page['userinfo']['status']; ?></dd>

    <dt>Comment</dt>
      <dd><?php echo $page['userinfo']['comment']; ?></dd>
    
    <br style="clear: left;" />
  </dl>
  
  <h2>Authorized Projects</h2>
    <ul>
<?php foreach($page['userinfo']['auth_projects'] as $project) { ?>
      <li><a href="<?php echo makelink(array('a' => 'summary', 'p' => $project)); ?>"><?php echo $project; ?></a></li>
<?php }?>
    </ul>

  <p>
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist">Back to userlist</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'edituser', 'id' => $page['userinfo']['id'])); ?>" title="Edit user '<?php echo $page['userinfo']['name']; ?>'">Edit user</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'editauth', 'id' => $page['userinfo']['id'])); ?>" title="Edit user '<?php echo $page['userinfo']['name']; ?>' authorized projects">Edit authorized projects</a> -
     <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['userinfo']['id'])); ?>" title="Delete user '<?php echo $page['userinfo']['name']; ?>'">Delete user</a>
  </p>
