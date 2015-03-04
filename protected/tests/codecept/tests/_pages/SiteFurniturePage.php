<?php

class SiteFurniturePage
{
    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */
    public static $top_nav = 'body>div#page>nav#top_nav';
    public static $admin_nav = 'body>#page>nav#admin_nav';
    public static $top_bar = 'body>#page>div.yellow_orange_bar';
    public static $streams_nav = 'body>#page>nav#streams_nav';
    public static $article_root = 'body>#page>article#content';
    public static $side_bar = 'body>#page>article#content>div#sidebar_container';
    public static $messages = 'body>#page>article#content>div#messages';
    public static $article_page = 'body>#page>article#content>div#content_page';

}