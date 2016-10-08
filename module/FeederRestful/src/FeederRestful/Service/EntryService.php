<?php

namespace FeederRestful\Service;

use Core\Model\DAO\DAOInterface;
use Core\Service\AbstractDAOService;
use Core\Service\Exception\BusinessException;
use Core\Util\DateTime;
use DateTime as SplDateTime;
use FeederRestful\Model\DAO\EntryDAOInterface;
use FeederRestful\Model\Entity\Entry;
use FeederRestful\Model\Entity\Feed;

/**
 * Service class for Entry
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class EntryService extends AbstractDAOService {

    /**
     * @var EntryDAOInterface
     */
    protected $dao;

    public function setDAOInterface(DAOInterface $dao) {
        if (!($dao instanceof EntryDAOInterface))
            throw new BusinessException(sprintf("This Service must receive a FeederRestful\Model\DAO\EntryDAOInterface !"));

        return parent::setDAOInterface($dao);
    }

    public function save($entry, array $values = null) {

        if ($entry == null) {
            $entry = new Entry;

            if (isset($values['id']) && $values['id'] !== null)
                throw new BusinessException(spritf("Entity Entry has auto-generated ID, if you want to edit this entity on database, you must send the object !"));
        } else {
            if (!($entry instanceof Entry))
                $entry = $this->findById($entry);
        }

        $values = $this->fillValues($values, $entry->getData());


        if ($values['feedId'] === null)
            throw new BusinessException(sprintf("Feed of the Entry, must be informed !"));

        if ($values['link'] === null)
            throw new BusinessException(sprintf("Link of the Entry, must be informed !"));

        if ($values['feedId'] instanceof Feed)
            $values['feedId'] = $values['feedId']->id;

        if (is_array($values['authors']))
            $values['authors'] = implode(',', $values['authors']);

        if ($values['title'] === null)
            $values['title'] = "";

        if ($values['dateModified'] === null)
            throw new BusinessException(sprintf("Modified date of the Entry, must be informed !"));

        if (!$values['dateModified'] instanceof DateTime) {
            if ($values['dateModified'] instanceof SplDateTime)
                $values['dateModified'] = DateTime::convert($values['dateModified']);
            else
                $values['dateModified'] = new DateTime($values['dateModified']);
        }

        if ($values['content'] === null)
            $values['content'] = '';

        if ($values['readed'])
            $values['readed'] = false;

        $entry->setData($values);

        if ($entry->isValid()) {
            return parent::save($entry);
        }

        return null;
    }

    /**
     * Find the Entry based on the link
     * @param string $link
     * @return Entry
     */
    public function findByLink($link) {
        return $this->dao->findByLink($link);
    }

    public function markAsReaded(Entry $entry) {
        
    }

    public function markAsUnreaded(Entry $entry) {
        
    }

}
