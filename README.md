Twitter Reader
==============
Increase the social profile of your Magento site by easily adding store specific tweets.

Description
-----------
This extension seamlessly integrates with the latest Twitter API technology and allows you to quickly and easily add tweets, per store, to any page in any position.

Usage
-----
Admin settings can be found under System -> Configuration -> Hussey Coding -> Twitter Reader and are as follows:

**Twitter Screen Name**
Enter the screen name of the account you want to pull tweets from

**Maximum Tweets To Display**
Maximum number of tweets to display, defaults to 5

**Limit Tweet Length**
Limit tweets to a set number of characters or leave blank to show all characters

**Display Tweet Date**
Display the date the tweet was sent

**Date Format**
Optional date format string to set how the date is displayed

**Consumer Key**
Consumer key for the account the tweets are being shown from

**Consumer Secret**
Consumer secret for the account the tweets are being shown from

**Status**
Shows whether or not access to the Twitter account has been established

**Request Token**
Shows whether a request token has been obtained or not, required to obtain an access token

**Access Token**
Shows whether an access token has been obtained or not, required to access the Twitter account

Under the display position settings you can define where the tweets block should be shown on the page as well as enable custom positioning.  If custom positioning is enabled then you need to add <?php echo Mage::helper('twitterreader')->show(); ?> to the template of your choice where you want the tweets block to be displayed.

Support
-------
If you have any problems with this extension, open an issue on GitHub

Contribution
------------
Contributions are welcomed, just open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Jonathan Hussey
[http://www.husseycoding.co.uk](http://www.husseycoding.co.uk)
[@husseycoding](https://twitter.com/husseycoding)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2015 Hussey Coding
