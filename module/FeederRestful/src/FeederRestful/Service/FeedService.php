<?php

namespace FeederRestful\Service;

use Core\Model\DAO\DAOInterface;
use Core\Model\DAO\Exception\DAOException;
use Core\Service\AbstractDAOService;
use Core\Service\Exception\BusinessException;
use Core\Util\DateTime;
use DateTime as SplDateTime;
use DOMXPath;
use Exception;
use FeederRestful\Model\DAO\FeedDAOInterface;
use FeederRestful\Model\Entity\Feed;
use Zend\Feed\Reader\Entry\EntryInterface;
use Zend\Feed\Reader\Feed\FeedInterface as ZendFeed;
use Zend\Feed\Reader\Reader;

/**
 * Service class for Feed
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class FeedService extends AbstractDAOService {

    /**
     * @var FeedDAOInterface
     */
    protected $dao;

    public function setDAOInterface(DAOInterface $dao) {
        if (!($dao instanceof FeedDAOInterface))
            throw new BusinessException(sprintf("This Service must receive a FeederRestful\Model\DAO\FeedDAOInterface !"));

        return parent::setDAOInterface($dao);
    }

    /**
     * @return EntryService
     */
    protected function getEntryService() {
        return $this->getService('FeederRestful\Service\Entry');
    }

    public function save($feed, array $values = null) {
        if ($feed == null) {
            $feed = new Feed;

            $values['lastUpdate'] = null;
            if (isset($values['id']) && $values['id'] !== null)
                throw new BusinessException(spritf("Entity Feed has auto-generated ID, if you want to edit this entity on database, you must send the object !"));
        } else {
            if (!($feed instanceof Feed))
                $feed = $this->findById($feed);
        }

        $values = $this->fillValues($values, $feed->getData());

        if ($values['link'] === null)
            throw new BusinessException(sprintf("Link of the Feed, must be informed !"));

        if ($values['title'] === null)
            throw new BusinessException(sprintf("Title of the Feed, must be informed !"));

        if ($values['dateModified'] === null)
            throw new BusinessException(sprintf("Modified date of the Feed, must be informed !"));

        if (!$values['dateModified'] instanceof DateTime) {
            if ($values['dateModified'] instanceof SplDateTime)
                $values['dateModified'] = DateTime::convert($values['dateModified']);
            else
                $values['dateModified'] = new DateTime($values['dateModified']);
        }

        if ($values['description'] === null)
            $values['description'] = '';

        if (strlen($values['description']) > 300) {
            $values['description'] = substr($values['description'], 0, 294) . " [...]";
        }

        if ($values['type'] === null)
            $values['type'] = 'DEFAULT';
        else
            $values['type'] = strtoupper($values['type']);

        if ($values['nextExpectedUpdate'] === null) {
            $values['nextExpectedUpdate'] = new DateTime('now');
        } else {
            if ($values['lastUpdate'] !== null && $values['nextExpectedUpdate'] < $values['lastUpdate'])
                throw new BusinessException(sprintf("The Next Expected Update date, must be after Modified date !"));
        }

        $feed->setData($values);

        if ($feed->isValid()) {
            return parent::save($feed);
        }

        return null;
    }

    /**
     * Process a string and fetch related Feeds
     * @param string $params
     */
    public function search($params) {
        $params = trim($params);

        if ($params == null || !is_string($params) || strlen($params) < 3)
            throw new BusinessException(sprintf("Invalid search parameter !"), sprintf("Parameter must have a text with more than 3 letters."));

        $feed = $this->searchAtUrl($params);

        if ($feed !== null) {
            return array($feed);
        }

        if (strpos($params, 'http://') === 0 || strpos($params, 'https://') === 0) {

            if (strpos($params, 'http://') === 0)
                $params = substr($params, 7);
            else
                $params = substr($params, 8);

            if (strlen($params) < 5)
                throw new BusinessException(sprintf("Invalid search link !"), sprintf("Link must have more then five letters after the \"http://\""));

            $feeds = $this->dao->fetchFeedLinkBeginsWith($params);

            if (count($feeds) > 0)
                return $feeds;

            $feeds = $this->dao->fetchLinkBeginsWith($params);

            if (count($feeds) > 0)
                return $feeds;
        }

        $result = $this->dao->fetchTitleTermsWith(array($params));

        if (count($result) === 0) {
            $split_params = split(" ", $params);

            $result = $this->dao->fetchTitleTermsWith($split_params);
        }

        if (count($result) === 0) {
            $result = $this->dao->fetchFeedLinkWith($params);
        }

        return $result;
    }

    /**
     * Verify Feeds to update, and update they.
     */
    public function updateAll() {
        $feeds = $this->fetchByParams(array('nextExpectedUpdate' => array('lte' => new DateTime('now'))));
        foreach ($feeds as $feed) {
            echo sprintf("Updating Feed: %s\n", $feed->title);
            $this->updateFeed($feed);
        }
    }

    protected function getFeedData(ZendFeed $zfeed) {
        $data = array(
            'title' => $zfeed->getTitle(),
            'link' => $zfeed->getLink(),
            'feedLink' => $zfeed->getFeedLink(),
            'dateModified' => $zfeed->getDateModified(),
            'description' => $zfeed->getDescription(),
            'thumbnail' => $zfeed->getImage(),
        );

        if ($data['description'] === null)
            $data['description'] = "";

        if (preg_match('/(http|https):\/\/(www\.|)youtube\.com/', $data['link']))
            $data['type'] = "YOUTUBE";
        else
            $data['type'] = "DEFAULT";

        if ($data['thumbnail'])
            $data['thumbnail'] = $data['thumbnail']['uri'];

        if ($data['dateModified'])
            $data['dateModified'] = DateTime::convert($data['dateModified']);
        else
            $data['dateModified'] = new DateTime();

        return $data;
    }

    /**
     * Verify the Feed for updates
     * @param Feed $feed
     */
    public function updateFeed(Feed $feed) {
        if ($feed->lastUpdate == null || $feed->nextExpectedUpdate <= new DateTime('now')) {
            try {
                Reader::getHttpClient()->setOptions(array('sslverifypeer' => false));
                $zfeed = Reader::import($feed->feedLink);
                /* @var $zfeed ZendFeed */

                $data = $this->getFeedData($zfeed);

                foreach ($zfeed as $zentry) {

                    if ($feed->lastUpdate === null || $zentry->getDateModified() >= $feed->lastUpdate) {
                        /* @var $zentry EntryInterface */
                        $edata = array(
                            'link' => $zentry->getLink(),
                            'feedId' => $feed->id,
                            'title' => $zentry->getTitle(),
                            'dateModified' => $zentry->getDateModified(),
                            'content' => $zentry->getContent(),
                        );

                        $edata['authors'] = array();

                        if ($zentry->getAuthors() !== null) {
                            foreach ($zentry->getAuthors() as $author) {
                                $edata['authors'][] = $author['name'];
                            }
                        }

                        $edata['authors'] = implode(',', $edata['authors']);

                        if ($edata['dateModified'])
                            $edata['dateModified'] = DateTime::convert($edata['dateModified']);

                        if ($edata['content'])
                            $edata['content'] = "<p>{$edata['content']}</p>";
                        else
                            $edata['content'] = "<p></p>";

                        $entry = $this->getEntryService()->findByLink($edata['link']);
                        $this->getEntryService()->save($entry, $edata);
                    }
                }

                $data['lastUpdate'] = new DateTime;
                $data['nextExpectedUpdate'] = new DateTime;
                $data['nextExpectedUpdate']->hours++;

                $feed = $this->save($feed, $data);
            } catch (Exception $ex) {
                if ($ex instanceof DAOException || $ex instanceof BusinessException)
                    throw $ex;
            }
        }
    }

    /**
     * Seatch at a URL, and if has a Feed at it, process and return it
     * @param string $url
     * @return Feed
     */
    public function searchAtUrl($url) {
        try {
            Reader::getHttpClient()->setOptions(array('sslverifypeer' => false));

            if (strpos($url, "http://") === 0 && strpos($url, "https://") === 0)
                $url = "http://$url";

            if (strpos($url, "https://") === 0) {
                $url = "http" . substr($url, 5);
            }

            $feed = $this->dao->findByFeedLink($url);
            if ($feed !== null)
                return $feed;

            $links = Reader::findFeedLinks($url);
            $link = null;

            if ($links->rss) {
                $feed = $this->dao->findByFeedLink($links->rss);
                if ($feed !== null)
                    return $feed;

                $link = $links->rss;
            }

            if ($links->rdf) {
                $feed = $this->dao->findByFeedLink($links->rdf);
                if ($feed !== null)
                    return $feed;

                $link = $links->rdf;
            }

            if ($links->atom) {
                $feed = $this->dao->findByFeedLink($links->atom);
                if ($feed !== null)
                    return $feed;

                $link = $links->atom;
            }

            if ($link === null)
                $link = $url;

            $zfeed = Reader::import($link);
            /* @var $zfeed ZendFeed */

            $feed = $this->dao->findByFeedLink($zfeed->getFeedLink());
            if ($feed !== null)
                return $feed;

            $data = $this->getFeedData($zfeed);
            $data['nextExpectedUpdate'] = new DateTime('now');

            return $this->save(null, $data);
        } catch (Exception $ex) {
            if ($ex instanceof DAOException)
                throw $ex;

            return null;
        }
    }

}
