<?php
class TopNavTest extends WebTestCase
{

    public function testTitle()
    {
        $this->url('/');
        $nav_elements = $this->elements($this->using('css selector')->value('body>div#page>nav#top_nav>ul'));
        $about = $nav_elements[1];
        $about_link = $about->elements($this->using('css selector')->value('a'));
        $this->assertEquals($about_link->text(), 'About');
    }

}
?>