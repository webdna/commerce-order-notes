<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes\assetbundles\ordernotes;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class OrderNotesAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

	public function init(): void
    {
        $this->sourcePath = "@webdna/commerce/ordernotes/assetbundles/ordernotes/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/notes.js',
        ];

        $this->css = [
            'css/notes.css',
        ];

        parent::init();
    }
}
