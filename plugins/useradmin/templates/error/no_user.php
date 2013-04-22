<?php
global $page;
?><h1>Error</h1>

  <p style="border: 1px solid red; padding: 2px; background: #f77;">Could not find any user with the ID <?php echo $page['userinfo']['id']; ?>.</p>
  
  <p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Return to userlist" class="userlist">Back to userlist</a></p>