<?php
/**
 * Copyright 2015 Sky Wickenden
 * 
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 * 
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 * 
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 */


/**
 * Model for the post DB table.
 * A list of posts made by users in streams.
 *
 * @package PHP_Models
 * @refactor post.parent and post.top_parent are no longer needed as the functionality is duplicated in
 * post_descendent. There are however majny references to them in the code that need refactoring to use the new
 * system before they can be removed.
 */
class Post extends CActiveRecord
{

    /**
     * The primary key of the post.
     *
     * @var integer
     */
    public $post_id;

    /**
     * The primary key of the domain that owns this post.
     *
     * @var integer
     */
    public $site_id;

    /**
     * The post_id on the site that owns this post.
     *
     * For new posts in the site, this is initially set to NULL and then imediatly updated to reflect the post_id.
     *
     * @var integer
     */
    public $site_post_id;

    /**
     * The extra id of the stream that the stream is in.
     *
     * @var integer
     */
    public $stream_extra_id;

    /**
     * The id of the user that is making an post.
     *
     * @var integer
     */
    public $user_id;

    /**
     * The creation date of the post.
     *
     * @var integer
     */
    public $date_created;

    /**
     * The post_id of the parent post (If there is one).
     *
     * @var integer|null
     */
    public $parent;

    /**
     * The top post_id in a heirarchy of parent offfer_ids for this post (If there is one).
     *
     * @var integer|null
     */
    public $top_parent;

    /**
     * The block of posts that this post is in.
     *
     * Posts are grouped into blocks for easy caching.
     *
     * @var integer
     */
    public $block;

    /**
     * The block of posts that this post is in when it is part of a tree of posts.
     *
     * Posts are grouped into blocks for easy caching.
     *
     * @var integer
     */
    public $block_tree;

    /**
     * What is the permission status of this post. See lookup table for valid values.
     *
     * @var integer
     */
    public $status;

    /**
     * A count of all public child posts.
     *
     * @var integer
     */
    public $child_count;

    /**
     * Allows the content from post_content to be referenced here to get around DISTINCT issues.
     *
     * Not a table column.
     *
     * @var string
     */
    public $content;

    /**
     * Returns the parent model.
     *
     * @param type $className The name of this class.
     *
     * @return Model
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * Getter for the tables name.
     *
     * @return string the associated database table name.
     */
    public function tableName() {
        return 'post';
    }

    /**
     * Rules applied when validating this models attributes.
     *
     * @link http://www.yiiframework.com/doc/api/1.1/CModel#rules-detail
     *
     * @return array An array of rules.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            // site_post_id is required but not on first save as the row needs creating first.
            array('stream_extra_id, user_id, status', 'required'),
            array('stream_extra_id', 'length', 'max' => 10),
            array(
                'post_id, site_id, site_post_id, stream_extra_id, user_id,
                    parent, top_parent, block, block_tree, status, child_count, ',
                'numerical',
                'integerOnly' => true,
            ),
            array('status', 'ruleStatus'),
        );
    }

    /**
     * Checks that the status of an post is valid.
     *
     * @return void
     */
    public function ruleStatus() {
        $valid = LookupHelper::validId("post.status", $this->status);
        if ($valid === false) {
            $this->addError('status', 'Invalid status id : ' . $this->status);
        }
    }

    /**
     * Relationships to other tables used in fetching nested models.
     *
     * @return array(array)
     */
    public function relations() {
        return array(
            'post_popular' => array(self::BELONGS_TO, 'PostPopular', 'post_popular_id', 'joinType' => 'INNER JOIN'),
            'post_content' => array(self::HAS_MANY, 'PostContent', 'post_id', 'joinType' => 'INNER JOIN'),
            'stream_extra' => array(
                self::BELONGS_TO,
                'StreamExtra',
                'stream_extra_id',
                'joinType' => 'INNER JOIN',
            ),
            'stream_field' => array(self::BELONGS_TO, 'StreamField', 'stream_extra_id'),
        );
    }

    /**
     * Labels used for this models attributes on Yii html components.
     *
     * @return array customized attribute labels (name=> label).
     */
    public function attributeLabels() {
        return array(
            'post_id' => 'Post',
            'stream_extra_id' => 'Stream',
            'date_created' => 'Date Created',
        );
    }

