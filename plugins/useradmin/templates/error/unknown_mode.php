<?php
global $page;
?><h1>Error</h1>

  <p style="border: 1px solid red; padding: 2px; background: #f77;">Unknown mode "<?php echo $page['admin_mode']; ?>".</p>
  
  <p><a href="<?php echo makelink(array('a' => 'admin', 'm' => 'userlist')); ?>" title="Go to userlist" class="userlist">Go to userlist</a></p>