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
 * This class handles database deletions.
 *
 * !!!!!!!     WARNING       !!!!!!!!!
 * Be carfull when calling this class directly as it completely removes all indexes and relationships.
 * If you want to remove data but keep indexes then use ScrubMulti (@todo).
 *
 * @package PHP_Model_Multi
 */
class DeleteMulti
{
    /**
     * @var CDbTransaction
     */
    private $transaction;

    /**
     * @var integer
     */
    private $transaction_layer_count = 0;

    /**
     * @var array
     */
    private $transaction_errors = array();


    /**
     * All DB processes in this class occour in this single transaction.
     *
     * Call this method whenever a transaction might start. Call endTransaction when it should end.
     * When all called transactions have ended then the real transaction will run.
     *
     * @return void
     */
    private function startTransaction($error_message) {
        $this->transaction_layer_count++;
        $this->transaction_errors[] = $error_message;
        if (isset($this->transaction) === false) {
            $this->transaction = Yii::app()->db->beginTransaction();
        }
    }

    private function endTransaction() {
        $this->transaction_layer_count--;
        if ($this->transaction_layer_count === 0) {
            try {
                $this->transaction->commit();
                $this->transaction = null;
            } catch (Exception $e) {
                $this->transaction->rollBack();
                throw new Exception(
                    'There was an exception when commiting the DeleteMulti transaction</br/><br/>' . $e
                    . explode('<br/>', $this->transaction_errors)
                );
            }
        }
    }

    /**
     * Deletes the takes data for this post.
     *
     * @param integer $post_id The id of the post in takes that is being deleted.
     *
     * @return void
     */
    private function deletePostsTakes($post_id) {
        $take_ids = Take::getTakeIdsForPost($post_id);
        foreach ($take_ids as $take_id) {
            $this->deleteTake($take_id);
        }
    }

    /**
     * Deletes all the posts made by a user
     *
     * @param integer $user_id The id of the user whose posts are being deleted.
     *
     * @return void
     */
    private function deleteUsersPosts($user_id) {
        $post_ids = Post::getAllPostIdsForUser($user_id);
        foreach ($post_ids as $post_id) {
            $this->deletePost($post_id);
        }
    }

    /**
     * Deletes the meta post data for a user_id.
     *
     * @param integer $user_id The id of the user whose meta post data is being deleted.
     *
     * @return void
     */
    private function deleteUsersMetaPost($user_id) {
        $meta_post_id = User::getMetaPostId($user_id);
        User::setMetaPostToNull($user_id);
        $this->deletePost($meta_post_id);
    }

    /**
     * Delete all user_rhythm data for a rhythm extra.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm to delete user_rhtyhm data for.
     *
     * @return void
     */
    public function deleteRhythmsUserRhythms($rhythm_extra_id) {
        $this->startTransaction('There was an exception when deleting a take(' . $rhythm_extra_id . '). ');

        $user_rhythm_ids = UserRhythm::getIdsForRhythmExtraId($rhythm_extra_id);
        foreach ($user_rhythm_ids as $user_rhythm_id) {
            TakeKindred::deletebyUserRhythmId($user_rhythm_id);
            Kindred::deleteByUserRhythmId($user_rhythm_id);
        }

        UserRhythm::deleteByRhythmExtraId($rhythm_extra_id);

        $this->endTransaction();
    }

    /**
     * Delete a rhythm_extra row and its associated child data.
     *
     * @param integer $rhythm_extra_id The extra id of the rhythm to delete rhythm_extra data for.
     *
     * @return void
     */
    public function deleteRhythmByRhythmExtraId($rhythm_extra_id) {
        $this->startTransaction('There was an exception when deleting a rhythm_extra row(' . $rhythm_extra_id . '). ');

        RhythmParam::deleteByRhythmExtraId($rhythm_extra_id);
        SuggestionsDeclined::deleteByDeclinedStreamExtraId($rhythm_extra_id);
        UserStreamSubscriptionFilter::deleteByRhythmExtraId($rhythm_extra_id);
        StreamPublic::deleteByRhythmExtraId($rhythm_extra_id);
        StreamPublicRhythm::deleteByRhythmExtraId($rhythm_extra_id);
        StreamDefaultRhythm::deleteByRhythmExtraId($rhythm_extra_id);
        RhythmUserData::deleteByRhythmExtraId($rhythm_extra_id);

        $this->deleteRhythmsUserRhythms($rhythm_extra_id);

        // set these to null rather than removing them as a ring should not be deleted just
        // because one of its rhythms is.
        Ring::removeRingRhythm($rhythm_extra_id);
        Ring::removeMembershipRhythm($rhythm_extra_id);

        RhythmExtra::deleteByRhythmExtraId($rhythm_extra_id);

        $this->endTransaction();
    }

