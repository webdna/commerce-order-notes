<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes;

use webdna\commerce\ordernotes\services\Notes as OrderNotesService;
use webdna\commerce\ordernotes\assetbundles\ordernotes\OrderNotesAsset;
use webdna\commerce\ordernotes\models\Refund as RefundModel;
use webdna\commerce\ordernotes\adjusters\Manual as ManualAdjuster;
use webdna\commerce\ordernotes\adjusters\Code as CodeAdjuster;
use webdna\commerce\ordernotes\adjusters\Refund as RefundAdjuster;

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
 * @author    webdna
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
     * @var Plugin
     */
    public static Plugin $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.1.3';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
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
                $model->type = 'webdna\\commerce\\ordernotes\\models\\Refund';
                $model->orderId = $event->transaction->orderId;
                $model->userId = Craft::$app->getUser()->getIdentity()->id;
                $model->comments = Craft::$app->getRequest()->getParam('note');
                $model->value = $event->amount;
                $model->data = '';
                //Craft::dd($model);
                OrderNotes::$plugin->notes->saveNote($model);
                //OrderNotes::$plugin->notes->updateOrder($model->order);
                }
            });
        }

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {

            $permissions = [];

            foreach ($this->notes->getAllTypes() as $type)
            {
                $handle = (new \ReflectionClass($type))->getShortName();
                $class = new $type();
                if ($handle != 'Note') {
                $permissions['ordernotes_type_'.$handle] = ['label' => $class->name];
                }
            }

            $permissions['ordernotes_action_Delete'] = ['label' => 'Delete Notes'];

            $event->permissions[] = [
                'heading' => 'Order Notes',
                'permissions' => $permissions,
            ];
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
            $context['tabs']['notes'] = [
                'label' => 'Notes',
                'url' => '#notesTab',
                'class' => null
            ];

            $types = [];
            foreach ($this->notes->getTypes($context['order']) as $type)
            {
                $class = new $type();
                $types[] = [
                'type' => $type,
                'name' => $class->name,
                'props' => $class->properties,
                ];
            }

            return $view->renderTemplate('commerce-order-notes/notes', [
                'order' => $context['order'],
                'notes' => $this->notes->getNotesByOrderId($context['order']->id),
                'noteTypes' => $types,
            ]);
        });

        Craft::info(
            Craft::t(
                'commerce-order-notes',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