    /**
     * Get a local post_id for an post.
     *
     * If it is not already present locally then it is fetched.
     *
     * @param string $domain The domain that owns the post_id we are fetching.
     * @param integer $remote_id The post_id that is local to $domain.
     *
     * @return array
     */
    public static function getPostAndTypeId($domain, $remote_id) {
        if ($domain !== Yii::app()->params['host']) {
            $site_id = SiteMulti::getSiteID($domain, false);
            if ($site_id === false) {
                // @fixme check remote domain is a valid BabblingBrook site
                $site_id = SiteMulti::getSiteID($domain, true);
            }
        } else {
            $site_id = Yii::app()->params['site_id'];
        }

        $sql = "SELECT
                     post_id
                    ,stream_extra_id
                FROM
                    post
                WHERE site_id = :site_id AND site_post_id = :site_post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":site_post_id", $remote_id, PDO::PARAM_INT);
        $row = $command->queryRow();
        if (empty($row) === true && $site_id !== Yii::app()->params['site_id']) {
            //@fixme get the post and then paerhaps stream from the remote site. insert and then fetch the id
            // if still not found then return empty array
            $temp = true;
        }
        return $row;
    }

    /**
     * Fetch a block of posts for an stream.
     *
     * Does not return post content, just the header information.
     *
     * @param integer $stream_extra_id The extra id of the stream that we are fetching a block of post for.
     * @param integer $block The block number that we are fetching.
     *
     * @return array Ready for JSON encoding.
     * @fixme What about streams that have multiple versions?
     * @fixme refactor this and all methods in this class to fetch content
     *  as per geting private posts. more than X10 improvment.
     */
    public static function getPostsBlock($stream_extra_id, $block) {
        $status_id = LookupHelper::getID('post.status', 'public');
        $sql =    "SELECT
                         post.post_id
                        ,post_user.username AS username
                        ,post_site.domain AS domain
                        ,stream.name AS stream_name
                        ,stream_user.username AS stream_username
                        ,stream_site.domain AS stream_domain
                        ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                        ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                        ,post.parent AS parent_id
                        ,post.top_parent AS top_parent_id
                        ,status_lookup.value AS status
                        ,post.block AS stream_block
                        ,post.block_tree AS tree_block
                        ,post.child_count
                    FROM post
                        INNER JOIN user AS post_user ON post.user_id = post_user.user_id
                        INNER JOIN site AS post_site ON post_user.site_id = post_site.site_id
                        INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                        INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                        INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                        INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
                        INNER JOIN version ON stream_extra.version_id = version.version_id
                        INNER JOIN lookup AS status_lookup ON post.status = status_lookup.lookup_id
                    WHERE
                        post.stream_extra_id = :stream_extra_id
                        AND post.status = :status_id
                        AND post.block = :block
                        AND (post.date_created < :cooldown OR post.user_id = :user_id)
                    ORDER BY post.date_created DESC";
        $command = Yii::app()->db->createCommand($sql);

        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":block", $block, PDO::PARAM_INT);
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $posts = $command->queryAll();

        return $posts;
    }

    /**
     * Fetch the latest posts in a stream.
     *
     * Does not return post content, just the header information.
     *
     * @param integer $stream_extra_id The $stream_extra_id to get the latest posts for.
     *
     * @return array The post rows requested.
     */
    public static function getLatestPosts($stream_extra_id) {
        $status_id = LookupHelper::getID('post.status', 'public');
        $sql = "
            SELECT
                 post.post_id
                ,post_user.username AS username
                ,post_site.domain AS domain
                ,stream.name AS stream_name
                ,stream_user.username AS stream_username
                ,stream_site.domain AS stream_domain
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,status_lookup.value AS status
                ,post.block AS stream_block
                ,post.block_tree AS tree_block
                ,post.child_count
                ,post_content.revision AS revision
            FROM post
                INNER JOIN user AS post_user ON post.user_id = post_user.user_id
                INNER JOIN site AS post_site ON post_user.site_id = post_site.site_id
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN lookup AS status_lookup ON post.status = status_lookup.lookup_id
                INNER JOIN post_content
                    ON post.post_id = post_content.post_id
                        AND post_content.revision =
                            (
                             SELECT revision FROM post_content
                             WHERE post_id = post.post_id ORDER BY revision DESC LIMIT 1
                            )
                        AND post_content.display_order = 1
            WHERE
                post.stream_extra_id = :stream_extra_id
                AND post.status = :status_id
                AND UNIX_TIMESTAMP(post.date_created) > unix_timestamp() - :refresh_frequency
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
            ORDER BY post.date_created DESC";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $refresh_frequency = Yii::app()->params['refresh_frequency'];
        // @fixme this is necessary for the tests to run. Need to change so that tests can overwrite the default.
        $refresh_frequency = 600000000;
        $command->bindValue(":refresh_frequency", $refresh_frequency, PDO::PARAM_INT);
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $posts = $command->queryAll();

        return $posts;
    }

    /**
     * Fetch the full nested sub posts for an post, and return as an array ready for converting to json.
     *
     * @param integer $post_id The post_id to fetch sub posts for.
     * @param integer $block_tree The block of data to fetch. 0 = current.
     *
     * @return array The sub posts requested.
     */
    public static function getSubPostsBlock($post_id, $block_tree) {
        $sql = "
            SELECT
                 post.post_id
                ,post_user.username AS username
                ,post_site.domain AS domain
                ,stream.name AS stream_name
                ,stream_user.username AS stream_username
                ,stream_site.domain AS stream_domain
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,status_lookup.value AS status
                ,post.block AS stream_block
                ,post.block_tree AS tree_block
                ,post.child_count
                ,post_content.text AS title
                ,post_content.link_title
                ,post_content.link
                ,post_content.revision
            FROM post
                INNER JOIN post_descendent ON post.post_id = post_descendent.descendent_post_id
                    AND post_descendent.ancestor_post_id = :post_id
                INNER JOIN user AS post_user ON post.user_id = post_user.user_id
                INNER JOIN site AS post_site ON post_user.site_id = post_site.site_id
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN stream_child ON stream_extra.stream_extra_id = stream_child.parent_id
                INNER JOIN lookup AS status_lookup ON post.status = status_lookup.lookup_id
                INNER JOIN post_content
                    ON post.post_id = post_content.post_id
                        AND post_content.revision =
                            (
                             SELECT revision FROM post_content
                             WHERE post_id = post.post_id ORDER BY revision DESC LIMIT 1
                            )
                        AND post_content.display_order = 1
            WHERE
                post.block_tree = :block_tree
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                AND (
                    post.status = :public_status
                    OR  (post.user_id = :user_id AND post.status = :deleted_status)
                )
            ORDER BY post.date_created DESC";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":block_tree", $block_tree, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":public_status", LookupHelper::getID('post.status', 'public'), PDO::PARAM_INT);
        $command->bindValue(":deleted_status", LookupHelper::getID('post.status', 'deleted'), PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $sub_posts = $command->queryAll();

        $count = count($sub_posts);
        for ($i = 0; $i < $count; $i++) {
            // Delete content and user for anything marked private - but keep the post object as it will be needed
            // To display any children.
            // @fixme store.js needs to fetch these, if the owner has access.
            if ($sub_posts[$i]['status'] !== 'public') {
                $sub_posts[$i]['content'] = array(
                    '1' => array(
                        'text' => 'Deleted',
                        'display_order' => '1',
                    )
                );
                $sub_posts[$i]['username'] = 'deleted';
                $sub_posts[$i]['domain'] = 'deleted';
            } else {
                // Fetch the content for the post here.
                $sub_posts[$i]['content'] = PostContent::getPostContent(
                    $sub_posts[$i]['post_id'],
                    $sub_posts[$i]['revision']
                );
            }
        }

        return $sub_posts;
    }

    /**
     * Fetch only the latest full nested sub posts for an post, and return as an array ready for converting to json.
     *
     * @param integer $post_id The post_id to fetch sub posts for.
     *
     * @return array The requested posts.
     */
    public static function getLatestSubPosts($post_id) {
        $top_parent = Post::getTopParent($post_id);
        if ($top_parent === null) {
            $top_parent = $post_id;
        }

        $status_id = LookupHelper::getID('post.status', 'public');
        $sql = "
            SELECT
                 post.post_id
                ,post_user.username AS username
                ,post_site.domain AS domain
                ,stream.name AS stream_name
                ,stream_user.username AS stream_username
                ,stream_site.domain AS stream_domain
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,status_lookup.value AS status
                ,post.block AS stream_block
                ,post.block_tree AS tree_block
                ,post.child_count
                ,post_content.text AS title
                ,post_content.link_title
                ,post_content.link
                ,post_content.revision
            FROM
                post
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user AS post_user ON post.user_id = post_user.user_id
                INNER JOIN site AS post_site ON post_user.site_id = post_site.site_id
                INNER JOIN user AS stream_user ON stream.user_id = stream_user.user_id
                INNER JOIN site AS stream_site ON stream_user.site_id = stream_site.site_id
                INNER JOIN stream_child ON stream_extra.stream_extra_id = stream_child.parent_id
                INNER JOIN lookup AS status_lookup ON post.status = status_lookup.lookup_id
                INNER JOIN post_content
                    ON post.post_id = post_content.post_id
                        AND post_content.revision =
                            (SELECT revision
                             FROM post_content
                             WHERE post_id = post.post_id ORDER BY revision DESC LIMIT 1
                            )
                        AND display_order = 1
            WHERE
                post.top_parent = :top_parent
                AND post.status = :status_id
                AND UNIX_TIMESTAMP(post.date_created) > unix_timestamp() - :refresh_frequency
                AND post.date_created < :cooldown
            ORDER BY post.parent";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":top_parent", $top_parent, PDO::PARAM_INT);
        // Add an extra 60 seconds overlap to catch edge cases with page loading.
        $refresh_frequency = Yii::app()->params['refresh_frequency'] + 600;
        // @fixme this is necessary for the tests to run. Need to change so that tests can overwrite the default.
        $refresh_frequency = 60000000;
        $command->bindValue(":refresh_frequency", $refresh_frequency, PDO::PARAM_INT);
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $sub_posts = $command->queryAll();

        $count = count($sub_posts);
        for ($i = 0; $i < $count; $i++) {
            $sub_posts[$i]['content'] = PostContent::getPostContent(
                $sub_posts[$i]['post_id'],
                $sub_posts[$i]['revision']
            );
        }

        return $sub_posts;
    }


    /**
     * Fetch the top parent of an post.
     *
     * @param integer $post_id The post_id to fetch the top parent for.
     *
     * @return integer
     */
    public static function getTopParent($post_id) {
        $connection = Yii::app()->db;
        $sql =    "SELECT top_parent FROM post WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $top_parent_id = $command->queryScalar();
        if (isset($top_parent_id) === true) {
            return $top_parent_id;
        } else {
            return null;
        }
    }

    /**
     * Fetch the takes for this post.
     *
     * @param integer $post_id The post_id of the post to fetch takes for.
     *
     * @return array(array)
     */
    public static function getTakes($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT
                     take.value
                    ,take.field_id
                    ,user.username
                    ,site.domain
                FROM take
                INNER JOIN user ON take.user_id = user.user_id
                INNER JOIN site ON user.site_id = site.site_id
                WHERE
                    take.post_id = :post_id
                ORDER BY take.date_taken, field_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $rows = $command->queryAll();

        return $rows;
    }

    /**
     * Fetch the stream_extra_id for the stream that an post resides in.
     *
     * @param integer $post_id The post_id of the post to fetch an stream_extra_id for.
     *
     * @return integer
     */
    public static function getStreamExtraId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT stream_extra_id
                FROM post
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $stream_extra_id = $command->queryScalar();
        return $stream_extra_id;
    }


    /**
     * Return a full url for the meta id of this post.
     *
     * @param integer $post_id The id of the post to fetch a meta url for.
     *
     * @return string The meta post url.
     */
    public static function getMetaUrl($post_id) {
        return Yii::app()->params['host'] . '/post/' . Yii::app()->params['host'] . '/' . $post_id;
    }

    /**
     * Verify that the child_id is a nested sub post of parent_id
     *
     * @param type $child_id The child post_id to ensure it is a child of top_parent_id.
     * @param type $parent_id The top parent post id to ensure it is the parent of child_id.
     */
    public static function verifyParent($child_id, $top_parent_id) {
        $sql = "SELECT post_id
                FROM post
                WHERE
                    post_id = :child_id
                    AND top_parent = :top_parent_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":child_id", $child_id, PDO::PARAM_INT);
        $command->bindValue(":top_parent_id", $top_parent_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id === $child_id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fetch the username and the domain of the user who submitted an post.
     *
     * @param integer $post_id The local id of the post that a user is being looked up for.
     *
     * @return array|false Contains two paramaters, username and domain.
     */
    public static function getUserOwner($post_id) {
        $user_id = self::getUserId($post_id);
        if ($user_id === false) {
            return false;
        }

        $user_array = User::getFullUsernameParts($user_id);
        return $user_array;
    }

    /**
     * Fetch the user_id for the owner of this post.
     *
     * @param integer $post_id The local id of the post that a user is being looked up for.
     *
     * @return integer|false The user id or false.
     */
    public static function getUserId($post_id) {
        $sql = "SELECT user_id
                FROM post
                WHERE post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $user_id = $command->queryScalar();
        return intval($user_id);
    }

    /**
     * Delete an post.
     *
     * If the post has children or takes it will be set to deleted status rather than deleted.
     * If the post is private and the logged on user is the owner then the status is set to deleted.
     * If the post is private and the logged on user is the recipient then the delete flag in the link table
     *      is set to deleted.
     *
     * @param integer $site_post_id The id of the post in the data store that
     *      houses the stream that owns the post to delete.
     *
     * @return string|false status of the deletion or failure to find the post.
     * @fixme refresh caches
     */
    public static function deletePost($site_post_id) {

        $post_id = self::getPostIdBySitePostId($site_post_id);
        if ($post_id === false) {
            return false;
        }

        $owner_post_id = self::getUserId($post_id);
        $status = self::getStatus($post_id);
        if (($status === 'private' || $status === 'deleted') && $owner_post_id !== Yii::app()->user->getId()) {
            PostPrivateRecipient::setDeleted($post_id, Yii::app()->user->getId());
            return 'recipient';
        }

        // Fetch the cooldown of a post - if it is private and not cooled down then do
        // a full delete, if it is private and cooldown has expired then only mark as deleted.
        $created_timestamp = (int)self::getTimestamp($post_id);
        $cooldown_expired = false;
        if ($created_timestamp < time() - Yii::app()->params['post_cooldown']) {
            $cooldown_expired = true;
        }

        $has_children = self::hasChildren($post_id);
        $has_takes = false;
        if ($has_children === false) {
            $has_takes = Take::doesPostHaveTakes($post_id);
        }

        // If others have access to the post it should be hidden rather than deleted.
        $others_access = $has_children === true || $has_takes === true;
        if (($others_access === true || $status === 'private') && $cooldown_expired === true) {
            self::setStatus($post_id, LookupHelper::getID('post.status', 'deleted'));
            return 'hidden';
        } else {
            PostContent::deleteByPostId($post_id);
            PostPopular::deletePost($post_id);
            StreamPublic::deletePost($post_id);

            // Can do this by calling recalculateChildCountForAncestors as the ancestors need fetching
            // before the descendent is deleted.
            $ancestors = PostDescendent::getAllAncestorIds($post_id);
            PostDescendent::deleteDescendentByDescendentPostId($post_id);
            foreach ($ancestors as $ancestor) {
                $child_count = PostDescendent::getChildCount($ancestor['ancestor_post_id']);
                Post::updateChildCount($ancestor['ancestor_post_id'], $child_count);
            }

            self::deletePostRow($post_id);

            return 'full';
        }
    }


    /**
     * Get the timestamp for an post.
     *
     * @param integer $post_id The id of the post to check if it has any children.
     *
     * @return integer The timestamp for when the post was created.
     */
    public static function getTimestamp($post_id) {
        $sql = "SELECT UNIX_TIMESTAMP(date_created)
                FROM post
                WHERE post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $timestamp = $command->queryScalar();
        return $timestamp;
    }

    /**
     * Get the status of an post.
     *
     * @param integer $post_id The id of the post to check if it has any children.
     *
     * @return string The string value of a status. See loookup table for valid options.
     */
    public static function getStatus($post_id) {
        $sql = "SELECT status
                FROM post
                WHERE post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $status_id = $command->queryScalar();
        return LookupHelper::getValue($status_id);
    }

    /**
     * Set the status of an post.
     *
     * @param integer $post_id The id of the post to check if it has any children.
     * @param integer $status_id The lookup id for the status to set.
     *
     * @return void
     */
    public static function setStatus($post_id, $status_id) {
        $post = Post::model()->findByPk($post_id);
        $post->status = $status_id;
        if ($post->save() === false) {
            throw new Exception("Error when saving post model : " . ErrorHelper::model($post->getErrors()));
        }
    }

    /**
     * Get an post data from its site post id.
     *
     * @param integer $post_id The id of the post to check if it has any children.
     *
     * @return boolean does this post have any children.
     */
    public static function hasChildren($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT post_id
            FROM post
            WHERE parent = :post_id
            LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get an post_id data from its site_post_id.
     *
     * @param integer $site_post_id The id of the post in the data store that
     *      houses the stream that owns the post to delete.
     *
     * @return integer|false The post_id, or false if not found.
     */
    public static function getPostIdBySitePostId($site_post_id) {
        $connection = Yii::app()->db;
        $sql = "
            SELECT post_id
            FROM post
            WHERE site_post_id = :site_post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":site_post_id", $site_post_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        return $post_id;
    }

    /**
     * Deletes a row from the post table.
     *
     * Assumes that integrity checks have been made.
     *
     * @param integer $post_id The id of the post to delete.
     *
     * @return void
     */
    public static function deletePostRow($post_id) {
        $connection = Yii::app()->db;
        $sql = "
            DELETE
            FROM post
            WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Converts a flat array of posts with content into a nested array with one post row for multiple
     * content rows.
     *
     * @param type $posts The posts to convert These should follow the naming conventions of the protocol.
     *      Content rows should begin column names with 'content_'.
     *
     * @return array
     */
    private static function contevertPostsToJsonArray($posts) {
        $json_posts = array();
        $json_post = array();
        $current_post_id = 0;
        foreach ($posts as $post) {
            if ($current_post_id !== $post['post_id']) {
                if (empty($json_post) === false) {
                    // Append the finished post.
                    $json_posts[] = $json_post;
                    $current_post_id = $post['post_id'];
                }
                if ($current_post_id === 0) {
                    $current_post_id = $post['post_id'];
                }
                $json_post = array();
                // append the top level post data that is only needed once.
                if (empty($post['post_id']) === false) {
                    $json_post['post_id'] = $post['post_id'];
                }
                if (empty($post['parent_id']) === false) {
                    $json_post['parent_id'] = $post['parent_id'];
                }
                if (empty($post['top_parent_id']) === false) {
                    $json_post['top_parent_id'] = $post['top_parent_id'];
                }
                if (empty($post['stream_id']) === false) {
                    $json_post['stream_id'] = $post['stream_id'];
                }
                if (empty($post['stream_extra_id']) === false) {
                    $json_post['stream_extra_id'] = $post['stream_extra_id'];
                }
                if (empty($post['stream_child_id']) === false) {
                    $json_post['stream_child_id'] = $post['stream_child_id'];
                }
                if (isset($post['status']) === true) {
                    $json_post['status'] = LookupHelper::getValue($post['status']);
                }
                $json_post['timestamp'] = $post['timestamp'];
                $json_post['username'] = $post['username'];
                $json_post['domain'] = $post['domain'];
                $json_post['stream_domain'] = $post['stream_domain'];
                $json_post['stream_name'] = $post['stream_name'];
                $json_post['stream_username'] = $post['stream_username'];
                $json_post['stream_post_mode'] = $post['stream_post_mode'];
                $json_post['stream_version'] = $post['stream_version'];
                if (isset($post['revision']) === true) {
                    $json_post['revision'] = $post['revision'];
                }
                if (isset($post['content']) === true) {
                    $json_post['content'] = array();
                }
                // Start the array counter from one as the content array is one based.
            }
            if (isset($post['content_display_order']) === true) {
                $json_content = array();
                // Append the data for this row of content
                if (empty($post['content_text']) === false) {
                    $json_content['text'] = $post['content_text'];
                }
                if (empty($post['content_link']) === false) {
                    $json_content['link'] = $post['content_link'];
                }
                if (empty($post['content_link_title']) === false) {
                    $json_content['link_title'] = $post['content_link_title'];
                }
                if (empty($post['content_checked']) === false) {
                    if ($post['content_checked'] === '1') {
                        $json_content['checked'] = true;
                    } else {
                        $json_content['checked'] = false;
                    }
                }
                if (empty($post['content_selected']) === false) {
                    $selected_list = explode(',', $post['content_selected']);

                    $json_content['selected'] = $selected_list;

                }
                if (empty($post['content_value_max']) === false) {
                    $json_content['value_max'] = $post['content_value_max'];
                }
                if (empty($post['content_value_min']) === false) {
                    $json_content['value_min'] = $post['content_value_min'];
                }
                if (empty($post['content_display_order']) === false) {
                    $json_content['display_order'] = $post['content_display_order'];
                }
                // The index of the fields is basaed on the display order.
                $json_post['content'][$json_content['display_order']] = $json_content;
            }
        }
        // Append the final post in the data.
        if (empty($json_post) === false) {
            $json_posts[] = $json_post;
        }

        return $json_posts;
    }

    /**
     * Fetch all the child private posts for a child post.
     *
     * @param integer $top_parent_id The id of the post to fetch child posts for.
     * @param integer $timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getChildPrivatePosts($top_parent_id, $timestamp, $with_content,
        $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,stream_child.child_id AS stream_child_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN stream_child ON stream_extra.stream_extra_id = stream_child.parent_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
                LEFT JOIN post_private_recipient ON post.post_id = post_private_recipient.post_id
                    AND post_private_recipient.user_id = :user_id
            WHERE
                (post_private_recipient.user_id = :user_id
                    OR (
                        post.user_id = :user_id
                        AND post.status = :status_id
                    )
                )
                AND (
                    post_private_recipient.deleted != 1
                    OR post_private_recipient.deleted IS NULL
                )
                AND post.top_parent = :top_parent
                AND UNIX_TIMESTAMP(post.date_created) > :end_time
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.parent, post.date_created" . $sql_order . "
            LIMIT :qty";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":top_parent", $top_parent_id, PDO::PARAM_INT);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $end_time = date('Y-m-d H:i:s', $timestamp);
        $command->bindValue(":end_time", $end_time, PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'private');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval(Yii::app()->params['private_post_page_qty']), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch all the private posts in a stream.
     *
     * Fetches :
     *      All the private posts the logged in user has made.
     *      All the private posts that where sent to the logged in user.
     *      All the public posts that where deleted but this user has a child post or a take.
     *
     * @param integer $stream_extra_id The id of the stream to fetch posts for.
     * @param integer $timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getStreamPrivatePosts($stream_extra_id, $timestamp, $with_content,
        $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
                LEFT JOIN post_private_recipient ON post.post_id = post_private_recipient.post_id
                LEFT JOIN take ON post.post_id = take.post_id
                LEFT JOIN post AS post_child ON post.post_id = post_child.parent
            WHERE
                (post_private_recipient.user_id = :user_id
                    OR (
                        post.user_id = :user_id
                        AND post.status = :private_status_id
                    )
                    OR (
                        post.status = :deleted_status_id
                        AND take.value IS NOT NULL
                        AND take.user_id = :user_id
                        AND take.user_id != post.user_id
                    )
                    OR (
                        post.status = :deleted_status_id
                        AND post_child.post_id IS NOT NULL
                        AND post_child.user_id = :user_id
                        AND post_child.user_id != post.user_id
                    )
                )
                AND (
                    post_private_recipient.deleted != 1
                    OR post_private_recipient.deleted IS NULL
                )
                AND post.stream_extra_id = :stream_extra_id
                AND UNIX_TIMESTAMP(post.date_created) > :end_time
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created" . $sql_order . "
            LIMIT :qty";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $command->bindValue(":end_time", $timestamp, PDO::PARAM_INT);
        $command->bindValue(":private_status_id", LookupHelper::getID('post.status', 'private'), PDO::PARAM_INT);
        $command->bindValue(":deleted_status_id", LookupHelper::getID('post.status', 'deleted'), PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval(Yii::app()->params['private_post_page_qty']), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }


    /**
     * Fetch all the private posts for a user that have been sent from the client site that requested them.
     *
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results. Only used if this is a general request and
     *      not for stream or tree requests.
     * @param string $client_domain The domain of the client that has requested the posts.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getLocalPrivatePosts($oldest_timestamp, $newest_timestamp, $page, $client_domain,
        $with_content, $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user AS ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user AS o_user ON post.user_id = o_user.user_id
                INNER JOIN site AS o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
                INNER JOIN post_private_recipient ON post.post_id = post_private_recipient.post_id
                INNER JOIN site AS client_site ON post.site_id = client_site.site_id
            WHERE
                post_private_recipient.user_id = :user_id
                AND post_private_recipient.deleted != 1
                AND UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                AND client_site.domain = :client_domain
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $qty = Yii::app()->params['private_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'private');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $command->bindValue(":client_domain", $client_domain, PDO::PARAM_STR);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch private posts that have been sent by the logged on user from the client site that has requested them.
     *
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results. Only used if this is a general request and
     *      not for stream or tree requests.
     * @param string $client_domain The domain of the client that has requested the posts.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     * @fixme need to include the usernames of the people the post was sent to in all private posts that are retrieved.
     */
    public static function getSentLocalPosts($oldest_timestamp, $newest_timestamp, $page, $client_domain,
        $with_content, $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user AS ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user AS o_user ON post.user_id = o_user.user_id
                INNER JOIN site AS o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
                INNER JOIN site AS client_site ON post.site_id = client_site.site_id
            WHERE
                post.user_id = :user_id
                AND post.status != :deleted_status_id
                AND UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                AND client_site.domain = :client_domain
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $qty = Yii::app()->params['private_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $deleted_status_id = LookupHelper::getID('post.status', 'deleted');
        $command->bindValue(":deleted_status_id", $deleted_status_id, PDO::PARAM_INT);
        $command->bindValue(":client_domain", $client_domain, PDO::PARAM_STR);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch all the posts that have been sent by the logged in user. Private and public posts are fetched.
     *
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results. Only used if this is a general request and
     *      not for stream or tree requests.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     *
     * @return array The requested posts.
     */
    public static function getSentGlobalPosts($oldest_timestamp, $newest_timestamp, $page, $with_content,
        $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
            WHERE

                post.user_id = :user_id
                AND post.status != :deleted_status_id
                AND UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $qty = Yii::app()->params['private_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $deleted_status_id = LookupHelper::getID('post.status', 'deleted');
        $command->bindValue(":deleted_status_id", $deleted_status_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch a page of private posts for the logged in user.
     *
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results. Only used if this is a general request and
     *      not for stream or tree requests.
     * @param boolean $with_content Should the posts be fetched with their full content.
     *
     * @fixme all refferences to the current user need to be removed so these processes can be cached.
     *      Create a new protocol action to get a users posts that are cooling down.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getGlobalPrivatePosts($oldest_timestamp, $newest_timestamp, $page, $with_content,
        $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
                INNER JOIN post_private_recipient ON post.post_id = post_private_recipient.post_id
            WHERE
                post_private_recipient.user_id = :user_id
                AND post_private_recipient.deleted != 1
                AND UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $qty = Yii::app()->params['private_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'private');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch private posts for the logged on user.
     *
     * @param integer|false $stream_extra_id The id of the stream to fetch posts for or false if not
     *      restricted to a stream.
     * @param integer $post_id|false The id of the post to fetch child posts for. Or false if not
     *      restricted to child posts.
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results. Only used if this is a general request and
     *      not for stream or tree requests.
     * @param string The type of request being made. See actionGetPrivatePosts for valid types.
     * @param string $client_domain The domain of the client website that is requesting the posts.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getPrivatePosts($stream_extra_id, $post_id, $oldest_timestamp,
        $newest_timestamp, $page, $type, $client_domain, $with_content,
        $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        if ($post_id !== false) {
            $json_posts = self::getChildPrivatePosts(
                $post_id,
                $oldest_timestamp,
                $with_content,
                $search_phrase,
                $search_title,
                $search_other_fields
            );
        } else if ($stream_extra_id !== false) {
            $json_posts = self::getStreamPrivatePosts(
                $stream_extra_id,
                $oldest_timestamp,
                $with_content,
                $search_phrase,
                $search_title,
                $search_other_fields
            );
        } else if ($type  === 'global_private') {
            $json_posts = self::getGlobalPrivatePosts(
                $oldest_timestamp,
                $newest_timestamp,
                $page,
                $with_content,
                $search_phrase,
                $search_title,
                $search_other_fields
            );
        } else if ($type  === 'global_sent_private') {
            $json_posts = self::getSentGlobalPosts(
                $oldest_timestamp,
                $newest_timestamp,
                $page,
                $with_content,
                $search_phrase,
                $search_title,
                $search_other_fields
            );
        } else if ($type  === 'local_private') {
            $json_posts = self::getLocalPrivatePosts(
                $oldest_timestamp,
                $newest_timestamp,
                $page,
                $client_domain,
                $with_content,
                $search_phrase,
                $search_title,
                $search_other_fields
            );
        } else if ($type  === 'local_sent_private') {
            $json_posts = self::getSentLocalPosts(
                $oldest_timestamp,
                $newest_timestamp,
                $page,
                $client_domain,
                $with_content,
                $search_phrase,
                $search_title,
                $search_other_fields
            );
        } else {
            throw new Exception('Request to getPrivatePosts is not valid.');
        }
        return $json_posts;
    }

    /**
     * Checks if a user owns a child for an post.
     *
     * @param integer $user_id The id of the user we are checking.
     * @param integer $post_id The id of the post that we are checking for children.
     *
     * @return boolean
     */
    public static function doesOwnChild($user_id, $post_id) {
        $sql = "
            SELECT parent_post.post_id
            FROM
                post AS parent_post
                INNER JOIN post AS child_post ON parent_post.post_id = child_post.parent
            WHERE
                parent_post.post_id = :post_id
                AND child_post.user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        // @fixme need to refactor fetching for a time - needs to be between two times.
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $child_post_id = $command->queryScalar();
        if ($child_post_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Fetch the number of messages that are waiting for a user since they last checked their inbox.
     *
     * @param integer $user_id The id of the user to check.
     * @param string $domain The domain of the client site making the request, or null for the global inbox.
     *
     * @return array Ready to convert ot json with two paramaters : 'local' and 'global'.
     *
     * @note This process is not ideal. If a local inbox is viewed. the messages still count towards the
     * global inbox. Could set a flag on each message but that would be expensive.
     */
    public static function fetchWaitingPostCount($user_id, $domain) {
        $site_id = Site::getSiteId($domain);
        $time_private_client_updated = WaitingPostTime::fetchTime($user_id, $site_id, 'private');
        $private_client_qty = self::fetchQtyOfClientRecievedPrivatePostsSinceTime(
            $user_id,
            $time_private_client_updated,
            $site_id
        );
        $time_private_global_updated = WaitingPostTime::fetchTime($user_id, null, 'private');
        $private_global_qty = self::fetchQtyOfGlobalRecievedPrivatePostsSinceTime(
            $user_id,
            $time_private_global_updated
        );
        $time_public_client_updated = WaitingPostTime::fetchTime($user_id, $site_id, 'public');
        $public_client_qty = self::fetchQtyOfClientRecievedPublicPostsSinceTime(
            $user_id,
            $time_public_client_updated,
            $site_id
        );
        $time_public_global_updated = WaitingPostTime::fetchTime($user_id, null, 'public');
        $public_global_qty = self::fetchQtyOfGlobalRecievedPublicPostsSinceTime(
            $user_id,
            $time_public_global_updated,
            $site_id
        );

        $json_data = array(
            'private_client' => array(
                'qty' => $private_client_qty,
                'timestamp' => $time_private_client_updated,
            ),
            'private_global' => array(
                'qty' => $private_global_qty,
                'timestamp' => $time_private_global_updated,
            ),
            'public_client' => array(
                'qty' => $public_client_qty,
                'timestamp' => $time_public_client_updated,
            ),
            'public_global' => array(
                'qty' => $public_global_qty,
                'timestamp' => $time_public_global_updated,
            ),
        );
        return $json_data;
    }

    /**
     * Fetch the number of private posts waiting for a user to view in their global inbox.
     *
     * @param integer $user_id The id of the user to check.
     * @param integer $time_viewed The time this inbox was last viewed.
     * @param string $site_id The id of the client site whose inbox a count is being fetched for.
     *
     * @return number
     */
    private static function fetchQtyOfGlobalRecievedPrivatePostsSinceTime($user_id, $time_viewed) {
        $sql = "
            SELECT
                 COUNT(post.post_id)
            FROM
                post
                INNER JOIN post_private_recipient ON post.post_id = post_private_recipient.post_id
            WHERE
                post_private_recipient.user_id = :user_id
                AND post_private_recipient.deleted != 1
                AND UNIX_TIMESTAMP(post.date_created) > :time_viewed
                AND (post.date_created < :cooldown OR post.user_id = :user_id)";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":time_viewed", $time_viewed, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $post_count = $command->queryScalar();
        return $post_count;
    }

    /**
     * Fetch the number of posts that have made in response to posts made by this user (excluding private posts).
     *
     * Only posts specific to the given client website
     *
     * @param integer $user_id The id of the user to check.
     * @param integer $time_viewed The time this inbox was last viewed.
     * @param string $site_id The id of the client site whose inbox a count is being fetched for.
     *
     * @return number
     */
    private static function fetchQtyOfGlobalRecievedPublicPostsSinceTime($user_id, $time_viewed, $site_id) {
        $sql = "
            SELECT
                 COUNT(other_post.post_id)
            FROM
                post
                INNER JOIN post AS other_post ON post.post_id = other_post.parent
            WHERE
                post.user_id = :user_id
                AND other_post.status = :public_status
                AND post.status = :public_status
                AND UNIX_TIMESTAMP(other_post.date_created) > :time_viewed
                AND other_post.date_created < :cooldown
                AND other_post.user_id != :user_id
                AND post.site_id != :site_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":time_viewed", $time_viewed, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":public_status", LookupHelper::getID('post.status', 'public'), PDO::PARAM_INT);
        $post_count = $command->queryScalar();
        return $post_count;
    }

    /**
     * Fetch the number of private posts waiting for a user to view in a client inbox.
     *
     * @param integer $user_id The id of the user to check.
     * @param integer $time_viewed The time this inbox was last viewed.
     * @param string $site_id The id of the client site whose inbox a count is being fetched for.
     *
     * @return number
     */
    private static function fetchQtyOfClientRecievedPrivatePostsSinceTime($user_id, $time_viewed, $site_id) {
        $sql = "
            SELECT
                 COUNT(post.post_id)
            FROM
                post
                INNER JOIN post_private_recipient ON post.post_id = post_private_recipient.post_id
            WHERE
                post_private_recipient.user_id = :user_id
                AND post_private_recipient.deleted != 1
                AND UNIX_TIMESTAMP(post.date_created) > :time_viewed
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                AND post.site_id = :site_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":time_viewed", $time_viewed, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $post_count = $command->queryScalar();
        return $post_count;
    }

    /**
     * Fetch the number of posts that have made in response to posts made by this user (excluding private posts).
     *
     * Only posts specific to the given client website
     *
     * @param integer $user_id The id of the user to check.
     * @param integer $time_viewed The time this inbox was last viewed.
     * @param string $site_id The id of the client site whose inbox a count is being fetched for.
     *
     * @return number
     */
    private static function fetchQtyOfClientRecievedPublicPostsSinceTime($user_id, $time_viewed, $site_id) {
        $sql = "
            SELECT
                 COUNT(other_post.post_id)
            FROM
                post
                INNER JOIN post AS other_post ON post.post_id = other_post.parent
            WHERE
                post.user_id = :user_id
                AND other_post.status = :public_status
                AND post.status = :public_status
                AND UNIX_TIMESTAMP(other_post.date_created) > :time_viewed
                AND other_post.date_created < :cooldown
                AND other_post.user_id != :user_id
                AND post.site_id = :site_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":time_viewed", $time_viewed, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":public_status", LookupHelper::getID('post.status', 'public'), PDO::PARAM_INT);
        $post_count = $command->queryScalar();
        return $post_count;
    }

    /**
     * Fetches the most recent revision number for an post.
     *
     * @param integer $post_id The id of the post to fetch a revision number for.
     *
     * @return integer The revision number, zero if not found.
     */
    public static function getLatestRevisionNumber($post_id) {
        $sql = "
            SELECT revision
            FROM post_content
            WHERE post_id = :post_id
            ORDER BY revision DESC
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id === false) {
            $post_id = 0;
        }
        return $post_id;
    }

    /**
     * Checks if an post revision number is available.
     *
     * Used when storing an post and this domain is not the domain of the stream that the post is in.
     *
     * @param integer $post_id The id of the post to check a revision number for.
     * @param integer $revision The revision number to check
     *
     * @return boolean True if the revision number has not been used.
     */
    public static function checkRevisionAvailable($post_id, $revision) {
        $sql = "
            SELECT post_id
            FROM post_content
            WHERE
                post_id = :post_id
                AND revision = :revision
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":revision", $revision, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id === false) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Fetches the local post_id for a remote domain
     *
     * Used when storing an post and this domain is not the domain of the stream that the post is in.
     *
     * @param integer $post_id The id of the post to check a revision number for.
     * @param integer $revision The revision number to check
     *
     * @return integer|false False if not found.
     */
    public static function getLocalPostId($site_id, $site_post_id) {
        $sql = "
            SELECT post_id
            FROM post
            WHERE
                site_id = :site_id
                AND site_post_id = :site_post_id
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":site_id", $site_id, PDO::PARAM_INT);
        $command->bindValue(":site_post_id", $site_post_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        return $post_id;
    }

    /**
     * Checks if an post is local by comparing its id to its site_post_id
     *
     * @param integer $post_id The id of the post to check.
     *
     * @return boolean
     */
    public static function isLocal($post_id) {
        $sql = "
            SELECT post_id
            FROM post
            WHERE
                post_id = :post_id
                AND site_post_id = :post_id
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkExists($post_id) {
        $sql = "
            SELECT post_id
            FROM post
            WHERE
                post_id = :post_id
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if many post ids exist in one sql query.
     *
     * Must use full equals (===) and not equivilant (==) for this to work.
     *
     * @param array post_ids Both key and value should be the post id.
     *
     * @return boolean|integer success or the id of the post that failed.
     */
    public static function checkIfManyExist($post_ids) {
        $sql = "
            SELECT post_id
            FROM post
            WHERE post_id in (:post_ids)";
        $command = Yii::app()->db->createCommand($sql);
        $offfer_id_string = implode(',', $post_ids);
        $command->bindValue(":post_ids", $offfer_id_string, PDO::PARAM_INT);
        $rows = $command->queryAll();

        foreach ($rows as $row) {
            if (isset($post_ids[$row['post_id']]) === false) {
                return $row['post_id'];
            }
        }
        return true;
    }

    /**
     * Fetch a page of private posts for the logged in user.
     *
     * @param $user_id The id of the user whose posts are being fetched.
     * @param $top_parent_post_id The id of the top parent post that posts should be returned from.
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getTreePostsForUser($user_id, $top_parent_post_id, $oldest_timestamp,
        $newest_timestamp, $page, $with_content, $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order= "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }

        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
            WHERE
                UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                AND post.user_id = :user_id
                AND post.parent = :top_parent_post_id
                AND post.status = :status_id
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":public_status", LookupHelper::getID('post.status', 'public'), PDO::PARAM_INT);
        $qty = Yii::app()->params['public_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'public');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":top_parent_post_id", $top_parent_post_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch a page of posts for the logged in user.
     *
     * @param integer $user_id The id of the user whose posts are being fetched.
     * @param integer $stream_extra_id The extra id of the stream that posts should be returned from.
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string|boolean [$status=false] The status of the posts.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getStreamPostsForUser($user_id, $stream_extra_id, $oldest_timestamp, $newest_timestamp,
        $page, $with_content, $status=false, $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }
        $sql_status = "";
        if ($status !== false) {
            $sql_status .= ",post.status";
        }

        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                " . $sql_status . "
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
            WHERE
                UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                AND post.user_id = :user_id
                AND post.stream_extra_id = :stream_extra_id ";
        if ($status !== false) {
            $sql .= " AND post.status = :status_id ";
        }
        $sql .= "AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":public_status", LookupHelper::getID('post.status', 'public'), PDO::PARAM_INT);
        $qty = Yii::app()->params['public_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        if ($status !== false) {
            $status_id = LookupHelper::getID('post.status', $status);
            $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        }
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Fetch a page of public posts made in response to posts the given user made.
     *
     * @param $user_id The id of the user whose posts are being fetched.
     * @param integer $oldest_timestamp|false A time to fetch posts upto, or false if not restricted by time.
     * @param integer $newest_timestamp A time to fetch posts from
     * @param integer $page The page number of results.
     * @param boolean $with_content Should the posts be fetched with their full content.
     * @param string [$search_phrase] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     * @param boolean [$search_title] If set then the results will be searched for this phrase.
     * @param boolean [$search_other_fields] If search_phrase is set,
     *      this decides if the fields other than the title should be searched.
     *
     * @return array The requested posts.
     */
    public static function getPublicPostResponses($user_id, $oldest_timestamp, $newest_timestamp,
        $page, $with_content, $search_phrase=null, $search_title=null, $search_other_fields=null
    ) {
        $sql_content = "";
        $sql_from = "";
        $sql_order = "";
        $sql_where = "";
        $include_search_phrase = false;
        if ($with_content === true) {
            $sql_content = "
                ,post_content.text AS content_text
                ,post_content.link AS content_link
                ,post_content.link_title AS content_link_title
                ,post_content.checked AS content_checked
                ,post_content.selected AS content_selected
                ,post_content.value_max AS content_value_max
                ,post_content.value_min AS content_value_min
                ,post_content.display_order AS content_display_order
                ,post_content.revision";
            $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
            $sql_where = "
                AND post_content.revision = (
                    SELECT max(revision) FROM post_content WHERE post_id = post.post_id
                )";

            if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
                if ($search_title === true && $search_other_fields === true) {
                    $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                } else if ($search_title === true && $search_other_fields === false) {
                    $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                    $include_search_phrase = true;
                }
            }

            $sql_order = ", post_content.display_order";
        }

        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                INNER JOIN post AS post_parent ON post.parent = post_parent.post_id
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
            WHERE
                UNIX_TIMESTAMP(post.date_created) > :oldest_time
                AND UNIX_TIMESTAMP(post.date_created) < :newest_time
                AND post_parent.user_id = :user_id
                AND post.status = :status_id
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($oldest_timestamp === false) {
            $oldest_timestamp = 0;
        }
        $command->bindValue(":oldest_time", $oldest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":newest_time", $newest_timestamp, PDO::PARAM_INT);
        $command->bindValue(":public_status", LookupHelper::getID('post.status', 'public'), PDO::PARAM_INT);
        $qty = Yii::app()->params['public_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'public');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Search a tree of posts for a particular search phrase.
     *
     * @param integer $top_post_id The id of the top parent post in the tree.
     * @param integer [$from_timestamp] A time to fetch posts from.
     * @param integer [$to_timestamp] A time to fetch posts upto.
     * @param string [$search_phrase] The phrase to search for.
     * @param boolean [$search_title] If set then the title will be searched for this phrase.
     * @param boolean [$search_other_fields] Decides if fields other than the title should be searched.
     * @param integer [$page] The page of results to fetch.
     *
     * @return array An array of posts.
     */
    public static function getTreeSearch($top_post_id, $from_timestamp, $to_timestamp,
        $search_phrase, $search_title, $search_other_fields, $page
    ) {
        $sql_content = "
            ,post_content.text AS content_text
            ,post_content.link AS content_link
            ,post_content.link_title AS content_link_title
            ,post_content.checked AS content_checked
            ,post_content.selected AS content_selected
            ,post_content.value_max AS content_value_max
            ,post_content.value_min AS content_value_min
            ,post_content.display_order AS content_display_order
            ,post_content.revision";
        $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
        $sql_where = "
            AND post_content.revision = (
                SELECT max(revision) FROM post_content WHERE post_id = post.post_id
            )";

        $include_search_phrase = false;
        if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
            if ($search_title === true && $search_other_fields === true) {
                $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                $include_search_phrase = true;
            } else if ($search_title === true && $search_other_fields === false) {
                $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                $include_search_phrase = true;
            }
        }

        $sql_order = ", post_content.display_order";

        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                INNER JOIN post AS post_parent ON post.parent = post_parent.post_id
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
            WHERE
                UNIX_TIMESTAMP(post.date_created) > :from_timestamp
                AND UNIX_TIMESTAMP(post.date_created) < :to_timestamp
                AND post.top_parent = :top_post_id
                AND post.status = :status_id
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($from_timestamp === false) {
            $from_timestamp = 0;
        }
        if ($to_timestamp === false) {
            $to_timestamp = time();
        }
        $command->bindValue(":top_post_id", $top_post_id, PDO::PARAM_INT);
        $command->bindValue(":from_timestamp", $from_timestamp, PDO::PARAM_INT);
        $command->bindValue(":to_timestamp", $to_timestamp, PDO::PARAM_INT);

        $qty = Yii::app()->params['search_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'public');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }


    /**
     * Search a stream of posts for a particular search phrase.
     *
     * @param integer $stream_extra_id The extra id of the stream to search.
     * @param integer [$from_timestamp] A time to fetch posts from.
     * @param integer [$to_timestamp] A time to fetch posts upto.
     * @param string [$search_phrase] The phrase to search for.
     * @param boolean [$search_title] If set then the title will be searched for this phrase.
     * @param boolean [$search_other_fields] Decides if fields other than the title should be searched.
     * @param integer [$page] The page of results to fetch.
     *
     * @return array An array of posts.
     */
    public static function getStreamSearch($stream_extra_id, $from_timestamp, $to_timestamp,
        $search_phrase, $search_title, $search_other_fields, $page
    ) {
        $sql_content = "
            ,post_content.text AS content_text
            ,post_content.link AS content_link
            ,post_content.link_title AS content_link_title
            ,post_content.checked AS content_checked
            ,post_content.selected AS content_selected
            ,post_content.value_max AS content_value_max
            ,post_content.value_min AS content_value_min
            ,post_content.display_order AS content_display_order
            ,post_content.revision";
        $sql_from = "INNER JOIN post_content ON post_content.post_id = post.post_id";
        $sql_where = "
            AND post_content.revision = (
                SELECT max(revision) FROM post_content WHERE post_id = post.post_id
            )";

        $include_search_phrase = false;
        if (isset($search_phrase) === true  && strlen($search_phrase) > 3) {
            if ($search_title === true && $search_other_fields === true) {
                $sql_where .= " AND MATCH(text) AGAINST(:search_phrase)";
                $include_search_phrase = true;
            } else if ($search_title === true && $search_other_fields === false) {
                $sql_where .= " AND post_content.display_order = 1 AND MATCH(text) AGAINST(:search_phrase)";
                $include_search_phrase = true;
            }
        }

        $sql_order = ", post_content.display_order";

        $sql = "
            SELECT
                 post.post_id AS post_id
                ,post.parent AS parent_id
                ,post.top_parent AS top_parent_id
                ,UNIX_TIMESTAMP(post.date_created) AS timestamp
                ,o_user.username AS username
                ,o_site.domain AS domain
                ,o_site.domain AS stream_domain
                ,stream.name AS stream_name
                ,ot_user.username AS stream_username
                ,post_mode_lookup.value AS stream_post_mode
                ,stream_extra.stream_extra_id AS stream_id
                ,CONCAT(version.major, '/', version.minor, '/', version.patch) AS stream_version
                ,post.status
                " . $sql_content . "
            FROM
                post
                " . $sql_from . "
                INNER JOIN stream_extra ON post.stream_extra_id = stream_extra.stream_extra_id
                INNER JOIN stream ON stream_extra.stream_id = stream.stream_id
                INNER JOIN version ON stream_extra.version_id = version.version_id
                INNER JOIN user as ot_user ON stream.user_id = ot_user.user_id
                INNER JOIN user as o_user ON post.user_id = o_user.user_id
                INNER JOIN site as o_site ON o_user.site_id = o_site.site_id
                INNER JOIN lookup AS post_mode_lookup ON post_mode_lookup.lookup_id = stream_extra.post_mode
            WHERE
                UNIX_TIMESTAMP(post.date_created) > :from_timestamp
                AND UNIX_TIMESTAMP(post.date_created) < :to_timestamp
                AND post.stream_extra_id = :stream_extra_id
                AND post.status = :status_id
                AND (post.date_created < :cooldown OR post.user_id = :user_id)
                " . $sql_where . "
            ORDER BY post.date_created DESC, post.post_id" . $sql_order . "
            LIMIT :limit_start, :qty";
        $command = Yii::app()->db->createCommand($sql);
        if ($from_timestamp === false) {
            $from_timestamp = 0;
        }
        if ($to_timestamp === false) {
            $to_timestamp = time();
        }
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":from_timestamp", $from_timestamp, PDO::PARAM_INT);
        $command->bindValue(":to_timestamp", $to_timestamp, PDO::PARAM_INT);

        $qty = Yii::app()->params['search_post_page_qty'];
        $command->bindValue(":limit_start", intval(($page - 1) * $qty), PDO::PARAM_INT);
        $status_id = LookupHelper::getID('post.status', 'public');
        $command->bindValue(":status_id", $status_id, PDO::PARAM_INT);
        $cooldown = date('Y-m-d H:i:s', time() - Yii::app()->params['post_cooldown']);
        $command->bindValue(":cooldown", $cooldown, PDO::PARAM_INT);
        $command->bindValue(":user_id", Yii::app()->user->getId(), PDO::PARAM_INT);
        $command->bindValue(":qty", intval($qty), PDO::PARAM_INT);
        if ($include_search_phrase === true) {
            $command->bindValue(":search_phrase", $search_phrase, PDO::PARAM_STR);
        }

        $posts = $command->queryAll();

        // Convert the posts to json format
        $json_posts = self::contevertPostsToJsonArray($posts);

        return $json_posts;
    }

    /**
     * Checks if posts have been made that are not by the streamowner.
     *
     * @param integer $stream_extra_id The extra id of the stream that posts being searched for are in.
     * @param integer $owner_user_id The id of the owner of the stream.
     *
     * @return Boolean True if there are posts not made by the stream owner.
     */
    public static function areTherePostsNotByOwner($stream_extra_id, $owner_user_id) {
        $sql = "
            SELECT post_id
            FROM post
            WHERE
                stream_extra_id = :stream_extra_id
                AND user_id != :owner_user_id
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $command->bindValue(":owner_user_id", $owner_user_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id === false) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Deletes all posts by the owner of a stream.
     *
     * @param integer $stream_extra_id The extra id of the stream that posts being deleted in.
     * @param integer $owner_user_id The id of the owner of the stream.
     *
     * @return void
     */
    public static function deleteStreamOwnerPosts($stream_extra_id, $owner_user_id) {
        Post::model()->deleteAll(
            array(
                'condition' => 'stream_extra_id=:stream_extra_id and user_id=:owner_user_id',
                'params' => array(
                    ':stream_extra_id' => $stream_extra_id,
                    ':owner_user_id' => $owner_user_id,
                )
            )
        );
    }

    /**
     * Are there any posts in a stream.
     *
     * @param $stream_extra_id The extra id of the stream to check if there are posts.
     *
     * @return boolean
     */
    public static function areTherePostsInStream($stream_extra_id) {
          $sql = "
            SELECT post_id
            FROM post
            WHERE
                stream_extra_id = :stream_extra_id
            LIMIT 1";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $post_id = $command->queryScalar();
        if ($post_id === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Recalculates the child count for all the parents of an post.
     *
     * @param string $post_id
     *
     * @return void
     */
    public static function recalculateChildCountForAncestors($post_id) {
        $ancestors = PostDescendent::getAllAncestorIds($post_id);
        foreach ($ancestors as $ancestor) {
            $child_count = PostDescendent::getChildCount($ancestor['ancestor_post_id']);
            Post::updateChildCount($ancestor['ancestor_post_id'], $child_count);
        }
    }

    public static function updateChildCount($post_id, $child_count) {
        if (ctype_digit($child_count) === false) {
            throw new Exception('Child count must be a positive integer');
        }
        $sql = "
            UPDATE post
            SET child_count = :child_count
            WHERE post_id = :post_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->bindValue(":child_count", $child_count, PDO::PARAM_INT);
        $command->execute();
    }

    public static function getAllPostIds() {
        $sql = "
            SELECT post_id
            FROM post";
        $command = Yii::app()->db->createCommand($sql);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Fetch all the post_id entries for a user.
     *
     * @param integer $user_id The id of the user that post ids are being fetched for.
     *
     * @return array
     */
    public static function getAllPostIdsForUser($user_id) {
        $sql = "
            SELECT post_id
            FROM post
            WHERE user_id = :user_id";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $post_ids = $command->queryColumn();
        return $post_ids;
    }

    /**
     * Delete a post by its post_id.
     *
     * Note: only call this from DeleteMulti as it has dependent child rows connected with a foreign key.
     *
     * @param integer $post_id The id of the take used to delete this take.
     *
     * @return void
     */
    public static function deleteByPostId($post_id) {
        try {
            $connection = Yii::app()->db;
            $sql = "
                DELETE
                FROM post
                WHERE post_id = :post_id";
            $command = $connection->createCommand($sql);
            $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
            $command->execute();

        } catch (Exception $e) {
            throw new Exception(
                'Post::deleteByPostId should be called from DeleteMulti to ensure that all child '
                . 'records connected with a foreign key are also deleted.' . $e
            );
        }
    }

    /**
     * Fetches all the posts values for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that post ids are being fetched for.
     *
     * @return array
     */
    public static function getAllPostIdsForStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT post_id
                FROM post
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $post_ids = $command->queryColumn();
        return $post_ids;
    }

    /**
     * Marks all child posts as having no parent.
     *
     * @param integer $post_id The parent post_id to use in marking child posts as having a deleted parent.
     *
     * @return void
     */
    public static function markChildPostsWithDeletedParent($post_id) {
        $connection = Yii::app()->db;
        $sql = "UPDATE post
                SET parent = NULL
                WHERE parent = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Marks all child posts of a top parent as having no top parent.
     *
     * @param integer $post_id The top_parent post_id to use in marking child posts as having a deleted top_parent.
     *
     * @return void
     */
    public static function markChildPostsWithDeletedTopParent($post_id) {
        $connection = Yii::app()->db;
        $sql = "UPDATE post
                SET top_parent = NULL
                WHERE top_parent = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Select a row of post data.
     *
     * @param type $post_id The id of the post to select a row of data for.
     *
     * @return array. Indexed by column name.
     */
    public static function getRowByPostId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $post_row = $command->queryRow();
        return $post_row;
    }

    /**
     * Get the post_id for this post on its home domain.
     *
     * @param integer $post_id The id of the post we are getting a home post_id for.
     *
     * @return string
     */
    public static function getHomeId($post_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT site_post_id
                FROM post
                WHERE post_id = :post_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":post_id", $post_id, PDO::PARAM_INT);
        $home_post_id = $command->queryScalar();
        return $home_post_id;
    }

    /**
     * Select rows of post data for a stream_extra_id.
     *
     * @param type $stream_extra_id The extra id of the stream to select data for.
     *
     * @return array
     */
    public static function getRowsForStreamExtraId($stream_extra_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post
                WHERE stream_extra_id = :stream_extra_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":stream_extra_id", $stream_extra_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

    /**
     * Select rows of post data for a user_id.
     *
     * @param type $user_id The id of the user to select data for.
     *
     * @return array
     */
    public static function getRowsForUserId($user_id) {
        $connection = Yii::app()->db;
        $sql = "SELECT *
                FROM post
                WHERE user_id = :user_id";
        $command = $connection->createCommand($sql);
        $command->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $rows = $command->queryAll();
        return $rows;
    }

}

?>