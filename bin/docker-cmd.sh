#!/bin/sh
set -e

# process files
/usr/bin/find $APP_DIR/inbox -iname '*.txt' -exec /usr/local/share/shoutbomb/scripts/process_files.php "{}" \;
/bin/rm $APP_DIR/inbox/*.txt