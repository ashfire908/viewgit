<?php
global $page;
?><h1>Delete User</h1>

<?php if ($page['userinfo']['confirm'] == false) { ?>
  <form id="deleteuser" method="post" action="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['userinfo']['id'])); ?>">
    <p>Do you really want to delete user '<?php echo $page['userinfo']['name']?>' (ID <?php echo $page['userinfo']['id']; ?>)?</p>
    
    <p><input type="submit" name="confirm" value="Yes" /> <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $page['userinfo']['id'])); ?>">Cancel</a></p>
  </form>
<?php } else { ?>
  <p>User '<?php echo $page['userinfo']['name']?>' (ID <?php echo $page['userinfo']['id']; ?>) has been deleted.</p>
  
  <p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist">Back to userlist</a></p>
<?php }