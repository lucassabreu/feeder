<?php

namespace FeederRestful\Controller;

use Core\Controller\AbstractRestfulController;
use Core\Service\Exception\BusinessException;
use Exception;
use FeederRestful\Model\Entity\Feed;
use FeederRestful\Service\EntryService;
use FeederRestful\Service\FeedService;
use MongoDB;
use Zend\Mvc\MvcEvent;

/**
 * RESTful API for controlling the feeds and entries
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class FeederRestfulAPI extends AbstractRestfulController {

    protected $collection = null;
    protected $child = null;

    public function authAction() {
        $this->response->setStatusCode(401);

        //$feed = $this->getFeedService()->findById(1);
        //$entries = $this->getEntryService()->fetchByParams(array('feedId' => 1));
        //$feed = $feed->toArray();

        $m = $this->getService('Core\MongoDB');
        /* @var $m MongoDB */

        $feed = $m->feed->findOne(['_id' => 1]);
        $entries = $m->entry->find(['feedId' => 1]);

        $feed['entries'] = $entries;
        $m->feed->save($feed);

        return $this->json($feed);
    }

    public function feedSearchAction() {
        $query = $this->params()->fromRoute('q');

        if ($query == null)
            $query = $this->params()->fromQuery('q');

        if ($query == null)
            $query = $this->params()->fromPost('q');

        try {
            if ($query === null || trim($query) == '') {
                throw new BusinessException('Parameter "q" is missing !', 'Parameter must be sended.');
            }

            $result = $this->getFeedService()->search($query);
        } catch (Exception $ex) {
            return $this->returnJsonException($ex);
        }

        $return = array();
        if (is_array($result)) {
            foreach ($result as $value) {
                /* @var $value Feed */
                $return[] = array(
                    'id' => $value->id,
                    'title' => $value->title,
                    'link' => $value->link,
                );
            }
        }

        return $this->json($return);
    }

    public function get($id) {
        $return = array();

        switch ($this->collection) {
            case 'feed':
                $return = $this->getFeedService()->findById(intval($id));
                break;
            case 'entry':
                $return = $this->getEntryService()->findById(intval($id));
                break;
            default:
                return $this->notFoundAction();
        }

        if (is_null($return))
            return $this->notFoundAction();

        if (!is_array($return))
            $return = $return->toArray();

        return $this->json($return);
    }

    public function getList() {
        $return = array();

        switch ($this->collection) {
            case 'feed':
                $return = $this->getFeedService()->fetchAll();
                break;
            case 'entry':
                $return = $this->getEntryService()->fetchAll();
                break;
            default:
                return $this->notFoundAction();
        }

        return $this->json($return);
    }

    public function update($id, $data) {
        $return = array();

        switch ($this->collection) {
            case 'feed':
                break;
            case 'entry':
                break;
            default:
                return $this->notFoundAction();
        }

        return $this->json($return);
    }

    public function create($data) {
        $return = array();

        switch ($this->collection) {
            case 'feed':
                break;
            case 'entry':
                break;
            default:
                return $this->notFoundAction();
        }

        return $this->json($return);
    }

    /**
     * @return FeedService
     */
    protected function getFeedService() {
        return $this->getService('FeederRestful\Service\Feed');
    }

    /**
     * @return EntryService
     */
    protected function getEntryService() {
        return $this->getService('FeederRestful\Service\Entry');
    }

    public function onDispatch(MvcEvent $e) {
        $rm = $e->getRouteMatch();

        $this->collection = $rm->getParam('collection');
        $this->child = $rm->getParam('child');

        if ($rm->getParam('action') === null && $this->collection === null && $this->child === null)
            return $this->notFoundAction();

        return parent::onDispatch($e);
    }

}
