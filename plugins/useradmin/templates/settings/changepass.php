<?php
global $page;
?><h1>Change password</h1>

<?php
if (count($page['useradmin']['errors']) > 0) {
    echo '  <p style="border: 1px solid red; padding: 2px; background: #f77;">' . implode("<br />\n", $page['useradmin']['errors']) . "</p>\n\n";
}
if (count($page['useradmin']['messages']) > 0) {
    echo '  <p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . implode("<br />\n", $page['useradmin']['messages']) . "</p>\n\n";
}

if ($page['usersettings']['confirm'] === true) { ?>
  <p>Password has been updated.</p>
<?php
} else {
?>
  <form class="user_settings" method="post" action="<?php echo makelink(array('a' => 'user', 'm' => 'changepass')); ?>">
    <fieldset class="two_column">
      <label for="old_password">Old password:</label>
        <input type="password" name="old_password" /><br />
      <label for="password">New password:</label>
        <input type="password" name="password" /><br />
      <label for="password_confirm">Confirm:</label>
        <input type="password" name="password_confirm" /><br />
      
      <input type="submit" name="submit_pass" value="Change password" />
    </fieldset>
  </form>
<?php } ?>
  <p><a href="<?php echo makelink(array('a' => 'user', 'm' => 'show')); ?>" title="View settings" class="user_settings">Return to user settings</a></p>