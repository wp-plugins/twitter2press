=== Plugin Name ===
Contributors: mathieulesniak
Donate link: 
Tags: Twitter, Wordpress, image hosting, twitpic, tweet, tweetie, twittelator, tweetpress
Requires at least: 2.3
Tested up to: 2.8.5
Version: 1.0.5
Stable Tag: 1.0.5

Use your Wordpress blog to host the photos you post to Twitter! 

== Description ==

Have you ever noticed that the pictures you send along with your tweets ends on public images hosting services ? These hosting services put ads around your content, and can make traffic (and money) with YOUR pictures. 
Twitter2Press is a little plugin that'll transform your Wordpress in a image hosting service. So, your images will still be your property, and the generated traffic will be yours. 

This plugin will communicate with the wonderful Twitter client for iPhone Tweetie, very easily. 
Once your picture has been uploaded, the plugin will grab the content of the associated tweet when it's available. 

And, one more thing : Twitter2Press is free !

== Installation ==

Twitter2Press is a plugin that can communicate with Tweetie 2 for the images uploads. 

Simply follow these steps :

1. Download Twitter2Press here
1. Upload in the wp-content/plugins Wordpress directory
1. Activate the plugin in Wordpress
1. Configure the plugin, by specifiying the page that'll host the gallery, and your Twitter credentials

Voilà, the plugin is ready. Easy, isn't it ?

NB : We encourage you to create a special page in Wordpress to host your gallery. And what about naming it "Gallery" ? ;) 

Now, le's setup Tweetie. Just a few seconds, and you'll get your personnal gallery !

1. Launch Tweetie. On the "Accounts" page, choose "Settings"
1. In "Image Service", choose "Custom..."
1. Type in your Wordpress URL in the field
1. Save

Now everytime you'll upload a picture from Tweetie, the link will point to YOUR Wordpress gallery. The traffic will we your, instead of losing it with an image hosting service.

== Screenshots ==

Live example on [this gallery](http://newsdegeek.com/galerie).

== Changelog ==


= 1.0.5 = 
* Fix retweet link, missing tweet text

= 1.0.4 =
* Brand new image management : images are now considered as attachments. 
* Each image can now have its own comments.
* Each image can now be reused in Wordpress.
* Can now edit each image title & caption.
* No more directory creation and chmod problems.
* Added a "Retweet this image" link below picture.
* Can define a custom CSS for the gallery. Just put a "twitter2press.css" file in your current theme directory. Default CSS can be found at /wp-content/plugins/twitter2press/twitter2press.css
* Fixed image width issue, but not on Internet Explorer 6. Sorry guys :-)
* Fixed a bug with the & character.


= 1.0.3 =
* Can now delete images from the admin panel
* Can define the number of thumbnails below main image

= 1.0.2 = 
* Support for more URL Shorteners : tr.im, is.gd, tinyURL, bit.ly, j.mp and YOURLS (http://yourls.org/) private shortener
* changed the method for URL accessing causing error on some wordpress setups (hope so :p)
* added chmod 0755 value for created directories

= 1.0.1 =
* Support for &apos; character in tweets

= 1.0 =
* Initial Version


