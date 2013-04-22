<?php
global $page;
?><h1>Delete User</h1>

<?php
if (count($page['useradmin']['errors']) > 0) {
    echo '  <p style="border: 1px solid red; padding: 2px; background: #f77;">' . implode("<br />\n", $page['useradmin']['errors']) . "</p>\n\n";
}
if (count($page['useradmin']['messages']) > 0) {
    echo '  <p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . implode("<br />\n", $page['useradmin']['messages']) . "</p>\n\n";
}

if ($page['useradmin']['confirm'] == false) { ?>
  <form id="deleteuser" method="post" action="<?php echo makelink(array('a' => 'admin', 'm' => 'deleteuser', 'id' => $page['user']->id)); ?>">
    <p>Do you really want to delete user '<?php echo $page['user']->name; ?>' (ID <?php echo $page['user']->id; ?>)?</p>
    
    <p><input type="submit" name="confirm" value="Yes" /> <a href="<?php echo makelink(array('a' => 'admin', 'm' => 'viewuser', 'id' => $page['user']->id)); ?>">Cancel</a></p>
  </form>
<?php } else { ?>
  <p>User '<?php echo $page['user']->name; ?>' (ID <?php echo $page['user']->id; ?>) has been deleted.</p>
  
  <p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist" class="userlist">Back to userlist</a></p>
<?php }