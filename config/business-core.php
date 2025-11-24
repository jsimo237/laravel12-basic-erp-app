<?php

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Modules\CoresManagement\Models\Media;
use App\Modules\CoresManagement\Models\Notification;
use App\Modules\LocalizationManagement\Models\Address;
use App\Modules\LocalizationManagement\Models\City;
use App\Modules\LocalizationManagement\Models\Country;
use App\Modules\LocalizationManagement\Models\Quarter;
use App\Modules\LocalizationManagement\Models\State;
use App\Modules\OrganizationManagement\Commands\CreateStaffCommand;
use App\Modules\OrganizationManagement\Middlewares\EnsureRequestHasOrganization;
use App\Modules\OrganizationManagement\Models\ContactForm;
use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\OrganizationManagement\Models\Setting;
use App\Modules\OrganizationManagement\Models\Staff;
use App\Modules\SalesManagement\Models\BaseCustomer;
use App\Modules\SalesManagement\Models\Customer;
use App\Modules\SalesManagement\Models\Invoice;
use App\Modules\SalesManagement\Models\InvoiceItem;
use App\Modules\SalesManagement\Models\Order;
use App\Modules\SalesManagement\Models\OrderItem;
use App\Modules\SalesManagement\Models\Payment;
use App\Modules\SalesManagement\Models\Product;
use App\Modules\SalesManagement\Models\Tax;
use App\Modules\SalesManagement\Models\TaxGroup;
use App\Modules\SecurityManagement\Middlewares\EnsureAuthGuardHeaderIsPresent;
use App\Modules\SecurityManagement\Models\OtpCode;
use App\Modules\SecurityManagement\Models\Permission;
use App\Modules\SecurityManagement\Models\Role;
use App\Modules\SecurityManagement\Models\User;
use App\Modules\SecurityManagement\Models\UserHasOrganization;
use App\Modules\SubscriptionsManagement\Models\Advantage;
use App\Modules\SubscriptionsManagement\Models\Package;
use App\Modules\SubscriptionsManagement\Models\Plan;
use App\Modules\SubscriptionsManagement\Models\Subscription;

return [

    //  Active ou non la personnalisation des fichiers du package
    'customization' => true,

    'middlewares' => [
        'has-auth-guard-header' => EnsureAuthGuardHeaderIsPresent::class,
        'has-organization' => EnsureRequestHasOrganization::class
    ],

    // 🧑‍💼 Définition des classes utilisables pour l'authentification selon le rôle
    "authenticables" => [
        'customer' => Customer::class,
        'staff' => Staff::class,
    ],

    // 📁 Sous-dossier dans le dossier de migrations où seront placées celles du package
    "migrations_subpath" => "business-core",

    // 🔄 Mapping morphologique pour les relations morphTo dans les modèles
    "morphs_map" => [
        // OrganizationManagement
        "organization" => Organization::class,
        "staff" => Staff::class,

        // CoreManagement
        "media" =>  Media::class,
        "notification" => Notification::class,

        // SecurityManagement
        "role" => Role::class,
        "permission" => Permission::class,
        "user" => User::class,
        "otp-code" =>  OtpCode::class,

        // LocalizationManagement
        "country" => Country::class,
        "state" => State::class,
        "city" => City::class,
        "quarter" => Quarter::class,

        // SalesManagement
        "order" => Order::class,
        "order-item" => OrderItem::class,
        "invoice" => Invoice::class,
        "invoice-item" => InvoiceItem::class,
        "tax" => Tax::class,
        "tax-group" => TaxGroup::class,
        "customer" => BaseCustomer::class,
        "payment" => Payment::class,

        // SubscriptionsManagement
        "subscription" =>  Subscription::class,
        "plan" =>  Plan::class,
        "package" =>  Package::class,
        "advantage" =>  Advantage::class,
    ],

    // 📜 Liste des commandes Artisan à enregistrer automatiquement depuis le package
    "console_commands" => [
        CreateStaffCommand::class,
    ],

    // 🔗 Liste des modèles qui doivent interagir avec une organisation
    "models_interact_with_organization" => [
        // Cas avec relation BelongsToMany (pivot)
        User::class => [
            "type" => BelongsToMany::class,
            "related_column_name" => "user_id",
            "related_model" => UserHasOrganization::class,
        ],

        Staff::class => [
            "type" => BelongsToMany::class,
            "related_column_name" => "user_id",
            "related_model" => UserHasOrganization::class,
        ],

        /**
         * Cas simples : relations BelongsTo vers une organisation
         */
        User::class,
        Role::class,
        OtpCode::class,
        ContactForm::class,
        Setting::class,
        Media::class,
        Order::class,
        Invoice::class,
        Payment::class,
        Tax::class,
        TaxGroup::class,
        Customer::class,
        Plan::class,
        Package::class,
        Subscription::class,
    ],

    /**
     * ✍️ Liste des modèles qui doivent enregistrer l’auteur
     * Voir la configuration associée dans :
     * @see \config/eloquent-authorable.php
     */
    "models_has_authors" => [
        Organization::class ,
        Staff::class ,

        User::class ,
        Role::class ,
        Permission::class ,
        ContactForm::class,
        Setting::class,
        Media::class,
        Customer::class,
        Product::class,
        Order::class,
        OrderItem::class,
        Invoice::class,
        InvoiceItem::class,
        Tax::class,
        Payment::class,
        TaxGroup::class,
        Plan::class,
        Package::class,
        Subscription::class,

        Address::class,
    ]
];
