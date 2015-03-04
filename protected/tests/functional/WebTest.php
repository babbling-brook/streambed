<?php
class WebTest extends WebTestCase
{

    public function testTitle()
    {
        $this->url('/');
        $body_elements = $this->elements($this->using('css selector')->value('body>div'));
        $page = $body_elements[1];
        $list = $page->elements($this->using('css selector')->value('nav>ul>li'));
        $link = $list[0]->element($this->using('css selector')->value('a'));
        $this->assertEquals($link->text(), 'About');
        $div = $this->elements($this->using('css selector')->value('body>div>p'));
    }

}
?>