    /**
     * Delete all rhythm_extra data for a rhythm.
     *
     * @param integer $rhythm_id The id of the rhythm to delete rhythm_extra data for.
     *
     * @return void
     */
    public function deleteRhythm($rhythm_id) {
        $this->startTransaction('There was an exception when deleting a take(' . $rhythm_id . '). ');

        $rhythm_extra_ids = RhythmExtra::getIdsForRhythmId($rhythm_id);
        foreach ($rhythm_extra_ids as $rhythm_extra_id) {
            $this->deleteRhythmByRhythmExtraId($rhythm_extra_id);
        }

        Rhythm::deleteByRhythmId($rhythm_id);

        $this->endTransaction();
    }

    /**
     * Delete all rhythms created by a user.
     *
     * @param integer $user_id The id of the user to delete rhythms for.
     *
     * @return void
     */
    public function deleteUsersRhythms($user_id) {
        $this->startTransaction('There was an exception when deleting a users(' . $user_id . ') rhythms. ');

        $rhythm_ids = Rhythm::getRhythmsForUserId($user_id);
        foreach ($rhythm_ids as $rhythm_id) {
            $this->deleteRhythm($rhythm_id);
        }

        $this->endTransaction();
    }

    /**
     * Delete all data associated with a take.
     *
     * @param integer $take_id The id of the take to delete.
     *
     * @return void
     */
    public function deleteTake($take_id) {
        $this->startTransaction('There was an exception when deleting a take(' . $take_id . '). ');

        UserTake::deleteByTakeId($take_id);
        TakeKindred::deleteByTakeId($take_id);
        Take::deleteByTakeId($take_id);

        $this->endTransaction();
    }

    /**
     * Delete a post and all its dependencies.
     *
     * Note: does not delete a post if it is a meta post for a user, stream or rhythm.
     *
     * @param integer $post_id The id of the post that is being deleted.
     *
     * @return void
     */
    public function deletePost($post_id) {
        $this->startTransaction('There was an exception when deleting a post(' . $post_id . '). ');

        Post::markChildPostsWithDeletedParent($post_id);
        Post::markChildPostsWithDeletedTopParent($post_id);
        User::setMetaPostToNullForPostId($post_id);
        StreamExtra::setMetaPostToNullForPostId($post_id);
        RhythmExtra::setMetaPostToNullForPostId($post_id);

        RingUserTake::deleteByPostId($post_id);
        StreamDefaultRhythm::deleteByTopParentId($post_id);
        PostUser::deleteByPostId($post_id);
        StreamPublic::deleteByPostId($post_id);
        StreamPublic::deleteByTopParentId($post_id);
        PostPrivateRecipient::deleteByPostId($post_id);
        PostDescendent::deleteDescendentByAncestorPostId($post_id);
        PostDescendent::deleteDescendentByDescendentPostId($post_id);
        UserTake::deleteByPostId($post_id);
        PostContent::deleteByPostId($post_id);
        StreamBlockTree::deleteByPostId($post_id);

        $this->deletePostsTakes($post_id);

        Post::deleteByPostId($post_id);

        $this->endTransaction();
    }

    /**
     * Deletes all posts that are in a stream_extra.
     *
     * @param integer $stream_extra_id The extra id of the stream that posts are being deleted from.
     *
     * @return void
     */
    public function deletePostsForStreamExtra($stream_extra_id) {
        $this->startTransaction(
            'There was an exception when deleting posts for a stream_extra_id(' . $stream_extra_id . '). '
        );

        $post_ids = Post::getAllPostIdsForStreamExtraId($stream_extra_id);
        foreach ($post_ids as $post_id) {
            $this->deletePost($post_id);
        }

        $this->endTransaction();
    }

