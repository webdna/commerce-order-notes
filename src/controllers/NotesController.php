<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes\controllers;

use webdna\commerce\ordernotes\OrderNotes;
use webdna\commerce\ordernotes\models\Note as NoteModel;
use webdna\commerce\ordernotes\models\Manual as ManualModel;

use Craft;
use craft\web\Controller;
use craft\commerce\Plugin as Commerce;
use craft\web\Response;

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class NotesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array|int Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected bool|array|int $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    public function actionSave(): ?Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $type = $request->getParam('type');
        $orderId = $request->getParam('orderId');
        $userId = Craft::$app->getUser()->getIdentity()->id;
        $comments = $request->getParam('comments');
        $value = $request->getParam('value', 0);
        $data = $request->getParam('data', []);

        //$class = "webdna\\commerce\\ordernotes\\models\\".ucfirst($type);
        $model = new $type();

        $model->type = $type;
        $model->orderId = $orderId;
        $model->userId = $userId;
        $model->comments = $comments;
        $model->value = $value;
        $model->data = $data;
        //Craft::dd($model);

        $model->validate();

        //Craft::dump($model->getErrors());

        /*if ($model->hasErrors()) {
            return $this->asJson([
                'errors' => $model->getErrors(),
            ]);
        }*/

        if (!OrderNotes::$plugin->notes->saveNote($model)) {
            //Craft::dd($model->getErrors());
            return $this->asJson([
                'errors' => $model->getErrors(),
            ]);
        }

        $model->afterSave();
        //Craft::dd(count($model->order->lineItems));
        $recalc = true;
        if ($model->type == 'webdna\\commerce\\ordernotes\\models\\Note' || $model->type == 'webdna\\commerce\\ordernotes\\models\\Email') {
            $recalc = false;
        }
        //Craft::dd($recalc);

        OrderNotes::$plugin->notes->updateOrder($model->order, $recalc);

        return $this->asJson(['success'=>true]);
    }

    public function actionDelete(): ?Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        if ($id) {
            $note = OrderNotes::$plugin->notes->getNoteById($id);
            $note->afterDelete();
            $order = $note->getOrder();
            $value = $note->value;

            OrderNotes::$plugin->notes->deleteNoteById($id);

            OrderNotes::$plugin->notes->updateOrder($order, $value != 0);
            return $this->asJson(['success' => true]);
        }

        return $this->asFailure("There was an error deleting the note.");
    }

}
