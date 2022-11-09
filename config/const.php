<?php

return [
    'FLG_OFF' => '0',
    'FLG_ON'  => '1',
    'PAGINATE_LIMIT'    => '15',
    'REVIEW_COMMENT_LIMIT'  => '40',
    'LOCAL_POST_TITLE_LIMIT'  => '20',
    'LOCAL_POST_SUMMARY_LIMIT'  => '100',
    'TEMP_USER_ID' => '1234567890',
    'ACTIVE_RATE_STRING'   => '★',
    'INACTIVE_RATE_STRING' => '☆',
    'RATE_LIMIT'           => '5',
    'EXPORT_REVIEWS_FILE_NAME' => 'reviews',
    'EXPORT_LOCAL_POSTS_FILE_NAME' => '投稿',
    'EXPORT_DASHBOARD_CHART_FILE_NAME' => 'dashboard_chart',
    'DASHBOARD_DATE_RANGE_DEFAULT' => '1',
    'DASHBOARD_DATE_RANGE_INTERVAL' => env('DATE_RANGE_INTERVAL', '0'),
    'DASHBOARD_DATE_RANGE_MINDATE' => '90',
    'DASHBOARD_DATE_RANGE_MAXDATE' => '0',
    'DASHBOARD_CHART_TYPE_DEFAULT' => '0',
    'DASHBOARD_UPDATE_SCHEDULED_TIME' => env('UPDATE_SCHEDULED_TIME', '0'),
    'THUMBNAIL_PREFIX' => '_thumb',
    'MINIMUM_OF_FILES' => 1,
    'MAXIMUM_OF_FILES' => 10,
    'SEARCH_RESULT_ITEMS' => [
        'REVIEW' => [
            'reviews.review_id',
            'reviews.gmb_create_time',
            'locations.gmb_location_name',
            'reviews.gmb_star_rating',
            'reviews.gmb_reviewer_display_name',
            'reviews.gmb_comment',
            'reviews.gmb_review_reply_comment',
            'reviews.sync_status as review_sync_status',
            'reviews.scheduled_sync_time as review_scheduled_sync_time',
            'review_replies.review_reply_id',
            'review_replies.sync_status as reply_sync_status',
            'review_replies.scheduled_sync_time as reply_scheduled_sync_time'
        ],
        'REPLY' => [
            'review_replies.review_reply_id',
            'review_replies.review_id',
            'review_replies.gmb_comment',
            'review_replies.sync_type',
            'review_replies.sync_status',
            'review_replies.scheduled_sync_time'
        ],
        'PHOTO' => [
            'account_id',
            'media_item2_group_id',
            'gmb_description',
            'gmb_source_url',
            'create_time',
            'update_time'
        ]
    ],
    'CSV_ITEMS' => [
        'REVIEW' => [
            'reviews.review_id',
            'reviews.gmb_create_time',
            'locations.gmb_location_name',
            'gmb_star_rating',
            'reviews.gmb_reviewer_display_name',
            'reviews.gmb_comment',
            'reviews.gmb_review_reply_update_time',
            'reviews.gmb_review_reply_comment'
        ],
        'LOCAL_POST_GROUP' => [
            'id',
            'accounts.gmb_account_name',
            'local_posts.sync_time',
            'topic_type',
            'event_title',
            'event_title',
            'local_posts.gmb_summary',
            'event_start_time',
            'event_end_time',
            'event_end_time',
            'local_posts.gmb_action_type',
            'local_posts.sync_status'
        ]
    ],
    'CSV_HEADERS' => [
        'REVIEW' => [
            'ID',
            '投稿日時',
            '店舗名',
            '評点',
            '投稿者表示名',
            'コメント',
            '返信日時',
            '返信'
        ],
        'LOCAL_POST_GROUP' => [
            'ID',
            'ブランド',
            '投稿日',
            '投稿種類',
            'タイトル',
            '詳細',
            'イベント開始',
            'イベント終了',
            'アクション',
            'ステータス'
        ],
        'DASHBOARD_CHART' => [
            '0' => [
                '集計日',
                '全店舗合計数',
                '1店舗平均'
            ],
            '1' => [
                '集計日',
                '直接検索数',
                '間接検索数',
                'ブランド検索数'
            ],
            '2' => [
                '集計日',
                '直接検索数比率(％)',
                '間接検索数比率(％)',
                'ブランド検索数比率(％)'
            ],
            '3' => [
                '集計日',
                'ウェブサイトクリック数',
                '電話番号クリック数',
                'ルート検索リクエスト数'
            ],
            '4' => [
                '集計日',
                'ウェブサイトクリック数比率(％)',
                '電話番号クリック数比率(％)',
                'ルート検索リクエスト数比率(％)'
            ],
            '5' => [
                '集計日',
                'アクション数比率(対検索数合計、％)'
            ]
        ]
    ],
    'REPLY_REQUEST_TYPE' => [
        'DRAFT' => 1,
        'SHORTEST_REPLY' => 2,
        'RESERVATION_REPLY' => 3,
        'DELETE_REPLY' => 4,
    ],
    'LOCAL_POST_REQUEST_TYPE' => [
        'DRAFT' => 1,
        'SHORTEST_POST' => 2,
        'RESERVATION_POST' => 3,
    ],
    'DASHBOARD_DATE_RANGE_VALUE' => [
        '0' => 0,
        '1' => 7,
        '2' => 30,
        '3' => 90,
        '4' => 13,
        '5' => 25
    ],
    'CONTRACT_TYPE_NAME' => [
        '0' => '管理者用',
        '1' => '基本契約',
        '2' => 'オプション契約Ａ',
        '3' => 'オプション契約Ｂ',
        '4' => 'オプション契約Ｃ',
    ],
    'CONTRACT_TYPE_PERIOD' => [
        '0' => 0,   // 期間制限なしでデータ参照可能
        '1' => 12,  // 過去12カ月間のデータのみ参照可能
        '2' => 0,
        '3' => 0,
        '4' => 0,
    ],
    'ACCESS_CONTROL' => [
        '0' => [
            'allow_localpost' => 0,
            'edit_localpost' => 0,
            'allow_review' => 0,
            'edit_review' => 0
        ],
        '1' => [
            'allow_localpost' => 1,
            'edit_localpost' => 1,
            'allow_review' => 1,
            'edit_review' => 1
        ],
        '10' => [
            'allow_localpost' => 1,
            'edit_localpost' => 1,
            'allow_review' => 1,
            'edit_review' => 1
        ],
        '11' => [
            'allow_localpost' => 1,
            'edit_localpost' => 1,
            'allow_review' => 1,
            'edit_review' => 0
        ],
        '12' => [
            'allow_localpost' => 1,
            'edit_localpost' => 1,
            'allow_review' => 0,
            'edit_review' => 0
        ],
        '13' => [
            'allow_localpost' => 1,
            'edit_localpost' => 0,
            'allow_review' => 1,
            'edit_review' => 1
        ],
        '14' => [
            'allow_localpost' => 1,
            'edit_localpost' => 0,
            'allow_review' => 1,
            'edit_review' => 0
        ],
        '15' => [
            'allow_localpost' => 1,
            'edit_localpost' => 0,
            'allow_review' => 0,
            'edit_review' => 0
        ],
        '16' => [
            'allow_localpost' => 0,
            'edit_localpost' => 0,
            'allow_review' => 1,
            'edit_review' => 1
        ],
        '17' => [
            'allow_localpost' => 0,
            'edit_localpost' => 0,
            'allow_review' => 1,
            'edit_review' => 0
        ],
        '18' => [
            'allow_localpost' => 0,
            'edit_localpost' => 0,
            'allow_review' => 0,
            'edit_review' => 0
        ]
    ]
];