    /**
     * Deletes all user stream subscription from a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that stream subscriptions are being deleted from.
     *
     * @return void
     */
    public function deleteUserStreamSubscriptionsByStreamExtraId($stream_extra_id) {
        $this->startTransaction(
            'There was an exception when deleting user_stream_subscriptions '
            . 'for a stream_extra_id(' . $stream_extra_id . '). '
        );

        $user_stream_subscription_ids = UserStreamSubscription::getUserStreamSubscriptionIdsForStreamExtraID(
            $stream_extra_id
        );
        foreach ($user_stream_subscription_ids as $user_stream_subscription_id) {
            UserStreamSubscriptionFilter::deleteByUserStreamSubscriptionId($user_stream_subscription_id);
            UserStreamSubscriptionRing::deleteByUserStreamSubscriptionId($user_stream_subscription_id);
        }
        UserStreamSubscription::deleteByStreamExtraID($stream_extra_id);



        $this->endTransaction();
    }

    /**
     * Deletes the take rows for this stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that takes that are being deleted for.
     *
     * @return void
     */
    private function deleteTakesByStreamExtraId($stream_extra_id) {
        $take_ids = Take::getTakeIdsForStreamExtraId($stream_extra_id);
        foreach ($take_ids as $take_id) {
            $this->deleteTake($take_id);
        }
    }

    /**
     * Deletes a single stream_field.
     *
     * @param integer $stream_field_id The id of the stream field that is being deleted.
     *
     * @return Boolean Was a field deleted or not.
     */
    public function deleteStreamFieldByStreamFieldId($stream_field_id) {
        $this->startTransaction(
            'There was an exception when deleting a user_stream_field '
            . 'for a stream_field_id(' . $stream_field_id . ').'
        );

        TakeValueList::deleteByStreamFieldId($stream_field_id);
        StreamOpenListItem::deleteByStreamFieldId($stream_field_id);
        StreamList::deleteByStreamFieldId($stream_field_id);

        $deleted = StreamField::deleteByStreamFieldId($stream_field_id);

        $this->endTransaction();

        return $deleted;
    }

    /**
     * Deletes all stream fields for a stream_extra_id.
     *
     * @param integer $stream_extra_id The extra id of the stream that stream_field rows are being deleted for.
     *
     * @return void
     */
    public function deleteStreamFieldsByStreamExtraId($stream_extra_id) {
        $this->startTransaction(
            'There was an exception when deleting user_stream_subscriptions '
            . 'for a stream_extra_id(' . $stream_extra_id . ').'
        );

        UserTake::deleteByStreamExtraId($stream_extra_id);
        Take::deleteByStreamExtraId($stream_extra_id);
        $stream_field_ids = StreamField::getStreamFieldIdsForStreamExtraId($stream_extra_id);
        foreach ($stream_field_ids as $stream_field_id) {
            $this->deleteStreamFieldByStreamFieldId($stream_field_id);
        }

        $this->endTransaction();
    }

    /**
     * Deletes a streams stream extra row.
     *
     * @param integer $stream_extra_id The extra id of the stream that is being deleted.
     *
     * @return void
     */
    public function deleteStreamExtraByStreamExtraId($stream_extra_id) {
        $this->startTransaction(
            'There was an exception when deleting a stream extra row(' . $stream_extra_id . '). '
        );

        UserStreamCount::deleteByStreamExtraId($stream_extra_id);
        StreamDefaultRing::deleteByStreamExtraId($stream_extra_id);
        StreamDefaultRhythm::deleteByStreamExtraId($stream_extra_id);
        StreamPublicRhythm::deleteByStreamExtraId($stream_extra_id);
        StreamPublic::deleteByStreamExtraId($stream_extra_id);
        SuggestionsDeclined::deleteByDeclinedStreamExtraId($stream_extra_id);
        UserTake::deleteByStreamExtraId($stream_extra_id);
        StreamOpenListItem::deleteByStreamExtraId($stream_extra_id);
        StreamChild::deleteParentsByStreamExtraId($stream_extra_id);
        StreamChild::deleteChildrenByStreamExtraId($stream_extra_id);
        StreamBlock::deleteByStreamExtraId($stream_extra_id);

        RingTakeName::updateStreamExtraIdToNull($stream_extra_id);

        $this->deletePostsForStreamExtra($stream_extra_id);
        $this->deleteUserStreamSubscriptionsByStreamExtraId($stream_extra_id);
        $this->deleteTakesByStreamExtraId($stream_extra_id);
        $this->deleteStreamFieldsByStreamExtraId($stream_extra_id);

        StreamExtra::deleteByStreamExtraId($stream_extra_id);

        $this->endTransaction();
    }

