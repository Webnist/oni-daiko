=== Plugin Name ===
Contributors: Webnist
Donate link: 
Tags: multi, site, recent, posts, widget, rss
Requires at least: 3.0.4
Tested up to: 3.1
Stable tag: 0.5.3

Shows a list of the latest posts from all blogs under your WordPress Multisite.

== Description ==
Oni daiko is the traditional masked dance-drama of Sado Island in Japan since it began 500 years ago. People in Sado Island used to play the dance-drama for exorcism, success in business, and good harvests.

Unfortunately there was no relation between this plugin Oni daiko and the traditional dance-drama. Instead it provides a function that shows a list of the latest posts from all blogs under your WordPress Multisite
Also it supports widgets and outputs RSS feeds.

== Thanks ==
readme.txt written by [Hitoshi Omagari](http://www.warna.info/)

== Installation ==
   1. Upload Oni daiko folder to the wp-content/plugins directory in your WordPress multisite installation. Or you can download and install it using the built in WordPress plugin installer.
   2. Activate the plugin in your Administration Panel.
   3. In the Oni Daiko Setting screen, you can change the title, the number of posts to show, show/hide post’s content, the length of content to show. (Also you can changes the settings in each widgets settings.)
   4. Place the Oni Daiko Post List widget in a widget area or put the template tag below in your theme file.
      `<?php echo oni_daiko_template_tag(); ?>`

== Frequently Asked Questions ==

= Doesn’t show the latest posts =
Pleae make sure that the plugin is enabled, not forget to put “echo” before the template tag and so on.
Also, the network main site’s posts are not included the latest posts of Oni Daiko.

= Please tell me how to dance Oni Daiko =
I’m afraid I can’t dance Oni Daiko, so pleae watch the video below and learn it by yourself.

http://www.youtube.com/watch?v=mm0Rr6T8rPg

== Screenshots ==
1. Administration interface of Oni Daiko
2. Oni Daiko Widget

== Changelog ==
* **0.5.3**
* Public release

== Upgrade Notice ==
Backup your data first please.