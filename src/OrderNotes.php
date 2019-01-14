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
use kuriousagency\commerce\ordernotes\models\Refund as RefundModel;
use kuriousagency\commerce\ordernotes\adjusters\Manual as ManualAdjuster;
use kuriousagency\commerce\ordernotes\adjusters\Code as CodeAdjuster;
use kuriousagency\commerce\ordernotes\adjusters\Refund as RefundAdjuster;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\View;
use craft\events\TemplateEvent;
use craft\commerce\events\RefundTransactionEvent;
use craft\commerce\services\Payments;
use craft\commerce\services\OrderAdjustments;
use craft\events\RegisterComponentTypesEvent;
use craft\commerce\events\DiscountAdjustmentsEvent;

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

		Event::on(OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function(RegisterComponentTypesEvent $e) {
			$e->types[] = ManualAdjuster::class;
			$e->types[] = CodeAdjuster::class;
			$e->types[] = RefundAdjuster::class;
		});

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

		//add event for refund transactions to save order note.
		if (Craft::$app->getRequest()->getIsCpRequest()) {
			Event::on(Payments::class, Payments::EVENT_AFTER_REFUND_TRANSACTION, function(RefundTransactionEvent $event) {
				//Craft::dd($event);
				if ($event->transaction->status == 'success') {
					$model = new RefundModel();
					$model->type = 'refund';
					$model->orderId = $event->transaction->orderId;
					$model->userId = Craft::$app->getUser()->getIdentity()->id;
					$model->comments = Craft::$app->getRequest()->getParam('note');
					$model->value = $event->amount;
					$model->data = '';
					//Craft::dd($model);
					OrderNotes::$plugin->notes->saveNote($model);
				}
			});
		}
		
		Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
			
			$permissions = [];

			foreach ($this->notes->getAllTypes() as $key => $type)
			{
				if ($key != 'note') {
					$permissions['ordernotes_type_'.$key] = ['label' => $type];
				}
			}

			$permissions['ordernotes_action_delete'] = ['label' => 'Delete Notes'];
			
			$event->permissions['Order Notes'] = $permissions;
		});

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
				'noteTypes' => $this->notes->getTypes($context['order']),
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