    /**
     * Deletes a streams stream extra data.
     *
     * @param integer $stream_id The id of the stream that is being deleted.
     *
     * @return void
     */
    public function deleteStreamsStreamExtras($stream_id) {
        $this->startTransaction(
            'There was an exception when deleting a stream extra for a stream(' . $stream_id . '). '
        );

        $stream_extra_ids = StreamExtra::getStreamExtraIdsForStream($stream_id);
        foreach ($stream_extra_ids as $stream_extra_id) {
            $this->deleteStreamExtraByStreamExtraId($stream_extra_id);
        }

        $this->endTransaction();
    }

    /**
     * Deletes a users streams.
     *
     * @param integer $user_id The id of the user whoose streams are being deleted.
     *
     * @return void
     */
    public function deleteUsersStreams($user_id) {
        $this->startTransaction('There was an exception when deleting a stream for a user(' . $user_id . ').');

        $stream_ids = Stream::getStreamIdsForUser($user_id);
        foreach ($stream_ids as $stream_id) {
            $this->deleteStreamsStreamExtras($stream_id);
            Stream::deleteByStreamId($stream_id);
        }

        $this->endTransaction();
    }

    /**
     * Coompletely deletes a users stream subscriptions.
     *
     * @param $user_id The id of the user whose stream subscriptions are being deleted.
     *
     * @return void
     */
    public function deleteUserStreamSubscriptions($user_id) {
        $this->startTransaction('There was an exception when deleting a users(' . $user_id . ') stream subscriptions.');

        $user_stream_subscription_ids = UserStreamSubscription::getUserStreamSubscriptionIdsForUser($user_id);
        foreach ($user_stream_subscription_ids as $user_stream_subscription_id) {
            UserStreamSubscriptionFilter::deleteByUserStreamSubscriptionId($user_stream_subscription_id);
            UserStreamSubscriptionRing::deleteByUserStreamSubscriptionId($user_stream_subscription_id);
        }

        UserStreamSubscription::deleteByUserId($user_id);

        $this->endTransaction();
    }


    /**
     * Coompletely deletes all tkaes by a user.
     *
     * @param $user_id The id of the user whose takes are being deleted.
     *
     * @return void
     */
    public function deleteUsersTakes($user_id) {
        $this->startTransaction(
            'There was an exception when deleting a users(' . $user_id . ') stream subscriptions. '
        );

        $take_ids = Take::getTakeIdsForUser($user_id);
        foreach ($take_ids as $take_id) {
            UserTake::deleteByTakeId($take_id);
            TakeKindred::deleteByTakeId($take_id);
            Take::deleteByTakeId($take_id);
        }

        $this->endTransaction();
    }

