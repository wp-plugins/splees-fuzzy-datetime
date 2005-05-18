=== Splee's Fuzzy DateTime Plugin, v0.5 Beta ===

Tags: fuzzy, post, date, time
Contributors: LeeMcFadden

This plugin takes the time and date of a post and makes it 'fuzzy', i.e. more human.  For example, if a post was made three days ago, instead of simply displaying the date the plugin can output '3 days ago'.

The structure of the output is completely customisable by the user from the admin panel using the replacement strings, which are documented on the options page itself.

== Installation ==

1. Upload the fuzzy_datetime.php file to your plugins directory (/wp-content/plugins/)
2. Go to your Wordpress administration area and activate the plugin.
3. Go to your Options menu and select 'Fuzzy DateTime'
4. Customise your output strings using the displayed available variables
5. Substitute <?php splees_fuzzy_datetime() ?> for <?php the_time() ?> where ever you wish the plugin to output the date and time.

== Frequently Asked Questions == 

Q. Why is nothing displayed when I put the 'splees_fuzzy_datetime()' function in my template?

A. Did you go to the options page at least once after installing the plugin?  If not, the default replacement strings haven't been set up yet.  Go to your dashboard, click options and select 'Fuzzy DateTime'.  This should solve your problem.

Got a question or problem installing this plugin?  Email me: splee@splee.co.uk

= Do I really need to use this plugin? =

No, this plugin is purely for aesthetic purposes.  However, if you want to have more freindly publishing dates this plugin is for you.
