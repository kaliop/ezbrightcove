# Installation

## Requirements

1. PHP 5.2 or higher
2. Tested on eZ Publish 2012.6 and 2012.8 but should work on previous versions.
3. CURL support in PHP.
4. A Brightcove account with API write access (Video Cloud Pro or Enterprise).

eZ Publish clustering is supported.

## Installing

1. Copy `ezbrightcove` into 'extension' directory.

2. Enable `ezbrightcove` in settings/override/site.ini.append.php:

        ActiveExtensions[]=ezbrightcove

3. Create settings/override/ezbrightcove.ini.append.php and enter your
   API details:

        <?php /*

        [BrightcoveSettings]
        ApiReadToken=
        ApiWriteToken=

        */ ?>

    You can find these under `Account Settings` -> `API Management` when logged in
    to Brightcove's Video Cloud.

4. Install the database table:

        $ mysql -u<user> -p <database> < extension/ezbrightcove/sql/mysql/schema.sql

5. Regenerate autoloads

        $ php bin/php/ezpgenerateautoloads.php -e

6. Clear cache

        $ php bin/php/ezcache.php --clear-all

7. Install the required cronjobs. It's suggested to run the frequent cronjobs every 1 to 5 minutes and the infrequent cronjobs can run once a day, but you can adjust this to fit your own needs:

        # Convert and update Brigthcove videos - every 3 minutes.
        0-59/3 * * * * cd $EZPUBLISHROOT && $PHP runcronjobs.php -q brightcove_frequent 2>&1

        # Delete Brightcove videos - once a day at 04:00 AM.
        00 04 * * * cd $EZPUBLISHROOT && $PHP runcronjobs.php -q brightcove_infrequent 2>&1

8. In the administration interface create a new class or modify an existing class:

    1. Add a new attribute of type `Text line` that will hold the name of the
       video. Make it required.
    2. Add a new attribute of type `Text line` that will hold the description
       of the video. It's recommended to make it required.
    3. Add a new attribute of type `Brightcove media`
        1. For the options `Where name of the video is stored`
and `Where description of the video is stored` use the drop downs to choose the
`Text line` attributes you created in the steps above.
        2. Enter a player id in the `Video player id` field. This can be found when selecting a Video Player under `Publishing` in Brightcove's Video Cloud.
        3. Enter a player key in the `Video player key` field. This can found be in the embed code for a player. Select a video player while in `Publishing` in Brightcove's Video Cloud and click `Get Code` in the toolbar at the bottom.
        4. Enter the width and height you wish the player to be and optionally
           a background color for the player.
        5. Enter the maximum size of any uploaded videos in the `Max video file
           size` field.
