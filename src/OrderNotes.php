<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes;

use kuriousagency\commerce\ordernotes\services\Notes as OrderNotesService;
use kuriousagency\commerce\ordernotes\assetbundles\ordernotes\OrderNotesAsset;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\web\View;
use craft\events\TemplateEvent;

use yii\base\Event;

/**
 * Class CommerceOrderNotes
 *
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 *
 * @property  OrderNotesService $orderNotes
 */
class OrderNotes extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var OrderNotes
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
		self::$plugin = $this;
		
		$this->setComponents([
			'notes' => OrderNotesService::class,
		]);

		if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function (TemplateEvent $event) {
                    try {
                        Craft::$app->getView()->registerAssetBundle(OrderNotesAsset::class);
                    } catch (InvalidConfigException $e) {
                        Craft::error(
                            'Error registering AssetBundle - '.$e->getMessage(),
                            __METHOD__
                        );
                    }
                }
            );
		}

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'commerce-order-notes/default';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'commerce-order-notes/default/do-something';
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
		);
		
		//
		Craft::$app->view->hook('cp.commerce.order.edit', function(array &$context) {
			$view = Craft::$app->getView();
			//Craft::dd($context);
			$context['tabs'][] = [
				'label' => 'Notes',
				'url' => '#notesTab',
				'class' => null
			];

        	return $view->renderTemplate('order-notes/notes', [
				'order' => $context['order'],
				'notes' => $this->notes->getNotesByOrderId($context['order']->id),
				'noteTypes' => $this->notes->getTypes(),
			]);
		});

        Craft::info(
            Craft::t(
                'order-notes',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
