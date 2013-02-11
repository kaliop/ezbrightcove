<?php /* #?ini charset="utf8"?

[CronjobSettings]
ExtensionDirectories[]=ezbrightcove

# Run this cron group frequently (every 1-5 min)
[CronjobPart-brightcove_frequent]
Scripts[]=klpbccreate.php
Scripts[]=klpbcstatus.php
Scripts[]=klpbcupdate.php

# Run this cron group infrequently (once a day)
[CronjobPart-brightcove_infrequent]
Scripts[]=klpbcdelete.php

[CronjobPart-brightcove_create]
Scripts[]=klpbccreate.php

[CronjobPart-brightcove_status]
Scripts[]=klpbcstatus.php

[CronjobPart-brightcove_update]
Scripts[]=klpbcupdate.php

[CronjobPart-brightcove_delete]
Scripts[]=klpbcdelete.php

*/
