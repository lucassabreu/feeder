<?php

namespace Application\Controller;

use Core\Controller\AbstractController;
use Core\Service\Exception\BusinessException;
use FeederRestful\Service\EntryService;
use FeederRestful\Service\FeedService;
use Zend\Json\Json;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class IndexController extends AbstractController {

    public function indexAction() {
        $fsv = $this->getService('FeederRestful\Service\Feed');
        /* @var $fsv FeedService */
        $esv = $this->getService('FeederRestful\Service\Entry');
        /* @var $esv EntryService */

        //$p = new Paginator($fsv->getAdapterPaginator(array(), array("id" => 'asc')));
        //$p->setItemCountPerPage(2);
        //$p->setCurrentPageNumber(1);
        //$content = $p->toJson();

        $this->response->getHeaders()->addHeaderLine("Content-Type: application/json");
        $search = $this->request->getQuery('search');
        if ($search) {
            try {
                $result = $fsv->search($search);
            } catch (BusinessException $ex) {
                $result = array(
                    'message' => $ex->getMessage(),
                    'help' => $ex->getHelp()
                );

                $this->response->setStatusCode(400);
            }

            $this->response->setContent(Json::encode($result));
        }

        $feed = $this->request->getQuery('feed');

        if ($feed) {
            $fsv->updateFeed($fsv->findById(intval($feed)));
            $this->response->setContent(Json::encode($fsv->findById(intval($feed))));
        }

        $entry = $this->request->getQuery('entry');

        if ($entry) {
            $entry = $esv->findById(intval($entry));
            $this->response->setContent(Json::encode($entry));
        }

        return $this->response;
    }

    public function aliveAction() {
        $this->response->setContent("alive");
        return $this->response;
    }

}
