<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes\assetbundles\ordernotes;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class OrderNotesAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

	public function init()
    {
        $this->sourcePath = "@kuriousagency/commerce/ordernotes/assetbundles/ordernotes/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Notes.js',
        ];

        $this->css = [
            'css/Notes.css',
        ];

        parent::init();
    }
}