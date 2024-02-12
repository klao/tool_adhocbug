# Description
Some ad-hoc tasks fail with `Class not found` fatal error when they are started from the `cli/adhoc_task.php --keep-alive=59`, but run normally and succeed when run from the "normal" `cli/cron.php`.

A specific example implemented in this plugin, the `get_moduleinfo_data()` function triggers this behavior.

# Steps to reproduce
1. Install this plugin in your test Moodle.
2. Create at least one course with at least one course module.
3. Add the `* * * * * /usr/bin/php  /path/to/moodle/admin/cli/adhoc_task.php --execute --keep-alive=59` to your crontab, and log its output somewhere.
4. Navigate to the http://your.test.moodle/admin/tool/adhocbug/index.php page and click the "Run task" button.
5. Look at the cron output and see that it's failing with `Class "grade_item" not found` error.
6. Disable the "keep-alive" cron runner, and next time the task is retried it will succeed.

# For Moodle 4.2 or higher
In Moodle 4.2 the default cron script has this "keep-alive" behavior enabled by default. (See: https://tracker.moodle.org/browse/MDL-77186). So, to reproduce this bug, you don't need the additional `cli/adhoc_task.php ...` in your crontab.

So, in the above list:
- skip \#3.
- Observe the task failing.
- Change the default crontab line to: `.../admin/cli/cron.php --no-keep-alive >> log_somewhere 2>&1`.
- Wait until the "keep-aliving" instances exit (or kill them).
- The next time the ad-hoc task is retried it will succeed.

Note that this buggy behavior doesn't _always_ happen with this setup (regular cron with "keep-alive" enabled by default). I was able to reproduce it a few times, but not reliably. The `cli/adhoc_task.php --execute --keep-alive=59` _always_ triggers this bug, even in Moodle 4.2.

# Notes
_Most_ of the regular ad-hoc tasks run just fine when started from "keep-alive" cron jobs. It's one obscure plugin where we noticed this behavior. But it makes the bug that much more devious, as it's not clear that the problem is not with the plugin, but with how the task is run.

I have tested this with the latest 4.1 and 4.2 Moodle versions.

Hat tip to [@olivabigyo](https://github.com/olivabigyo) who discovered the bug!
