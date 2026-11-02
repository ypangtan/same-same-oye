<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Voucher Module Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Subscription Voucher Module for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'voucher' => 'Voucher',
    'name' => 'Name',
    'description' => 'Description',
    'duration_in_months' => 'Duration ( Months )',
    'months' => 'month(s)',
    'quantity' => 'Quantity',
    'quantity_hint' => 'Codes will be generated automatically based on quantity.',
    'universal_code' => 'Universal Code',
    'has_universal_code' => 'Generate a universal code',
    'universal_code_placeholder' => 'Leave empty to auto generate, e.g. OYE2026',
    'universal_code_hint' => 'One code for everyone ( e.g. share in social media ). Each user can claim once, each claim uses 1 quantity.',
    'universal_code_fully_claimed' => 'This voucher is fully claimed.',
    'issued' => 'Issued',
    'claimed_count' => 'Claimed',
    'sent' => 'Sent',
    'remaining' => 'Remaining',
    'start_date' => 'Claim Start Date',
    'end_date' => 'Claim End Date',
    'date_placeholder' => 'Leave empty for no limit',
    'voucher_codes' => 'Voucher Codes',
    'view_codes' => 'View Codes',
    'code' => 'Code',
    'user' => 'User',
    'claimed_at' => 'Claimed At',
    'available' => 'Available',
    'claimed' => 'Claimed',
    'voided' => 'Voided',
    'void' => 'Void',
    'restore' => 'Restore',
    'total' => 'Total',
    'code_cannot_update' => 'This code can not be updated.',
    'quantity_less_than_issued' => 'Quantity can not be less than issued codes.',

    'content' => 'Content',
    'image' => 'Image',
    'recipients' => 'Recipients',
    'select_free_users' => 'Only free users are listed.',
    'content_hint' => 'Use {voucher_code} in title / content, each user will see his own voucher code.',
    'content_not_editable_hint' => 'Recipients and codes can not be changed after sent.',
    'published' => 'Published',
    'unpublished' => 'Unpublished',
    'publish' => 'Publish',
    'unpublish' => 'Unpublish',
    'publishing_date' => 'Publishing Date',
    'publishing_date_sent_hint' => 'Already sent, publishing date can not be changed.',
    'scheduled_cannot_change_status' => 'Scheduled notification will be published automatically on publishing date.',

    'voucher_not_active' => 'This voucher is not active.',
    'not_enough_quantity' => 'Not enough quantity, only :remaining code(s) left.',
    'users_already_received' => 'These users already received this voucher: :users',

    'invalid_code' => 'Invalid voucher code.',
    'code_already_claimed' => 'This voucher code has already been claimed.',
    'voucher_not_available' => 'This voucher is not available now.',
    'only_free_user' => 'Only free users can claim this voucher.',
    'voucher_already_claimed' => 'You have already claimed this voucher.',
    'claim_success' => 'Voucher claimed. Enjoy :months month(s) free subscription.',

    'type' => 'Type',
    'type_subscription' => 'Subscription',
    'type_third_party' => 'Third Party',
    'type_hint' => 'Subscription: user claim the code to get free subscription. Third Party: user only receive the url provided by third party, no claim.',
    'type_not_editable' => 'Type can not be changed after created.',
    'third_party_no_code' => 'Third party voucher has no code, please add urls instead.',
    'not_third_party' => 'This voucher is not a third party voucher.',

    'voucher_urls' => 'Voucher URLs',
    'manage_urls' => 'Manage URLs',
    'url' => 'URL',
    'urls' => 'URLs',
    'urls_placeholder' => 'One URL per line',
    'add_urls' => 'Add URLs',
    'max_users' => 'Max Users per URL',
    'max_users_hint' => 'Leave empty for unlimited. 1 URL + unlimited = every user get the same URL. Max users 1 = every user get a different URL.',
    'unlimited' => 'Unlimited',
    'assigned' => 'Assigned',
    'available_urls' => 'Available URLs',
    'select_all' => 'Select All',
    'edit_selected' => 'Edit Selected',
    'delete_selected' => 'Delete Selected',
    'activate_selected' => 'Activate Selected',
    'suspend_selected' => 'Suspend Selected',
    'select_at_least_one' => 'Please select at least one row.',
    'delete_urls_confirm' => 'Delete selected URLs?',
    'urls_added' => ':total URL(s) added.',
    'line_x' => 'Line :line',
    'invalid_url_line' => 'Invalid URL.',
    'url_not_found' => 'URL not found.',
    'url_assigned_cannot_edit' => 'URL already sent to users can not be edited.',
    'url_assigned_cannot_delete' => 'URL already sent to users can not be deleted, please suspend it instead.',
    'not_enough_urls' => 'Not enough URLs, only :remaining user(s) can be sent. Please add more URLs or increase max users.',
    'url_content_hint' => 'Third party voucher: use {voucher_url} in title / content, each user will see the URL assigned to him.',
];
