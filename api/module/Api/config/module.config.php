<?php
return array(
    'router' => array(
        'routes' => array(
            'api.rest.user' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user[/:user_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\User\\Controller',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'upload' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/upload',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rpc\\User\\Upload',
                                'action' => 'upload',
                            ),
                        ),
                    ),
                    'photo' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/photo[/:size]',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rpc\\User\\Photo',
                                'action' => 'photo',
                            ),
                        ),
                    ),
                    'event' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/event',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rest\\Event\\Controller'
                            ),
                        ),
                    ),
                    'contact' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/contact',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rest\\Contact\\Controller'
                            ),
                        ),
                    ),
                ),
            ),

            'api.rpc.users.photo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/photo[/:size][/:name]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Users\\Photo',
                        'action' => 'photo',
                    ),
                ),
            ),

            'api.rpc.user.forgot.password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user/forgot-password[/:email]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\User\\ForgotPassword',
                        'action' => 'forgotPassword',
                    ),
                ),
			),
			'api.rpc.user.logout' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/user/logout[/:user_id]',
					'defaults' => array(
						'controller' => 'Api\\V1\\Rpc\\User\\Logout',
						'action' => 'logout',
					),
				),
			),
			'api.rpc.subscription.checkuser' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/subscription/checkuser[/:subscription_id]',
					'defaults' => array(
						'controller' => 'Api\\V1\\Rpc\\Subscription\\CheckUser',
						'action' => 'checkuser',
					),
				),
			),
			'api.rpc.user.logoutall' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/user/logoutall[/:user_id]',
					'defaults' => array(
						'controller' => 'Api\\V1\\Rpc\\User\\LogoutAll',
						'action' => 'logoutAll',
					),
				),
			),

			'api.rpc.user.emergency.mail' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/user/emergency-mail/',
					'defaults' => array(
						'controller' => 'Api\\V1\\Rpc\\User\\EmergencyMail',
						'action' => 'emergencyMail',
					),
				),
			),

            'api.rpc.user.forgot.password.validate.token' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user/validate/token[/:token]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\User\\ValidateToken',
                        'action' => 'validateChangePasswordToken',
                    ),
                ),
            ),

            'api.rpc.user.forgot.password.reset.password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user/reset-password',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\User\\ResetPassword',
                        'action' => 'resetPassword',
                    ),
                ),
            ),

            'api.rpc.user.validate.phone' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user/validate/phone[/:phone][/:ignoreUserId]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\User\\Validate\\Phone',
                        'action' => 'validatePhone',
                    ),
                ),
            ),

            'api.rest.asset' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/asset[/:asset_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Asset\\Controller',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'videoUrl' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/videourl.mp4',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rpc\\Asset\\VideoUrl',
                                'action' => 'videoUrl',
                            ),
                        ),
                    )
                ),
            ),

            'api.rest.contact' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/contact[/:contact_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Contact\\Controller',
                    ),
                ),
            ),

            'api.rpc.contact.validate.token' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/contact/validate/token[/:token]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Contact\\ValidateToken',
                        'action' => 'validateToken',
                    ),
                ),
            ),

            'api.rpc.contact.confirm' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/contact/confirm',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Contact\\Confirm',
                        'action' => 'confirm',
                    ),
                ),
            ),

            'api.rest.event' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/event[/:event_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Event\\Controller',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'imageUrl' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/imageurl',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rpc\\Event\\ImageUrl',
                                'action' => 'imageUrl',
                            ),
                        ),
                    ),
                    'videoUrl' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/videourl.mp4',
                            'defaults' => array(
                                'controller' => 'Api\\V1\\Rpc\\Event\\VideoUrl',
                                'action' => 'videoUrl',
                            ),
                        ),
                    )
                ),
            ),

            'api.rest.prospect' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/prospect[/:prospect_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Prospect\\Controller',
                    ),
                ),
            ),

            'api.rest.subscription' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/subscription[/:subscription_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Subscription\\Controller',
                    ),
                ),
            ),

            'api.rpc.subscription.validate.promo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/subscription/validate/promo[/:promoCode][/:isFree]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Subscription\\Validate\\Promo',
                        'action' => 'validatePromo',
                    ),
                ),
            ),

            'api.rpc.subscription.validate.recipient_email' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/subscription/validate/email[/:email][/:delivery_date]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Subscription\\Validate\\Email',
                        'action' => 'validateRecipientEmail',
                    ),
                ),
            ),

            'api.rpc.subscription.report.revenue' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/subscription/report/revenue[/:from][/:to]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Subscription\\Controller\\Revenue',
                        'action' => 'revenueReport',
                    ),
                ),
            ),

            'api.rpc.subscription.help' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/subscription/help',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Subscription\\Controller\\Help',
                        'action' => 'help',
                    ),
                ),
            ),

            'api.rest.invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/invitation[/:invitation_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Invitation\\Controller',
                    ),
                ),
            ),

            'api.rest.emergency' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/emergency[/:emergency_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Emergency\\Controller',
                    ),
                ),
            ),

            'api.rest.device' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/device[/:device_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Device\\Controller',
                    ),
                ),
            ),

            'api.rest.message' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/message[/:message_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Message\\Controller',
                    ),
                )
            ),

            'api.rest.coupon' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/coupon[/:coupon_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Coupon\\Controller',
                    ),
                ),
            ),

            'api.rpc.search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/search[/:type]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rpc\\Search',
                        'action' => 'index',
                    ),
                ),
            ),

            'api.rest.plan' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/plan[/:plan_id]',
                    'defaults' => array(
                        'controller' => 'Api\\V1\\Rest\\Plan\\Controller',
                    ),
                ),
            ),

        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'api.rest.user',
            1 => 'api.rest.asset',
            2 => 'api.rest.contact',
            3 => 'api.rest.event',
            4 => 'api.rest.prospect',
			5 => 'api.rest.subscription',
			6 => 'api.rpc.user.logout',
			7 => 'api.rpc.user.logoutall',
            8 => 'api.rest.invitation',
            9 => 'api.rpc.users.photo',
            10 => 'api.rest.emergency',
            11 => 'api.rest.device',
            12 => 'api.rest.message',
            13 => 'api.rest.coupon',
            14 => 'api.rpc.subscription.checkuser',
            15 => 'api.rpc.subscription.validate.promo',
            16 => 'api.rpc.subscription.validate.recipient_email',
            17 => 'api.rpc.search',
            18 => 'api.rpc.subscription.report.revenue',
            19 => 'api.rpc.user.forgot.password',
            20 => 'api.rpc.user.forgot.password.validate.token',
            21 => 'api.rpc.user.forgot.password.reset.password',
            22 => 'api.rpc.subscription.help',
            23 => 'api.rest.plan',
            24 => 'api.rpc.contact.validate.token',
            23 => 'api.rpc.contact.confirm',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Api\\V1\\Rest\\User\\UserResource' => 'Api\\V1\\Rest\\User\\UserResourceFactory',
            'Api\\V1\\Rest\\Asset\\AssetResource' => 'Api\\V1\\Rest\\Asset\\AssetResourceFactory',
            'Api\\V1\\Rest\\Contact\\ContactResource' => 'Api\\V1\\Rest\\Contact\\ContactResourceFactory',
            'Api\\V1\\Rest\\Event\\EventResource' => 'Api\\V1\\Rest\\Event\\EventResourceFactory',
            'Api\\V1\\Rest\\Prospect\\ProspectResource' => 'Api\\V1\\Rest\\Prospect\\ProspectResourceFactory',
            'Api\\V1\\Rest\\Subscription\\SubscriptionResource' => 'Api\\V1\\Rest\\Subscription\\SubscriptionResourceFactory',
            'Api\\V1\\Rest\\Invitation\\InvitationResource' => 'Api\\V1\\Rest\\Invitation\\InvitationResourceFactory',
            'Api\\V1\\Rest\\Emergency\\EmergencyResource' => 'Api\\V1\\Rest\\Emergency\\EmergencyResourceFactory',
            'Api\\V1\\Rest\\Device\\DeviceResource' => 'Api\\V1\\Rest\\Device\\DeviceResourceFactory',
            'Api\\V1\\Rest\\Message\\MessageResource' => 'Api\\V1\\Rest\\Message\\MessageResourceFactory',
            'Api\\V1\\Rest\\Coupon\\CouponResource' => 'Api\\V1\\Rest\\Coupon\\CouponResourceFactory',
            'Api\\V1\\Rest\\Plan\\PlanResource' => 'Api\\V1\\Rest\\Plan\\PlanResourceFactory',
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'Api\\V1\\Rpc\\Users\\Photo' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\User\\Photo' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
			'Api\\V1\\Rpc\\User\\Logout' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
			'Api\\V1\\Rpc\\User\\LogoutAll' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
			'Api\\V1\\Rpc\\User\\ForgotPassword' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\User\\EmergencyMail' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\User\\Upload' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\User\\Validate\\Phone' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\User\\ValidateToken' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\User\\ResetPassword' => 'Api\\V1\\Rpc\\User\\UserControllerFactory',
            'Api\\V1\\Rpc\\Event\\ImageUrl' => 'Api\\V1\\Rpc\\Event\\EventControllerFactory',
            'Api\\V1\\Rpc\\Event\\VideoUrl' => 'Api\\V1\\Rpc\\Event\\EventControllerFactory',
            'Api\\V1\\Rpc\\Asset\\VideoUrl' => 'Api\\V1\\Rpc\\Asset\\AssetControllerFactory',
            'Api\\V1\\Rpc\\Subscription\\CheckUser' => 'Api\\V1\\Rpc\\Subscription\\SubscriptionControllerFactory',
            'Api\\V1\\Rpc\\Subscription\\Validate\\Promo' => 'Api\\V1\\Rpc\\Subscription\\SubscriptionControllerFactory',
            'Api\\V1\\Rpc\\Subscription\\Validate\\Email' => 'Api\\V1\\Rpc\\Subscription\\SubscriptionControllerFactory',
            'Api\\V1\\Rpc\\Subscription\\Controller\\Revenue' => 'Api\\V1\\Rpc\\Subscription\\SubscriptionControllerFactory',
            'Api\\V1\\Rpc\\Search' => 'Api\\V1\\Rpc\\Search\\SearchControllerFactory',
            'Api\\V1\\Rpc\\Contact\\ValidateToken' => 'Api\\V1\\Rpc\\Contact\\ContactControllerFactory',
            'Api\\V1\\Rpc\\Contact\\Confirm' => 'Api\\V1\\Rpc\\Contact\\ContactControllerFactory',
            'Api\\V1\\Rpc\\Subscription\\Controller\\Help' => 'Api\\V1\\Rpc\\Subscription\\SubscriptionControllerFactory',

        ),
    ),

    'zf-rest' => array(
        'Api\\V1\\Rest\\User\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\User\\UserResource',
            'route_name' => 'api.rest.user',
            'route_identifier_name' => 'user_id',
            'collection_name' => 'user',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array('search'),
            'page_size' => 25,
            'page_size_param' => 'size',
            'entity_class' => 'Api\\V1\\Entity\\User',
            'collection_class' => 'Api\\V1\\Rest\\User\\UserCollection',
            'service_name' => 'User',
        ),
        'Api\\V1\\Rest\\Asset\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Asset\\AssetResource',
            'route_name' => 'api.rest.asset',
            'route_identifier_name' => 'asset_id',
            'collection_name' => 'asset',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => 'size',
            'entity_class' => 'Api\\V1\\Entity\\Asset',
            'collection_class' => 'Api\\V1\\Rest\\Asset\\AssetCollection',
            'service_name' => 'Asset',
        ),
        'Api\\V1\\Rest\\Contact\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Contact\\ContactResource',
            'route_name' => 'api.rest.contact',
            'route_identifier_name' => 'contact_id',
            'collection_name' => 'contact',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => 'size',
            'entity_class' => 'Api\\V1\\Entity\\Contact',
            'collection_class' => 'Api\\V1\\Rest\\Contact\\ContactCollection',
            'service_name' => 'Contact',
        ),
        'Api\\V1\\Rest\\Event\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Event\\EventResource',
            'route_name' => 'api.rest.event',
            'route_identifier_name' => 'event_id',
            'collection_name' => 'event',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => 'size',
            'entity_class' => 'Api\\V1\\Entity\\Event',
            'collection_class' => 'Api\\V1\\Rest\\Event\\EventCollection',
            'service_name' => 'Event',
        ),
        'Api\\V1\\Rest\\Prospect\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Prospect\\ProspectResource',
            'route_name' => 'api.rest.prospect',
            'route_identifier_name' => 'prospect_id',
            'collection_name' => 'prospect',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 2,
            'page_size_param' => 'size',
            'entity_class' => 'Api\\V1\\Entity\\Prospect',
            'collection_class' => 'Api\\V1\\Rest\\Prospect\\ProspectCollection',
            'service_name' => 'Prospect',
        ),
        'Api\\V1\\Rest\\Subscription\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Subscription\\SubscriptionResource',
            'route_name' => 'api.rest.subscription',
            'route_identifier_name' => 'subscription_id',
            'collection_name' => 'subscription',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => 'size',
            'entity_class' => 'Api\\V1\\Entity\\Subscription',
            'collection_class' => 'Api\\V1\\Rest\\Subscription\\SubscriptionCollection',
            'service_name' => 'Subscription',
        ),
        'Api\\V1\\Rest\\Invitation\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Invitation\\InvitationResource',
            'route_name' => 'api.rest.invitation',
            'route_identifier_name' => 'invitation_id',
            'collection_name' => 'invitation',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Api\\V1\\Rest\\Invitation\\Invitation',
            'collection_class' => 'Api\\V1\\Rest\\Invitation\\InvitationCollection',
            'service_name' => 'Invitation',
        ),
        'Api\\V1\\Rest\\Emergency\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Emergency\\EmergencyResource',
            'route_name' => 'api.rest.emergency',
            'route_identifier_name' => 'emergency_id',
            'collection_name' => 'emergency',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Api\\V1\\Rest\\Emergency\\Emergency',
            'collection_class' => 'Api\\V1\\Rest\\Emergency\\EmergencyCollection',
            'service_name' => 'Emergency',
        ),
        'Api\\V1\\Rest\\Device\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Device\\DeviceResource',
            'route_name' => 'api.rest.device',
            'route_identifier_name' => 'device_id',
            'collection_name' => 'device',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Api\\V1\\Rest\\Device\\Device',
            'collection_class' => 'Api\\V1\\Rest\\Device\\DeviceCollection',
            'service_name' => 'Device',
        ),
        'Api\\V1\\Rest\\Message\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Message\\MessageResource',
            'route_name' => 'api.rest.message',
            'route_identifier_name' => 'message_id',
            'collection_name' => 'message',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Api\\V1\\Rest\\Message\\Message',
            'collection_class' => 'Api\\V1\\Rest\\Message\\MessageCollection',
            'service_name' => 'Message',
        ),
        'Api\\V1\\Rest\\Coupon\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Coupon\\CouponResource',
            'route_name' => 'api.rest.coupon',
            'route_identifier_name' => 'coupon_id',
            'collection_name' => 'coupon',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Api\\V1\\Entity\\Coupon',
            'collection_class' => 'Api\\V1\\Rest\\Coupon\\CouponCollection',
            'service_name' => 'Coupon',
        ),
        'Api\\V1\\Rest\\Plan\\Controller' => array(
            'listener' => 'Api\\V1\\Rest\\Plan\\PlanResource',
            'route_name' => 'api.rest.plan',
            'route_identifier_name' => 'plan_id',
            'collection_name' => 'plan',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Api\\V1\\Rest\\Plan\\PlanEntity',
            'collection_class' => 'Api\\V1\\Rest\\Plan\\PlanCollection',
            'service_name' => 'Plan',
        ),

    ),

    'zf-content-negotiation' => array(
        'controllers' => array(
            'Api\\V1\\Rest\\User\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Asset\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Contact\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Event\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Prospect\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Subscription\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Invitation\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Emergency\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Device\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Message\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Coupon\\Controller' => 'HalJson',
            'Api\\V1\\Rpc\\Subscription\\Validate\\Promo' => 'Json',
            'Api\\V1\\Rpc\\Search\\Controller' => 'HalJson',
            'Api\\V1\\Rest\\Plan\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'Api\\V1\\Rest\\User\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Asset\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
                3 => 'multipart/form-data',
            ),
            'Api\\V1\\Rest\\Contact\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Event\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Prospect\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Subscription\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Invitation\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Admin\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rpc\\Search\\Controller' => array(
                0 => 'application/json',
                1 => 'application/*+json'
            ),
            'Api\\V1\\Rest\\Emergency\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Device\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Message\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Coupon\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'Api\\V1\\Rest\\Plan\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'Api\\V1\\Rest\\User\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Asset\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
                2 => 'multipart/form-data',
            ),
            'Api\\V1\\Rest\\Contact\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Event\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Prospect\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Subscription\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Invitation\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Emergency\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Device\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Message\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rest\\Coupon\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'Api\\V1\\Rpc\\Search\\Controller' => array(
                0 => 'application/json',
                1 => 'application/*+json'
            ),
            'Api\\V1\\Rest\\Plan\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'Api\\V1\\Entity\\User' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.user',
                'route_identifier_name' => 'user_id',
                'hydrator' => 'Api\\V1\\Hydrator\\UserHydrator',
            ),

            'Api\\V1\\Rest\\User\\UserCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.user',
                'route_identifier_name' => 'user_id',
                'is_collection' => true,
            ),

            'Api\\V1\\Entity\\Asset' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.asset',
                'route_identifier_name' => 'asset_id',
                'hydrator' => 'Api\\V1\\Hydrator\\AssetHydrator',
            ),
            'Api\\V1\\Rest\\Asset\\AssetCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.asset',
                'route_identifier_name' => 'asset_id',
                'is_collection' => true,
            ),
            'Api\\V1\\Entity\\Contact' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.contact',
                'route_identifier_name' => 'contact_id',
                'hydrator' => 'Api\\V1\\Hydrator\\ContactHydrator',
            ),
            'Api\\V1\\Rest\\Contact\\ContactCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.contact',
                'route_identifier_name' => 'contact_id',
                'is_collection' => true,
            ),
            'Api\\V1\\Entity\\Event' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'hydrator' => 'Api\\V1\\Hydrator\\EventHydrator',
            ),
            'Api\\V1\\Rest\\Event\\EventCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'is_collection' => true,
            ),
            'Api\\V1\\Entity\\Prospect' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.prospect',
                'route_identifier_name' => 'prospect_id',
                'hydrator' => 'Api\\V1\\Hydrator\\ProspectHydrator',
            ),
            'Api\\V1\\Rest\\Prospect\\ProspectCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.prospect',
                'route_identifier_name' => 'prospect_id',
                'is_collection' => true,
            ),
            'Api\\V1\\Entity\\Subscription' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.subscription',
                'route_identifier_name' => 'subscription_id',
                'hydrator' => 'Api\\V1\\Hydrator\\SubscriptionHydrator',
            ),
            'Api\\V1\\Rest\\Subscription\\SubscriptionCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.subscription',
                'route_identifier_name' => 'subscription_id',
                'is_collection' => true,
            ),
            'Api\\V1\\Rest\\Invitation\\Invitation' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.invitation',
                'route_identifier_name' => 'invitation_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'Api\\V1\\Rest\\Emergency\\Emergency' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.emergency',
                'route_identifier_name' => 'emergency_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'Api\\V1\\Entity\\Device' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.device',
                'route_identifier_name' => 'device_id',
                'hydrator' => 'Api\\V1\\Hydrator\\DeviceHydrator',
            ),
            'Api\\V1\\Rest\\Device\\DeviceCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.device',
                'route_identifier_name' => 'device_id',
                'is_collection' => true,
            ),
            'Api\\V1\\Entity\\Message' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.message',
                'route_identifier_name' => 'message_id',
                'hydrator' => 'DoctrineModule\\Stdlib\\Hydrator\\DoctrineObject',
            ),
            'Api\\V1\\Rest\\Message\\MessageCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.message',
                'route_identifier_name' => 'message_id',
                'is_collection' => true,
            ),

            'Api\\V1\\Entity\\Coupon' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.coupon',
                'route_identifier_name' => 'coupon_id',
                'hydrator' => 'Api\\V1\\Hydrator\\CouponHydrator',
            ),

            'Api\\V1\\Entity\\GiftCard' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.coupon',
                'route_identifier_name' => 'coupon_id',
                'hydrator' => 'Api\\V1\\Hydrator\\CouponHydrator',
            ),

            'Api\\V1\\Rest\\Coupon\\CouponCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.coupon',
                'route_identifier_name' => 'coupon_id',
                'is_collection' => true,
            ),

            'Api\\V1\\Entity\\Plan' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.plan',
                'route_identifier_name' => 'plan_id',
                'hydrator' => 'Api\\V1\\Hydrator\\PlanHydrator',
            ),
            'Api\\V1\\Rest\\Plan\\PlanCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.plan',
                'route_identifier_name' => 'plan_id',
                'is_collection' => true,
            ),
        ),
    ),

    'zf-rpc' => array(
        'Api\\V1\\Rpc\\User\\Photo' => array(
            'service_name' => 'User photo',
            'http_methods' => array('GET'),
            'route_name' => 'api.rest.user/photo',
        ),
        'Api\\V1\\Rpc\\User\\Upload' => array(
            'service_name' => 'User upload photo',
            'http_methods' => array('POST'),
            'route_name' => 'api.rest.user/upload',
        ),
        'Api\\V1\\Rpc\\Users\\Photo' => array(
            'service_name' => 'Photo',
            'http_methods' => array(
                0 => 'GET',
                1 => 'POST',
                2 => 'PUT',
                3 => 'PATCH',
                4 => 'DELETE',
            ),
            'route_name' => 'api.rpc.users.photo',
        ),
        'Api\\V1\\Rpc\\User\\ForgotPassword' => array(
            'service_name' => 'ResetPassword',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.user.forgot.password',
        ),
		'Api\\V1\\Rpc\\User\\Logout' => array(
			'service_name' => 'Logout',
			'http_methods' => array(0 => 'GET'),
			'route_name' => 'api.rpc.user.logout',
		),
		'Api\\V1\\Rpc\\User\\LogoutAll' => array(
			'service_name' => 'LogoutAll',
			'http_methods' => array(0 => 'GET'),
			'route_name' => 'api.rpc.user.logoutall',
		),
		'Api\\V1\\Rpc\\Subscription\\CheckUser' => array(
			'service_name' => 'checkuser',
			'http_methods' => array(0 => 'GET'),
			'route_name' => 'api.rpc.subscription.checkuser',
		),
        'Api\\V1\\Rpc\\User\\EmergencyMail' => array(
            'service_name' => 'EmergencyMail',
            'http_methods' => array(0 => 'POST'),
            'route_name' => 'api.rpc.user.emergency.mail',
        ),
        'Api\\V1\\Rpc\\User\\ResetPassword' => array(
            'service_name' => 'Reset password',
            'http_methods' => array(0 => 'POST'),
            'route_name' => 'api.rpc.user.forgot.password.reset.password',
        ),
        'Api\\V1\\Rpc\\User\\ValidateToken' => array(
            'service_name' => 'Validate token',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.user.forgot.password.validate.token',
        ),

        'Api\\V1\\Rpc\\Contact\\ValidateToken' => array(
            'service_name' => 'Validate token',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.contact.validate.token',
        ),
        'Api\\V1\\Rpc\\Contact\\Confirm' => array(
            'service_name' => 'confirm',
            'http_methods' => array(0 => 'POST'),
            'route_name' => 'api.rpc.contact.confirm',
        ),

        'Api\\V1\\Rpc\\Event\\ImageUrl' => array(
            'service_name' => 'Event\'s image',
            'http_methods' => array('GET'),
            'route_name' => 'api.rest.event/imageurl',
        ),
        'Api\\V1\\Rpc\\Event\\VideoUrl' => array(
            'service_name' => 'Event\'s video',
            'http_methods' => array('GET'),
            'route_name' => 'api.rest.event/videourl',
        ),
        'Api\\V1\\Rpc\\Asset\\VideoUrl' => array(
            'service_name' => 'Asset\'s video',
            'http_methods' => array('GET'),
            'route_name' => 'api.rest.asset/videourl',
        ),
        'Api\\V1\\Rpc\\Subscription\\Validate\\Promo' => array(
            'service_name' => 'Subscription validate promotion code',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.subscription.validate.promo',
        ),

        'Api\\V1\\Rpc\\Subscription\\Validate\\Email' => array(
            'service_name' => 'Subscription validate email',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.subscription.validate.recipient_email',
        ),

        'Api\\V1\\Rpc\\Subscription\\Controller\\Revenue' => array(
            'service_name' => 'Subscription revenue report',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.subscription.report.revenue',
        ),

        'Api\\V1\\Rpc\\User\\Validate\\Phone' => array(
            'service_name' => 'Phone Validation',
            'http_methods' => array(0 => 'GET'),
            'route_name' => 'api.rpc.user.validate.phone',
        ),

        'Api\\V1\\Rpc\\Search' => array(
            'service_name' => 'Search',
            'http_methods' => array('GET'),
            'route_name' => 'api.rpc.search',
        ),

        'Api\\V1\\Rpc\\Subscription\\Controller\\Help' => array(
            'service_name' => 'Subscription',
            'http_methods' => array(0 => 'POST'),
            'route_name' => 'api.rpc.subscription.help',
        ),
    ),
);