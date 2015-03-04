<?php

class DataAccess {

    private static $host = 'localhost';
    private static $database = 'cobalt_cascade_test_data';
    private static $username = 'root';
    private static $password = '';
    private static $pdo;

    private static $posts;



    private static function preparePDO() {
        if (isset(self::$pdo) === false) {
            self::$pdo = new PDO(
                'mysql:host=' . self::$host . ';dbname=' . self::$database,
                self::$username,
                self::$password
            );
        }
    }

    private static function loadSinglePost($post_id) {
        self::preparePDO();
        $sql = "
            SELECT
                 post.post_id
                ,user.username
                ,site.domain
                ,site.
            FROM
                post
                INNER JOIN post_content ON post.post_id = post_content.post_id
            ";
        $statement = $db->prepare("SELECT * FROM post WHERE post_id=:post_id AND name=:name");
        $statement->bindValue(':post_id', $id, PDO::PARAM_INT);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        self::$posts[$post_id] = $row;
        return $row;
    }

    private static function loadAllTestPosts() {
        self::preparePDO();
        $statement = $db->prepare("SELECT * FROM post");
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        self::$posts = $rows;
    }

    public static function getPost($post_id) {
        $post_id = (int)$post_id;
        if (isset(self::$posts) === false) {
            loadAllTestPosts();
        }

        // Might be checking for a post that hads just been made.
        if (isset(self::$posts[$post_id]) === false) {
            return self::loadSinglePost($post_id);
        }

        if (isset(self::$posts[$post_id]) === true) {
            return self::$posts[$post_id];
        } else {
            throw 'Test post does not exist : ' . $post_id;
        }
    }
}













$fixtures = array ();

$fixtures['posts'] = array (
    1 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    2 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    3 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),

    4 => array (
        'title' => 'test post 4',
        'post_id' => 4,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    5 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    6 => array (
        'title' => 'test post 6',
        'post_id' => 06,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    7 => array (
        'title' => 'test post 7',
        'post_id' => 7,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    8 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    9 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    10 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    11 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    12 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    13 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    14=> array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    15 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    16 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    17 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    18 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    19 => array (
        'title' => '',
        'post_id' => 19,
        'user' => array (
            'username' => 'test2',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    20 => array (
        'title' => 'test post 20',
        'post_id' => 20,
        'user' => array (
            'username' => 'test3',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    21 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    22 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    23 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    24 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    25 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    26 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    27 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    28 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    29 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    30 => array (
        'title' => '',
        'post_id' => 0,
        'user' => array (
            'username' => 'test',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => 0,
    ),
    10225 => array (
        'title' => 'Hello World',
        'post_id' => 10225,
        'user' => array (
            'username' => 'sky',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => -1,
    ),
    10226 => array (
        'title' => 'Tortoise or Hare?',
        'post_id' => 10226,
        'user' => array (
            'username' => 'sky',
            'domain' => GlobalPage::$domain,
        ),
        'time' => 'Wed Nov 09 2011 12:00:00 GMT+0000 (GMT Standard Time)',
        'comment_count' => -1,
    ),
);

$fixtures['rhythms'] = array (
    'skys_priority' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'skys priority',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
    'popular_recently' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'skys priority',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
    'popular_in_last_week' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'popular in last week',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
    'popular_in_last_day' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'popular in last day',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
    'popular_in_last_hour' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'popular in last hour',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
    'newest' => array(
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'newest',
        'version' => array(
            'major' => '0',
            'minor' => '0',
            'patch' => '0',
        ),
    ),
);

$fixtures['streams'] = array (
    'test_stream' => array (
        'domain' => GlobalPage::$domain,
        'username' => 'sky',
        'name' => 'news',
        'version' => array (
            'major' => 'latest',
            'minor' => 'latest',
            'patch' => 'latest',
        ),
        'partial_description' => 'News about',
        'default_rhythm' => $fixtures['rhythms'][''],
        'rhythms' => array(
            $fixtures['rhythms'][],
            $fixtures['rhythms'][],
            $fixtures['rhythms'][],
            $fixtures['rhythms'][],
        ),
    ),
);
