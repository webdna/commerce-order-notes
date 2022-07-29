# Commerce Order Notes plugin for Craft CMS 4.x

Add notes to an order, they can also affect price.

## Requirements

This plugin requires Craft CMS 4 and Craft Commerce 4.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for Commerce Order Notes”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project

# tell Composer to load the plugin
composer require webdna/commerce-order-notes

# tell Craft to install the plugin
./craft install/plugin commerce-order-notes
```

## Features

This plugin will add a notes tab to the order detail page.

<img src="./resources/img/Screenshot-1.png" />

All note types are available when an order/cart has not been fully paid, but the following are only available when and order is fully paid:

-   General
-   Quantity Adjustment
-   Add Products

### Note Types

#### General Note

<img src="./resources/img/Screenshot-2.png" />
Leave comments about the order.

#### Manual Discount

<img src="./resources/img/Screenshot-3.png" />
You can enter a value that will be removed from the order.

#### Discount Code

<img src="./resources/img/Screenshot-4.png" />

#### Quantity Adjustment

<img src="./resources/img/Screenshot-5.png" />

#### Add Products

<img src="./resources/img/Screenshot-6.png" />

### Added notes

<img src="./resources/img/Screenshot-7.png" />
Once a note has been added you will be able to see an audit trail of the date and time as well as the user you created the note.

Please note that only admins can delete a note.

### Refunds

Any refunds issued will automatically create a note as well, so you will be able to see a full audit trail in one view.

### Price adjustments

Any note that changes the price will create an adjustment on the order.

<img src="./resources/img/Screenshot-8.png" />

### User Permissions

User permissions for each note type can be set.

<img src="./resources/img/Screenshot-9.png" />


Brought to you by [webdna](https://webdna.co.uk)
