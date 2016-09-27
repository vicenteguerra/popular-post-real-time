# PopularPostRealTime

Wordpress Plugin: https://wordpress.org/plugins/popular-post-google-analytics-real-time/installation/

Trendy post based in Google Analytics Real Time

Author: Vicente Guerra

Based in hlashbrooke wordpress plugin template

Tags: wordpress, plugin, template, google, trendy, analytics, real time, api, most views, popular

Requires at least: 3.9

Tested up to: 4.0

Stable tag: 1.0

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html


### Description

Get popular posts from Google Analytics Real Time and set every 10 minutes a new category called "Popular RT" with a number (By default 10 Posts) of popular post that you want and the number of active users in every post in this category, ready for you display your blog´s trendy post in every place of your Wordpress.

Query Example using the category and the number of views for sorting.

`<?php

$args = array( 'posts_per_page' => 5,
     'offset' => 0,
     'category_name' => 'popular_real_time_cat',
     'meta_key' => 'active_users',
     'orderby' => 'meta_value_num',
     'order' => 'DESC');

     $queryWP = new WP_Query();

 ?>`

*   The number of posts assigned in "Popular RT" may be less than the number selected (By default 10) because Google Analytics also include root path / (Home), categories or tags if have more active users than another posts.

###  Installation

Installing "PopularPostRealTime" can be done either by searching for "PopularPostRealTime" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
1. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the Settings->Popular Post RT screen to configure the plugin

###  Configuration

Go to: Settings -> Popular Post RT

You only need fill the next information and in 24 hours with the correct configuration your connection to **Google Analytics Real Time API** should be work.

https://docs.google.com/forms/d/e/1FAIpQLSc9OpoDGB3tBD7oy1OG9fyum8KBIxs-2ihPCsHp13WTnM-SSQ/viewform


###  Screenshots

![alt tag](https://lh3.googleusercontent.com/xPX0WN1bGoBsQBywhgyfp3u_AQgGZ6Dj5RtmHAkRHDiIbA1IPa7ofR8PBsOR49BsK-vZRReo1OoLFOSGlmgw1LtOSvbf7URjEPhqXhKoIzh1rpiFKjNmOXQQLK2UDA-ludHLp4Ria7vbxKE4FQJHywdxSyd85fONeQVOJg05gQKfGXasjbSwfNz_MbAZsx846ngl5hGYpncTsV9wiS7nyvcp5gpKwLXHmwZhbAoC6UJhZOm4ghIn69L7H14_Zc5M94WlbtKup8iu8pm3fuhajtFnuA2fszRDG-77_QOQ5d67-g_7IuYLg4OW7sYUphMjlqB7y5h1rquLDPaj73fNH5wlLao68qWi5DOHJWjgqpy_MggjcHU2Skoj9lsqeaXZQ26XjGlvQ_nzUvRDsALO5TsmMBWWiQ9VDTobXkx7RkFdvDHpf3cEZRbYQVstvpmxMXq4G9z7gCFuU5c_pFKLo8qqcVtNXuHg0u9j27CSIUgAtabY0b5x33O1--trC6cPQ0sSDLh_rc3TUNQysFk9VNuOg0rz1s_YwweiPRUCyA9u0mMAktWIjSTqFErEnjfRBdJ6Hhywpgv5OW6KqG2B8I-sO5dbdNczj5nrtryT3ZIJM6mS=w2202-h1324-no)

### Changelog

= 1.0 =
* 2016-09-27
* Initial release

## Support on Beerpay
Hey dude! Help me out for a couple of :beers:!

[![Beerpay](https://beerpay.io/vicenteguerra/popular-post-real-time/badge.svg?style=beer-square)](https://beerpay.io/vicenteguerra/popular-post-real-time)  [![Beerpay](https://beerpay.io/vicenteguerra/popular-post-real-time/make-wish.svg?style=flat-square)](https://beerpay.io/vicenteguerra/popular-post-real-time?focus=wish)
