<?php return [
    'plugin'                   => [
        'name'        => 'Mall',
        'description' => 'E-commerce solution for October CMS',
    ],
    'titles'                   => [
        'products'             => [
            'create'  => 'Create product',
            'update'  => 'Edit product',
            'preview' => 'Preview product',
        ],
        'categories'           => [
            'create'  => 'Create category',
            'update'  => 'Edit category',
            'preview' => 'Category preview',
        ],
        'orders'               => [
            'show'   => 'Order details',
            'export' => 'Export orders',
        ],
        'discounts'            => [
            'create'  => 'Create discount',
            'update'  => 'Edit discount',
            'preview' => 'Preview discount',
        ],
        'shipping_methods'     => [
            'create'  => 'Create shipping method',
            'update'  => 'Edit shipping method',
            'preview' => 'Preview shipping method',
        ],
        'payment_methods'      => [
            'create'  => 'Create payment method',
            'edit'    => 'Edit payment method',
            'reorder' => 'Reorder',
        ],
        'custom_field_options' => [
            'edit' => 'Edit field options',
        ],
        'properties'           => [
            'create' => 'Create properites',
            'edit'   => 'Edit properties',
        ],
        'order_states'         => [
            'create'  => 'Create status',
            'edit'    => 'Edit status',
            'reorder' => 'Reorder status',
        ],
        'brands'               => [
            'create' => 'Create brand',
            'edit'   => 'Edit brand',
        ],
        'property_groups'      => [
            'create' => 'Create group',
            'edit'   => 'Edit group',
        ],
        'customer_groups'      => [
            'create' => 'Create group',
            'update' => 'Edit group',
        ],
    ],
    'menu_items'               => [
        'all_categories'  => 'All shop categories',
        'single_category' => 'Single shop category',
    ],
    'currency_settings'        => [
        'label'             => 'Currencies',
        'description'       => 'Setup your currencies',
        'currencies'        => 'Only enter official 3-char currency codes.',
        'currency_code'     => 'Currency code',
        'currency_decimals' => 'Anz. Dezimalstellen',
        'currency_format'   => 'Format',
        'currency_symbol'   => 'Symbol',
        'currency_rate'     => 'Rate',
        'is_default'        => 'Is default',
    ],
    'payment_gateway_settings' => [
        'label'       => 'Payment gateways',
        'description' => 'Configure your payment gateways',
        'stripe'      => [
            'api_key'         => 'Stripe API key',
            'api_key_comment' => 'You can find this key in your Stripe Dashboard',
        ],
        'paypal'      => [
            'client_id'         => 'PayPal Client I',
            'secret'            => 'PayPal Secret',
            'test_mode'         => 'Test mode',
            'test_mode_comment' => 'Run all payments in the PayPal Sandbox.',
        ],
    ],
    'general_settings'         => [
        'category'                   => 'Mall Shop',
        'label'                      => 'Configuration',
        'description'                => 'General settings',
        'product_page'               => 'Product details page',
        'product_page_comment'       => 'This is where the product details are displayed',
        'address_page'               => 'Address page',
        'address_page_comment'       => 'The addressForm component has to be present on this page',
        'checkout_page'              => 'Checkout page',
        'checkout_page_comment'      => 'The checkout component has to be present on this page',
        'account_page'               => 'Account page',
        'account_page_comment'       => 'The myAccount component has to be present on this page',
        'category_page'              => 'Category page for products listing',
        'auto_pop'                   => 'Auto pop cart overlay',
        'auto_pop_comment'           => 'Auto pop the cart overlay after a product was added to the shopping cart',
        'links'                      => 'CMS pages',
        'links_comment'              => 'Choose which pages are used to display your products',
        'customizations'             => 'Customizations',
        'customizations_comment'     => 'Customize the features of your shop',
        'category_page_comment'      => 'Add the "products" component to this page.',
        'order_number_start'         => 'First order number',
        'order_number_start_comment' => 'Initial id of the first order',
    ],
    'common'                   => [
        'shop'                 => 'Shop',
        'products'             => 'Products',
        'product'              => 'Product',
        'orders'               => 'Orders',
        'cart'                 => 'Cart',
        'shipping'             => 'Shipping',
        'taxes'                => 'Taxes',
        'rates'                => 'Rates',
        'inventory'            => 'Inventory',
        'accessories'          => 'Accessories',
        'shipping_methods'     => 'Shipping methods',
        'accessory'            => 'Accessory',
        'custom_fields'        => 'Custom fields',
        'variants'             => 'Variants',
        'discounts'            => 'Discounts',
        'discount'             => 'Discount',
        'select_placeholder'   => '-- Please choose',
        'main_image'           => 'Main image',
        'images'               => 'Images',
        'image_set'            => 'Image set',
        'attachments'          => 'Images/Downloads',
        'downloads'            => 'Downloads',
        'select_image'         => 'Choose image',
        'select_file'          => 'Choose file',
        'allowed'              => 'Allowed',
        'not_allowed'          => 'Not allowed',
        'yes'                  => 'Ja',
        'no'                   => 'Nein',
        'seo'                  => 'SEO',
        'properties_links'     => 'Properties/Links',
        'categories'           => 'Categories',
        'category'             => 'Category',
        'meta_title'           => 'Meta title',
        'meta_description'     => 'Meta description',
        'meta_keywords'        => 'Meta keywords',
        'reorder'              => 'Reorder entries',
        'id'                   => 'ID',
        'created_at'           => 'Created at',
        'updated_at'           => 'Updated at',
        'hide_published'       => 'Hide published',
        'slug'                 => 'URL',
        'name'                 => 'Name',
        'display_name'         => 'Display name',
        'group_name'           => 'Group name',
        'add_value'            => 'Add value',
        'export_orders'        => 'Export orders',
        'use_backend_defaults' => 'Use defaults configured in backend settings',
        'api_error'            => 'Could not save discount. Error while sending changes to the Mall API.',
        'includes_tax'         => 'Including taxes',
        'conditions'           => 'Conditions',
        'general'              => 'General',
        'logo'                 => 'Logo',
        'payment_gateway'      => 'Payment gateway',
        'payment_provider'     => 'Payment provider',
        'payment_methods'      => 'Payment methods',
        'image'                => 'Image',
        'color'                => 'Color',
        'unit'                 => 'Unit',
        'dont_group'           => '-- Do not group',
        'properties'           => 'Properties',
        'old_price'            => 'Old price',
        'property'             => 'Property',
        'property_groups'      => 'Property groups',
        'property_group'       => 'Property group',
        'options'              => 'Options',
        'option'               => 'Option',
        'catalogue'            => 'Catalogue',
        'out_of_stock'         => 'This product is out of stock.',
        'out_of_stock_short'   => 'Out of stock',
        'stock_limit_reached'  => 'You cannot add any more items of this product to your cart since the stock limit has been reached.',
        'deleted_at'           => 'Deleted at',
        'sort_order'           => 'Sort order',
        'order_states'         => 'Order states',
        'website'              => 'Website',
        'brands'               => 'Brands',
        'brand'                => 'Brand',
        'sorting_updated'      => 'Sort order has been updated',
        'not_in_use'           => 'Option is not in use',
        'saved_changes'        => 'Saved changes successfully',
        'none'                 => '-- None',
        'customers'            => 'Customers',
        'customer_groups'      => 'Customer groups',
        'customer_group'       => 'Customer group',
        'product_or_variant'   => 'Product/Variant',
        'code'                 => 'Code',
        'code_comment'         => 'This code can be used to identify this record programmatically',
    ],
    'variant'                  => [
        'method' => [
            'single'  => 'Article',
            'variant' => 'Article variants',
        ],
    ],
    'properties'               => [
        'use_for_variants'         => 'Use for variants',
        'use_for_variants_comment' => 'This property is different for different variants of this product',
        'filter_type'              => 'Filter type',
        'filter_types'             => [
            'none'  => 'Without filter',
            'set'   => 'Set',
            'range' => 'Range',
        ],
    ],
    'custom_field_options'     => [
        'text'       => 'Textfield',
        'integer'    => 'Integer',
        'float'      => 'Float',
        'textarea'   => 'Multi-line textfield',
        'dropdown'   => 'Dropdown',
        'checkbox'   => 'Checkbox',
        'color'      => 'Color',
        'image'      => 'Image',
        'add'        => 'Add option',
        'name'       => 'Name',
        'price'      => 'Price',
        'attributes' => 'Attribute',
        'option'     => 'Option',
    ],
    'product'                  => [
        'user_defined_id'                      => 'Product ID',
        'name'                                 => 'Product name',
        'published'                            => 'Published',
        'published_short'                      => 'Publ.',
        'not_published'                        => 'Not published',
        'published_comment'                    => 'This product is visible on the website',
        'stock'                                => 'Stock',
        'price'                                => 'Price',
        'description_short'                    => 'Short description',
        'description'                          => 'Description',
        'weight'                               => 'Weight (g)',
        'length'                               => 'Length (mm)',
        'height'                               => 'Height (mm)',
        'width'                                => 'Width (mm)',
        'quantity_default'                     => 'Default quantity',
        'quantity_min'                         => 'Minimum quantity',
        'quantity_max'                         => 'Maximum quantity',
        'inventory_management_method'          => 'Inventory management method',
        'allow_out_of_stock_purchases'         => 'Allow out of stock purchases',
        'allow_out_of_stock_purchases_comment' => 'This product can be ordered even if it is out of stock',
        'stackable'                            => 'Stack in cart',
        'stackable_comment'                    => 'If this product is added to the cart multiple times only show one entry (increase quantity)',
        'shippable'                            => 'Shippable',
        'shippable_comment'                    => 'This product can be shipped',
        'taxable'                              => 'Taxable',
        'taxable_comment'                      => 'Calculate taxes on this product',
        'add_currency'                         => 'Add currency',
        'is_taxable'                           => 'Use tax',
        'is_not_taxable'                       => 'Use no tax',
        'currency'                             => 'Currency',
        'general'                              => 'General',
        'duplicate_currency'                   => 'You have entered multiple prices for the same currency',
        'property_title'                       => 'Title',
        'property_value'                       => 'Value',
        'link_title'                           => 'Title',
        'link_target'                          => 'Target URL',
        'properties'                           => 'Properties',
        'links'                                => 'Links',
        'price_includes_tax'                   => 'Price includes taxes',
        'price_includes_tax_comment'           => 'The defined price includes all taxes',
        'group_by_property'                    => 'Attribute for variant grouping',
        'price_table_modal'                    => [
            'trigger'           => 'Edit stock and price values',
            'label'             => 'Price and stock',
            'title'             => 'Price and stock overview',
            'currency_dropdown' => 'Currency: ',
        ],
    ],
    'image_sets'               => [
        'is_main_set'         => 'Is main set',
        'is_main_set_comment' => 'Use this image set for this product',
        'create_new'          => 'Create new set',
    ],
    'category'                 => [
        'name'                            => 'Name',
        'code'                            => 'Code',
        'code_comment'                    => 'This code can be used to identify this category in your frontend partials.',
        'parent'                          => 'Parent',
        'no_parent'                       => 'No parent',
        'inherit_property_groups'         => 'Inherit properties of parent category',
        'inherit_property_groups_comment' => 'Use the property groups of this category\'s parent category',
    ],
    'custom_fields'            => [
        'name'             => 'Field name',
        'type'             => 'Field type',
        'options'          => 'Options',
        'required'         => 'Required',
        'required_comment' => 'This field is required to place an order',
        'is_required'      => 'Required',
        'is_not_required'  => 'Not required',
    ],
    'tax'                      => [
        'percentage'        => 'Percent',
        'countries'         => 'Only apply tax when shipping to these countries',
        'countries_comment' => 'If no country is selected the tax is applied worldwide.',
    ],
    'discounts'                => [
        'name'                                 => 'Name',
        'code'                                 => 'Discount code',
        'total_to_reach'                       => 'Minimal order total for discount to be valid',
        'type'                                 => 'Discount type',
        'trigger'                              => 'Valid if',
        'rate'                                 => 'Rate (%)',
        'amount'                               => 'Fixed amount',
        'alternate_price'                      => 'Alternate price',
        'max_number_of_usages'                 => 'Max number of usages',
        'expires'                              => 'Expires',
        'number_of_usages'                     => 'Numer of usages',
        'shipping_description'                 => 'Name of alternative shipping method',
        'shipping_price'                       => 'Price of alternative shipping method',
        'shipping_guaranteed_days_to_delivery' => 'Guaranteed days to delivery',
        'section_type'                         => 'What does this discount do?',
        'section_trigger'                      => 'When is this discount applicable?',
        'types'                                => [
            'fixed_amount'    => 'Fixed amount',
            'rate'            => 'Rate',
            'alternate_price' => 'Alternate price',
            'shipping'        => 'Alternate shipping',
        ],
        'triggers'                             => [
            'total'   => 'Order total is reached',
            'code'    => 'Discount code is entered',
            'product' => 'A specific product is present in the cart',
        ],
        'validation'                           => [
            'empty'               => 'Enter a promo code.',
            'shipping'            => 'You can only apply one promo code that lowers your shipping fees.',
            'duplicate'           => 'You can use the same promo code only once.',
            'expired'             => 'This promo code has expired.',
            'not_found'           => 'This promo code is not valid.',
            'usage_limit_reached' => 'This promo code has been applied to many times and is therefore no longer valid.',
        ],
    ],
    'order'                    => [
        'order_number'                        => 'Order number',
        'invoice_number'                      => 'Invoice number',
        'customer'                            => 'Customer',
        'creation_date'                       => 'Created at',
        'modification_date'                   => 'Modified at',
        'completion_date'                     => 'Completed at',
        'credit_card'                         => 'Credit cart',
        'payment_status'                      => 'Payment status',
        'grand_total'                         => 'Grand total',
        'billing_address'                     => 'Billing address',
        'shipping_address'                    => 'Shipping address',
        'currency'                            => 'Currency',
        'status'                              => 'Status',
        'email'                               => 'Email',
        'will_be_paid_later'                  => 'Will be paid later',
        'shipping_address_same_as_billing'    => 'Shipping address is same as billing',
        'credit_card_last4_digits'            => 'Last 4 digits',
        'tracking_number'                     => 'Tracking number',
        'tracking_url'                        => 'Tracking url',
        'shipping_fees'                       => 'Shipping fees',
        'shipping_provider'                   => 'Shipping provider',
        'shipping_method'                     => 'Shipping method',
        'card_holder_name'                    => 'Card holder',
        'card_type'                           => 'Cart type',
        'payment_method'                      => 'Payment method',
        'payment_gateway_used'                => 'Payment gateway',
        'tax_provider'                        => 'Tax provider',
        'lang'                                => 'Language',
        'refunds_amount'                      => 'Refunds amount',
        'adjusted_amount'                     => 'Adjusted amount',
        'rebate_amount'                       => 'Rebate amount',
        'total'                               => 'Total',
        'taxes_total'                         => 'Taxes total',
        'items_total'                         => 'Items total',
        'subtotal'                            => 'Subtotal',
        'taxable_total'                       => 'Taxable total',
        'total_weight'                        => 'Total weight',
        'total_rebate_rate'                   => 'Total rebate',
        'notes'                               => 'Notes',
        'custom_fields'                       => 'Custom fields',
        'shipping_enabled'                    => 'Shipping enabled',
        'payment_transaction_id'              => 'Payment transaction id',
        'change_order_status'                 => 'Change order status',
        'change_payment_status'               => 'Change payment status',
        'items'                               => 'Items',
        'quantity'                            => 'Quantity',
        'shipping_address_is_same_as_billing' => 'Shipping address is same as billing address',
        'update_tracking_info'                => 'Add tracking info',
        'invalid_status'                      => 'The selected status does not exist.',
        'updated'                             => 'Order update successful',
        'deleted'                             => 'Order successfully deleted',
        'deleting'                            => 'Deleting order...',
        'delete_confirm'                      => 'Do you really want to delete this order?',
        'update_invoice_number'               => 'Set invoice number',
        'modal'                               => [
            'cancel' => 'Cancel',
            'update' => 'Update information',
        ],
        'payment_states'                      => [
            'pending_state'  => 'Payment peding',
            'failed_state'   => 'Payment failed',
            'refunded_state' => 'Payment refunded',
            'paid_state'     => 'Paid',
        ],
    ],
    'shipping_method'          => [
        'guaranteed_delivery_days' => 'Guaranteed delivery in days',
        'available_above_total'    => 'Available if total is greater than or equals',
        'available_below_total'    => 'Available if total is lower than',
        'countries'                => 'Available for shipping to these countries',
        'countries_comment'        => 'If no country is selected this method is available worldwide.',
    ],
    'payment_method'           => [
        'price_fixed'      => 'Fixed fee',
        'price_percentage' => 'Procentual fee (of grand total)',
    ],
    'payment_status'           => [
        'paid'          => 'Paid',
        'deferred'      => 'Deferred',
        'paid_deferred' => 'Paid deferred',
        'paiddeferred'  => 'Paid deferred',
        'charged_back'  => 'Charged back',
        'refunded'      => 'Refunded',
        'paidout'       => 'Paidout',
        'failed'        => 'Failed',
        'pending'       => 'Pending',
        'expired'       => 'Expired',
        'cancelled'     => 'Cancelled',
        'open'          => 'Open',
    ],
    'permissions'              => [
        'manage_products'        => 'Can manage products',
        'manage_categories'      => 'Can manage categories',
        'manage_orders'          => 'Can manage orders',
        'manage_discounts'       => 'Can manage discounts',
        'settings'               => [
            'manage_general'         => 'Can change general shop settings',
            'manage_api'             => 'Can change api shop settings',
            'manage_currency'        => 'Can change currecy shop settings',
            'manage_payment_methods' => 'Zahlungsmethoden verwalten',
        ],
        'manage_properties'      => 'Can edit product properites',
        'manage_customer_groups' => 'Can manage customer groups',
    ],
    'components'               => [
        'category'              => [
            'details'    => [
                'name'        => 'Category',
                'description' => 'Displays all products of a category',
            ],
            'properties' => [
                'use_url'          => 'Use slug from URL',
                'show_variants'    => [
                    'title'       => 'Show article variants',
                    'description' => 'Don\'t show single products but all available product variants',
                ],
                'include_children' => [
                    'title'       => 'Include children',
                    'description' => 'Show all products of child categories as well',
                ],
                'product_page'     => [
                    'title'       => 'Product page',
                    'description' => 'Page where product details are displayed',
                ],
            ],
        ],
        'categoryFilter'        => [
            'details'    => [
                'name'        => 'Category filter',
                'description' => 'Filters the products from a category',
            ],
            'properties' => [
                'showPriceFilter'     => [
                    'title' => 'Show price filter',
                ],
                'includeChildren'     => [
                    'title'       => 'Include children',
                    'description' => 'Include properties and filters from products in child categories as well',
                ],
                'includeVariants'     => [
                    'title'       => 'Include variants',
                    'description' => 'Show filters for variant properties',
                ],
                'includeSliderAssets' => [
                    'title'       => 'Include noUI Slider',
                    'description' => 'Include all dependiencies of noUI Slider via cdnjs',
                ],
            ],
            'sortOrder'  => [
                'bestseller' => 'Bestseller',
                'priceLow'   => 'Lowest price',
                'priceHigh'  => 'Highest price',
                'latest'     => 'Latest',
                'oldest'     => 'Oldest',
            ],
        ],
        'myAccount'             => [
            'details'    => [
                'name'        => 'User account',
                'description' => 'Displays different forms where a user can view and edit his profile',
            ],
            'properties' => [
                'page' => [
                    'title' => 'Active subpage',
                ],
            ],
            'pages'      => [
                'orders'    => 'Orders',
                'profile'   => 'Profile',
                'addresses' => 'Addresses',
            ],
        ],
        'customerProfile'       => [
            'details'    => [
                'name'        => 'Customer profile',
                'description' => 'Displays a customer profile edit form.',
            ],
            'properties' => [
            ],
        ],
        'currencyPicker'        => [
            'details'    => [
                'name'        => 'Currency picker',
                'description' => 'Shows a picker to select the currently active shop currency',
            ],
            'properties' => [
            ],
        ],
        'addressList'           => [
            'details'    => [
                'name'        => 'Address list',
                'description' => 'Displays a list of all registered user addresses',
            ],
            'properties' => [
            ],
            'errors'     => [
                'address_not_found'          => 'The requested address could not be found',
                'cannot_delete_last_address' => 'You cannot delete your last address',
            ],
            'messages'   => [
                'address_deleted' => 'Address deleted',
            ],
        ],
        'ordersList'            => [
            'details'    => [
                'name'        => 'Orders list',
                'description' => 'Displays a list of all customer orders',
            ],
            'properties' => [
            ],
        ],
        'product'               => [
            'details'       => [
                'name'        => 'Product details',
                'description' => 'Displays details of a product',
            ],
            'properties'    => [
                'productSlug' => [
                    'title'       => 'Product url parameter',
                    'description' => 'Use this parameter to load the slug from the url',
                ],
            ],
            'added_to_cart' => 'Added product successfully',
        ],
        'cart'                  => [
            'details' => [
                'name'        => 'Cart',
                'description' => 'Displays the shopping cart',
            ],
        ],
        'checkout'              => [
            'details' => [
                'name'        => 'Checkout',
                'description' => 'Handles the checkout process',
            ],
            'errors'  => [
                'missing_settings' => 'Please select a payment and shipping method.',
            ],
        ],
        'discountApplier'       => [
            'details' => [
                'name'        => 'Promo code input',
                'description' => 'Displays a promo code input field',
            ],
        ],
        'shippingSelector'      => [
            'details' => [
                'name'        => 'Shipping selector',
                'description' => 'Displays a list of all available shipping methods',
            ],
            'errors'  => [
                'unavailable' => 'The selected shipping method is not available for your order.',
            ],
        ],
        'paymentMethodSelector' => [
            'details' => [
                'name'        => 'Payment method selector',
                'description' => 'Displays a list of all available payment methods',
            ],
            'errors'  => [
                'unavailable' => 'The selected payment method is not available for your order.',
            ],
        ],
        'addressSelector'       => [
            'details' => [
                'name'        => 'Address selector',
                'description' => 'Displays a list of all existing user addresses',
            ],
            'errors'  => [
            ],
        ],
        'addressForm'           => [
            'details'    => [
                'name'        => 'Address form',
                'description' => 'Displays a form to edit a user\'s address',
            ],
            'properties' => [
                'address'  => [
                    'title' => 'Address',
                ],
                'redirect' => [
                    'title' => 'Redirect (after save)',
                ],
                'set'      => [
                    'title' => 'Use this address as',
                ],
            ],
            'redirects'  => [
                'checkout' => 'Checkout page',
            ],
            'set'        => [
                'billing'  => 'Billing address',
                'shipping' => 'Shipping address',
            ],
        ],
        'signup'                => [
            'details'    => [
                'name'        => 'Signup',
                'description' => 'Displays a signup and signin form',
            ],
            'properties' => [
                'redirect' => [
                    'name' => 'Redirect after login',
                ],
            ],
            'errors'     => [
                'user_is_guest'   => 'You are trying to sign in with a guest account.',
                'unknown_user'    => 'The credentials you have entered are invalid.',
                'login'           => [
                    'required' => 'Please enter an email address.',
                    'email'    => 'Please enter a valid email address.',
                    'between'  => 'Please enter a valid email address.',
                ],
                'password'        => [
                    'required' => 'Please enter your password.',
                    'max'      => 'The provided password is too long.',
                    'min'      => 'The provided password is too short. Please enter at least 8 characters.',
                ],
                'password_repeat' => [
                    'required' => 'Please repeat your password.',
                    'same'     => 'Your password confirmation does not match your entered password.',
                ],
                'email'           => [
                    'required'          => 'Please enter an email address.',
                    'email'             => 'This email address is invalid.',
                    'unique'            => 'A user with this email address is already registered.',
                    'non_existing_user' => 'A user with this email address is already registered. Use the password reset function.',
                ],
                'firstname'       => [
                    'required' => 'Please enter your lastname.',
                ],
                'lastname'        => [
                    'required' => 'Please enter your firstname.',
                ],
                'lines'           => [
                    'required' => 'Please enter your address.',
                ],
                'zip'             => [
                    'required' => 'Please enter your zip code.',
                ],
                'city'            => [
                    'required' => 'Please enter a city.',
                ],
                'country_id'      => [
                    'required' => 'Choose a country.',
                    'exists'   => 'The provided country is not valid.',
                ],
                'state_id'        => [
                    'required' => 'Choose a state',
                    'exists'   => 'The selected value is not valid.',
                ],
            ],
        ],
        'categories'            => [
            'details'    => [
                'name'        => 'Categories',
                'description' => 'Lists available categories',
            ],
            'properties' => [
                'parent'       => [
                    'title'       => 'Start from category',
                    'description' => 'Only show child categories of this category',
                ],
                'categorySlug' => [
                    'title'       => 'Category slug parameter',
                    'description' => 'Use this parameter to load the parent category from the url',
                ],
                'categoryPage' => [
                    'title'       => 'Category page',
                    'description' => 'Links will point to this page. If nothing is entered the default settings from the backend settings will be used.',
                ],
            ],
            'no_parent'  => 'Show all categories',
            'by_slug'    => 'Use category in url as parent',
        ],
        'cartSummary'           => [
            'details'    => [
                'name'        => 'Cart summary',
                'description' => 'Displays the number of products in and total value of the cart',
            ],
            'properties' => [
                'showItemCount'  => [
                    'title'       => 'Show product count',
                    'description' => 'Displays the count of items in the cart',
                ],
                'showTotalPrice' => [
                    'title'       => 'Show total value',
                    'description' => 'Displays the total value of all items in the cart',
                ],
            ],
        ],
        'customerDashboard'     => [
            'details'    => [
                'name'        => 'Customer dashboard',
                'description' => 'Displays a link for the customer to login and change her account settings',
            ],
            'properties' => [
                'customerDashboardLabel' => [
                    'title'       => 'Customer dashboard label',
                    'description' => 'Link text for the customer account page',
                ],
                'logoutLabel'            => [
                    'title'       => 'Logout label',
                    'description' => 'Link text for the logout link',
                ],
            ],
        ],
        'products'              => [
            'details'    => [
                'name'        => 'Products',
                'description' => 'Displays a list of products',
            ],
            'properties' => [
                'categoryFilter'      => [
                    'title'       => 'Category filter',
                    'description' => 'Only show products from this category',
                    'no_filter'   => 'Show all products',
                    'by_slug'     => 'Load category from slug',
                ],
                'categorySlug'        => [
                    'title'       => 'Category slug parameter',
                    'description' => 'Use this parameter to load the category from the url',
                ],
                'displayCustomFields' => [
                    'title'       => 'Show custom fields',
                    'description' => 'Show all custom fields directly on the product page',
                ],
                'productsPerPage'     => [
                    'title' => 'Number of products per page',
                ],
                'noProductsMessage'   => [
                    'title'       => '«No products» message',
                    'description' => 'This text will be displayed if a category is empty',
                ],
                'sortOrder'           => [
                    'title'       => 'Sort order',
                    'description' => 'How the products will be sorted',
                ],
                'productPage'         => [
                    'title'       => 'Product page',
                    'description' => 'Product links will point to this page. If nothing is selected, the defaults from the backend settings will be used.',
                ],
            ],
        ],
    ],
    'shipping_method_rates'    => [
        'from_weight' => 'From (Weight in gramms)',
        'to_weight'   => 'To (Weight in gramms)',
    ],
    'products'                 => [
        'variants_comment' => 'Create different variants of the same product',
    ],
    'order_states'             => [
        'name'        => 'Name',
        'description' => 'Description',
        'color'       => 'Color',
        'flag'        => 'Special flag',
        'flags'       => [
            'new'      => 'Set the state of the order as "new"',
            'complete' => 'Set the state of the order as "done"',
        ],
    ],
    'customer_group'           => [
        'code_comment' => 'This code can be used to identify this group programmatically',
    ],
    'order_status'             => [
        'processed' => 'Processed',
        'disputed'  => 'Disputed',
        'shipped'   => 'Shipped',
        'delivered' => 'Delivered',
        'pending'   => 'Pending',
        'cancelled' => 'Cancelled',
    ],
];