    /**
     * Completely deletes a user and all dependent data.
     *
     * WARNING. This should normally only be used for test accounts.
     * If a user is closing their account then use ScrubUser @todo, as this will maintain indexes for content generated
     * by other users in relation to this one.
     *
     * @param integer $user_id The users id.
     * @return @void
     */
    public function deleteUser($user_id) {
        $this->startTransaction('There was an exception when deleting a user(' . $user_id . ')');

        // Delete general user data that is not a parent to any other user data
        SiteAccess::deleteByUserId($user_id);
        UserConfig::deleteByUserId($user_id);
        SiteAccess::deleteByUserId($user_id);
        UserSecret::deleteByUserId($user_id);
        UserFeatureUsage::deleteByUserId($user_id);
        UserClientData::deleteByUserId($user_id);
        SignupCode::deleteByUsedUserId($user_id);
        UserLevel::deleteByUserId($user_id);
        WaitingPostTime::deleteByUserId($user_id);
        SuggestionsDeclined::deleteByUserId($user_id);
        SuggestionsDeclined::deleteByDeclinedUserId($user_id);
        Kindred::deleteByUserId($user_id);
        Kindred::deleteByKindredUserId($user_id);
        PostPrivateRecipient::deleteByUserId($user_id);
        UserProfile::deleteByUserId($user_id);
        UserStreamCount::deleteByUserId($user_id);
        UserTake::deleteByUserId($user_id);
        RhythmUserData::deleteByUserId($user_id);
        TakeKindred::deleteByUserId($user_id);

        // Delete the users ring membership and ring admin data
        UserRing::deleteByUserId($user_id);
        RingUserTake::deleteByUserId($user_id);
        Ring::setAdminSuperRingUserIdToNull($user_id);
        UserRingPassword::deleteByUserId($user_id);
        RingUserTake::deleteByUserId($user_id);
        Invitation::deleteByFromUserId($user_id);
        Invitation::deleteByToUserId($user_id);
        RingRhythmData::deleteByUserId($user_id);

        $this->deleteUserStreamSubscriptions($user_id);

        // delete the users posts and associated data.
        $this->deleteUsersMetaPost($user_id);
        PostUser::deleteByUserId($user_id);
        $this->deleteUsersPosts($user_id);

        // Also deletes take kindred.
        $this->deleteUsersUserRhythms($user_id);

        // Also deletes any associated user_take and take_kindred data.
        $this->deleteUsersTakes($user_id);

        // If this user is also a ring then delete the ring and any associated data.
        $this->deleteRing($user_id);

        // Delete any rhythms created by this user - and all usage of that rhythm by other users.
        $this->deleteUsersRhythms($user_id);

        // Deletes any streams associated with this user.
        $this->deleteUsersStreams($user_id);

        // Finally, we delete the user.
        User::deleteByUserId($user_id);

        $this->endTransaction();
    }

    /**
     * Deletes a users kindred data.
     *
     * @param integer $user_id The id of the user whose kindred data is being deleted.
     *
     * @return void
     */
    public function deleteUsersUserRhythms($user_id) {
        $this->startTransaction('There was an exception when deleting a user(' . $user_id . ')');

        $user_rhythm_ids = UserRhythm::getIdsForUser($user_id);
        foreach ($user_rhythm_ids as $user_rhythm_id) {
            TakeKindred::deletebyUserRhythmId($user_rhythm_id);
            Kindred::deleteByUserRhythmId($user_rhythm_id);
        }

        UserRhythm::deleteByUserId($user_id);

        $this->endTransaction();
    }

    /**
     * Delete all the ring_take_name data associated with a ring.
     *
     * @param integer $ring_id The ring id of the ring_take_name rows that are being deleted.
     *
     * @return void
     */
    public function deleteRingTakeNames($ring_id) {
        $this->startTransaction('There was an exception when deleting a ring (ring_id:' . $ring_id . ')');

        $ring_take_name_ids = RingTakeName::getRingTakeNameIds($ring_id);
        foreach ($ring_take_name_ids as $ring_take_name_id) {
            RingUserTake::deleteByRingTakeNameId($ring_take_name_id);
        }
        RingTakeName::deleteByRingId($ring_id);

        $this->endTransaction();
    }

    /**
     * Delete all the data associated with a ring.
     *
     * @param integer $user_id The user id of the ring that is being deleted.
     *
     * @return void
     */
    public function deleteRing($user_id) {
        $this->startTransaction('There was an exception when deleting a ring (user_id:' . $user_id . ')');
        $ring_id = Ring::getRingIdFromRingUserId($user_id);
        if ($ring_id !== false) {
            UserRing::deleteByRingId($ring_id);
            RingApplication::deleteByRingId($ring_id);
            RingRhythmData::deleteByRingId($ring_id);
            Invitation::deleteByRingId($ring_id);
            UserStreamSubscriptionRing::deleteByRingId($ring_id);
            $this->deleteRingTakeNames($ring_id);
        }
        StreamDefaultRing::deleteByRingUserId($user_id);
        UserRingPassword::deleteByRingUserId($user_id);

        Ring::deleteByUserId($user_id);

        $this->endTransaction();
    }

    public function deleteAllTestUsers() {
        $this->startTransaction('There was an exception when deleting all test users.');
        $user_ids = User::getAllTestUserIds();
        foreach ($user_ids as $user_id) {
            $this->deleteUser($user_id);
        }
        $this->endTransaction();
    }
}

?>
