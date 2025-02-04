<?php
return [
    'slider' => [
        'multiple' => [
            'field_name' => [
                'name' => 'text',
                'short_description' => 'textarea',
                'button_name' => 'text',
                'my_link' => 'url',
                'image' => 'file'
            ],
            'validation' => [
                'name.*' => 'required|max:100',
                'description.*' => 'required|max:2000',
                'button_name.*' => 'required|max:100',
                'my_link.*' => 'required',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png'
            ],
            'size' => [
                'image' => '1403x300'
            ]
        ],
    ],
    'about_us' => [
        'single' => [
            'field_name' => [
                'title' => 'text',
                'sub_title' => 'text',
                'description' => 'textarea',
                'image' => 'file'
            ],
            'validation' => [
                'title.*' => 'required|max:100',
                'sub_title.*' => 'required|max:100',
                'description.*' => 'required|max:2000',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
            ],
            'size' => [
                'image' => '615x625'
            ]
        ],
    ],

    'faq' => [
        'single' => [
            'field_name' => [
                'title' => 'text',
            ],
            'validation' => [
                'title.*' => 'required|max:100',
            ],
        ],
        'multiple' => [
            'field_name' => [
                'title' => 'text',
                'description' => 'textarea'
            ],
            'validation' => [
                'title.*' => 'required|max:190',
                'description.*' => 'required|max:3000'
            ],
        ],
    ],

    'blog' => [
        'single' => [
            'field_name' => [
                'title' => 'text',
            ],
            'validation' => [
                'title.*' => 'required|max:100',
            ],
        ],
        'multiple' => [
            'field_name' => [
                'title' => 'text',
                'description' => 'textarea',
                'image' => 'file'
            ],
            'validation' => [
                'title.*' => 'required|max:100',
                'description.*' => 'required|max:2000',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png'
            ],
            'size' => [
                'image' => '848x509',
                'thumb' => '412x247'
            ]
        ],
    ],

    'testimonial' => [
        'single' => [
            'field_name' => [
                'title' => 'text',
                'sub_title' => 'text',
                'short_description' => 'textarea',
            ],
            'validation' => [
                'title.*' => 'required|max:100',
                'sub_title.*' => 'required|max:2000',
                'short_description.*' => 'required|max:2000'
            ]
        ],
        'multiple' => [
            'field_name' => [
                'name' => 'text',
                'designation' => 'text',
                'description' => 'textarea',
                'image' => 'file'
            ],
            'validation' => [
                'name.*' => 'required|max:100',
                'designation.*' => 'required|max:2000',
                'description.*' => 'required|max:2000',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png'
            ],
            'size' => [
                'image' => '80x80'
            ]
        ],
    ],

    'contact_us' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'short_description' => 'textarea',
                'address' => 'text',
                'house' => 'text',
                'email' => 'text',
                'phone' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:1000',
                'short_description.*' => 'required|max:2000',
                'address.*' => 'required|max:2000',
                'house.*' => 'required|max:2000',
                'email.*' => 'required|max:2000',
            ]
        ],
    ],

    'authentication' => [
        'single' => [
            'field_name' => [
                'login_page_heading' => 'text',
                'register_page_heading' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'login_page_heading.*' => 'required|max:300',
                'register_page_heading.*' => 'required|max:300',
                'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png,webp,svg',
            ],
            'size' => [
                'image' => '1000x667'
            ],
        ],
    ],

    'footer' => [
        'single' => [
            'field_name' => [
                'message' => 'text',
                'phone' => 'text',
                'email' => 'text',
                'address' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'message.*' => 'required|max:500',
                'phone.*' => 'required|max:20',
                'email.*' => 'required|max:50',
                'address.*' => 'required|max:2000',
                'image.*' => 'nullable|max:10240|image|mimes:jpg,jpeg,png,webp,svg',
            ]
        ],
        'size' => [
            'image' => '3840x2160'
        ],
    ],

    'social' => [
        'multiple' => [
            'field_name' => [
                'name' => 'text',
                'icon' => 'text',
                'my_link' => 'url',
            ],
            'validation' => [
                'name.*' => 'required|max:100',
                'icon.*' => 'required|max:100',
                'my_link.*' => 'required|url'
            ],
        ],
    ],


    'message' => [
        'required' => 'This field is required.',
        'min' => 'This field must be at least :min characters.',
        'max' => 'This field may not be greater than :max characters.',
        'image' => 'This field must be image.',
        'mimes' => 'This image must be a file of type: jpg, jpeg, png.',
        'integer' => 'This field must be an integer value',
    ],

    'content_media' => [
        'image' => 'file',
        'thumb_image' => 'file',
        'my_link' => 'url',
        'icon' => 'icon',
        'count_number' => 'number',
        'start_date' => 'date'
    ]
];

