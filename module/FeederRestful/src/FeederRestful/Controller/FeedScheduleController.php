<?php

namespace FeederRestful\Controller;

use Core\Controller\AbstractController;
use Core\Service\Exception\BusinessException;
use Exception;
use FeederRestful\Model\Entity\Feed;
use FeederRestful\Service\FeedService;
use Zend\Console\Request;
use Zend\Console\Response;

class FeedScheduleController extends AbstractController {

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    public function checkUrlAction() {
        $url = $this->request->getParam('url');

        try {
            if ($url === null || trim($url) == '') {
                throw new BusinessException('Parameter "url" is missing !', 'Parameter must be informed.');
            }

            $result = $this->getFeedService()->searchAtUrl($url);
        } catch (Exception $ex) {
            return "Error: {$ex->getMessage()}. Stack Trace: \n{$ex->getTraceAsString()} \n";
        }

        $return = null;

        if ($result) {
            $return = array(
                'id' => $result->id,
                'title' => $result->title,
                'link' => $result->link,
            );
        }

        echo "Result: ";
        print_r($return);
    }

    public function searchAction() {
        $param = $this->request->getParam('param');

        try {
            if ($param === null || trim($param) == '')
                throw new BusinessException('Parameter is missing !', 'Parameter must be informed.');

            $result = $this->getFeedService()->search($param);
        } catch (Exception $ex) {
            return "Error: {$ex->getMessage()}. Stack Trace: \n{$ex->getTraceAsString()} \n";
        }
        
        $return = array();

        if (is_array($result)) {
            foreach ($result as $value) {
                $return[] = array(
                    'id' => $value->id,
                    'title' => $value->title,
                    'link' => $value->link,
                );
            }
        }

        echo "Result: ";
        print_r($return);
    }

    public function scheduleAction() {

        $request = $this->getRequest();

        // Check verbose flag
        $verbose = $request->getParam('verbose') || $request->getParam('v');

        // Check mode
        $feedId = $request->getParam('feedId', null);

        if ($feedId) {
            $feed = $this->getFeedService()->findById(intval($feedId));

            if (!$feed) {
                return sprintf("Feed with ID %d does not exist !\n", $feedId);
            }

            $this->getFeedService()->updateFeed($feed);
        } else {
            $this->getFeedService()->updateAll();
        }

        return "Schedule executed with sucess !\n";
    }

    /**
     * @return FeedService
     */
    protected function getFeedService() {
        return $this->getService('FeederRestful\Service\Feed');
    }

}
