<?php
/**
 * This class removes all existing test data and then restores all the virgin test data.
 */
class Restore
{

    /**
     * This list of sql delete commands that removes all data created by or for test accounts.
     *
     * ******** IMPORTANT ALL CHANGES HERE MUST BE CAREFULLY REVIEWED ******
     * ******** THERE IS THE RISK OF DELETING LIVE USER DATA IF THESE QUEREIES ARE BROKEN
     *
     * They run in the order given, which is important as some deletions will not catch all data
     * if they are run out of order.
     *
     * @var array
     */
    private $delete_sql = array(
        "UPDATE user SET test_user = 1 WHERE username like 'test%' OR user_id < 10000",
        "DELETE stream_public_rhythm FROM stream_public_rhythm INNER JOIN stream_extra ON stream_public_rhythm.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN stream_extra ON stream_public.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block FROM stream_block INNER JOIN stream_extra ON stream_block.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
		"DELETE waiting_offer_time FROM waiting_offer_time INNER JOIN user ON waiting_offer_time.user_id = user.user_id WHERE user.test_user = 1",
		"DELETE take_value_list FROM take_value_list INNER JOIN stream_field ON take_value_list.stream_field_id = stream_field.stream_field_id  INNER JOIN stream_extra ON stream_field.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id INNER JOIN  stream_extra ON offer.offer_id = stream_extra.meta_offer_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN stream_extra ON user_take.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream_extra.stream_id = stream.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN take ON user_take.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN take ON user_take.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN take ON user_take.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN user ON user_take.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN take ON take_kindred.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN take ON take_kindred.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN take ON take_kindred.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN take ON take_kindred.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN take ON take_kindred.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN take ON take_kindred.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take_kindred FROM take_kindred INNER JOIN user ON take_kindred.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN take ON user_take.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN take ON user_take.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS  offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN take ON user_take.take_id = take.take_id INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN user ON user_take.take_user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN offer ON user_take.offer_id = offer.offer_id INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN offer ON user_take.offer_id = offer.offer_id INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN offer ON user_take.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN offer ON user_take.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN offer ON user_take.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_take FROM user_take INNER JOIN offer ON user_take.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN  stream_extra ON offer.offer_id = stream_extra.meta_offer_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_private_recipient FROM offer_private_recipient INNER JOIN offer ON  offer_private_recipient.offer_id = offer.offer_id INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_rhythm FROM user_rhythm  INNER JOIN user ON  user_rhythm.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN user ON take.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_content FROM offer_content INNER JOIN offer ON offer_content.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_popular FROM offer_popular INNER JOIN offer ON offer_popular.offer_id = offer.offer_id INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_popular FROM offer_popular INNER JOIN offer ON offer_popular.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_popular FROM offer_popular INNER JOIN offer ON offer_popular.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_popular FROM offer_popular INNER JOIN offer ON offer_popular.offer_id = offer.offer_id INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_popular FROM offer_popular INNER JOIN offer ON offer_popular.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_popular FROM offer_popular INNER JOIN offer ON offer_popular.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block_tree FROM stream_block_tree INNER JOIN offer ON stream_block_tree.offer_id = offer.offer_id INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block_tree FROM stream_block_tree INNER JOIN offer ON stream_block_tree.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block_tree FROM stream_block_tree INNER JOIN offer ON stream_block_tree.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block_tree FROM stream_block_tree INNER JOIN offer ON stream_block_tree.offer_id = offer.offer_id INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block_tree FROM stream_block_tree INNER JOIN offer ON stream_block_tree.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block_tree FROM stream_block_tree INNER JOIN offer ON stream_block_tree.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN offer ON stream_public.offer_id = offer.offer_id INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN offer ON stream_public.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN offer ON stream_public.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN offer ON stream_public.offer_id = offer.offer_id INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN offer ON stream_public.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN offer ON stream_public.offer_id = offer.offer_id INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN stream_extra ON take.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id  INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE take FROM take INNER JOIN offer ON take.offer_id = offer.offer_id  INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_private_recipient FROM offer_private_recipient INNER JOIN user ON offer_private_recipient.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_private_recipient FROM offer_private_recipient INNER JOIN user ON offer_private_recipient.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN offer AS offer2 ON offer.parent = offer2.offer_id INNER JOIN  stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN  stream_extra ON offer2.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN offer AS offer2 ON offer.top_parent = offer2.offer_id INNER JOIN user ON offer2.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer_user FROM offer_user INNER JOIN user ON offer_user.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_subscription_ring FROM user_stream_subscription_ring INNER JOIN user_stream_subscription ON user_stream_subscription_ring.user_stream_subscription_id = user_stream_subscription.user_stream_subscription_id INNER JOIN user ON user_stream_subscription.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_subscription_ring FROM user_stream_subscription_ring INNER JOIN user_stream_subscription ON user_stream_subscription_ring.user_stream_subscription_id = user_stream_subscription.user_stream_subscription_id INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream_extra.stream_id = stream.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN  stream_extra ON offer.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE offer FROM offer INNER JOIN user ON offer.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_filter FROM user_filter INNER JOIN rhythm_extra ON rhythm_extra.rhythm_extra_id = user_filter.rhythm_extra_id INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id INNER JOIN user ON rhythm.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_filter FROM user_filter INNER JOIN user_stream_subscription ON user_filter.user_stream_subscription_id = user_stream_subscription.user_stream_subscription_id INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream_extra.stream_id = stream.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_filter FROM user_filter INNER JOIN user_stream_subscription ON user_filter.user_stream_subscription_id = user_stream_subscription.user_stream_subscription_id INNER JOIN user ON user_stream_subscription.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_list FROM stream_list INNER JOIN stream_field ON stream_list.stream_field_id = stream_field.stream_field_id INNER JOIN stream_extra ON stream_field.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_subscription_ring FROM user_stream_subscription_ring INNER JOIN ring ON user_stream_subscription_ring.ring_id = ring.ring_id INNER JOIN user ON ring.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE signup_code FROM signup_code INNER JOIN user ON signup_code.used_user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_count FROM user_stream_count INNER JOIN user ON user_stream_count.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_count FROM user_stream_count INNER JOIN stream_extra ON user_stream_count.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream_extra.stream_id = stream.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_ring FROM user_ring INNER JOIN user ON user_ring.user_id= user.user_id WHERE user.test_user = 1",
        "DELETE user_ring FROM user_ring INNER JOIN ring ON ring.ring_id = user_ring.ring_id INNER JOIN user ON ring.user_id= user.user_id WHERE user.test_user = 1",
        "DELETE user_ring_password FROM user_ring_password INNER JOIN user ON user_ring_password.user_id= user.user_id WHERE user.test_user = 1",
        "DELETE user_ring_password FROM user_ring_password INNER JOIN user ON user_ring_password.ring_user_id= user.user_id WHERE user.test_user = 1",
        "DELETE version FROM version INNER JOIN stream_extra ON version.version_id = stream_extra.version_id INNER JOIN stream ON stream_extra.stream_id = stream.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE ring_user_take FROM ring_user_take  INNER JOIN user ON ring_user_take.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE waiting_offer_time FROM waiting_offer_time INNER JOIN user ON waiting_offer_time.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_block FROM stream_block INNER JOIN stream_extra ON stream_extra.stream_extra_id = stream_block.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE version FROM version INNER JOIN rhythm_extra ON version.version_id = rhythm_extra.version_id INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id INNER JOIN user ON rhythm.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_subscription FROM user_stream_subscription INNER JOIN stream_extra ON user_stream_subscription.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream_extra.stream_id = stream.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_stream_subscription FROM user_stream_subscription INNER JOIN user ON user_stream_subscription.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_child FROM stream_child INNER JOIN stream_extra ON stream_child.parent_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_child FROM stream_child INNER JOIN stream_extra ON stream_child.child_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_default_ring FROM stream_default_ring INNER JOIN stream_extra ON stream_default_ring.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_field FROM stream_field INNER JOIN stream_extra ON stream_field.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_public FROM stream_public INNER JOIN stream_extra ON stream_public.stream_extra_id = stream_extra.stream_extra_id INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE ring_user_take FROM ring_user_take INNER JOIN ring_take_name ON ring_user_take.ring_take_name_id = ring_take_name.ring_take_name_id INNER JOIN ring ON ring_take_name.ring_id = ring.ring_id  INNER JOIN user ON ring.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream_extra FROM stream_extra INNER JOIN stream ON stream.stream_id = stream_extra.stream_id INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE rhythm_extra FROM rhythm_extra INNER JOIN rhythm ON rhythm_extra.rhythm_id = rhythm.rhythm_id INNER JOIN user ON rhythm.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE ring_take_name  FROM ring_take_name  INNER JOIN ring ON ring.ring_id = ring_take_name.ring_id INNER JOIN user ON ring.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE rhythm_user_data FROM rhythm_user_data INNER JOIN user ON rhythm_user_data.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_profile FROM user_profile INNER JOIN user ON user_profile.user_id= user.user_id WHERE user.test_user = 1",
        "DELETE user_rhythm FROM user_rhythm INNER JOIN user ON user_rhythm.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE ring FROM ring  INNER JOIN user ON ring.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE stream FROM stream INNER JOIN user ON stream.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE rhythm FROM rhythm INNER JOIN user ON rhythm.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE invitation FROM invitation INNER JOIN user ON invitation.from_user_id = user.user_id WHERE user.test_user = 1",
        "DELETE invitation FROM invitation INNER JOIN user ON invitation.to_user_id = user.user_id WHERE user.test_user = 1",
        "DELETE invitation FROM invitation INNER JOIN ring ON ring.ring_id = invitation.ring_id INNER JOIN user ON ring.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_config FROM user_config INNER JOIN user ON user_config.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE user_feature_usage FROM user_feature_usage INNER JOIN user ON user_feature_usage.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE ring_application FROM ring_application INNER JOIN user ON ring_application.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE ring_application FROM ring_application INNER JOIN ring ON ring_application.ring_id = ring.ring_id INNER JOIN user ON ring.user_id = user.user_id WHERE user.test_user = 1",
        "DELETE FROM user WHERE test_user = 1",
    );

    public function __construct() {

        $this->deleteOldTestData();
        $this->copyTestDataFromTestDB();

    }

    private function deleteOldTestData() {
        foreach ($this->delete_sql as $delete_sql) {
            if (strpos($delete_sql, 'test_user = 1') === false) {
                throw new Exception('Delete test user SQL code does not include the necessary "test_user = 1" ');
            }

            $command = Yii::app()->db->createCommand($delete_sql);
            $command->execute();
        }
    }

    private function copyTestDataFromTestDB() {
        $tables = $this->getTestDBTableList();
        foreach ($tables as $table) {
//echo $table['Tables_in_cobalt_cascade_test_data'] . "\n";
            $sql = "
                INSERT INTO " .  Yii::app()->params['main_db_name'] . "." . $table['Tables_in_cobalt_cascade_test_data'] . "
                SELECT * FROM " . Yii::app()->params['test_db_name'] . "." . $table['Tables_in_cobalt_cascade_test_data'];
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
    }

    private function getTestDBTableList() {
        $sql = "show tables";
        $command = Yii::app()->dbtest->createCommand($sql);
        $tables = $command->queryAll();
        return $tables;
    }

}
?>