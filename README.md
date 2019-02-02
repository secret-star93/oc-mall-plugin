<div style="text-align: center;">
	<img style="max-width: 100%; margin: 2rem auto; display: block;" src="https://user-images.githubusercontent.com/8600029/52163618-c3bf3d80-26e4-11e9-870c-427401a27937.jpeg">
</div>


# oc-mall

> E-commerce solution for October CMS

[![Build Status](https://travis-ci.org/OFFLINE-GmbH/oc-mall-plugin.svg?branch=develop)](https://travis-ci.org/OFFLINE-GmbH/oc-mall-plugin)

`oc-mall` is a fully featured online shop solution for October CMS.

* Manage Products and Variants
* Stock management
* Checkout via Stripe and PayPal supported out-of-the-box
* Custom payment providers 
* Integrated with RainLab.User
* Multi-currency and multi-language (integrates with RainLab.Translate)
* Shipping and Tax management
* Specific prices for different customer groups
* Unlimited additional price fields (reseller, retail, reduced, etc)
* Custom order states
* Flexible e-mail notifications
* Easily extendable with custom features

#### Documentation
The documentation of this plugin can be found here:
[https://offline-gmbh.github.io/oc-mall-plugin/](https://offline-gmbh.github.io/oc-mall-plugin/)

#### Requirements
* PHP7.1+
* October Build 444+


#### Demo

A live demo of the plugin can be found here:
[https://mall.offline.swiss](https://mall.offline.swiss)

#### Support

For support and development requests please file an issue on GitHub.

## Installation

The easiest way to get you started is by using the command line:

```bash
php artisan plugin:install rainlab.user
php artisan plugin:install rainlab.location
php artisan plugin:install offline.mall
``` 

Once the plugin is installed take a look at
[the official documentation](https://offline-gmbh.github.io/oc-mall-plugin/)
to get everything up and running.

## Contributing

### Documentation

The raw documentation for this plugin is stored in the docs directory. It is written in markdown and built with 
[VuePress](https://vuepress.vuejs.org/).

For a live preview of the docs install `vuepress` locally and run `vuepress dev` from the docs directory.

### Bugs and feature requests

If you found a bug or want to request a feature please file a GitHub issue.

### Pull requests

PRs are always welcome! Open them against the `develop` branch.
If you plan a time consuming contribution please open an issue first and describe what changes you have in mind. 
