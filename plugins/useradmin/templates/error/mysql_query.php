<?php
global $conf;
global $page;
?><h1>Error</h1>

  <p style="border: 1px solid red; padding: 2px; background: #f77;">A database error was encountered while executing the query.<?php if ($conf['debug']) { ?> The error was: <code><?php echo $page['mysql_error']; ?></code><?php } ?></p>