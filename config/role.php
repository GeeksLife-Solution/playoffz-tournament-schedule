<?php

$arr = [
    'dashboard' => [
        'label' => "Dashboard",
        'access' => [
            'view' => ['admin.dashboard'],
            'add' => [],
            'edit' => [],
            'delete' => [],
        ],
    ],
    'manage_staff' => [
        'label' => "Manage Staff",
        'access' => [
            'view' => [
                'admin.role',
                'admin.get.role',
                'admin.role.staff',
            ],
            'add' => [
                'admin.staff.create',
                'admin.role.usersCreate',
            ],
            'edit' => [
                'admin.edit.staff',
                'admin.staff.role.update',
                'admin.role.statusChange',
                'admin.role.usersLogin',
            ],
            'delete' => [
                'admin.role.delete',
            ],
        ],
    ],


    'identify_form' => [
        'label' => "Identity Form",
        'access' => [
            'view' => [
                'admin.kyc.form.list',
                'admin.kyc.list',
                'admin.kyc.view',
                'admin.kyc.search',
                'admin.userKyc.search',
            ],
            'add' => [
                'admin.kyc.create',
                'admin.kyc.store',
            ],
            'edit' => [
                'admin.kyc.edit',
                'admin.kyc.update',
                'admin.kyc.action',
            ],
            'delete' => [],
        ],
    ],

    'manage_game' => [
        'label' => "Manage Game Module",
        'access' => [
            'view' => [
                'admin.listCategory',
                'admin.listTournament',
                'admin.listTeam',
                'admin.listMatch',
                'admin.infoMatch',
                'admin.addQuestion',
                'admin.optionList',
            ],
            'add' => [
                'admin.storeCategory',
                'admin.updateCategory',
                'admin.deleteCategory',
                'admin.storeTournament',
                'admin.storeTeam',
                'admin.storeMatch',
                'admin.storeQuestion',
                'admin.optionAdd',
            ],
            'edit' => [
                'admin.updateTournament',
                'admin.updateTeam',
                'admin.updateMatch',
                'admin.locker',
                'admin.updateQuestion',
                'admin.optionUpdate',

            ],
            'delete' => [
                'admin.deleteTournament',
                'admin.deleteTeam',
                'admin.deleteMatch',
                'admin.deleteQuestion',
                'admin.optionDelete',
            ],
        ],
    ],

    'manage_result' => [
        'label' => "Manage Result",
        'access' => [
            'view' => [
                'admin.resultList.pending',
                'admin.resultList.complete',
                'admin.searchResult',
                'admin.resultWinner',
                'admin.betUser',
            ],
            'add' => [

            ],
            'edit' => [
                'admin.makeWinner',
                'admin.refundQuestion'
            ],
            'delete' => [],
        ],
    ],

    'commission_setting' => [
        'label' => "Commission Setting",
        'access' => [
            'view' => [
                'admin.referral-commission',
            ],
            'add' => [
            ],
            'edit' => [
                'admin.referral-commission.store',
                'admin.referral-commission.action',
            ],
            'delete' => [],
        ],
    ],


    'all_transaction' => [
        'label' => "All Transaction",
        'access' => [
            'view' => [
                'admin.transaction',
                'admin.transaction.search',
                'admin.commissions',
                'admin.commissions.search',
                'admin.bet-history',
                'admin.searchBet',
            ],
            'add' => [],
            'edit' => [
                'admin.refundBet'
            ],
            'delete' => [],
        ],
    ],


    'user_management' => [
        'label' => "User Management",
        'access' => [
            'view' => [
                'admin.users',
                'admin.users.search',
                'admin.user.specific.activity',
                'admin.user.kyc.list',
                'admin.user.transaction',
                'admin.user.payment',
                'admin.user.payout',
            ],
            'add' => [
                'admin.email-send',
                'admin.email-send.store',
                'admin.users.add',
                'admin.user.store',
                'admin.user.create.success.message',
            ],
            'edit' => [
                'admin.login.as.user',
                'admin.block.profile',
                'admin.user.edit',
                'admin.user.userKycHistory',
                'admin.user.delete.multiple',
                'admin.user.update',
                'admin.user.email.update',
                'admin.user.username.update',
                'admin.user.update.balance',
                'admin.user.password.update',
                'admin.user.preferences.update',
                'admin.user.twoFa.update',
                'admin.user-balance-update',
                'admin.send.email',
                'admin.user.email.send',
                'admin.mail.all.user',
            ],
            'delete' => [
                'admin.user.delete'
            ],
        ],
    ],

    'payment_gateway' => [
        'label' => "Payment Gateway",
        'access' => [
            'view' => [
                'admin.payment.methods',
                'admin.deposit.manual.index',
            ],
            'add' => [
                'admin.deposit.manual.create',
                'admin.deposit.manual.store'
            ],
            'edit' => [
                'admin.edit.payment.methods',
                'admin.update.payment.methods',
                'admin.sort.payment.methods',
                'admin.payment.methods.deactivate',
                'admin.deposit.manual.edit',
                'admin.deposit.manual.update',
            ],
            'delete' => [],
        ],
    ],

    'payment_log' => [
        'label' => "Payment Request & Log",
        'access' => [
            'view' => [
                'admin.payment.pending',
                'admin.payment.log',
                'admin.payment.search',
            ],
            'add' => [],
            'edit' => [
                'admin.payment.action'
            ],
            'delete' => [],
        ],
    ],

    'payout_manage' => [
        'label' => "Payout method & Log",
        'access' => [
            'view' => [
                'admin.payout.method.list',
                'admin.payout-log',
                'admin.payout-request',
                'admin.payout-log.search',
                'admin.payout.withdraw.days',
            ],
            'add' => [
                'admin.payout.method.create',
                'admin.payout.method.store',
            ],
            'edit' => [
                'admin.payout.manual.method.edit',
                'admin.payout.method.edit',
                'admin.payout.method.update',
                'admin.payout.active.deactivate',
                'admin.withdrawal.days.update',
            ],
            'delete' => [],
        ],
    ],


    'support_ticket' => [
        'label' => "Support Ticket",
        'access' => [
            'view' => [
                'admin.ticket',
                'admin.ticket.view',
            ],
            'add' => [],
            'edit' => [
                'admin.ticket.reply'
            ],
            'delete' => [],
        ],
    ],
    'subscriber' => [
        'label' => "Subscriber",
        'access' => [
            'view' => [
                'admin.subscribe',
            ],
            'add' => [],
            'edit' => [],
            'delete' => [],
        ],
    ],

    'website_controls' => [
        'label' => "Website Controls",
        'access' => [
            'view' => [
                'admin.basic.control',
                'admin.app.control',
                'admin.logo.settings',
                'admin.settings',
                'admin.language.index',
                'admin.storage.index',
                'admin.currency.exchange.api.config',
                'admin.translate.api.setting',
                'admin.plugin.config',
                'admin.maintenance.index',
                'admin.tawk.configuration',
                'admin.fb.messenger.configuration',
                'admin.google.recaptcha.configuration',
                'admin.manual.recaptcha',
                'admin.google.analytics.configuration',
                'admin.pusher.config',
                'admin.in.app.notification.templates',
                'admin.firebase.config',
                'admin.push.notification.templates',
                'admin.email.control',
                'admin.email.template.default',
                'admin.email.templates',
                'admin.sms.controls',
                'admin.sms.templates',
                'admin.language.keywords',
            ],
            'add' => [],
            'edit' => [
                'admin.basic.control.update',
                'admin.basic.control.activity.update',
                'admin.currency.exchange.api.config.update',
                'admin.storage.edit',
                'admin.storage.update',
                'admin.storage.setDefault',
                'admin.maintenance.mode.update',
                'admin.logo.update',
                'admin.firebase.config.update',
                'admin.pusher.config.update',
                'admin.email.config.edit',
                'admin.email.config.update',
                'admin.email.set.default',
                'admin.email.template.update',
                'admin.sms.template.update',
                'admin.in.app.notification.template.edit',
                'admin.in.app.notification.template.update',
                'admin.push.notification.template.edit',
                'admin.push.notification.template.update',
                'admin.sms.config.edit',
                'admin.sms.config.update',
                'admin.manual.sms.method.update',
                'admin.sms.set.default',
                'admin.tawk.configuration.update',
                'admin.fb.messenger.configuration.update',
                'admin.google.recaptcha.Configuration.update',
                'admin.google.analytics.configuration.update',
                'admin.manual.recaptcha.update',
                'admin.active.recaptcha',
                'admin.translate.api.config.edit',
                'admin.translate.api.setting.update',
                'admin.translate.set.default',
                'admin.language.create',
                'admin.language.store',
                'admin.language.edit',
                'admin.language.update',
                'admin.add.language.keyword',
                'admin.update.language.keyword',
                'admin.delete.language.keyword',
            ],
            'delete' => [],
        ],
    ],


    'theme_settings' => [
        'label' => "Theme Settings",
        'access' => [
            'view' => [],
            'add' => [],
            'edit' => ['page.index'],
            'delete' => [],
        ],
    ],
];

return $arr;



